<?php
/*
Plugin Name: Cricboard
Plugin URI: http://wordpress.org/plugins/cricboard/
Description: Plugin to maintain leaderboards in online cricket leagues
Author: itnicks
Author URI: http://www.itnicks.com/
Version: 1.0
Text Domain: cricboard
*/

//Check if user is logged in else redirect user to login URL
function cricboard_check_login() {
    if(!is_user_logged_in() && $GLOBALS['pagenow'] != 'wp-login.php') auth_redirect();
}
add_action('init', 'cricboard_check_login');

//Hide Admin Bar
show_admin_bar(false);

//Enqueue Scripts
function cricboard_enqueue_scripts() {    
    wp_enqueue_script("cricboard-js", plugin_dir_url(__FILE__).'js/cricboard.js', array('jquery'), "1.0", true);    
    wp_enqueue_style("cricboard-css", plugin_dir_url(__FILE__).'css/cricboard.css', array(), "1.0");
}
add_action("wp_enqueue_scripts", "cricboard_enqueue_scripts");

//Register Post Type - League
function cricboard_register_post_type() {
    $labels = array (
        'name'               => _x('Leagues', 'post type general name','cricboard'),
        'singular_name'      => _x('League', 'post type singular name','cricboard'),
        'add_new'            => _x('Add New', 'league','cricboard'),
        'add_new_item'       => __('Add New League','cricboard'),
        'edit_item'          => __('Edit League','cricboard'),
        'new_item'           => __('New League','cricboard'),
        'all_items'          => __('All Leagues','cricboard'),
        'view_item'          => __('View League','cricboard'),
        'search_items'       => __('Search Leagues','cricboard'),
        'not_found'          => __('No leagues found','cricboard'),
        'not_found_in_trash' => __('No leagues found in the Trash','cricboard'),
        'menu_name'          => __('Leagues','cricboard')
    );
    
    $args = array(
        'labels'          => $labels,
        'public'          => true,
        'menu_position'   => 5,
        'menu_icon'       => plugin_dir_url(__FILE__).'images/league_post_type_icon.png',
        'supports'        => array('title', 'editor', 'thumbnail', 'comments', 'page-attributes'),
        'has_archive'     => true,
        'capability_type' => 'post',
        'hierarchical'    => false,
        'taxonomies'      => array('post_tag','category'),
    );
    register_post_type('league', $args);
}
add_action('init', 'cricboard_register_post_type');

//Allow contributor to edit posts
function cricboard_allow_edit_post() {
    $role_obj = get_role('contributor');
    $role_obj->add_cap('edit_others_posts');
}
add_action('admin_init', 'cricboard_allow_edit_post');

//Add post meta box for post
function cricboard_add_post_meta_box() {
    add_meta_box('match_limit', __('Max number of matches per player', 'cricboard'), 'cricboard_match_limit_meta_box_callback', 'league', 'normal', 'high');
    add_meta_box('win_points', __('Points for Win', 'cricboard'), 'cricboard_win_points_meta_box_callback', 'league', 'normal', 'high');
    add_meta_box('part_points', __('Points for Participation', 'cricboard'), 'cricboard_part_points_meta_box_callback', 'league', 'normal', 'high');
    add_meta_box('deadline', __('Deadline (DD-MM-YYYY)', 'cricboard'), 'cricboard_deadline_meta_box_callback', 'league', 'normal', 'high');
}
add_action('add_meta_boxes', 'cricboard_add_post_meta_box');

function cricboard_match_limit_meta_box_callback() {
    global $post;
    wp_nonce_field('match_limit_nonce', 'match_limit_nonce');
    $value = get_post_meta($post->ID, 'match_limit', true);
    echo cricboard_output_field('match_limit', 'number', '', esc_attr($value));
}

function cricboard_win_points_meta_box_callback() {
    global $post;
    wp_nonce_field('win_points_nonce', 'win_points_nonce');
    $value = get_post_meta($post->ID, 'win_points', true);
    echo cricboard_output_field('win_points', 'number', '', esc_attr($value));
}

function cricboard_part_points_meta_box_callback() {
    global $post;
    wp_nonce_field('part_points_nonce', 'part_points_nonce');
    $value = get_post_meta($post->ID, 'part_points', true);
    echo cricboard_output_field('part_points', 'number', '', esc_attr($value));
}

