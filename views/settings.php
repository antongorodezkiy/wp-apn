<?php if (!defined('WPINC')) die(); ?>

<div class="wp-apn-admin-settings js-wp-apn-admin-settings">
	
		<div class="pure-g">
			<div class="pure-u-lg-2-3 pure-u-md-1-1 pure-u-sm-1-1 pure-u-xs-1-1">
				
				<div class="wp-apn-bl row">
					<div class="row hdr">
						<h3>
							<span class="fa fa-info"></span>
							<?php _e('About', WP_APN_PLUGIN)?>
						</h3>
					</div>
					<div class="row container">
						<p class="center">
							<a class="logo" href="http://wp-apn.wp.teamlead.pw" target="_blank">
								WordPress APN Plugin
							</a>
						</p>
						
						<blockquote>
							WordPress APN Plugin &copy; <a target="_blank" href="http://teamlead.pw">Teamlead Power&nbsp;<span class="fa fa-external-link-square"></span></a>
						</blockquote>
						
						<blockquote>
							<p>
								Icons &copy; <a href="http://fortawesome.github.io/Font-Awesome/" target="_blank">Font Awesome&nbsp;<span class="fa fa-external-link-square"></span></a>
							</p>
							<p>
								Logo Icon &copy; <a href="http://thenounproject.com/term/message-notification/23246/" target="_blank">aguycalledgary&nbsp;<span class="fa fa-external-link-square"></span></a>
							</p>
							<p>
								CSS Framework &copy; <a href="http://purecss.io/" target="_blank">Pure.css&nbsp;<span class="fa fa-external-link-square"></span></a>
							</p>
							<p>
								Color theme &copy; <a href="http://www.colourlovers.com/palette/3528801/Wheres_my_juice" target="_blank">superchicami&nbsp;<span class="fa fa-external-link-square"></span></a>
							</p>
						</blockquote>
					</div>
				</div>

				<div class="wp-apn-bl row">
					<div class="row hdr">
						<h3>
							<span class="fa fa-exclamation-triangle"></span>
							<?php _e('Plugin Requirements', WP_APN_PLUGIN)?>
						</h3>
					</div>
					<div class="row in container">
						<ul>
							<?php
								foreach($requirements as $requirement) {
									
									if ($requirement['status']) {
										?>
											<li>
												<span class="fa-stack wp-apn-requirement-success">
													<i class="fa fa-circle fa-stack-2x"></i>
													<i class="fa fa-check fa-stack-1x fa-inverse"></i>
												</span>
												<?php echo $requirement['name'];?> <?php echo $requirement['success'];?>
											</li>
										<?php
									}
									else {
										?>
											<li>
												<span class="fa-stack wp-apn-requirement-fail">
													<i class="fa fa-circle fa-stack-2x"></i>
													<i class="fa fa-exclamation fa-stack-1x fa-inverse"></i>
												</span>
												<?php echo $requirement['name'];?> <?php echo $requirement['fail'];?>
											</li>
										<?php
									}
								}
							?>
						</ul>
					</div>
				</div>
			
				<form class="wp-apn-content wp-apn-bl settings-pure-form pure-form-aligned pure-form" method="post" action="options.php">
					
					<?php settings_fields(WP_APN_PLUGIN);?>
					
					<div class="row hdr">
						<h3>
							<span class="fa fa-sliders"></span>
							<?php _e('Settings', WP_APN_PLUGIN)?>
						</h3>
					</div>
			
					<div class="row in">
						<div class="row">
							<legend><span class="fa fa-cogs"></span><?php _e('Main settings', WP_APN_PLUGIN)?></legend>
							
							<p class="pure-control-group">
								<label for="<?php echo WP_APN_PLUGIN?>[show_post_type]">
									<span class="fa-stack">
										<i class="fa fa-circle fa-stack-2x"></i>
										<i class="fa  fa-th-list fa-stack-1x fa-inverse"></i>
									</span>
									<?php _e('Show Notifications in Menu', WP_APN_PLUGIN)?>
									<a href="#!" class="js-tip tip" title="<?php _e('Show Notifications menu item in the left admin sidebar', WP_APN_PLUGIN)?>"><span class="fa fa-question-circle"></span></a>								</label>
								<input
									type="checkbox"
									name="<?php echo WP_APN_PLUGIN?>[show_post_type]"
									value="1"
									<?php echo ( WPAPN_AdminController::getSetting('show_post_type') ? 'checked="checked"' : '' )?>
									/>
							</p>
							
							<p class="pure-control-group">
								<label for="<?php echo WP_APN_PLUGIN?>[debug]">
									<span class="fa-stack">
										<i class="fa fa-circle fa-stack-2x"></i>
										<i class="fa fa-bell-slash fa-stack-1x fa-inverse"></i>
									</span>
									<?php _e('Enable debug logging', WP_APN_PLUGIN)?>
									<a href="#!" class="js-tip tip" title="<?php _e('Enable logging debug information to logfie. If turned off, then only errors will be logged.', WP_APN_PLUGIN)?>"><span class="fa fa-question-circle"></span></a>								</label>
								<input
									type="checkbox"
									name="<?php echo WP_APN_PLUGIN?>[debug]"
									value="1"
									<?php echo ( WPAPN_AdminController::getSetting('debug') ? 'checked="checked"' : '' )?>
									/>
							</p>
							
							<p class="pure-control-group">
								<label>
									<span class="fa-stack">
										<i class="fa fa-circle fa-stack-2x"></i>
										<i class="fa fa-paper-plane fa-stack-1x fa-inverse"></i>
									</span>
									<?php _e('APN Mode', WP_APN_PLUGIN)?>
									<a target="_blank" href="https://developer.apple.com/library/ios/documentation/NetworkingInternet/Conceptual/RemoteNotificationsPG/Chapters/ProvisioningDevelopment.html#//apple_ref/doc/uid/TP40008194-CH104-SW2" class="js-tip tip" title="<?php _e('Apple official documentation', WP_APN_PLUGIN)?>"><span class="fa fa-question-circle"></span></a>
								</label>
								<input
									type="radio"
									name="<?php echo WP_APN_PLUGIN?>[Sandbox]"
									value="1"
									<?php echo ( WPAPN_AdminController::getSetting('Sandbox') ? 'checked="checked"' : '' )?>
									/>
								<span><?php _e('Sandbox', WP_APN_PLUGIN)?></span>
								<input
									type="radio"
									name="<?php echo WP_APN_PLUGIN?>[Sandbox]"
									value="0"
									<?php echo ( !WPAPN_AdminController::getSetting('Sandbox') ? 'checked="checked"' : '' )?>
									/>
								<span><?php _e('Production', WP_APN_PLUGIN)?></span>
							</p>
						</div>
						
						
						
						<div class="row">
							<legend><span class="fa fa-cogs"></span><?php _e('Sandbox mode settings', WP_APN_PLUGIN)?></legend>
							
							<p class="pure-control-group">
								<label>
									<span class="fa fa-certificate"></span>
									<?php _e('PEM file password', WP_APN_PLUGIN)?>
									<a href="#!" class="js-tip tip" title="<?php _e('Leave it empty if no password specified', WP_APN_PLUGIN)?>"><span class="fa fa-question-circle"></span></a>
								</label>
								<input
									type="text"
									name="<?php echo WP_APN_PLUGIN?>[PassPhraseSandbox]"
									value="<?php echo WPAPN_AdminController::getSetting('PassPhraseSandbox'); ?>"
									/>
							</p>
							
							<p class="pure-control-group">
								<label>
									<span class="fa fa-file-text"></span>
									<?php _e('PEM file', WP_APN_PLUGIN)?>
									<a href="#!" class="js-tip tip" title="<?php _e('ABSPATH will be used as prefix for the specified path', WP_APN_PLUGIN)?>"><span class="fa fa-question-circle"></span></a>
								</label>
								
								<input
									type="text"
									class="js-wp-apn-sandbox-certificate-path"
									name="<?php echo WP_APN_PLUGIN?>[PermissionFileSandbox]"
									value="<?php echo WPAPN_AdminController::getSetting('PermissionFileSandbox'); ?>"
									/>
									
								<button class="button js-wp-apn-sandbox-certificate-upload">
									<?php _e('Upload Certificate', WP_APN_PLUGIN)?>
								</button>
							</p>
						</div>
						
						<div class="row">
							<legend><span class="fa fa-cogs"></span><?php _e('Production mode settings', WP_APN_PLUGIN)?></legend>
							
							<p class="pure-control-group">
								<label>
									<span class="fa fa-certificate"></span>
									<?php _e('PEM file password', WP_APN_PLUGIN)?>
									<a href="#!" class="js-tip tip" title="<?php _e('Leave it empty if no password specified', WP_APN_PLUGIN)?>"><span class="fa fa-question-circle"></span></a>
								</label>
								<input
									type="text"
									name="<?php echo WP_APN_PLUGIN?>[PassPhrase]"
									value="<?php echo WPAPN_AdminController::getSetting('PassPhrase'); ?>"
									/>
							</p>
							
							<p class="pure-control-group">
								<label>
									<span class="fa fa-file-text"></span>
									<?php _e('PEM file', WP_APN_PLUGIN)?>
									<a href="#!" class="js-tip tip" title="<?php _e('ABSPATH will be used as prefix for the specified path', WP_APN_PLUGIN)?>"><span class="fa fa-question-circle"></span></a>
								</label>
								
								<input
									type="text"
									class="js-wp-apn-production-certificate-path"
									name="<?php echo WP_APN_PLUGIN?>[PermissionFile]"
									value="<?php echo WPAPN_AdminController::getSetting('PermissionFile'); ?>"
									/>
									
								<button class="button js-wp-apn-production-certificate-upload">
									<?php _e('Upload Certificate', WP_APN_PLUGIN)?>
								</button>
							</p>
						</div>
						
						<!--<p>
							<blockquote>
								<?php echo sprintf(__('More information about settings you could find below in %s "Documentation" section, "Where to start?" subsection.',LINKED_ARTICLES_PLUGIN),'<span class="fa fa-file-code-o"></span>')?>
							</blockquote>
						</p>-->

						<hr />
						
						<div class="row">
							<button class="button-primary" type="submit">
								<span class="fa fa-save"></span>
								<?php _e('Save', WP_APN_PLUGIN)?>
							</button>
						</div>
						
					</div>
					
				</form>
			
			</div>
			
			
			
			<div class="pure-u-lg-1-3 pure-u-md-1-1 pure-u-sm-1-1 pure-u-xs-1-1">
			
				<div class="wp-apn-bl row">
					<div class="row hdr">
						<h3>
							<span class="fa fa-envelope-o"></span>
							<?php _e('Personal Support', WP_APN_PLUGIN)?>
						</h3>
					</div>
					<div class="row in container">
			
						<div class="row">
							<blockquote>
								<p>
									<?php
			
									$subject = sprintf(__('Support request, plugin: %s (time: %s)', WP_APN_PLUGIN),
										WP_APN_PLUGIN,
										date('d.m.Y H:i:s')
									);
									
									echo sprintf(__('To get support please contact us on address <a target="_blank" href="%s">%s</a>. Please also attach information below to let us know more about your server and site environment - this could be helpful to solve the issue.', WP_APN_PLUGIN),
										'mailto:support@teamlead.pw?subject='.$subject,
										'support@teamlead.pw&nbsp;<span class="fa fa-external-link-square"></span>'
									);?>
								</p>
							</blockquote>
							<p>
								Email: <a target="_blank" href="mailto:support@teamlead.pw?subject=<?php echo $subject;?>">support@teamlead.pw&nbsp;<span class="fa fa-external-link-square"></span></a>
							</p>
							<p>
								<?php _e('Subject', WP_APN_PLUGIN)?>: <?php echo $subject;?>
							</p>
						</div>
			
						<div class="row">
							<h5 class="row">
								<?php _e('Server Info', WP_APN_PLUGIN)?>
							</h5>
							<ul>
								<?php
									foreach(WPAPN_Plugin::serverInfo() as $option => $val) {
										$info = $option.' -> '.$val;
										?>
											<li>
												<?php echo $info; ?>
											</li>
										<?php
									}
								?>
							</ul>
						</div>
						
						<hr />
						
						<div class="row">
							<h5 class="row">
								<?php _e('Theme', WP_APN_PLUGIN)?>
							</h5>
							<?php $current_theme = wp_get_theme(); ?>
							<p>
								<?php echo $current_theme->get('Name');?>,
								<?php echo $current_theme->get('Version');?>,
								<?php echo $current_theme->get('ThemeURI');?>
							</p>
							<p>
								<?php _e('from', WP_APN_PLUGIN)?> <?php echo $current_theme->get('Author');?>,
								<?php echo $current_theme->get('AuthorURI');?>
							</p>
							
						</div>
						
						<hr />
						
						<div class="row">
							<h5 class="row">
								<?php _e('Plugins', WP_APN_PLUGIN)?>
							</h5>
							<ul>
								<?php
									foreach(WPAPN_Plugin::getActivePlugins() as $pl) {
										$plugin = $pl['Name'].', '.$pl['Version'].', '.$pl['PluginURI'];
										?>
											<li>
												<?php echo $plugin; ?>
											</li>
										<?php
									}
								?>
							</ul>
						</div>
				
					</div>
				</div>
				
			</div>
		</div>
		
		<div class="pure-u-1">
			<div class="wp-apn-bl">

				<div class="row hdr">
					<h3>
						<span class="fa fa-tasks"></span>
						<?php _e("Today's log file", WP_APN_PLUGIN)?>
					</h3>
				</div>
		
				<div class="row in wp-apn-logfile-preview">
					<code><pre><?php
						if (file_exists(WPAPN_Plugin::getLogsPath().date('Y-m-d').'.php')) {
							include_once(WPAPN_Plugin::getLogsPath().date('Y-m-d').'.php');
						}
						else {
							_e("Today's log file doesn't exist", WP_APN_PLUGIN); 
						}
						?></pre></code>
				</div>
				
			</div>
		</div>
		
	<div class="pure-u-1">
		<?php
			$documentation_url = WPAPN_Plugin::getDocsUrl();
		?>
		<div class="wp-apn-bl">
			<div class="row hdr">
				<h3>
					<span class="fa fa-file-code-o"></span>
					<?php _e('Documentation', WP_APN_PLUGIN)?>
					<a class="right" target="_blank" href="<?php echo $documentation_url?>" title="<?php _e('open in the separate tab', WP_APN_PLUGIN)?>"><span class="fa fa-external-link"></span></a>
				</h3>
			</div>
			<div class="row in container">
				<iframe class="wp-apn-iframe" src="<?php echo $documentation_url?>" frameborder="0"></iframe>
			</div>
		</div>
	</div>
		
</div>
