# Custom Templates

The tool uses a template system based on plain PHP files. The default template ships in `tpl/default/`. You can create a custom template by making a new directory under `tpl/` and overriding only the files you want to change — missing files automatically fall back to the default template.

## Creating a Custom Template

```bash
# Create a custom template
mkdir -p tpl/mytheme

# Override only the stylesheet
cp tpl/default/style.css tpl/mytheme/style.css
# Edit tpl/mytheme/style.css to your liking

# Use it
php bin/fluxbb-archiver --template=mytheme --host=... --output=/tmp/export
```

## Template Structure

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

## Template API

Inside templates, `$this` refers to the `TemplateEngine` instance:

| Method | Description |
|--------|-------------|
| `$this->partial('name', $data)` | Render a partial template |
| `$this->h($text)` | HTML escape a string |
| `$this->render('template', $data)` | Render another template |

## Translation Overrides

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

## Available Translation Keys

See the default translation files for all available keys:

- `src/FluxbbArchiver/I18n/lang/en.php` - English
- `src/FluxbbArchiver/I18n/lang/fi.php` - Finnish

Common keys include:

| Key | Description |
|-----|-------------|
| `forum_archive` | Page title |
| `forums` | "Forums" heading |
| `topics` | "Topics" column header |
| `posts` | "Posts" column header |
| `last_post` | "Last post" column header |
| `by` | "by" (author attribution) |
| `users` | "Users" / "Members" |
| `generated_on` | Footer timestamp text |
| `date_format` | PHP date format for dates |
| `datetime_format` | PHP date format for timestamps |
