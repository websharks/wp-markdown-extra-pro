<?php
/**
 * Markdown utils.
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
 * Markdown utils.
 *
 * @since 170126.30913 Initial release.
 */
class Markdown extends SCoreClasses\SCore\Base\Core
{
    /**
     * `content_save_pre` tokenizers.
     *
     * @since 170126.30913 Initial release.
     *
     * @type array
     */
    protected $csp_Tokenizers = [];

    /**
     * Markdown transform.
     *
     * @since 170126.30913 Initial release.
     *
     * @param string $string  Markdown.
     * @param int    $post_id Post ID.
     * @param array  $args    MD args.
     *
     * @return string HTML markup.
     */
    public function __invoke(
        string $string,
        int $post_id = 0,
        array $args = []
    ): string {
        $texturizer   = s::getOption('texturizer');
        $default_args = [
            'cache'               => false,
            'cache_expires_after' => '30 days',
            'fn_id_prefix'        => 'p-'.$post_id.'-',
            'smartypants'         => $texturizer === 'smartypants',
        ]; // The defaults can be filtered by other plugins.
        $default_args = s::applyFilters('default_args', $default_args);
        $args += $default_args; // Merge with defaults.

        $cache               = (bool) $args['cache'];
        $cache_expires_after = (string) $args['cache_expires_after'];
        unset($args['cache'], $args['cache_expires_after']);

        if (!($string = c::mbTrim($string))) {
            return $string; // Nothing to do.
        }
        if ($cache) { // Cache markdown?
            $cache_sha1          = sha1($string.$post_id.serialize($args));
            $cache_sha1_shard_id = c::sha1ModShardId($cache_sha1, true);

            $cache_dir             = $this->App->Config->©fs_paths['©cache_dir'].'/markdown/'.$cache_sha1_shard_id;
            $cache_dir_permissions = $this->App->Config->©fs_permissions['©transient_dirs'];
            $cache_file            = $cache_dir.'/'.$cache_sha1.'.html';

            if (is_file($cache_file) && filemtime($cache_file) >= strtotime('-'.$cache_expires_after)) {
                return (string) file_get_contents($cache_file);
            } // Use the already-cached markdown.
        }
        $preserve  = ['shortcodes'];
        $tk_args   = ['shortcode_unautop_compat' => true];
        $Tokenizer = c::tokenize($string, $preserve, $tk_args);

        $string = &$Tokenizer->getString();
        $string = s::applyFilters('before', $string);

        $string = c::markdown($string, $args);
        $string = $Tokenizer->restoreGetString();
        $string = shortcode_unautop($string);

        $string = s::applyFilters('after', $string);
        $string = c::mbTrim($string);

        if ($cache && isset($cache_dir, $cache_dir_permissions, $cache_file)) {
            if (!is_dir($cache_dir)) {
                mkdir($cache_dir, $cache_dir_permissions, true);

                if (!is_dir($cache_dir)) {
                    debug(0, c::issue(vars(), 'Unable to create cache directory.'));
                    return $string; // Soft failure.
                }
            } // Cache directory exists.
            file_put_contents($cache_file, $string);
        }
        return $string; // HTML markup now.
    }

    /**
     * On `content_save_pre` hook.
     *
     * @since 170126.30913 Initial release.
     *
     * @param string|scalar $content Markup.
     *
     * @return string Tokenized markup.
     */
    public function onContentSavePre($content): string
    {
        $content = (string) $content;

        if ($this->isBulkOrInlineEdit()) {
            return $content; // Not applicable.
        } elseif ($this->isRestoringPostRevision()) {
            return $content; // Not applicable.
        } // Revisions are already good-to-go.

        if (!$content) {
            return $content; // Nothing to do.
        } elseif (!s::getOption('posts_enable')) {
            return $content; // Nothing to do.
        } elseif (!preg_match('/`|\<(?:pre|code|samp)/ui', $content)) {
            return $content; // Nothing to do.
        }
        // The point here is that we need to avoid KSES filters.
        // Anything inside these fences/tags becomes a code sample.
        // So stripping these from the equation is perfectly safe.
        $preserve                  = ['md-fences', 'pre', 'code', 'samp'];
        $Tokenizer                 = c::tokenize($content, $preserve);
        $tk                        = c::uniqueId();
        $this->csp_Tokenizers[$tk] = $Tokenizer;

        $content        = $Tokenizer->getString();
        return $content = '⁅⁅⒯'.$tk.'⒯⁆⁆'.$content;
    }

