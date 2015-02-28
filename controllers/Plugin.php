<?php

class WPAPN_Plugin {

	
	public static function activation() {

	}
	
	public static function initEarlyActions() {
		add_filter('upload_mimes', array(__CLASS__, 'upload_mimes'));
	}
 
	// Function to add mime types
		public static function upload_mimes($mime_types=array()) {
			$mime_types['pem'] = 'application/x-pem-file';
			return $mime_types;
		}

	// plugin actions
		public static function registerPluginActions($links, $file) {
			if (stristr($file, WP_APN_PLUGIN)) {
				$settings_link = '<a href="options-general.php?page='.WP_APN_PLUGIN.'">' . __('Settings', WP_APN_PLUGIN) . '</a>';
				$links = array_merge(array($settings_link), $links);
			}
			return $links;
		}
		
		
	// plugin actions
		public static function registerPostTypes() {			
			WPAPN_NotificationController::init();
		}
		
		
		
	public static function getActivePlugins() {
		$apl = get_option('active_plugins');
		$plugins = get_plugins();
		$activated_plugins = array();
		foreach($apl as $p) {           
			if(isset($plugins[$p])) {
				array_push($activated_plugins, $plugins[$p]);
			}           
		}
		
		return $activated_plugins;
	}
	
	public static function serverInfo() {
		global $wp_version, $wpdb;
		
		$mysql = $wpdb->get_row("SHOW VARIABLES LIKE 'version'");
		
		$info = array(
			'os' => php_uname(),
			'php' => phpversion(),
			'mysql' => $mysql->Value,
			'wordpress' => $wp_version
		);
		
		return $info;
	}
	
	// plugin requirements
		public static function requirements($boolean = false) {
			$upload_dir_message = __('Logs folder',WP_APN_PLUGIN).': <code>'.self::getLogsPath().'</code>';
			
			$requirements = array(
				array(
					'name' => $upload_dir_message,
					'status' => self::createLogsDirectory() && is_writable(self::getLogsPath()),
					'success' => __('is writable',WP_APN_PLUGIN),
					'fail' => __('is not writable',WP_APN_PLUGIN)
				)
			);
			
			if ($boolean) {
				$status = true;
				foreach($requirements as $requirement) {
					$status = $status && $requirement['status'];
				}
				return $status;
			}
			else {
				return $requirements;
			}
		}
	
	public static function getDocsUrl() {
		if (file_exists(WP_APN_APPPATH.'/documentation/index_'.WPLANG.'.html')) {
			$documentation_url = 'documentation/index'.WPLANG.'.html';
		}
		else {
			$documentation_url = 'documentation/index.html';
		}
		$documentation_url = plugins_url($documentation_url, WP_APN_FILE);
		return $documentation_url;
	}
	
	public static function getSettingsUrl() {
		return admin_url('options-general.php?page='.WP_APN_PLUGIN);
	}
	
	public static function getLogsPath() {
		return WP_CONTENT_DIR.'/'.WP_APN_PLUGIN.'-logs/';
	}
	
	public static function createLogsDirectory() {
		$log_path = self::getLogsPath();
		if ( ! file_exists($log_path)) {
			mkdir($log_path);
		}
		
		return file_exists($log_path);
	}
	
	public static function log($label, $msg) {
		
		if (is_array($msg) || is_object($msg)) {
			$msg = print_r($msg,true);
		}
			
		$log_path = self::getLogsPath();
		
		if ( ! file_exists($log_path)) {
			mkdir($log_path);
		}
		
		$filename = date('Y-m-d').'.php';
		$filepath = $log_path.$filename;
			
		/*$messages = explode("\n",$msg);
		
		foreach($messages as $k => $m) {
			$messages[$k] = substr($m,0,2000);
		}
		$msg = implode("\n",$messages);*/
		
		$message = '';

		if (!file_exists($filepath)) {
			$message .= "<"."?php if ( ! defined('WPINC')) exit('No direct script access allowed'); ?".">\n\n";
		}

		if (!$fp = fopen($filepath, 'ab')) {
			return FALSE;
		}

		$message .= "======================\n".date('d-m-Y H-i-s')."\n".' ---------------------- '."\n".$label.' >>> '.$msg."\n\n";

		flock($fp, LOCK_EX);
		fwrite($fp, $message);
		flock($fp, LOCK_UN);
		fclose($fp);

		@chmod($filepath, 0666);
		return TRUE;
	}
		
	/*
	 * (c) http://php.net/manual/en/function.json-encode.php#80339
	 */
	public static function json_format($json) {
		$tab = "  ";
		$new_json = "";
		$indent_level = 0;
		$in_string = false;
	
		$json_obj = json_decode($json);
	
		if($json_obj === false)
			return false;
	
		$json = json_encode($json_obj);
		$len = strlen($json);
	
		for($c = 0; $c < $len; $c++)
		{
			$char = $json[$c];
			switch($char)
			{
				case '{':
				case '[':
					if(!$in_string)
					{
						$new_json .= $char . "\n" . str_repeat($tab, $indent_level+1);
						$indent_level++;
					}
					else
					{
						$new_json .= $char;
					}
					break;
				case '}':
				case ']':
					if(!$in_string)
					{
						$indent_level--;
						$new_json .= "\n" . str_repeat($tab, $indent_level) . $char;
					}
					else
					{
						$new_json .= $char;
					}
					break;
				case ',':
					if(!$in_string)
					{
						$new_json .= ",\n" . str_repeat($tab, $indent_level);
					}
					else
					{
						$new_json .= $char;
					}
					break;
				case ':':
					if(!$in_string)
					{
						$new_json .= ": ";
					}
					else
					{
						$new_json .= $char;
					}
					break;
				case '"':
					if($c > 0 && $json[$c-1] != '\\')
					{
						$in_string = !$in_string;
					}
				default:
					$new_json .= $char;
					break;                   
			}
		}
	
		return $new_json;
	} 
	
}
