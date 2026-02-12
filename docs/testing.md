# Testing

The project includes a PHPUnit test suite with unit and integration tests.

## Running Tests

```bash
# Install dependencies (includes PHPUnit)
docker compose exec php composer install

# Run all tests
docker compose exec php vendor/bin/phpunit

# Run only unit tests (no database required)
docker compose exec php vendor/bin/phpunit --testsuite Unit

# Run only integration tests (requires database)
docker compose exec php vendor/bin/phpunit --testsuite Integration

# Run with coverage report
docker compose exec php vendor/bin/phpunit --coverage-html coverage/
```

## Test Structure

```plain
tests/
├── Unit/                          # Unit tests (no external dependencies)
│   ├── AssetCollectorTest.php     # File copying, caching, URL processing
│   ├── BbcodeParserTest.php       # BBcode conversion, email obfuscation
│   ├── CliOutputTest.php          # Console output formatting
│   ├── ConfigTest.php             # CLI argument parsing, validation
│   ├── SlugGeneratorTest.php      # Slug generation, transliteration
│   ├── TemplateEngineTest.php     # Template rendering, partials
│   └── TranslatorTest.php         # i18n loading, fallback
├── Integration/                   # Integration tests (require database)
│   ├── DatabaseTest.php           # Database connection, queries
│   └── ExportTest.php             # Full export, output validation
├── fixtures/                      # Test data
│   ├── database.sql               # Database dump for testing
│   └── expected-output/           # Expected export output for comparison
└── TestHelper.php                 # Utilities (timestamp normalization, temp dirs)
```

## Test Coverage

Current coverage: **87% lines** (152 tests, 353 assertions)

| Class | Lines |
|-------|-------|
| Config | 100% |
| TemplateEngine | 100% |
| SitemapExporter | 100% |
| BbcodeParser | 98% |
| ForumExporter | 98% |
| UserExporter | 98% |
| Translator | 94% |
| AssetCollector | 94% |
| SlugGenerator | 93% |
| Database | 86% |
| Application | 84% |
| CliOutput | 83% |
| MessageExporter | 22% |

Note: MessageExporter has low coverage because it requires PM tables (`pms_new_topics`) which are not present in the test database.

## Test Fixtures

The `tests/fixtures/` directory contains:

- `database.sql` — A FluxBB database dump with sample data (forums, topics, posts, users)
- `expected-output/` — Expected HTML/JSON output for regression testing

### Updating Fixtures

To update fixtures after intentional changes:

```bash
# Re-run export
docker compose exec php php bin/fluxbb-archiver \
  --host=mariadb --user=fluxbb --password=fluxbb --database=fluxbb \
  --prefix=fluxbb_ --output=/var/www/html/output --lang=en \
  --source-dir=/var/www/html/input/

# Update expected output
cp -r output/public tests/fixtures/expected-output/
cp -r output/private tests/fixtures/expected-output/

# Update database fixture
docker compose exec mariadb mysqldump -u fluxbb -pfluxbb fluxbb > tests/fixtures/database.sql
```

## Timestamp Normalization

Export output contains dynamic timestamps (e.g., "Generated on 2026-02-12 19:01:14 UTC"). The `TestHelper::normalizeTimestamps()` method replaces these with placeholders for deterministic comparison:

```php
$normalized = TestHelper::normalizeTimestamps($htmlContent);
// "2026-02-12T19:01:14+00:00" becomes "{{TIMESTAMP_ISO}}"
// "2026-02-12 19:01:14 UTC" becomes "{{TIMESTAMP_EN}}"
```

## Writing New Tests

### Unit Tests

Unit tests should not require external dependencies (database, network, filesystem outside temp dirs):

```php
<?php
namespace FluxbbArchiver\Tests\Unit;

use FluxbbArchiver\Content\SlugGenerator;
use PHPUnit\Framework\TestCase;

class SlugGeneratorTest extends TestCase
{
    public function testBasicSlugGeneration(): void
    {
        $generator = new SlugGenerator();
        $this->assertSame('hello-world', $generator->generate('Hello World'));
    }
}
```

### Integration Tests

Integration tests may require the database. Mark them with the `@group integration` annotation:

```php
<?php
namespace FluxbbArchiver\Tests\Integration;

use FluxbbArchiver\Database;
use PHPUnit\Framework\TestCase;

/**
 * @group integration
 */
class DatabaseTest extends TestCase
{
    // ...
}
```
