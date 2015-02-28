<?php if (!defined('WPINC')) die();

abstract class WPAPN_ApiController {
	
	protected static $apn = null;
	
	// settings
		public static function send($device_token, $message, $additional_data = array(), $badge = null, $sound = 'default') {
			
			if (!self::$apn) {
				$settings = WPAPN_AdminController::getSettings();
				
				try {
					self::$apn = new WPAPN_APN($settings);
				} catch(Exception $e) {
					
					WPAPN_AdminController::showMessage($e->getMessage(),true);
					
					self::$apn = false;
					return false;
				}
				
			}
			
			$post_id = wp_insert_post(array(
				'post_type' => WPAPN_NotificationController::$type,
				'post_status' => 'publish',
				'post_title' => $message
			));
			
			if (is_wp_error($post_id)) {
				return false;
			}
			
			self::$apn->payloadMethod = 'enhance'; // you can turn on this method for debuggin purpose
			
			$connected = true;
			try {
				$connected = self::$apn->connectToPush();
			} catch(Exception $e) {
				$connected = false;
			}
			
			if (!$connected) {
				WPAPN_Plugin::log('APN error', 'Could not connect to the push service');
				return false;
			}
		
			// adding custom variables to the notification
				if (!empty($additional_data)) {
					self::$apn->setData($additional_data);
				}
			
			// send
				$send_result = self::$apn->sendMessage(
					$device_token,
					$message,
					$badge,
					$sound
				);
			
			wp_update_post(array(
				'ID' => $post_id,
				'post_content' => self::$apn->recent_payload
			));
			
			update_post_meta($post_id, WP_APN_PLUGIN.'-device-token', $device_token);
			update_post_meta($post_id, WP_APN_PLUGIN.'-badge', $badge);
			update_post_meta($post_id, WP_APN_PLUGIN.'-sound', $sound);
		
			if($send_result) {
				update_post_meta($post_id, WP_APN_PLUGIN.'-sent-status', 'sent');
				update_post_meta($post_id, WP_APN_PLUGIN.'-sent-error', '');
				WPAPN_Plugin::log('APN debug','Sending successful');
			}
			else {
				update_post_meta($post_id, WP_APN_PLUGIN.'-sent-status', 'error');
				update_post_meta($post_id, WP_APN_PLUGIN.'-sent-error', self::$apn->error);
				WPAPN_Plugin::log('APN error', self::$apn->error);
			}
		
		
			self::$apn->disconnectPush();
			
			return $post_id;
		}
		
		public static function simpleSendToUser($user_id, $user_meta_field = 'device-token', $message) {
			
			$device_tokens = (array)get_user_meta($user_id, $user_meta_field, true);
			
			$post_ids = array();
			foreach($device_tokens as $token) {
				$post_ids[] = self::simpleSendToDevice($token, $message);
				update_post_meta($post_id, WP_APN_PLUGIN.'-user', $user_id);
			}
			
			return $post_ids;
		}
		
		public static function simpleSendToDevice($device_token, $message) {
			
			$post_id = self::send(
				$device_token,
				$message
			);
			
			return $post_id;
		}
	
		public static function advancedSendToUser($user_id, $user_meta_field = 'device-token', $message, $additional_data) {
			
			$device_tokens = (array)get_user_meta($user_id, $user_meta_field, true);
			
			$post_ids = array();
			foreach($device_tokens as $token) {
				$post_ids[] = self::advncedSendToDevice($token, $message, $additional_data);
				update_post_meta($post_id, WP_APN_PLUGIN.'-user', $user_id);
			}
			
			return $post_ids;
		}
		
		public static function advncedSendToDevice($device_token, $message, $additional_data) {
			
			$post_id = self::send(
				$device_token,
				$message,
				$additional_data
			);
			
			return $post_id;
		}
	
}
