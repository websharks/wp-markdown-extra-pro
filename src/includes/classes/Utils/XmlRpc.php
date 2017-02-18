<?php
/**
 * XML-RPC utils.
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
 * XML-RPC utils.
 *
 * @since 170126.30913 Initial release.
 */
class XmlRpc extends SCoreClasses\SCore\Base\Core
{
    /**
     * On `init` hook.
     *
     * @since 170126.30913 Initial release.
     */
    public function onInit()
    {
        if (!defined('XMLRPC_REQUEST') || !XMLRPC_REQUEST) {
            return; // Not applicable.
        } // Review `xmlrpc.php` for this flag.

        $data = &$GLOBALS['HTTP_RAW_POST_DATA'];
        // Already set very early-on by `xmlrpc.php`.

        if (mb_strpos($data, 'metaWeblog.getPost') === false
                && mb_strpos($data, 'wp.getPage') === false) {
            return; // Not applicable.
        }
        include_once ABSPATH.WPINC.'/class-IXR.php';

        $IXR_Message = new \IXR_Message($data);
        $IXR_Message->parse(); // Parse IXR message.
        $post_id_key = $IXR_Message->methodName === 'metaWeblog.getPost' ? 0 : 1;

        if (!empty($IXR_Message->params[$post_id_key])) {
            $this->primePostCache((int) $IXR_Message->params[$post_id_key]);
        }
    }

    /**
     * On `xmlrpc_call` hook.
     *
     * @since 170126.30913 Initial release.
     *
     * @param string|scalar $method XML-RPC method.
     */
    public function onXmlRpcCall($method)
    {
        $wp_xmlrpc_server = &$GLOBALS['wp_xmlrpc_server'];
        // Review `xmlrpc.php` for this global var.

        switch ($method = (string) $method) {
            case 'wp.getPosts':
            case 'wp.getPages':
            case 'metaWeblog.getRecentPosts':
                add_action('parse_query', [$this, 'filterPosts']);
                break;

            case 'wp.getPost':
            // case 'metaWeblog.getPost':
            // This case is covered by the early check above.
                if (!empty($wp_xmlrpc_server->message->params[3])) {
                    $this->primePostCache((int) $wp_xmlrpc_server->message->params[3]);
                }
                break;
        }
    }

    /**
     * Attaches to `parse_query` hook.
     *
     * @param \WP_Query $WP_Query Query instance.
     */
    public function filterPosts(\WP_Query $WP_Query)
    {
        $WP_Query->set('suppress_filters', false);

        add_filter('the_posts', function (array $posts, \WP_Query $WP_Query) {
            foreach ($posts as $_WP_Post) {
                $_post_id = (int) $_WP_Post->ID;

                if (!s::getPostMeta($_post_id, '_is')) {
                    continue; // Not applicable.
                }
                $_markdown                       = $_WP_Post->post_content_filtered;
                $_WP_Post->post_content_filtered = $_WP_Post->post_content;
                $_WP_Post->post_content          = $_markdown;
                //
            } // unset($_key, $_WP_Post, $_post_id, $_markdown);
            return $posts; // Filtered posts.
        }, 10, 2);
    }

    /**
     * Prime the post cache.
     *
     * @param int|scalar $post_id Post ID.
     */
    protected function primePostCache(int $post_id)
    {
        $post_id = (int) $post_id;

        static $posts_to_uncache  = [];
        static $shutdown_hook_set = false;

        if (!s::getPostMeta($post_id, '_is')) {
            return; // Not applicable.
        } elseif (!($WP_Post = get_post($post_id))) {
            return; // Not possible.
        }
        wp_cache_delete($post_id, 'posts');

        $markdown                       = $WP_Post->post_content_filtered;
        $WP_Post->post_content_filtered = $WP_Post->post_content;
        $WP_Post->post_content          = $markdown;

        $posts_to_uncache[] = $post_id;
        wp_cache_add($post_id, $WP_Post, 'posts');

        if (!$shutdown_hook_set && wp_using_ext_object_cache()) {
            add_action('shutdown', function () use (&$posts_to_uncache) {
                foreach ($posts_to_uncache as $_post_id) {
                    wp_cache_delete($_post_id, 'posts');
                } // unset($_post_id);
            });
        }
    }
}
