<?php
/**
 * Cricboard Leaderboard Functions
 */

//Get All Users
function cricboard_users($exclude = null) {
    $output = array();
    if($exclude != null) $args = array('exclude' => $exclude, 'orderby' => 'display_name');
    else $args = array('orderby' => 'display_name');
    foreach(get_users($args) as $entry) {
        array_push($output, array('ID' => $entry->ID, 'display_name' => $entry->display_name));
    }
    return $output;
}

function cricboard_dropdown_list($list, $value, $label) {
    $return_list = array();
    foreach($list as $entry) {
        array_push($return_list, array('value' => $entry[$value], 'label' => $entry[$label]));
    }
    return $return_list;
}

//Get user display name
function cricboard_user_display_name($id) {
  $user_info = get_userdata($id);
  return $user_info->display_name;
}

//Convert date to readable format
function cricboard_format_date($date_str) {
    return date('jS F Y h:i A', strtotime($date_str));
}

//Get total number of games played by User
function cricboard_total_matches($user_id, $league_id = null, $opponent_id = null) {
    $wins = count(cricboard_win_matches($user_id, $league_id, $opponent_id));
    $losses = count(cricboard_lost_matches($user_id, $league_id, $opponent_id));
    $total_games = $wins + $losses;
    return $total_games;
}

//Get league names
function cricboard_get_leagues() {
    $leagues = get_posts(array('post_type' => 'league'));
    $output = array();
    foreach ($leagues as $entry) {
        array_push($output, array('ID' => $entry->ID, 'post_title' => $entry->post_title));
    }
    return $output;
}

//Get all matches
function cricboard_all_matches($league_id = null, $limit = null) {
    if($league_id != null && $limit != null) {
        $args = array('post_type' => 'league', 'post_id' => $league_id, 'orderby' => 'comment_ID', 'number' => $limit);
    } elseif($league_id != null) {
        $args = array('post_type' => 'league', 'post_id' => $league_id, 'orderby' => 'comment_ID');
    } elseif($limit != null) {
        $args = array('post_type' => 'league', 'number' => $limit, 'orderby' => 'comment_ID');
    } else {
        $args = array('post_type' => 'league', 'orderby' => 'comment_ID');
    }
    return get_comments($args);
}

//Get win matches results
function cricboard_win_matches($user_id, $league_id = null, $opponent_id = null) {
    if($league_id != null && $opponent_id != null) {
        $args = array('post_type' => 'league', 'post_id'=>$league_id, 'meta_key'=>'opponent', 'meta_value'=>$opponent_id, 'author__in'=> $user_id);
    } elseif($league_id != null) {
        $args = array('post_type' => 'league', 'author__in'=> $user_id, 'post_id'=>$league_id);
    } elseif($opponent_id != null) {
        $args = array('post_type' => 'league', 'author__in'=> $user_id, 'meta_key'=>'opponent', 'meta_value'=>$opponent_id);
    } else {
        $args = array('post_type' => 'league', 'author__in'=> $user_id);
    }
    return get_comments($args);
}

//Get lost matches results
function cricboard_lost_matches($user_id, $league_id = null, $opponent_id = null) {
    if($league_id != null && $opponent_id != null) {
        $args = array('post_type' => 'league', 'post_id'=>$league_id, 'meta_key'=>'opponent', 'meta_value'=>$user_id, 'author__in'=> $opponent_id);
    } elseif($league_id != null) {
        $args = array('post_type' => 'league', 'post_id'=>$league_id, 'meta_key'=>'opponent', 'meta_value'=>$user_id);
    } elseif($opponent_id != null) {
        $args = array('post_type' => 'league', 'meta_key'=>'opponent', 'meta_value'=>$user_id, 'author__in'=> $opponent_id);
    } else {
        $args = array('post_type' => 'league', 'meta_key'=>'opponent', 'meta_value'=>$user_id);
    }
    return get_comments($args);
}

