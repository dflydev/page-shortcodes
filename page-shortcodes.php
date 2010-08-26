<?php
/*
Plugin Name: Page Shortcodes
Plugin URI: http://wordpress.org/extend/plugins/page-shortcodes/
Description: Shortcode tools to embed pages and page lists.
Author: Dragonfly Development
Author URI: http://dflydev.com/
Version: 0.1
License: New BSD License - http://www.opensource.org/licenses/bsd-license.php
*/

$page_shortcodes_cache_by_id = array();
$page_shortcodes_cache_by_name = array();

function page_shortcodes_buffer_open() {
    ob_start();
}

function page_shortcodes_buffer_close() {
    $result = ob_get_contents();
    ob_end_clean();
    return $result;
}

function page_shortcodes_find_page($atts, $content = null) {
    global $page_shortcodes_cache_by_id;
    global $page_shortcodes_cache_by_name;
    extract(shortcode_atts(array(
        'id' => null,
        'name' => null,
    ), $atts));
    $queryArgs = array('post_type' => 'page');
    if ( $id ) {
        if ( isset($page_shortcodes_cache_by_id[$id]) ) return $page_shortcodes_cache_by_id[$id];
        $queryArgs['page_id'] = $id;
    }
    if ( $name ) {
        if ( isset($page_shortcodes_cache_by_name[$name]) ) return $page_shortcodes_cache_by_name[$name];
        $queryArgs['pagename'] = $name;
    }
    $theQuery = new WP_Query($queryArgs);
    $posts = $theQuery->get_posts();
    if ( count($posts) ) {
        $foundPost = $posts[0];
        $page_shortcodes_cache_by_id[$foundPost->ID] = $foundPost;
        $page_shortcodes_cache_by_name[$foundPost->post_name] = $foundPost;
        return $foundPost;
    }
    return null;
}

function handle_page_shortcodes_shortcode__page_permalink($atts, $content = null) {
    $page = page_shortcodes_find_page($atts, $content);
    if ( $page ) return get_permalink($page->ID);
    return $content;
}

function handle_page_shortcodes_shortcode__page_title($atts, $content = null) {
    $page = page_shortcodes_find_page($atts, $content);
    if ( $page ) return $page->post_title;
    return $content;
}

function handle_page_shortcodes_shortcode__page_content($atts, $content = null) {
    $page = page_shortcodes_find_page($atts, $content);
    if ( $page ) return $page->post_content;
    return $content;
}


function handle_page_shortcodes_shortcode__page_list($atts, $content = null) {
    extract(shortcode_atts(array(
        'id' => null,
        'name' => null,
        'template' => null,
    ), $atts));
    if ( $name ) {
        $page = page_shortcodes_find_page(array('name' => $name));
        if ( $page ) $id = $page->ID;
    }
    if ( ! $id ) return $content;
    $queryArgs = array('post_type' => 'page', 'post_parent' => $id);
    $theQuery = new WP_Query($queryArgs);
    $posts = $theQuery->get_posts();
    if ( count($posts) ) {
        foreach ( $posts as $foundPost ) {
            $page_shortcodes_cache_by_id[$foundPost->ID] = $foundPost;
            $page_shortcodes_cache_by_name[$foundPost->post_name] = $foundPost;
        }
    }
    $output = '';
    if ( $template ) {
        //
    } else {
        //
        $output .= '<ul class="page-shortcodes-list">';
        foreach ( $posts as $foundPost ) {
            $output .= '<li><a href="' . get_permalink($foundPost->ID) . '">' . $foundPost->post_title . '</a></li>';
        }
        $output .= '</ul>';
    }
    return $output;
}

/**
 * Init the plugin.
 */
function handle_page_shortcodes_init() {
    add_shortcode('page_permalink', 'handle_page_shortcodes_shortcode__page_permalink');
    add_shortcode('page_title', 'handle_page_shortcodes_shortcode__page_title');
    add_shortcode('page_content', 'handle_page_shortcodes_shortcode__page_content');
    add_shortcode('page_list', 'handle_page_shortcodes_shortcode__page_list');
}

add_action('init', 'handle_page_shortcodes_init');
?>