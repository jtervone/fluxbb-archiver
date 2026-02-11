<?php

declare(strict_types=1);

namespace FluxbbArchiver;

use FluxbbArchiver\Console\CliOutput;
use FluxbbArchiver\Content\AssetCollector;
use FluxbbArchiver\Content\BbcodeParser;
use FluxbbArchiver\Content\SlugGenerator;
use FluxbbArchiver\Export\ForumExporter;
use FluxbbArchiver\Export\MessageExporter;
use FluxbbArchiver\Export\SitemapExporter;
use FluxbbArchiver\Export\UserExporter;
use FluxbbArchiver\Html\TemplateEngine;
use FluxbbArchiver\I18n\Translator;

class Application
{
    private Config $config;
    private CliOutput $output;

    public function __construct(Config $config)
    {
        $this->config = $config;
        $this->output = new CliOutput();
    }

    public function run(): int
    {
        $out = $this->output;
        $config = $this->config;

        $out->heading('FluxBB Archiver - Static HTML Export');

        // Connect to database
        try {
            $db = new Database($config->host, $config->port, $config->user, $config->password, $config->database, $config->prefix);
        } catch (\RuntimeException $e) {
            $out->error($e->getMessage());
            return 1;
        }
        $out->info('Connected to database.');

        // Load board config
        $boardTitle = 'Forum Archive';
        $rows = $db->fetchAll("SELECT conf_name, conf_value FROM {$config->prefix}config");
        foreach ($rows as $row) {
            if ($row['conf_name'] === 'o_board_title') {
                $boardTitle = $row['conf_value'];
            }
        }
        $out->info('Board title: ' . $boardTitle);

        // Resolve template directories
        $projectRoot = $this->resolveProjectRoot();
        $defaultDir = $projectRoot . 'tpl/default/';
        $templateDir = $projectRoot . 'tpl/' . $config->template . '/';

        if (!is_dir($defaultDir)) {
            $out->error('Default template directory not found: ' . $defaultDir);
            return 1;
        }
        if ($config->template !== 'default' && !is_dir($templateDir)) {
            $out->error('Template directory not found: ' . $templateDir);
            return 1;
        }

        $engine = new TemplateEngine($templateDir, $defaultDir);
        $out->info('Template: ' . $config->template);

        // Initialize components
        $translator = new Translator($config->lang);

        // Load template translation overrides
        $langDir = $engine->getLangDir();
        if ($langDir !== null) {
            $langFile = $langDir . $config->lang . '.php';
            if (file_exists($langFile)) {
                $overrides = require $langFile;
                if (is_array($overrides)) {
                    $translator->mergeOverrides($overrides, $config->lang);
                    $out->info('Loaded template translations for: ' . $config->lang);
                }
            }
            // Also load English overrides if using a non-English language
            if ($config->lang !== 'en') {
                $enFile = $langDir . 'en.php';
                if (file_exists($enFile)) {
                    $enOverrides = require $enFile;
                    if (is_array($enOverrides)) {
                        $translator->mergeOverrides($enOverrides, 'en');
                    }
                }
            }
        }

        $assets = new AssetCollector($config->publicDir(), $config->sourceDir, $config->localFetchBase, $config->originalUrlBase);
        $bbcode = new BbcodeParser($translator, $assets, $config->obfuscateEmails);
        $slugGenerator = new SlugGenerator();

        // Prepare directories
        $this->prepareDirectories();

        // Copy static assets
        $out->blank();
        $out->info('Copying static assets...');
        $assetsCopied = $assets->copyStaticAssets();
        $out->info("Copied {$assetsCopied} static files (avatars, smilies, etc.).");

        // Export CSS from template
        $out->blank();
        $out->info('Exporting CSS...');
        $cssSource = $engine->getStylesheetPath();
        $css = file_get_contents($cssSource);
        @file_put_contents($config->publicDir() . 'css/style.css', $css);
        @file_put_contents($config->privateDir() . 'css/style.css', $css);
        $out->info('CSS exported.');

        // Export users
        $out->blank();
        $out->info('Exporting users (public profiles)...');
        $userExporter = new UserExporter($db, $config, $translator, $bbcode, $engine, $boardTitle);
        $userCount = $userExporter->exportPublic();
        $out->info("Exported {$userCount} users.");

        // Load forum structure
        $out->blank();
        $out->info('Identifying private forums...');
        $forumExporter = new ForumExporter($db, $config, $translator, $bbcode, $engine, $userExporter, $boardTitle);
        [$publicForums, $privateForums] = $forumExporter->loadStructure();
        $out->info("Found " . count($forumExporter->categories()) . " public categories with {$publicForums} forums.");
        $out->info("Found " . count($forumExporter->categoriesPrivate()) . " private categories with {$privateForums} forums.");

        // Build topic slugs
        $out->blank();
        $out->info('Building topic URL slugs...');
        $topicRows = $db->fetchAll("SELECT id, subject FROM {$config->prefix}topics WHERE moved_to IS NULL");
        $topicSlugs = $slugGenerator->buildTopicSlugs($topicRows);
        $forumExporter->setTopicSlugs($topicSlugs);
        $out->info('Generated ' . count($topicSlugs) . ' topic slugs.');

        // Export topics and posts
        $out->blank();
        $out->info('Exporting topics and posts...');
        $forumExporter->exportTopicsAndPosts();
        $out->info("Exported {$forumExporter->totalTopics()} topics and {$forumExporter->totalPosts()} posts.");

        // Generate main index
        $out->blank();
        $out->info('Generating main index...');
        $forumExporter->exportMainIndex();
        $out->info('Main index generated.');

        // Generate private forums index
        if (!empty($forumExporter->categoriesPrivate())) {
            $out->blank();
            $out->info('Generating private forums index...');
            $forumExporter->exportPrivateIndex();
            $out->info('Private forums index generated.');
        }

        // Export private messages
        $out->blank();
        $out->info('Exporting private messages to protected directory...');
        $messageExporter = new MessageExporter(
            $db, $config, $translator, $bbcode, $engine, $userExporter, $boardTitle,
            !empty($forumExporter->categoriesPrivate())
        );
        $pmCount = $messageExporter->export();
        if ($pmCount > 0) {
            $out->info("Exported {$pmCount} private message conversations.");
        } else {
            $out->info('Private messaging tables not found, skipping.');
        }

        // Export sensitive user data
        $out->blank();
        $out->info('Exporting sensitive user data...');
        $privateUserCount = $userExporter->exportPrivate();
        $out->info("Exported {$privateUserCount} users with sensitive data.");

        // Generate sitemap
        $out->blank();
        $out->info('Generating sitemap.xml...');
        $sitemapExporter = new SitemapExporter($db, $config);
        $sitemapCount = $sitemapExporter->export(
            $userExporter->usersData(),
            $forumExporter->categories(),
            $topicSlugs
        );
        $out->info("Sitemap generated with {$sitemapCount} URLs.");

        // Summary
        $out->blank();
        $out->heading('Export Complete!');
        $out->blank();
        $publicPath = realpath($config->publicDir()) ?: $config->publicDir();
        $privatePath = realpath($config->privateDir()) ?: $config->privateDir();
        $out->info("Public archive: {$publicPath}");
        $out->info("Private archive: {$privatePath}");
        $out->blank();
        $out->info('Statistics:');
        $out->info('  - Users: ' . count($userExporter->usersData()));
        $out->info('  - Public categories: ' . count($forumExporter->categories()));
        $out->info("  - Public forums: {$publicForums}");
        $out->info('  - Private categories: ' . count($forumExporter->categoriesPrivate()));
        $out->info("  - Private forums: {$privateForums}");
        $out->info('  - Total topics: ' . $forumExporter->totalTopics());
        $out->info('  - Total posts: ' . $forumExporter->totalPosts());
        $out->info("  - Static assets copied: {$assetsCopied}");
        $out->info('  - Images/files processed: ' . $assets->downloadCount());
        $out->blank();
        $out->info('SECURITY NOTE:');
        $out->info('  - Passwords and password hashes were NOT exported');
        $out->info("  - Private forums are in the separate 'private' directory");
        $out->info("  - Private messages are in the separate 'private' directory");
        $out->info("  - Ensure the 'private' directory has appropriate access controls");

        $db->close();

        return 0;
    }

