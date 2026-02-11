<?php

declare(strict_types=1);

namespace FluxbbArchiver\Content;

class AssetCollector
{
    private string $publicDir;
    private string $sourceDir;
    private string $localFetchBase;
    private string $originalUrlBase;
    /** @var array<string, string> */
    private array $downloadedFiles = [];

    /**
     * @param string $publicDir      Absolute path to public output directory.
     * @param string $sourceDir      Absolute path to the FluxBB source root (PUN_ROOT equivalent).
     * @param string $localFetchBase Local URL base for fetching images (e.g. http://localhost:8080/splatboard/).
     * @param string $originalUrlBase Original URL base to rewrite (e.g. http://splatweb.net/splatboard/).
     */
    public function __construct(string $publicDir, string $sourceDir, string $localFetchBase = '', string $originalUrlBase = '')
    {
        $this->publicDir = $publicDir;
        $this->sourceDir = rtrim($sourceDir, '/') . '/';
        $this->localFetchBase = $localFetchBase;
        $this->originalUrlBase = $originalUrlBase;
    }

    /**
     * Get count of downloaded/processed files.
     */
    public function downloadCount(): int
    {
        return count($this->downloadedFiles);
    }

    /**
     * Download/copy a local file to the static export directory.
     * Returns the new relative path or original URL if download fails.
     */
    public function fetchFile(string $url, string $targetDir = 'img/'): string
    {
        if (isset($this->downloadedFiles[$url])) {
            return $this->downloadedFiles[$url];
        }

        // Convert original URLs to local fetch URLs
        $fetchUrl = $url;
        if ($this->originalUrlBase && strpos($url, $this->originalUrlBase) === 0) {
            $fetchUrl = str_replace($this->originalUrlBase, $this->localFetchBase, $url);
        } elseif ($this->localFetchBase && strpos($url, $this->localFetchBase) === 0) {
            $fetchUrl = $url;
        } else {
            return $url;
        }

        $relativePath = str_replace($this->localFetchBase, '', $fetchUrl);
        $cleanPath = preg_replace('/\?.*$/', '', $relativePath);

        // Determine target subdirectory
        if (strpos($cleanPath, 'img/avatars/') === 0) {
            $targetSubdir = 'img/avatars/';
        } elseif (strpos($cleanPath, 'img/smilies/') === 0) {
            $targetSubdir = 'img/smilies/';
        } elseif (strpos($cleanPath, 'img/') === 0) {
            $targetSubdir = 'img/';
        } elseif (preg_match('/\.(jpg|jpeg|png|gif|webp|svg|ico)$/i', $cleanPath)) {
            $targetSubdir = 'img/';
        } else {
            $targetSubdir = 'files/';
        }

        $fullTargetDir = $this->publicDir . $targetSubdir;
        if (!is_dir($fullTargetDir)) {
            @mkdir($fullTargetDir, 0777, true);
        }

        $filename = basename($cleanPath);
        $targetPath = $fullTargetDir . $filename;
        $relativeResult = $targetSubdir . $filename;

        // Try local copy first
        $localSource = $this->sourceDir . $cleanPath;
        if (file_exists($localSource)) {
            if (@copy($localSource, $targetPath)) {
                $this->downloadedFiles[$url] = $relativeResult;
                return $relativeResult;
            }
        }

        // Try HTTP fetch
        $context = stream_context_create([
            'http' => [
                'timeout' => 10,
                'ignore_errors' => true,
            ],
        ]);

        $content = @file_get_contents($fetchUrl, false, $context);
        if ($content !== false && strlen($content) > 0) {
            if (@file_put_contents($targetPath, $content)) {
                $this->downloadedFiles[$url] = $relativeResult;
                return $relativeResult;
            }
        }

        $this->downloadedFiles[$url] = $url;
        return $url;
    }

    /**
     * Process text to find and download local images, updating URLs.
     */
    public function processUrls(string $text): string
    {
        $patterns = [
            '/(https?:\/\/splatweb\.net\/splatboard\/[^\s\'"<>\)]+\.(jpg|jpeg|png|gif|webp|svg|ico)(\?[^\s\'"<>\)]*)?)/i',
            '/(https?:\/\/localhost:\d+\/splatboard\/[^\s\'"<>\)]+\.(jpg|jpeg|png|gif|webp|svg|ico)(\?[^\s\'"<>\)]*)?)/i',
        ];

        foreach ($patterns as $pattern) {
            $self = $this;
            $text = preg_replace_callback($pattern, function ($matches) use ($self) {
                $originalUrl = $matches[1];
                $newPath = $self->fetchFile($originalUrl);
                if (strpos($newPath, 'http') !== 0) {
                    return $newPath;
                }
                return $originalUrl;
            }, $text);
        }

        return $text;
    }

    /**
     * Copy all static assets (avatars, smilies) from source to export.
     */
    public function copyStaticAssets(): int
    {
        $assetDirs = [
            'img/avatars' => $this->publicDir . 'img/avatars/',
            'img/smilies' => $this->publicDir . 'img/smilies/',
        ];

        $copied = 0;
        foreach ($assetDirs as $sourceDir => $targetDir) {
            $fullSource = $this->sourceDir . $sourceDir;
            if (!is_dir($fullSource)) {
                continue;
            }

            if (!is_dir($targetDir)) {
                @mkdir($targetDir, 0777, true);
            }

            $files = glob($fullSource . '/*.*');
            if ($files === false) {
                continue;
            }
            foreach ($files as $file) {
                $filename = basename($file);
                $target = $targetDir . $filename;
                if (@copy($file, $target)) {
                    $copied++;
                }
            }
        }

        return $copied;
    }
}
