<?php
/*
Plugin Name: Really Simple Twitter Feed Widget
Plugin URI: http://www.whiletrue.it/
Description: Displays your public Twitter messages in the sidbar of your blog. Simply add your username and all your visitors can see your tweets!
Author: WhileTrue
Version: 2.0.2
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

/**
 * ReallySimpleTwitterWidget Class
 */
class ReallySimpleTwitterWidget extends WP_Widget {
	private /** @type {string} */ $languagePath;

    /** constructor */
    function ReallySimpleTwitterWidget() {
		$this->languagePath = basename(dirname(__FILE__)).'/languages';
        load_plugin_textdomain('rstw', 'false', $this->languagePath);

		$this->options = array(
			array(
				'label' => __( 'Twitter Authentication options', 'rstw' ),
				'type'	=> 'separator', 	'notes' => __('Get them creating your Twitter Application', 'rstw' ).' <a href="https://dev.twitter.com/apps" target="_blank">'.__('here', 'rstw' ).'</a><br /><br />'	),
			array(
				'name'	=> 'consumer_key',	'label'	=> 'Consumer Key',
				'type'	=> 'text',	'default' => ''			),
			array(
				'name'	=> 'consumer_secret',	'label'	=> 'Consumer Secret',
				'type'	=> 'text',	'default' => ''			),
			array(
				'name'	=> 'access_token',	'label'	=> 'Access Token',
				'type'	=> 'text',	'default' => ''			),
			array(
				'name'	=> 'access_token_secret',	'label'	=> 'Access Token Secret',
				'type'	=> 'text',	'default' => ''			),
			array(
				'label' => __( 'Twitter data options', 'rstw' ),
				'type'	=> 'separator'			),
			array(
				'name'	=> 'username',		'label'	=> __( 'Twitter Username', 'rstw' ),
				'type'	=> 'text',	'default' => ''			),
			array(
				'name'	=> 'num',			'label'	=> __( 'Show # of Tweets', 'rstw' ),
				'type'	=> 'text',	'default' => '5'			),
			array(
				'name'	=> 'skip_text',		'label'	=> __( 'Skip tweets containing this text', 'rstw' ),
				'type'	=> 'text',	'default' => ''			),
			array(
				'label' => __( 'Widget title options', 'rstw' ),
				'type'	=> 'separator'			),
			array(
				'name'	=> 'title',	'label'	=> __( 'Title', 'rstw' ),
				'type'	=> 'text',	'default' => __( 'Last Tweets', 'rstw' )			),
			array(
				'name'	=> 'title_icon',	'label'	=> __( 'Show Twitter icon on title', 'rstw' ),
				'type'	=> 'checkbox',	'default' => false			),
			array(
				'name'	=> 'link_title',	'label'	=> __( 'Link above Title with Twitter user', 'rstw' ),
				'type'	=> 'checkbox',	'default' => false			),
			array(
				'label' => __( 'Links and display options', 'rstw' ),
				'type'	=> 'separator'			),
			array(
				'name'	=> 'linked',		'label'	=> __( 'Show this linked text for each Tweet', 'rstw' ),
				'type'	=> 'text',	'default' => ''			),
			array(
				'name'	=> 'link_user_text',	'label'	=> __( 'Text for link below tweets', 'rstw' ),
				'type'	=> 'text',	'default' => ''			),
			array(
				'name'	=> 'link_user',		'label'	=> __( 'Link below tweets with Twitter user', 'rstw' ),
				'type'	=> 'checkbox',	'default' => false			),
			array(
				'name'	=> 'update',	'label'	=> __( 'Show timestamps', 'rstw' ),
				'type'	=> 'checkbox',	'default' => true			),
			array(
				'name'	=> 'hyperlinks',	'label'	=> __( 'Find and show hyperlinks', 'rstw' ),
				'type'	=> 'checkbox',	'default' => true			),
			array(
				'name'	=> 'twitter_users',	'label'	=> __( 'Find Replies in Tweets', 'rstw' ),
				'type'	=> 'checkbox',	'default' => true			),
			array(
				'name'	=> 'link_target_blank',	'label'	=> __( 'Create links on new window / tab', 'rstw' ),
				'type'	=> 'checkbox',	'default' => false			),
			array(
				'name'	=> 'encode_utf8',	'label'	=> __( 'UTF8 Encode', 'rstw' ),
				'type'	=> 'checkbox',	'default' => false			),
			array(
				'label' => __( 'Debug options', 'rstw' ),
				'type'	=> 'separator'			),
			array(
				'name'	=> 'debug',	'label'	=> __( 'Show debug info', 'rstw' ),
				'type'	=> 'checkbox',	'default' => false			),
		);

        $control_ops = array('width' => 400);
        parent::WP_Widget(false, 'Really Simple Twitter', array(), $control_ops);	
    }

