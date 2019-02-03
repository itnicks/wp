<?php
/**
 * Render HTML Components
 */
function cricboard_output_field($id,$type, $label, $value, $option_list = array(), $req = false) {
    if($req) $req_label = ' *'; else $req_label = '';
    if($label != "") $label = '<label>'.$label.$req_label.'</label>'; else $label = '';
    if($type == "select") {
        $option_value_list = '';
        foreach($option_list as $entry) {
            $option_value_list .= '<option value="'.$entry['value'].'">'.$entry['label'].'</option>';
        }
        $output = '
        <p class="'.$id.'-field">
            '.$label.'
            <select name="'.$id.'" id="'.$id.'" class="'.$id.'">
                <option value=""></option>
                '.$option_value_list.'
            </select>
        </p>';
    }
    if($type == "textarea") {
        $output = '
        <p class="'.$id.'-field">
            '.$label.'
            <textarea name="'.$id.'" id="'.$id.'" class="'.$id.'">'.$value.'</textarea>
        </p>';
    }
    if($type == "hidden") {
        $output = '
        <input type="'.$type.'" name="'.$id.'" id="'.$id.'" class="'.$id.'" value="'.$value.'" />
        ';
    }
    if($type != "select" && $type != "textarea" && $type != "hidden") {
        $output = '
        <p class="'.$id.'-field">
            '.$label.'
            <input type="'.$type.'" name="'.$id.'" id="'.$id.'" class="'.$id.'" value="'.$value.'" />
        </p>';
    }
    
    return $output;
}

//Output leaderboard as table
function cricboard_html_leaderboard($data, $column_list, $id, $limit = null) {
    $output = '';    
    if(count($data) > 0 && count($column_list) > 0) {
        $col_count = count($column_list);        
        $output .= '<table id="'.$id.'-container" class="leaderboard-container">';
        $output .= '<tr class="leaderboard-header-row">';        
        foreach($column_list as $col) {
            $output .= '<th class="leaderboard-header-col">'.$col['value'].'</th>';            
        }
        $output .= '</tr>';
        
        $count = 0;
        foreach($data as $entry) {
            $data_attr = '';
            $count++;
            foreach($column_list as $col) {
                $data_attr .= ' data-'.wp_filter_nohtml_kses($col['key']).'="'.wp_filter_nohtml_kses($entry[$col['key']]).'"';
            }
            $output .= '<tr class="leaderboard-row"'.$data_attr.'>';            
            foreach($column_list as $col) {
                $output .= '<td class="leaderboard-col">'.$entry[$col['key']].'</td>';
            }
            $output .= '</tr>';
            if($limit != null && $count >= $limit) break;
        }
        $output .= '</table>';
    }
    return $output;
}

//Output leaderboard
function cricboard_html_leaderboard_as_table($data, $column_list, $id, $limit = null) {
    $output = '';    
    if(count($data) > 0 && count($column_list) > 0) {
        $col_count = count($column_list);        
        $output .= '<div id="'.$id.'-container" class="leaderboard-container">';
        $output .= '<div class="leaderboard-header-inline-row leaderboard-header-row">';        
        foreach($column_list as $col) {
            $output .= '<div style="width:'.(100/$col_count).'%;" class="leaderboard-header-inline-col leaderboard-header-col">'.$col['value'].'</div>';            
        }
        $output .= '</div>';
        
        $count = 0;
        foreach($data as $entry) {
            $data_attr = '';
            $count++;
            foreach($column_list as $col) {
                $data_attr .= ' data-'.wp_filter_nohtml_kses($col['key']).'="'.wp_filter_nohtml_kses($entry[$col['key']]).'"';
            }
            $output .= '<div class="leaderboard-inline-row leaderboard-row"'.$data_attr.'>';            
            foreach($column_list as $col) {
                $output .= '<div class="leaderboard-inline-col leaderboard-col">'.$entry[$col['key']].'</div>';
            }
            $output .= '</div>';
            if($limit != null && $count >= $limit) break;
        }
        $output .= '</div>';
    }
    return $output;
}
?>