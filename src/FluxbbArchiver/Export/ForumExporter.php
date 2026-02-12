<?php

declare(strict_types=1);

namespace FluxbbArchiver\Export;

use FluxbbArchiver\Config;
use FluxbbArchiver\Content\BbcodeParser;
use FluxbbArchiver\Database;
use FluxbbArchiver\Html\TemplateEngine;
use FluxbbArchiver\I18n\Translator;

class ForumExporter
{
    private const POSTS_PER_PAGE = 25;
    private const TOPICS_PER_PAGE = 50;

    private Database $db;
    private Config $config;
    private Translator $translator;
    private BbcodeParser $bbcode;
    private TemplateEngine $engine;
    private UserExporter $users;
    private string $boardTitle;

    /** @var array<int, string> topic_id => slug */
    private array $topicSlugs = [];
    /** @var array<int, array<string, mixed>> */
    private array $forumsData = [];
    /** @var array<array<string, mixed>> */
    private array $categories = [];
    /** @var array<array<string, mixed>> */
    private array $categoriesPrivate = [];

    private int $totalTopics = 0;
    private int $totalPosts = 0;

    public function __construct(
        Database $db,
        Config $config,
        Translator $translator,
        BbcodeParser $bbcode,
        TemplateEngine $engine,
        UserExporter $users,
        string $boardTitle
    ) {
        $this->db = $db;
        $this->config = $config;
        $this->translator = $translator;
        $this->bbcode = $bbcode;
        $this->engine = $engine;
        $this->users = $users;
        $this->boardTitle = $boardTitle;
    }

    /** @return array<int, string> */
    public function topicSlugs(): array
    {
        return $this->topicSlugs;
    }

    public function setTopicSlugs(array $slugs): void
    {
        $this->topicSlugs = $slugs;
    }

    /** @return array<array<string, mixed>> */
    public function categories(): array
    {
        return $this->categories;
    }

    /** @return array<array<string, mixed>> */
    public function categoriesPrivate(): array
    {
        return $this->categoriesPrivate;
    }

    /** @return array<int, array<string, mixed>> */
    public function forumsData(): array
    {
        return $this->forumsData;
    }

    public function totalTopics(): int
    {
        return $this->totalTopics;
    }

    public function totalPosts(): int
    {
        return $this->totalPosts;
    }

