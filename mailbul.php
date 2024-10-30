<?php
/*
Plugin Name: Mailbul
Version: 1.0.1
Description: Automatically add your new WordPress user's email to your contact list on Mailbul.
Author: Mailbul
Author URI: http://www.mailbul.com

License: GPL v3

Mailbul Plugin
Copyright (C) 2008-2017, Mailbul

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/
/**
 * @package Main
 */
 
	//Security check
	 
	if( !defined('ABSPATH') ) die('Not again!');
	  
	// Directory Seperator
	 
	if( !defined( 'DS' ) ){
		PHP_OS == "Windows" || PHP_OS == "WINNT" ? define("DS", "\\") : define("DS", "/");
	}
	//Main Class  
		
	class Mailbul{
		
		//Constructor
		function __construct(){
				add_action('admin_menu', array($this,'menu'));
				$mailbul_enable = get_option('mailbul_enable');
				if($mailbul_enable==1){
					add_action( 'user_register', array($this,'ajoft_registration_subscribe'), 10, 1 );
				}
		}
		
		public function menu(){		
				add_menu_page(
				'Mailbul',
				'Mailbul',
				'manage_options',
				'Mailbul',
				array($this,'mailbul_admin'),	
				plugin_dir_url(__FILE__) . 'files' .DS. 'lib' .DS. 'img' .DS. 'mailbul.png',
				'65'
				);
		}
		
		public function mailbul_admin() {
			include plugin_dir_path(__FILE__) . 'files' .DS. 'mailbul_admin.php';
		}
		
		
		public function ajoft_registration_subscribe( $user_id ) {

			$user = new WP_User( $user_id );

			$user_login = stripslashes( $user->user_login );
			$user_email = stripslashes( $user->user_email );

			$message  = sprintf( __('New user registration on %s:'), get_option('blogname') ) . "\r\n\r\n";
			$message .= sprintf( __('Username: %s'), $user_login ) . "\r\n\r\n";
			$message .= sprintf( __('E-mail: %s'), $user_email ) . "\r\n";
			
			$mailbul_mid = get_option('mailbul_mid');
			$mailbul_enable = get_option('mailbul_enable');
			$mailbul_url = get_option('mailbul_url');
			$mailbul_username = get_option('mailbul_username');
			$mailbul_usertoken = get_option('mailbul_usertoken');
			$xml = '<xmlrequest>
			<username>'.$mailbul_username.'</username>
			<usertoken>'.$mailbul_usertoken.'</usertoken>
			<requesttype>subscribers</requesttype>
			<requestmethod>AddSubscriberToList</requestmethod>
			<details>
			<emailaddress>'.$user_email.'</emailaddress>
			<mailinglist>'.$mailbul_mid.'</mailinglist>
			<confirmed>1</confirmed>
			<format>html</format>
			</details>
			</xmlrequest>
			';
			$result = wp_remote_post(
				$mailbul_url,
				array(
					'method'      => 'POST',
					'timeout'     => 45,
					'redirection' => 5,
					'httpversion' => '1.1',
					'headers'     => array(
						'Content-Type' => 'text/xml'
					),
					'body'        => $xml,
					'sslverify'   => 'false'
				)
			);
			if ( !is_wp_error( $result ) ) {
				$return = wp_remote_retrieve_body($result);
				if (strpos($return, 'SUCCESS') !== false){
					@wp_mail(
						get_option('admin_email'),
						sprintf(__('[%s] New Subscription success'), get_option('blogname') ),
						$message
					);
				}else{
					@wp_mail(
						get_option('admin_email'),
						sprintf(__('[%s] New User Subscription failed'), get_option('blogname') ),
						$message
					);
				}	
			}else{
				@wp_mail(
						get_option('admin_email'),
						sprintf(__('[%s] New User not able to process'), get_option('blogname') ),
						$message
					);
			}
		}
	}
	global $Mailbul;
	$Mailbul = new Mailbul();
?>
