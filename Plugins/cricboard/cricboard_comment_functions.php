<?php
/**
 * Customize comment form
 */
function cricboard_add_comment_fields() {
    global $post;
	
    if(get_post_type(get_the_ID()) == 'league') {
        $user_list = cricboard_dropdown_list(cricboard_users(get_current_user_id()), 'ID', 'display_name');
        echo cricboard_output_field('user', 'select', __('Opponent', 'cricboard'), '', $user_list, true);
        echo cricboard_output_field('my_runs', 'number', __('Runs', 'cricboard'), '', array(), false);
        echo cricboard_output_field('my_wickets', 'number', __('Wickets', 'cricboard'), '', array(), false);
        echo cricboard_output_field('my_overs', 'number', __('Overs', 'cricboard'), '', array(), false);
        echo cricboard_output_field('opp_runs', 'number', __('Opponent Runs', 'cricboard'), '', array(), false);
        echo cricboard_output_field('opp_wickets', 'number', __('Opponent Wickets', 'cricboard'), '', array(), false);
        echo cricboard_output_field('opp_overs', 'number', __('Opponent Overs', 'cricboard'), '', array(), false);
        echo cricboard_output_field('add_comments', 'textarea', __('Additional Comment', 'cricboard'), '', array(), false);
    }
}
add_action('comment_form_logged_in_after', 'cricboard_add_comment_fields');

//Verify additional comment fields
function cricboard_verify_comment_fields($commentdata) {
    $comment_txt = $commentdata['comment_content'];
    if(get_post_type($_POST['comment_post_ID']) == 'league') {
        if(trim($_POST['user']) == "") wp_die(__('Please select your opponent', 'cricboard'));
        $max_matches_allowed = get_post_meta($_POST['comment_post_ID'], 'match_limit', true);
        $matches_played = cricboard_total_matches(get_current_user_id(), $_POST['comment_post_ID'], $_POST['user']);        
        $max_matches_allowed = get_post_meta($_POST['comment_post_ID'], 'match_limit', true);
        if($matches_played >= $max_matches_allowed) wp_die(sprintf(__('You already played %s matches with %s','cricboard'), $matches_played, cricboard_user_display_name($_POST['user'])));        
        if(trim($_POST['my_runs']) != "" && !is_integer(intval(trim($_POST['my_runs'])))) wp_die(__('Please enter a numeric value for runs scored', 'cricboard'));
        if(trim($_POST['my_overs']) != "" && !is_numeric(trim($_POST['my_overs']))) wp_die(__('Please enter a numeric value for overs played', 'cricboard'));
        if(trim($_POST['my_wickets']) != "" && !is_integer(intval(trim($_POST['my_wickets'])))) wp_die(__('Please enter a numeric value for wickets lost', 'cricboard'));
        if(trim($_POST['opp_runs']) != "" && !is_integer(intval(trim($_POST['opp_runs'])))) wp_die(__('Please enter a numeric value for opponent&apos;s runs scored', 'cricboard'));
        if(trim($_POST['opp_overs']) != "" && !is_numeric(trim($_POST['opp_overs']))) wp_die(__('Please enter a numeric value for opponent&apos;s overs played', 'cricboard'));
        if(trim($_POST['opp_wickets']) != "" && !is_integer(intval(trim($_POST['opp_wickets'])))) wp_die(__('Please enter a numeric value for opponent&apos;s wickets lost', 'cricboard'));
        
        $opponent_name = cricboard_user_display_name($_POST['user']);
        $new_games_played = ($matches_played + 1);
        
        $comment_txt = __('Won against','cricboard').' '.$opponent_name.'. '.__('Game: ','cricboard').$new_games_played."\n";
        $comment_txt .= cricboard_user_display_name(get_current_user_id()).': Runs - '.trim($_POST['my_runs']).', Wickets - '.trim($_POST['my_wickets']).', Overs - '.trim($_POST['my_overs'])."\n";
        $comment_txt .= $opponent_name.': Runs - '.trim($_POST['opp_runs']).', Wickets - '.trim($_POST['opp_wickets']).', Overs - '.trim($_POST['opp_overs'])."\n";
        $comment_txt .= "\n".trim($_POST['add_comments']);
    }
    $commentdata['comment_content'] = $comment_txt;
    return $commentdata;
}
add_filter('preprocess_comment', 'cricboard_verify_comment_fields');

//Save comment meta
function cricboard_save_commentmeta($comment_id) {
    $opponent = wp_filter_nohtml_kses($_POST['user']);
    $my_runs = wp_filter_nohtml_kses($_POST['my_runs']);
    $my_overs = wp_filter_nohtml_kses($_POST['my_overs']);
    $my_wickets = wp_filter_nohtml_kses($_POST['my_wickets']);
    $opp_runs = wp_filter_nohtml_kses($_POST['opp_runs']);
    $opp_overs = wp_filter_nohtml_kses($_POST['opp_overs']);
    $opp_wickets = wp_filter_nohtml_kses($_POST['opp_wickets']);
    add_comment_meta($comment_id, 'opponent', $opponent);
    if(isset($my_runs)) add_comment_meta($comment_id, 'my_runs', $my_runs);
    if(isset($my_overs)) add_comment_meta($comment_id, 'my_overs', $my_overs);
    if(isset($my_wickets)) add_comment_meta($comment_id, 'my_wickets', $my_wickets);
    if(isset($opp_runs)) add_comment_meta($comment_id, 'opp_runs', $opp_runs);
    if(isset($opp_overs)) add_comment_meta($comment_id, 'opp_overs', $opp_overs);
    if(isset($opp_wickets)) add_comment_meta($comment_id, 'opp_wickets', $opp_wickets);
}
add_action('comment_post', 'cricboard_save_commentmeta');

/**
 * Hide the main comment field for league post type
 */
function cricboard_league_css() {
    if(get_post_type(get_the_ID()) == 'league') {
    ?>
    <style type="text/css">
        .comment-form-comment { display: none; }
    </style>
    <?php
    }
}
add_action("wp_head", "cricboard_league_css");

/**
 * Put a dummy comment for league post (which will be later replaced)
 */
function cricboard_league_js() {
    if(get_post_type(get_the_ID()) == 'league') {
    ?>
    <script type="text/javascript">
        jQuery("#submit").click(function() {
            jQuery("#comment").val('Won against');
        });
    </script>
    <?php
    }
}
add_action('wp_footer', 'cricboard_league_js');

/**
 * Modify comment title based on custom post type
 */
function cricboard_custom_comment_form_title( $defaults ){
    if(get_post_type(get_the_ID()) == 'league') {
        $defaults['title_reply'] = __('Post Scorecard', 'cricboard');
    }
    return $defaults;
}
add_filter('comment_form_defaults', 'cricboard_custom_comment_form_title', 20);
?>