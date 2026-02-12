# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

FluxBB Archiver (`fluxbb-archiver`) is a PHP CLI tool that exports a FluxBB 1.5 forum database to static HTML and JSON files. It produces a `public/` directory (safe to serve) and a `private/` directory (sensitive data: emails, IPs, private messages, private forums).

## Running the Tool

The tool runs inside a Docker container (PHP 8.2 + MariaDB) from the companion `wordpress` project:

```bash
# Run from the wordpress directory
docker compose exec php php /var/www/html/fluxbb-archiver/bin/fluxbb-archiver \
  --host=mariadb --user=wordpress --password=wordpress --database=wordpress \
  --prefix=fluxbb_ --output=/var/www/html/export-test --lang=fi \
  --source-dir=/var/www/html/splatboard/ \
  --local-fetch-base=http://localhost:8080/splatboard/ \
  --original-url-base=http://splatweb.net/splatboard/
```

After changes, always re-copy the project to `wordpress/src/fluxbb-archiver` before running.

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
- No test suite exists

## Namespace

`FluxbbArchiver\` maps to `src/FluxbbArchiver/` via PSR-4 autoloading.
