<?php if (!defined('WPINC')) die();

class WPAPN_ApiController {
	
	protected static $apn = null;
	
	// settings
		public static function send($device_token, $message, $additional_data = array(), $badge = null, $sound = 'default') {
			
			if (!self::$apn) {
				$settings = WPAPN_AdminController::getSettings();
				self::$apn = new WPAPN_APN($settings);
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
			self::$apn->connectToPush();
		
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
				WPAPN_Plugin::log('APN debug: Sending successful');
			}
			else {
				update_post_meta($post_id, WP_APN_PLUGIN.'-sent-status', 'error');
				update_post_meta($post_id, WP_APN_PLUGIN.'-sent-error', self::$apn->error);
				WPAPN_Plugin::log('APN error:'.self::$apn->error);
			}
		
		
			self::$apn->disconnectPush();
			
			return $post_id;
		}
		
		public static function sendToUser($user_id) {
			exit;
			$post_id = self::send(
				'e3d21400afe2fed78bbdceb10c2ce10bedb580efa4402bba04f33178dbd483f7',
				'Test notif #1 (TIME:'.date('H:i:s').')'
			);
			update_post_meta($post_id, WP_APN_PLUGIN.'-user', $user_id);
		}
	
	
}
