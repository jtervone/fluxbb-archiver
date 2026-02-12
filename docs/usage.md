# Usage

## Basic Usage

```bash
php bin/fluxbb-archiver \
  --host=localhost \
  --user=dbuser \
  --password=dbpass \
  --database=forum_db
```

## Command Line Options

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

## Example with All Options

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

## Output Structure

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

## Docker Usage

When using Docker, run commands inside the PHP container:

```bash
docker compose exec php php bin/fluxbb-archiver \
  --host=mariadb \
  --user=fluxbb \
  --password=fluxbb \
  --database=fluxbb \
  --prefix=fluxbb_ \
  --output=/var/www/html/output \
  --lang=en \
  --source-dir=/var/www/html/input/
```
