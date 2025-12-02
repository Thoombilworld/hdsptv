NEWS HDSPTV – V20 ENTERPRISE PRO FULL SYSTEM
===========================================

This package is designed to be extracted directly into public_html so that
the site loads at:

  https://hdsptv.com/

This V20 build includes:
- Root homepage with advanced blocks:
  - Breaking news ticker
  - Featured slider
  - Multi-category blocks (India / GCC / Kerala / World / Sports)
  - Trending topics
  - Video news & gallery sections (structure ready)
  - Ad slots (top, sidebar, in-content placeholders)
- Admin Panel modules:
  - Dashboard
  - SEO Center
  - Social Media Links
  - Ads Manager
  - Staff Users (admin / editor / reporter)
  - Homepage Layout Manager (basic toggle structure)
  - Logs placeholder
  - Newsletter & Push placeholders (DB+pages ready to extend)
- Frontend user system:
  - Register, login, logout
  - Forgot / reset password (token-based, SMTP integration required)
  - Premium flag for users
- Auto Installer:
  - Creates all DB tables
  - Inserts default categories, SEO, social links (official HDSPTV)
  - Creates admin account
  - Generates .env.php

Install (cPanel)
----------------
1. Upload this ZIP into public_html and extract.
2. Ensure permissions:
   - writable/               -> 0777
   - writable/uploads/       -> 0777
   - writable/logs/          -> 0777
   - public_html/.env.php    -> 0666 (create empty file first if needed)
3. Visit: https://yourdomain.com/install/
4. Fill database + admin details and click "Install".
5. On success, delete the /install folder.
6. Frontend: https://yourdomain.com/
   Admin:    https://yourdomain.com/admin/

Generated: 2025
