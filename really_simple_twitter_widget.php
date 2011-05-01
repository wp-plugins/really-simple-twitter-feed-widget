<?php
/*
Plugin Name: Really Simple Twitter Feed Widget
Plugin URI: http://www.whiletrue.it/
Description: Displays your public Twitter messages in the sidbar of your blog. Simply add your username and all your visitors can see your tweets!
Author: WhileTrue
Version: 1.1.0
Author URI: http://www.whiletrue.it/
*/

/*
    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License version 2, 
    as published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.
*/


$twitter_options['widget_fields']['title'] = array('label'=>'Sidebar Title:', 'type'=>'text', 'default'=>'');
$twitter_options['widget_fields']['username'] = array('label'=>'Twitter Username:', 'type'=>'text', 'default'=>'');
$twitter_options['widget_fields']['num'] = array('label'=>'How many tweets:', 'type'=>'text', 'default'=>'5');
$twitter_options['widget_fields']['update'] = array('label'=>'Show timestamps:', 'type'=>'checkbox', 'default'=>true);
$twitter_options['widget_fields']['linked'] = array('label'=>'Linked:', 'type'=>'text', 'default'=>'#');
$twitter_options['widget_fields']['hyperlinks'] = array('label'=>'Show Hyperlinks:', 'type'=>'checkbox', 'default'=>true);
$twitter_options['widget_fields']['twitter_users'] = array('label'=>'Find @replies:', 'type'=>'checkbox', 'default'=>true);
$twitter_options['widget_fields']['skip_text'] = array('label'=>'Skip tweets containing this text:', 'type'=>'text', 'default'=>'');
$twitter_options['widget_fields']['encode_utf8'] = array('label'=>'UTF8 Encode:', 'type'=>'checkbox', 'default'=>false);
$twitter_options['prefix'] = 'twitter';

