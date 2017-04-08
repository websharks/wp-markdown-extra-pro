<?php
/**
 * Shortcode utils.
 *
 * @author @jaswsinc
 * @copyright WP Sharks™
 */
declare(strict_types=1);
namespace WebSharks\WpSharks\WpMarkdownExtra\Pro\Classes\Utils;

use WebSharks\WpSharks\WpMarkdownExtra\Pro\Classes;
use WebSharks\WpSharks\WpMarkdownExtra\Pro\Interfaces;
use WebSharks\WpSharks\WpMarkdownExtra\Pro\Traits;
#
use WebSharks\WpSharks\WpMarkdownExtra\Pro\Classes\AppFacades as a;
use WebSharks\WpSharks\WpMarkdownExtra\Pro\Classes\SCoreFacades as s;
use WebSharks\WpSharks\WpMarkdownExtra\Pro\Classes\CoreFacades as c;
#
use WebSharks\WpSharks\Core\Classes as SCoreClasses;
use WebSharks\WpSharks\Core\Interfaces as SCoreInterfaces;
use WebSharks\WpSharks\Core\Traits as SCoreTraits;
#
use WebSharks\Core\WpSharksCore\Classes as CoreClasses;
use WebSharks\Core\WpSharksCore\Classes\Core\Base\Exception;
use WebSharks\Core\WpSharksCore\Interfaces as CoreInterfaces;
use WebSharks\Core\WpSharksCore\Traits as CoreTraits;
#
use function assert as debug;
use function get_defined_vars as vars;

/**
 * Shortcode utils.
 *
 * @since 170408.19959 Initial release.
 */
class Shortcode extends SCoreClasses\SCore\Base\Core
{
    /**
     * Shortcode.
     *
     * @since 170408.19959 Initial release.
     *
     * @param array|string $atts      Shortcode attributes.
     * @param string|null  $content   Shortcode content.
     * @param string       $shortcode Shortcode name.
     */
    public function onShortcode($atts = [], $content = '', $shortcode = ''): string
    {
        $atts      = is_array($atts) ? $atts : [];
        $content   = (string) $content;
        $shortcode = (string) $shortcode;

        $default_atts = [
            'url'                 => '',
            'iv'                  => '',
            'cache_expires_after' => '1 hour',
        ];
        $atts = shortcode_atts($default_atts, $atts, $shortcode);
        $atts = array_map('strval', $atts); // Force strings.

        $html                = ''; // Initialize.
        $url                 = $atts['url']; // Markdown.
        $iv                  = $atts['iv']; // Incremented version.
        $post_id             = (int) (is_singular() ? get_the_ID() : 0);
        $cache_expires_after = $atts['cache_expires_after'] ?: $default_atts['cache_expires_after'];

        if ($url && ($md = $this->urlContents($url, compact('iv', 'cache_expires_after')))) {
            $html = a::transform($md, $post_id, ['cache' => true]);
        }
        return $html; // From markdown.
    }

    /**
     * Get URL contents.
     *
     * @since 170408.19959 Initial release.
     *
     * @param string $url  URL to get contents of.
     * @param array  $args Behavioral args.
     *
     * @return string URL contents.
     */
    protected function urlContents(string $url, array $args = []): string
    {
        $default_args = [
            'iv'                  => '',
            'cache_expires_after' => '1 hour',
        ];
        $args += $default_args; // Merge with defaults.
        // Note: `iv` is an incremented version; allows cache busting.
        // i.e., The site owner can force a cache update by altering the `iv`.

        $cache_sha1          = sha1($url.serialize($args));
        $cache_sha1_shard_id = c::sha1ModShardId($cache_sha1, true);

        $cache_dir             = $this->App->Config->©fs_paths['©cache_dir'].'/shortcode/urls/'.$cache_sha1_shard_id;
        $cache_dir_permissions = $this->App->Config->©fs_permissions['©transient_dirs'];
        $cache_file            = $cache_dir.'/'.$cache_sha1.'.md';

        $cache_expires_after = (string) $args['cache_expires_after'];
        $cache_expires_after = $cache_expires_after ?: $default_args['cache_expires_after'];

        if (is_file($cache_file) && filemtime($cache_file) >= strtotime('-'.$cache_expires_after)) {
            return $contents = (string) file_get_contents($cache_file);
        } // Use the already-cached URL contents.

        $contents = wp_remote_retrieve_body(wp_remote_get($url));
        $contents = (string) $contents; // Force string.

        if (!$contents && is_file($cache_file)) {
            $contents = (string) file_get_contents($cache_file);
        } // On failure to get *new* contents, use the old contents.
        // i.e., Don't allow HTTP connection failures to nullify contents.

        // At the same time, this should trigger an update to the cache file.
        // i.e., To avoid repeat attempts by updating the cache file modified time.

        if (!is_dir($cache_dir)) {
            mkdir($cache_dir, $cache_dir_permissions, true);

            if (!is_dir($cache_dir)) {
                debug(0, c::issue(vars(), 'Unable to create cache directory.'));
                return $contents; // Soft failure.
            }
        } // Cache directory exists.
        file_put_contents($cache_file, $contents);

        return $contents;
    }
}
