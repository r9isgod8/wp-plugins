<?php

namespace FSPoster\App\Controller\Ajax;

use FSPoster\App\Providers\DB;
use FSPoster\App\Providers\Helper;
use FSPoster\App\Providers\Request;

trait Settings
{

	private function isAdmin()
	{
		if( !current_user_can('administrator') )
		{
			exit();
		}
	}

	public function settings_general_save()
	{
		$this->isAdmin();

		$fs_show_fs_poster_column = Request::post('fs_show_fs_poster_column' , 0 , 'string' , ['on']) === 'on' ? 1 : 0;
		$fs_allowed_post_types = Request::post('fs_allowed_post_types' , ['post' , 'attachment' , 'page' , 'product'] , 'array');
		$fs_collect_statistics				= Request::post('fs_collect_statistics' , 0 , 'string' , ['on']) === 'on' ? 1 : 0;

		$newArrPostTypes = [];
		$allTypes = get_post_types();
		foreach( $fs_allowed_post_types AS $fsAPT )
		{
			if( is_string($fsAPT) && in_array( $fsAPT , $allTypes ))
			{
				$newArrPostTypes[] = $fsAPT;
			}
		}
		$newArrPostTypes = implode('|' , $newArrPostTypes);

		$fs_hide_for_roles = Request::post('fs_hide_for_roles' , [] , 'array');
		$newArrHideForRoles = [];
		$allRoles = get_editable_roles();
		foreach( $fs_hide_for_roles AS $fsAPT )
		{
			if( $fsAPT != 'administrator' && is_string($fsAPT) && isset( $allRoles[$fsAPT] ) )
			{
				$newArrHideForRoles[] = $fsAPT;
			}
		}
		$newArrHideForRoles = implode('|' , $newArrHideForRoles);

		update_option('fs_show_fs_poster_column' , (string)$fs_show_fs_poster_column);
		update_option('fs_allowed_post_types' , $newArrPostTypes);
		update_option('fs_hide_menu_for' , $newArrHideForRoles);
		update_option('fs_collect_statistics' , (string)$fs_collect_statistics);

		Helper::response(true);
	}

	public function settings_share_save()
	{
		$this->isAdmin();

		$fs_auto_share_new_posts	= Request::post('fs_auto_share_new_posts' , 0 , 'string' , ['on']) === 'on' ? 1 : 0;
		$fs_share_on_background		= Request::post('fs_share_on_background' , 0 , 'string' , ['on']) === 'on' ? 1 : 0;
		$fs_share_timer				= Request::post('fs_share_timer' , '0' , 'integer' );
		$fs_keep_logs				= Request::post('fs_keep_logs' , 0 , 'string' , ['on']) === 'on' ? 1 : 0;
		$fs_post_interval			= Request::post('fs_post_interval' , '0' , 'integer' );
		$fs_post_interval_type		= Request::post('fs_post_interval_type' , 0 , 'string' , ['on']) === 'on' ? 1 : 0;

		update_option('fs_auto_share_new_posts' , (string)$fs_auto_share_new_posts);
		update_option('fs_share_on_background' , (string)$fs_share_on_background);
		update_option('fs_share_timer' , $fs_share_timer);
		update_option('fs_keep_logs' , (string)$fs_keep_logs);
		update_option('fs_post_interval' , (string)$fs_post_interval);
		update_option('fs_post_interval_type' , (string)$fs_post_interval_type);

		Helper::response(true);
	}

	public function settings_url_save()
	{
		$this->isAdmin();

		$fs_unique_link						= Request::post('fs_unique_link' , 0 , 'string' , ['on']) === 'on' ? 1 : 0;

		$fs_url_shortener					= Request::post('fs_url_shortener' , 0 , 'string' , ['on']) === 'on' ? 1 : 0;
		$fs_shortener_service				= Request::post('fs_shortener_service' , 0 , 'string' , ['tinyurl' , 'bitly']);
		$fs_url_short_access_token_bitly	= Request::post('fs_url_short_access_token_bitly' , '' , 'string' );
		$fs_url_additional					= Request::post('fs_url_additional' , '' , 'string' );

		$fs_share_custom_url				= Request::post('fs_share_custom_url' , 0 , 'string' , ['on']) === 'on' ? 1 : 0;
		$fs_custom_url_to_share				= Request::post('fs_custom_url_to_share' , '' , 'string' );

		update_option('fs_unique_link' , (string)$fs_unique_link);
		update_option('fs_url_shortener' , (string)$fs_url_shortener);
		update_option('fs_shortener_service' , $fs_shortener_service);
		update_option('fs_url_short_access_token_bitly' , $fs_url_short_access_token_bitly);
		update_option('fs_url_additional' , $fs_url_additional);

		update_option('fs_share_custom_url' , (string)$fs_share_custom_url);
		update_option('fs_custom_url_to_share' , $fs_custom_url_to_share);

		Helper::response(true);
	}