// Display Twitter messages
function really_simple_twitter_messages($options) {
	include_once(ABSPATH . WPINC . '/rss.php');
	
	// CHECK OPTIONS
	
	if ($options['username'] == '') {
		return __('RSS not configured','really_simple_twitter_widget');
	} 
	
	if (!is_numeric($options['num']) or $options['num']<=0) {
		return __('Number of tweets not valid','really_simple_twitter_widget');
	}

	// SET THE NUMBER OF ITEMS TO RETRIEVE - IF "SKIP TEXT" IS ACTIVE, GET MORE ITEMS
	$max_items_to_retrieve = $options['num'];
	if ($options['skip_text']!='') {
		$max_items_to_retrieve *= 3;
	}
	
	// MODIFY FEED CACHE LIFETIME ONLY FOR THIS FEED (30 minutes)
	add_filter( 'wp_feed_cache_transient_lifetime', create_function( '$a', 'return 1800;' ) );

	//$rss = fetch_feed('http://twitter.com/statuses/user_timeline/'.$options['username'].'.rss');
	// USE THE NEW TWITTER REST API
	$rss = fetch_feed('http://api.twitter.com/1/statuses/user_timeline.rss?screen_name='.$options['username'].'&count='.$max_items_to_retrieve);


	// RESET STANDARD FEED CACHE LIFETIME (12 hours)
	remove_filter( 'wp_feed_cache_transient_lifetime', create_function( '$a', 'return 1800;' ) );

	if (is_wp_error($rss)) {
		return __('WP Error: Feed not created correctly','really_simple_twitter_widget');
	}

	$max_items_retrieved = $rss->get_item_quantity(); 

	if ($max_items_retrieved==0) {
		return __('No public Twitter messages','really_simple_twitter_widget');
	}
	
	// SET THE MAX NUMBER OF ITEMS  
	$num_items_shown = $options['num'];
	if ($max_items_retrieved<$options['num']) {
		$num_items_shown = $max_items_retrieved;
	}
		
	$out = '<ul class="really_simple_twitter_widget">';

	// BUILD AN ARRAY OF ALL THE ITEMS, STARTING WITH ELEMENT 0 (FIRST ELEMENT).
	$rss_items = $rss->get_items(0, $max_items_retrieved); 

	$i = 0;
	foreach ($rss_items as $message) {
		if ($i>=$num_items_shown) {
			break;
		}
		$msg = " ".substr(strstr($message->get_description(),': '), 2, strlen($message->get_description()))." ";
		
		if ($options['skip_text']!='' and strpos($msg, $options['skip_text'])!==false) {
			continue;
		}
		if($options['encode_utf8']) $msg = utf8_encode($msg);
				
		$out .= '<li>';

		if ($options['hyperlinks']) { 
			// match protocol://address/path/file.extension?some=variable&another=asf%
			$msg = preg_replace('/\b([a-zA-Z]+:\/\/[\w_.\-]+\.[a-zA-Z]{2,6}[\/\w\-~.?=&%#+$*!]*)\b/i',"<a href=\"$1\" class=\"twitter-link\">$1</a>", $msg);
			// match www.something.domain/path/file.extension?some=variable&another=asf%
			$msg = preg_replace('/\b(?<!:\/\/)(www\.[\w_.\-]+\.[a-zA-Z]{2,6}[\/\w\-~.?=&%#+$*!]*)\b/i',"<a href=\"http://$1\" class=\"twitter-link\">$1</a>", $msg);    
			// match name@address
			$msg = preg_replace('/\b([a-zA-Z][a-zA-Z0-9\_\.\-]*[a-zA-Z]*\@[a-zA-Z][a-zA-Z0-9\_\.\-]*[a-zA-Z]{2,6})\b/i',"<a href=\"mailto://$1\" class=\"twitter-link\">$1</a>", $msg);
			 //mach #trendingtopics
			$msg = preg_replace('/([\.|\,|\:|\¡|\¿|\>|\{|\(]?)#{1}(\w*)([\.|\,|\:|\!|\?|\>|\}|\)]?)\s/i', "$1<a href=\"http://twitter.com/#search?q=$2\" class=\"twitter-link\">#$2</a>$3 ", $msg);
		}
		if ($options['twitter_users'])  { 
			$msg = preg_replace('/([\.|\,|\:|\¡|\¿|\>|\{|\(]?)@{1}(\w*)([\.|\,|\:|\!|\?|\>|\}|\)]?)\s/i', "$1<a href=\"http://twitter.com/$2\" class=\"twitter-user\">@$2</a>$3 ", $msg);
		}
          					
		$link = $message->get_permalink();
		if($options['linked'] == 'all')  { 
			$msg = '<a href="'.$link.'" class="twitter-link">'.$msg.'</a>';  // Puts a link to the status of each tweet 
		} else if ($options['linked'] != '') {
			$msg = $msg . '<a href="'.$link.'" class="twitter-link">'.$options['linked'].'</a>'; // Puts a link to the status of each tweet
		} 
		$out .= $msg;
		
		if($options['update']) {				
			$time = strtotime($message->get_date());
			$h_time = ( ( abs( time() - $time) ) < 86400 ) ? sprintf( __('%s ago'), human_time_diff( $time )) : date(__('Y/m/d'), $time);
			$out .= ', '.sprintf( __('%s', 'twitter-for-wordpress'),' <span class="twitter-timestamp"><abbr title="' . date(__('Y/m/d H:i:s'), $time) . '">' . $h_time . '</abbr></span>' );
		}          
                  
		$out .= '</li>';
		$i++;
	}
	$out .= '</ul>';
	return $out;
}