    /** @see WP_Widget::widget */
    function widget($args, $instance) {		
		extract( $args );
		$title = apply_filters('widget_title', $instance['title']);
		echo $before_widget;  
		if ( $title != '') {
			echo $before_title;
			$title_icon = ($instance['title_icon']) ? '<img src="'.WP_PLUGIN_URL.'/'.basename(dirname(__FILE__)).'/twitter_small.png" alt="'.$title.'" title="'.$title.'" /> ' : '';
			if ( $instance['link_title'] === true ) {
				$link_target = ($instance['link_target_blank']) ? ' target="_blank" ' : '';
				echo '<a href="http://twitter.com/' . $instance['username'] . '" class="twitter_title_link" '.$link_target.'>'. $title_icon . $title . '</a>';
			} else {
				echo $title_icon . $title;
			}
			echo $after_title;
		}
		echo $this->really_simple_twitter_messages($instance);
		echo $after_widget;
    }

    /** @see WP_Widget::update */
    function update($new_instance, $old_instance) {				
		$instance = $old_instance;
		
		foreach ($this->options as $val) {
			if ($val['type']=='text') {
				$instance[$val['name']] = strip_tags($new_instance[$val['name']]);
			} else if ($val['type']=='checkbox') {
				$instance[$val['name']] = ($new_instance[$val['name']]=='on') ? true : false;
			}
		}
        return $instance;
    }

    /** @see WP_Widget::form */
    function form($instance) {
		if (empty($instance)) {
			foreach ($this->options as $val) {
				if ($val['type']=='separator') {
					continue;
				}
				$instance[$val['name']] = $val['default'];
			}
		}					
	
		// CHECK AUTHORIZATION
		if (!function_exists('curl_init')) {
			echo __('CURL extension not found. You need enable it to use this Widget');
			return;
		}
		
		foreach ($this->options as $val) {
			if ($val['type']=='separator') {
				echo '<hr />';
				if ($val['label']!='') {
					echo '<h3>'.$val['label'].'</h3>';
				}
				if ($val['notes']!='') {
					echo '<span class="description">'.$val['notes'].'</span>';
				}
			} else if ($val['type']=='text') {
				$label = '<label for="'.$this->get_field_id($val['name']).'">'.$val['label'].'</label>';
				echo '<p>'.$label.'<br />';
				echo '<input class="widefat" id="'.$this->get_field_id($val['name']).'" name="'.$this->get_field_name($val['name']).'" type="text" value="'.esc_attr($instance[$val['name']]).'" /></p>';
			} else if ($val['type']=='checkbox') {
				$label = '<label for="'.$this->get_field_id($val['name']).'">'.$val['label'].'</label>';
				$checked = ($instance[$val['name']]) ? 'checked="checked"' : '';
				echo '<input id="'.$this->get_field_id($val['name']).'" name="'.$this->get_field_name($val['name']).'" type="checkbox" '.$checked.' /> '.$label.'<br />';
			}
		}
	}


	protected function debug ($options, $text) {
		if ($options['debug']) {
			echo $text."\n";
		}
	}
	

