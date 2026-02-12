<?php

declare(strict_types=1);

namespace FluxbbArchiver\Tests\Unit;

use FluxbbArchiver\Content\AssetCollector;
use PHPUnit\Framework\TestCase;

class AssetCollectorTest extends TestCase
{
    private string $testDir;
    private string $publicDir;
    private string $sourceDir;

    protected function setUp(): void
    {
        $this->testDir = sys_get_temp_dir() . '/asset-test-' . uniqid();
        $this->publicDir = $this->testDir . '/public/';
        $this->sourceDir = $this->testDir . '/source/';

        mkdir($this->publicDir, 0777, true);
        mkdir($this->sourceDir . 'img/avatars', 0777, true);
        mkdir($this->sourceDir . 'img/smilies', 0777, true);
    }

    protected function tearDown(): void
    {
        $this->recursiveDelete($this->testDir);
    }

    private function recursiveDelete(string $dir): void
    {
        if (!is_dir($dir)) {
            return;
        }
        $files = array_diff(scandir($dir), ['.', '..']);
        foreach ($files as $file) {
            $path = $dir . '/' . $file;
            is_dir($path) ? $this->recursiveDelete($path) : unlink($path);
        }
        rmdir($dir);
    }

    public function testDownloadCountInitiallyZero(): void
    {
        $collector = new AssetCollector($this->publicDir, $this->sourceDir);
        $this->assertSame(0, $collector->downloadCount());
    }

    public function testFetchFileReturnsOriginalUrlForExternalUrls(): void
    {
        $collector = new AssetCollector($this->publicDir, $this->sourceDir);
        $url = 'https://external.example.com/image.jpg';

        $result = $collector->fetchFile($url);

        $this->assertSame($url, $result);
        $this->assertSame(0, $collector->downloadCount());
    }

    public function testFetchFileCopiesLocalFile(): void
    {
        // Create a test image in source
        $testImage = $this->sourceDir . 'img/avatars/test.jpg';
        file_put_contents($testImage, 'fake image content');

        $collector = new AssetCollector(
            $this->publicDir,
            $this->sourceDir,
            'http://localhost:8080/forum/',
            'http://example.com/forum/'
        );

        $url = 'http://example.com/forum/img/avatars/test.jpg';
        $result = $collector->fetchFile($url);

        $this->assertSame('img/avatars/test.jpg', $result);
        $this->assertSame(1, $collector->downloadCount());
        $this->assertFileExists($this->publicDir . 'img/avatars/test.jpg');
    }

    public function testFetchFileCachesResults(): void
    {
        $testImage = $this->sourceDir . 'img/test.jpg';
        @mkdir(dirname($testImage), 0777, true);
        file_put_contents($testImage, 'content');

        $collector = new AssetCollector(
            $this->publicDir,
            $this->sourceDir,
            'http://localhost:8080/forum/',
            'http://example.com/forum/'
        );

        $url = 'http://example.com/forum/img/test.jpg';

        // First call
        $result1 = $collector->fetchFile($url);
        $this->assertSame(1, $collector->downloadCount());

        // Second call should use cache
        $result2 = $collector->fetchFile($url);
        $this->assertSame(1, $collector->downloadCount());
        $this->assertSame($result1, $result2);
    }

    public function testFetchFileHandlesUrlWithQueryString(): void
    {
        $testImage = $this->sourceDir . 'img/avatars/user.jpg';
        file_put_contents($testImage, 'avatar content');

        $collector = new AssetCollector(
            $this->publicDir,
            $this->sourceDir,
            'http://localhost:8080/forum/',
            'http://example.com/forum/'
        );

        $url = 'http://example.com/forum/img/avatars/user.jpg?v=123';
        $result = $collector->fetchFile($url);

        $this->assertSame('img/avatars/user.jpg', $result);
    }

