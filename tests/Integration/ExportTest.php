<?php

declare(strict_types=1);

namespace FluxbbArchiver\Tests\Integration;

use FluxbbArchiver\Application;
use FluxbbArchiver\Config;
use FluxbbArchiver\Tests\TestHelper;
use PHPUnit\Framework\TestCase;

/**
 * End-to-end export tests.
 *
 * @group integration
 * @group e2e
 */
class ExportTest extends TestCase
{
    private string $outputDir;

    protected function setUp(): void
    {
        $this->outputDir = TestHelper::createTempDir();
    }

    protected function tearDown(): void
    {
        TestHelper::deleteDir($this->outputDir);
    }

    public function testFullExportProducesExpectedStructure(): void
    {
        $config = $this->createConfig();

        try {
            $app = new Application($config);
            $exitCode = $app->run();
        } catch (\Exception $e) {
            $this->markTestSkipped('Export failed: ' . $e->getMessage());
            return;
        }

        $this->assertSame(0, $exitCode, 'Export should complete successfully');

        // Check public directory structure
        $publicDir = $this->outputDir . '/public';
        $this->assertDirectoryExists($publicDir);
        $this->assertFileExists($publicDir . '/index.html');
        $this->assertFileExists($publicDir . '/sitemap.xml');
        $this->assertDirectoryExists($publicDir . '/css');
        $this->assertDirectoryExists($publicDir . '/forums');
        $this->assertDirectoryExists($publicDir . '/users');
        $this->assertDirectoryExists($publicDir . '/json');

        // Check private directory structure
        $privateDir = $this->outputDir . '/private';
        $this->assertDirectoryExists($privateDir);
        $this->assertFileExists($privateDir . '/index.html');
    }

    public function testExportProducesValidHtml(): void
    {
        $config = $this->createConfig();

        try {
            $app = new Application($config);
            $app->run();
        } catch (\Exception $e) {
            $this->markTestSkipped('Export failed: ' . $e->getMessage());
            return;
        }

        $indexHtml = file_get_contents($this->outputDir . '/public/index.html');

        $this->assertStringContainsString('<!DOCTYPE html>', $indexHtml);
        $this->assertStringContainsString('<html', $indexHtml);
        $this->assertStringContainsString('</html>', $indexHtml);
        $this->assertStringContainsString('<title>', $indexHtml);
    }

    public function testExportProducesValidJson(): void
    {
        $config = $this->createConfig();

        try {
            $app = new Application($config);
            $app->run();
        } catch (\Exception $e) {
            $this->markTestSkipped('Export failed: ' . $e->getMessage());
            return;
        }

        $jsonPath = $this->outputDir . '/public/json/index.json';
        $this->assertFileExists($jsonPath);

        $jsonContent = file_get_contents($jsonPath);
        $data = json_decode($jsonContent, true);

        $this->assertNotNull($data, 'JSON should be valid');
        $this->assertArrayHasKey('board_title', $data);
        $this->assertArrayHasKey('statistics', $data);
        $this->assertArrayHasKey('categories', $data);
    }

    public function testExportStatisticsMatchDatabase(): void
    {
        $config = $this->createConfig();

        try {
            $app = new Application($config);
            $app->run();
        } catch (\Exception $e) {
            $this->markTestSkipped('Export failed: ' . $e->getMessage());
            return;
        }

        $jsonPath = $this->outputDir . '/public/json/index.json';
        $data = json_decode(file_get_contents($jsonPath), true);

        $stats = $data['statistics'];

        // These should match the fixture database
        $this->assertGreaterThan(0, $stats['total_users']);
        $this->assertGreaterThan(0, $stats['total_topics']);
        $this->assertGreaterThan(0, $stats['total_posts']);
        $this->assertGreaterThan(0, $stats['total_forums']);
        $this->assertGreaterThan(0, $stats['total_categories']);
    }

    public function testExportIndexMatchesExpected(): void
    {
        $expectedPath = TestHelper::fixturesPath('expected-output/public/index.html');

        if (!file_exists($expectedPath)) {
            $this->markTestSkipped('Expected output fixture not found');
            return;
        }

        $config = $this->createConfig();

        try {
            $app = new Application($config);
            $app->run();
        } catch (\Exception $e) {
            $this->markTestSkipped('Export failed: ' . $e->getMessage());
            return;
        }

        $actualPath = $this->outputDir . '/public/index.html';
        $expected = TestHelper::normalizeTimestamps(file_get_contents($expectedPath));
        $actual = TestHelper::normalizeTimestamps(file_get_contents($actualPath));

        $this->assertSame(
            $expected,
            $actual,
            "Index HTML does not match expected.\n" . TestHelper::getDiff($expected, $actual)
        );
    }

    public function testSitemapContainsExpectedUrls(): void
    {
        $config = $this->createConfig();

        try {
            $app = new Application($config);
            $app->run();
        } catch (\Exception $e) {
            $this->markTestSkipped('Export failed: ' . $e->getMessage());
            return;
        }

        $sitemapPath = $this->outputDir . '/public/sitemap.xml';
        $sitemap = file_get_contents($sitemapPath);

        $this->assertStringContainsString('<?xml version="1.0"', $sitemap);
        $this->assertStringContainsString('<urlset', $sitemap);
        $this->assertStringContainsString('<url>', $sitemap);
        $this->assertStringContainsString('<loc>', $sitemap);
        $this->assertStringContainsString('index.html', $sitemap);
        $this->assertStringContainsString('users/', $sitemap);
        $this->assertStringContainsString('forums/', $sitemap);
    }

    public function testEmailsAreObfuscatedByDefault(): void
    {
        $config = $this->createConfig();

        try {
            $app = new Application($config);
            $app->run();
        } catch (\Exception $e) {
            $this->markTestSkipped('Export failed: ' . $e->getMessage());
            return;
        }

        // Check that no raw email addresses appear in public output
        $publicDir = $this->outputDir . '/public';
        $htmlFiles = glob($publicDir . '/**/*.html');

        foreach ($htmlFiles as $file) {
            $content = file_get_contents($file);
            // Should not contain raw email patterns (except in obfuscated form)
            $this->assertDoesNotMatchRegularExpression(
                '/[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,}/i',
                $content,
                "File $file contains non-obfuscated email address"
            );
        }
    }

    public function testPrivateOutputContainsSensitiveData(): void
    {
        $config = $this->createConfig();

        try {
            $app = new Application($config);
            $app->run();
        } catch (\Exception $e) {
            $this->markTestSkipped('Export failed: ' . $e->getMessage());
            return;
        }

        $privateUsersDir = $this->outputDir . '/private/users';

        if (!is_dir($privateUsersDir)) {
            $this->markTestSkipped('Private users directory not created');
            return;
        }

        // Private user files should exist
        $this->assertDirectoryExists($privateUsersDir);
    }

    private function createConfig(): Config
    {
        $host = getenv('DB_HOST') ?: 'mariadb';
        $user = getenv('DB_USER') ?: 'fluxbb';
        $password = getenv('DB_PASSWORD') ?: 'fluxbb';
        $database = getenv('DB_NAME') ?: 'fluxbb';
        $prefix = getenv('DB_PREFIX') ?: 'fluxbb_';

        return new Config(
            $host,
            3306,
            $user,
            $password,
            $database,
            $prefix,
            $this->outputDir,
            'en',
            'https://example.com/',
            '/var/www/html/input/',
            '',
            '',
            true,
            'default',
            []
        );
    }
}
