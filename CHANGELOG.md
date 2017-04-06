## $v

- Bug fix. On bulk edit PHP warning.
- Enhancing configurable filter tweaks.
- Adding compatibility with Yoast SEO and Yoast SEO Premium.
- Adding `[md url="" cache_expires_after="1 hour" /]` shortcode.
- Adding HTML comment transform marker to `post_content` after filtering.
- Bug fix. When disabling visual editor globally, that should impact profile edits also.
- Adding new methods `addMarker()` and `stripMarker()` for consistent marker handling.
- Bug fix. Improve handling of empty content and avoid showing a marker when editing empty content.
- Enhancing comment marker handling by automatically stripping the marker from raw HTML output by a theme.
- Enhancing PHP full preview option by applying all content filters after the Markdown transform.
- Enhancing filter associated with theme integration, which allows a theme to set custom preview styles.
- Adding compatibility with TypeKit for custom previews integrated by themes.
- Adding support for `<!--raw-->` MD bypass tag.
- Enhancing Markdown parsing in previews.

## v170221.4504

- Adding compatibility with multiple editor instances in WP.
- Enhanced scroll-locking whenever the Markdown editor is enabled.
- Bug fix. Apply Markdown to WooCommerce short description whenever `product` is enabled as a MD post type.

## v170219.18502

- Initial release.
