<?php

declare(strict_types=1);

namespace FluxbbArchiver\Tests\Unit;

use FluxbbArchiver\Content\AssetCollector;
use FluxbbArchiver\Content\BbcodeParser;
use FluxbbArchiver\I18n\Translator;
use PHPUnit\Framework\TestCase;

class BbcodeParserTest extends TestCase
{
    private BbcodeParser $parser;
    private BbcodeParser $parserNoObfuscate;

    protected function setUp(): void
    {
        $translator = $this->createMock(Translator::class);
        $translator->method('get')->willReturnCallback(function (string $key) {
            $translations = [
                'wrote' => 'wrote',
                'image' => 'Image',
            ];
            return $translations[$key] ?? $key;
        });

        $assets = $this->createMock(AssetCollector::class);
        $assets->method('fetchFile')->willReturnArgument(0);

        $this->parser = new BbcodeParser($translator, $assets, true);
        $this->parserNoObfuscate = new BbcodeParser($translator, $assets, false);
    }

    public function testNullInputReturnsEmpty(): void
    {
        $this->assertSame('', $this->parser->toHtml(null));
    }

    public function testEmptyInputReturnsEmpty(): void
    {
        $this->assertSame('', $this->parser->toHtml(''));
    }

    public function testPlainTextEscaped(): void
    {
        $this->assertSame(
            '&lt;script&gt;alert(&quot;XSS&quot;)&lt;/script&gt;',
            $this->parser->toHtml('<script>alert("XSS")</script>')
        );
    }

    public function testBoldTag(): void
    {
        $this->assertSame('<strong>text</strong>', $this->parser->toHtml('[b]text[/b]'));
    }

    public function testItalicTag(): void
    {
        $this->assertSame('<em>text</em>', $this->parser->toHtml('[i]text[/i]'));
    }

    public function testUnderlineTag(): void
    {
        $this->assertSame('<u>text</u>', $this->parser->toHtml('[u]text[/u]'));
    }

    public function testStrikeTag(): void
    {
        $this->assertSame('<del>text</del>', $this->parser->toHtml('[s]text[/s]'));
    }

    public function testCodeTag(): void
    {
        $this->assertSame('<pre><code>code here</code></pre>', $this->parser->toHtml('[code]code here[/code]'));
    }

    public function testQuoteTagWithoutAttribution(): void
    {
        $this->assertSame('<blockquote>quoted text</blockquote>', $this->parser->toHtml('[quote]quoted text[/quote]'));
    }

    public function testQuoteTagWithAttribution(): void
    {
        $result = $this->parser->toHtml('[quote=John]quoted text[/quote]');
        $this->assertSame('<blockquote><cite>John wrote:</cite>quoted text</blockquote>', $result);
    }

    public function testUrlTagSimple(): void
    {
        $result = $this->parser->toHtml('[url]https://example.com[/url]');
        $this->assertSame('<a href="https://example.com" rel="nofollow">https://example.com</a>', $result);
    }

    public function testUrlTagWithText(): void
    {
        $result = $this->parser->toHtml('[url=https://example.com]Click here[/url]');
        $this->assertSame('<a href="https://example.com" rel="nofollow">Click here</a>', $result);
    }

    public function testImageTag(): void
    {
        $result = $this->parser->toHtml('[img]https://example.com/image.jpg[/img]');
        $this->assertSame('<img src="https://example.com/image.jpg" alt="Image" loading="lazy">', $result);
    }

    public function testColorTag(): void
    {
        $result = $this->parser->toHtml('[color=red]colored text[/color]');
        $this->assertSame('<span style="color:red">colored text</span>', $result);
    }

    public function testSizeTag(): void
    {
        $result = $this->parser->toHtml('[size=18px]large text[/size]');
        $this->assertSame('<span style="font-size:18px">large text</span>', $result);
    }

    public function testUnorderedListTag(): void
    {
        $result = $this->parser->toHtml('[list][*]item1[*]item2[/list]');
        $this->assertSame('<ul><li>item1</li><li>item2</li></ul>', $result);
    }

    public function testOrderedListTag(): void
    {
        $result = $this->parser->toHtml('[list=1][*]first[*]second[/list]');
        $this->assertSame('<ol><li>first</li><li>second</li></ol>', $result);
    }

    public function testNestedTags(): void
    {
        $result = $this->parser->toHtml('[b][i]bold and italic[/i][/b]');
        $this->assertSame('<strong><em>bold and italic</em></strong>', $result);
    }