//Get last 5 matches record
function cricboard_last_games($player_id, $post_id) {
    $data = array();
    $wins = cricboard_win_matches($player_id, $post_id);
    $loss = cricboard_lost_matches($player_id, $post_id);
    
    foreach($wins as $entry) {
        array_push ($data, array(
            'result' => __('Won', 'cricboard'),
            'posted' => cricboard_format_date($entry->comment_date),
            'comment_id' => $entry->comment_ID,
        ));
    }
    
    foreach($loss as $entry) {
        array_push ($data, array(
            'result' => __('Lost', 'cricboard'),
            'posted' => cricboard_format_date($entry->comment_date),
            'comment_id' => $entry->comment_ID,
        ));
    }
    
    $output = '';
    if(count($data) > 0) {
        $sorted_data = cricboard_sort_list($data, 'comment_id');
        $count = 0;
        foreach($sorted_data as $entry) {
            if($count >= 5) break;
            if($entry['result'] == 'Won')
              $output .= '<span class="win_flg">W</span>';
            else
              $output .= '<span class="lose_flg">L</span>';
            $count++;            
        }
    }
    
    return $output;
}

//Get league's overall leaderboard
function cricboard_league_leaderboard() {
    $users = get_users();
    $post_id = get_the_ID();
    $win_points = intval(get_post_meta($post_id, 'win_points', true)) == 0 ? 5 : intval(get_post_meta($post_id, 'win_points', true));
    $part_points = intval(get_post_meta($post_id, 'part_points', true));
    $data = array();
    $column_list = array(
        'player'=>array('key'=>'player', 'value'=>__('Player', 'cricboard')),
        'matches'=>array('key'=>'matches', 'value'=>__('Matches Played', 'cricboard')),
        'wins'=>array('key'=>'wins', 'value'=>__('Won', 'cricboard')),
        'loss'=>array('key'=>'loss', 'value'=>__('Lost', 'cricboard')),
        'points'=>array('key'=>'points', 'value'=>__('Points', 'cricboard')),
        'percent'=>array('key'=>'percent', 'value'=>__('Win Percent (%)', 'cricboard')),
        'last_games'=>array('key'=>'last_games', 'value'=>__('Last 5 Games', 'cricboard')),
    );
    foreach($users as $user_entry) {
        $player_name = $user_entry->display_name;
        $player_id = $user_entry->ID;
        $wins = count(cricboard_win_matches($player_id, $post_id));
        $loss = count(cricboard_lost_matches($player_id, $post_id));
        $total_matches = $wins + $loss;
        $points = ($wins * $win_points) + ($total_matches * $part_points);
        if($total_matches == 0) $win_percent = 0;
        else $win_percent = round(floatval(($wins / $total_matches) * 100), 2);
        $last_games = cricboard_last_games($player_id, $post_id);
        if($total_matches > 0) {
            array_push($data, array(
                'player' => $player_name,
                'matches' => $total_matches,
                'wins' => $wins,
                'loss' => $loss,
                'points' => $points,
                'percent' => $win_percent,
                'last_games' => $last_games
            ));
        }
    }
	if(count($data) > 0) {
    	$sorted_data = cricboard_sort_list($data, 'percent', 'points');
	} else $sorted_data = array();
    $points_text = '<p><i>'.__('Win Points:', 'cricboard').' '.get_post_meta($post_id, 'win_points', true).', '.__('Participation Points:','cricboard').' '.get_post_meta($post_id, 'part_points', true).'</i></p>';
    $output = '<h3>'.__('Leaderboard','cricboard').'</h3>'.$points_text.cricboard_html_leaderboard($sorted_data, $column_list, 'league-leaderboard');
    return $output;
}

