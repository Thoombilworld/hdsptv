# HDSPTV News Platform

This repository contains the HDSPTV news CMS, including the public site, admin panel, installer, and language packs. Use the bundled installer (`install/`) to set up the database, create the first admin user, and generate the `.env.php` configuration file.

## Requirements
- PHP 8.1+ with `mysqli`, `session`, `json`, `mbstring`, `curl`, and `openssl` extensions enabled
- MySQL 5.7+ (or MariaDB equivalent)
- Web server with `.htaccess`/mod_rewrite support for clean URLs
- Writable `writable/` directory for cache, logs, and uploads

## Getting started
- Review **README_INSTALL.txt** for step-by-step deployment guidance, permissions, and the list of bundled modules.
- Serve the project from the web root so clean URLs and the included `.htaccess` rules take effect.
- After installation, log in at `/admin/` (default admin is created during install) to manage content, ads, homepage layout, analytics, and legal/settings data.
- Use the new **System Health** screen in `/admin/system.php` to confirm database connectivity, required tables, writable directories, and key shipped files.

## Language and URL configuration
- Default language and favicon are configured in **Admin → Settings** and are propagated to public SEO tags.
- Clean URL helpers power links across posts, categories, tags, search, auth, and dashboard routes; keep `.htaccess` enabled.

## Analytics
- Page views are automatically captured for home, article, category, tag, and search pages with device, browser, and country aggregation.
- Reporter and editor performance cards are available in **Admin → Analytics** when reporter/editor assignments are set on articles.

## File completeness check
The System Health page now surfaces missing critical files (config, installer SQL, CSS, and language packs). After extraction or deployment, you can also compare your directory tree against this repository using your preferred diff tool. The installer and admin dashboards depend on assets under `assets/`, language files in `lang/`, and writable directories in `writable/`, so ensure those paths remain intact.
