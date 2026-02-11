<?php

declare(strict_types=1);

namespace FluxbbArchiver\Content;

class SlugGenerator
{
    /** @var array<string, string> */
    private static array $transliteration = [
        'ä' => 'a', 'Ä' => 'a',
        'ö' => 'o', 'Ö' => 'o',
        'å' => 'a', 'Å' => 'a',
        'ü' => 'u', 'Ü' => 'u',
        'é' => 'e', 'É' => 'e',
        'è' => 'e', 'È' => 'e',
        'ê' => 'e', 'Ê' => 'e',
        'ë' => 'e', 'Ë' => 'e',
        'à' => 'a', 'À' => 'a',
        'â' => 'a', 'Â' => 'a',
        'ô' => 'o', 'Ô' => 'o',
        'û' => 'u', 'Û' => 'u',
        'î' => 'i', 'Î' => 'i',
        'ï' => 'i', 'Ï' => 'i',
        'ñ' => 'n', 'Ñ' => 'n',
        'ß' => 'ss',
        '&' => '-and-',
        '@' => '-at-',
    ];

    public function generate(string $text, int $maxLength = 80): string
    {
        $slug = strtr($text, self::$transliteration);
        $slug = mb_strtolower($slug, 'UTF-8');
        $slug = preg_replace('/[^a-z0-9]+/', '-', $slug);
        $slug = trim($slug, '-');
        $slug = preg_replace('/-+/', '-', $slug);

        if (strlen($slug) > $maxLength) {
            $slug = substr($slug, 0, $maxLength);
            $lastHyphen = strrpos($slug, '-');
            if ($lastHyphen !== false && $lastHyphen > $maxLength - 20) {
                $slug = substr($slug, 0, $lastHyphen);
            }
            $slug = rtrim($slug, '-');
        }

        if (empty($slug)) {
            $slug = 'topic';
        }

        return $slug;
    }

    /**
     * Build a lookup of topic ID => unique slug from the database.
     *
     * @param array<int, array{id: string|int, subject: string}> $topics  Rows with 'id' and 'subject'.
     * @return array<int, string>  Map of topic_id => slug.
     */
    public function buildTopicSlugs(array $topics): array
    {
        $slugs = [];
        $usedSlugs = [];

        foreach ($topics as $topic) {
            $id = (int)$topic['id'];
            $baseSlug = $this->generate($topic['subject']);
            $slug = $baseSlug;

            $counter = 1;
            while (isset($usedSlugs[$slug])) {
                $slug = $baseSlug . '-' . $id;
                $counter++;
                if ($counter > 2) {
                    $slug = $baseSlug . '-' . $id;
                    break;
                }
            }

            $usedSlugs[$slug] = true;
            $slugs[$id] = $slug;
        }

        return $slugs;
    }
}
