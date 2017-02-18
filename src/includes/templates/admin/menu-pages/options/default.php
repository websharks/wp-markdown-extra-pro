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
        __('General Options', 'wp-markdown-extra'),
        sprintf(__('You can browse our <a href="%1$s" target="_blank">knowledge base</a> to learn more about these options.', 'wp-markdown-extra'), esc_url(s::brandUrl('/kb')))
    ); ?>

        <?= $Form->selectRow([
            'label' => __('Enable for Posts?', 'wp-markdown-extra'),
            'tip'   => __('This allows you to publish content using Markdown.', 'wp-markdown-extra'),

            'name'    => 'posts_enable',
            'value'   => s::getOption('posts_enable'),
            'options' => [
                '1' => __('Yes', 'wp-markdown-extra'),
                '0' => __('No', 'wp-markdown-extra'),
            ],
        ]); ?>

        <?= $Form->selectRow([
            'if'    => 'posts_enable',

            'label' => __('Markdown Post Types', 'wp-markdown-extra'),
            'tip'   => __('Select the Post Types that are written in Markdown.', 'wp-markdown-extra'),

            'name'     => 'post_types',
            'multiple' => true, // i.e., An array.
            'value'    => s::getOption('post_types'),
            'options'  => s::postTypeSelectOptions([
                'filters'            => ['show_ui' => true],
                'exclude'            => [
                    'attachment',
                    'revision',
                    'shop_order',
                    'shop_coupon',
                    'shop_webhook',
                ],
                'allow_empty'        => false,
                'allow_arbitrary'    => false,
                'current_post_types' => s::getOption('post_types'),
            ]),
        ]); ?>

        <?= $Form->selectRow([
            'label' => __('Enable for Comments?', 'wp-markdown-extra'),
            'tip'   => __('This allows all users to comment in Markdown.', 'wp-markdown-extra'),

            'name'     => 'comments_enable',
            'value'    => s::getOption('comments_enable'),
            'options'  => [
                '1' => __('Yes', 'wp-markdown-extra'),
                '0' => __('No', 'wp-markdown-extra'),
            ],
        ]); ?>

    <?= $Form->closeTable(); ?>

    <?= $Form->submitButton(); ?>
<?= $Form->closeTag(); ?>
