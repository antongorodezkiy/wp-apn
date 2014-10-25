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
		
		// metaboxes		
			if ($pagenow == 'post-new.php') {
				add_filter('cmb_meta_boxes', array(__CLASS__, 'cmb_meta_boxes'));
			}
			else if ($pagenow == 'post.php') {
				add_filter('add_meta_boxes', array(__CLASS__, 'add_meta_boxes'));
			}
		
		// saving
			add_action('save_post', array(__CLASS__, 'save_post'), 1000);
			
		// remove row actions
			add_filter( 'post_row_actions', 'rys_remove_row_actions', 10, 1 );
			
			//WPAPN_ApiController::sendToUser();
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
			'menu_icon' => plugins_url('assets/img/notification.png', WP_APN_FILE),
			'hierarchical' => false,
			'map_meta_cap' => true,
			'supports' => array('title', /*'thumbnail'*//*, 'custom-fields'*/),
		);
		register_post_type(self::$type,$register_post_data);
	}
	
	public static function post_row_actions($actions) {
		global $typenow;
		if($typenow == self::$type) {
			unset($actions['view']);
			unset($actions['inline hide-if-no-js']);
			unset($actions['edit']);
			unset($actions['trash']);
		}
	 
		return $actions;
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
	
	
	public static function cmb_meta_boxes($meta_boxes) {
		
		if (!isset($_GET[self::$type.'_mass_upload'])) {
			$meta_boxes['eben_song_file_metabox'] = array(
				'id' => 'eben_song_file_metabox',
				'title' => __('Song', WP_APN_PLUGIN),
				'pages' => array(self::$type), // post type
				'context' => 'normal',
				'priority' => 'high',
				'show_names' => true, // Show field names on the left
				'fields' => array(
					array(
						'name' => 'Song File',
						'desc' => '',
						'id' => 'song_file',
						'type' => 'file'
					),
				),
			);
		}
	
		return $meta_boxes;
	}
	
	
	public static function add_meta_boxes() {
		
		if (isset($_GET[self::$type.'_mass_upload'])) {
			
			// add meta box for song info
				add_meta_box(
					'eben_song_mass_upload',
					__('Mass Upload', WP_APN_PLUGIN),
					array(__CLASS__, 'metabox_mass_upload'),
					self::$type,
					'normal',
					'default',
					array()
				);
		}
		else {
			// add meta box for song info
				add_meta_box(
					'eben_song_song_info',
					__('Song Info', WP_APN_PLUGIN),
					array(__CLASS__, 'metabox_song_info'),
					self::$type,
					'advanced',
					'default',
					array()
				);
		}
	}
	
	public static function metabox_song_info($post) {
		
		$Song = ebenMusicSongModel::getById($post->ID);
		
		if ($Song) {
			include_once(WP_APN_APPPATH.'/views/metaboxes/song-info.php');
		}
	}
	
	
	/*
	 * On song insert
	 */ 
		public static function insert_post_data($data) {
			global $typenow;
			
			if (current_user_can('edit_posts') && $typenow == self::$type && $_POST && $data['post_type'] == self::$type) {
				$form_data = $_REQUEST;
		
					// mass upload
						if (!empty($_FILES) && !self::$mass_upload_fired) {
							self::$mass_upload_fired = true;
							self::mass_upload();
							
							wp_redirect(admin_url('edit.php?post_status=just_added&post_type='.self::$type));
							die();
						}
					
				
					$song_file_id = $form_data['song_file_id'];
					$song_file_path = get_attached_file($song_file_id);
					
					if (ebenMusicSongModel::songFileIsChanged($form_data['post_ID'],$song_file_path)) {
						
						$data['post_title'] = self::getTitleFromID3($song_file_path);
					
						$data['post_name'] = sanitize_title($data['post_title']);
					}
			}
			
			
			return $data;
		}
		
	

	/*
	 * On song save
	 */
		public static function save_post($song_id) {
			global $typenow;
			
			if (self::$type == $typenow) {
				
				if ( (defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE) || !$_POST ) {
					return;
				}
				
				if (current_user_can('edit_posts')) {
					$form_data = $_REQUEST;
					
					$song_file_id = $form_data['song_file_id'];
					
					self::save_song($song_id, $song_file_id);
				}
			}
		}

	
}
