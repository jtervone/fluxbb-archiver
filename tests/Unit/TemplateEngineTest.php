<?php

declare(strict_types=1);

namespace FluxbbArchiver\Tests\Unit;

use FluxbbArchiver\Html\TemplateEngine;
use PHPUnit\Framework\TestCase;
use RuntimeException;

class TemplateEngineTest extends TestCase
{
    private string $testDir;
    private string $customDir;
    private string $defaultDir;

    protected function setUp(): void
    {
        $this->testDir = sys_get_temp_dir() . '/fluxbb-template-test-' . uniqid();
        $this->defaultDir = $this->testDir . '/default';
        $this->customDir = $this->testDir . '/custom';

        mkdir($this->defaultDir . '/partials', 0777, true);
        mkdir($this->customDir . '/partials', 0777, true);
        mkdir($this->customDir . '/lang', 0777, true);
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

    public function testRenderBasicTemplate(): void
    {
        file_put_contents($this->defaultDir . '/test.php', '<?php echo $title; ?>');

        $engine = new TemplateEngine($this->defaultDir, $this->defaultDir);
        $result = $engine->render('test', ['title' => 'Hello World']);

        $this->assertSame('Hello World', $result);
    }

    public function testRenderWithMultipleVariables(): void
    {
        file_put_contents(
            $this->defaultDir . '/test.php',
            '<?php echo $name . " - " . $age; ?>'
        );

        $engine = new TemplateEngine($this->defaultDir, $this->defaultDir);
        $result = $engine->render('test', ['name' => 'John', 'age' => 30]);

        $this->assertSame('John - 30', $result);
    }

    public function testRenderThrowsExceptionForMissingTemplate(): void
    {
        $engine = new TemplateEngine($this->defaultDir, $this->defaultDir);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Template not found: nonexistent.php');

        $engine->render('nonexistent');
    }

    public function testPartialRendersFromPartialsDirectory(): void
    {
        file_put_contents(
            $this->defaultDir . '/partials/header.php',
            '<header><?php echo $title; ?></header>'
        );

        $engine = new TemplateEngine($this->defaultDir, $this->defaultDir);
        $result = $engine->partial('header', ['title' => 'Site Title']);

        $this->assertSame('<header>Site Title</header>', $result);
    }

    public function testRenderPageWrapsContentInLayout(): void
    {
        file_put_contents(
            $this->defaultDir . '/layout.php',
            '<!DOCTYPE html><html><head><title><?php echo $title; ?></title></head><body><?php echo $content; ?></body></html>'
        );

        $engine = new TemplateEngine($this->defaultDir, $this->defaultDir);
        $result = $engine->renderPage('<p>Content</p>', ['title' => 'Page Title']);

        $this->assertSame(
            '<!DOCTYPE html><html><head><title>Page Title</title></head><body><p>Content</p></body></html>',
            $result
        );
    }

    public function testCustomTemplateOverridesDefault(): void
    {
        file_put_contents($this->defaultDir . '/test.php', 'Default');
        file_put_contents($this->customDir . '/test.php', 'Custom');

        $engine = new TemplateEngine($this->customDir, $this->defaultDir);
        $result = $engine->render('test');

        $this->assertSame('Custom', $result);
    }

    public function testFallsBackToDefaultWhenCustomMissing(): void
    {
        file_put_contents($this->defaultDir . '/test.php', 'Default');

        $engine = new TemplateEngine($this->customDir, $this->defaultDir);
        $result = $engine->render('test');

        $this->assertSame('Default', $result);
    }

    public function testCustomPartialOverridesDefault(): void
    {
        file_put_contents($this->defaultDir . '/partials/item.php', 'Default Item');
        file_put_contents($this->customDir . '/partials/item.php', 'Custom Item');

        $engine = new TemplateEngine($this->customDir, $this->defaultDir);
        $result = $engine->partial('item');

        $this->assertSame('Custom Item', $result);
    }

    public function testGetStylesheetPathReturnsCustomWhenExists(): void
    {
        file_put_contents($this->defaultDir . '/style.css', 'default');
        file_put_contents($this->customDir . '/style.css', 'custom');

        $engine = new TemplateEngine($this->customDir, $this->defaultDir);
        $result = $engine->getStylesheetPath();

        $this->assertSame($this->customDir . '/style.css', $result);
    }

    public function testGetStylesheetPathFallsBackToDefault(): void
    {
        file_put_contents($this->defaultDir . '/style.css', 'default');

        $engine = new TemplateEngine($this->customDir, $this->defaultDir);
        $result = $engine->getStylesheetPath();

        $this->assertSame($this->defaultDir . '/style.css', $result);
    }

    public function testGetStylesheetPathWhenSameDirectory(): void
    {
        file_put_contents($this->defaultDir . '/style.css', 'default');

        $engine = new TemplateEngine($this->defaultDir, $this->defaultDir);
        $result = $engine->getStylesheetPath();

        $this->assertSame($this->defaultDir . '/style.css', $result);
    }

    public function testGetLangDirReturnsPathWhenExists(): void
    {
        $engine = new TemplateEngine($this->customDir, $this->defaultDir);
        $result = $engine->getLangDir();

        $this->assertSame($this->customDir . '/lang/', $result);
    }

    public function testGetLangDirReturnsNullWhenMissing(): void
    {
        rmdir($this->customDir . '/lang');

        $engine = new TemplateEngine($this->customDir, $this->defaultDir);
        $result = $engine->getLangDir();

        $this->assertNull($result);
    }

    public function testGetLangDirReturnsNullWhenSameDirectory(): void
    {
        $engine = new TemplateEngine($this->defaultDir, $this->defaultDir);
        $result = $engine->getLangDir();

        $this->assertNull($result);
    }

    public function testHtmlEscapeHelper(): void
    {
        $engine = new TemplateEngine($this->defaultDir, $this->defaultDir);

        $this->assertSame('&lt;script&gt;', $engine->h('<script>'));
        $this->assertSame('&amp;', $engine->h('&'));
        $this->assertSame('', $engine->h(null));
    }

    public function testTemplateHasAccessToThis(): void
    {
        file_put_contents(
            $this->defaultDir . '/test.php',
            '<?php echo $this->h("<div>"); ?>'
        );

        $engine = new TemplateEngine($this->defaultDir, $this->defaultDir);
        $result = $engine->render('test');

        $this->assertSame('&lt;div&gt;', $result);
    }

    public function testTemplateCanCallPartial(): void
    {
        file_put_contents(
            $this->defaultDir . '/partials/inner.php',
            '<span>Inner</span>'
        );
        file_put_contents(
            $this->defaultDir . '/outer.php',
            '<div><?php echo $this->partial("inner"); ?></div>'
        );

        $engine = new TemplateEngine($this->defaultDir, $this->defaultDir);
        $result = $engine->render('outer');

        $this->assertSame('<div><span>Inner</span></div>', $result);
    }

    public function testDirectoryPathsNormalized(): void
    {
        // Paths with trailing slashes should be handled
        $engine = new TemplateEngine($this->defaultDir . '/', $this->defaultDir . '/');

        file_put_contents($this->defaultDir . '/test.php', 'OK');
        $result = $engine->render('test');

        $this->assertSame('OK', $result);
    }
}
