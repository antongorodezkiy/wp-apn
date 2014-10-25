<?php if (!defined('WPINC')) die();

class WPAPN_Model {
	
	public static $meta_fields = array();
	
	public static function saveMeta($post_id, $metas) {
		foreach($metas as $meta_key => $meta_value) {
			update_post_meta($post_id, $meta_key, $meta_value);
		}
	}
	
	private static $cached_meta = array();
	static public function get_post_meta($post_id, $key = '', $single = false) {
		if (!isset(self::$cached_meta[$post_id])) {
			self::$cached_meta[$post_id] = array();
			$meta_data = get_post_meta($post_id);
			if (!empty($meta_data)) {
				foreach($meta_data as $meta_key => $meta) {
					if (is_serialized($meta[0])) {
						$meta[0] = unserialize($meta[0]);
					}
					self::$cached_meta[$post_id][$meta_key] = $meta[0];
				}
			}
		}
		
		if (!$key) {
			return isset(self::$cached_meta[$post_id]) ? self::$cached_meta[$post_id] : null;
		}
		else {
			return isset(self::$cached_meta[$post_id][$key]) ? self::$cached_meta[$post_id][$key] : null;
		}
	}
	
	private static $cached_usermeta = array();
	static public function get_user_meta($user_id, $key = '', $single = false) {
		if (!isset(self::$cached_usermeta[$user_id])) {
			self::$cached_usermeta[$user_id] = array();
			$meta_data = get_user_meta($user_id);
			if ($meta_data) {
				foreach($meta_data as $meta_key => $meta) {
					if (is_serialized($meta[0])) {
						$meta[0] = unserialize($meta[0]);
					}
					self::$cached_usermeta[$user_id][$meta_key] = $meta[0];
				}
			}
		}
		
		if (!$key && isset(self::$cached_usermeta[$user_id])) {
			return self::$cached_usermeta[$user_id];
		}
		else if (isset(self::$cached_usermeta[$user_id][$key])) {
			return self::$cached_usermeta[$user_id][$key];
		}
		else {
			return null;
		}
	}
	

	public static function getDistinctMetaValues($post_type, $meta_key){
		global $wpdb;

		$query = "
			SELECT DISTINCT(".$wpdb->postmeta.".meta_value) 
			FROM ".$wpdb->posts."
			LEFT JOIN ".$wpdb->postmeta."
				ON ".$wpdb->posts.".ID = ".$wpdb->postmeta.".post_id
			WHERE
				".$wpdb->posts.".post_type IN ('".implode("','",$post_type)."')
				AND ".$wpdb->postmeta.".meta_key = '".$meta_key."'
				AND ".$wpdb->postmeta.".meta_value != ''
			ORDER BY ".$wpdb->postmeta.".meta_value
		";
		
		$meta_values = $wpdb->get_col($query);
		
		return $meta_values;
	}
	
	
}
