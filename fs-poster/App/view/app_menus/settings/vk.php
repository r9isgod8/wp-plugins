<?php

namespace FSPoster\App\view;

use FSPoster\App\Providers\DB;
use FSPoster\App\Providers\Helper;

defined( 'ABSPATH' ) or exit;
?>

<div style="display: flex;">
	<div style="width: 50%;">
		<div class="fs_setting_item">
			<div class="fs_setting_item_label">
				<div><?=esc_html__('Load own communities:' , 'fs-poster');?></div>
				<div class="fs_s_help"><?=esc_html__('Enable to add all the included \'own\' communities (page / group / event) automatically when a new account is added. Consequently, you can share posts on these communities.' , 'fs-poster');?></div>
			</div>
			<div class="fs_s_input">
				<div class="fs_onoffswitch">
					<input type="checkbox" name="fs_vk_load_admin_communities" class="fs_onoffswitch-checkbox" id="fs_vk_load_admin_communities"<?=get_option('fs_vk_load_admin_communities', '1')?' checked':''?>>
					<label class="fs_onoffswitch-label" for="fs_vk_load_admin_communities"></label>
				</div>
			</div>
		</div>
	</div>
</div>

<div style="display: flex;">
	<div style="width: 50%;">
		<div class="fs_setting_item">
			<div class="fs_setting_item_label">
				<div><?=esc_html__('Load communities:' , 'fs-poster');?></div>
				<div class="fs_s_help"><?=esc_html__('Enable to add all the included communities (page / group / event) automatically when a new account is added. Consequently, you can share posts on these communities.' , 'fs-poster');?></div>
			</div>
			<div class="fs_s_input">
				<div class="fs_onoffswitch">
					<input type="checkbox" name="fs_vk_load_members_communities" class="fs_onoffswitch-checkbox" id="fs_vk_load_members_communities"<?=get_option('fs_vk_load_members_communities', '1')?' checked':''?> onchange="if($(this).is(':checked')){ $('#hide2').fadeIn(fadeSpeed); }else{ $('#hide2').fadeOut(fadeSpeed); }">
					<label class="fs_onoffswitch-label" for="fs_vk_load_members_communities"></label>
				</div>
			</div>
		</div>
	</div>
	<div style="width: 50%;" id="hide2">
		<div class="fs_setting_item">
			<div class="fs_setting_item_label">
				<div><?=esc_html__('Maximum communities to load:' , 'fs-poster');?></div>
				<div class="fs_s_help"><?=esc_html__('Within the added account, communities will have a limit in regards to amount of loads (you will have a MAX of 1000 communities to load).' , 'fs-poster');?></div>
			</div>
			<div class="fs_s_input">
				<input type="text" name="vk_max_communities_limit" class="ws_form_element" style="text-align: center; width: 50px;" value="<?=esc_html(get_option('fs_vk_max_communities_limit', '100'))?>">
			</div>
		</div>
	</div>
</div>

<div style="display: flex;">
	<div style="width: 50%;">
		<div class="fs_setting_item">
			<div class="fs_setting_item_label">
				<div><?=esc_html__('Upload featured image to VK:' , 'fs-poster');?></div>
				<div class="fs_s_help"><?=esc_html__('Enable to upload the featured image to VK, not attached image from the link; otherwise, your posts on VK will be shared as link card view.' , 'fs-poster');?></div>
			</div>
			<div class="fs_s_input">
				<div class="fs_onoffswitch">
					<input type="checkbox" name="fs_vk_upload_image" class="fs_onoffswitch-checkbox" id="fs_vk_upload_image"<?=get_option('fs_vk_upload_image', '1')?' checked':''?>>
					<label class="fs_onoffswitch-label" for="fs_vk_upload_image"></label>
				</div>
			</div>
		</div>
	</div>
</div>

