# fluxbb-archiver

Export a FluxBB 1.5 forum database to static HTML and JSON files.

Generates a browsable static archive with SEO metadata (Open Graph, Twitter Cards, JSON-LD breadcrumbs, sitemap.xml), paginated topic listings, user profiles, and private message archives. Content is split into separate `public/` and `private/` directories so that sensitive data (private forums, PMs, email addresses, IPs) is never mixed with the public archive.

## Requirements

- PHP 7.4 or newer
- `ext-mysqli`
- `ext-mbstring`
- Composer

## Installation

```bash
git clone <repository-url>
cd fluxbb-archiver
composer install
```

If your host PHP is missing `ext-mysqli` or `ext-mbstring` (common on desktop installs), you can install with:

```bash
composer install --ignore-platform-reqs
```

The extensions must still be available in the environment where you actually run the tool (e.g. inside a Docker container with PHP + mysqli).

## Usage

```bash
php bin/fluxbb-archiver \
  --host=localhost \
  --user=dbuser \
  --password=dbpass \
  --database=forum_db
```

### All options

| Option | Default | Description |
| ------ | ------- | ----------- |
| `--host` | *(required)* | Database host |
| `--user` | *(required)* | Database username |
| `--password` | *(required)* | Database password |
| `--database` | *(required)* | Database name |
| `--port` | `3306` | Database port |
| `--prefix` | `fluxbb_` | FluxBB table prefix |
| `--output` | `./export` | Output directory |
| `--lang` | `fi` | Language (`fi` or `en`) |
| `--base-url` | `https://example.com/` | Base URL for sitemap.xml |
| `--source-dir` | *(empty)* | FluxBB root directory for copying local assets (avatars, smilies) |
| `--local-fetch-base` | *(empty)* | Local URL base for fetching post images (e.g. `http://localhost:8080/forum/`) |
| `--original-url-base` | *(empty)* | Original forum URL to rewrite in post content (e.g. `http://forum.example.com/`) |
| `--template` | `default` | Template/theme name (looks for `tpl/<name>/` directory) |
| `--no-obfuscate-emails` | *(off)* | Disable email obfuscation (obfuscation is enabled by default) |
| `--force-public-categories` | *(empty)* | Comma-separated category names to treat as public even if they have restricted permissions |

### Example with all options

```bash
php bin/fluxbb-archiver \
  --host=mariadb \
  --user=wordpress \
  --password=wordpress \
  --database=wordpress \
  --prefix=fluxbb_ \
  --output=/tmp/forum-export \
  --lang=fi \
  --base-url=https://archive.example.com/ \
  --source-dir=/var/www/html/forum/ \
  --local-fetch-base=http://localhost:8080/forum/ \
  --original-url-base=http://forum.example.com/ \
  --force-public-categories="General,News"
```

## Output structure

```plain
export/
├── public/                    # Safe to serve publicly
│   ├── index.html             # Forum index with categories and forums
│   ├── sitemap.xml            # Sitemap for search engines
│   ├── css/style.css
│   ├── img/                   # Avatars, smilies, downloaded images
│   ├── forums/                # Paginated topic listings per forum
│   ├── topics/                # Individual topic pages with posts
│   ├── users/                 # Public user profiles (no emails or IPs)
│   └── json/                  # JSON data files (forums, topics, users)
└── private/                   # Must NOT be publicly accessible
    ├── index.html             # Private forums index
    ├── forums/                # Private forum topics
    ├── topics/                # Private forum posts
    ├── messages/              # Private message conversations
    └── json/                  # Private JSON data (full user records, PMs)
```

## Output directory and security

**The output directory is wiped and recreated on every run.** All existing files in the output directory are deleted before export starts.

When experimenting with different configurations, always use a temporary or non-public directory as the output path (e.g. `/tmp/forum-export`). Do not point `--output` at a web-accessible directory until you are satisfied with the results, because:

- Each run deletes the previous output first, so a misconfigured run leaves you with incomplete or broken files served to visitors.
- The `private/` subdirectory contains sensitive data (private messages, email addresses, IP addresses, admin notes). If the output is inside a document root, the private directory may be accessible via the web unless you configure your web server to block it.

A safe workflow:

