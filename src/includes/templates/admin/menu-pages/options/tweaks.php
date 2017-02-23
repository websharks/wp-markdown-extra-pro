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
        __('Subtle Tweaks', 'wp-markdown-extra'),
        sprintf(__('You can browse our <a href="%1$s" target="_blank">knowledge base</a> to learn more.', 'wp-markdown-extra'), esc_url(s::brandUrl('/kb')))
    ); ?>

        <?= $Form->selectRow([
            'label' => __('Tweak Core Filters?', 'wp-markdown-extra'),
            'tip'   => __('If you don\'t like surprises, please enable this option.<hr />Why? Because when you publish in Markdown, mixed with raw HTML, these built-in WordPress filters have a tendency to get in your way.<hr /><strong>Note:</strong> Aside from the occassional unexpected formatting produced by these core filters, Markdown still works fine with them on; i.e., this setting is optional.', 'wp-markdown-extra'),
            'note'  => __('A mixed set of helpful tweaks for the professional Markdowner. This disables the <code>convert_smilies</code>, <code>convert_chars</code>, <code>wpautop</code>, <code>shortcode_unautop</code>, and <code>capital_P_dangit</code> filters in WordPress core; i.e., filters that can lead to quirky surprises. It also enables Shortcodes in Text Widgets.<br /><br /><em><strong>Note:</strong> With this option enabled, the filters mentioned above are removed globally, for all content &amp; excerpts, whether they are written in Markdown or not. However, they are only removed if you have Markdown enabled for Posts. The filters are also removed globally for comments, but only if you have Markdown enabled for Comments.</em>', 'wp-markdown-extra'),

            'name'     => 'filter_tweaks_enable',
            'value'    => s::getOption('filter_tweaks_enable'),
            'options'  => [
                '1' => __('Yes, enable additional filter tweaks.', 'wp-markdown-extra'),
                '0' => __('No, I don\'t want any additional filter tweaks.', 'wp-markdown-extra'),
            ],
        ]); ?>

        <?= $Form->selectRow([
            'label' => __('Use PHP SmartyPants?', 'wp-markdown-extra'),
            'tip'   => __('PHP SmartyPants and <code>wptexturize</code> both beautify &amp; enhance your content by automatically converting \'single\' or "double" quotes into ‘fancy’ “quotes”.<hr />Among other subtleties, they also convert three dots (...) into an ellipsis entity.', 'wp-markdown-extra'),
            'note'  => sprintf(__('%1$s suggests using <a href="%2$s" target="_blank">PHP SmartyPants</a> for texturization because it\'s a little more Markdown-friendly, which leads to fewer surprises. However, the built-in <code>wptexturize</code> filter works just fine also.</em>', 'wp-markdown-extra'), esc_html($this->App->Config->©brand['©name']), esc_url(s::coreUrl('/r/php-smartypants'))),

            'name'     => 'texturizer',
            'value'    => s::getOption('texturizer'),
            'options'  => [
                'wptexturize' => __('No, use wptexturize (WordPress default).', 'wp-markdown-extra'),
                'smartypants' => __('Yes, use PHP SmartyPants for texturization.', 'wp-markdown-extra'),
                ''            => __('No, I don\'t want to use either of these.', 'wp-markdown-extra'),
            ],
        ]); ?>

        <?= $Form->selectRow([
            'label' => __('Forbid Visual Editing?', 'wp-markdown-extra'),
            'tip'   => __('When you forbid use of the Visual Editor in WordPress, in each user\'s Profile page, the checkbox to disable rich editing will be forced on (i.e., checked already, and then disabled too). This is to help indicate that Visual Editing has been disabled entirely.', 'wp-markdown-extra'),
            'note'  => __('This completely disables all use of the Visual Editor in WordPress. For Post Types that don\'t use Markdown, editing occurs in the default WordPress textarea; i.e., there is no Visual Editing allowed when this is on.', 'wp-markdown-extra'),

            'name'     => 'rich_editing_disable',
            'value'    => s::getOption('rich_editing_disable'),
            'options'  => [
                '1' => __('Yes, prohibit use of the visual editor.', 'wp-markdown-extra'),
                '0' => __('No, I to disable visual editing entirely.', 'wp-markdown-extra'),
            ],
        ]); ?>

    <?= $Form->closeTable(); ?>

    <?= $Form->submitButton(); ?>
<?= $Form->closeTag(); ?>
