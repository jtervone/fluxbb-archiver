<?php

declare(strict_types=1);

namespace FluxbbArchiver\Content;

use FluxbbArchiver\I18n\Translator;

class BbcodeParser
{
    private Translator $translator;
    private AssetCollector $assets;
    private bool $obfuscateEmails;

    public function __construct(Translator $translator, AssetCollector $assets, bool $obfuscateEmails = true)
    {
        $this->translator = $translator;
        $this->assets = $assets;
        $this->obfuscateEmails = $obfuscateEmails;
    }

    public function toHtml(?string $text): string
    {
        if ($text === null || $text === '') {
            return '';
        }
        // Remove FluxBB comment markers
        $text = preg_replace('/<!-- [a-z] -->/', '', $text);

        // Protect existing HTML tags
        $protected = [];
        $text = preg_replace_callback(
            '/<(a|img|br|em|strong)[^>]*>.*?<\/\1>|<(a|img|br)[^>]*\/?>/is',
            function ($match) use (&$protected) {
                $key = '###PROTECTED' . count($protected) . '###';
                $protected[$key] = $match[0];
                return $key;
            },
            $text
        );

        // Escape remaining text
        $text = self::h($text);

        // Restore protected HTML
        foreach ($protected as $key => $html) {
            $text = str_replace($key, $html, $text);
        }

        // Convert newlines to <br>
        $text = nl2br($text);

        // Bold
        $text = preg_replace('/\[b(:[a-z0-9]+)?\](.*?)\[\/b(:[a-z0-9]+)?\]/is', '<strong>$2</strong>', $text);

        // Italic
        $text = preg_replace('/\[i(:[a-z0-9]+)?\](.*?)\[\/i(:[a-z0-9]+)?\]/is', '<em>$2</em>', $text);

        // Underline
        $text = preg_replace('/\[u(:[a-z0-9]+)?\](.*?)\[\/u(:[a-z0-9]+)?\]/is', '<u>$2</u>', $text);

        // Strike
        $text = preg_replace('/\[s(:[a-z0-9]+)?\](.*?)\[\/s(:[a-z0-9]+)?\]/is', '<del>$2</del>', $text);

        // Code
        $text = preg_replace('/\[code(:[a-z0-9]+)?\](.*?)\[\/code(:[a-z0-9]+)?\]/is', '<pre><code>$2</code></pre>', $text);

        // Quote with attribution
        $wroteText = $this->translator->get('wrote');
        $text = preg_replace(
            '/\[quote=([^\]:]+)(:[a-z0-9]+)?\](.*?)\[\/quote(:[a-z0-9]+)?\]/is',
            '<blockquote><cite>$1 ' . $wroteText . ':</cite>$3</blockquote>',
            $text
        );

        // Quote without attribution
        $text = preg_replace('/\[quote(:[a-z0-9]+)?\](.*?)\[\/quote(:[a-z0-9]+)?\]/is', '<blockquote>$2</blockquote>', $text);

        // URL
        $text = preg_replace('/\[url(:[a-z0-9]+)?\](.*?)\[\/url(:[a-z0-9]+)?\]/is', '<a href="$2" rel="nofollow">$2</a>', $text);
        $text = preg_replace('/\[url=([^\]]+?)(:[a-z0-9]+)?\](.*?)\[\/url(:[a-z0-9]+)?\]/is', '<a href="$1" rel="nofollow">$3</a>', $text);

        // Image - download local images
        $imageText = $this->translator->get('image');
        $assets = $this->assets;
        $text = preg_replace_callback(
            '/\[img(:[a-z0-9]+)?\](.*?)\[\/img(:[a-z0-9]+)?\]/is',
            function ($matches) use ($imageText, $assets) {
                $imgUrl = html_entity_decode($matches[2], ENT_QUOTES, 'UTF-8');
                $newUrl = $assets->fetchFile($imgUrl);
                return '<img src="' . self::h($newUrl) . '" alt="' . $imageText . '" loading="lazy">';
            },
            $text
        );

        // Color
        $text = preg_replace('/\[color=([^\]:]+)(:[a-z0-9]+)?\](.*?)\[\/color(:[a-z0-9]+)?\]/is', '<span style="color:$1">$3</span>', $text);

        // Size
        $text = preg_replace('/\[size=([^\]:]+)(:[a-z0-9]+)?\](.*?)\[\/size(:[a-z0-9]+)?\]/is', '<span style="font-size:$1">$3</span>', $text);

        // List - process items first, then wrappers
        $text = preg_replace('/\[\*(:[a-z0-9]+)?\](.*?)(?=\[\*(:[a-z0-9]+)?\]|\[\/list(:[a-z0-9]+)?\])/is', '<li>$2</li>', $text);
        $text = preg_replace('/\[list(:[a-z0-9]+)?\](.*?)\[\/list(:[a-z0-9]+)?\]/is', '<ul>$2</ul>', $text);
        $text = preg_replace('/\[list=1(:[a-z0-9]+)?\](.*?)\[\/list(:[a-z0-9]+)?\]/is', '<ol>$2</ol>', $text);

        if ($this->obfuscateEmails) {
            $text = self::obfuscateEmailAddresses($text);
        }

        return $text;
    }