	public function settings_facebook_save()
	{
		$this->isAdmin();

		$fs_load_own_pages = Request::post('fs_load_own_pages' , 0 , 'string' , ['on']) === 'on' ? 1 : 0;
		$fs_load_groups = Request::post('fs_load_groups' , 0 , 'string' , ['on']) === 'on' ? 1 : 0;

		$fs_max_groups_limit = Request::post('fs_max_groups_limit' , '50' , 'num');


		if( $fs_max_groups_limit > 1000 )
			$fs_max_groups_limit = 1000;

		$fs_post_text_message_fb = Request::post('fs_post_text_message_fb' , '' , 'string');
		$fs_facebook_posting_type = Request::post('fs_facebook_posting_type' , '1' , 'num' , ['1', '2', '3'] );


		update_option('fs_post_text_message_fb' , $fs_post_text_message_fb);

		update_option('fs_load_own_pages' , (string)$fs_load_own_pages);

		update_option('fs_load_groups' , (string)$fs_load_groups);
		update_option('fs_max_groups_limit' , $fs_max_groups_limit);

		update_option('fs_facebook_posting_type' , $fs_facebook_posting_type);

		Helper::response(true);
	}

	public function settings_instagram_save()
	{
		$this->isAdmin();

		$fs_instagram_post_in_type = Request::post('fs_instagram_post_in_type' , 0 , 'int' , [1,2,3]);
		$fs_instagram_story_link = Request::post('fs_instagram_story_link' , 0 , 'string' , ['on']) === 'on' ? 1 : 0;
		$fs_instagram_story_hashtag = Request::post('fs_instagram_story_hashtag' , 0 , 'string' , ['on']) === 'on' ? 1 : 0;

		$fs_instagram_story_hashtag_name = Request::post('fs_instagram_story_hashtag_name' , '' , 'string');
		$fs_instagram_story_hashtag_position = Request::post('fs_instagram_story_hashtag_position' , 'top' , 'string' , ['top' , 'bottom']);

		if( $fs_instagram_story_hashtag && empty($fs_instagram_story_hashtag_name) )
		{
			Helper::response(false , 'Plase type the hashtag');
		}

		$fs_post_text_message_instagram = Request::post('fs_post_text_message_instagram' , '' , 'string');
		$fs_post_text_message_instagram_h = Request::post('fs_post_text_message_instagram_h' , '' , 'string');

		$fs_instagram_story_background = Request::post('fs_instagram_story_background' , '' , 'string');
		$fs_instagram_story_title_background = Request::post('fs_instagram_story_title_background' , '' , 'string');
		$fs_instagram_story_title_background_opacity = Request::post('fs_instagram_story_title_background_opacity' , '' , 'int');
		$fs_instagram_story_title_color = Request::post('fs_instagram_story_title_color' , '' , 'string');
		$fs_instagram_story_title_top = Request::post('fs_instagram_story_title_top' , '' , 'string');
		$fs_instagram_story_title_left = Request::post('fs_instagram_story_title_left' , '' , 'string');
		$fs_instagram_story_title_width = Request::post('fs_instagram_story_title_width' , '' , 'string');
		$fs_instagram_story_title_font_size = Request::post('fs_instagram_story_title_font_size' , '' , 'string');


		update_option('fs_post_text_message_instagram' , $fs_post_text_message_instagram);
		update_option('fs_post_text_message_instagram_h' , $fs_post_text_message_instagram_h);

		update_option('fs_instagram_post_in_type' , $fs_instagram_post_in_type);
		update_option('fs_instagram_story_link' , (string)$fs_instagram_story_link);
		update_option('fs_instagram_story_hashtag' , (string)$fs_instagram_story_hashtag);

		update_option('fs_instagram_story_hashtag_name' , $fs_instagram_story_hashtag ? $fs_instagram_story_hashtag_name : '');
		update_option('fs_instagram_story_hashtag_position' , $fs_instagram_story_hashtag ? $fs_instagram_story_hashtag_position : '');

		update_option('fs_instagram_story_background' , $fs_instagram_story_background );
		update_option('fs_instagram_story_title_background' , $fs_instagram_story_title_background );
		update_option('fs_instagram_story_title_background_opacity' , ($fs_instagram_story_title_background_opacity > 100 || $fs_instagram_story_title_background_opacity < 0 ? 30 : $fs_instagram_story_title_background_opacity) );
		update_option('fs_instagram_story_title_color' , $fs_instagram_story_title_color );
		update_option('fs_instagram_story_title_top' , $fs_instagram_story_title_top );
		update_option('fs_instagram_story_title_left' , $fs_instagram_story_title_left );
		update_option('fs_instagram_story_title_width' , $fs_instagram_story_title_width );
		update_option('fs_instagram_story_title_font_size' , $fs_instagram_story_title_font_size );

		Helper::response(true);
	}

