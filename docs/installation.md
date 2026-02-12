# Installation

## Requirements

- PHP 7.4 (FluxBB 1.5 does not support PHP 8.x)
- `ext-mysqli`
- `ext-mbstring`
- Composer

## Standard Installation

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

## Docker Installation (Recommended)

The project includes a complete Docker development environment with PHP 7.4 (FPM), nginx, and MariaDB. FluxBB is automatically cloned from GitHub on first container start.

> **Note:** PHP 7.4 is used because FluxBB 1.5 does not support newer PHP versions.

### Quick Start

```bash
# 1. Copy environment file
cp .env.example .env

# 2. Start the environment (FluxBB is auto-cloned)
docker compose up -d

# 3. Install FluxBB via browser
# Visit http://localhost:8080/input/ and complete the installer
# Database settings: host=mariadb, user=fluxbb, password=fluxbb, database=fluxbb

# 4. Create some test content in FluxBB, then run the archiver
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

### Using Your Own FluxBB Data

To archive an existing FluxBB forum:

```bash
# 1. Copy your FluxBB files to the input directory
rm -rf input/*
cp -r /path/to/your/fluxbb/* input/

# 2. Import your database dump
docker compose exec mariadb mysql -u fluxbb -pfluxbb fluxbb < /path/to/dump.sql

# 3. Update input/config.php with Docker DB credentials:
#    $db_host = 'mariadb';
#    $db_name = 'fluxbb';
#    $db_username = 'fluxbb';
#    $db_password = 'fluxbb';
```

### View Exported Files

After running the archiver, view the results in your browser:

| URL | Description |
|-----|-------------|
| http://localhost:8080/input/ | Live FluxBB forum |
| http://localhost:8080/archive/ | Exported public archive |
| http://localhost:8080/archive-private/ | Exported private archive |

### Stop the Environment

```bash
docker compose down

# To also remove the database volume (for fresh start):
docker compose down -v
```

### Directory Structure

```plain
input/                    # Not tracked in git (auto-populated with FluxBB)
├── config.php            # FluxBB configuration
├── img/
│   ├── avatars/
│   └── smilies/
└── ...

output/                   # Not tracked in git
├── public/               # Safe to publish
└── private/              # Sensitive data
```