    /**
     * Obfuscate email addresses in HTML content.
     *
     * Handles:
     * - mailto: links: <a href="mailto:user@example.com">user@example.com</a>
     * - Plain email addresses in text: user@example.com
     *
     * Converts to format: user [at] example [dot] com
     */
    public static function obfuscateEmailAddresses(string $text): string
    {
        // Handle all <a> tags where the href contains an @ sign (mailto: or otherwise)
        // This catches mailto:user@host, bare user@host, and http://user@host URLs
        $text = preg_replace_callback(
            '/<a\b[^>]*href=[^>]*@[^>]*>(.*?)<\/a>/is',
            function (array $matches): string {
                // Return just the visible text with emails obfuscated
                return preg_replace_callback(
                    '/[a-zA-Z0-9._%+\-]+@[a-zA-Z0-9.\-]+\.[a-zA-Z]{2,}/',
                    function (array $m): string {
                        return self::obfuscateEmail($m[0]);
                    },
                    $matches[1]
                );
            },
            $text
        );

        // Then handle plain email addresses in text (not inside HTML attributes)
        // Split by HTML tags to only process text nodes
        $parts = preg_split('/(<[^>]+>)/s', $text, -1, PREG_SPLIT_DELIM_CAPTURE);
        $result = '';
        foreach ($parts as $part) {
            if (strpos($part, '<') === 0) {
                // HTML tag — leave as-is
                $result .= $part;
            } else {
                // Text node — obfuscate email addresses (but not URLs containing @)
                $result .= preg_replace_callback(
                    '/(?<![\/:])[a-zA-Z0-9._%+\-]+@[a-zA-Z0-9.\-]+\.[a-zA-Z]{2,}/',
                    function (array $matches): string {
                        return self::obfuscateEmail($matches[0]);
                    },
                    $part
                );
            }
        }

        return $result;
    }

    /**
     * Obfuscate email addresses in plain text (BBCode / JSON content).
     * Unlike obfuscateEmailAddresses(), this does not parse HTML tags.
     */
    public static function obfuscateEmailsInText(?string $text): string
    {
        if ($text === null || $text === '') {
            return '';
        }
        // Replace mailto: references
        $text = preg_replace_callback(
            '/mailto:([a-zA-Z0-9._%+\-]+@[a-zA-Z0-9.\-]+\.[a-zA-Z]{2,})/',
            function (array $matches): string {
                return self::obfuscateEmail($matches[1]);
            },
            $text
        );
        // Replace plain email addresses (not preceded by URL chars)
        $text = preg_replace_callback(
            '/(?<![\/:])[a-zA-Z0-9._%+\-]+@[a-zA-Z0-9.\-]+\.[a-zA-Z]{2,}/',
            function (array $matches): string {
                return self::obfuscateEmail($matches[0]);
            },
            $text
        );
        return $text;
    }

    /**
     * Convert a single email address to obfuscated form.
     * user@example.com => user [at] example [dot] com
     */
    private static function obfuscateEmail(string $email): string
    {
        $email = str_replace('@', ' [at] ', $email);
        // Only replace dots in the domain part (after [at])
        $atPos = strpos($email, ' [at] ');
        if ($atPos !== false) {
            $local = substr($email, 0, $atPos);
            $domain = substr($email, $atPos + 6); // length of ' [at] '
            $domain = str_replace('.', ' [dot] ', $domain);
            return $local . ' [at] ' . $domain;
        }
        return $email;
    }

    /**
     * Process text to find and download local images, updating URLs.
     */
    public function processLocalUrls(string $text): string
    {
        return $this->assets->processUrls($text);
    }

    /**
     * @param mixed $str
     */
    public static function h($str): string
    {
        return htmlspecialchars((string)($str ?? ''), ENT_QUOTES, 'UTF-8');
    }
}