	public function settings_vk_save()
	{
		$this->isAdmin();

		$fs_vk_load_admin_communities = Request::post('fs_vk_load_admin_communities' , 0 , 'string' , ['on']) === 'on' ? 1 : 0;
		$fs_vk_load_members_communities = Request::post('fs_vk_load_members_communities' , 0 , 'string' , ['on']) === 'on' ? 1 : 0;
		$fs_vk_upload_image = Request::post('fs_vk_upload_image' , 0 , 'string' , ['on']) === 'on' ? 1 : 0;

		$fs_vk_max_communities_limit = Request::post('fs_vk_max_communities_limit' , '50' , 'num');

		if( $fs_vk_max_communities_limit > 1000 )
			$fs_vk_max_communities_limit = 1000;


		$fs_post_text_message_vk = Request::post('fs_post_text_message_vk' , '' , 'string');

		update_option('fs_post_text_message_vk' , $fs_post_text_message_vk);

		update_option('fs_vk_load_admin_communities' , (string)$fs_vk_load_admin_communities);
		update_option('fs_vk_load_members_communities' , (string)$fs_vk_load_members_communities);

		update_option('fs_vk_max_communities_limit' , $fs_vk_max_communities_limit);
		update_option('fs_vk_upload_image' , $fs_vk_upload_image);

		Helper::response(true);
	}

	public function settings_twitter_save()
	{
		$this->isAdmin();

		$fs_post_text_message_twitter = Request::post('fs_post_text_message_twitter' , '' , 'string');
		$fs_twitter_auto_cut_tweets = Request::post('fs_twitter_auto_cut_tweets' , 0 , 'string' , ['on']) === 'on' ? 1 : 0;
		$fs_twitter_posting_type = Request::post('fs_twitter_posting_type' , '1' , 'num' , ['1', '2', '3'] );

		update_option('fs_post_text_message_twitter' , $fs_post_text_message_twitter);
		update_option('fs_twitter_auto_cut_tweets' , $fs_twitter_auto_cut_tweets);
		update_option('fs_twitter_posting_type' , $fs_twitter_posting_type);

		Helper::response(true);
	}

	public function settings_linkedin_save()
	{
		$this->isAdmin();

		$fs_post_text_message_linkedin = Request::post('fs_post_text_message_linkedin' , '' , 'string');

		update_option('fs_post_text_message_linkedin' , $fs_post_text_message_linkedin);

		Helper::response(true);
	}

	public function settings_pinterest_save()
	{
		$this->isAdmin();

		$fs_post_text_message_pinterest = Request::post('fs_post_text_message_pinterest' , '' , 'string');

		update_option('fs_post_text_message_pinterest' , $fs_post_text_message_pinterest);

		Helper::response(true);
	}

	public function settings_google_b_save()
	{
		$this->isAdmin();

		$fs_post_text_message_google_b	= Request::post('fs_post_text_message_google_b' , '' , 'string');
		$fs_google_b_share_as_product	= Request::post('fs_google_b_share_as_product' , 0 , 'string' , ['on']) === 'on' ? 1 : 0;
		$fs_google_b_button_type		= Request::post('fs_google_b_button_type' , 'LEARN_MORE' , 'string', ['BOOK', 'ORDER', 'SHOP', 'SIGN_UP', '-']);

		update_option('fs_post_text_message_google_b' , $fs_post_text_message_google_b);
		update_option('fs_google_b_share_as_product' , $fs_google_b_share_as_product);
		update_option('fs_google_b_button_type' , $fs_google_b_button_type);

		Helper::response(true);
	}

	public function settings_tumblr_save()
	{
		$this->isAdmin();

		$fs_post_text_message_tumblr = Request::post('fs_post_text_message_tumblr' , '' , 'string');

		update_option('fs_post_text_message_tumblr' , $fs_post_text_message_tumblr);

		Helper::response(true);
	}

	public function settings_reddit_save()
	{
		$this->isAdmin();

		$fs_post_text_message_reddit = Request::post('fs_post_text_message_reddit' , '' , 'string');

		update_option('fs_post_text_message_reddit' , $fs_post_text_message_reddit);

		Helper::response(true);
	}

	public function settings_ok_save()
	{
		$this->isAdmin();

		$fs_post_text_message_ok = Request::post('fs_post_text_message_ok' , '' , 'string');
		$fs_ok_posting_type = Request::post('fs_ok_posting_type' , '1' , 'num' , ['1', '2', '3'] );

		update_option('fs_post_text_message_ok' , $fs_post_text_message_ok);
		update_option('fs_ok_posting_type' , $fs_ok_posting_type);

		Helper::response(true);
	}

	public function settings_telegram_save()
	{
		$this->isAdmin();

		$fs_post_text_message_telegram	= Request::post('fs_post_text_message_telegram' , '' , 'string');
		$fs_telegram_type_of_sharing	= Request::post('fs_telegram_type_of_sharing' , '1' , 'int', [ '1', '2', '3', '4' ]);

		update_option('fs_post_text_message_telegram' , $fs_post_text_message_telegram);
		update_option('fs_telegram_type_of_sharing' , $fs_telegram_type_of_sharing);

		Helper::response(true);
	}

	public function settings_medium_save()
	{
		$this->isAdmin();

		$fs_post_text_message_medium = Request::post('fs_post_text_message_medium' , '' , 'string');

		update_option('fs_post_text_message_medium' , $fs_post_text_message_medium);

		Helper::response(true);
	}

}