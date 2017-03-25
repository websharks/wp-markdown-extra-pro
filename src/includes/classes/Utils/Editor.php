<?php
/**
 * Editor utils.
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
 * Editor utils.
 *
 * @since 170126.30913 Initial release.
 */
class Editor extends SCoreClasses\SCore\Base\Core
{
    /**
     * Is applicable?
     *
     * @since 170126.30913 Initial release.
     *
     * @return bool True if applicable.
     */
    public function isApplicable(): bool
    {
        return (bool) $this->applicableSettings();
    }

    /**
     * On `admin_enqueue_scripts` hook.
     *
     * @since 170126.30913 Initial release.
     */
    public function onAdminEnqueueScripts()
    {
        if (!$this->isApplicable()) {
            return; // Not applicable.
        } elseif (!($settings = $this->applicableSettings())) {
            return; // Not applicable.
        }
        $deps   = [ // Always.
            'jquery',
            'underscore',
            'highlight-js',
            'highlight-js-lang-wp',
            'media-upload',
        ];
        s::enqueueHighlightJsLibs(null);

        if ($settings['ideEnable']) {
            $ace_mode  = basename($settings['ide']['mode']);
            $ace_theme = basename($settings['ide']['theme']);
            s::enqueueAceLibs($ace_mode, $ace_theme);

            $deps[] = 'ace'; // Deps.
            $deps[] = 'ace-ext-linking';
            $deps[] = 'ace-ext-searchbox';
            $deps[] = 'ace-ext-spellcheck';
            $deps[] = 'ace-ext-language_tools';
            $deps[] = 'ace-mode-'.$ace_mode;
            $deps[] = 'ace-theme-'.$ace_theme;
        }
        if ($settings['previewMethod'] === 'php') {
            s::enqueuePakoLibs('deflate');
            $deps[] = 'pako-deflate';
        } else { // Default preview handler.
            s::enqueueMarkdownItLibs();
            $deps[] = 'markdown-it';
        }
        $slug = $this->App->Config->©brand['©slug'];
        $var  = $this->App->Config->©brand['©var'];

        s::enqueueLibs(__METHOD__, [
            'styles' => [
                $slug.'-editor' => [
                    'ver' => $this->App::VERSION,
                    'url' => c::appUrl('/client-s/css/admin/editor.min.css'),
                ],
            ],
            'scripts' => [
                $slug.'-editor' => [
                    'deps'     => $deps,
                    'ver'      => $this->App::VERSION,
                    'url'      => c::appUrl('/client-s/js/admin/editor.min.js'),
                    'localize' => [
                        'key'  => 'sxz4aq7w68twt86g8ye5m3np7nrtguw8EditorData',
                        'data' => [
                            'brand'    => [
                                'slug' => $slug,
                                'var'  => $var,
                            ],
                            'settings' => $settings, // Filterable.
                            // See filter below in `applicableSettings()`.

                            'i18n' => [
                                'toggle'                 => __('Toggle', 'wp-markdown-extra'),

                                'preview'                => __('Preview', 'wp-markdown-extra'),
                                'sitePreview'            => __('Site Preview', 'wp-markdown-extra'),
                                'fullscreenSplitPreview' => __('Fullscreen Split Preview', 'wp-markdown-extra'),
                                'fullscreen'             => __('Fullscreen', 'wp-markdown-extra'),

                                'saveDraft'              => __('Save Draft', 'wp-markdown-extra'),
                                'update'                 => __('Update', 'wp-markdown-extra'),
                                'publish'                => __('Publish', 'wp-markdown-extra'),
                                'trash'                  => __('Trash', 'wp-markdown-extra'),
                            ],
                        ],
                    ],
                ],
            ],
        ]);
    }

