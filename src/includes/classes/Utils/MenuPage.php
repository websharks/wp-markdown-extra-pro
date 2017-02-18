<?php
/**
 * Menu page utils.
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
 * Menu page utils.
 *
 * @since 170126.30913 Initial release.
 */
class MenuPage extends SCoreClasses\SCore\Base\Core
{
    /**
     * On `admin_menu` hook.
     *
     * @since 170126.30913 Initial release.
     */
    public function onAdminMenu()
    {
        s::addMenuPageItem([
            'parent_page'   => 'options-general.php',
            'menu_title'    => __('Markdown', 'wp-markdown-extra'),
            'template_file' => 'admin/menu-pages/options/default.php',

            'meta_links' => ['restore' => true],
            'tabs'       => [
                'default' => sprintf(__('%1$s', 'wp-markdown-extra'), esc_html($this->App->Config->©brand['©name'])),
                'editor'  => __('Editor', 'wp-syntax-highlight'),
                'preview' => __('Preview', 'wp-syntax-highlight'),
                'tweaks'  => __('Tweaks', 'wp-syntax-highlight'),
            ],
        ]);
    }
}
