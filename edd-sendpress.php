<?php
/*
Plugin Name: Easy Digital Downloads - SendPress
Plugin URL: http://easydigitaldownloads.com/extension/mail-chimp
Description: Include a SendPress signup option with your Easy Digital Downloads checkout
Version: 1.0
Author: SendPress
Author URI: http://sendpress.com
Contributors: Jared Harbour
*/

define( 'EDD_SENDPRESS_STORE_API_URL', 'http://sendpress.com' ); 
define( 'EDD_SENDPRESS_PRODUCT_NAME', 'SendPress - EDD' ); 


global $edd_options;

include( dirname( __FILE__ ) . '/classes/edd-sendpress-signup.php' );

register_activation_hook( __FILE__, array( 'SendPress_EDD_Signup', 'plugin_activation' ) );
register_deactivation_hook( __FILE__, array( 'SendPress_EDD_Signup', 'plugin_deactivation' ) );

// Initialize!
SendPress_EDD_Signup::get_instance();