    /**
     * Restore `content_save_pre` tokens.
     *
     * @since 170126.30913 Initial release.
     *
     * @param string $content Markup.
     *
     * @return string Markup w/ tokens restored.
     */
    protected function contentSavePreRestore(string $content): string
    {
        if ($this->isBulkOrInlineEdit()) {
            return $content; // Not applicable.
        } elseif ($this->isRestoringPostRevision()) {
            return $content; // Not applicable.
        } // Revisions are already good-to-go.

        $content = c::mbLTrim($content);

        if (!$content) {
            return $content; // Nothing to do.
        } elseif (!preg_match('/^⁅⁅⒯(?<tk>[^⒯⁆]+)⒯⁆⁆/u', $content, $_m) || !($tk = $_m['tk'])) {
            return $content; // Nothing to do.
        } elseif (!($Tokenizer = $this->csp_Tokenizers[$tk] ?? null)) {
            debug(0, c::issue($this->csp_Tokenizers, sprintf('Missing tokenizer[%1$s].', $tk)));
            return $content; // Nothing to do.
        }
        $content = preg_replace('/^⁅⁅⒯[^⒯⁆]+⒯⁆⁆/u', '', $content);

        $Tokenizer->setString($content);
        $content = $Tokenizer->restoreGetString();
        unset($this->csp_Tokenizers[$tk]);

        return $content;
    }

    /**
     * On `wp_insert_post_data` hook.
     *
     * @since 170126.30913 Initial release.
     *
     * @param array $data      Slashed post data.
     * @param array $post_data Sanitized post data.
     *
     * @return array Filtered post `$data` parameter.
     */
    public function onWpInsertPostData(array $data, array $post_data): array
    {
        if ($this->isBulkOrInlineEdit()) {
            return $data; // Not applicable.
        } elseif ($this->isRestoringPostRevision()) {
            return $data; // Not applicable.
        } // Revisions are already good-to-go.

        $post_id = (int) ($post_data['ID'] ?? 0);
        // Only available when updating and existing ID.

        $post_type   = $data['post_type'];
        $post_name   = $data['post_name'];
        $post_parent = $data['post_parent'];

        // This is where Markdown transformation occurs, if applicable.
        // The resulting raw HTML is stored in `post_content`, which is used by core/themes/plugins.
        // The original Markdown is preserved. It is stored in `post_content_filtered` (i.e., in the DB).

        $data['post_content']          = (string) $data['post_content'];
        $data['post_content_filtered'] = (string) $data['post_content_filtered'];
        $data['post_content']          = $this->contentSavePreRestore($data['post_content']);

        if (!s::getOption('posts_enable')) {
            if ($post_id && s::getPostMeta($post_id, '_is')) {
                $data['post_content_filtered'] = '';
            } // In case MD was enabled previously.
        } elseif ($post_type === 'revision' && $post_parent && ($parent_WP_Post = get_post($post_parent))) {
            if (in_array($parent_WP_Post->post_type, s::getOption('post_types'), true)) {
                if (mb_stripos($post_name, $post_parent.'-autosave-') === 0) {
                    $data['post_content_filtered'] = $data['post_content'];
                    $data['post_content']          = wp_slash($this->__invoke(wp_unslash($data['post_content']), $post_id));
                } // Else, it's simply a noop. Other revisions are already good-to-go.
            } elseif ($post_id && s::getPostMeta($post_id, '_is')) {
                $data['post_content_filtered'] = '';
            }
        } elseif ($post_type !== 'revision' && in_array($post_type, s::getOption('post_types'), true)) {
            $data['post_content_filtered'] = $data['post_content'];
            $data['post_content']          = wp_slash($this->__invoke(wp_unslash($data['post_content']), $post_id));
            //
        } elseif ($post_id && s::getPostMeta($post_id, '_is')) {
            $data['post_content_filtered'] = '';
        }
        return $data;
    }

    /**
     * On `save_post` hook.
     *
     * @since 170126.30913 Initial release.
     *
     * @param int|scalar $_       Post ID (not used).
     * @param \WP_Post   $WP_Post Post object instance.
     */
    public function onSavePost($_, \WP_Post $WP_Post)
    {
        if ($this->isBulkOrInlineEdit()) {
            return $data; // Not applicable.
        } elseif ($this->isRestoringPostRevision()) {
            return; // Not applicable.
        } // Revisions are already good-to-go.

        $post_id = (int) $WP_Post->ID;
        // Force an integer ID.

        $post_type   = $WP_Post->post_type;
        $post_name   = $WP_Post->post_name;
        $post_parent = $WP_Post->post_parent;

        // The point here is that we need to save a post meta flag.
        // Ideally, this would occur on the `wp_insert_post_data` hook.
        // However, only post updates will include the `ID` in that phase.
        // So this runs after the insert/update when we always have a post ID.

        if (!s::getOption('posts_enable')) {
            if ($post_id && s::getPostMeta($post_id, '_is')) {
                s::deletePostMeta($post_id, '_is');
            } // In case MD was enabled previously.
        } elseif ($post_type === 'revision' && $post_parent && ($parent_WP_Post = get_post($post_parent))) {
            if (in_array($parent_WP_Post->post_type, s::getOption('post_types'), true)) {
                s::updatePostMeta($post_id, '_is', '1');
                //
            } elseif ($post_id && s::getPostMeta($post_id, '_is')) {
                s::deletePostMeta($post_id, '_is');
            }
        } elseif ($post_type !== 'revision' && in_array($post_type, s::getOption('post_types'), true)) {
            s::updatePostMeta($post_id, '_is', '1');
            //
        } elseif ($post_id && s::getPostMeta($post_id, '_is')) {
            s::deletePostMeta($post_id, '_is');
        }
    }

