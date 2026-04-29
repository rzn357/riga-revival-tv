# Riga Revival TV — Custom WordPress Theme

A fully custom WordPress theme built for **Riga Revival TV**, a Christian media organization based in Riga, Latvia. The project covers a complete public-facing website with a live broadcast player, deep YouTube API integration, an interactive video library, an organization presentation, a donation page, and a contacts section with a custom-styled Google Map.

---

## Overview

|                      |                                          |
| -------------------- | ---------------------------------------- |
| **Type**             | Custom WordPress theme (no page builder) |
| **Version**          | 1.0.0                                    |
| **Build tool**       | Vite + custom plugins                    |
| **Styling**          | SASS/SCSS with PostCSS                   |
| **PHP architecture** | OOP, PSR-4 autoloading via Composer      |

---

## Pages & Templates

| Template             | Components included                                                            |
| -------------------- | ------------------------------------------------------------------------------ |
| **Homepage**         | Hero banner, Experience section, Our Videos, Animated statistics, Title image  |
| **Videos**           | Dynamic video sections from YouTube API, program cards organized by categories |
| **Videos / Program** | Program banner, multiple YouTube playlists with infinite scroll (AJAX)         |
| **Videos / Single**  | Individual YouTube video player                                                |
| **Watch Live**       | Full-screen Restream.io live broadcast embed                                   |
| **About Us**         | About the company, Team member grid with "Show all" toggle                     |
| **Contacts**         | Hero banner, Feature cards, Contact info, Custom-styled Google Maps            |
| **Donate**           | Donate hero banner, How-to video tutorial, Map image                           |

---

## Key Features

### YouTube API Integration

The theme connects directly to the **YouTube Data API v3** via the official Google PHP client library. It fetches video sections, programs, playlists, and individual videos at render time. Playlist pages support **AJAX-powered infinite scroll** — clicking "Load more" fetches the next batch of videos via a secure `wp_ajax` handler with nonce verification without a full page reload.

### Custom URL Router

A dedicated `Router` class registers **custom WordPress rewrite rules** to create clean URLs for video content:

- `/videos/{section}/{program}/` → program page with playlists
- `/videos/{youtube-video-id}/` → single video player

### Critical CSS Splitting

Assets are split into **critical** (render-blocking, inlined per template) and **non-critical** (deferred, non-render-blocking) groups. Each page template loads only the CSS it needs, with critical styles defined per route in a central configuration file and injected at build time by a custom Vite plugin.

### Performance Optimizer

A dedicated `WP_Performance_Optimizer` class handles:

- Deferred and module script loading
- Non-render-blocking style delivery
- Resource hints (`preconnect`)
- Image and script preloading with `<link rel="preload">`
- WordPress emoji removal
- Selective asset unloading per page (including GiveWP on non-donate pages)

### Security Optimizer

A `Security_Optimizer` class enforces:

- Blocked registration of forbidden usernames (`admin`, `administrator`, `webmaster`)
- `crossorigin="anonymous"` attribute on third-party scripts

### Accessibility Optimizer

An `Accessibility_Optimizer` class patches third-party plugins (e.g., Contact Form 7) to meet WCAG standards — for example, automatically injecting `aria-label` attributes into unlabelled form controls.

### Fluid Typography (Clampify)

The theme integrates a **Clampify** adapter for CSS fluid typography (`clamp()`), with a PostCSS plugin that processes SCSS tokens and replaces them with computed `clamp()` values at build time. A JavaScript accessibility widget allows users to disable fluid scaling when needed.

### Splide.js Sliders

Video playlists and program carousels use **Splide.js** with responsive breakpoints, accessible labels, and programmatic slide injection for the infinite-scroll loader indicator.

### Statistics Animation

An `IntersectionObserver`-based counter animates numeric statistics (subscribers, videos, years, etc.) when the section scrolls into view, counting from 0 to the final value with support for decimals and custom suffixes.

### Interactive Google Map

The Contacts page renders a fully custom-styled Google Map with a custom marker, loaded lazily via `IntersectionObserver` to avoid blocking the initial page render.

### Yoast SEO Integration

A `Yoast_SEO` integration class generates **custom XML sitemaps** for YouTube video content (videos and video programs), registered via Yoast's sitemap API with proper rewrite rules, so all video URLs are indexable by search engines.

### Polylang Multilingual Support

The theme includes a `Polylang` integration stub for registering translatable strings, making the theme ready for multilingual expansion.

### ACF (Advanced Custom Fields)

All dynamic content — hero banners, team members, video sections, statistics, footer assets, Google API keys — is managed through ACF option pages and flexible field groups, keeping the theme fully content-editable without code changes.

### Custom TinyMCE Editor

The classic editor is extended with custom font families (`Cinzel`, `Oswald`, `Open Sans`) and an extended font-size palette, ensuring content editors can match the site's typography inside the WordPress admin.

---

## Architecture

```
riga-revival-tv/
├── src/
│   ├── js/
│   │   ├── main.js               # Non-critical JS entry point
│   │   ├── critical.js           # Critical asset group definitions per template
│   │   ├── components/           # Per-component JS modules (lazy-loaded)
│   │   └── pages/                # Per-page JS modules (lazy-loaded)
│   └── sass/
│       ├── abstracts/            # Variables, mixins, tokens
│       ├── core/                 # Base reset and global styles
│       ├── layouts/              # Header, footer, socials
│       ├── components/           # Per-component SCSS
│       └── pages/                # Per-page SCSS
├── inc/
│   ├── class-riga-revival-tv.php # Main theme bootstrap class
│   ├── class-router.php          # Custom URL rewrite router
│   ├── class-ajax.php            # AJAX handler (load more videos)
│   ├── class-vite.php            # Vite dev server & manifest integration
│   ├── class-autoloader.php      # PSR-4 class autoloader
│   ├── class-shortcodes.php      # Shortcodes registry
│   ├── class-clampify-adapter.php# Clampify fluid typography adapter
│   ├── optimizers/
│   │   ├── class-wp-performance-optimizer.php
│   │   ├── class-accessibility-optimizer.php
│   │   └── class-security-optimizer.php
│   ├── integrations/
│   │   ├── acf/                  # ACF field registration & Gutenberg blocks
│   │   ├── class-yoast-seo.php   # Custom sitemaps for YouTube content
│   │   └── class-polylang.php    # Multilingual string registration
│   └── utils/
│       └── class-helpers.php     # YouTube API helpers, URL builders
├── components/                   # PHP template parts (22 components)
├── dist/                         # Production build output
└── tests/
    ├── html/                     # Playwright HTML structure tests
    ├── accessibility/             # axe-core accessibility audits
    └── page-speed-insights.spec.js # Automated PSI scores via sitemap crawl
```

---

## Tech Stack

**Backend**

- PHP 8+, WordPress theme API
- Composer, PSR-4 autoloading
- Google API PHP Client (YouTube Data API v3)
- Advanced Custom Fields Pro
- Yoast SEO, Polylang, GiveWP, Contact Form 7 (integrations)

**Frontend**

- Vanilla JavaScript (ES modules) + jQuery (for WordPress-compatible components)
- SASS/SCSS
- Splide.js (accessible carousels)
- Google Maps JavaScript API

**Build & Tooling**

- Vite with custom plugins (critical CSS splitting, PHP hot reload, SCSS token replacement, Clampify PostCSS)
- PostCSS + Autoprefixer
- Composer (production autoloader in build)
- Husky (Git hooks)
