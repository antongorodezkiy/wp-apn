<?php if (!defined('WPINC')) die();

class WPAPN_AdminController {
	
	// show message
		public static function showMessage($message, $errormsg = false) {
			
			if (!session_id()) {
				session_start();
			}
			
			if (!isset($_SESSION[WP_APN_PLUGIN.'admin_notice'])) {
				$_SESSION[WP_APN_PLUGIN.'admin_notice'] = array();
			}
			
			$_SESSION[WP_APN_PLUGIN.'admin_notice'][] = array(
				'text' => $message,
				'error' => $errormsg
			);
		}
		
		public static function showDirectMessage($message, $errormsg = false) {
			
			if ($errormsg) {
				$css_class = 'error';
			}
			else {
				$css_class = 'updated';
			}
			
			echo '<div class="'.$css_class.'"><p>'.$message.'</p></div>';
		}

		public static function showAdminNotifications() {
			if (!session_id()) {
				session_start();
			}
			
			if (isset($_SESSION[WP_APN_PLUGIN.'admin_notice'])) {
				foreach($_SESSION[WP_APN_PLUGIN.'admin_notice'] as $key => $notice) {
					
					if ($notice['error']) {
						$css_class = 'error';
					}
					else {
						$css_class = 'updated';
					}
					
					echo '<div class="'.$css_class.'"><p>'.$notice['text'].'</p></div>';
				}
				$_SESSION[WP_APN_PLUGIN.'admin_notice'] = array();
			}
		}
	
	// settings
		public static function registerMenuPage() {
			add_options_page(
				'WordPress APN',
				'WordPress APN',
				'manage_options',
				WP_APN_PLUGIN,
				array('WPAPN_AdminController','showSettings')
			);
		}
	
		public static function showSettings() {
			$requirements = WPAPN_Plugin::requirements();
			include_once(WP_APN_APPPATH.'/views/settings.php');
		}
		
		
		public static function settingsInit() {
			register_setting(WP_APN_PLUGIN, WP_APN_PLUGIN);
		}

		public static function getSettings() {
			$config = array();
			/*
			|--------------------------------------------------------------------------
			| APN Permission file
			|--------------------------------------------------------------------------
			|
			| Contains the certificate and private key, will end with .pem
			| Full server path to this file is required.
			|
			*/
				$config['PermissionFile'] = '';
				$config['PermissionFileSandbox'] = '';
				
			/*
			|--------------------------------------------------------------------------
			| APN Private Key's Passphrase
			|--------------------------------------------------------------------------
			*/
				$config['PassPhrase'] = '';
				$config['PassPhraseSandbox'] = '';
				
			/*
			|--------------------------------------------------------------------------
			| APN Services
			|--------------------------------------------------------------------------
			*/
				$config['Sandbox'] = true;
				$config['PushGatewaySandbox'] = 'ssl://gateway.sandbox.push.apple.com:2195';
				$config['PushGateway'] = 'ssl://gateway.push.apple.com:2195';
				$config['FeedbackGatewaySandbox'] = 'ssl://feedback.sandbox.push.apple.com:2196';
				$config['FeedbackGateway'] = 'ssl://feedback.push.apple.com:2196';
				
			/*
			|--------------------------------------------------------------------------
			| APN Connection Timeout
			|--------------------------------------------------------------------------
			*/
				$config['Timeout'] = 60;
				
			/*
			|--------------------------------------------------------------------------
			| APN Notification Expiry (seconds)
			|--------------------------------------------------------------------------
			| default: 86400 - one day
			*/
				$config['Expiry'] = 86400;
				
			/*
			|--------------------------------------------------------------------------
			| Enable logging debug information to logfie. If 'false' only errors will be logged.
			|--------------------------------------------------------------------------
			| default: true
			*/
				$config['debug'] = true;
				
			return wp_parse_args(get_option(WP_APN_PLUGIN),$config);
		}
		
		public static $cached_settings = null;
		public static function getSetting($name) {
			if (self::$cached_settings == null) {
				self::$cached_settings = self::getSettings();
			}
			
			if (isset(self::$cached_settings[$name])) {
				return self::$cached_settings[$name];
			}
			else {
				return null;
			}
		}
	
}
