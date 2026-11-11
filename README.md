# WP Sponsors

A small, standalone WordPress plugin for managing website, event, or association sponsors and displaying them with shortcodes. No theme or third-party plugin dependency.

## Requirements and installation

WordPress 6.0+ and PHP 7.4+.

1. Copy the `wp-sponsors` folder into `wp-content/plugins/` (or ZIP the folder and upload it through **Plugins → Add New → Upload Plugin**).
2. Activate **WP Sponsors** in WordPress.
3. Open **Sponsors → Add New**.

## Managing sponsors

- **Name:** use the title field.
- **Short description:** plain text with line breaks; the main content editor is disabled.
- **Logo:** choose or upload an image through the WordPress media library.
- **Active:** check this to allow the sponsor to appear on the website. New sponsors start inactive.
- **Website URL:** an optional HTTP/HTTPS address. The logo and name link to it in the same tab by default.

Publish the sponsor and check **Active** to display it. Draft, private, trashed, and inactive sponsors are excluded, even when explicitly requested by ID. Optional fields can be left blank. The Sponsors list includes active status and a copyable individual shortcode.

There are no sponsor taxonomies, public single pages, archives, or search results. Editing uses normal WordPress post permissions. Sponsors are not exposed through the REST API.

## Displaying sponsors

Add a **Shortcode** block to a page or post:

```text
[wp_sponsors]
```

This displays all published, active sponsors alphabetically in three columns, including logo, name, and description. The layout becomes one column on screens up to 600px wide. With no matching sponsors, the shortcode returns no markup.

```text
[wp_sponsors ids="123"]
[wp_sponsors ids="123,456,789" orderby="post__in"]
[wp_sponsors columns="4" show_name="no" show_description="no"]
[wp_sponsors limit="6" orderby="date" order="DESC" new_tab="yes"]
```

| Attribute | Default | Meaning |
| --- | --- | --- |
| `ids` | empty | Comma-separated sponsor IDs; empty selects all eligible sponsors. |
| `limit` | `-1` | Positive maximum number of sponsors; zero/negative means all. |
| `columns` | `3` | Grid columns, clamped to 1–6. |
| `orderby` | `title` | `title`, `date`, `rand`, or `post__in` (preserves supplied ID order). |
| `order` | `ASC` | `ASC` or `DESC`; not applicable to random or supplied ID ordering. |
| `show_name` | `yes` | Show the sponsor name. |
| `show_description` | `yes` | Show the description. |
| `show_logo` | `yes` | Show the logo. |
| `new_tab` | `no` | Open sponsor links in a new tab with `noopener noreferrer`. |

Boolean attributes accept `yes`, `true`, or `1` to enable. Use `no` to disable. Links use `rel="sponsored"`. For logo-only displays, add logos to every sponsor; entries without a logo otherwise have no visible identity.

In a theme template:

```php
echo do_shortcode( '[wp_sponsors columns="4"]' );
```

## Styling and data

The small front-end stylesheet loads globally so shortcodes in templates and widgets also receive styling. Override `.wp-sponsors`, `.wp-sponsors__sponsor`, `.wp-sponsors__logo`, `.wp-sponsors__name`, and `.wp-sponsors__description` in your theme. Logos retain their aspect ratio and have a 160px maximum display height.

Sponsors use post type `wps_sponsor` with metadata `_wps_description`, `_wps_logo` (attachment ID), `_wps_active` (`1`/`0`), and `_wps_url`. Media stays in the normal library. Deactivation or deletion of the plugin preserves sponsors and media; reinstalling restores access. No rewrite flush or database migration is needed.

## Development

See [agent.md](agent.md) for project scope, implementation details, and validation guidance. There is no build step; the plugin uses PHP, JavaScript, and CSS directly.