//Top run scrorer leaderboard
function cricboard_leading_run_scorer() {
    $users = get_users();
    $post_id = get_the_ID();
    $data = array();
    $column_list = array(
        'player'=>array('key'=>'player', 'value'=>__('Player', 'cricboard')),
        'matches'=>array('key'=>'matches', 'value'=>__('Matches Played', 'cricboard')),
        'runs'=>array('key'=>'runs', 'value'=>__('Runs Scored', 'cricboard')),
    );
    foreach($users as $user_entry) {
        $player_name = $user_entry->display_name;
        $player_id = $user_entry->ID;
        $wins = cricboard_win_matches($player_id, $post_id);
        $loss = cricboard_lost_matches($player_id, $post_id);
        $total_matches = count($wins) + count($loss);
        $total_runs = 0;
        foreach($wins as $run_entry) {
            $run = intval(get_comment_meta($run_entry->comment_ID, 'my_runs', true));
            $total_runs = $total_runs + $run;
        }        
        foreach($loss as $run_entry) {
            $run = intval(get_comment_meta($run_entry->comment_ID, 'opp_runs', true));
            $total_runs = $total_runs + $run;
        }
        if($total_matches > 0) {
            array_push($data, array(
                'player' => $player_name,
                'matches' => $total_matches,
                'runs' => $total_runs,
            ));
        }
    }
	
	if(count($data) > 0) {
    	$sorted_data = cricboard_sort_list($data, 'runs');
	} else $sorted_data = array();
    
    $output = '<h4>'.__('Leading Run Scorers','cricboard').'</h4>'.cricboard_html_leaderboard($sorted_data, $column_list, 'runs-leaderboard', 3);
    return $output;
}

//Top wicket taker leaderboard
function cricboard_leading_wicket_takers() {
    $users = get_users();
    $post_id = get_the_ID();
    $data = array();
    $column_list = array(
        'player'=>array('key'=>'player', 'value'=>__('Player', 'cricboard')),
        'matches'=>array('key'=>'matches', 'value'=>__('Matches Played', 'cricboard')),
        'wickets'=>array('key'=>'wickets', 'value'=>__('Wickets Taken', 'cricboard')),
    );
    foreach($users as $user_entry) {
        $player_name = $user_entry->display_name;
        $player_id = $user_entry->ID;
        $wins = cricboard_win_matches($player_id, $post_id);
        $loss = cricboard_lost_matches($player_id, $post_id);
        $total_matches = count($wins) + count($loss);
        $total_wickets = 0;
        foreach($wins as $wicket_entry) {
            $wicket = intval(get_comment_meta($wicket_entry->comment_ID, 'opp_wickets', true));
            $total_wickets = $total_wickets + $wicket;
        }        
        foreach($loss as $wicket_entry) {
            $wicket = intval(get_comment_meta($wicket_entry->comment_ID, 'my_wickets', true));
            $total_wickets = $total_wickets + $wicket;
        }
        if($total_matches > 0) {
            array_push($data, array(
                'player' => $player_name,
                'matches' => $total_matches,
                'wickets' => $total_wickets,
            ));            
        }
    }
	if(count($data) > 0) {
    	$sorted_data = cricboard_sort_list($data, 'wickets');
	} else $sorted_data = array();
    $output = '<h4>'.__('Leading Wicket Takers','cricboard').'</h4>'.cricboard_html_leaderboard($sorted_data, $column_list, 'wickets-leaderboard', 3);
    return $output;
}

//My Matches
function cricboard_my_matches() {
    $user_id = get_current_user_id();
    $data = array();
    $column_list = array(
        'league'=>array('key'=>'league', 'value'=>__('League', 'cricboard')),
        'result'=>array('key'=>'result', 'value'=>__('Result', 'cricboard')),
        'opponent'=>array('key'=>'opponent', 'value'=>__('Opponent', 'cricboard')),
        'posted'=>array('key'=>'posted', 'value'=>__('Date Posted', 'cricboard')),
        'link'=>array('key'=>'link', 'value'=>__('Scorecard', 'cricboard')),
    );
    $wins = cricboard_win_matches($user_id);
    $loss = cricboard_lost_matches($user_id);
    
    foreach($wins as $entry) {
        array_push ($data, array(
            'league' => get_the_title($entry->comment_post_ID),
            'result' => __('Won', 'cricboard'),
            'opponent' => cricboard_user_display_name(get_comment_meta($entry->comment_ID, 'opponent', true)),
            'posted' => cricboard_format_date($entry->comment_date),
            'link' => '<a href="'.get_comment_link($entry->comment_ID).'">'.__('View Scorecard','cricboard').'</a>',
            'comment_id' => $entry->comment_ID,
        ));
    }
    
    foreach($loss as $entry) {
        array_push ($data, array(
            'league' => get_the_title($entry->comment_post_ID),
            'result' => __('Lost', 'cricboard'),
            'opponent' => cricboard_user_display_name($entry->user_id),
            'posted' => cricboard_format_date($entry->comment_date),
            'link' => '<a href="'.get_comment_link($entry->comment_ID).'">'.__('View Scorecard','cricboard').'</a>',
            'comment_id' => $entry->comment_ID,
        ));
    }
    
	if(count($data) > 0) {
    	$sorted_data = cricboard_sort_list($data, 'comment_id');  
	} else $sorted_data = array();
    $filter_output = cricboard_output_field('league-filter', 'select', __('by league','cricboard'), '', cricboard_dropdown_list(cricboard_get_leagues(), 'post_title', 'post_title'));
    $filter_output .= cricboard_output_field('opponent-filter', 'select', __('by opponent','cricboard'), '', cricboard_dropdown_list(cricboard_users(get_current_user_id()), 'display_name', 'display_name'));
    $output = '<p>'.__('Filter','cricboard').' '.$filter_output.'</p>'.cricboard_html_leaderboard($sorted_data, $column_list, 'my-matches');
    return $output;
}