	// Display Twitter messages
	protected function really_simple_twitter_messages($options) {
	
		// CHECK OPTIONS

		if ($options['username'] == '') {
			return __('Twitter username is not configured','rstw');
		} 
		if (!is_numeric($options['num']) or $options['num']<=0) {
			return __('Number of tweets is not valid','rstw');
		}
		if ($options['consumer_key'] == '' or $options['consumer_secret'] == '' or $options['access_token'] == '' or $options['access_token_secret'] == '') {
			return __('Twitter Authentication data is incomplete','rstw');
		} 

		require_once ('lib/codebird.php');
		Codebird::setConsumerKey($options['consumer_key'], $options['consumer_secret']); 
		$this->cb = Codebird::getInstance();	
		$this->cb->setToken($options['access_token'], $options['access_token_secret']);

		// SET THE NUMBER OF ITEMS TO RETRIEVE - IF "SKIP TEXT" IS ACTIVE, GET MORE ITEMS
		$max_items_to_retrieve = $options['num'];
		if ($options['skip_text']!='') {
			$max_items_to_retrieve *= 3;
		}
	
		// USE TRANSIENT DATA, TO MINIMIZE REQUESTS TO THE TWITTER FEED
	
		$timeout = 30 * 60; //30m
		$error_timeout = 10 * 60; //10m
	
		$transient_name = 'twitter_data_'.$options['username'].$options['skip_text'].'_'.$options['num'];
    
		$twitter_data = get_transient($transient_name);
		$twitter_status = get_transient($transient_name."_status");
    
		// Twitter Status
		if(!$twitter_status || !$twitter_data) {
			try {
				$twitter_status = $this->cb->application_rateLimitStatus();
				set_transient($transient_name."_status", $twitter_status, $timeout);
			} catch (Exception $e) { 
				$this->debug($options, __('Error retrieving twitter rate limit').'<br />');
			}
		}
		$status_option = '/statuses/user_timeline';
		$reset_seconds = ((int)$twitter_status->resources->statuses->$status_option->reset-time());
    
		// Tweets

		if (empty($twitter_data) or count($twitter_data)<1 or isset($twitter_data->errors)) {
			$this->debug($options, '<!-- '.__('Fetching data from Twitter').'... -->');
			$this->debug($options, '<!-- '.__('Requested items').' : '.$max_items_to_retrieve.' -->');
			if($twitter_status->resources->statuses->$status_option->remaining <= 7) {
			    $timeout = $reset_seconds;
			    $error_timeout = $timeout;
			}

			try {
				$twitter_data =  $this->cb->statuses_userTimeline(array('screen_name'=>$options['username'], 'count'=>$max_items_to_retrieve));
			} catch (Exception $e) { return __('Error retrieving tweets','rstw'); }

			if(!isset($twitter_data->errors) and (count($twitter_data) >= 1) ) {
			    set_transient($transient_name, $twitter_data, $timeout);
			    update_option($transient_name."_valid", $twitter_data);
			} else {
			    set_transient($transient_name, $twitter_data, $error_timeout);	// Wait 5 minutes before retry
				if (isset($twitter_data->errors)) {
					$this->debug($options, __('Twitter data error:','rstw').' '.$twitter_data->errors[0]->message.'<br />');
				}
			}
		} else {
			$this->debug($options, '<!-- '.__('Using cached Twitter data').'... -->');

			if(isset($twitter_data->errors)) {
				$this->debug($options, __('Twitter cache error:','rstw').' '.$twitter_data->errors[0]->message.'<br />');
			}
		}
		$this->debug($options, '<!-- '.__('API calls left').' : '.$twitter_status->resources->statuses->$status_option->remaining.' -->');
		$this->debug($options, '<!-- '.__('Seconds until reset').' : '.$reset_seconds.' -->');
    
		if (empty($twitter_data) and false === ($twitter_data = get_option($transient_name."_valid"))) {
		    return __('No public tweets','rstw');
		}

		if (isset($twitter_data->errors)) {
			// STORE ERROR FOR DISPLAY
			$twitter_error = $twitter_data->errors;
		    if(false === ($twitter_data = get_option($transient_name."_valid"))) {
				$debug = ($options['debug']) ? '<br /><i>Debug info:</i> ['.$twitter_error[0]['code'].'] '.$twitter_error[0]['message'].' - username: "'.$options['username'].'"' : '';
			    return __('Unable to get tweets'.$debug,'rstw');
			}
		}
		/*
		if (isset($twitter_data->error)) {
			// STORE ERROR FOR DISPLAY
			$twitter_error = $twitter_data->error;
		    if(false === ($twitter_data = get_option($transient_name."_valid"))) {
				$debug = ($options['debug']) ? '<br /><i>Debug info:</i> ['.$twitter_error.']  - username: "'.$options['username'].'"' : '';
			    return __('Unable to get tweets'.$debug,'rstw');
			}
		}	
		*/

		$link_target = ($options['link_target_blank']) ? ' target="_blank" ' : '';
		
		$out = '<ul class="really_simple_twitter_widget">';

		$i = 0;

		if (empty($twitter_data) or count($twitter_data)<1) {
		    return __('No public tweets','rstw');
		}

		foreach($twitter_data as $message) {

			// CHECK THE NUMBER OF ITEMS SHOWN
			if ($i>=$options['num']) {
				break;
			}
			$msg = $message->text;
		
			if ($options['skip_text']!='' and strpos($msg, $options['skip_text'])!==false) {
				continue;
			}
			if($options['encode_utf8']) $msg = utf8_encode($msg);
				
			$out .= '<li>';

			if ($options['hyperlinks']) { 
				// match protocol://address/path/file.extension?some=variable&another=asf%
				$msg = preg_replace('/\b([a-zA-Z]+:\/\/[\w_.\-]+\.[a-zA-Z]{2,6}[\/\w\-~.?=&%#+$*!]*)\b/i',"<a href=\"$1\" class=\"twitter-link\" ".$link_target.">$1</a>", $msg);
				// match www.something.domain/path/file.extension?some=variable&another=asf%
				$msg = preg_replace('/\b(?<!:\/\/)(www\.[\w_.\-]+\.[a-zA-Z]{2,6}[\/\w\-~.?=&%#+$*!]*)\b/i',"<a href=\"http://$1\" class=\"twitter-link\" ".$link_target.">$1</a>", $msg);    
				// match name@address
				$msg = preg_replace('/\b([a-zA-Z][a-zA-Z0-9\_\.\-]*[a-zA-Z]*\@[a-zA-Z][a-zA-Z0-9\_\.\-]*[a-zA-Z]{2,6})\b/i',"<a href=\"mailto://$1\" class=\"twitter-link\" ".$link_target.">$1</a>", $msg);
				//NEW mach #trendingtopics
				//$msg = preg_replace('/#([\w\pL-.,:>]+)/iu', '<a href="http://twitter.com/#!/search/\1" class="twitter-link">#\1</a>', $msg);
				//NEWER mach #trendingtopics
				$msg = preg_replace('/(^|\s)#(\w*[a-zA-Z_]+\w*)/', '\1<a href="http://twitter.com/#!/search/%23\2" class="twitter-link" '.$link_target.'>#\2</a>', $msg);
			}
			if ($options['twitter_users'])  { 
				$msg = preg_replace('/([\.|\,|\:|\�|\�|\>|\{|\(]?)@{1}(\w*)([\.|\,|\:|\!|\?|\>|\}|\)]?)\s/i', "$1<a href=\"http://twitter.com/$2\" class=\"twitter-user\" ".$link_target.">@$2</a>$3 ", $msg);
			}
          					
			$link = 'http://twitter.com/#!/'.$options['username'].'/status/'.$message->id_str;
			if($options['linked'] == 'all')  { 
				$msg = '<a href="'.$link.'" class="twitter-link" '.$link_target.'>'.$msg.'</a>';  // Puts a link to the status of each tweet 
			} else if ($options['linked'] != '') {
				$msg = $msg . ' <a href="'.$link.'" class="twitter-link" '.$link_target.'>'.$options['linked'].'</a>'; // Puts a link to the status of each tweet
			} 
			$out .= $msg;
		
			if($options['update']) {				
				$time = strtotime($message->created_at);
				$h_time = ( ( abs( time() - $time) ) < 86400 ) ? sprintf( __('%s ago', 'rstw'), human_time_diff( $time )) : date(__('Y/m/d'), $time);
				$out .= '<span class="rstw_comma">,</span> '.sprintf( __('%s', 'rstw'),' <span class="twitter-timestamp"><abbr title="' . date(__('Y/m/d H:i:s', 'rstw'), $time) . '">' . $h_time . '</abbr></span>' );
			}          
                  
			$out .= '</li>';
			$i++;
		}
		$out .= '</ul>';
	
		if ($options['link_user']) {
			$out .= '<div class="rstw_link_user"><a href="http://twitter.com/' . $options['username'] . '" '.$link_target.'>'.$options['link_user_text'].'</a></div>';
		}
		return $out;
	}

} // class ReallySimpleTwitterWidget

// register ReallySimpleTwitterWidget widget
add_action('widgets_init', create_function('', 'return register_widget("ReallySimpleTwitterWidget");'));