    /**
     * Applicable settings.
     *
     * @since 170126.30913 Initial release.
     *
     * @return array Settings if applicable.
     */
    protected function applicableSettings(): array
    {
        if (($settings = &$this->cacheKey(__FUNCTION__)) !== null) {
            return $settings; // Cached this already.
        } elseif (!s::getOption('editor_enable')) {
            return $settings = []; // Not applicable.
        } elseif (!in_array(s::menuPageNow(), ['post-new.php', 'post.php'], true)) {
            return $settings = []; // Not applicable.
        } elseif (!in_array(s::currentMenuPagePostType(), s::getOption('post_types'), true)) {
            return $settings = []; // Not applicable.
        }
        $is_applicable_filter = s::applyFilters('is_applicable', null);

        if ($is_applicable_filter === false) {
            return $settings = []; // Nope.
        }
        $post_id            = (int) ($_REQUEST['post'] ?? 0);
        $hljs_style_data    = s::highlightJsStyleData(s::getOption('hljs_style'));
        $hljs_style_url     = sprintf($hljs_style_data['url'], urlencode($hljs_style_data['version']));
        $preview_styles_url = c::appUrl('/client-s/js/admin/editor/preview/styles.min.css?v='.urlencode($this->App::VERSION));

        return $settings = s::applyFilters('editor_settings', [
            'postId'       => $post_id, // If not new.

            'theme'        => s::getOption('editor_theme'),
            'fontSize'     => s::getOption('editor_font_size'),
            'fontFamily'   => s::getOption('editor_font_family'),

            'mediaInserts' => s::getOption('editor_media_inserts'),

            'ideEnable'    => s::getOption('editor_ide_enable') && !wp_is_mobile(),
            // The following `ide` options are passed directly to ACE.
            'ide'          => [
                'mode'  => 'ace/mode/markdown',
                'theme' => 'ace/theme/'.s::getOption('editor_theme'),

                'fontSize'    => s::getOption('editor_font_size'),
                'fontFamily'  => s::getOption('editor_font_family'),

                'cursorStyle'       => 'slim', // `ace`, `slim`, `wide`.
                'enableMultiselect' => true, // Multiple cursors.
                'dragEnabled'       => false,

                'minLines'                  => 0,
                'maxLines'                  => 0,
                'vScrollBarAlwaysVisible'   => false,

                'firstLineNumber'           => 1,
                'showLineNumbers'           => true,
                'newLineMode'               => 'unix',

                'tabSize'                   => 2,
                'useSoftTabs'               => true,

                'wrap'                      => true,
                'indentedSoftWrap'          => true,
                'hScrollBarAlwaysVisible'   => false,

                'scrollSpeed'               => 2,
                'scrollPastEnd'             => false,
                'animatedScroll'            => false,
                'autoScrollEditorIntoView'  => false,

                'showGutter'                => true,
                'fixedWidthGutter'          => true,
                'highlightGutterLine'       => true,

                'showFoldWidgets'           => true,
                'fadeFoldWidgets'           => true,

                'highlightActiveLine'       => true,
                'highlightSelectedWord'     => true,

                'showInvisibles'            => true,
                'displayIndentGuides'       => true,
                'showPrintMargin'           => false,

                'behavioursEnabled'         => true,
                'wrapBehavioursEnabled'     => true,

                'enableBasicAutocompletion' => true,
                'enableLiveAutocompletion'  => false,
                'enableSnippets'            => false,

                'enableLinking'             => true,
                'enableSpellCheck'          => true,

                'mergeUndoDeltas'           => true,
                'useWorker'                 => false,
                // Strange way of disabling syntax checking.
                // See:  <https://github.com/ajaxorg/ace/wiki/Syntax-validation>
            ],
            'hljsStyleUrl' => $hljs_style_url,
            'hljsStyleSri' => c::sri($hljs_style_url),

            'hljsBgColor'    => s::getOption('hljs_bg_color'),
            'hljsFontFamily' => s::getOption('hljs_font_family'),

            'previewMethod'            => s::getOption('editor_preview'),
            'ajaxRestActionPreviewUrl' => s::restActionUrl('ajax.preview', ['post_id' => $post_id]),

            'previewStylesUrl' => $preview_styles_url,
            'previewStylesSri' => c::sri($preview_styles_url),

            'previewTypekitId' => '', // Implemented for filters only.

            'customPreviewStyles'  => s::getOption('editor_preview_styles'),
            'customPreviewScripts' => s::getOption('editor_preview_scripts'),

            'previewUrl' => c::appUrl('/client-s/js/admin/editor/preview/index.html?v='.urlencode($this->App::VERSION)),
        ]);
    }