//Recent matches
function cricboard_recent_matches() {
    $data = array();
    $column_list = array(
        'league'=>array('key'=>'league', 'value'=>__('League', 'cricboard')),
        'player'=>array('key'=>'player', 'value'=>__('Player', 'cricboard')),
        'result'=>array('key'=>'result', 'value'=>__('Result', 'cricboard')),
        'opponent'=>array('key'=>'opponent', 'value'=>__('Opponent', 'cricboard')),
        'posted'=>array('key'=>'posted', 'value'=>__('Date Posted', 'cricboard')),
        'link'=>array('key'=>'link', 'value'=>__('Scorecard', 'cricboard')),
    );
    $matches = cricboard_all_matches(null, 5);
    foreach($matches as $entry) {
        array_push ($data, array(
            'league' => get_the_title($entry->comment_post_ID),
            'player' => cricboard_user_display_name($entry->user_id),
            'result' => __('Won against', 'cricboard'),
            'opponent' => cricboard_user_display_name(get_comment_meta($entry->comment_ID, 'opponent', true)),
            'posted' => cricboard_format_date($entry->comment_date),
            'link' => '<a href="'.get_comment_link($entry->comment_ID).'">'.__('View Scorecard','cricboard').'</a>',
        ));
    }
    $filter_output = cricboard_output_field('league-filter', 'select', __('by league','cricboard'), '', cricboard_dropdown_list(cricboard_get_leagues(), 'post_title', 'post_title'));
    $output = '<p>'.__('Filter','cricboard').' '.$filter_output.'</p>'.cricboard_html_leaderboard($data, $column_list, 'recent-matches');
    return $output;
}

//Sorting function
function cricboard_sort_list($arr, $sort_key1, $sort_key2 = null, $order = 'DESC') {
    if($order == 'ASC') $sort_by = SORT_ASC; else $sort_by = SORT_DESC;
    array_multisort(array_column($arr, $sort_key1), $sort_by, $arr);
    if($sort_key2 != null) {
        array_multisort(array_column($arr, $sort_key2), $sort_by, $arr);
    }
    return $arr;
}

//Custom array_column for PHP < 5.5
if (! function_exists('array_column')) {
    function array_column(array $input, $columnKey, $indexKey = null) {
        $array = array();
        foreach ($input as $value) {
            if ( !array_key_exists($columnKey, $value)) {
                trigger_error("Key \"$columnKey\" does not exist in array");
                return false;
            }
            if (is_null($indexKey)) {
                $array[] = $value[$columnKey];
            }
            else {
                if ( !array_key_exists($indexKey, $value)) {
                    trigger_error("Key \"$indexKey\" does not exist in array");
                    return false;
                }
                if ( ! is_scalar($value[$indexKey])) {
                    trigger_error("Key \"$indexKey\" does not contain scalar value");
                    return false;
                }
                $array[$value[$indexKey]] = $value[$columnKey];
            }
        }
        return $array;
    }
}
?>