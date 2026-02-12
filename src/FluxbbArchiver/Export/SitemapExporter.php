<?php

declare(strict_types=1);

namespace FluxbbArchiver\Export;

use FluxbbArchiver\Config;
use FluxbbArchiver\Database;

class SitemapExporter
{
    private const POSTS_PER_PAGE = 25;
    private const TOPICS_PER_PAGE = 50;

    private Database $db;
    private Config $config;

    public function __construct(Database $db, Config $config)
    {
        $this->db = $db;
        $this->config = $config;
    }

    /**
     * Generate sitemap.xml.
     *
     * @param array<int, array<string, mixed>> $usersData
     * @param array<array<string, mixed>> $categories Public categories with forums.
     * @param array<int, string> $topicSlugs
     * @return int Number of URLs in sitemap.
     */
    public function export(array $usersData, array $categories, array $topicSlugs): int
    {
        $baseUrl = $this->config->baseUrl;
        $publicDir = $this->config->publicDir();
        $prefix = $this->db->prefix();

        $urls = [];

        // Main index
        $urls[] = ['loc' => $baseUrl . 'index.html', 'priority' => '1.0', 'changefreq' => 'weekly'];

        // Users index
        $urls[] = ['loc' => $baseUrl . 'users/index.html', 'priority' => '0.6', 'changefreq' => 'monthly'];

        // User profiles
        foreach ($usersData as $user) {
            $userSlug = $user['slug'] ?? 'user-' . $user['id'];
            $urls[] = [
                'loc' => $baseUrl . 'users/' . $userSlug . '.html',
                'priority' => '0.4',
                'changefreq' => 'monthly',
            ];
        }

        // Public forums and topics
        foreach ($categories as $category) {
            foreach ($category['forums'] as $forum) {
                $forumId = $forum['id'];

                $row = $this->db->fetchOne("SELECT COUNT(*) as cnt FROM {$prefix}topics WHERE forum_id = {$forumId}");
                $topicCount = $row ? (int)$row['cnt'] : 0;
                $forumTotalPages = max(1, (int)ceil($topicCount / self::TOPICS_PER_PAGE));

                for ($page = 1; $page <= $forumTotalPages; $page++) {
                    $urls[] = [
                        'loc' => $baseUrl . 'forums/forum_' . $forumId . '_p' . $page . '.html',
                        'priority' => '0.7',
                        'changefreq' => 'weekly',
                    ];
                }

                $topicRows = $this->db->fetchAll("SELECT id, num_replies FROM {$prefix}topics WHERE forum_id = {$forumId} AND moved_to IS NULL");
                foreach ($topicRows as $topic) {
                    $postCount = (int)$topic['num_replies'] + 1;
                    $topicTotalPages = max(1, (int)ceil($postCount / self::POSTS_PER_PAGE));
                    $topicSlug = $topicSlugs[(int)$topic['id']] ?? 'topic_' . $topic['id'];

                    for ($page = 1; $page <= $topicTotalPages; $page++) {
                        $urls[] = [
                            'loc' => $baseUrl . 'topics/' . $topicSlug . '_p' . $page . '.html',
                            'priority' => '0.5',
                            'changefreq' => 'monthly',
                        ];
                    }
                }
            }
        }

        // Generate XML
        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

        foreach ($urls as $url) {
            $xml .= '  <url>' . "\n";
            $xml .= '    <loc>' . htmlspecialchars($url['loc']) . '</loc>' . "\n";
            if (isset($url['priority'])) {
                $xml .= '    <priority>' . $url['priority'] . '</priority>' . "\n";
            }
            if (isset($url['changefreq'])) {
                $xml .= '    <changefreq>' . $url['changefreq'] . '</changefreq>' . "\n";
            }
            $xml .= '  </url>' . "\n";
        }

        $xml .= '</urlset>' . "\n";

        @file_put_contents($publicDir . 'sitemap.xml', $xml);

        return count($urls);
    }
}
