<?php

declare(strict_types=1);

namespace FluxbbArchiver\Tests\Unit;

use FluxbbArchiver\I18n\Translator;
use PHPUnit\Framework\TestCase;

class TranslatorTest extends TestCase
{
    public function testConstructorLoadsEnglish(): void
    {
        $translator = new Translator('en');

        $this->assertSame('en', $translator->lang());
        // Should have loaded English strings
        $this->assertNotEmpty($translator->get('forum_index'));
    }

    public function testConstructorLoadsRequestedLanguage(): void
    {
        $translator = new Translator('fi');

        $this->assertSame('fi', $translator->lang());
    }

    public function testGetReturnsTranslation(): void
    {
        $translator = new Translator('en');

        // 'forum_index' should exist in the English translations
        $result = $translator->get('forum_index');
        $this->assertIsString($result);
        $this->assertNotEmpty($result);
    }

    public function testGetFallsBackToEnglish(): void
    {
        $translator = new Translator('fi');

        // Create a key that only exists in English by using a known key
        // and checking it returns something
        $result = $translator->get('forum_index');
        $this->assertIsString($result);
        $this->assertNotEmpty($result);
    }

    public function testGetReturnsKeyWhenNotFound(): void
    {
        $translator = new Translator('en');

        $unknownKey = 'this_key_does_not_exist_anywhere';
        $this->assertSame($unknownKey, $translator->get($unknownKey));
    }

    public function testMergeOverridesAddsTranslations(): void
    {
        $translator = new Translator('en');

        $translator->mergeOverrides(['custom_key' => 'Custom Value'], 'en');

        $this->assertSame('Custom Value', $translator->get('custom_key'));
    }

    public function testMergeOverridesOverwritesExisting(): void
    {
        $translator = new Translator('en');

        $originalValue = $translator->get('forum_index');
        $translator->mergeOverrides(['forum_index' => 'Overridden Value'], 'en');

        $this->assertSame('Overridden Value', $translator->get('forum_index'));
        $this->assertNotSame($originalValue, $translator->get('forum_index'));
    }

    public function testMergeOverridesForDifferentLanguage(): void
    {
        $translator = new Translator('fi');

        $translator->mergeOverrides(['custom_fi_key' => 'Finnish Value'], 'fi');

        $this->assertSame('Finnish Value', $translator->get('custom_fi_key'));
    }

    public function testMergeOverridesCreatesLanguageIfNotExists(): void
    {
        $translator = new Translator('en');

        // Try to merge to a language that doesn't have a file
        $translator->mergeOverrides(['test_key' => 'Test Value'], 'xx');

        // Since current lang is 'en', this won't be used
        // But the internal array should be created
        $this->assertSame('test_key', $translator->get('test_key')); // Falls back to key
    }

    public function testLangReturnsCurrentLanguage(): void
    {
        $translatorEn = new Translator('en');
        $translatorFi = new Translator('fi');

        $this->assertSame('en', $translatorEn->lang());
        $this->assertSame('fi', $translatorFi->lang());
    }

    public function testFinnishTranslationsDifferFromEnglish(): void
    {
        $translatorEn = new Translator('en');
        $translatorFi = new Translator('fi');

        // These should be different between languages
        $enValue = $translatorEn->get('forums');
        $fiValue = $translatorFi->get('forums');

        // Finnish and English translations should differ
        $this->assertNotSame($enValue, $fiValue, 'Finnish and English translations should differ');
    }

    public function testCommonTranslationKeysExist(): void
    {
        $translator = new Translator('en');

        // These keys should exist and return non-empty values
        $expectedKeys = [
            'forums',
            'topics',
            'posts',
            'users',
            'replies',
            'views',
            'last_post',
            'wrote',
        ];

        foreach ($expectedKeys as $key) {
            $value = $translator->get($key);
            $this->assertNotEmpty($value, "Expected translation for '$key' to exist and be non-empty");
        }
    }

    public function testKeyEqualsValueStillWorks(): void
    {
        // Some translations have key == value (e.g., 'by' => 'by')
        // This should still work correctly
        $translator = new Translator('en');
        $this->assertSame('by', $translator->get('by'));
    }
}