    public function testNewlinesConvertedToBr(): void
    {
        $result = $this->parser->toHtml("line1\nline2");
        $this->assertStringContainsString('<br', $result);
    }

    public function testFluxbbCommentMarkersRemoved(): void
    {
        $result = $this->parser->toHtml('<!-- m -->text<!-- m -->');
        $this->assertSame('text', $result);
    }

    // Static method tests

    public function testHtmlEscaping(): void
    {
        $this->assertSame('&lt;div&gt;', BbcodeParser::h('<div>'));
        $this->assertSame('&amp;', BbcodeParser::h('&'));
        $this->assertSame('&quot;', BbcodeParser::h('"'));
        $this->assertSame('&#039;', BbcodeParser::h("'"));
    }

    public function testHtmlEscapingNull(): void
    {
        $this->assertSame('', BbcodeParser::h(null));
    }

    public function testHtmlEscapingNumber(): void
    {
        $this->assertSame('123', BbcodeParser::h(123));
    }

    // Email obfuscation tests

    public function testObfuscateEmailsInTextBasic(): void
    {
        $result = BbcodeParser::obfuscateEmailsInText('Contact: user@example.com');
        $this->assertSame('Contact: user [at] example [dot] com', $result);
    }

    public function testObfuscateEmailsInTextMailto(): void
    {
        $result = BbcodeParser::obfuscateEmailsInText('Email: mailto:user@example.com');
        $this->assertSame('Email: user [at] example [dot] com', $result);
    }

    public function testObfuscateEmailsInTextMultiple(): void
    {
        $result = BbcodeParser::obfuscateEmailsInText('first@example.com and second@test.org');
        $this->assertSame('first [at] example [dot] com and second [at] test [dot] org', $result);
    }

    public function testObfuscateEmailsInTextPreservesDotsInLocalPart(): void
    {
        $result = BbcodeParser::obfuscateEmailsInText('john.doe@example.com');
        $this->assertSame('john.doe [at] example [dot] com', $result);
    }

    public function testObfuscateEmailsInTextNullInput(): void
    {
        $this->assertSame('', BbcodeParser::obfuscateEmailsInText(null));
    }

    public function testObfuscateEmailsInTextEmptyInput(): void
    {
        $this->assertSame('', BbcodeParser::obfuscateEmailsInText(''));
    }

    public function testObfuscateEmailsInTextNoEmails(): void
    {
        $text = 'This text has no email addresses';
        $this->assertSame($text, BbcodeParser::obfuscateEmailsInText($text));
    }

    public function testObfuscateEmailAddressesRemovesMailtoLinks(): void
    {
        $html = '<a href="mailto:user@example.com">user@example.com</a>';
        $result = BbcodeParser::obfuscateEmailAddresses($html);
        $this->assertSame('user [at] example [dot] com', $result);
    }

    public function testObfuscateEmailAddressesPlainText(): void
    {
        $html = 'Contact us at user@example.com for help';
        $result = BbcodeParser::obfuscateEmailAddresses($html);
        $this->assertSame('Contact us at user [at] example [dot] com for help', $result);
    }

    public function testParserObfuscatesEmailsWhenEnabled(): void
    {
        $result = $this->parser->toHtml('Contact: user@example.com');
        $this->assertStringContainsString('[at]', $result);
        $this->assertStringNotContainsString('@example.com', $result);
    }

    public function testParserDoesNotObfuscateEmailsWhenDisabled(): void
    {
        $result = $this->parserNoObfuscate->toHtml('Contact: user@example.com');
        $this->assertStringContainsString('user@example.com', $result);
        $this->assertStringNotContainsString('[at]', $result);
    }

    // Edge cases

    public function testTagsWithPhpBBStyleSuffix(): void
    {
        // FluxBB sometimes adds :suffix to tags
        $result = $this->parser->toHtml('[b:abc123]text[/b:abc123]');
        $this->assertSame('<strong>text</strong>', $result);
    }

    public function testPreservesExistingHtmlTags(): void
    {
        $result = $this->parser->toHtml('<a href="test">link</a>');
        $this->assertStringContainsString('<a href="test">link</a>', $result);
    }

    public function testComplexContent(): void
    {
        $input = "[b]Bold[/b] and [i]italic[/i]\n[quote=User]quoted[/quote]";
        $result = $this->parser->toHtml($input);

        $this->assertStringContainsString('<strong>Bold</strong>', $result);
        $this->assertStringContainsString('<em>italic</em>', $result);
        $this->assertStringContainsString('<blockquote>', $result);
        $this->assertStringContainsString('User wrote:', $result);
    }
}
