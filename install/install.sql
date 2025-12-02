CREATE TABLE IF NOT EXISTS hs_settings (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `key` VARCHAR(100) NOT NULL UNIQUE,
  `value` TEXT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO hs_settings (`key`,`value`) VALUES
('site_title','NEWS HDSPTV'),
('tagline','News for India, GCC, Kerala & the World'),
('logo','assets/images/logo.png'),
('theme','dark'),
('favicon','assets/images/favicon.png'),
('default_language','en'),
('hp_show_breaking','1'),
('hp_show_featured','1'),
('hp_show_trending','1'),
('hp_show_video','1'),
('hp_show_gallery','1'),
('hp_show_ads_top','1'),
('hp_show_ads_inline','1'),
('hp_show_ads_sidebar','1'),
('seo_meta_description','NEWS HDSPTV – GCC, India, Kerala and World news, sports, entertainment and more.'),
('seo_meta_keywords','NEWS HDSPTV, Kerala news, GCC news, India news, Malayalam news'),
('social_facebook','https://www.facebook.com/hdsptv'),
('social_youtube','https://www.youtube.com/@hdsptv'),
('social_instagram','https://www.instagram.com/hdsptv'),
('social_x','https://x.com/hdsptv'),
('social_tiktok','https://www.tiktok.com/@hdsptv'),
('social_linkedin','https://www.linkedin.com/company/hdsptv'),
('social_threads','https://www.threads.net/@hdsptv'),
('social_telegram','https://t.me/hdsptv'),
('homepage_og_image',''),
('default_article_og_image',''),
('seo_schema_enabled','1'),
('seo_default_author','NEWS HDSPTV')
ON DUPLICATE KEY UPDATE value = VALUES(value);

CREATE TABLE IF NOT EXISTS hs_users (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  email VARCHAR(150) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  role ENUM('admin','editor','reporter') NOT NULL DEFAULT 'admin',
  bio TEXT NULL,
  avatar VARCHAR(255) NULL,
  status ENUM('active','inactive','pending') NOT NULL DEFAULT 'active',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS hs_categories (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(150) NOT NULL,
  slug VARCHAR(160) NOT NULL UNIQUE,
  parent_id INT UNSIGNED NULL,
  is_highlight TINYINT(1) NOT NULL DEFAULT 0,
  highlight_order INT UNSIGNED NOT NULL DEFAULT 0,
  show_in_menu TINYINT(1) NOT NULL DEFAULT 1,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX (slug)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO hs_categories (name, slug) VALUES
('India','india'),
('GCC','gcc'),
('Kerala','kerala'),
('World','world'),
('Sports','sports'),
('Qatar News','qatar'),
('UAE News','uae'),
('Saudi News','saudi'),
('Oman News','oman'),
('Kuwait News','kuwait'),
('Bahrain News','bahrain'),
('Entertainment','entertainment'),
('Business','business'),
('Technology','technology'),
('Lifestyle','lifestyle'),
('Health','health'),
('Travel','travel'),
('Auto','auto'),
('Opinion','opinion'),
('Politics','politics'),
('Crime','crime'),
('Education','education'),
('Religion','religion')
ON DUPLICATE KEY UPDATE name = VALUES(name);

CREATE TABLE IF NOT EXISTS hs_posts (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  category_id INT UNSIGNED NULL,
  title VARCHAR(255) NOT NULL,
  slug VARCHAR(260) NOT NULL UNIQUE,
  excerpt TEXT NULL,
  content LONGTEXT NULL,
  type ENUM('article','video','gallery') NOT NULL DEFAULT 'article',
  region ENUM('global','india','gcc','kerala','world','sports') NOT NULL DEFAULT 'global',
  reporter_id INT UNSIGNED NULL,
  editor_id INT UNSIGNED NULL,
  image_main VARCHAR(255) NULL,
  video_url VARCHAR(255) NULL,
  is_breaking TINYINT(1) NOT NULL DEFAULT 0,
  is_featured TINYINT(1) NOT NULL DEFAULT 0,
  is_trending TINYINT(1) NOT NULL DEFAULT 0,
  status ENUM('draft','published') NOT NULL DEFAULT 'draft',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NULL,
  INDEX (slug),
  INDEX (status),
  INDEX (region),
  INDEX (reporter_id),
  INDEX (editor_id),
  CONSTRAINT fk_posts_category FOREIGN KEY (category_id) REFERENCES hs_categories(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS hs_frontend_users (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  email VARCHAR(150) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  is_premium TINYINT(1) NOT NULL DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS hs_password_resets (
  email VARCHAR(150) NOT NULL UNIQUE,
  token VARCHAR(64) NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS hs_ads (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  slot VARCHAR(50) NOT NULL UNIQUE,
  image_url VARCHAR(255) NULL,
  link_url VARCHAR(255) NULL,
  active TINYINT(1) NOT NULL DEFAULT 1,
  code TEXT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO hs_ads (slot, image_url, link_url) VALUES
('global_top','',''),
('global_sidebar','',''),
('global_footer','',''),
('homepage_top','',''),
('homepage_right','',''),
('homepage_inline','',''),
('article_top','',''),
('article_inline','',''),
('article_sidebar','',''),
('category_top','',''),
('category_inline','',''),
('search_inline','',''),
('tag_inline','','')
ON DUPLICATE KEY UPDATE image_url=VALUES(image_url), link_url=VALUES(link_url);

CREATE TABLE IF NOT EXISTS hs_tags (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL UNIQUE,
  slug VARCHAR(120) NOT NULL UNIQUE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS hs_post_tags (
  post_id INT UNSIGNED NOT NULL,
  tag_id INT UNSIGNED NOT NULL,
  PRIMARY KEY (post_id, tag_id),
  CONSTRAINT fk_pt_post FOREIGN KEY (post_id) REFERENCES hs_posts(id) ON DELETE CASCADE,
  CONSTRAINT fk_pt_tag FOREIGN KEY (tag_id) REFERENCES hs_tags(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO hs_posts (category_id,title,slug,excerpt,content,type,region,is_breaking,is_featured,is_trending,status)
VALUES
((SELECT id FROM hs_categories WHERE slug='india' LIMIT 1),
 'Sample India Headline','sample-india-headline',
 'Short intro for India news sample.',
 'Full content for India news sample in English.',
 'article','india',1,1,1,'published'),
((SELECT id FROM hs_categories WHERE slug='gcc' LIMIT 1),
 'Sample GCC Headline','sample-gcc-headline',
 'Short intro for GCC news sample.',
 'Full content for GCC news sample in English.',
 'article','gcc',0,1,0,'published'),
((SELECT id FROM hs_categories WHERE slug='kerala' LIMIT 1),
 'സാമ്പിൾ കേരള വാർത്ത','sample-kerala-malayalam',
 'മലയാളത്തിൽ ഒരു ചെറിയ വാർത്തയുടെ വിവരണം.',
 'ഇത് മലയാളത്തിൽ തയ്യാറാക്കിയ സാമ്പിൾ വാർത്ത ഉള്ളടക്കമാണ്.',
 'article','kerala',0,0,1,'published');

CREATE TABLE IF NOT EXISTS hs_menus (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  location ENUM('header','footer') NOT NULL,
  label VARCHAR(150) NOT NULL,
  url VARCHAR(255) NOT NULL,
  sort_order INT UNSIGNED NOT NULL DEFAULT 0,
  visible TINYINT(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


CREATE TABLE IF NOT EXISTS hs_stats_daily (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  stat_date DATE NOT NULL,
  page_views INT UNSIGNED NOT NULL DEFAULT 0,
  unique_visitors INT UNSIGNED NOT NULL DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY (stat_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS hs_analytics_events (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  event_type VARCHAR(40) NOT NULL DEFAULT 'pageview',
  post_id INT UNSIGNED NULL,
  category_id INT UNSIGNED NULL,
  reporter_id INT UNSIGNED NULL,
  editor_id INT UNSIGNED NULL,
  visitor_hash VARCHAR(80) NULL,
  country VARCHAR(80) NULL,
  device VARCHAR(30) NULL,
  browser VARCHAR(40) NULL,
  user_agent VARCHAR(255) NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX (event_type),
  INDEX (post_id),
  INDEX (category_id),
  INDEX (reporter_id),
  INDEX (editor_id),
  INDEX (visitor_hash),
  INDEX (country),
  INDEX (device),
  INDEX (browser),
  INDEX (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
