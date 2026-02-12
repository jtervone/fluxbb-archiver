# Architecture

## Project Structure

```plain
fluxbb-archiver/
├── bin/
│   └── fluxbb-archiver              # CLI entry point
├── src/
│   └── FluxbbArchiver/
│       ├── Application.php          # Main orchestrator
│       ├── Config.php               # CLI argument parsing and configuration
│       ├── Database.php             # mysqli wrapper
│       ├── Console/
│       │   └── CliOutput.php        # Terminal output with ANSI colors
│       ├── Content/
│       │   ├── AssetCollector.php   # Download/copy images and static files
│       │   ├── BbcodeParser.php     # BBCode to HTML conversion + email obfuscation
│       │   └── SlugGenerator.php    # URL-friendly slug generation
│       ├── Export/
│       │   ├── ForumExporter.php    # Categories, forums, topics, posts
│       │   ├── MessageExporter.php  # Private messages
│       │   ├── SitemapExporter.php  # sitemap.xml generation
│       │   └── UserExporter.php     # User profiles (public + private)
│       ├── Html/
│       │   └── TemplateEngine.php   # Template resolution and rendering
│       └── I18n/
│           ├── Translator.php       # Translation helper
│           └── lang/
│               ├── en.php
│               └── fi.php
├── tpl/
│   └── default/                     # Default template
│       ├── style.css
│       ├── layout.php
│       ├── index.php, forum.php, topic.php, ...
│       └── partials/
│           ├── head.php, breadcrumbs.php, pagination.php, ...
│           └── post.php, forum_item.php, topic_row.php, user_card.php
├── tests/
│   ├── Unit/
│   ├── Integration/
│   └── fixtures/
├── docker/
│   ├── nginx/
│   │   └── default.conf             # nginx config (serves /archive/)
│   └── php/
│       └── Dockerfile               # PHP 7.4 with mysqli, mbstring
├── docs/                            # Documentation
├── input/                           # Source FluxBB files (not in git)
├── output/                          # Generated files (not in git)
├── docker-compose.yml               # Docker services
├── .env.example                     # Environment template
├── composer.json
└── phpunit.xml
```

## Export Pipeline

The export runs sequentially in the following order:

1. **UserExporter::exportPublic()** — Must run first; builds username→ID lookup used by other exporters
2. **ForumExporter::loadStructure()** — Detects public/private forums via group permissions
3. **ForumExporter::exportTopicsAndPosts()** — Paginated (50 topics/page, 25 posts/page)
4. **ForumExporter::exportMainIndex()** + **exportPrivateIndex()**
5. **MessageExporter::export()** — Private messages (checks for `pms_new_topics` table)
6. **UserExporter::exportPrivate()** — Sensitive user data
7. **SitemapExporter::export()**

## Key Components

### Application

Main orchestrator that coordinates all exporters. Entry point: `Application::run()`.

### Config

Parses CLI arguments and provides configuration values to other components.

### Database

Simple mysqli wrapper with methods:
- `query()` — Execute raw SQL
- `fetchAll()` — Get all rows as array
- `fetchOne()` — Get single row
- `tableExists()` — Check if table exists

### BbcodeParser

Converts BBCode to HTML. Also handles:
- Email obfuscation (`user@host` → `user [at] host [dot] com`)
- URL processing for local assets

### TemplateEngine

Resolves templates with fallback chain: custom theme → default theme.

Methods:
- `render('template_name', $data)` — Renders content template
- `partial('name', $data)` — Renders from `partials/` subdirectory
- `renderPage($content, $layoutData)` — Wraps content in `layout.php`

### Translator

Loads translations from `lang/<code>.php` files. Supports:
- Default translations from `src/FluxbbArchiver/I18n/lang/`
- Template overrides from `tpl/<theme>/lang/`
- Merging via `mergeOverrides()`

## Namespace

`FluxbbArchiver\` maps to `src/FluxbbArchiver/` via PSR-4 autoloading (configured in `composer.json`).

## Dependencies

- **No external runtime dependencies** beyond PHP extensions (mysqli, mbstring)
- **PHPUnit 9.6** for testing (dev dependency)
- **PCOV** for code coverage (Docker only)
