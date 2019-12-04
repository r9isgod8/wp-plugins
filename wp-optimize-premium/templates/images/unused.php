<?php if (!defined('WPO_VERSION')) die('No direct access allowed'); ?>
<div class="wpo-unused-images-section">
	<div class="wpo_shade"></div>
	<h3 class="wpo-first-child"><?php _e('Unused images', 'wp-optimize');?> <img class="wpo_unused_images_loader" width="16" height="16" src="<?php echo admin_url(); ?>/images/spinner-2x.gif" /></h3>
	<div class="wp-optimize-images-download-csv">
		<a href="<?php echo add_query_arg(array('wpo_unused_images_csv' => '1', '_nonce' => wp_create_nonce('wpo_unused_images_csv'))); ?>"><?php _e('Download as CSV', 'wp-optimize'); ?></a>
	</div>
	<div class="wpo_unused_images_switch_view">
		<a href="javascript:;" data-mode="grid"><span class="dashicons dashicons-grid-view"></span></a>
		<a href="javascript:;" data-mode="list"><span class="dashicons dashicons-list-view"></span></a>
	</div>
	<div class="wp-optimize-images-refresh-icon" style="float:right">
		&nbsp;
		<a href="javascript:;" id="wpo_unused_images_refresh" class="wpo-refresh-button"><span class="dashicons dashicons-image-rotate"></span><?php _e('Refresh image list', 'wp-optimize'); ?></a>
	</div>
	<div class="wpo_unused_images_buttons_wrap">
		<a href="javascript:;" id="wpo_unused_images_select_all"><?php _e('Select all', 'wp-optimize'); ?></a> /
		<a href="javascript:;" id="wpo_unused_images_select_none"><?php _e('Select none', 'wp-optimize'); ?></a>
	</div>

	<div id="wpo_unused_images"></div>
	
	<p id="wpo_unused_images_loaded_count"></p>

	<div id="wpo_unused_images_loader_bottom">
		<img width="16" height="16" src="<?php echo admin_url(); ?>/images/spinner-2x.gif" />
	</div>

	<div class="wpo-fieldgroup">
		<div id="wpo_unused_images_sites_select_container">
			<label for="wpo_unused_images_sites_select"><?php _e('Select site', 'wp-optimize');?> </label>
			<select id="wpo_unused_images_sites_select"></select>
		</div>
		<div class="notice notice-warning wpo-warning">
			<p>
				<span class="dashicons dashicons-shield"></span>
				<?php _e('This action is irreversible if you do not have a backup.', 'wp-optimize'); ?><br>
				<?php _e('You are recommended to review all images and take a backup before running this action.', 'wp-optimize'); ?><br>
				<strong><?php _e('You may have plugins which do not correctly register their images as in-use.', 'wp-optimize'); ?></strong>
			</p>
		</div>
		<input type="button" id="wpo_remove_unused_images_btn" class="button button-primary button-large" value="<?php _e('Remove selected images', 'wp-optimize'); ?>" />
		<?php $wp_optimize->include_template('take-a-backup.php'); ?>
	</div>
</div>

<div class="wpo-image-sizes-section">
	<h3><?php _e('Image sizes', 'wp-optimize'); ?></h3>
	<div class="wpo_shade"></div>
	<div class="wpo-fieldgroup">
		<h3><?php _e('Registered image sizes', 'wp-optimize'); ?></h3><img class="wpo_unused_images_loader" width="20" height="20" src="<?php echo admin_url(); ?>/images/spinner-2x.gif" />
		<div id="registered_image_sizes"></div>
		<h3><?php _e('Unused image sizes', 'wp-optimize');?></h3><img class="wpo_unused_images_loader" width="20" height="20" src="<?php echo admin_url(); ?>/images/spinner-2x.gif" />
		<p class="hide_on_empty">
			<?php _e('These image sizes were used by some of the themes or plugins installed previously and they remain within your database.', 'wp-optimize'); ?>
			<a href="https://codex.wordpress.org/Post_Thumbnails#Add_New_Post_Thumbnail_Sizes" target="_blank"><?php _e('Read more about custom image sizes here.', 'wp-optimize'); ?></a>
		</p>
		<div id="unused_image_sizes"></div>
		<div class="wpo_remove_selected_sizes_btn__container">
			<div class="notice notice-warning wpo-warning">
				<p>
					<span class="dashicons dashicons-shield"></span>
					<?php _e("This feature is for experienced users. Don't remove registered image sizes if you are not sure that images with selected sizes are not used on your site.", 'wp-optimize'); ?>
				</p>
			</div>
			<input type="button" id="wpo_remove_selected_sizes_btn" class="button button-primary button-large" value="<?php _e('Remove selected sizes', 'wp-optimize'); ?>" disabled />
			<?php $wp_optimize->include_template('take-a-backup.php'); ?>
		</div>
	</div>
</div>
