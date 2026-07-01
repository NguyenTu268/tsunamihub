# TsunamiHub

Source code for [tsunamihub.info](https://tsunamihub.info) — a WordPress-based website.

## Overview

This repository contains the WordPress core, theme, and plugin files that power tsunamihub.info.

**Stack**
- WordPress (core)
- PHP
- MySQL/MariaDB
- Theme: [Blocksy](https://creativethemes.com/blocksy/) (with a child theme) — Flatsome is also present as an alternate/legacy theme
- Page builder: Elementor (with Elementor Pro, Premium Addons, ElementsKit, Prime Slider)
- Key plugins: WooCommerce, Advanced Custom Fields, Contact Form 7, WPForms, Rank Math SEO, Custom Post Type UI, Responsive Menu Pro, Google Site Kit, WP Sweep

## Repository structure

```
.
├── wp-admin/          WordPress admin core (unmodified core files)
├── wp-includes/       WordPress core libraries (unmodified core files)
├── wp-content/
│   ├── themes/        Site themes (Blocksy, Blocksy child theme, Flatsome)
│   ├── plugins/       Installed plugins
│   └── mu-plugins/    Must-use plugins
├── wp-config-sample.php   Template for local WordPress configuration
└── .htaccess          Server rewrite rules (WordPress pretty URLs, HTTPS redirect)
```

## Local setup

1. Clone the repository into your local server's document root (Apache/Nginx + PHP + MySQL).
2. Copy `wp-config-sample.php` to `wp-config.php` and fill in your local database credentials and unique auth keys/salts (generate them at the [WordPress secret-key service](https://api.wordpress.org/secret-key/1.1/salt/)).
3. Create a MySQL database and import a copy of the site database (not included in this repository).
4. Point your local domain at the `wp-config.php`/`index.php` root and visit it in a browser to confirm WordPress loads.

> **Note:** `wp-config.php`, the `wp-content/uploads/` media library, and any WordPress database exports are intentionally excluded from version control (see `.gitignore`). They contain live credentials and user-uploaded media that shouldn't be committed to a public repository.

## Notes

- WordPress core (`wp-admin/`, `wp-includes/`) is tracked as-is to make deployments reproducible; avoid hand-editing core files — use `wp-content/` for customizations instead.
- Uploaded media in `wp-content/uploads/` is excluded from this repo and should be synced/backed up separately.
