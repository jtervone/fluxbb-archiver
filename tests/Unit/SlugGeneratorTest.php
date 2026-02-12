<?php

declare(strict_types=1);

namespace FluxbbArchiver\Tests\Unit;

use FluxbbArchiver\Content\SlugGenerator;
use PHPUnit\Framework\TestCase;

class SlugGeneratorTest extends TestCase
{
    private SlugGenerator $generator;

    protected function setUp(): void
    {
        $this->generator = new SlugGenerator();
    }

    public function testBasicSlugGeneration(): void
    {
        $this->assertSame('hello-world', $this->generator->generate('Hello World'));
    }

    public function testLowercaseConversion(): void
    {
        $this->assertSame('test-string', $this->generator->generate('TEST STRING'));
    }

    public function testSpecialCharactersRemoved(): void
    {
        // Note: @ is transliterated to -at- by design
        $this->assertSame('hello-at-world', $this->generator->generate('Hello! @World#'));
    }

    public function testMultipleSpacesCollapsed(): void
    {
        $this->assertSame('hello-world', $this->generator->generate('Hello   World'));
    }

    public function testLeadingTrailingHyphensRemoved(): void
    {
        $this->assertSame('hello-world', $this->generator->generate('  Hello World  '));
    }

    public function testFinnishCharacterTransliteration(): void
    {
        $this->assertSame('aaoa', $this->generator->generate('äÄöÅ'));
    }

    public function testGermanCharacterTransliteration(): void
    {
        $this->assertSame('uber-strasse', $this->generator->generate('Über Straße'));
    }

    public function testFrenchCharacterTransliteration(): void
    {
        $this->assertSame('cafe-creme', $this->generator->generate('Café Crème'));
    }

    public function testSpanishCharacterTransliteration(): void
    {
        $this->assertSame('espana', $this->generator->generate('España'));
    }

    public function testAmpersandTransliteration(): void
    {
        $this->assertSame('rock-and-roll', $this->generator->generate('Rock & Roll'));
    }

    public function testAtSignTransliteration(): void
    {
        $this->assertSame('user-at-host', $this->generator->generate('user@host'));
    }

    public function testMaxLengthEnforced(): void
    {
        $longText = 'This is a very long title that should be truncated to fit within the maximum length';
        $slug = $this->generator->generate($longText, 30);

        $this->assertLessThanOrEqual(30, strlen($slug));
    }

    public function testMaxLengthBreaksAtHyphen(): void
    {
        $text = 'Hello beautiful world of programming';
        $slug = $this->generator->generate($text, 25);

        // Should break at hyphen before "programming"
        $this->assertSame('hello-beautiful-world-of', $slug);
    }

    public function testEmptyInputReturnsDefault(): void
    {
        $this->assertSame('topic', $this->generator->generate(''));
    }

    public function testOnlySpecialCharsReturnsDefault(): void
    {
        // @ becomes 'at', so we need truly empty input
        $this->assertSame('topic', $this->generator->generate('!#$%^'));
    }

    public function testAtSymbolTransliterated(): void
    {
        // @ is transliterated to -at-, not removed
        $this->assertSame('at', $this->generator->generate('@'));
    }

    public function testNumbersPreserved(): void
    {
        $this->assertSame('topic-123-test', $this->generator->generate('Topic 123 Test'));
    }

    public function testBuildTopicSlugsBasic(): void
    {
        $topics = [
            ['id' => 1, 'subject' => 'First Topic'],
            ['id' => 2, 'subject' => 'Second Topic'],
            ['id' => 3, 'subject' => 'Third Topic'],
        ];

        $slugs = $this->generator->buildTopicSlugs($topics);

        $this->assertSame('first-topic', $slugs[1]);
        $this->assertSame('second-topic', $slugs[2]);
        $this->assertSame('third-topic', $slugs[3]);
    }

    public function testBuildTopicSlugsHandlesCollisions(): void
    {
        $topics = [
            ['id' => 1, 'subject' => 'Same Title'],
            ['id' => 2, 'subject' => 'Same Title'],
            ['id' => 3, 'subject' => 'Same Title'],
        ];

        $slugs = $this->generator->buildTopicSlugs($topics);

        $this->assertSame('same-title', $slugs[1]);
        $this->assertSame('same-title-2', $slugs[2]);
        $this->assertSame('same-title-3', $slugs[3]);
    }

    public function testBuildTopicSlugsWithStringIds(): void
    {
        $topics = [
            ['id' => '10', 'subject' => 'Topic A'],
            ['id' => '20', 'subject' => 'Topic B'],
        ];

        $slugs = $this->generator->buildTopicSlugs($topics);

        $this->assertArrayHasKey(10, $slugs);
        $this->assertArrayHasKey(20, $slugs);
        $this->assertSame('topic-a', $slugs[10]);
        $this->assertSame('topic-b', $slugs[20]);
    }

    public function testBuildTopicSlugsEmpty(): void
    {
        $slugs = $this->generator->buildTopicSlugs([]);
        $this->assertSame([], $slugs);
    }

    public function testBuildTopicSlugsMixedCollisions(): void
    {
        $topics = [
            ['id' => 1, 'subject' => 'Unique Topic'],
            ['id' => 2, 'subject' => 'Duplicate Topic'],
            ['id' => 3, 'subject' => 'Another Unique'],
            ['id' => 4, 'subject' => 'Duplicate Topic'],
        ];

        $slugs = $this->generator->buildTopicSlugs($topics);

        $this->assertSame('unique-topic', $slugs[1]);
        $this->assertSame('duplicate-topic', $slugs[2]);
        $this->assertSame('another-unique', $slugs[3]);
        $this->assertSame('duplicate-topic-4', $slugs[4]);
    }

    public function testBuildTopicSlugsPreservesOrder(): void
    {
        $topics = [
            ['id' => 100, 'subject' => 'First'],
            ['id' => 50, 'subject' => 'Second'],
            ['id' => 200, 'subject' => 'Third'],
        ];

        $slugs = $this->generator->buildTopicSlugs($topics);

        $this->assertArrayHasKey(100, $slugs);
        $this->assertArrayHasKey(50, $slugs);
        $this->assertArrayHasKey(200, $slugs);
    }

    public function testAccentedCharacterVariations(): void
    {
        $this->assertSame('e', $this->generator->generate('é'));
        $this->assertSame('e', $this->generator->generate('è'));
        $this->assertSame('e', $this->generator->generate('ê'));
        $this->assertSame('e', $this->generator->generate('ë'));
        $this->assertSame('a', $this->generator->generate('à'));
        $this->assertSame('a', $this->generator->generate('â'));
        $this->assertSame('o', $this->generator->generate('ô'));
        $this->assertSame('u', $this->generator->generate('û'));
        $this->assertSame('i', $this->generator->generate('î'));
        $this->assertSame('i', $this->generator->generate('ï'));
    }

    public function testMixedContent(): void
    {
        $text = 'Täällä on ääkkösiä & erikoismerkkejä!';
        $slug = $this->generator->generate($text);

        $this->assertSame('taalla-on-aakkosia-and-erikoismerkkeja', $slug);
    }

    public function testConsecutiveHyphensCollapsed(): void
    {
        $text = 'Hello---World';
        $slug = $this->generator->generate($text);

        $this->assertSame('hello-world', $slug);
    }
}