function cricboard_deadline_meta_box_callback() {
    global $post;
    wp_nonce_field('deadline_nonce', 'deadline_nonce');
    $value = get_post_meta($post->ID, 'deadline', true);
    echo cricboard_output_field('deadline', 'text', '', esc_attr($value));
}

//Save meta info
function cricboard_post_meta_save($post_id) {
    // Check if our nonce is set.
    if(!isset($_POST['match_limit_nonce']) && !isset($_POST['win_points_nonce']) && !isset($_POST['part_points_nonce'])) {
        return;
    }
    
    // Verify that the nonce is valid.
    if(!wp_verify_nonce( $_POST['match_limit_nonce'], 'match_limit_nonce') && !wp_verify_nonce($_POST['win_points_nonce'], 'win_points_nonce') && !    wp_verify_nonce($_POST['part_points_nonce'], 'part_points_nonce') && !wp_verify_nonce($_POST['deadline_nonce'], 'deadline_nonce')) {
        return;
    }
    
    //Set match limit
    if(!isset($_POST['match_limit']) &&  !isset($_POST['win_points']) && !isset($_POST['part_points']) && !isset($_POST['deadline'])) {
        return;
    }
    
    $match_limit = intval(sanitize_text_field($_POST['match_limit']));
    $win_points = intval(sanitize_text_field($_POST['win_points']));
    $part_points = intval(sanitize_text_field($_POST['part_points']));
    $deadline = cricboard_sanitize_date_format($_POST['deadline']);    
    
    // Update the meta field in the database.
    update_post_meta($post_id, 'match_limit', $match_limit);
    update_post_meta($post_id, 'win_points', $win_points);
    update_post_meta($post_id, 'part_points', $part_points);
    update_post_meta($post_id, 'deadline', $deadline);
}
add_action('save_post', 'cricboard_post_meta_save');

//Include functions
require_once(plugin_dir_path(__FILE__).'cricboard_html_functions.php');
require_once(plugin_dir_path(__FILE__).'cricboard_leaderboard_functions.php');
require_once(plugin_dir_path(__FILE__).'cricboard_shortcodes.php');
require_once(plugin_dir_path(__FILE__).'cricboard_comment_functions.php');
require_once(plugin_dir_path(__FILE__).'cricboard_admin_functions.php');

//Add login, logout, profile links to main menu
function cricboard_user_links($items) {
    
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
    $current_url = $protocol.$_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
    
    $completed_leagues_begin = '<li class="menu-item-closed-leagues menu-item-has-children"><a href="javascript:void(0)">'.__('Closed Leagues','cricboard').'</a>';

    //Active leagues
    foreach(cricboard_get_leagues() as $league) {
        if(comments_open($league['ID'])) {
            if(get_permalink($league['ID']) == $current_url) $menu_class = ' current-menu-item'; else $menu_class = '';
            $items .= '<li class="menu-item-closed-league'.$menu_class.'"><a href="'.get_permalink($league['ID']).'">'.$league['post_title'].'</a></li>';
        }
    }
    
    //Closed leagues
    $completed_leagues = '';
    foreach(cricboard_get_leagues() as $league) {
        if(!comments_open($league['ID'])) {
            if(get_permalink($league['ID']) == $current_url) $menu_class = ' current-menu-item'; else $menu_class = '';
            $completed_leagues .= '<li class="menu-item-closed-league'.$menu_class.'"><a href="'.get_permalink($league['ID']).'">'.$league['post_title'].'</a></li>';
        }
    }
    if($completed_leagues != '') $completed_leagues = $completed_leagues_begin.'<ul>'.$completed_leagues.'</ul></li>';
    
    $items .= $completed_leagues;
    
	if(is_user_logged_in()) {
		$items .= '<li class="menu-item-myprofile"><a target="_blank" href="'.esc_url(get_edit_user_link()).'">'.__('My Profile','saaf').'</a></li>';
	}
    $items .= '<li class="menu-item-loginout">'.wp_loginout(get_permalink(), false).'</li>';
	return $items;
}
add_filter('wp_nav_menu_items', 'cricboard_user_links', 10, 2);

?>