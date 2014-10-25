<?php if (!defined('WPINC')) die();
		
class WPAPN_AssetsController {	
	
	public static function admin_head() {
		global $typenow;
		
		if (
			(isset($_GET['page']) && $_GET['page'] == WP_APN_PLUGIN)
			|| $typenow == WPAPN_NotificationController::$type) {
			// styles
				wp_enqueue_media();

				
				$styles = array(
					'purecss.grids.responsive' => '/assets/bower_components/pure/grids-responsive-min.css',
					'purecss.grids.core' => '/assets/bower_components/pure/grids-core-min.css',
					'purecss.forms' => '/assets/bower_components/pure/forms-min.css',
					'font-awesome' => '/assets/bower_components/fontawesome/css/font-awesome.min.css',
					'admin.'.WP_APN_PLUGIN => '/assets/css/admin.css'
				);
				
				foreach($styles as $id => $file) {
					wp_enqueue_style(
						$id,
						plugins_url($file, WP_APN_FILE)
					);
				}
	
			// scripts
				$jquery_plugins = array(
					
				);
				
				foreach($jquery_plugins as $id => $file) {
					wp_enqueue_script(
						$id,
						plugins_url($file, WP_APN_FILE),
						array('jquery')
					);
				}
	
			wp_enqueue_script(
				'admin.'.WP_APN_PLUGIN,
				plugins_url('/assets/js/admin.js', WP_APN_FILE),
				array('jquery')
			);
			
			// javascript settings
				wp_localize_script('admin.'.WP_APN_PLUGIN, 'WPAPNAdmin', array(
					'url' => array(
						'site_url' => site_url(),
						'ajaxurl' => admin_url('admin-ajax.php'),
						'plugin_url' => plugins_url('/',WP_APN_FILE)
					),
					'lang' => array(
						'Upload Certificate' => __('Upload Certificate', WP_APN_PLUGIN),
						'Choose Certificate' => __('Choose Certificate', WP_APN_PLUGIN),
					)
				));
	
		}
	}
	
	
}