    private function resolveProjectRoot(): string
    {
        // Find project root by looking for tpl/default/ relative to this file
        // Structure: src/FluxbbArchiver/Application.php -> project root is ../../
        $dir = dirname(__DIR__, 2) . '/';
        if (is_dir($dir . 'tpl/default/')) {
            return $dir;
        }
        // Fallback: check from vendor path (installed as dependency)
        $vendorDir = dirname(__DIR__, 4) . '/';
        if (is_dir($vendorDir . 'tpl/default/')) {
            return $vendorDir;
        }
        return $dir;
    }

    private function prepareDirectories(): void
    {
        $config = $this->config;
        $out = $this->output;

        $out->blank();
        $out->info('Cleaning existing export directories...');
        if (is_dir($config->outputDir)) {
            $this->recursiveDelete($config->outputDir);
            $out->info('Removed old export files.');
        } else {
            $out->info('No existing export to clean.');
        }

        $directories = [
            $config->outputDir,
            $config->publicDir(),
            $config->publicDir() . 'forums/',
            $config->publicDir() . 'topics/',
            $config->publicDir() . 'users/',
            $config->publicDir() . 'css/',
            $config->publicDir() . 'img/',
            $config->publicDir() . 'json/',
            $config->publicDir() . 'json/forums/',
            $config->publicDir() . 'json/topics/',
            $config->publicDir() . 'json/users/',
            $config->privateDir(),
            $config->privateDir() . 'messages/',
            $config->privateDir() . 'users/',
            $config->privateDir() . 'css/',
            $config->privateDir() . 'json/',
            $config->privateDir() . 'json/messages/',
            $config->privateDir() . 'json/users/',
            $config->privateDir() . 'forums/',
            $config->privateDir() . 'topics/',
            $config->privateDir() . 'json/forums/',
            $config->privateDir() . 'json/topics/',
        ];

        foreach ($directories as $dir) {
            if (!is_dir($dir)) {
                @mkdir($dir, 0777, true);
            }
        }

        $out->info('Created export directories.');
    }

    private function recursiveDelete(string $dir): void
    {
        if (!is_dir($dir)) {
            return;
        }
        $files = array_diff(scandir($dir), ['.', '..']);
        foreach ($files as $file) {
            $path = $dir . '/' . $file;
            if (is_dir($path)) {
                $this->recursiveDelete($path);
            } else {
                @unlink($path);
            }
        }
        @rmdir($dir);
    }
}
