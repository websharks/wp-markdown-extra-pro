<?php
/**
 * Application.
 *
 * @author @jaswsinc
 * @copyright WP Sharks™
 */
declare(strict_types=1);
namespace WebSharks\WpSharks\WpMarkdownExtra\Pro\Classes;

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
 * Application.
 *
 * @since 170126.30913 Initial release.
 */
class App extends SCoreClasses\App
{
    /**
     * Version.
     *
     * @since 170126.30913
     *
     * @type string Version.
     */
    const VERSION = '170620.35711'; //v//

    /**
     * Constructor.
     *
     * @since 170126.30913 Initial release.
     *
     * @param array $instance Instance args.
     */
    public function __construct(array $instance = [])
    {
        $monospace = "'Hack', 'Menlo', 'Monaco',".
            " 'Consolas', 'Andale Mono', 'DejaVu Sans Mono',".
            ' monospace'; // Needed below.

        $instance_base = [
            '©di' => [
                '©default_rule' => [
                    'new_instances' => [],
                ],
            ],

            '§specs' => [
                '§in_wp'           => false,
                '§is_network_wide' => false,

                '§type'            => 'plugin',
                '§file'            => dirname(__FILE__, 4).'/plugin.php',
            ],
            '©brand' => [
                '©acronym'     => 'WP MD Extra',
                '©name'        => 'WP Markdown Extra',

                '©slug'        => 'wp-markdown-extra',
                '©var'         => 'wp_markdown_extra',

                '©short_slug'  => 'wp-mde',
                '©short_var'   => 'wp_mde',

                '©text_domain' => 'wp-markdown-extra',
            ],

            'hljs' => [
                'cdn_files_list_url' => 'https://cdnjs.com/libraries/highlight.js',
                'style_demos_url'    => 'https://highlightjs.org/static/demo/',
            ],
            '§pro_option_keys' => [],

            '§default_options' => [
                'posts_enable'    => true,
                'comments_enable' => true,
                'post_types'      => [
                    'post',
                    'page',
                    'product',
                    'kb_article',
                    'snippet',
                ],
                'editor_enable'              => false,
                'editor_theme'               => 'light',
                'editor_font_size'           => '1em',
                'editor_font_family'         => $monospace,
                'editor_media_inserts'       => 'md',
                'editor_ide_enable'          => false,
                'editor_preview'             => 'js',
                'editor_preview_frame_side'  => 'right',
                'editor_preview_frame_width' => '',
                'editor_preview_styles'      => '',
                'editor_preview_scripts'     => '',

                'hljs_style'       => 'github',
                'hljs_bg_color'    => '',
                'hljs_font_family' => '',

                'filter_tweaks_enable' => true,
                'texturizer'           => 'smartypants',
                'rich_editing_disable' => true,
            ],

            '§dependencies' => [ // @TODO Convert this into a conflict.
                // Currently, there is no way in the WPSC to define a custom conflict.
                '§others' => [
                    'jetpack_markdown' => [
                        'name'        => __('Disable Jetpack Markdown', 'wp-markdown-extra'),
                        'description' => __('that Jetpack Markdown be disabled', 'wp-markdown-extra'),

                        'test' => function (string $key) {
                            if ($this->Wp->is_jetpack_active && s::jetpackMarkdownEnabled()) {
                                return [
                                    'how_to_resolve' => sprintf(__('<a href="%1$s">change your Jetpack Settings</a> (disable Jetpack Markdown)', 'wp-markdown-extra'), esc_url(admin_url('/admin.php?page=jetpack#/settings'))),
                                    'cap_to_resolve' => 'manage_options',
                                ];
                            }
                        },
                    ],
                ],
            ],
        ];
        parent::__construct($instance_base, $instance);
    }

    /**
     * Early hook setup handler.
     *
     * @since 170126.30913 Initial release.
     */
    protected function onSetupEarlyHooks()
    {
        parent::onSetupEarlyHooks();

        s::addAction('vs_upgrades', [$this->Utils->Installer, 'onVsUpgrades']);
        s::addAction('other_install_routines', [$this->Utils->Installer, 'onOtherInstallRoutines']);
        s::addAction('other_uninstall_routines', [$this->Utils->Uninstaller, 'onOtherUninstallRoutines']);
    }