    /**
     * On `wp_editor_settings` hook.
     *
     * @since 170126.30913 Initial release.
     *
     * @param array         $settings  Settings.
     * @param string|scalar $editor_id Editor ID.
     *
     * @return array Editor settings.
     */
    public function onWpEditorSettings(array $settings, $editor_id): array
    {
        $editor_id = (string) $editor_id;

        if (!$this->isApplicable()) {
            return $settings; // Not applicable.
        } elseif ($editor_id !== 'content') {
            return $settings; // Not applicable.
        }
        $slug = $this->App->Config->©brand['©slug'];
        $cns  = $slug.'-editor'; // Class namespace.

        return s::applyFilters('wp_editor_settings', [
            'media_buttons' => true,
            'tinymce'       => false,
            'quicktags'     => false,
            'editor_class'  => $cns.'-textarea',
        ]);
    }

    /**
     * On `wp_editor_expand` hook.
     *
     * @since 170126.30913 Initial release.
     *
     * @param bool|scalar   $expand    Expand?
     * @param string|scalar $post_type Post type.
     *
     * @return bool Expand?
     */
    public function onWpEditorExpand($expand, $post_type): bool
    {
        $expand    = (bool) $expand;
        $post_type = (string) $post_type;

        if ($this->isApplicable()) {
            $expand = false; // Disable.
        }
        return $expand;
    }

    /**
     * On `the_editor` hook.
     *
     * @since 170126.30913 Initial release.
     *
     * @param string|scalar $markup HTML markup.
     *
     * @return string The editor markup.
     */
    public function onTheEditor($markup): string
    {
        $markup = (string) $markup;

        if (mb_strpos($markup, ' id="content"') === false) {
            return $markup; // Not the `content`.
        }
        if ($this->isApplicable()) {
            $theme    = s::getOption('editor_theme');
            $color    = $theme === 'dark' ? '#fff' : '#000';
            $bg_color = $theme === 'dark' ? '#060708' : '#fff';

            $cns              = $this->App->Config->©brand['©slug'].'-editor';
            $svg_loading_icon = file_get_contents(dirname(__FILE__, 4).'/client-s/images/md-solid.svg');

            $loading =  '<style type="text/css" class="'.$cns.'-loading-styles">'.
                            '.'.$cns.'-loading{background:'.$bg_color.';}'.
                            '.'.$cns.'-loading>svg>rect{fill:'.$color.';}'.
                            '.'.$cns.'-loading::after{background:'.$color.';}'.
                        '</style>';
            $loading .= '<div class="'.$cns.'-loading">'.$svg_loading_icon.'</div>';

            // This is necessary because `the_editor` later goes through `printf()` where `%` has meaning.
            $markup .= str_replace('%', '%%', $loading); // Appends loading div.
        }
        return $markup;
    }

    /**
     * On `user_can_richedit` hook.
     *
     * @since 170219.18502 Initial release.
     *
     * @param bool|scalar $can Can?
     *
     * @return bool Can a user rich edit?
     */
    public function onUserCanRichEdit($can): bool
    {
        if ($this->isApplicable()) {
            $can = false; // MD editor only.
        } elseif (s::getOption('rich_editing_disable')) {
            $can = false; // Disable.
        }
        return (bool) $can; // Force boolean.
    }

    /**
     * On `show_user_profile` hook.
     *
     * @since 170219.18502 Initial release.
     *
     * @param \WP_User User associated w/ profile.
     */
    public function onShowUserProfile(\WP_User $WP_User)
    {
        if (!s::getOption('rich_editing_disable')) {
            return; // Not applicable.
        }
        echo '<script>';
        echo    '(function($){ ';
        echo        "$('#rich_editing').prop('checked', true).prop('disabled', true);";
        echo    ' })(jQuery);';
        echo '</script>';
    }

    /**
     * On `edit_user_profile` hook.
     *
     * @since 17xxxx Initial release.
     *
     * @param \WP_User User associated w/ profile.
     */
    public function onEditUserProfile(\WP_User $WP_User)
    {
        if (!s::getOption('rich_editing_disable')) {
            return; // Not applicable.
        }
        echo '<script>';
        echo    '(function($){ ';
        echo        "$('#rich_editing').prop('checked', true).prop('disabled', true);";
        echo    ' })(jQuery);';
        echo '</script>';
    }
}
