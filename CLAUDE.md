# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

FluxBB Archiver (`fluxbb-archiver`) is a PHP CLI tool that exports a FluxBB 1.5 forum database to static HTML and JSON files. It produces a `public/` directory (safe to serve) and a `private/` directory (sensitive data: emails, IPs, private messages, private forums).

## Running the Tool

### Standalone Docker Environment (Recommended)

The project includes its own Docker environment with PHP 7.4 (FPM), nginx, and MariaDB. FluxBB is automatically cloned from GitHub on first start.

```bash
# Setup
cp .env.example .env
docker compose up -d

# FluxBB is auto-installed in input/ directory
# Access FluxBB installer at: http://localhost:8080/input/
# Use DB credentials: host=mariadb, user=fluxbb, password=fluxbb, database=fluxbb

# Run the archiver
docker compose exec php php bin/fluxbb-archiver \
  --host=mariadb --user=fluxbb --password=fluxbb --database=fluxbb \
  --prefix=fluxbb_ --output=/var/www/html/output --lang=en \
  --source-dir=/var/www/html/input/

# View exported files in browser
# Public:  http://localhost:8080/archive/
# Private: http://localhost:8080/archive-private/
# Live FluxBB: http://localhost:8080/input/

# Stop environment
docker compose down
```

### Key URLs

| URL | Description |
|-----|-------------|
| http://localhost:8080/input/ | Live FluxBB forum (PHP 7.4) |
| http://localhost:8080/archive/ | Exported public archive |
| http://localhost:8080/archive-private/ | Exported private archive |

## Architecture

**Entry point:** `bin/fluxbb-archiver` → `Application::run()`

**Export pipeline (sequential, order matters):**

1. `UserExporter::exportPublic()` — must run first, builds username→ID lookup used by other exporters
2. `ForumExporter::loadStructure()` — detects public/private forums via group permissions
3. `ForumExporter::exportTopicsAndPosts()` — paginated (50 topics/page, 25 posts/page)
4. `ForumExporter::exportMainIndex()` + `exportPrivateIndex()`
5. `MessageExporter::export()` — private messages (checks for `pms_new_topics` table)
6. `UserExporter::exportPrivate()` — sensitive user data
7. `SitemapExporter::export()`

**Key dependency:** `UserExporter` is passed to `ForumExporter` and `MessageExporter` for user link generation via `getUserLink()`.

## Template System

Templates are plain PHP files in `tpl/<themename>/`. The `TemplateEngine` resolves files with fallback: custom theme → `tpl/default/`.

- `render('template_name', $data)` — renders a page content template
- `partial('name', $data)` — renders from `partials/` subdirectory
- `renderPage($content, $layoutData)` — wraps content in `layout.php`
- `$this` in templates refers to `TemplateEngine` (access `$this->partial()`, `$this->h()`)

Templates use 2-space indentation for HTML nesting. PHP control structures (`foreach`, `if`) align with their HTML context.

## Translation System

Default translations in `src/FluxbbArchiver/I18n/lang/{en,fi}.php`. Templates can override via `tpl/<theme>/lang/<lang>.php` files, merged via `Translator::mergeOverrides()`.

## Key Conventions

- Every HTML page has a corresponding JSON file in `json/` subdirectory
- Public directory never contains emails, IPs, passwords, admin notes, or private content
- Email obfuscation is on by default (`user@host` → `user [at] host [dot] com`)
- The output directory is wiped on every run (`recursiveDelete`)
- `BbcodeParser::h()` is used for HTML escaping throughout
- User profile URLs use slugified usernames (e.g., `john-doe.html`) via `SlugGenerator`
- Date/time formats are localizable via translation keys (`date_format`, `datetime_format`, `generated_at_format`)
- No external dependencies beyond PHP extensions (mysqli, mbstring)

## Testing

PHPUnit 9.6 test suite with **143 tests** and **86% line coverage**.

### Running Tests

```bash
# Install dependencies
docker compose exec php composer install

# Run all tests
docker compose exec php vendor/bin/phpunit

# Run only unit tests (fast, no DB)
docker compose exec php vendor/bin/phpunit --testsuite Unit

# Run only integration tests (requires DB)
docker compose exec php vendor/bin/phpunit --testsuite Integration

# Generate coverage report
docker compose exec php vendor/bin/phpunit --coverage-text
docker compose exec php vendor/bin/phpunit --coverage-html coverage/
```

### Test Coverage

| Class | Lines | Notes |
|-------|-------|-------|
| Config | 100% | |
| TemplateEngine | 100% | |
| SitemapExporter | 100% | |
| BbcodeParser | 98% | |
| ForumExporter | 98% | |
| UserExporter | 98% | |
| Translator | 94% | |
| SlugGenerator | 93% | |
| Database | 86% | |
| Application | 84% | |
| CliOutput | 83% | error() writes to STDERR |
| AssetCollector | 78% | |
| MessageExporter | 22% | Requires PM tables |

### Test Structure

| Directory | Purpose |
|-----------|---------|
| `tests/Unit/` | Unit tests (no DB): Config, SlugGenerator, BbcodeParser, Translator, TemplateEngine, CliOutput, AssetCollector |
| `tests/Integration/` | Integration tests (require DB): DatabaseTest, ExportTest |
| `tests/fixtures/` | Test data: `database.sql` dump, `expected-output/` for regression testing |
| `tests/TestHelper.php` | Utilities for timestamp normalization, temp directories, file comparison |

### Key Test Classes

**Unit Tests:**
- **ConfigTest** — CLI argument parsing, validation, defaults, path helpers
- **SlugGeneratorTest** — Slug generation, transliteration (Finnish, German, French chars), collision handling
- **BbcodeParserTest** — BBcode tags, HTML escaping, email obfuscation
- **TranslatorTest** — Language loading, fallback chain, merge overrides
- **TemplateEngineTest** — Template resolution, custom/default fallback, partials, layout wrapping
- **CliOutputTest** — Console output formatting (info, success, heading, blank)
- **AssetCollectorTest** — File copying, caching, URL rewriting, static asset copying

**Integration Tests:**
- **DatabaseTest** — Connection, queries, `tableExists()`
- **ExportTest** — Full export pipeline, output structure validation, sitemap, JSON validity

### Timestamp Handling in Tests

Export output contains dynamic timestamps. `TestHelper::normalizeTimestamps()` replaces them with placeholders for deterministic comparison:

- `2026-02-12T19:01:14+00:00` → `{{TIMESTAMP_ISO}}`
- `2026-02-12 19:01:14 UTC` → `{{TIMESTAMP_EN}}`
- `12.2.2026 19:01:14 UTC` → `{{TIMESTAMP_FI}}`

### Updating Fixtures

After intentional output changes, update expected output:

```bash
# Re-export
docker compose exec php php bin/fluxbb-archiver \
  --host=mariadb --user=fluxbb --password=fluxbb --database=fluxbb \
  --prefix=fluxbb_ --output=/var/www/html/output --lang=en \
  --source-dir=/var/www/html/input/

# Update fixtures
cp -r output/public tests/fixtures/expected-output/
cp -r output/private tests/fixtures/expected-output/
```

## Namespace

`FluxbbArchiver\` maps to `src/FluxbbArchiver/` via PSR-4 autoloading.

`FluxbbArchiver\Tests\` maps to `tests/` via PSR-4 autoload-dev.
