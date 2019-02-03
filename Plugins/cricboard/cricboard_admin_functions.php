<?php
/**
 * Admin Functions
 */
/*error_reporting(E_ALL);
//include PSN lib
require_once plugin_dir_path(__FILE__).'vendor/autoload.php';
use PlayStation\Client;
$client = new Client(["verify" => false, "proxy" => "localhost:8888"]);
$client->login(getenv('bea851f5-9a10-4d60-871a-dd97e4cf7028'));
$refreshToken = $client->refreshToken();
*/
function cricboard_user_meta_field($user) {
	$psn = esc_attr(get_user_meta($user->ID, 'psn', true));
    ?>
    <table class="form-table">
    <tr class="user-psn-wrap" id="psn-form">
        <th><?php _e('Playstation User (PSN)','cricboard'); ?>:</th>
        <td><?php echo cricboard_output_field('psn', 'text', '', $psn, array(), false); ?></td>
    </tr>
    </table>
    <?php
}
add_action( 'show_user_profile', 'cricboard_user_meta_field' );
add_action( 'edit_user_profile', 'cricboard_user_meta_field' );

function cricinfo_save_user_meta( $user_id ) {
    if ( !current_user_can( 'edit_user', $user_id ) ) { 
        return false; 
    }
    update_user_meta( $user_id, 'psn', $_POST['psn'] );
}
add_action( 'personal_options_update', 'cricinfo_save_user_meta' );
add_action( 'edit_user_profile_update', 'cricinfo_save_user_meta' );

/**
 * Remove unwanted fields
 */
if(is_admin()) {
    remove_action( 'admin_color_scheme_picker', 'admin_color_scheme_picker' );
    add_action( 'personal_options', 'cricboard_hide_personal_options' );
}

function cricboard_remove_contact_methods( $contactmethods ) {

    unset( $contactmethods['aim'] );
    unset( $contactmethods['jabber'] );
    unset( $contactmethods['yim'] );

    return $contactmethods;

}
add_filter( 'user_contactmethods', 'cricboard_remove_contact_methods' );

function cricboard_hide_personal_options() {
  ?>
    <script type="text/javascript">
        jQuery( document ).ready(function( $ ){
            $( '#your-profile .form-table:first, #your-profile h3:first' ).remove();
            $(".user-url-wrap").remove();
            $(".user-description-wrap").remove();
            $(".user-sessions-wrap").remove();
            $("#psn-form").insertAfter(".user-display-name-wrap");
        } );
    </script>
  <?php
}

//Change login page logo
function cricboard_login_logo() {
    $img_src = plugin_dir_url(__FILE__).'images/logo.png';
    
    if($img_src != '') {
        ?>
        <style type="text/css">
            #login h1 a, .login h1 a {
                background-image: url(<?php echo $img_src; ?>);
                height:72px;
                width: auto;
                background-size: auto 72px;
                background-repeat: no-repeat;
                padding-bottom: 30px;
            }
        </style>
        <?php
    }
}
add_action( 'login_enqueue_scripts', 'cricboard_login_logo' );
/*
function cricboard_authenticate_psn_token() {
	global $client;
	$client->login('b7aeb485-xxxx-4ec2-zzzz-0f23bcee5bc5', '421550');
	$refreshToken = $client->refreshToken();
}
//cricboard_authenticate_psn_token();
*/

function cricboard_sanitize_date_format($date) {
		if (preg_match("/^(0[1-9]|[1-2][0-9]|3[0-1])-(0[1-9]|1[0-2])-[0-9]{4}$/",$date)) {
				return $date;
		} else {
				return "";
		}
}

function cricboard_score_update() {
		$matches = cricboard_all_matches();
		foreach($matches as $entry) {
			
		}
}
?>