```bash
# 1. Export to a temporary directory
php bin/fluxbb-archiver --host=... --output=/tmp/forum-export

# 2. Inspect the results
ls /tmp/forum-export/public/
# open files in a browser, verify content

# 3. When satisfied, copy only the public directory to your web root
rsync -a --delete /tmp/forum-export/public/ /var/www/html/archive/

# 4. Keep private/ somewhere safe with restricted access
cp -r /tmp/forum-export/private/ /var/backups/forum-private/
```

## Privacy and email obfuscation

By default, email addresses found in forum posts, signatures, and metadata are obfuscated:

```plain
user@example.com  =>  user [at] example [dot] com
```

This applies to:

- Post body content (HTML output)
- User signatures
- `mailto:` links (the link is removed, leaving only obfuscated text)
- JSON data files (message fields, signature fields)
- SEO meta description tags

Email addresses embedded in URLs (e.g. `http://user@host.example.com`) are intentionally left unmodified.

To disable obfuscation, pass `--no-obfuscate-emails`.

The `public/` directory never contains:

- User email addresses or registration IPs
- Password hashes
- Admin notes
- Private forum content
- Private messages

## Running inside Docker

If your FluxBB database is inside a Docker container, you can copy the tool into the container's mounted volume or run it directly:

```bash
# Copy into a mounted volume
cp -r fluxbb-archiver/ /path/to/docker-mount/fluxbb-archiver/

# Run inside the container
docker compose exec php php /var/www/html/fluxbb-archiver/bin/fluxbb-archiver \
  --host=mariadb \
  --user=wordpress \
  --password=wordpress \
  --database=wordpress \
  --prefix=fluxbb_ \
  --output=/tmp/forum-export \
  --lang=fi \
  --source-dir=/var/www/html/splatboard/

# Copy results out
docker cp $(docker compose ps -q php):/tmp/forum-export ./forum-export
```

## Custom templates

The tool uses a template system based on plain PHP files. The default template ships in `tpl/default/`. You can create a custom template by making a new directory under `tpl/` and overriding only the files you want to change — missing files automatically fall back to the default template.

```bash
# Create a custom template
mkdir -p tpl/mytheme

# Override only the stylesheet
cp tpl/default/style.css tpl/mytheme/style.css
# Edit tpl/mytheme/style.css to your liking

# Use it
php bin/fluxbb-archiver --template=mytheme --host=... --output=/tmp/export
```

### Template structure

```plain
tpl/mytheme/
├── style.css              # Stylesheet
├── layout.php             # Page layout (doctype, head, body wrapper, footer)
├── index.php              # Main forum index
├── forum.php              # Forum page (topic table)
├── topic.php              # Topic page (posts)
├── user_profile.php       # User profile
├── user_list.php          # Members list
├── pm_conversation.php    # Private message conversation
├── pm_index.php           # Private messages index
├── pm_empty.php           # No messages fallback
├── private_index.php      # Private forums index
├── private_user_list.php  # Private user data list
├── lang/                  # Translation overrides (optional)
│   ├── fi.php
│   └── en.php
└── partials/
    ├── head.php           # <head> section
    ├── breadcrumbs.php    # Breadcrumb navigation
    ├── pagination.php     # Page navigation
    ├── post.php           # Single post article
    ├── forum_item.php     # Forum row on index page
    ├── topic_row.php      # Topic row in forum table
    └── user_card.php      # User card in members grid
```

Inside templates, `$this` refers to the `TemplateEngine` instance. Use `$this->partial('name', $data)` to render partials and `$this->h($text)` for HTML escaping.

### Translation overrides

Templates can override translation strings by including a `lang/` directory with PHP files that return associative arrays:

```php
<?php
// tpl/mytheme/lang/en.php
return [
    'forum_archive' => 'My Custom Archive',
    'powered_by' => 'Powered by MyTheme',
];
```

Template translations are merged on top of the default translations from `src/FluxbbArchiver/I18n/lang/`.

## Project structure

```plain
fluxbb-archiver/
├── composer.json
├── bin/
│   └── fluxbb-archiver              # CLI entry point
├── tpl/
│   └── default/                     # Default template
│       ├── style.css
│       ├── layout.php
│       ├── index.php, forum.php, topic.php, ...
│       └── partials/
│           ├── head.php, breadcrumbs.php, pagination.php, ...
│           └── post.php, forum_item.php, topic_row.php, user_card.php
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
└── README.md
```

## Development

This project was developed using AI-assisted ("vibe-coded") workflows.

## License

MIT
