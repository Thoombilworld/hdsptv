# HDSPTV News Platform

This repository contains the HDSPTV news CMS, including the public site, admin panel, installer, and language packs. Use the bundled installer (`install/`) to set up the database, create the first admin user, and generate the `.env.php` configuration file.

## Getting started
- Review **README_INSTALL.txt** for step-by-step deployment guidance, permissions, and the list of bundled modules.
- Serve the project from the web root so clean URLs and the included `.htaccess` rules take effect.
- After installation, log in at `/admin/` to manage content, ads, homepage layout, analytics, and legal/settings data.
- Use the new **System Health** screen in `/admin/system.php` to confirm database connectivity, required tables, and writable directories.

## File completeness check
To verify all shipped files are present after extraction or deployment, compare your directory tree against this repository using your preferred diff tool. The installer and admin dashboards depend on assets under `assets/`, language files in `lang/`, and writable directories in `writable/`, so ensure those paths remain intact.
