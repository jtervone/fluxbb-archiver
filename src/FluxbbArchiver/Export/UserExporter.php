<?php

declare(strict_types=1);

namespace FluxbbArchiver\Export;

use FluxbbArchiver\Config;
use FluxbbArchiver\Content\BbcodeParser;
use FluxbbArchiver\Content\SlugGenerator;
use FluxbbArchiver\Database;
use FluxbbArchiver\Html\TemplateEngine;
use FluxbbArchiver\I18n\Translator;

class UserExporter
{
    private Database $db;
    private Config $config;
    private Translator $translator;
    private BbcodeParser $bbcode;
    private TemplateEngine $engine;
    private SlugGenerator $slugGenerator;
    private string $boardTitle;

    /** @var array<int, array<string, mixed>> */
    private array $usersData = [];
    /** @var array<string, int> */
    private array $usernameToId = [];
    /** @var array<int, string> user_id => slug */
    private array $userSlugs = [];

    public function __construct(
        Database $db,
        Config $config,
        Translator $translator,
        BbcodeParser $bbcode,
        TemplateEngine $engine,
        SlugGenerator $slugGenerator,
        string $boardTitle
    ) {
        $this->db = $db;
        $this->config = $config;
        $this->translator = $translator;
        $this->bbcode = $bbcode;
        $this->engine = $engine;
        $this->slugGenerator = $slugGenerator;
        $this->boardTitle = $boardTitle;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function usersData(): array
    {
        return $this->usersData;
    }

    /**
     * Get user link HTML by username.
     */
    public function getUserLink(string $username, string $relativePath = '../'): string
    {
        $key = strtolower($username);
        if (isset($this->usernameToId[$key])) {
            $userId = $this->usernameToId[$key];
            $slug = $this->userSlugs[$userId] ?? 'user-' . $userId;
            return '<a href="' . $relativePath . 'users/' . $slug . '.html">' . BbcodeParser::h($username) . '</a>';
        }
        return BbcodeParser::h($username);
    }

    /**
     * Export public user profiles.
     */
    public function exportPublic(): int
    {
        $prefix = $this->db->prefix();
        $publicDir = $this->config->publicDir();
        $t = $this->translator;

        $rows = $this->db->fetchAll("
            SELECT u.id, u.username, u.title, u.realname, u.url, u.location,
                   u.signature, u.num_posts, u.registered, u.last_visit,
                   g.g_title as group_title
            FROM {$prefix}users u
            LEFT JOIN {$prefix}groups g ON u.group_id = g.g_id
            WHERE u.id > 1
            ORDER BY u.username
        ");

        // Build user slugs
        $usedSlugs = [];
        $userSlugMap = [];
        foreach ($rows as $user) {
            $baseSlug = $this->slugGenerator->generate($user['username'], 60);
            if (empty($baseSlug)) {
                $baseSlug = 'user';
            }
            $slug = $baseSlug;
            if (isset($usedSlugs[$slug])) {
                $slug = $baseSlug . '-' . $user['id'];
            }
            $usedSlugs[$slug] = true;
            $userSlugMap[(int)$user['id']] = $slug;
        }
        $this->userSlugs = $userSlugMap;

        $userCount = 0;
        foreach ($rows as $user) {
            $userCount++;
            $userId = (int)$user['id'];
            $userSlug = $this->userSlugs[$userId];

            $publicUser = [
                'id' => $userId,
                'username' => $user['username'],
                'title' => $user['title'],
                'realname' => $user['realname'],
                'url' => $user['url'],
                'location' => $user['location'],
                'signature' => $this->config->obfuscateEmails
                    ? BbcodeParser::obfuscateEmailsInText($user['signature'])
                    : ($user['signature'] ?? ''),
                'num_posts' => (int)$user['num_posts'],
                'registered' => (int)$user['registered'],
                'last_visit' => (int)$user['last_visit'],
                'group_title' => $user['group_title'],
                'slug' => $userSlug,
            ];

            $this->usersData[$user['id']] = $publicUser;
            $this->usernameToId[strtolower($user['username'])] = $userId;

            // Check for avatar
            $userAvatar = $this->findAvatar($publicDir, (int)$user['id']);

            // Avatar HTML
            if ($userAvatar) {
                $avatarHtml = '<img src="../' . $userAvatar . '" alt="' . BbcodeParser::h($user['username']) . ' avatar">';
            } else {
                $avatarHtml = '<div style="width:100px;height:100px;background:#ddd;border-radius:50%;margin:0 auto;display:flex;align-items:center;justify-content:center;font-size:2em;color:#666;">' . strtoupper(substr($user['username'], 0, 1)) . '</div>';
            }

            // Build profile details
            $details = [];
            $details[$t->get('username')] = BbcodeParser::h($user['username']);
            $details[$t->get('title')] = BbcodeParser::h($user['title'] ?: $user['group_title']);
            if ($user['realname']) {
                $details[$t->get('real_name')] = BbcodeParser::h($user['realname']);
            }
            if ($user['location']) {
                $details[$t->get('location')] = BbcodeParser::h($user['location']);
            }
            if ($user['url']) {
                $details[$t->get('website')] = '<a href="' . BbcodeParser::h($user['url']) . '" rel="nofollow">' . BbcodeParser::h($user['url']) . '</a>';
            }
            $details[$t->get('registered')] = self::formatTime((int)$user['registered'], $t->get('datetime_format'));
            $details[$t->get('last_visit')] = self::formatTime((int)$user['last_visit'], $t->get('datetime_format'));
            $details[$t->get('num_posts')] = number_format((int)$user['num_posts']);

            $content = $this->engine->render('user_profile', [
                'avatarHtml' => $avatarHtml,
                'details' => $details,
                'signatureHtml' => $user['signature'] ? $this->bbcode->toHtml($user['signature']) : null,
                'signatureTitle' => $t->get('signature'),
            ]);

            // Build description
            $userDesc = $user['username'] . ' - ' . ($user['title'] ?: $user['group_title']) . '. ' . number_format((int)$user['num_posts']) . ' posts.';
            if ($user['location']) {
                $userDesc .= ' From ' . $user['location'] . '.';
            }

            $html = $this->engine->renderPage($content, [
                'title' => $user['username'] . ' - ' . $t->get('profile'),
                'boardTitle' => $this->boardTitle,
                'lang' => $t->lang(),
                'relativePath' => '../',
                'breadcrumbs' => [$t->get('users') => 'users/index.html', $user['username'] => null],
                'seo' => [
                    'description' => $userDesc,
                    'type' => 'profile',
                    'image' => $userAvatar ? '../' . $userAvatar : '',
                ],
                'translator' => $t,
                'generatedAt' => date($t->get('generated_at_format')),
            ]);

            $this->writeWithJson(
                $publicDir . 'users/' . $userSlug . '.html',
                $html,
                $publicUser,
                $publicDir . 'json/users/' . $userSlug . '.json'
            );
        }

        // User index page
        $userCards = [];
        foreach ($this->usersData as $user) {
            $userCards[] = [
                'userSlug' => $user['slug'],
                'username' => BbcodeParser::h($user['username']),
                'userTitle' => BbcodeParser::h($user['title'] ?: $user['group_title']),
                'postCount' => sprintf($t->get('x_posts'), number_format($user['num_posts'])),
            ];
        }

        $content = $this->engine->render('user_list', ['userCards' => $userCards]);

        $html = $this->engine->renderPage($content, [
            'title' => $t->get('members_list'),
            'boardTitle' => $this->boardTitle,
            'lang' => $t->lang(),
            'relativePath' => '../',
            'breadcrumbs' => [$t->get('users') => null],
            'seo' => ['description' => sprintf($t->get('members_desc'), count($this->usersData))],
            'translator' => $t,
            'generatedAt' => date($t->get('generated_at_format')),
        ]);

        @file_put_contents($publicDir . 'users/index.html', $html);
        @file_put_contents(
            $publicDir . 'json/users_index.json',
            json_encode(array_values($this->usersData), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
        );

        return $userCount;
    }

    /**
     * Export sensitive (private) user data.
     */
    public function exportPrivate(): int
    {
        $prefix = $this->db->prefix();
        $publicDir = $this->config->publicDir();
        $privateDir = $this->config->privateDir();
        $t = $this->translator;

        $rows = $this->db->fetchAll("
            SELECT u.id, u.username, u.email, u.title, u.realname, u.url, u.location,
                   u.signature, u.num_posts, u.registered, u.last_visit,
                   u.registration_ip, u.last_post, u.admin_note,
                   g.g_title as group_title
            FROM {$prefix}users u
            LEFT JOIN {$prefix}groups g ON u.group_id = g.g_id
            WHERE u.id > 1
            ORDER BY u.username
        ");

        $privateUsers = [];
        foreach ($rows as $user) {
            $privateUser = [
                'id' => (int)$user['id'],
                'username' => $user['username'],
                'email' => $user['email'],
                'title' => $user['title'],
                'realname' => $user['realname'],
                'url' => $user['url'],
                'location' => $user['location'],
                'signature' => $this->config->obfuscateEmails
                    ? BbcodeParser::obfuscateEmailsInText($user['signature'])
                    : ($user['signature'] ?? ''),
                'num_posts' => (int)$user['num_posts'],
                'registered' => (int)$user['registered'],
                'last_visit' => (int)$user['last_visit'],
                'registration_ip' => $user['registration_ip'],
                'last_post' => (int)$user['last_post'],
                'admin_note' => $user['admin_note'],
                'group_title' => $user['group_title'],
            ];

            $privateUsers[] = $privateUser;

            // Avatar HTML
            $avatarFile = $this->findAvatar($publicDir, (int)$user['id']);
            if ($avatarFile) {
                $avatarHtml = '<img src="../../public/' . $avatarFile . '" alt="' . BbcodeParser::h($user['username']) . ' avatar">';
            } else {
                $avatarHtml = '<div style="width:100px;height:100px;background:#ddd;border-radius:50%;margin:0 auto;display:flex;align-items:center;justify-content:center;font-size:2em;color:#666;">' . strtoupper(substr($user['username'], 0, 1)) . '</div>';
            }

            // Build profile details (with sensitive fields)
            $details = [];
            $details[$t->get('username')] = BbcodeParser::h($user['username']);
            $details[$t->get('email')] = BbcodeParser::h($user['email']);
            $details[$t->get('title')] = BbcodeParser::h($user['title'] ?: $user['group_title']);
            if ($user['realname']) {
                $details[$t->get('real_name')] = BbcodeParser::h($user['realname']);
            }
            if ($user['location']) {
                $details[$t->get('location')] = BbcodeParser::h($user['location']);
            }
            if ($user['url']) {
                $details[$t->get('website')] = '<a href="' . BbcodeParser::h($user['url']) . '" rel="nofollow">' . BbcodeParser::h($user['url']) . '</a>';
            }
            $details[$t->get('registered')] = self::formatTime((int)$user['registered'], $t->get('datetime_format'));
            $details[$t->get('registration_ip')] = BbcodeParser::h($user['registration_ip']);
            $details[$t->get('last_visit')] = self::formatTime((int)$user['last_visit'], $t->get('datetime_format'));
            $details[$t->get('num_posts')] = number_format((int)$user['num_posts']);
            if ($user['admin_note']) {
                $details[$t->get('admin_note')] = BbcodeParser::h($user['admin_note']);
            }

            $content = $this->engine->render('user_profile', [
                'avatarHtml' => $avatarHtml,
                'details' => $details,
                'signatureHtml' => $user['signature'] ? $this->bbcode->toHtml($user['signature']) : null,
                'signatureTitle' => $t->get('signature'),
            ]);

            $html = $this->engine->renderPage($content, [
                'title' => $user['username'] . ' - ' . $t->get('full_profile'),
                'boardTitle' => $this->boardTitle,
                'lang' => $t->lang(),
                'relativePath' => '../',
                'breadcrumbs' => [$t->get('users') => 'index.html', $user['username'] => null],
                'seo' => [
                    'description' => $t->get('full_profile') . ': ' . $user['username'],
                    'noindex' => true,
                ],
                'translator' => $t,
                'generatedAt' => date($t->get('generated_at_format')),
            ]);

            $userSlug = $this->userSlugs[$user['id']] ?? 'user-' . $user['id'];
            $this->writeWithJson(
                $privateDir . 'users/' . $userSlug . '.html',
                $html,
                $privateUser,
                $privateDir . 'json/users/' . $userSlug . '.json'
            );
        }

        // Private users index
        $warningHtml = '<p class="warning"><strong>' . $t->get('private_warning') . ':</strong> ' . $t->get('users_warning_text') . '</p>';

        $userTableData = [];
        foreach ($privateUsers as $user) {
            $userTableData[] = [
                'id' => $user['id'],
                'username' => BbcodeParser::h($user['username']),
                'email' => BbcodeParser::h($user['email']),
                'registered' => self::formatTime($user['registered'], $t->get('datetime_format')),
                'numPosts' => number_format($user['num_posts']),
            ];
        }

        $content = $this->engine->render('private_user_list', [
            'warningHtml' => $warningHtml,
            'users' => $userTableData,
            'thUsername' => $t->get('username'),
            'thEmail' => $t->get('email'),
            'thRegistered' => $t->get('registered'),
            'thPosts' => $t->get('num_posts'),
        ]);

        $html = $this->engine->renderPage($content, [
            'title' => $t->get('users_full_data'),
            'boardTitle' => $this->boardTitle,
            'lang' => $t->lang(),
            'relativePath' => '../',
            'breadcrumbs' => [$t->get('users') => null],
            'seo' => ['description' => $t->get('users_desc'), 'noindex' => true],
            'translator' => $t,
            'generatedAt' => date($t->get('generated_at_format')),
        ]);

        @file_put_contents($privateDir . 'users/index.html', $html);
        @file_put_contents(
            $privateDir . 'json/users_index.json',
            json_encode($privateUsers, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
        );

        return count($privateUsers);
    }

    private function findAvatar(string $publicDir, int $userId): ?string
    {
        foreach (['jpg', 'jpeg', 'png', 'gif'] as $ext) {
            $file = 'img/avatars/' . $userId . '.' . $ext;
            if (file_exists($publicDir . $file)) {
                return $file;
            }
        }
        return null;
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

    public static function formatTime(int $timestamp, string $format = 'Y-m-d H:i:s'): string
    {
        return date($format, $timestamp);
    }
}
