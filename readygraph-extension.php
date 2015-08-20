<?php
	if ( ! defined( 'ABSPATH' ) ) exit;

  // Extension Configuration
	$rstw_plugin_slug = basename(dirname(__FILE__));
	$rstw_menu_slug = 'readygraph-app';
	$rstw_main_plugin_title = 'Really Simple Twitter Widget';

	add_action( 'wp_ajax_nopriv_rstw-myajax-submit', 'rstw_myajax_submit' );
	add_action( 'wp_ajax_rstw-myajax-submit', 'rstw_myajax_submit' );
	
function rstw_myajax_submit() {
	if ($_POST['adsoptimal_id']) update_option('readygraph_adsoptimal_id',sanitize_key($_POST['adsoptimal_id']));
	if ($_POST['adsoptimal_secret']) update_option('readygraph_adsoptimal_secret',sanitize_key($_POST['adsoptimal_secret']));
	if (isset($_POST['readygraph_monetize'])) update_option('readygraph_enable_monetize',sanitize_text_field($_POST['readygraph_monetize']));
    wp_die();
}

function readygraph_premium_page(){
	include('extension/readygraph/go-premium.php');
}
function readygraph_menu_page(){
	global $wpdb;
	$current_page = isset($_GET['ac']) ? $_GET['ac'] : '';
	switch($current_page)
	{
		case 'signup-popup':
			include('extension/readygraph/signup-popup.php');
			break;
		case 'invite-screen':
			include('extension/readygraph/invite-screen.php');
			break;
		case 'social-feed':
			include('extension/readygraph/social-feed.php');
			break;
		case 'site-profile':
			include('extension/readygraph/site-profile.php');
			break;
		case 'customize-emails':
			include('extension/readygraph/customize-emails.php');
			break;
		case 'deactivate-readygraph':
			include('extension/readygraph/deactivate-readygraph.php');
			break;
		case 'welcome-email':
			include('extension/readygraph/welcome-email.php');
			break;
		case 'retention-email':
			include('extension/readygraph/retention-email.php');
			break;
		case 'invitation-email':
			include('extension/readygraph/invitation-email.php');
			break;	
		case 'faq':
			include('extension/readygraph/faq.php');
			break;
		case 'monetization-settings':
			include('extension/readygraph/monetization.php');
			break;
		default:
			include('extension/readygraph/admin.php');
			break;
	}

}
	
function rstw_add_to_menu() 
{

	global $rstw_menu_slug;
	add_menu_page( __( 'Really Simple Twitter Widget', 'rstw' ), __( 'Really Simple Twitter Widget', 'rstw' ), 'admin_dashboard', 'really-simple-twitter-widget', 'readygraph_menu_page' );
	add_submenu_page('really-simple-twitter-widget', 'Go Premium', __( 'Go Premium', 'rstw' ), 'administrator', 'readygraph-go-premium', 'readygraph_premium_page');
}
	add_action('admin_menu', 'rstw_add_to_menu');
	// ReadyGraph Engine Hooker

include_once('extension/readygraph/extension.php');

function on_plugin_activated_readygraph_rstw_redirect(){
	global $rstw_menu_slug;
	$setting_url="admin.php?page=$rstw_menu_slug";    
	if (get_option('rg_rstw_plugin_do_activation_redirect', false)) {  
		delete_option('rg_rstw_plugin_do_activation_redirect'); 
		wp_redirect(admin_url($setting_url)); 
	}  
}

