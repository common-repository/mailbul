<?php
	if( !defined('ABSPATH') ) die('Not again!'); // Exit if accessed directly
	
	function import_all_users() {
		$all_users = get_users();

		foreach ( $all_users as $user ) {
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
		}
	}

	

    if(isset($_POST['mailbul_hidden']) == 'Y') {
		$verify_nonce = $_REQUEST['__wpnonce_mailbul'];
		if( wp_verify_nonce( $verify_nonce, 'mail_bul' ) && current_user_can( 'manage_options' ) ) {
			
			$mailbul_mid = sanitize_text_field($_POST['mailbul_mid']);
			update_option('mailbul_mid', $mailbul_mid);
			 
			$mailbul_enable = sanitize_text_field($_POST['mailbul_enable']);
			update_option('mailbul_enable', $mailbul_enable);

			$mailbul_importall = sanitize_text_field($_POST['mailbul_importall']);
			if ( $mailbul_importall == '1' )
			{
				import_all_users();
			}
			update_option('mailbul_importall', $mailbul_importall);
	 
			$mailbul_url = esc_url_raw($_POST['mailbul_url']);
			update_option('mailbul_url', $mailbul_url);

			$mailbul_username = sanitize_text_field($_POST['mailbul_username']);
			update_option('mailbul_username', $mailbul_username);
	 
			$mailbul_usertoken = sanitize_key($_POST['mailbul_usertoken']);
			update_option('mailbul_usertoken', $mailbul_usertoken);
			?>
			<div class="updated"><p><strong><?php _e('Options saved.' ); ?></strong></p></div>
			<?php 
		}
		else{
			die( __( 'Security check', 'textdomain' ) );
		}
    } else {
        //Normal page display
        $mailbul_mid = get_option('mailbul_mid');
        $mailbul_enable = get_option('mailbul_enable');
        $mailbul_importall = get_option('mailbul_importall');
        $mailbul_url = get_option('mailbul_url');
        $mailbul_username = get_option('mailbul_username');
        $mailbul_usertoken = get_option('mailbul_usertoken');
        $nonce_page = wp_create_nonce( 'mail_bul' );
?>
<p></p>
<h4>This plugin automatically adds your WordPress users' emails to your contact list on Mailbul.
<br>
<font color="#FF000">No Mailbul account yet?</font> <br>
<a href="https://mailbul.com/registration/" target="_blank">Sign Up for Free >></a></h5>

    <?php    echo "<h2>" . __( 'Mailbul Options', 'oscimp_trdom' ) . "</h2>"; ?>
     
    <form name="oscimp_form" method="post" action="<?php echo str_replace( '%7E', '~', $_SERVER['REQUEST_URI']); ?>">
        <input type="hidden" name="mailbul_hidden" value="Y">
        <?php    echo "<h4>" . __( 'Mailing List Settings', 'oscimp_trdom' ) . "</h4>"; ?>
        <p><?php _e("Mailing List ID: " ); ?><input type="text" name="mailbul_mid" value="<?php echo $mailbul_mid; ?>" size="20"><?php _e(" ex: 12. <a href=\"https://mailbul.com/how-to-use-mailbul-plugin/\" target=\"_blank\">What is my list ID? >></a>" ); ?></p>
        <p><?php _e("Enable Plugin: " ); ?> <select name="mailbul_enable"> <option value="1" <?php echo ($mailbul_enable == 1 ? 'selected' : '');?>>Yes</option> <option value="0" <?php echo ($mailbul_enable == 0 ? 'selected' : '');?>>No</option></select><?php _e(" ex: Yes/No" ); ?></p>
        <p><?php _e("Import All Wordpress subscribers I have till now: " ); ?> <select name="mailbul_importall"> <option value="1" <?php echo ($mailbul_importall == 1 ? 'selected' : '');?>>Yes</option> <option value="0" <?php echo ($mailbul_importall == 0 ? 'selected' : '');?>>No</option></select><?php _e("If Yes is selected, all emails of current users will be imported to your Mailbul contact list." ); ?></p>
         <hr />
        <?php    echo "<h4>" . __( 'Mailbul API Settings', 'ajint_apiset' ) . "</h4>"; ?>
        <p><?php _e("API URL: " ); ?><input type="text" name="mailbul_url" value="<?php echo $mailbul_url; ?>" size="35"><?php _e(" Default API URL. Don’t change unless you know what you are doing! <a href=\"https://mailbul.com/bg/how-to-use-mailbul-plugin/\" target=\"_blank\">Learn more >></a>" ); ?></p>
        <p><?php _e("API Username: " ); ?><input type="text" name="mailbul_username" value="<?php echo $mailbul_username; ?>" size="35"><?php _e(" ex: mailbul <a href=\"https://mailbul.com/how-to-use-mailbul-plugin/\" target=\"_blank\">What is my API Username? >></a>" ); ?></p>
        <p><?php _e("API Usertoken: " ); ?><input type="text" name="mailbul_usertoken" value="<?php echo $mailbul_usertoken; ?>" size="35"><?php _e(" ex: c7c3a4a200979c05fc620745930f9ce44d23ae53 <a href=\"https://mailbul.com/how-to-use-mailbul-plugin/\" target=\"_blank\">Get your API Usertoken now >></a>" ); ?></p>
         
     
        <p class="submit">
		<input type="hidden" name="__wpnonce_mailbul" value="<?php echo esc_attr( $nonce_page ); ?>">
        <input type="submit" name="Submit" value="<?php _e('Update Options', 'oscimp_trdom' ) ?>" />
        </p>
    </form>
<?php
}
?>
