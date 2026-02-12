<?php

declare(strict_types=1);

namespace FluxbbArchiver\Tests\Unit;

use FluxbbArchiver\Config;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class ConfigTest extends TestCase
{
    public function testFromArgvWithRequiredOptions(): void
    {
        $argv = [
            'fluxbb-archiver',
            '--host=localhost',
            '--user=root',
            '--password=secret',
            '--database=fluxbb',
        ];

        $config = Config::fromArgv($argv);

        $this->assertSame('localhost', $config->host);
        $this->assertSame('root', $config->user);
        $this->assertSame('secret', $config->password);
        $this->assertSame('fluxbb', $config->database);
    }

    public function testFromArgvWithAllOptions(): void
    {
        $argv = [
            'fluxbb-archiver',
            '--host=db.example.com',
            '--port=3307',
            '--user=admin',
            '--password=pass123',
            '--database=forum_db',
            '--prefix=forum_',
            '--output=/var/export',
            '--lang=en',
            '--base-url=https://forum.example.com/',
            '--source-dir=/var/www/forum',
            '--local-fetch-base=http://localhost:8080/',
            '--original-url-base=http://old.example.com/',
            '--template=custom',
            '--force-public-categories=General,News',
        ];

        $config = Config::fromArgv($argv);

        $this->assertSame('db.example.com', $config->host);
        $this->assertSame(3307, $config->port);
        $this->assertSame('admin', $config->user);
        $this->assertSame('pass123', $config->password);
        $this->assertSame('forum_db', $config->database);
        $this->assertSame('forum_', $config->prefix);
        $this->assertSame('/var/export/', $config->outputDir);
        $this->assertSame('en', $config->lang);
        $this->assertSame('https://forum.example.com/', $config->baseUrl);
        $this->assertSame('/var/www/forum', $config->sourceDir);
        $this->assertSame('http://localhost:8080/', $config->localFetchBase);
        $this->assertSame('http://old.example.com/', $config->originalUrlBase);
        $this->assertSame('custom', $config->template);
        $this->assertSame(['General', 'News'], $config->forcePublicCategories);
    }

    public function testFromArgvWithDefaults(): void
    {
        $argv = [
            'fluxbb-archiver',
            '--host=localhost',
            '--user=root',
            '--password=secret',
            '--database=fluxbb',
        ];

        $config = Config::fromArgv($argv);

        $this->assertSame(3306, $config->port);
        $this->assertSame('fluxbb_', $config->prefix);
        $this->assertSame('./export/', $config->outputDir);
        $this->assertSame('fi', $config->lang);
        $this->assertSame('https://example.com/', $config->baseUrl);
        $this->assertSame('', $config->sourceDir);
        $this->assertSame('', $config->localFetchBase);
        $this->assertSame('', $config->originalUrlBase);
        $this->assertTrue($config->obfuscateEmails);
        $this->assertSame('default', $config->template);
        $this->assertSame([], $config->forcePublicCategories);
    }

    public function testFromArgvWithNoObfuscateEmails(): void
    {
        $argv = [
            'fluxbb-archiver',
            '--host=localhost',
            '--user=root',
            '--password=secret',
            '--database=fluxbb',
            '--no-obfuscate-emails',
        ];

        $config = Config::fromArgv($argv);

        $this->assertFalse($config->obfuscateEmails);
    }

    public function testFromArgvMissingRequiredOptionsThrowsException(): void
    {
        $argv = [
            'fluxbb-archiver',
            '--host=localhost',
        ];

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Missing required options: --user, --password, --database');

        Config::fromArgv($argv);
    }

    public function testFromArgvMissingAllRequiredOptionsThrowsException(): void
    {
        $argv = ['fluxbb-archiver'];

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Missing required options: --host, --user, --password, --database');

        Config::fromArgv($argv);
    }

    public function testFromArgvEmptyValueThrowsException(): void
    {
        $argv = [
            'fluxbb-archiver',
            '--host',
            '--user=root',
            '--password=secret',
            '--database=fluxbb',
        ];

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Missing required options: --host');

        Config::fromArgv($argv);
    }

    public function testPublicDir(): void
    {
        $config = $this->createMinimalConfig('/var/export');
        $this->assertSame('/var/export/public/', $config->publicDir());
    }

    public function testPrivateDir(): void
    {
        $config = $this->createMinimalConfig('/var/export');
        $this->assertSame('/var/export/private/', $config->privateDir());
    }

    public function testOutputDirNormalization(): void
    {
        $config = $this->createMinimalConfig('/var/export/');
        $this->assertSame('/var/export/', $config->outputDir);

        $config2 = $this->createMinimalConfig('/var/export');
        $this->assertSame('/var/export/', $config2->outputDir);
    }

    public function testBaseUrlNormalization(): void
    {
        $argv = [
            'fluxbb-archiver',
            '--host=localhost',
            '--user=root',
            '--password=secret',
            '--database=fluxbb',
            '--base-url=https://example.com',
        ];

        $config = Config::fromArgv($argv);
        $this->assertSame('https://example.com/', $config->baseUrl);
    }

    public function testForcePublicCategoriesParsing(): void
    {
        $argv = [
            'fluxbb-archiver',
            '--host=localhost',
            '--user=root',
            '--password=secret',
            '--database=fluxbb',
            '--force-public-categories=Cat1, Cat2 , Cat3',
        ];

        $config = Config::fromArgv($argv);
        $this->assertSame(['Cat1', 'Cat2', 'Cat3'], $config->forcePublicCategories);
    }

    public function testForcePublicCategoriesEmptyFiltered(): void
    {
        $argv = [
            'fluxbb-archiver',
            '--host=localhost',
            '--user=root',
            '--password=secret',
            '--database=fluxbb',
            '--force-public-categories=Cat1,,Cat2',
        ];

        $config = Config::fromArgv($argv);
        $this->assertSame(['Cat1', 'Cat2'], $config->forcePublicCategories);
    }

    public function testUsageTextContainsRequiredOptions(): void
    {
        $usage = Config::usageText();

        $this->assertStringContainsString('--host=HOST', $usage);
        $this->assertStringContainsString('--user=USER', $usage);
        $this->assertStringContainsString('--password=PASS', $usage);
        $this->assertStringContainsString('--database=DB', $usage);
    }

    public function testUsageTextContainsOptionalOptions(): void
    {
        $usage = Config::usageText();

        $this->assertStringContainsString('--port=3306', $usage);
        $this->assertStringContainsString('--prefix=fluxbb_', $usage);
        $this->assertStringContainsString('--output=./export', $usage);
        $this->assertStringContainsString('--lang=fi', $usage);
        $this->assertStringContainsString('--template=default', $usage);
        $this->assertStringContainsString('--no-obfuscate-emails', $usage);
    }

    private function createMinimalConfig(string $outputDir = './export'): Config
    {
        return new Config(
            'localhost',
            3306,
            'root',
            'password',
            'fluxbb',
            'fluxbb_',
            $outputDir,
            'fi',
            'https://example.com/',
            '',
            '',
            '',
            true,
            'default',
            []
        );
    }
}
