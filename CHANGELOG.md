# Changelog

All notable changes to this project will be documented in this file.

The format is based on Keep a Changelog. This project adheres to Semantic Versioning.

## Unreleased

### Added

- **PHPUnit test suite** with 152 tests and 87% line coverage
  - Unit tests: ConfigTest, SlugGeneratorTest, BbcodeParserTest, TranslatorTest, TemplateEngineTest, CliOutputTest, AssetCollectorTest
  - Integration tests: DatabaseTest, ExportTest
  - Test fixtures: database.sql dump, expected-output for regression testing
  - TestHelper utility for timestamp normalization and temp directory management
- **PCOV extension** in Docker for fast code coverage generation
- **Composer** installed in Docker container for dependency management
- **Auto-cloning FluxBB** from GitHub on first container start via entrypoint script
- **Documentation** split into focused files in `docs/` directory:
  - `docs/installation.md` — Requirements, standard and Docker setup
  - `docs/usage.md` — CLI options, examples, output structure
  - `docs/security.md` — Privacy, email obfuscation, safe workflows
  - `docs/templates.md` — Custom themes, translation overrides
  - `docs/testing.md` — PHPUnit tests, coverage, fixtures
  - `docs/architecture.md` — Project structure, export pipeline
- Standalone Docker development environment (PHP 7.4 FPM, nginx, MariaDB)
- nginx configuration serving live FluxBB at `/input/`, exported files at `/archive/` and `/archive-private/`
- `input/` directory for source FluxBB files (auto-populated from GitHub)
- `output/` directory for generated archive files
- `config.template.php` for FluxBB configuration
- `.env.example` with configurable database credentials and ports
- Localizable date/time formatting with translation keys (`date_format`, `datetime_format`, `generated_at_format`)
- Finnish date formats (e.g., `15.1.2026 09:42:15`)
- Translated "by" text in topic lists (`by` / `kirjoittanut`)
- Translated footer text (`generated_on` key)
- Slug-based user profile URLs (e.g., `users/john-doe.html` instead of `users/user_42.html`)
- CSS styling for footer links and forum stats links

### Changed

- **AssetCollector URL patterns** now built dynamically from `--original-url-base` and `--local-fetch-base` parameters instead of hardcoded values
- **README.md** simplified to marketing overview with links to detailed documentation
- Docker PHP container changed from CLI to FPM for web serving
- CLAUDE.md updated with testing documentation and coverage breakdown
- phpunit.xml configuration with Unit and Integration test suites

### Fixed

- **BBcode list parsing** — fixed regex order so list items are processed before list wrappers
- **Config array indexing** — `--force-public-categories` now re-indexes array after filtering empty values
- Breadcrumbs on public user profiles now use translated "Users" text instead of hardcoded English
- Footer link colors now consistent with site theme
- Front page username link colors now consistent with site theme
- PHP deprecated warnings suppressed via php.ini configuration
