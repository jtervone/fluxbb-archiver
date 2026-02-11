<?php

declare(strict_types=1);

namespace FluxbbArchiver\Export;

use FluxbbArchiver\Config;
use FluxbbArchiver\Content\BbcodeParser;
use FluxbbArchiver\Database;
use FluxbbArchiver\Html\TemplateEngine;
use FluxbbArchiver\I18n\Translator;

class MessageExporter
{
    private Database $db;
    private Config $config;
    private Translator $translator;
    private BbcodeParser $bbcode;
    private TemplateEngine $engine;
    private UserExporter $users;
    private string $boardTitle;
    private bool $hasPrivateCategories;

    public function __construct(
        Database $db,
        Config $config,
        Translator $translator,
        BbcodeParser $bbcode,
        TemplateEngine $engine,
        UserExporter $users,
        string $boardTitle,
        bool $hasPrivateCategories
    ) {
        $this->db = $db;
        $this->config = $config;
        $this->translator = $translator;
        $this->bbcode = $bbcode;
        $this->engine = $engine;
        $this->users = $users;
        $this->boardTitle = $boardTitle;
        $this->hasPrivateCategories = $hasPrivateCategories;
    }

    /**
     * Export private messages.
     *
     * @return int Number of conversations exported.
     */
    public function export(): int
    {
        $prefix = $this->db->prefix();
        $privateDir = $this->config->privateDir();
        $publicDir = $this->config->publicDir();
        $t = $this->translator;
        $usersData = $this->users->usersData();

        if (!$this->db->tableExists($prefix . 'pms_new_topics')) {
            $this->writeNoMessagesIndex();
            return 0;
        }

        $pmResult = $this->db->fetchAll("
            SELECT t.id, t.topic as subject, t.starter_id, t.to_id, t.replies as num_replies,
                   t.last_posted as last_post, t.starter as starter_name, t.to_user as recipient_name
            FROM {$prefix}pms_new_topics t
            ORDER BY t.last_posted DESC
        ");

        $pmConversations = [];
        $pmCount = 0;

        foreach ($pmResult as $pm) {
            $pmCount++;

            $messages = $this->db->fetchAll("
                SELECT p.id, p.message, p.posted, p.poster_id, p.poster as poster_name
                FROM {$prefix}pms_new_posts p
                WHERE p.topic_id = {$pm['id']}
                ORDER BY p.posted
            ");

            $messageData = [];
            foreach ($messages as $msg) {
                $msgText = $this->config->obfuscateEmails
                    ? BbcodeParser::obfuscateEmailsInText($msg['message'])
                    : ($msg['message'] ?? '');
                $messageData[] = [
                    'id' => (int)$msg['id'],
                    'poster_id' => (int)$msg['poster_id'],
                    'poster_name' => $msg['poster_name'],
                    'message' => $msgText,
                    'posted' => (int)$msg['posted'],
                ];
            }

            $conversation = [
                'id' => (int)$pm['id'],
                'subject' => $pm['subject'],
                'starter_id' => (int)$pm['starter_id'],
                'starter_name' => $pm['starter_name'],
                'recipient_id' => (int)$pm['to_id'],
                'recipient_name' => $pm['recipient_name'],
                'num_replies' => (int)$pm['num_replies'],
                'last_post' => (int)$pm['last_post'],
                'messages' => $messageData,
            ];

            $pmConversations[] = $conversation;

            // Build post data for each message
            $postDataList = [];
            $msgNum = 0;
            foreach ($messageData as $msg) {
                $msgNum++;
                $posterData = $usersData[$msg['poster_id']] ?? null;

                $avatarHtml = $this->buildAvatarHtml($publicDir, $msg['poster_id'], $msg['poster_name'], $posterData);
                $usernameHtml = $posterData
                    ? '<a href="../../public/users/user_' . $msg['poster_id'] . '.html">' . BbcodeParser::h($msg['poster_name']) . '</a>'
                    : BbcodeParser::h($msg['poster_name']);

                $userDetails = null;
                $userTitle = '';
                if ($posterData) {
                    $userTitle = BbcodeParser::h($posterData['title'] ?: $posterData['group_title']);
                    $userDetails = [];
                    if ($posterData['location']) {
                        $userDetails[$t->get('location')] = BbcodeParser::h($posterData['location']);
                    }
                    $userDetails[$t->get('registered')] = date('Y-m-d', $posterData['registered']);
                    $userDetails[$t->get('num_posts')] = number_format($posterData['num_posts']);
                }

                $postDataList[] = [
                    'postId' => $msg['id'],
                    'postNum' => $msgNum,
                    'postDate' => UserExporter::formatTime($msg['posted']),
                    'idPrefix' => 'm',
                    'body' => $this->bbcode->toHtml($msg['message']),
                    'signature' => null,
                    'editedNotice' => null,
                    'avatarHtml' => $avatarHtml,
                    'usernameHtml' => $usernameHtml,
                    'userTitle' => $userTitle,
                    'userDetails' => $userDetails,
                ];
            }

            $starterLink = '<a href="../../public/users/user_' . $pm['starter_id'] . '.html">' . BbcodeParser::h($pm['starter_name']) . '</a>';
            $recipientLink = '<a href="../../public/users/user_' . $pm['to_id'] . '.html">' . BbcodeParser::h($pm['recipient_name']) . '</a>';

            $content = $this->engine->render('pm_conversation', [
                'participantsLabel' => $t->get('participants'),
                'starterLink' => $starterLink,
                'recipientLink' => $recipientLink,
                'messages' => $postDataList,
            ]);

            $html = $this->engine->renderPage($content, [
                'title' => 'PM: ' . $pm['subject'],
                'boardTitle' => $this->boardTitle,
                'lang' => $t->lang(),
                'relativePath' => '../',
                'breadcrumbs' => [$t->get('private_messages') => 'index.html', $pm['subject'] => null],
                'seo' => [
                    'description' => sprintf($t->get('private_conversation'), $pm['starter_name'], $pm['recipient_name']),
                    'noindex' => true,
                ],
                'translator' => $t,
                'generatedAt' => date('Y-m-d H:i:s T'),
            ]);

            $this->writeWithJson(
                $privateDir . 'messages/pm_' . $pm['id'] . '.html',
                $html,
                $conversation,
                $privateDir . 'json/messages/pm_' . $pm['id'] . '.json'
            );
        }

        // Messages index page
        $warningHtml = '<p class="warning"><strong>' . $t->get('private_warning') . ':</strong> ' . $t->get('messages_warning_text') . '</p>';

        $conversationItems = [];
        foreach ($pmConversations as $pm) {
            $conversationItems[] = [
                'url' => 'messages/pm_' . $pm['id'] . '.html',
                'subject' => BbcodeParser::h($pm['subject']),
                'starterLink' => '<a href="../public/users/user_' . $pm['starter_id'] . '.html">' . BbcodeParser::h($pm['starter_name']) . '</a>',
                'recipientLink' => '<a href="../public/users/user_' . $pm['recipient_id'] . '.html">' . BbcodeParser::h($pm['recipient_name']) . '</a>',
                'preview' => sprintf($t->get('x_messages_last'), $pm['num_replies'] + 1, UserExporter::formatTime($pm['last_post'])),
            ];
        }

        $quickLinks = [];
        if ($this->hasPrivateCategories) {
            $quickLinks[] = ['url' => 'forums.html', 'text' => $t->get('private_forums')];
        }
        $quickLinks[] = ['url' => 'users/index.html', 'text' => $t->get('users_full_data')];

        $content = $this->engine->render('pm_index', [
            'warningHtml' => $warningHtml,
            'conversations' => $conversationItems,
            'otherDataTitle' => $t->get('other_private_data'),
            'quickLinks' => $quickLinks,
        ]);

        $html = $this->engine->renderPage($content, [
            'title' => $t->get('private_messages'),
            'boardTitle' => $this->boardTitle,
            'lang' => $t->lang(),
            'relativePath' => '',
            'breadcrumbs' => [$t->get('private_messages') => null],
            'seo' => [
                'description' => $t->get('private_messages_desc'),
                'noindex' => true,
            ],
            'translator' => $t,
            'generatedAt' => date('Y-m-d H:i:s T'),
        ]);

        @file_put_contents($privateDir . 'index.html', $html);
        @file_put_contents(
            $privateDir . 'json/messages_index.json',
            json_encode($pmConversations, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
        );

        return $pmCount;
    }

    private function writeNoMessagesIndex(): void
    {
        $privateDir = $this->config->privateDir();
        $t = $this->translator;

        $quickLinks = [];
        if ($this->hasPrivateCategories) {
            $quickLinks[] = ['url' => 'forums.html', 'text' => $t->get('private_forums')];
        }
        $quickLinks[] = ['url' => 'users/index.html', 'text' => $t->get('users_full_data')];

        $content = $this->engine->render('pm_empty', [
            'noMessagesText' => $t->get('no_private_messages'),
            'privateDataTitle' => $t->get('private_data'),
            'quickLinks' => $quickLinks,
        ]);

        $html = $this->engine->renderPage($content, [
            'title' => $t->get('private_data'),
            'boardTitle' => $this->boardTitle,
            'lang' => $t->lang(),
            'relativePath' => '',
            'breadcrumbs' => [$t->get('private_data') => null],
            'seo' => [
                'description' => $t->get('private_data_desc'),
                'noindex' => true,
            ],
            'translator' => $t,
            'generatedAt' => date('Y-m-d H:i:s T'),
        ]);

        @file_put_contents($privateDir . 'index.html', $html);
    }

    private function buildAvatarHtml(string $publicDir, int $userId, string $username, ?array $posterData): string
    {
        if ($posterData) {
            foreach (['jpg', 'jpeg', 'png', 'gif'] as $ext) {
                $avatarFile = 'img/avatars/' . $userId . '.' . $ext;
                if (file_exists($publicDir . $avatarFile)) {
                    return '<img src="../../public/' . $avatarFile . '" alt="">';
                }
            }
        }
        return '<div class="avatar-placeholder">' . strtoupper(substr($username, 0, 1)) . '</div>';
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
