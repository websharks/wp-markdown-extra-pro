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

    <?= $Form->openTable(
        __('Editor Options', 'wp-markdown-extra'),
        sprintf(__('You can browse our <a href="%1$s" target="_blank">knowledge base</a> to learn more about these options.', 'wp-markdown-extra'), esc_url(s::brandUrl('/kb')))
    ); ?>

        <?= $Form->hrRow(); ?>

        <?= $Form->selectRow([
            'label' => __('Enable Mardown Editor?', 'wp-markdown-extra'),
            'tip'   => __('This replaces the built-in WordPress editor with a simpler Markdown editor.', 'wp-markdown-extra'),

            'name'    => 'editor_enable',
            'value'   => s::getOption('editor_enable'),
            'options' => [
                '1' => __('Yes, enable for Markdown Post Types', 'wp-markdown-extra'),
                '0' => __('No', 'wp-markdown-extra'),
            ],
        ]); ?>

        <?= $Form->hrRow(); ?>

        <?= $Form->selectRow([
            'if'    => 'editor_enable',

            'label' => __('Editor Theme', 'wp-markdown-extra'),
            'tip'   => __('This controls the editable content in the MD editor.', 'wp-markdown-extra'),

            'name'    => 'editor_theme',
            'value'   => s::getOption('editor_theme'),
            'options' => [
                'light' => __('Light', 'wp-markdown-extra'),
                'dark'  => __('Dark', 'wp-markdown-extra'),
            ],
        ]); ?>

        <?= $Form->inputRow([
            'if'    => 'editor_enable',

            'type'  => 'text',
            'label' => __('Font Size', 'wp-markdown-extra'),
            'tip'   => __('Controls the CSS font size used while editing.<hr />The <code>em</code> unit is suggested. However, if you want to force a constant pixel size, just use <code>px</code> instead of <code>em</code>.', 'wp-markdown-extra'),
            'note'  => __('The <tt>em</tt> unit is suggested so it scales automatically in fullscreen mode. The baseline font size is <tt><strong>14px</strong></tt>. Therefore, <tt>0.857em = 12px</tt>, <tt>1em = <strong>14px</strong></tt>, <tt>1.143em = 16px</tt>. In fullscreen mode (if you\'re on a large screen) the baseline jumps from <tt>14px</tt> to <tt><strong>16px</strong></tt> automatically, which results in all calculations being slightly larger in fullscreen mode.', 'wp-markdown-extra'),

            'name'  => 'editor_font_size',
            'value' => s::getOption('editor_font_size'),
        ]); ?>

        <?= $Form->inputRow([
            'if'    => 'editor_enable',

            'type'  => 'text',
            'label' => __('Font Family', 'wp-markdown-extra'),
            'tip'   => __('Controls the font family used while editing.', 'wp-markdown-extra'),
            'note'  => __('Comma-delimited monospace fonts used in CSS declaration.', 'wp-markdown-extra'),

            'name'  => 'editor_font_family',
            'value' => s::getOption('editor_font_family'),
        ]); ?>

        <?= $Form->selectRow([
            'if'     => 'editor_enable',

            'label'  => __('Enable Enhanced IDE?', 'wp-markdown-extra'),
            'tip'    => __('Enables highlighting, line numbers, auto-indent, auto-brackets, tabs, and more.', 'wp-markdown-extra'),
            'note'   => __('This enables a powerful IDE  that includes highlighting, line numbers, auto-indent, brackets, tabs, and more.', 'wp-markdown-extra'),

            'name'    => 'editor_ide_enable',
            'value'   => s::getOption('editor_ide_enable'),
            'options' => [
                '1'  => __('Yes', 'wp-markdown-extra'),
                '0'  => __('No', 'wp-markdown-extra'),
            ],
        ]); ?>

        <?= $Form->selectRow([
            'if'     => 'editor_enable',

            'label'  => __('Quick Preview Method', 'wp-markdown-extra'),
            'tip'    => __('Go with JavaScript for instant feedback in split preview mode. It\'s a little inaccurate at times, but much faster!<hr />If you use a lot of advanced Markdown syntax and need accurate previews of the <em>actual</em> Markdown render, go with PHP/AJAX instead.', 'wp-markdown-extra'),
            'note'   => sprintf(__('%1$s can do previews in pure JavaScript, giving you instant feedback in split preview mode. Or, it can do previews via PHP/AJAX for improved accuracy; i.e., giving you a preview of the <em>actual</em> Markdown render.', 'wp-markdown-extra'), esc_html($this->App->Config->©brand['©name'])),

            'name'    => 'editor_preview',
            'value'   => s::getOption('editor_preview'),
            'options' => [
                'js'   => __('JavaScript (99% accurate, lightning fast)', 'wp-markdown-extra'),
                'php'  => __('PHP via AJAX (100% accurate, slightly slower)', 'wp-markdown-extra'),
            ],
        ]); ?>

        <?= $Form->selectRow([
            'if'     => 'editor_enable',

            'label'  => __('Media Inserts', 'wp-markdown-extra'),
            'tip'    => __('Either way works just fine. This is simply a matter of personal preference.', 'wp-markdown-extra'),
            'note'   => __('Markdown image example: <code>![My Image](image.png){.alignright width=128}</code>', 'wp-markdown-extra'),

            'name'    => 'editor_media_inserts',
            'value'   => s::getOption('editor_media_inserts'),
            'options' => [
                'md'    => __('Markdown; e.g., ![alt](image.png)', 'wp-markdown-extra'),
                'html'  => __('HTML; e.g., &lt;img src="image.png" /&gt;', 'wp-markdown-extra'),
            ],
        ]); ?>

    <?= $Form->closeTable(); ?>

    <?= $Form->submitButton(); ?>
<?= $Form->closeTag(); ?>
