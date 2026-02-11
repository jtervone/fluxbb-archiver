<?php

declare(strict_types=1);

namespace FluxbbArchiver;

class Config
{
    public string $host;
    public int $port;
    public string $user;
    public string $password;
    public string $database;
    public string $prefix;
    public string $outputDir;
    public string $lang;
    public string $baseUrl;
    public string $sourceDir;
    public string $localFetchBase;
    public string $originalUrlBase;
    public bool $obfuscateEmails;
    public string $template;
    /** @var string[] */
    public array $forcePublicCategories;

    public function __construct(
        string $host,
        int $port,
        string $user,
        string $password,
        string $database,
        string $prefix,
        string $outputDir,
        string $lang,
        string $baseUrl,
        string $sourceDir,
        string $localFetchBase,
        string $originalUrlBase,
        bool $obfuscateEmails,
        string $template,
        array $forcePublicCategories
    ) {
        $this->host = $host;
        $this->port = $port;
        $this->user = $user;
        $this->password = $password;
        $this->database = $database;
        $this->prefix = $prefix;
        $this->outputDir = rtrim($outputDir, '/') . '/';
        $this->lang = $lang;
        $this->baseUrl = rtrim($baseUrl, '/') . '/';
        $this->sourceDir = $sourceDir;
        $this->localFetchBase = $localFetchBase;
        $this->originalUrlBase = $originalUrlBase;
        $this->obfuscateEmails = $obfuscateEmails;
        $this->template = $template;
        $this->forcePublicCategories = $forcePublicCategories;
    }

    public function publicDir(): string
    {
        return $this->outputDir . 'public/';
    }

    public function privateDir(): string
    {
        return $this->outputDir . 'private/';
    }

    /**
     * Parse CLI arguments into a Config instance.
     *
     * @param string[] $argv
     */
    public static function fromArgv(array $argv): self
    {
        $options = [];
        foreach ($argv as $arg) {
            if (strpos($arg, '--') === 0) {
                $arg = substr($arg, 2);
                $eqPos = strpos($arg, '=');
                if ($eqPos !== false) {
                    $key = substr($arg, 0, $eqPos);
                    $value = substr($arg, $eqPos + 1);
                } else {
                    $key = $arg;
                    $value = true;
                }
                $options[$key] = $value;
            }
        }

        // Validate required options
        $required = ['host', 'user', 'password', 'database'];
        $missing = [];
        foreach ($required as $key) {
            if (!isset($options[$key]) || $options[$key] === true) {
                $missing[] = '--' . $key;
            }
        }
        if (!empty($missing)) {
            throw new \InvalidArgumentException(
                'Missing required options: ' . implode(', ', $missing) . "\n\n" . self::usageText()
            );
        }

        $forcePublic = [];
        if (isset($options['force-public-categories']) && is_string($options['force-public-categories'])) {
            $forcePublic = array_map('trim', explode(',', $options['force-public-categories']));
            $forcePublic = array_filter($forcePublic, fn(string $v): bool => $v !== '');
        }

        return new self(
            (string)$options['host'],
            isset($options['port']) ? (int)$options['port'] : 3306,
            (string)$options['user'],
            (string)$options['password'],
            (string)$options['database'],
            isset($options['prefix']) ? (string)$options['prefix'] : 'fluxbb_',
            isset($options['output']) ? (string)$options['output'] : './export',
            isset($options['lang']) ? (string)$options['lang'] : 'fi',
            isset($options['base-url']) ? (string)$options['base-url'] : 'https://example.com/',
            isset($options['source-dir']) ? (string)$options['source-dir'] : '',
            isset($options['local-fetch-base']) ? (string)$options['local-fetch-base'] : '',
            isset($options['original-url-base']) ? (string)$options['original-url-base'] : '',
            !isset($options['no-obfuscate-emails']),
            isset($options['template']) ? (string)$options['template'] : 'default',
            $forcePublic
        );
    }

    public static function usageText(): string
    {
        return <<<'USAGE'
Usage: fluxbb-archiver [OPTIONS]

Required:
  --host=HOST           Database host
  --user=USER           Database username
  --password=PASS       Database password
  --database=DB         Database name

Optional:
  --port=3306           Database port (default: 3306)
  --prefix=fluxbb_      Table prefix (default: fluxbb_)
  --output=./export     Output directory (default: ./export)
  --lang=fi             Language: fi or en (default: fi)
  --base-url=URL        Base URL for sitemap (default: https://example.com/)
  --source-dir=PATH     FluxBB root directory for copying local assets (avatars, smilies)
  --local-fetch-base=URL  Local URL base for fetching images (e.g. http://localhost:8080/splatboard/)
  --original-url-base=URL Original URL base to rewrite (e.g. http://splatweb.net/splatboard/)
  --no-obfuscate-emails  Disable email obfuscation in posts and signatures (enabled by default)
  --template=default    Template/theme name (default: default). Looks for tpl/<name>/
  --force-public-categories="Cat1,Cat2"
                        Comma-separated category names to force as public
USAGE;
    }
}
