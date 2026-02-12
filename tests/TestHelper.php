<?php

declare(strict_types=1);

namespace FluxbbArchiver\Tests;

class TestHelper
{
    /**
     * Normalize timestamps in HTML/JSON content for comparison.
     *
     * Replaces:
     * - ISO 8601 dates (2026-02-12T19:01:14+00:00)
     * - Generated at timestamps (2026-02-12 19:01:14 UTC)
     * - Finnish date format (12.2.2026 19:01:14 UTC)
     */
    public static function normalizeTimestamps(string $content): string
    {
        // ISO 8601 format: 2026-02-12T19:01:14+00:00
        $content = preg_replace(
            '/\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}[+-]\d{2}:\d{2}/',
            '{{TIMESTAMP_ISO}}',
            $content
        );

        // English format: 2026-02-12 19:01:14 UTC
        $content = preg_replace(
            '/\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2} [A-Z]{2,4}/',
            '{{TIMESTAMP_EN}}',
            $content
        );

        // Finnish format: 12.2.2026 19:01:14 UTC
        $content = preg_replace(
            '/\d{1,2}\.\d{1,2}\.\d{4} \d{2}:\d{2}:\d{2} [A-Z]{2,4}/',
            '{{TIMESTAMP_FI}}',
            $content
        );

        // Date only formats
        $content = preg_replace(
            '/\d{4}-\d{2}-\d{2}/',
            '{{DATE}}',
            $content
        );

        return $content;
    }

    /**
     * Compare two files with normalized timestamps.
     */
    public static function filesMatchNormalized(string $expected, string $actual): bool
    {
        $expectedContent = self::normalizeTimestamps(file_get_contents($expected));
        $actualContent = self::normalizeTimestamps(file_get_contents($actual));

        return $expectedContent === $actualContent;
    }

    /**
     * Get diff between two strings (for debugging test failures).
     */
    public static function getDiff(string $expected, string $actual): string
    {
        $expectedLines = explode("\n", $expected);
        $actualLines = explode("\n", $actual);

        $diff = [];
        $maxLines = max(count($expectedLines), count($actualLines));

        for ($i = 0; $i < $maxLines; $i++) {
            $exp = $expectedLines[$i] ?? '(missing)';
            $act = $actualLines[$i] ?? '(missing)';

            if ($exp !== $act) {
                $diff[] = "Line " . ($i + 1) . ":";
                $diff[] = "  Expected: " . substr($exp, 0, 100);
                $diff[] = "  Actual:   " . substr($act, 0, 100);
            }
        }

        return implode("\n", array_slice($diff, 0, 30)); // Limit output
    }

    /**
     * Create a temporary directory for test output.
     */
    public static function createTempDir(): string
    {
        $dir = sys_get_temp_dir() . '/fluxbb-test-' . uniqid();
        mkdir($dir, 0777, true);
        return $dir;
    }

    /**
     * Recursively delete a directory.
     */
    public static function deleteDir(string $dir): void
    {
        if (!is_dir($dir)) {
            return;
        }

        $files = array_diff(scandir($dir), ['.', '..']);
        foreach ($files as $file) {
            $path = $dir . '/' . $file;
            is_dir($path) ? self::deleteDir($path) : unlink($path);
        }
        rmdir($dir);
    }

    /**
     * Get path to test fixtures directory.
     */
    public static function fixturesPath(string $subpath = ''): string
    {
        $base = dirname(__DIR__) . '/tests/fixtures';
        return $subpath ? $base . '/' . ltrim($subpath, '/') : $base;
    }
}
