<?php

declare(strict_types=1);

namespace FluxbbArchiver\I18n;

class Translator
{
    private string $lang;
    /** @var array<string, array<string, string>> */
    private array $strings = [];

    public function __construct(string $lang)
    {
        $this->lang = $lang;
        $this->loadLanguage('en');
        if ($lang !== 'en') {
            $this->loadLanguage($lang);
        }
    }

    public function lang(): string
    {
        return $this->lang;
    }

    private function loadLanguage(string $lang): void
    {
        $file = __DIR__ . '/lang/' . $lang . '.php';
        if (file_exists($file)) {
            $this->strings[$lang] = require $file;
        }
    }

    /**
     * Merge override translations (e.g. from a template's lang directory).
     *
     * @param array<string, string> $overrides Key-value translation pairs
     * @param string $lang Language code these overrides apply to
     */
    public function mergeOverrides(array $overrides, string $lang): void
    {
        if (!isset($this->strings[$lang])) {
            $this->strings[$lang] = [];
        }
        $this->strings[$lang] = array_merge($this->strings[$lang], $overrides);
    }

    /**
     * Get a translated string.
     */
    public function get(string $key): string
    {
        if (isset($this->strings[$this->lang][$key])) {
            return $this->strings[$this->lang][$key];
        }
        if (isset($this->strings['en'][$key])) {
            return $this->strings['en'][$key];
        }
        return $key;
    }
}