// WIDGET FUNCTION
function really_simple_widget_twitter_init() {

	if ( !function_exists('register_sidebar_widget') ) {
		return;
	}
	$check_options = get_option('really_simple_twitter_widget');
	if (!is_numeric($check_options['number']) or $check_options['number'] < 1) {
		$check_options['number'] = 1;
		update_option('really_simple_twitter_widget', $check_options);
	}
  
	function really_simple_widget_twitter($args, $number = 1) {

		global $twitter_options;
		
		// $args is an array of strings that help widgets to conform to
		// the active theme: before_widget, before_title, after_widget,
		// and after_title are the array keys. Default tags: li and h2.
		extract($args);

		// Each widget can store its own options. We keep strings here.
		$options = get_option('really_simple_twitter_widget');
		
		// fill options with default values if value is not set
		$item = $options[$number];
		foreach($twitter_options['widget_fields'] as $key => $field) {
			if (! isset($item[$key])) {
				$item[$key] = $field['default'];
			}
		}
		
		// These lines generate our output.
		echo $before_widget . $before_title . '<a href="http://twitter.com/' . $item['username'] . '" class="twitter_title_link">'. $item['title'] . '</a>' . $after_title;
		echo really_simple_twitter_messages($item);
		echo $after_widget;
	}

	// This is the function that outputs the form to let the users edit
	// the widget's title. It's an optional feature that users cry for.
	function really_simple_widget_twitter_control($number) {
	
		global $twitter_options;

		// Get our options and see if we're handling a form submission.
		$options = get_option('really_simple_twitter_widget');
		if ( isset($_POST['twitter-submit']) ) {

			foreach($twitter_options['widget_fields'] as $key => $field) {
				$options[$number][$key] = $field['default'];
				$field_name = sprintf('%s_%s_%s', $twitter_options['prefix'], $key, $number);

				if ($field['type'] == 'text') {
					$options[$number][$key] = strip_tags(stripslashes($_POST[$field_name]));
				} elseif ($field['type'] == 'checkbox') {
					$options[$number][$key] = isset($_POST[$field_name]);
				}
			}

			update_option('really_simple_twitter_widget', $options);
		}

		foreach($twitter_options['widget_fields'] as $key => $field) {
			
			$field_name = sprintf('%s_%s_%s', $twitter_options['prefix'], $key, $number);
			$field_checked = '';
			if ($field['type'] == 'text') {
				$field_value = htmlspecialchars($options[$number][$key], ENT_QUOTES);
			} elseif ($field['type'] == 'checkbox') {
				$field_value = 1;
				if (! empty($options[$number][$key])) {
					$field_checked = 'checked="checked"';
				}
			}
			
			printf('<p style="text-align:right;" class="twitter_field"><label for="%s">%s <input id="%s" name="%s" type="%s" value="%s" class="%s" %s /></label></p>',
				$field_name, __($field['label']), $field_name, $field_name, $field['type'], $field_value, $field['type'], $field_checked);
		}

		echo '<input type="hidden" id="twitter-submit" name="twitter-submit" value="1" />';
	}
	
	function really_simple_widget_twitter_setup() {
		$options = $newoptions = get_option('really_simple_twitter_widget');
		
		if ( isset($_POST['twitter-number-submit']) ) {
			$number = (int) $_POST['twitter-number'];
			$newoptions['number'] = $number;
		}
		
		if ( $options != $newoptions ) {
			update_option('really_simple_twitter_widget', $newoptions);
			widget_twitter_register();
		}
	}
	
	
	function really_simple_widget_twitter_register() {
		
		$options = get_option('really_simple_twitter_widget');
		$dims = array('width' => 300, 'height' => 300);
		$class = array('classname' => 'widget_twitter');

		$name = __('Really Simple Twitter');
		$id = "really-simple-twitter"; // Never never never translate an id
		wp_register_sidebar_widget($id, $name, 'really_simple_widget_twitter', $class, 1);
		wp_register_widget_control($id, $name, 'really_simple_widget_twitter_control', $dims, 1);
		
		add_action('sidebar_admin_setup', 'really_simple_widget_twitter_setup');
	}

	really_simple_widget_twitter_register();
}

// Run our code later in case this loads prior to any required plugins.
add_action('widgets_init', 'really_simple_widget_twitter_init');
