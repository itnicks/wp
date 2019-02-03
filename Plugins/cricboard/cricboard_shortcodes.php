<?php
/**
 * Shortcodes
 */
//Add shortcode for leaderboard creation
function cricboard_leaderboard_shortcode($content) {
    $content .= '<p>'.cricboard_league_leaderboard().'</p>';
    return $content;
}

//Shortcode for leading run scorer
function cricboard_leading_run_scorer_shortcode($content) {
    $content .= '<p>'.cricboard_leading_run_scorer().'</p>';
    return $content;
}

//Shortcode for leading wicket taker
function cricboard_leading_run_wickettaker_shortcode($content) {
    $content .= '<p>'.cricboard_leading_wicket_takers().'</p>';
    return $content;
}

//Shortcode for mygames creation
function cricboard_mygames_shortcode($content) {
    $content .= '<p>'.cricboard_my_matches().'</p>';
    return $content;
}

//Shortcode for recent games creation
function cricboard_recentgames_shortcode($content) {
    $content .= '<p>'.cricboard_recent_matches().'</p>';
    return $content;
}

//Shortcode for showing countdown timer
function cricboard_show_deadline_shortcode($content) {
    $post_id = get_the_ID();
    $deadline = get_post_meta($post_id, 'deadline', true);
    $countdown_html = '<h3>'.__('League Deadline','cricboard').'</h3><h4 id="deadline_placeholder"></h4><input type="hidden" id="deadline" value="'.$deadline.'"/>';
    $content .= $countdown_html;
    return $content;
}

add_shortcode('leaderboard', 'cricboard_leaderboard_shortcode');
add_shortcode('run_scorer', 'cricboard_leading_run_scorer_shortcode');
add_shortcode('wicket_taker', 'cricboard_leading_run_wickettaker_shortcode');
add_shortcode('mygames', 'cricboard_mygames_shortcode');
add_shortcode('recentgames', 'cricboard_recentgames_shortcode');
add_shortcode('deadline', 'cricboard_show_deadline_shortcode');

/* Add shortcode buttons */
function cricboard_add_shortcode_button() {
    add_filter('mce_external_plugins', 'cricboard_shortcode_button_plugin');
    add_filter('mce_buttons', 'cricboard_register_shortcode_button');
}

function cricboard_register_shortcode_button($buttons) {
    array_push($buttons,"leaderboard");
    array_push($buttons,"run_scorer");
    array_push($buttons,"wicket_taker");
    array_push($buttons,"deadline");
    return $buttons;
}

function cricboard_shortcode_button_plugin($plugin_array) {
    $plugin_array['leaderboard'] = plugin_dir_url(__FILE__).'js/cricboard-shortcode-button.js';
    $plugin_array['run_scorer'] = plugin_dir_url(__FILE__).'js/cricboard-shortcode-button.js';
    $plugin_array['wicket_taker'] = plugin_dir_url(__FILE__).'js/cricboard-shortcode-button.js';
    $plugin_array['deadline'] = plugin_dir_url(__FILE__).'js/cricboard-shortcode-button.js';
    return $plugin_array;
}
add_action('init','cricboard_add_shortcode_button');
?>