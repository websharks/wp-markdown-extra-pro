<?php
/**
 * Preview utils.
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
 * Preview utils.
 *
 * @since 170126.30913 Initial release.
 */
class Preview extends SCoreClasses\SCore\Base\Core
{
    /**
     * Markdown preview.
     *
     * @since 170126.30913 Initial release.
     */
    public function onAjaxRestActionPreview()
    {
        if (!current_user_can('edit_posts')) {
            s::dieForbidden();
        }
        $data    = s::restActionData();
        $post_id = (int) ($data['post_id'] ?? 0);
        $binary  = (string) file_get_contents('php://input');
        $md      = isset($binary[1]) ? gzinflate(substr($binary, 2)) : '';

        $html = a::transform($md, $post_id, ['cache' => false]);

        if (has_filter('the_content', 'wptexturize')) {
            $html = wptexturize($html);
        }
        exit(json_encode(['success' => true, 'html' => $html]));
    }
}
