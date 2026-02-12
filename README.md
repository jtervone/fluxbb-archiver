# FluxBB Archiver

Export a FluxBB 1.5 forum database to static HTML and JSON files.

![FluxBB Archiver](docs/images/fluxbb-archiver-small.jpg)

## Features

- **Static HTML archive** — Browsable forum archive with no PHP or database required
- **SEO optimized** — Open Graph, Twitter Cards, JSON-LD breadcrumbs, sitemap.xml
- **Privacy focused** — Automatic email obfuscation, separate public/private directories
- **Pagination** — Paginated topic listings and long threads
- **User profiles** — Public member profiles with post history
- **Private messages** — PM archive in protected directory
- **Custom templates** — PHP-based template system with fallback support
- **Localization** — Finnish and English translations, extensible
- **Docker ready** — Complete development environment included

## Quick Start

```bash
# Clone and install
git clone https://github.com/jtervone/fluxbb-archiver.git
cd fluxbb-archiver
composer install

# Run the archiver
php bin/fluxbb-archiver \
  --host=localhost \
  --user=dbuser \
  --password=dbpass \
  --database=forum_db \
  --output=/tmp/forum-export
```

Or use Docker (recommended):

```bash
cp .env.example .env
docker compose up -d

docker compose exec php php bin/fluxbb-archiver \
  --host=mariadb \
  --user=fluxbb \
  --password=fluxbb \
  --database=fluxbb \
  --output=/var/www/html/output
```

## Output

```plain
export/
├── public/           # Safe to serve publicly
│   ├── index.html    # Forum index
│   ├── sitemap.xml   # For search engines
│   ├── forums/       # Topic listings
│   ├── topics/       # Individual threads
│   ├── users/        # Member profiles
│   └── json/         # JSON data files
└── private/          # Keep protected!
    ├── forums/       # Private forum content
    ├── messages/     # Private messages
    └── json/         # Sensitive user data
```

## Documentation

| Document | Description |
|----------|-------------|
| [Installation](docs/installation.md) | Requirements, setup, Docker environment |
| [Usage](docs/usage.md) | CLI options, examples, output structure |
| [Security](docs/security.md) | Privacy, email obfuscation, safe workflows |
| [Templates](docs/templates.md) | Custom themes, translation overrides |
| [Testing](docs/testing.md) | PHPUnit tests, coverage, fixtures |
| [Architecture](docs/architecture.md) | Project structure, export pipeline |

## Requirements

- PHP 7.4 with `ext-mysqli` and `ext-mbstring`
- Composer
- Or: Docker (includes everything)

## License

MIT
