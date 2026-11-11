# WP Sponsors project notes

## Purpose

A portable WordPress plugin for managing sponsors and rendering them through `[wp_sponsors]` on existing or new websites. Keep this small and independent of themes and other plugins.

## Scope

Sponsors have a name (post title), plain-text short description, media-library logo, active flag, and optional website URL. No main content editor, taxonomy, public sponsor landing page, or archive. Only published sponsors with `_wps_active = 1` may render, including explicit ID selections. New sponsors default to inactive.

## Implementation

- `wp-sponsors.php`: plugin bootstrap, private `wps_sponsor` post type, meta box, secure save handler, admin columns, and shortcode.
- `assets/admin.js`: WordPress media-library image selection/removal.
- `assets/sponsors.css`: minimal responsive grid; theme overrides are welcome.
- `README.md`: installation, shortcode contract, and data behavior.

Maintain WordPress 6.0+ and PHP 7.4+ compatibility. Use WordPress APIs. Preserve the shortcode, post type, metadata keys, and CSS hooks unless a deliberate migration is documented. Do not add framework/build dependencies for routine changes.

## Security and data handling

Verify the save nonce and `edit_post` capability; skip autosaves and revisions. Sanitize text and URLs, validate that logo attachment IDs are images, and escape rendered output. Keep links limited to HTTP/HTTPS. Never allow shortcode attributes to bypass published/active filtering. Preserve stored data and media on deactivation and deletion unless a future explicit removal feature is requested.

## Validation

Run `php -l wp-sponsors.php` and `node --check assets/admin.js` when the runtimes are available. Test against the local WordPress installation: sponsor creation/editing, media selection/removal, active toggling, invalid/missing nonces, unauthorized saves, and exclusion of drafts/inactive sponsors. Check shortcode ID filtering/order, escaping, empty output, and responsive presentation. Remove only temporary test fixtures. Distinguish executed checks from manual checks still outstanding.
