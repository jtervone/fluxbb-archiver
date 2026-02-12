# Changelog

All notable changes to this project will be documented in this file.

The format is based on Keep a Changelog. This project adheres to Semantic Versioning.

## Unreleased

### Added

- Localizable date/time formatting with translation keys (`date_format`, `datetime_format`, `generated_at_format`)
- Finnish date formats (e.g., `15.1.2026 09:42:15`)
- Translated "by" text in topic lists (`by` / `kirjoittanut`)
- Translated footer text (`generated_on` key)
- Slug-based user profile URLs (e.g., `users/john-doe.html` instead of `users/user_42.html`)
- CSS styling for footer links and forum stats links

### Fixed

- Breadcrumbs on public user profiles now use translated "Users" text instead of hardcoded English
- Footer link colors now consistent with site theme
- Front page username link colors now consistent with site theme
