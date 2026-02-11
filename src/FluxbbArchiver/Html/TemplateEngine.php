<?php

declare(strict_types=1);

namespace FluxbbArchiver\Html;

use FluxbbArchiver\Content\BbcodeParser;

class TemplateEngine
{
    private string $templateDir;
    private string $defaultDir;

    public function __construct(string $templateDir, string $defaultDir)
    {
        $this->templateDir = rtrim($templateDir, '/') . '/';
        $this->defaultDir = rtrim($defaultDir, '/') . '/';
    }

    /**
     * Resolve a template file path. Checks custom template dir first, then default.
     */
    private function resolve(string $name, string $suffix = '.php'): string
    {
        $customPath = $this->templateDir . $name . $suffix;
        if ($this->templateDir !== $this->defaultDir && file_exists($customPath)) {
            return $customPath;
        }

        $defaultPath = $this->defaultDir . $name . $suffix;
        if (file_exists($defaultPath)) {
            return $defaultPath;
        }

        throw new \RuntimeException("Template not found: {$name}{$suffix}");
    }

    /**
     * Render a page template. Returns HTML fragment (page content).
     *
     * @param string $template Template name (e.g. 'topic', 'forum')
     * @param array<string, mixed> $data Variables to extract into template scope
     */
    public function render(string $template, array $data = []): string
    {
        $file = $this->resolve($template);
        return $this->renderFile($file, $data);
    }

    /**
     * Render a partial template from the partials/ subdirectory.
     *
     * @param string $name Partial name (e.g. 'post', 'pagination')
     * @param array<string, mixed> $data Variables to extract into template scope
     */
    public function partial(string $name, array $data = []): string
    {
        $file = $this->resolve('partials/' . $name);
        return $this->renderFile($file, $data);
    }

    /**
     * Wrap page content in layout.php to produce a complete HTML document.
     *
     * @param string $content Page content HTML
     * @param array<string, mixed> $layoutData Variables for the layout (title, breadcrumbs, seo, etc.)
     */
    public function renderPage(string $content, array $layoutData = []): string
    {
        $layoutData['content'] = $content;
        $file = $this->resolve('layout');
        return $this->renderFile($file, $layoutData);
    }

    /**
     * Get the resolved path to style.css (custom template first, then default).
     */
    public function getStylesheetPath(): string
    {
        if ($this->templateDir !== $this->defaultDir) {
            $customCss = $this->templateDir . 'style.css';
            if (file_exists($customCss)) {
                return $customCss;
            }
        }
        return $this->defaultDir . 'style.css';
    }

    /**
     * Get the template language directory if it exists, or null.
     */
    public function getLangDir(): ?string
    {
        if ($this->templateDir !== $this->defaultDir) {
            $langDir = $this->templateDir . 'lang/';
            if (is_dir($langDir)) {
                return $langDir;
            }
        }
        return null;
    }

    /**
     * HTML escaping helper — available in templates as $this->h().
     *
     * @param mixed $value
     */
    public function h($value): string
    {
        return BbcodeParser::h($value);
    }

    /**
     * Render a PHP template file with extracted data.
     *
     * @param string $__file__ Template file path
     * @param array<string, mixed> $__data__ Variables to extract
     */
    private function renderFile(string $__file__, array $__data__): string
    {
        extract($__data__, EXTR_SKIP);
        ob_start();
        include $__file__;
        return ob_get_clean();
    }
}