    public function testFetchFileCategorizesSmilies(): void
    {
        $testSmilie = $this->sourceDir . 'img/smilies/smile.gif';
        file_put_contents($testSmilie, 'smilie content');

        $collector = new AssetCollector(
            $this->publicDir,
            $this->sourceDir,
            'http://localhost:8080/forum/',
            'http://example.com/forum/'
        );

        $url = 'http://example.com/forum/img/smilies/smile.gif';
        $result = $collector->fetchFile($url);

        $this->assertSame('img/smilies/smile.gif', $result);
        $this->assertFileExists($this->publicDir . 'img/smilies/smile.gif');
    }

    public function testCopyStaticAssetsCopiesAvatarsAndSmilies(): void
    {
        // Create test files
        file_put_contents($this->sourceDir . 'img/avatars/user1.jpg', 'avatar1');
        file_put_contents($this->sourceDir . 'img/avatars/user2.png', 'avatar2');
        file_put_contents($this->sourceDir . 'img/smilies/smile.gif', 'smilie');

        $collector = new AssetCollector($this->publicDir, $this->sourceDir);
        $copied = $collector->copyStaticAssets();

        $this->assertSame(3, $copied);
        $this->assertFileExists($this->publicDir . 'img/avatars/user1.jpg');
        $this->assertFileExists($this->publicDir . 'img/avatars/user2.png');
        $this->assertFileExists($this->publicDir . 'img/smilies/smile.gif');
    }

    public function testCopyStaticAssetsHandlesMissingSourceDirs(): void
    {
        // Remove source dirs
        rmdir($this->sourceDir . 'img/smilies');
        rmdir($this->sourceDir . 'img/avatars');
        rmdir($this->sourceDir . 'img');

        $collector = new AssetCollector($this->publicDir, $this->sourceDir);
        $copied = $collector->copyStaticAssets();

        $this->assertSame(0, $copied);
    }

    public function testProcessUrlsReplacesMatchingUrls(): void
    {
        $testImage = $this->sourceDir . 'img/test.png';
        @mkdir(dirname($testImage), 0777, true);
        file_put_contents($testImage, 'content');

        $collector = new AssetCollector(
            $this->publicDir,
            $this->sourceDir,
            'http://localhost:8080/forum/',
            'http://forum.example.com/'
        );

        $text = 'Check this image: http://forum.example.com/img/test.png in the post.';
        $result = $collector->processUrls($text);

        $this->assertStringContainsString('img/test.png', $result);
        $this->assertStringNotContainsString('forum.example.com', $result);
    }

    public function testProcessUrlsPreservesNonMatchingUrls(): void
    {
        $collector = new AssetCollector($this->publicDir, $this->sourceDir);

        $text = 'External image: https://other-site.com/image.jpg';
        $result = $collector->processUrls($text);

        $this->assertSame($text, $result);
    }

    public function testFetchFileCreatesTargetDirectory(): void
    {
        $testImage = $this->sourceDir . 'img/new/deep/path/image.jpg';
        mkdir(dirname($testImage), 0777, true);
        file_put_contents($testImage, 'content');

        $collector = new AssetCollector(
            $this->publicDir,
            $this->sourceDir,
            'http://localhost:8080/forum/',
            ''
        );

        $url = 'http://localhost:8080/forum/img/new/deep/path/image.jpg';
        $result = $collector->fetchFile($url);

        // Should categorize as img/ since it's an image file
        $this->assertStringStartsWith('img/', $result);
    }

    public function testFetchFileCategorizesNonImageAsFiles(): void
    {
        $testFile = $this->sourceDir . 'uploads/document.pdf';
        @mkdir(dirname($testFile), 0777, true);
        file_put_contents($testFile, 'pdf content');

        $collector = new AssetCollector(
            $this->publicDir,
            $this->sourceDir,
            'http://localhost:8080/forum/',
            'http://example.com/forum/'
        );

        $url = 'http://example.com/forum/uploads/document.pdf';
        $result = $collector->fetchFile($url);

        $this->assertStringStartsWith('files/', $result);
        $this->assertFileExists($this->publicDir . 'files/document.pdf');
    }