<div class="fs_setting_item">
	<div class="fs_setting_item_label" style="width: 55%;">
		<div><?=esc_html__('Custom text:' , 'fs-poster')?></div>
		<div class="fs_s_help">
			<div><?=esc_html__('You can customize the text of the shared post as you like by using the current keywords.' , 'fs-poster')?></div>
			<div>
				<div class="fs_text_codes">
					<div><?=esc_html__('Post ID' , 'fs-poster')?></div>
					<div class="ws_tooltip ws_color_info append_to_text" data-title="<?=esc_html__('Click to append in text' , 'fs-poster')?>">{id}</div>
				</div>
				<div class="fs_text_codes">
					<div><?=esc_html__('Post title' , 'fs-poster')?></div>
					<div class="ws_tooltip ws_color_info append_to_text" data-title="<?=esc_html__('Click to append in text' , 'fs-poster')?>">{title}</div>
				</div>
				<div class="fs_text_codes">
					<div><?=esc_html__('Post excerpt' , 'fs-poster')?></div>
					<div class="ws_tooltip ws_color_info append_to_text" data-title="<?=esc_html__('Click to append in text' , 'fs-poster')?>">{excerpt}</div>
				</div>
				<div class="fs_text_codes">
					<div><?=esc_html__('Post author name' , 'fs-poster')?></div>
					<div class="ws_tooltip ws_color_info append_to_text" data-title="<?=esc_html__('Click to append in text' , 'fs-poster')?>">{author}</div>
				</div>
				<div class="fs_text_codes">
					<div><?=esc_html__('Post content (first 40 symbols)' , 'fs-poster')?></div>
					<div class="ws_tooltip ws_color_info append_to_text" data-title="<?=esc_html__('Click to append in text' , 'fs-poster')?>">{content_short_40}</div>
				</div>
				<div class="fs_text_codes">
					<div><?=esc_html__('Post content Full' , 'fs-poster')?></div>
					<div class="ws_tooltip ws_color_info append_to_text" data-title="<?=esc_html__('Click to append in text' , 'fs-poster')?>">{content_full}</div>
				</div>
				<div class="fs_text_codes">
					<div><?=esc_html__('Post link' , 'fs-poster')?></div>
					<div class="ws_tooltip ws_color_info append_to_text" data-title="<?=esc_html__('Click to append in text' , 'fs-poster')?>">{link}</div>
				</div>
				<div class="fs_text_codes">
					<div><?=esc_html__('Post short link' , 'fs-poster')?></div>
					<div class="ws_tooltip ws_color_info append_to_text" data-title="<?=esc_html__('Click to append in text' , 'fs-poster')?>">{short_link}</div>
				</div>
				<div class="fs_text_codes">
					<div><?=esc_html__('Featured image URL' , 'fs-poster')?></div>
					<div class="ws_tooltip ws_color_info append_to_text" data-title="<?=esc_html__('Click to append in text' , 'fs-poster')?>">{featured_image_url}</div>
				</div>
				<div class="fs_text_codes">
					<div><?=esc_html__('WooCommerce - product price' , 'fs-poster')?></div>
					<div class="ws_tooltip ws_color_info append_to_text" data-title="<?=esc_html__('Click to append in text' , 'fs-poster')?>">{product_regular_price}</div>
				</div>
				<div class="fs_text_codes">
					<div><?=esc_html__('WooCommerce - product sale price' , 'fs-poster')?></div>
					<div class="ws_tooltip ws_color_info append_to_text" data-title="<?=esc_html__('Click to append in text' , 'fs-poster')?>">{product_sale_price}</div>
				</div>
				<div class="fs_text_codes">
					<div><?=esc_html__('Unique ID' , 'fs-poster')?></div>
					<div class="ws_tooltip ws_color_info append_to_text" data-title="<?=esc_html__('Click to append in text' , 'fs-poster')?>">{uniq_id}</div>
				</div>
				<div class="fs_text_codes">
					<div><?=esc_html__('Post Tags' , 'fs-poster')?></div>
					<div class="ws_tooltip ws_color_info append_to_text" data-title="<?=esc_html__('Click to append in text' , 'fs-poster')?>">{tags}</div>
				</div>
				<div class="fs_text_codes">
					<div><?=esc_html__('Post Categories' , 'fs-poster')?></div>
					<div class="ws_tooltip ws_color_info append_to_text" data-title="<?=esc_html__('Click to append in text' , 'fs-poster')?>">{categories}</div>
				</div>
				<div class="fs_text_codes">
					<div><?=esc_html__('Custom fields' , 'fs-poster')?></div>
					<div class="ws_tooltip ws_color_info append_to_text" data-title="<?=esc_html__('Click to append in text' , 'fs-poster')?>">{cf_KEY}</div>
				</div>
			</div>
		</div>
	</div>
	<div class="fs_s_input" style="width: 45%;">
		<textarea class="ws_form_element2" name="fs_post_text_message_vk" id="custom_text_area" style="height: 150px !important;"><?=esc_html(get_option('fs_post_text_message_vk', "{title}"))?></textarea>
	</div>
</div>

<script>
	var fadeSpeed = 0;
	jQuery(document).ready(function()
	{
		$("#save_btn").click(function()
		{
			var data = fsCode.serialize($(".settings_form"));

			fsCode.ajax('settings_vk_save' , data , function(result)
			{
				fsCode.toast("<?=esc_html__('Save successful!' , 'fs-poster');?>" , 'success');
			});
		});

		$("#fs_vk_load_members_communities").trigger('change');

		fadeSpeed = 200;
	});
</script>