    /**
     * Identify private forums and load category/forum structure.
     *
     * @return array{int, int} [publicForumCount, privateForumCount]
     */
    public function loadStructure(): array
    {
        $prefix = $this->db->prefix();

        // Identify private forums
        $privateForumIds = [];
        $rows = $this->db->fetchAll("
            SELECT DISTINCT forum_id
            FROM {$prefix}forum_perms
            WHERE group_id = 3 AND read_forum = 0
        ");
        foreach ($rows as $row) {
            $privateForumIds[$row['forum_id']] = true;
        }

        // Load categories and forums
        $categories = [];
        $categoriesPrivate = [];

        $rows = $this->db->fetchAll("
            SELECT c.id as cat_id, c.cat_name, c.disp_position as cat_position,
                   f.id as forum_id, f.forum_name, f.forum_desc, f.num_topics, f.num_posts,
                   f.last_post, f.last_poster, f.disp_position as forum_position
            FROM {$prefix}categories c
            LEFT JOIN {$prefix}forums f ON c.id = f.cat_id
            WHERE f.redirect_url IS NULL OR f.redirect_url = ''
            ORDER BY c.disp_position, f.disp_position
        ");

        foreach ($rows as $row) {
            $catId = (int)$row['cat_id'];
            $isPrivate = isset($privateForumIds[$row['forum_id']]);
            if ($isPrivate && in_array($row['cat_name'], $this->config->forcePublicCategories, true)) {
                $isPrivate = false;
            }

            if (!isset($categories[$catId])) {
                $categories[$catId] = [
                    'id' => $catId,
                    'name' => $row['cat_name'],
                    'position' => (int)$row['cat_position'],
                    'forums' => [],
                ];
            }
            if (!isset($categoriesPrivate[$catId])) {
                $categoriesPrivate[$catId] = [
                    'id' => $catId,
                    'name' => $row['cat_name'],
                    'position' => (int)$row['cat_position'],
                    'forums' => [],
                ];
            }

            if ($row['forum_id']) {
                $forum = [
                    'id' => (int)$row['forum_id'],
                    'name' => $row['forum_name'],
                    'description' => $row['forum_desc'],
                    'num_topics' => (int)$row['num_topics'],
                    'num_posts' => (int)$row['num_posts'],
                    'last_post' => (int)$row['last_post'],
                    'last_poster' => $row['last_poster'],
                    'position' => (int)$row['forum_position'],
                    'is_private' => $isPrivate,
                ];

                if ($isPrivate) {
                    $categoriesPrivate[$catId]['forums'][] = $forum;
                } else {
                    $categories[$catId]['forums'][] = $forum;
                }
                $this->forumsData[$row['forum_id']] = $forum;
            }
        }

        // Filter empty and sort
        $categories = array_filter($categories, fn(array $cat): bool => !empty($cat['forums']));
        $categoriesPrivate = array_filter($categoriesPrivate, fn(array $cat): bool => !empty($cat['forums']));

        usort($categories, fn(array $a, array $b): int => $a['position'] - $b['position']);
        usort($categoriesPrivate, fn(array $a, array $b): int => $a['position'] - $b['position']);

        foreach ($categories as &$cat) {
            usort($cat['forums'], fn(array $a, array $b): int => $a['position'] - $b['position']);
        }
        foreach ($categoriesPrivate as &$cat) {
            usort($cat['forums'], fn(array $a, array $b): int => $a['position'] - $b['position']);
        }
        unset($cat);

        $this->categories = array_values($categories);
        $this->categoriesPrivate = array_values($categoriesPrivate);

        $publicCount = count(array_filter($this->forumsData, fn(array $f): bool => !$f['is_private']));
        $privateCount = count(array_filter($this->forumsData, fn(array $f): bool => $f['is_private']));

        return [$publicCount, $privateCount];
    }

    /**
     * Export all forum topics and posts.
     */
    public function exportTopicsAndPosts(): void
    {
        $prefix = $this->db->prefix();
        $publicDir = $this->config->publicDir();
        $privateDir = $this->config->privateDir();
        $t = $this->translator;
        $usersData = $this->users->usersData();

        foreach ($this->forumsData as $forumId => $forum) {
            $isPrivate = $forum['is_private'];
            $baseDir = $isPrivate ? $privateDir : $publicDir;

            $topics = $this->db->fetchAll("
                SELECT id, poster, subject, posted, first_post_id, last_post, last_post_id,
                       last_poster, num_views, num_replies, closed, sticky
                FROM {$prefix}topics
                WHERE forum_id = {$forumId} AND moved_to IS NULL
                ORDER BY sticky DESC, last_post DESC
            ");

            $this->totalTopics += count($topics);

            // Forum pages
            $forumTotalPages = max(1, (int)ceil(count($topics) / self::TOPICS_PER_PAGE));

            for ($forumPage = 1; $forumPage <= $forumTotalPages; $forumPage++) {
                $start = ($forumPage - 1) * self::TOPICS_PER_PAGE;
                $pageTopics = array_slice($topics, $start, self::TOPICS_PER_PAGE);

                $forumDesc = $forum['description'] ?: $forum['name'] . ' ' . $t->get('forum_suffix') . '.';
                $forumDesc .= ' ' . sprintf($t->get('x_topics'), number_format($forum['num_topics'])) . ', ' . sprintf($t->get('x_posts'), number_format($forum['num_posts'])) . '.';
                if ($forumTotalPages > 1) {
                    $forumDesc .= ' ' . sprintf($t->get('page_x_of_y'), $forumPage, $forumTotalPages) . '.';
                }

                $paginationHtml = $this->renderPagination($forumPage, $forumTotalPages, 'forum_' . $forumId);

                // Build topic row data for template
                $topicRows = [];
                foreach ($pageTopics as $topic) {
                    $rowClass = '';
                    if ($topic['sticky']) $rowClass .= ' topic-sticky';
                    if ($topic['closed']) $rowClass .= ' topic-closed';

                    $badges = '';
                    if ($topic['sticky']) $badges .= '<strong>[' . $t->get('sticky') . ']</strong> ';
                    if ($topic['closed']) $badges .= '<em>[' . $t->get('closed') . ']</em> ';

                    $topicRows[] = [
                        'rowClass' => trim($rowClass),
                        'badges' => $badges,
                        'topicUrl' => '../topics/' . $this->getTopicFilename((int)$topic['id'], 1),
                        'subject' => BbcodeParser::h($topic['subject']),
                        'authorLink' => $this->users->getUserLink($topic['poster'], '../'),
                        'replies' => number_format((int)$topic['num_replies']),
                        'views' => number_format((int)$topic['num_views']),
                        'lastPostDate' => UserExporter::formatTime((int)$topic['last_post'], $t->get('datetime_format')),
                        'lastPosterLink' => $this->users->getUserLink($topic['last_poster'], '../'),
                        'byLabel' => $t->get('by'),
                    ];
                }

                $content = $this->engine->render('forum', [
                    'forumDescription' => $forum['description'] ? $this->bbcode->toHtml($forum['description']) : '',
                    'paginationHtml' => $paginationHtml,
                    'topicRows' => $topicRows,
                    'thTopic' => $t->get('topic'),
                    'thAuthor' => $t->get('author'),
                    'thReplies' => $t->get('replies'),
                    'thViews' => $t->get('views'),
                    'thLastPost' => $t->get('last_post'),
                ]);

                $html = $this->engine->renderPage($content, [
                    'title' => $forum['name'] . ($forumPage > 1 ? ' - ' . $t->get('page') . ' ' . $forumPage : ''),
                    'boardTitle' => $this->boardTitle,
                    'lang' => $t->lang(),
                    'relativePath' => '../',
                    'breadcrumbs' => [$forum['name'] => null],
                    'seo' => ['description' => $forumDesc, 'type' => 'website', 'noindex' => $isPrivate],
                    'translator' => $t,
                    'generatedAt' => date($t->get('generated_at_format')),
                ]);

                $forumJson = [
                    'forum' => $forum,
                    'page' => $forumPage,
                    'total_pages' => $forumTotalPages,
                    'topics' => array_map(function (array $t): array {
                        return [
                            'id' => (int)$t['id'],
                            'subject' => $t['subject'],
                            'slug' => $this->topicSlugs[(int)$t['id']] ?? 'topic_' . $t['id'],
                            'poster' => $t['poster'],
                            'posted' => (int)$t['posted'],
                            'num_replies' => (int)$t['num_replies'],
                            'num_views' => (int)$t['num_views'],
                            'last_post' => (int)$t['last_post'],
                            'last_poster' => $t['last_poster'],
                            'closed' => (bool)$t['closed'],
                            'sticky' => (bool)$t['sticky'],
                        ];
                    }, $pageTopics),
                ];

                $this->writeWithJson(
                    $baseDir . 'forums/forum_' . $forumId . '_p' . $forumPage . '.html',
                    $html,
                    $forumJson,
                    $baseDir . 'json/forums/forum_' . $forumId . '_p' . $forumPage . '.json'
                );
            }

            // Export each topic's posts
            foreach ($topics as $topic) {
                $this->exportTopicPosts($topic, $forum, $forumId, $isPrivate, $baseDir, $usersData);
            }
        }
    }

    private function exportTopicPosts(array $topic, array $forum, int $forumId, bool $isPrivate, string $baseDir, array $usersData): void
    {
        $prefix = $this->db->prefix();
        $t = $this->translator;
        $publicDir = $this->config->publicDir();

        $posts = $this->db->fetchAll("
            SELECT p.id, p.poster, p.poster_id, p.message, p.hide_smilies,
                   p.posted, p.edited, p.edited_by
            FROM {$prefix}posts p
            WHERE p.topic_id = {$topic['id']}
            ORDER BY p.id
        ");

        $this->totalPosts += count($posts);

        $topicTotalPages = max(1, (int)ceil(count($posts) / self::POSTS_PER_PAGE));

        for ($topicPage = 1; $topicPage <= $topicTotalPages; $topicPage++) {
            $start = ($topicPage - 1) * self::POSTS_PER_PAGE;
            $pagePosts = array_slice($posts, $start, self::POSTS_PER_PAGE);

            $breadcrumbs = [
                $forum['name'] => 'forums/forum_' . $forumId . '_p1.html',
                $topic['subject'] => null,
            ];

            // Description from first post
            $firstPostText = isset($posts[0]) ? ($posts[0]['message'] ?? '') : '';
            $firstPostText = preg_replace('/\[.*?\]/', '', $firstPostText);
            $firstPostText = strip_tags($firstPostText);
            $firstPostText = preg_replace('/\s+/', ' ', trim($firstPostText));
            if ($this->config->obfuscateEmails) {
                $firstPostText = BbcodeParser::obfuscateEmailsInText($firstPostText);
            }

            $topicDesc = $firstPostText;
            if ($topicTotalPages > 1) {
                $topicDesc = sprintf($t->get('page_x_of_y'), $topicPage, $topicTotalPages) . '. ' . $topicDesc;
            }

            $topicSlug = $this->topicSlugs[(int)$topic['id']] ?? 'topic_' . $topic['id'];
            $paginationHtml = $this->renderPagination($topicPage, $topicTotalPages, $topicSlug);

            // Build post data for template
            $postNum = $start;
            $postDataList = [];
            foreach ($pagePosts as $post) {
                $postNum++;
                $posterData = $usersData[$post['poster_id']] ?? null;

                $avatarBasePath = $isPrivate ? '../../public/' : '../';
                $userBasePath = $isPrivate ? '../../public/' : '../';

                $postDataList[] = $this->buildPostData(
                    $post, $posterData, $postNum, 'p',
                    $avatarBasePath, $userBasePath, $publicDir, $t
                );
            }

            $content = $this->engine->render('topic', [
                'paginationHtml' => $paginationHtml,
                'posts' => $postDataList,
            ]);

            $html = $this->engine->renderPage($content, [
                'title' => $topic['subject'] . ($topicPage > 1 ? ' - ' . $t->get('page') . ' ' . $topicPage : ''),
                'boardTitle' => $this->boardTitle,
                'lang' => $t->lang(),
                'relativePath' => '../',
                'breadcrumbs' => $breadcrumbs,
                'seo' => ['description' => $topicDesc, 'type' => 'article', 'noindex' => $isPrivate],
                'translator' => $t,
                'generatedAt' => date($t->get('generated_at_format')),
            ]);

            $topicJson = [
                'topic' => [
                    'id' => (int)$topic['id'],
                    'subject' => $topic['subject'],
                    'slug' => $topicSlug,
                    'poster' => $topic['poster'],
                    'posted' => (int)$topic['posted'],
                    'forum_id' => $forumId,
                    'forum_name' => $forum['name'],
                ],
                'page' => $topicPage,
                'total_pages' => $topicTotalPages,
                'posts' => array_map(function (array $p): array {
                    $msg = $this->config->obfuscateEmails
                        ? BbcodeParser::obfuscateEmailsInText($p['message'])
                        : ($p['message'] ?? '');
                    return [
                        'id' => (int)$p['id'],
                        'poster' => $p['poster'],
                        'poster_id' => (int)$p['poster_id'],
                        'message' => $msg,
                        'posted' => (int)$p['posted'],
                        'edited' => $p['edited'] ? (int)$p['edited'] : null,
                        'edited_by' => $p['edited_by'],
                    ];
                }, $pagePosts),
            ];

            $this->writeWithJson(
                $baseDir . 'topics/' . $topicSlug . '_p' . $topicPage . '.html',
                $html,
                $topicJson,
                $baseDir . 'json/topics/' . $topicSlug . '_p' . $topicPage . '.json'
            );
        }
    }

    /**
     * Build post data array for template rendering.
     */
    private function buildPostData(
        array $post,
        ?array $posterData,
        int $postNum,
        string $idPrefix,
        string $avatarBasePath,
        string $userBasePath,
        string $publicDir,
        Translator $t
    ): array {
        // Avatar
        $avatarHtml = '';
        $avatarFound = false;
        if ($posterData) {
            foreach (['jpg', 'jpeg', 'png', 'gif'] as $ext) {
                $avatarFile = 'img/avatars/' . $post['poster_id'] . '.' . $ext;
                if (file_exists($publicDir . $avatarFile)) {
                    $avatarHtml = '<img src="' . $avatarBasePath . $avatarFile . '" alt="">';
                    $avatarFound = true;
                    break;
                }
            }
        }
        if (!$avatarFound) {
            $avatarHtml = '<div class="avatar-placeholder">' . strtoupper(substr($post['poster'] ?? $post['poster_name'] ?? '?', 0, 1)) . '</div>';
        }

        // Username
        if ($posterData) {
            $posterName = $post['poster'] ?? $post['poster_name'] ?? '';
            $usernameHtml = $this->users->getUserLink($posterName, $userBasePath);
        } else {
            $usernameHtml = BbcodeParser::h($post['poster'] ?? $post['poster_name'] ?? '');
        }

        // User title
        $userTitle = '';
        if ($posterData) {
            $userTitle = BbcodeParser::h($posterData['title'] ?: $posterData['group_title']);
        }

        // User details
        $userDetails = null;
        if ($posterData) {
            $userDetails = [];
            if ($posterData['location']) {
                $userDetails[$t->get('location')] = BbcodeParser::h($posterData['location']);
            }
            $userDetails[$t->get('registered')] = date($t->get('date_format'), $posterData['registered']);
            $userDetails[$t->get('num_posts')] = number_format($posterData['num_posts']);
        }

        // Signature
        $signature = null;
        if ($posterData && $posterData['signature']) {
            $signature = $this->bbcode->toHtml($posterData['signature']);
        }

        // Edited notice
        $editedNotice = null;
        if (isset($post['edited']) && $post['edited']) {
            $editedNotice = sprintf($t->get('last_edited_by'), BbcodeParser::h($post['edited_by']), UserExporter::formatTime((int)$post['edited'], $t->get('datetime_format')));
        }

        return [
            'postId' => (int)$post['id'],
            'postNum' => $postNum,
            'postDate' => UserExporter::formatTime((int)$post['posted'], $t->get('datetime_format')),
            'idPrefix' => $idPrefix,
            'body' => $this->bbcode->toHtml($post['message'] ?? null),
            'signature' => $signature,
            'editedNotice' => $editedNotice,
            'avatarHtml' => $avatarHtml,
            'usernameHtml' => $usernameHtml,
            'userTitle' => $userTitle,
            'userDetails' => $userDetails,
        ];
    }

    /**
     * Generate main public index page.
     */
    public function exportMainIndex(): void
    {
        $publicDir = $this->config->publicDir();
        $t = $this->translator;
        $boardTitle = $this->boardTitle;

        $mainDesc = $boardTitle . ' - Forum archive with ' . count($this->categories) . ' categories, ' .
            count($this->forumsData) . ' forums, ' . number_format($this->totalTopics) . ' topics and ' .
            number_format($this->totalPosts) . ' posts.';

        // Build category data with forum items for template
        $categoriesData = [];
        foreach ($this->categories as $category) {
            $forumItems = [];
            foreach ($category['forums'] as $forum) {
                $forumItems[] = [
                    'icon' => '&#128172;',
                    'forumUrl' => 'forums/forum_' . $forum['id'] . '_p1.html',
                    'forumName' => BbcodeParser::h($forum['name']),
                    'description' => $forum['description'] ? $this->bbcode->toHtml($forum['description']) : '',
                    'topicCount' => sprintf($t->get('x_topics'), number_format($forum['num_topics'])),
                    'postCount' => sprintf($t->get('x_posts'), number_format($forum['num_posts'])),
                    'lastPoster' => $forum['last_poster'] ? $this->users->getUserLink($forum['last_poster'], '') : '',
                    'lastPosterLabel' => $t->get('last_poster'),
                ];
            }
            $categoriesData[] = [
                'name' => $category['name'],
                'forumItems' => $forumItems,
            ];
        }

        $content = $this->engine->render('index', [
            'categories' => $categoriesData,
            'quickLinksTitle' => $t->get('quick_links'),
            'membersListText' => $t->get('members_list'),
        ]);

        $html = $this->engine->renderPage($content, [
            'title' => $boardTitle,
            'boardTitle' => $boardTitle,
            'lang' => $t->lang(),
            'relativePath' => '',
            'breadcrumbs' => [],
            'seo' => ['description' => $mainDesc, 'type' => 'website'],
            'translator' => $t,
            'generatedAt' => date($t->get('generated_at_format')),
        ]);

        @file_put_contents($publicDir . 'index.html', $html);

        $indexJson = [
            'board_title' => $boardTitle,
            'export_date' => date('c'),
            'statistics' => [
                'total_users' => count($this->users->usersData()),
                'total_topics' => $this->totalTopics,
                'total_posts' => $this->totalPosts,
                'total_forums' => count($this->forumsData),
                'total_categories' => count($this->categories),
            ],
            'categories' => $this->categories,
        ];
        @file_put_contents($publicDir . 'json/index.json', json_encode($indexJson, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }

    /**
     * Generate private forums index page.
     */
    public function exportPrivateIndex(): void
    {
        if (empty($this->categoriesPrivate)) {
            return;
        }

        $privateDir = $this->config->privateDir();
        $t = $this->translator;
        $boardTitle = $this->boardTitle;

        $warningHtml = '<p class="warning"><strong>' . $t->get('private_warning') . ':</strong> ' . $t->get('private_warning_text') . '</p>';

        // Build category data
        $categoriesData = [];
        foreach ($this->categoriesPrivate as $category) {
            $forumItems = [];
            foreach ($category['forums'] as $forum) {
                $forumItems[] = [
                    'icon' => '&#128274;',
                    'forumUrl' => 'forums/forum_' . $forum['id'] . '_p1.html',
                    'forumName' => BbcodeParser::h($forum['name']),
                    'description' => $forum['description'] ? $this->bbcode->toHtml($forum['description']) : '',
                    'topicCount' => sprintf($t->get('x_topics'), number_format($forum['num_topics'])),
                    'postCount' => sprintf($t->get('x_posts'), number_format($forum['num_posts'])),
                    'lastPoster' => $forum['last_poster'] ? BbcodeParser::h($forum['last_poster']) : '',
                    'lastPosterLabel' => $t->get('last_poster'),
                ];
            }
            $categoriesData[] = [
                'name' => $category['name'],
                'forumItems' => $forumItems,
            ];
        }

        $content = $this->engine->render('private_index', [
            'warningHtml' => $warningHtml,
            'categories' => $categoriesData,
            'otherDataTitle' => $t->get('other_private_data'),
            'quickLinks' => [
                ['url' => 'messages/', 'text' => $t->get('private_messages')],
                ['url' => 'users/', 'text' => $t->get('users_full_data')],
            ],
        ]);

        $html = $this->engine->renderPage($content, [
            'title' => $t->get('private_forums') . ' - ' . $boardTitle,
            'boardTitle' => $boardTitle,
            'lang' => $t->lang(),
            'relativePath' => '',
            'breadcrumbs' => [],
            'seo' => [
                'description' => $t->get('private_forums_desc'),
                'type' => 'website',
                'noindex' => true,
            ],
            'translator' => $t,
            'generatedAt' => date($t->get('generated_at_format')),
        ]);

        @file_put_contents($privateDir . 'forums.html', $html);

        $privateIndexJson = [
            'board_title' => $boardTitle,
            'export_date' => date('c'),
            'type' => 'private_forums',
            'categories' => $this->categoriesPrivate,
        ];
        @file_put_contents($privateDir . 'json/forums_index.json', json_encode($privateIndexJson, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }

    private function renderPagination(int $currentPage, int $totalPages, string $baseUrl): string
    {
        $t = $this->translator;
        return $this->engine->partial('pagination', [
            'currentPage' => $currentPage,
            'totalPages' => $totalPages,
            'baseUrl' => $baseUrl,
            'firstText' => $t->get('first'),
            'prevText' => $t->get('prev'),
            'nextText' => $t->get('next'),
            'lastText' => $t->get('last'),
        ]);
    }

    private function getTopicFilename(int $topicId, int $page = 1): string
    {
        if (isset($this->topicSlugs[$topicId])) {
            return $this->topicSlugs[$topicId] . '_p' . $page . '.html';
        }
        return 'topic_' . $topicId . '_p' . $page . '.html';
    }

    private function writeWithJson(string $htmlPath, string $htmlContent, array $jsonData, string $jsonPath): void
    {
        $htmlDir = dirname($htmlPath);
        if (!is_dir($htmlDir)) {
            @mkdir($htmlDir, 0777, true);
        }
        @file_put_contents($htmlPath, $htmlContent);

        $jsonDir = dirname($jsonPath);
        if (!is_dir($jsonDir)) {
            @mkdir($jsonDir, 0777, true);
        }
        @file_put_contents($jsonPath, json_encode($jsonData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }
}
