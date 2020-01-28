<?php

namespace FSPoster\App\view;

use FSPoster\App\Providers\DB;
use FSPoster\App\Providers\Helper;

defined('MODAL') or exit();
?>

<style>
	.instagram_logo > img
	{
		width: 60%;
		height: 180px;
		margin: 20px;
	}
	.instagram_logo
	{
		width: 55%;
		display: flex;
		justify-content: center;
	}
	#proxy_show
	{
		margin-bottom: 20px;
		margin-right: 70px;
		display: flex;
		align-items: center;
	}
	#proxy_show
	{
		width: 180px;
	}
</style>
<span class="close" data-modal-close="true">&times;</span>

<div style="width: 100%; margin-top: 40px; display: flex; justify-content: center; align-items: center;">
	<div class="instagram_logo"><img src="<?=Helper::assets('images/instagram.png')?>"></div>
	<div style="width: 45%;">

		<div style="margin-bottom: 20px; font-size: 15px; font-weight: 600; color: #888; margin-top: 20px;"><?=esc_html__('Add new instagram account' , 'fs-poster')?> <a href="https://www.fs-poster.com/doc/add-instagram-account" target="_blank" class="ws_tooltip" data-title="How to?"><i class="fa fa-question-circle" style="color: #ff7171;"></i></a></div>

		<div style="position: relative; margin-bottom: 17px; margin-right: 70px; margin-top: 50px;">
			<input type="text" placeholder="Cookie - sessionid" class="ws_form_element cookie_sessionid" style="padding-left: 30px">
			<i class="far fa-copy" style="position: absolute; left: 10px; color: #AAA; top: 10px;"></i>
		</div>

		<div style="margin-bottom: 20px;" onclick="$(this).slideUp(200 , function(){ $('#proxy_show').slideDown(200); });">
			<label style="color: #74b9ff;"><i class="fa fa-globe"></i> Use proxy</label>
		</div>

		<div style="display: none;" id="proxy_show">
			<div style="position: relative;">
				<input type="text" placeholder="Proxy" class="ws_form_element proxy" style="padding-left: 30px">
				<i class="fas fa-globe" style="position: absolute; left: 10px; color: #74b9ff; top: 10px;"></i>
			</div>
			<div style="width: 30px; text-align: right; cursor: help;" class="ws_tooltip" data-float="left" data-title="<?=esc_html__('Optional field. Supported proxy formats: https://127.0.0.1:8888 or https://user:pass@127.0.0.1:8888' , 'fs-poster')?>"><i class="fa fa-info-circle" style="color: #999;"></i></div>
		</div>

		<div style="margin-bottom: 30px; margin-top: 50px;">
			<button type="button" class="ws_btn ws_bg_danger add_account_btn"><?=esc_html__('ADD ACCOUNT' , 'fs-poster')?></button>
			<button type="button" class="ws_btn" data-modal-close="true"><?=esc_html__('CANCEL' , 'fs-poster')?></button>
		</div>
	</div>
</div>

<script>

	$("#proModal<?=$mn?> .add_account_btn").click(function()
	{
		var cookie_sessionid    = $("#proModal<?=$mn?> .cookie_sessionid").val(),
			proxy       		= $("#proModal<?=$mn?> .proxy").val();

		fsCode.ajax('add_instagram_account_cookie_method' , {
			'cookie_sessionid':		cookie_sessionid ,
			'proxy':				proxy
		}, function(response)
		{
			fsCode.toast("<?=esc_html__('Account added successfully!' , 'fs-poster')?>" , 'success');
			fsCode.modalHide($("#proModal<?=$mn?>"));
			$('#fs_account_supports .fs_social_network_div[data-setting="instagram"]').click();
		});
	});

</script>