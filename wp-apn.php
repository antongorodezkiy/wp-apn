<?php
/*
	Plugin Name: WordPress Apple Push Notifications Plugin
	Plugin URI: http://wp-apn.wp.teamlead.pw/
	Description: Plugin gives you an API to send Apple Push Notifications from your WordPress site.
	Version: 1.0.0
	Author: Teamlead Power
	Author URI: http://teamlead.pw/
	License: BSD
	Text Domain: wp-apn
*/

if (!defined('WPINC')) die();

define('WP_APN_PLUGIN','wp-apn');
define('WP_APN_APPPATH',dirname(__FILE__));
define('WP_APN_FILE',__FILE__);

if (!class_exists('WPAPN_Plugin')) {
	include_once(WP_APN_APPPATH.'/controllers/Plugin.php');
}

if (!class_exists('APN')) {
	include_once(WP_APN_APPPATH.'/libraries/apn.class.php');
}

// initialization
	register_activation_hook(__FILE__, array('WPAPN_Plugin','activation'));
	
// plugin actions
	add_filter('plugin_action_links', array('WPAPN_Plugin','registerPluginActions'), 10, 2);

// early actions
	WPAPN_Plugin::initEarlyActions();
	
function wp_wp_apn_init() {

	if (!class_exists('WPAPN_Model')) {
		include_once(WP_APN_APPPATH.'/models/Model.php');
	}
	
	if (!class_exists('WPAPN_AssetsController')) {
		include_once(WP_APN_APPPATH.'/controllers/AssetsController.php');
	}
	
	if (!class_exists('WPAPN_AdminController')) {
		include_once(WP_APN_APPPATH.'/controllers/AdminController.php');
	}
	
	if (!class_exists('WPAPN_ApiController')) {
		include_once(WP_APN_APPPATH.'/controllers/ApiController.php');
	}
	
	if (!class_exists('WPAPN_MyApiController')) {
		include_once(WP_APN_APPPATH.'/controllers/MyApiController.php');
	}
	
	if (!class_exists('WPAPN_NotificationController')) {
		include_once(WP_APN_APPPATH.'/controllers/NotificationController.php');
	}

	// assets
		if (is_admin()) {
			add_action('admin_enqueue_scripts', array('WPAPN_AssetsController', 'admin_head'));
		}

	// post types
		add_action('init', array('WPAPN_Plugin','registerPostTypes'));

	//ADMIN
	if (is_admin() && (current_user_can('edit_posts') || current_user_can('edit_pages'))) {
		
		// settings init
			add_action('admin_init', array('WPAPN_AdminController','settingsInit'));
			
		// admin page
			add_action( 'admin_menu', array('WPAPN_AdminController','registerMenuPage'));
			
	}
}
add_action('after_setup_theme','wp_wp_apn_init');
