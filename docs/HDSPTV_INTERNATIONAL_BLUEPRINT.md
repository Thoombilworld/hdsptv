# HDSPTV — International Professional News Platform Blueprint

This document is the canonical product/UI blueprint for building HDSPTV as a global, trustworthy, mobile-first digital newsroom.

## 1) Product Goal

HDSPTV should feel:
- Professional
- Global
- Trustworthy
- Fast
- Mobile-first
- Easy for readers
- Powerful for editors/admins
- Scalable for TV/app/multilingual growth

Reference quality target:
- BBC + CNN + Al Jazeera + modern digital newsroom UX.

## 2) Public Information Architecture

Core public pages:
- Homepage
- Live TV / Live News
- Breaking News
- Latest News
- Category pages (World, Politics, Business, Tech, Sports, etc.)
- Article detail page
- Video News
- Photo Gallery
- Special Reports / Investigations
- Search results
- Reporter profile pages
- About / Contact / Advertise / Privacy / Terms

## 3) Newsroom / Admin Information Architecture

Core admin modules:
- Dashboard
- News Management
- Breaking News Manager
- Live TV Control
- Categories / Tags
- Reporters / Editors workflow
- Media / Video Library
- Homepage Section Manager
- Notifications
- Comments Moderation
- SEO Manager
- Ads / Banner Manager
- Analytics / Reports
- Users & Roles
- Settings
- Audit Logs

## 4) Homepage Frame System

### Desktop
- Canvas: 1440
- Content max width: 1280
- Safe margins: 80 left/right
- Grid: 12 columns
- Gutter: 24

### Tablet
- Canvas: 1024
- Content: 920
- Grid: 8 columns
- Gutter: 20

### Mobile
- Canvas: 390
- Content: 358
- Grid: 4 columns
- Gutter: 16

## 5) Spacing System (single scale)

Use only:
`4, 8, 12, 16, 24, 32, 40, 48, 64`

Rules:
- Small element spacing: 8
- Form field spacing: 16
- Card padding: 20 or 24
- Section spacing: 32
- Page block spacing: 40–48

## 6) Header and Main Layout Rules

Header:
- Logo left
- Main nav aligned baseline
- Utility actions right
- Search + language aligned vertically
- Live strip below header
- Height target: 88–104
- No duplicated nav rows

Main layout:
- Left content = 8 columns
- Right sidebar = 4 columns
- Sidebar starts at same top line as hero
- No oversized empty columns

## 7) Component Standards

Reusable first-class components:
- Header
- Category navigation / mega menu
- Breaking ticker
- Hero card
- Standard article card
- Video card
- Category block
- Sidebar widget
- Newsletter box
- Ad slot
- Search box
- Article metadata row
- Reporter snippet
- Footer
- Pagination
- Tag/chip
- Live badge
- Breaking badge

Card token rules:
- Radius: 16
- Padding: 20
- Border: 1px
- Light shadow
- Consistent title/meta alignment

## 8) Article Experience Standards

Article page includes:
- Headline / subheadline
- Main image/video
- Reporter + profile
- Published and updated timestamps
- Category + location
- Readable body
- Related articles
- Share tools
- Tags
- Comments
- Next/previous links
- Ad slots

Readability:
- Comfortable line-height
- Clear paragraph spacing
- Minimal distraction
- Visible update timestamp

## 9) CMS Workflow Model

Content status lifecycle:
- Draft → Submitted → Under Review → Fact Check → Approved → Scheduled → Published → Updated → Archived

Roles:
- Super Admin
- Admin
- Editor-in-Chief
- Senior Editor
- Section Editor
- Reporter
- Video Editor
- Photographer
- SEO Editor
- Moderator
- Ad Manager

Controls:
- Schedule publish
- Version history
- Approval trail
- Feature on homepage
- Mark breaking
- Push notifications
- Correction/update notes
- Embargo support

## 10) Visual Design System

Core palette:
- Primary Red: `#D60000`
- Dark: `#111111` (or `#0B1220`)
- Background: `#F6F7FB`
- Card: `#FFFFFF`
- Border: `#E5E7EB`
- Text secondary: `#6B7280`

Typography:
- H1: 36–40
- H2: 28–32
- H3: 20–24
- Body: 14–16
- Meta: 12–13

## 11) Security / Reliability Baseline

Must-have:
- Secure login
- Role-based access
- Session regeneration
- Login activity logs
- Account lockout strategy
- Audit trails
- CSRF protection
- Input validation
- Backup tools
- File integrity checks

## 12) Performance Baseline

Must-have:
- Fast server response
- Image optimization and lazy loading
- Asset compression
- Caching
- CDN-ready asset strategy
- Query efficiency
- Breaking-news traffic resilience

## 13) SEO / Discoverability Baseline

Must-have:
- Clean URLs
- Article/news/breadcrumb schema
- Open Graph + Twitter cards
- XML sitemap + Google News sitemap
- Canonicals
- Author/category/tag metadata
- Updated timestamps on articles

CMS SEO tools:
- SEO title
- Meta description
- Slug editor
- Social preview

## 14) Delivery Principle

HDSPTV should ship as:
> An international, multilingual, mobile-first digital newsroom platform with a modern editorial CMS, live coverage capabilities, scalable workflow, and a fast trustworthy UI for readers and administrators.

