# Flarum Showcase

[![Floxum](https://floxum.com/extension/ernestdefoe/flarum-showcase/badge/name)](https://floxum.com/extension/ernestdefoe/flarum-showcase)
[![Version](https://floxum.com/extension/ernestdefoe/flarum-showcase/badge/highest-version)](https://floxum.com/extension/ernestdefoe/flarum-showcase)
[![Downloads](https://floxum.com/extension/ernestdefoe/flarum-showcase/badge/downloads)](https://floxum.com/extension/ernestdefoe/flarum-showcase)
[![Review](https://floxum.com/extension/ernestdefoe/flarum-showcase/badge/review)](https://floxum.com/extension/ernestdefoe/flarum-showcase)
[![License](https://floxum.com/extension/ernestdefoe/flarum-showcase/badge/license)](https://floxum.com/extension/ernestdefoe/flarum-showcase)

A tag-driven feature rail for the Flarum 2 forum index. Pick a set of primary tags and a set of secondary tags in the admin panel — any discussion that carries one from each appears as a card above the discussion list, with the cover image pulled from the linked GitHub repository's social-preview when the opening post embeds a [`gh-readme`](https://github.com/ernestdefoe/gh-readme) shortcode.

![License](https://img.shields.io/badge/license-MIT-blue.svg)
![Flarum](https://img.shields.io/badge/Flarum-%5E2.0-orange.svg)
![PHP](https://img.shields.io/badge/PHP-%5E8.3-777bb4.svg)
![TypeScript](https://img.shields.io/badge/TypeScript-strict-3178c6.svg)

## What it does

Designed for support sites where each thread is "about" a specific extension or theme:

- **Primary tag** = the specific extension/theme slug (e.g. `theme-toggle`, `gh-readme`, `respawn`).
- **Secondary tag** = the umbrella category (e.g. `Extensions`, `Themes`).

A discussion shows up in the showcase rail iff it carries **at least one whitelisted primary tag AND at least one whitelisted secondary tag**. Empty whitelist on either side ⇒ nothing qualifies (admin must explicitly opt in).

## Features

- **Two layout styles.** Carousel (horizontal snap-scroll with prev/next buttons) or grid (wraps to multiple rows). Toggle in the admin panel.
- **Automatic cover images.** When the opening post contains a `[gh-readme repo="owner/name"]` shortcode (from [`ernestdefoe/gh-readme`](https://github.com/ernestdefoe/gh-readme)), the card pulls the repo's GitHub social-preview as the cover. No manual image uploads.
- **Hashed-gradient fallback.** Discussions without a `gh-readme` shortcode get a stable, deterministic gradient + first-letter monogram — so every card looks intentional even before authors update their posts.
- **Real tag pickers in the admin.** Uses Flarum 2's bundled `TagSelectionModal` via the `flarum-tags.select-tags` setting type. Each picker is filtered to either primary or secondary tags so admins can't pick the wrong kind.
- **Localized.** Every visible string flows through `app.translator.trans(...)` and lives in `locale/en.yml` — drop in additional locale files (or use [`fof/linguist`](https://github.com/FriendsOfFlarum/linguist)) to translate.
- **Strictly typed.** TypeScript end-to-end, type-checked against Flarum core's `.d.ts` and `flarum/tags`'s `.d.ts`.

## Compatibility

- Flarum core `^2.0`
- PHP `^8.3`
- Requires `flarum/tags`
- Pairs nicely with — but does **not** require — [`ernestdefoe/gh-readme`](https://github.com/ernestdefoe/gh-readme). Without it the synthesized gradient fallback is used for every card.

## Install

```bash
composer require ernestdefoe/flarum-showcase
php flarum cache:clear
```

Then enable **Showcase** from the admin panel's Extensions page.

## Configure

Admin → Extensions → Showcase exposes:

| Setting | What it does |
|---|---|
| **Display style** | Carousel (default) or Grid. |
| **Qualifying primary tags** | Multi-select tag picker, restricted to primary tags. A discussion needs one of these to qualify. |
| **Qualifying secondary tags** | Multi-select tag picker, restricted to secondary tags. A discussion needs one of these too (AND, not OR). |
| **Maximum cards** | Hard cap on how many cards render (default 12). |
| **Sort order** | Latest activity, most replied, or pinned first then latest. |

## Cover image resolution

For each qualifying discussion, the backend reads the opening post's raw content and runs a regex looking for `[gh-readme repo="owner/name"]`. If found, the cover URL is set to `https://opengraph.githubassets.com/1/owner/name` — the same image GitHub serves as the repo's social preview. Otherwise the frontend renders the hashed-gradient fallback.

This means `ernestdefoe/gh-readme` does **not** have to be installed locally for showcase to function — Showcase only parses the shortcode string, never invokes any gh-readme PHP. But you'll want gh-readme installed if you want the shortcodes to actually render as live READMEs inside the discussions.

## Development

```bash
cd js
npm install
npm run build           # production bundle (writes js/dist/forum.js + admin.js)
npm run dev             # watch mode for development
npm run check-typings   # tsc --noEmit, requires `composer install` at repo root first
```

The TypeScript path mapping in `js/tsconfig.json` resolves `flarum/*` from `../vendor/flarum/core/js/dist-typings/` and `flarum/tags/*` from `../vendor/flarum/tags/js/dist-typings/`. Run `composer install` at the repo root first.

### Project layout

```
extend.php                                Flarum extension bootstrap (settings, API field, frontend assets)
composer.json                             Package manifest (suggests ernestdefoe/gh-readme)
locale/en.yml                             User-facing strings (forum, lib, admin)
less/forum.less                           Showcase / card / carousel / grid styles
src/
  Api/AddCoverImageField.php              JSON:API field on Discussion → showcaseCoverUrl
  CoverImage/Resolver.php                 Parses gh-readme shortcode → og:image URL
  Showcase/Qualifier.php                  AND-rule over primary + secondary tag ID whitelists
js/
  forum.ts / admin.ts                     Webpack entry points (note: admin.ts uses `export { default as extend }`)
  tsconfig.json                           Extends flarum-tsconfig with flarum/tags path mapping
  src/forum/                              IndexPage extender, ShowcaseSection, ShowcaseCard, ShowcaseCarousel, ShowcaseGrid, loader, util
  src/admin/index.ts                      Two SelectTags pickers + display-style toggle + sort + max
```

## License

MIT