    /**
     * On `edit_post_content` hook.
     *
     * @since 170126.30913 Initial release.
     *
     * @param string|scalar $content Post content.
     * @param int|scalar    $post_id Post ID.
     *
     * @return string Swapped post content.
     */
    public function onEditPostContent($content, $post_id): string
    {
        $content = (string) $content;
        $post_id = (int) $post_id;

        if (!s::getPostMeta($post_id, '_is')) {
            return $content; // Not applicable.
        } elseif (!($WP_Post = get_post($post_id))) {
            return $content; // Not possible.
        }
        // Note: This doesn't only do a swap on the filter.
        // We're also altering the WP_Post object by reference.

        $content                        = $WP_Post->post_content_filtered;
        $WP_Post->post_content_filtered = $WP_Post->post_content;
        $WP_Post->post_content          = $content;

        if (!s::getOption('posts_enable')) {
            $WP_Post->post_content_filtered = '';
            //
        } elseif ($WP_Post->post_type !== 'revision' // Sanity check.
                && !in_array($WP_Post->post_type, s::getOption('post_types'), true)) {
            $WP_Post->post_content_filtered = '';
        }
        return $content;
    }

    /**
     * On `_wp_post_revision_fields` hook.
     *
     * @since 170126.30913 Initial release.
     *
     * @param array $fields Post fields pertinent to revisions.
     *
     * @return array Including `post_content_filtered`.
     */
    public function onWpPostRevisionFields(array $fields): array
    {
        if (!s::getOption('posts_enable')) {
            return $fields; // Not applicable.
        }
        $fields['post_content_filtered'] = __('Markdown', 'wp-markdown-extra');

        return $fields; // Including `post_content_filtered`.
    }

    /**
     * On `get_the_excerpt` hook.
     *
     * @since 170126.30913 Initial release.
     *
     * @param string|scalar $excerpt Markup.
     *
     * @return string Transformed excerpt markup.
     */
    public function onGetTheExcerpt($excerpt)
    {
        $excerpt   = (string) $excerpt;
        $post_id   = (int) get_the_ID();
        $post_type = (string) get_post_type();

        if (!$excerpt) {
            return $excerpt;
        } elseif (!$post_id) {
            return $excerpt; // No post ID.
        } elseif (!s::getOption('posts_enable')) {
            return $excerpt; // Not applicable.
        } elseif (!in_array($post_type, s::getOption('post_types'), true)) {
            return $excerpt; // Not applicable.
        }
        $sha1_f8        = mb_substr(sha1($excerpt), 0, 8);
        return $excerpt = $this->__invoke($excerpt, $post_id, [
            'cache'        => true, // Cache.
            'fn_id_prefix' => 'e-'.$sha1_f8.'-',
        ]);
    }

    /**
     * On `woocommerce_short_description` hook.
     *
     * @since 170126.30913 Initial release.
     *
     * @param string|scalar $description Markup.
     *
     * @return string Transformed description markup.
     */
    public function onWcShortDescription($description)
    {
        $description = (string) $description;
        $post_id     = 0; // Not possible.
        $post_type   = 'product'; // A given.

        if (!$description) {
            return $description;
        } elseif (!$post_id) {
            return $description; // No post ID.
        } elseif (!s::getOption('posts_enable')) {
            return $description; // Not applicable.
        } elseif (!in_array($post_type, s::getOption('post_types'), true)) {
            return $description; // Not applicable.
        }
        $sha1_f8            = mb_substr(sha1($description), 0, 8);
        return $description = $this->__invoke($description, $post_id, [
            'cache'        => true, // Cache.
            'fn_id_prefix' => 'd-'.$sha1_f8.'-',
        ]);
    }

    /**
     * On `pre_comment_content` hook.
     *
     * @since 170126.30913 Initial release.
     *
     * @param string|scalar $comment Markup.
     *
     * @return string Transformed comment markup.
     */
    public function onPreCommentContent($comment): string
    {
        $comment = (string) $comment;

        if (!$comment) {
            return $comment; // Empty.
        } elseif (!s::getOption('comments_enable')) {
            return $comment; // Not applicable.
        }
        $sha1_f8        = mb_substr(sha1($comment), 0, 8);
        return $comment = $this->__invoke($comment, 0, [
            'cache'        => false, // Never.
            'fn_id_prefix' => 'c-'.$sha1_f8.'-',
        ]);
    }

    /**
     * Is bulk or inline edit mode?
     *
     * @since 170126.30913 Initial release.
     *
     * @return bool Is bulk or inline edit mode?
     */
    protected function isBulkOrInlineEdit(): bool
    {
        return $this->Wp->is_admin && (isset($_REQUEST['bulk_edit']) || isset($_REQUEST['_inline_edit']));
    }

    /**
     * Is a revision restoration?
     *
     * @since 170126.30913 Initial release.
     *
     * @return bool Is a revision restoration?
     */
    protected function isRestoringPostRevision(): bool
    {
        return ($this->Wp->is_admin && s::isMenuPage('revision.php') && ($GLOBALS['action'] ?? '') === 'restore')
            || c::hasBacktraceCaller(['wp_update_post', 'wp_restore_post_revision']);
    }
}
