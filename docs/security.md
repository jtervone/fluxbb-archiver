# Security and Privacy

## Output Directory Behavior

**The output directory is wiped and recreated on every run.** All existing files in the output directory are deleted before export starts.

When experimenting with different configurations, always use a temporary or non-public directory as the output path (e.g. `/tmp/forum-export`). Do not point `--output` at a web-accessible directory until you are satisfied with the results, because:

- Each run deletes the previous output first, so a misconfigured run leaves you with incomplete or broken files served to visitors.
- The `private/` subdirectory contains sensitive data (private messages, email addresses, IP addresses, admin notes). If the output is inside a document root, the private directory may be accessible via the web unless you configure your web server to block it.

## Safe Workflow

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

## Email Obfuscation

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

## Data Separation

The `public/` directory never contains:

- User email addresses or registration IPs
- Password hashes
- Admin notes
- Private forum content
- Private messages

All sensitive data is placed in the `private/` directory, which should never be served publicly.

## Web Server Configuration

If you must place the output in a web-accessible location, configure your web server to block access to the `private/` directory:

### nginx

```nginx
location /archive/private/ {
    deny all;
    return 404;
}
```

### Apache (.htaccess)

```apache
<Directory "/var/www/html/archive/private">
    Require all denied
</Directory>
```
