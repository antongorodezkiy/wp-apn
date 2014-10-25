(function($) {
	
	$(document).ready(function() {
		
		// upload certificate
			WPAPNAdmin.custom_uploader = null;
			WPAPNAdmin.current_target = null;
			
			WPAPNAdmin.uploadCertificate = function(activator, target) {
				
				activator.click(function(e) {
					WPAPNAdmin.current_target = target;
					e.preventDefault();
			 
					//If the uploader object has already been created, reopen the dialog
					if (WPAPNAdmin.custom_uploader) {
						WPAPNAdmin.custom_uploader.open();
						return;
					}
			 
					//Extend the wp.media object
					WPAPNAdmin.custom_uploader = wp.media.frames.file_frame = wp.media({
						title: WPAPNAdmin.lang['Choose Certificate'],
						button: {
							text: WPAPNAdmin.lang['Choose Certificate']
						},
						multiple: false
					});
			 
					//When a file is selected, grab the URL and set it as the text field's value
					WPAPNAdmin.custom_uploader.on('select', function() {
						attachment = WPAPNAdmin.custom_uploader.state().get('selection').first().toJSON();
						attachment.url = attachment.url.replace(WPAPNAdmin.url.site_url,"");
						WPAPNAdmin.current_target.val(attachment.url);
					});
			 
					//Open the uploader dialog
					WPAPNAdmin.custom_uploader.open();
			
				});
			};
			
			WPAPNAdmin.uploadCertificate($('.js-wp-apn-production-certificate-upload'), $('.js-wp-apn-production-certificate-path'));
			WPAPNAdmin.uploadCertificate($('.js-wp-apn-sandbox-certificate-upload'), $('.js-wp-apn-sandbox-certificate-path'));
			
	});
	
})(jQuery);
