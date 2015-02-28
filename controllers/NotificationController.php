<?php
	
class WPAPN_NotificationController {
	

	public static $type = 'wp_apn_notification';
	
	protected static $mass_upload_fired = false;
	
	public static function init() {
		global $pagenow;
		
		self::register();
		
		// columns
			add_filter( 'manage_edit-'.self::$type.'_columns', array(__CLASS__, 'custom_columns_registration'), 10 );
			add_action( 'manage_'.self::$type.'_posts_custom_column', array(__CLASS__, 'custom_columns_views'), 10, 2 );
		
	}

	
	public static function register() {
		global $pagenow;
		
		$register_post_data = array(
			'labels' => array(
				'name' => __('APNs', WP_APN_PLUGIN),
				'singular_name' => __('APN', WP_APN_PLUGIN),
				'add_new_item' => __('Add APN', WP_APN_PLUGIN),
				'add_new' => __('Add APN', WP_APN_PLUGIN),
				'edit' => __('Edit', WP_APN_PLUGIN),
				'edit_item' => __('Edit APN', WP_APN_PLUGIN),
				'new_item' => __('New APN', WP_APN_PLUGIN),
				'view' => __('View', WP_APN_PLUGIN),
				'view_item' => __('View APN', WP_APN_PLUGIN),
				'search_items' => __('Search APNs', WP_APN_PLUGIN),
				'not_found' => __('No APNs found', WP_APN_PLUGIN),
			),
			'description' => __('For APNs', WP_APN_PLUGIN),
			'public' => false,
			'show_ui' => (bool)WPAPN_AdminController::getSetting('show_post_type'), 
			'_builtin' => false,
			'capability_type' => 'post',
			'capabilities' => array(
				'create_posts' => false, // Removes support for the "Add New" function
			),
			'menu_icon' => plugins_url('assets/img/notification.png', WP_APN_FILE),
			'hierarchical' => false,
			'map_meta_cap' => true,
			'supports' => array('title', /*'thumbnail'*//*, 'custom-fields'*/),
		);
		register_post_type(self::$type,$register_post_data);
	}
	
	
	public static function custom_columns_registration( $defaults ) {
		unset($defaults['title']);
		
		$defaults['message'] = __('Message', WP_APN_PLUGIN);
		$defaults['user'] = __('User', WP_APN_PLUGIN);
		$defaults['payload'] = __('Payload', WP_APN_PLUGIN);
		$defaults['device_token'] = __('Device Token', WP_APN_PLUGIN);
		$defaults['status'] = __('Status', WP_APN_PLUGIN);
	 
		return $defaults;
	}
	
	public static function custom_columns_views($column_name, $post_id) {
		global $post;
	 		
		switch($column_name) {
			case 'message':
				?>
					<strong><?php echo $post->post_title;?></strong>
				<?php
			break;
		
			case 'payload':
				?>
					<pre><?php echo WPAPN_Plugin::json_format($post->post_content);?></pre>
				<?php
			break;
		
			case 'user':
				$user_id = WPAPN_Model::get_post_meta($post_id, WP_APN_PLUGIN.'-user', true);
				$author = get_user_by('id',$user_id);
				
				if ($author) {
					$permalink = admin_url('/user-edit.php?user_id='.$post->post_author);
					?>
						<div style="float: left;"><?php echo get_avatar($post->post_author, 32);?></div>
						<div style="float: left;line-height: 30px; margin-left: 10px;">
							<a href="<?php echo $permalink;?>"><?php echo get_the_author_meta('display_name', $post->post_author);?></a>
						</div>
						<div style="clear:both;"></div>
						<div class="row-actions">
							<a href="<?php echo $permalink;?>"><?php echo __('Edit');?></a>
						</div>
					<?php
				}
			break;
		
			case 'device_token':
				$device_token = WPAPN_Model::get_post_meta($post_id, WP_APN_PLUGIN.'-device-token', true);
				?>
					<?php echo $device_token;?>
				<?php
			break;
		
			case 'status':
				$status = WPAPN_Model::get_post_meta($post_id, WP_APN_PLUGIN.'-sent-status', true);
				$error = WPAPN_Model::get_post_meta($post_id, WP_APN_PLUGIN.'-sent-error', true);
				?>
					<div><?php _e('Status', WP_APN_PLUGIN);?>: <?php echo $status;?></div>
					
					<?php if ($error) { ?>
						<div><?php _e('Send Error', WP_APN_PLUGIN);?>: <?php echo $error;?></div>
					<?php } ?>
				<?php
			break;
		}
	}
	
	
}
