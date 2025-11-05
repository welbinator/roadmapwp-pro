## Quick context for AI coding agents

This plugin is a WordPress Pro plugin that extends the free RoadMapWP plugin. It provides Gutenberg blocks, shortcodes, admin pages and integrations (e.g. LearnDash). Focus on small, safe edits and follow existing patterns.

Key locations:
- `wp-roadmap-pro.php` — main plugin bootstrap, defines `RMWP_PLUGIN_VERSION` and requires core files.
- `pro/blocks/` — PHP block registration and editor enqueue glue for compiled block assets.
- `src/` — uncompiled block sources (JS/JSX/SCSS). Edit here when changing block behavior/UI.
- `build/` — compiled block artifacts that are shipped and enqueued by the plugin (committed).
- `app/` and `pro/settings/` — server-side logic, templates, shortcodes and admin pages.

Build & developer workflow
- Node tooling: run `npm install` once. Blocks use `@wordpress/scripts`.
- Common npm scripts (from `package.json`):
  - `npm run build` — compiles blocks for production using `wp-scripts build`.
  - `npm run start` — dev build / watcher via `wp-scripts start` for local block development.
  - `npm run build:css` — runs PostCSS for plugin CSS output.
- Important: `src/` -> `npm run build` -> output appears in `build/`. The `build/` directory is committed; include compiled artifacts in PRs unless instructed otherwise.

PHP / Composer notes
- `composer.json` defines PSR-4 autoloading and contains dev tools (phpstan, phpcs with WPCS). Use `composer install` for developer tooling and `vendor/bin/phpcs` / `vendor/bin/phpstan` if needed.

Patterns and conventions to follow
- Namespaces: PHP files use namespaces under `RoadMapWP\Pro\...` and register functions via `add_action( 'init', __NAMESPACE__ . '\\register_block' )` (i.e. use `__NAMESPACE__` concatenation).
- Block registration: blocks are registered using `register_block_type_from_metadata( $path, [ 'render_callback' => __NAMESPACE__ . '\\block_render', ... ] )`. Example: `pro/blocks/display-ideas-block.php` registers a server-side render block and defines `block_render($attributes)`.
- Editor enqueue: `pro/blocks/blocks.php` enqueues built `build/<block>/index.js` files for the block editor. Use `plugin_dir_url(__FILE__) . '../../build/<block>/index.js'` when adding editor scripts to match existing style.
- Server-side rendering: server-side render functions (like `block_render`) typically use `ob_start()` / `ob_get_clean()` and include shared templates in `app/includes/`.
- Filters & options: the plugin relies on `get_option('wp_roadmap_settings')` and many `apply_filters(...)` hooks (e.g. `roadmapwp_display_ideas_block`, `wp_roadmap_hide_display_ideas_heading`) — prefer using filters rather than changing hard-coded strings.

Integration points
- LearnDash: presence checked with `function_exists('sfwd_lms_has_access')` and behavior branches accordingly. Keep this defensive — do not assume LearnDash exists.
- Shortcodes: `app/shortcodes/*.php` provide shortcode implementations used by blocks and admin pages.

Debugging and releases
- Enable `WP_DEBUG` and check `error_log()` output for server-side errors. Many activation routines use `register_activation_hook` and call `flush_rewrite_rules()`; watch for activation-time side effects.
- Versioning: bump `RMWP_PLUGIN_VERSION` in `wp-roadmap-pro.php` when shipping compiled assets in a release so enqueued scripts/styles use a new cache-busting version.

Small examples to reference
- Block registration (pattern): `register_block_type_from_metadata( $path, [ 'render_callback' => __NAMESPACE__ . '\\block_render' ] );` — see `pro/blocks/display-ideas-block.php`.
- Editor enqueue (pattern): `wp_enqueue_script( 'roadmapwp-pro-display-ideas-block', plugin_dir_url( __FILE__ ) . '../../build/display-ideas-block/index.js', ... );` — see `pro/blocks/blocks.php`.

What to avoid / watch-outs
- Do not change build output locations — `pro/blocks/*.php` expects compiled assets under `build/<block>/index.js` and `block.json` metadata.
- Because compiled `build/` artifacts are tracked, include them in PRs when you change source code under `src/` unless the release process instructs otherwise.

If something is unclear
- I can add examples or expand any area (e.g. a short checklist for editing a block: edit `src/<block>`, run `npm run build`, confirm `build/<block>` changed, bump `RMWP_PLUGIN_VERSION` if releasing).

Please review and tell me if you'd like additional examples (render callbacks, shortcode wiring, or activation hooks) added to this file.