    /**
     * Other hook setup handler.
     *
     * @since 170126.30913 Initial release.
     */
    protected function onSetupOtherHooks()
    {
        parent::onSetupOtherHooks();

        if ($this->Wp->is_admin) { // Admin-only hooks.
            add_action('admin_menu', [$this->Utils->MenuPage, 'onAdminMenu']);

            add_action('admin_enqueue_scripts', [$this->Utils->Editor, 'onAdminEnqueueScripts']);
            add_filter('wp_editor_settings', [$this->Utils->Editor, 'onWpEditorSettings'], 10, 2);
            add_filter('wp_editor_expand', [$this->Utils->Editor, 'onWpEditorExpand'], 10, 2);
            add_filter('the_editor', [$this->Utils->Editor, 'onTheEditor']);

            add_filter('user_can_richedit', [$this->Utils->Editor, 'onUserCanRichEdit']);
            add_action('edit_user_profile', [$this->Utils->Editor, 'onEditUserProfile']);
            add_action('show_user_profile', [$this->Utils->Editor, 'onShowUserProfile']);
        }
        add_action('init', [$this->Utils->XmlRpc, 'onInit']);
        add_action('xmlrpc_call', [$this->Utils->XmlRpc, 'onXmlRpcCall']);

        add_filter('content_save_pre', [$this->Utils->Markdown, 'onContentSavePre'], -10000);
        add_filter('wp_insert_post_data', [$this->Utils->Markdown, 'onWpInsertPostData'], -10000, 2);
        add_action('save_post', [$this->Utils->Markdown, 'onSavePost'], -10000, 2);

        add_filter('edit_post_content', [$this->Utils->Markdown, 'onEditPostContent'], 10, 2);
        add_filter('_wp_post_revision_fields', [$this->Utils->Markdown, 'onWpPostRevisionFields']);

        add_filter('the_content', [$this->Utils->Markdown, 'onTheContent'], -10001);
        add_filter('get_the_excerpt', [$this->Utils->Markdown, 'onGetTheExcerpt'], -10000);

        if (remove_filter('woocommerce_short_description', 'wc_format_product_short_description', 9999999)) {
            // Fix bug: WooCommerce applies markdown after other filters, which can cause corruption.
            add_filter('woocommerce_short_description', [$this->Utils->Markdown, 'onWcShortDescription'], -10000);
        } elseif (remove_filter('woocommerce_short_description', 'wc_format_product_short_description', -10000)) {
            // The same thing, except this time look for priority `-10000` (e.g., set by <Pre>serve plugin).
            add_filter('woocommerce_short_description', [$this->Utils->Markdown, 'onWcShortDescription'], -10000);
        }
        add_filter('pre_comment_content', [$this->Utils->Markdown, 'onPreCommentContent'], -10000);

        s::registerRestAction('ajax.preview', 'Preview', 'onAjaxRestActionPreview');

        add_shortcode('md', [$this->Utils->Shortcode, 'onShortcode']);
        /*
         * Content filter tweaks based on a multitude of configurable options.
         */
        $options = $this->App->Config->§options; // Collect all options.

        if ($options['posts_enable']) {
            if ($options['texturizer'] !== 'wptexturize') {
                remove_filter('the_content', 'wptexturize');
                remove_filter('the_excerpt', 'wptexturize');
                remove_filter('woocommerce_short_description', 'wptexturize');
            }
        }
        if ($options['comments_enable']) {
            if ($options['texturizer'] !== 'wptexturize') {
                remove_filter('comment_text', 'wptexturize');
            }
        }
        if ($options['filter_tweaks_enable']) {
            if ($options['posts_enable']) {
                // `convert_chars` not on `the_content`.
                // There is no need to remove that filter.
                remove_filter('the_content', 'wpautop');
                remove_filter('the_content', 'shortcode_unautop');
                remove_filter('the_content', 'capital_P_dangit', 11);
                remove_filter('the_content', 'convert_smilies', 20);

                remove_filter('the_excerpt', 'convert_smilies');
                remove_filter('the_excerpt', 'convert_chars');
                remove_filter('the_excerpt', 'wpautop');
                remove_filter('the_excerpt', 'shortcode_unautop');
                remove_filter('the_excerpt', 'capital_P_dangit', 11);

                remove_filter('woocommerce_short_description', 'convert_smilies');
                remove_filter('woocommerce_short_description', 'convert_chars');
                remove_filter('woocommerce_short_description', 'wpautop');
                remove_filter('woocommerce_short_description', 'shortcode_unautop');
            }
            if ($options['comments_enable']) {
                remove_filter('comment_text', 'convert_chars');
                remove_filter('comment_text', 'convert_smilies', 20);
                remove_filter('comment_text', 'wpautop', 30);
                remove_filter('comment_text', 'capital_P_dangit', 31);
                // This filter in core can be removed also.
                remove_filter('comment_excerpt', 'convert_chars');
            }
            add_filter('widget_text', 'do_shortcode'); // Shortcodes in text widgets.
        }
    }
}