    public function testFetchFileUsesLocalFetchBaseDirectly(): void
    {
        $testImage = $this->sourceDir . 'img/direct.png';
        @mkdir(dirname($testImage), 0777, true);
        file_put_contents($testImage, 'content');

        $collector = new AssetCollector(
            $this->publicDir,
            $this->sourceDir,
            'http://localhost:8080/forum/',
            'http://example.com/forum/'
        );

        // URL already uses local fetch base
        $url = 'http://localhost:8080/forum/img/direct.png';
        $result = $collector->fetchFile($url);

        $this->assertSame('img/direct.png', $result);
    }

    public function testFetchFileReturnsOriginalUrlWhenLocalFileMissing(): void
    {
        $collector = new AssetCollector(
            $this->publicDir,
            $this->sourceDir,
            'http://localhost:8080/forum/',
            'http://example.com/forum/'
        );

        // File doesn't exist locally and HTTP fetch will fail
        $url = 'http://example.com/forum/img/nonexistent.jpg';
        $result = $collector->fetchFile($url);

        // Should return original URL when download fails
        $this->assertSame($url, $result);
    }

    public function testFetchFileWithDifferentImageExtensions(): void
    {
        $extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg', 'ico'];

        foreach ($extensions as $ext) {
            $testFile = $this->sourceDir . "test-file.{$ext}";
            file_put_contents($testFile, "content for {$ext}");
        }

        $collector = new AssetCollector(
            $this->publicDir,
            $this->sourceDir,
            'http://localhost:8080/forum/',
            ''
        );

        foreach ($extensions as $ext) {
            $url = "http://localhost:8080/forum/test-file.{$ext}";
            $result = $collector->fetchFile($url);

            $this->assertStringStartsWith('img/', $result, "Extension {$ext} should be categorized as img/");
        }
    }

    public function testCopyStaticAssetsSkipsEmptyGlobResult(): void
    {
        // Create directories but no files
        // (setUp already creates empty directories)

        $collector = new AssetCollector($this->publicDir, $this->sourceDir);
        $copied = $collector->copyStaticAssets();

        $this->assertSame(0, $copied);
    }

    public function testProcessUrlsWithMultipleUrls(): void
    {
        $testImage1 = $this->sourceDir . 'img/first.png';
        $testImage2 = $this->sourceDir . 'img/second.jpg';
        @mkdir(dirname($testImage1), 0777, true);
        file_put_contents($testImage1, 'content1');
        file_put_contents($testImage2, 'content2');

        $collector = new AssetCollector(
            $this->publicDir,
            $this->sourceDir,
            'http://localhost:8080/forum/',
            'http://forum.example.com/'
        );

        $text = 'Image1: http://forum.example.com/img/first.png and Image2: http://forum.example.com/img/second.jpg';
        $result = $collector->processUrls($text);

        $this->assertStringContainsString('img/first.png', $result);
        $this->assertStringContainsString('img/second.jpg', $result);
        $this->assertStringNotContainsString('forum.example.com', $result);
    }

    public function testProcessUrlsWithLocalhostUrls(): void
    {
        $testImage = $this->sourceDir . 'img/local.png';
        @mkdir(dirname($testImage), 0777, true);
        file_put_contents($testImage, 'content');

        $collector = new AssetCollector(
            $this->publicDir,
            $this->sourceDir,
            'http://localhost:8080/forum/',
            'http://forum.example.com/'
        );

        $text = 'Check: http://localhost:8080/forum/img/local.png here';
        $result = $collector->processUrls($text);

        $this->assertStringContainsString('img/local.png', $result);
        $this->assertStringNotContainsString('localhost', $result);
    }

    public function testFetchFileCategorizesGenericImgPath(): void
    {
        $testImage = $this->sourceDir . 'img/generic/photo.jpg';
        @mkdir(dirname($testImage), 0777, true);
        file_put_contents($testImage, 'content');

        $collector = new AssetCollector(
            $this->publicDir,
            $this->sourceDir,
            'http://localhost:8080/forum/',
            ''
        );

        $url = 'http://localhost:8080/forum/img/generic/photo.jpg';
        $result = $collector->fetchFile($url);

        // img/* paths (not avatars/smilies) go to img/
        $this->assertSame('img/photo.jpg', $result);
    }
}