function add_rstw_readygraph_plugin_warning() {
  if (get_option('readygraph_access_token', '') != '') return;
  global $hook_suffix, $current_user, $rstw_menu_slug;
  if(isset($_GET["readygraph_notice"]) && $_GET["readygraph_notice"] == "dismiss") update_option('readygraph_connect_notice','false');
  if ( $hook_suffix == 'plugins.php' && get_option('readygraph_connect_notice') == 'true' ) {              
    echo '<div class="updated" style="padding: 0; margin: 0; border: none; background: none;">  
      <style type="text/css">  
        .readygraph_activate {
          min-width:825px;
          padding:7px;
          margin:15px 0;
          background:#1b75bb;
          -moz-border-radius:3px;
          border-radius:3px;
          -webkit-border-radius:3px;
          position:relative;
          overflow:hidden;
        }
        .readygraph_activate .aa_button {
          cursor: pointer;
          -moz-box-shadow:inset 0px 1px 0px 0px #ffffff;
          -webkit-box-shadow:inset 0px 1px 0px 0px #ffffff;
          box-shadow:inset 0px 1px 0px 0px #ffffff;
          background:-webkit-gradient( linear, left top, left bottom, color-stop(0.05, #f9f9f9), color-stop(1, #e9e9e9) );
          background:-moz-linear-gradient( center top, #f9f9f9 5%, #e9e9e9 100% );
          filter:progid:DXImageTransform.Microsoft.gradient(startColorstr="#f9f9f9", endColorstr="#e9e9e9");
          background-color:#f9f9f9;
          -webkit-border-top-left-radius:3px;
          -moz-border-radius-topleft:3px;
          border-top-left-radius:3px;
          -webkit-border-top-right-radius:3px;
          -moz-border-radius-topright:3px;
          border-top-right-radius:3px;
          -webkit-border-bottom-right-radius:3px;
          -moz-border-radius-bottomright:3px;
          border-bottom-right-radius:3px;
          -webkit-border-bottom-left-radius:3px;
          -moz-border-radius-bottomleft:3px;
          border-bottom-left-radius:3px;
          text-indent:0;
          border:1px solid #dcdcdc;
          display:inline-block;
          color:#333333;
          font-family: "Helvetica Neue",Helvetica,Arial,sans-serif;
          font-size:15px;
          font-weight:normal;
          font-style:normal;
          height:40px;
          line-height:40px;
          width:275px;
          text-decoration:none;
          text-align:center;
          text-shadow:1px 1px 0px #ffffff;
        }
        .readygraph_activate .aa_button:hover {
          background:-webkit-gradient( linear, left top, left bottom, color-stop(0.05, #e9e9e9), color-stop(1, #f9f9f9) );
          background:-moz-linear-gradient( center top, #e9e9e9 5%, #f9f9f9 100% );
          filter:progid:DXImageTransform.Microsoft.gradient(startColorstr="#e9e9e9", endColorstr="#f9f9f9");
          background-color:#e9e9e9;
        }
        .readygraph_activate .aa_button:active {
          position:relative;
          top:1px;
        }
        /* This button was generated using CSSButtonGenerator.com */
        .readygraph_activate .aa_description {
          position:absolute;
          top:19px;
          left:285px;
          margin-left:25px;
          color:#ffffff;
          font-size:15px;
          z-index:1000
        }
        .readygraph_activate .aa_description strong {
          color:#FFF;
          font-weight:normal
        }
		.aa_close {
		position: absolute;
		right: 18px;
		top: 18px;
		}
      </style>                       
      <form name="readygraph_activate" action="'.admin_url( 'admin.php?page=' . $rstw_menu_slug).'" method="POST"> 
        <input type="hidden" name="return" value="1"/>
        <div class="readygraph_activate">
          <div class="aa_button" onclick="document.readygraph_activate.submit();">  
            '.__('Connect Your ReadyGraph Account').'
          </div>  
          <div class="aa_description">'.__('<strong>Almost done</strong> - connect your account to start getting users.').'</div>
			<div class="aa_close"><a href="' . $_SERVER["PHP_SELF"] . '?readygraph_notice=dismiss"><img src="'.plugin_dir_url( __FILE__ ).'assets/dialog_close.png"></a></div>
        </div>  
      </form>  
    </div>';      
  }
}
  
	//add_action('admin_notices', 'add_rstw_readygraph_plugin_warning');
	if(get_option('readygraph_application_id') && strlen(get_option('readygraph_application_id')) > 0){
	if ((get_option('readygraph_access_token') && strlen(get_option('readygraph_access_token')) > 0)){
	add_action('wp_footer', 'rstw_readygraph_client_script_head', 9);
	}
	}
	add_action('admin_init', 'on_plugin_activated_readygraph_rstw_redirect');
	add_option('readygraph_connect_notice','true');

function rg_rstw_popup_options_enqueue_scripts() {
    if ( get_option('readygraph_popup_template') == 'default-template' ) {
        wp_enqueue_style( 'default-template', plugin_dir_url( __FILE__ ) .'extension/readygraph/assets/css/default-popup.css' );
    }
    if ( get_option('readygraph_popup_template') == 'red-template' ) {
        wp_enqueue_style( 'red-template', plugin_dir_url( __FILE__ ) .'extension/readygraph/assets/css/red-popup.css' );
    }
    if ( get_option('readygraph_popup_template') == 'blue-template' ) {
        wp_enqueue_style( 'blue-template', plugin_dir_url( __FILE__ ) .'extension/readygraph/assets/css/blue-popup.css' );
    }
	if ( get_option('readygraph_popup_template') == 'black-template' ) {
        wp_enqueue_style( 'black-template', plugin_dir_url( __FILE__ ) .'extension/readygraph/assets/css/black-popup.css' );
    }
	if ( get_option('readygraph_popup_template') == 'gray-template' ) {
        wp_enqueue_style( 'gray-template', plugin_dir_url( __FILE__ ) .'extension/readygraph/assets/css/gray-popup.css' );
    }
	if ( get_option('readygraph_popup_template') == 'green-template' ) {
        wp_enqueue_style( 'green-template', plugin_dir_url( __FILE__ ) .'extension/readygraph/assets/css/green-popup.css' );
    }
	if ( get_option('readygraph_popup_template') == 'yellow-template' ) {
        wp_enqueue_style( 'yellow-template', plugin_dir_url( __FILE__ ) .'extension/readygraph/assets/css/yellow-popup.css' );
    }
    if ( get_option('readygraph_popup_template') == 'custom-template' ) {  
		wp_enqueue_style( 'custom-template', plugin_dir_url( __FILE__ ) .'extension/readygraph/assets/css/custom-popup.css' );
    }	
}
	add_action( 'admin_enqueue_scripts', 'rg_rstw_popup_options_enqueue_scripts' );
	add_action( 'wp_enqueue_scripts', 'rg_rstw_popup_options_enqueue_scripts' );

function rstw_post_updated_send_email( $post_id ) {
	// If this is just a revision, don't send the email.
	$post_type = get_post_type( $post_id );
	if ('page' != $post_type && 'post' != $post_type) return;
	if ( wp_is_post_revision( $post_id ) ) return;
	if(get_option('readygraph_application_id') && strlen(get_option('readygraph_application_id')) > 0 && get_option('readygraph_send_blog_updates') == "true"){
		$post_title = get_the_title( $post_id );
		$post_url = get_permalink( $post_id );
		$post_content = get_post($post_id);
		$post_excerpt = (isset($post_content->post_excerpt) && (!empty($post_content->post_excerpt))) ? $post_content->post_excerpt : wp_trim_words(strip_tags(strip_shortcodes($post_content->post_content)),500);
		$attachments = get_children(array('post_parent' => $post_id,
			'post_status' => 'inherit',
			'post_type' => 'attachment',
			'post_mime_type' => 'image',
			'order' => 'ASC',
			'orderby' => 'menu_order ID'));
		$images_list = array();
		foreach($attachments as $att_id => $attachment) {
			$full_img_url = wp_get_attachment_url($attachment->ID);
			$images_list[] = $full_img_url;
		// Your Code here
		}
		$post_image = "";
		if ($images_list) $post_image = reset($images_list);
		$url = 'http://readygraph.com/api/v1/post.json/';
		$response = wp_remote_post($url, array( 'body' => array('is_wordpress'=>1, 'message' => $post_title, 'message_link' => $post_url, 'message_image_link' => $post_image, 'message_excerpt' => $post_excerpt,'client_key' => get_option('readygraph_application_id'), 'email' => get_option('readygraph_email'))));
		if ( is_wp_error( $response ) ) {
		$error_message = $response->get_error_message();
		} 	else {
		}
		$app_id = get_option('readygraph_application_id');
		wp_remote_get( "http://readygraph.com/api/v1/tracking?event=post_created&app_id=$app_id" );
	} else {
	}
}
	add_action('future_to_publish','rstw_post_updated_send_email');
	add_action('new_to_publish','rstw_post_updated_send_email');
	add_action('draft_to_publish','rstw_post_updated_send_email');

?>