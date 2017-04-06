<?php
/**
 * Template.
 *
 * @author @jaswsinc
 * @copyright WP Sharks™
 */
declare(strict_types=1);
namespace WebSharks\WpSharks\WpMarkdownExtra\Pro;

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

if (!defined('WPINC')) {
    exit('Do NOT access this file directly.');
}
$Form = $this->s::menuPageForm('§save-options');
?>
<?= $Form->openTag(); ?>

    <?php if (!s::getOption('editor_enable')) : ?>
        <?= $Form->openTable(
            __('Editor Preview Options', 'wp-markdown-extra'),
            __('<em><strong>Note:</strong> Preview options are applicable only when you\'ve enabled the Markdown Editor.</em>', 'wp-markdown-extra')
        ); ?>
        <?= $Form->closeTable(); ?>
    <?php endif; ?>

    <?= $Form->openTable(
        __('Highlight.js Preview Options', 'wp-markdown-extra'),
        __('These control syntax highlighting for fenced coded blocks, when previewing Markdown.', 'wp-markdown-extra')
    ); ?>

        <?= $Form->inputRow([
            'type'    => 'hidden',
            'name'    => 'editor_enable',
            'value'   => s::getOption('editor_enable'),
        ]); ?>

        <?= $Form->inputRow([
            'if'    => 'editor_enable',

            'type'  => 'text',
            'label' => __('Highlight.js Style', 'wp-markdown-extra'),
            'tip'   => __('Powered by Highlight.js.<hr />This option controls the Highlight.js colors used in fenced code blocks.<hr />Review CDN resources and enter a <code>[style]</code>.min.css file basename w/o extension.<hr />e.g., <code>github</code>, <code>atom-one-dark</code>, <code>ir-black</code>, <code>hybrid</code>', 'wp-markdown-extra'),
            'note'  => sprintf(__('Review the list of <a href="%1$s" target="_blank">CDN resources</a> and choose a <code>[style]</code>.min.css. See also: <a href="%2$s" target="_blank">style demos</a>', 'wp-markdown-extra'), esc_url($this->App->Config->hljs['cdn_files_list_url']), esc_url($this->App->Config->hljs['style_demos_url'])),

            'name'  => 'hljs_style',
            'value' => s::getOption('hljs_style'),
        ]); ?>

        <?= $Form->inputRow([
            'if'    => 'editor_enable',

            'type'        => 'text',
            'placeholder' => __('e.g., #f8f8f8', 'wp-markdown-extra'),
            'label'       => __('BG Color Override', 'wp-markdown-extra'),
            'tip'         => __('Hex color code; e.g., <code>#f8f8f8</code>, <code>#000</code>, <code>#ccc</code><hr />If empty, the background color is defined by the style you selected above.', 'wp-markdown-extra'),
            'note'        => __('If empty, the background color is simply defined by the Highlight.js style.', 'wp-markdown-extra'),

            'name'  => 'hljs_bg_color',
            'value' => s::getOption('hljs_bg_color'),
        ]); ?>

        <?= $Form->inputRow([
            'if'    => 'editor_enable',

            'type'        => 'text',
            'placeholder' => "'Hack', 'Menlo', 'Monaco', 'Consolas', 'Andale Mono', 'DejaVu Sans Mono', monospace",
            'label'       => __('Font Family Override', 'wp-markdown-extra'),
            'tip'         => __('Controls the containing element font family.', 'wp-markdown-extra'),
            'note'        => __('Comma-delimited monospace fonts used in CSS declaration.', 'wp-markdown-extra'),

            'name'  => 'hljs_font_family',
            'value' => s::getOption('hljs_font_family'),
        ]); ?>

    <?= $Form->closeTable(); ?>

    <?= $Form->openTable(
        __('Custom Preview Styles', 'wp-markdown-extra'),
        __('This allows you to include CSS of your own that may override some defaults, or perhaps style elements in previews in a way that more closely resembles the final output in your WordPress theme. Keep in mind there are already a number of built-in preview styles, so this is completely optional.', 'wp-markdown-extra')
    ); ?>

        <?= $Form->textareaRow([
            'if'    => 'editor_enable',

            'label'       => __('Custom Preview Styles', 'wp-markdown-extra'),
            'placeholder' => '@import url(\'/wp-content/my-theme/md-preview-styles.css\');',
            'tip'         => __('Custom styles are injected into Markdown previews, right after all of the default styles.', 'wp-markdown-extra'),
            'note'        => __('<strong>Tip:</strong> You can choose to use a CSS <code>@import</code> rule if you\'d like; i.e., so you can maintain custom styles in a separate file. For example: <code>@import url(\'/wp-content/my-theme/md-preview-styles.css\');</code>', 'wp-markdown-extra'),

            'name'  => 'editor_preview_styles',
            'value' => s::getOption('editor_preview_styles'),
        ]); ?>

    <?= $Form->closeTable(); ?>

    <?= $Form->submitButton(); ?>
<?= $Form->closeTag(); ?>
