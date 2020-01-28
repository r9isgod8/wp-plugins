<?php

namespace FSPoster\App\Controller;

use FSPoster\App\Providers\DB;
use FSPoster\App\Providers\Helper;
use FSPoster\App\Providers\Request;

class Modal
{

	public function __construct()
	{
		$methods = get_class_methods($this);
		foreach ($methods AS $method)
		{
			if( strpos($method , 'modal_') !== 0 )
			{
				continue;
			}

			add_action( 'wp_ajax_' . $method, function() use($method)
			{
				define('MODAL' , true);
				$this->$method();
				exit();
			});
		}
	}

	public function modal_add_fb_account()
	{
		Helper::modalView('fb.add_account');
	}

	public function modal_add_twitter_account()
	{
		Helper::modalView('twitter.add_account');
	}

	public function modal_add_linkedin_account()
	{
		Helper::modalView('linkedin.add_account');
	}

	public function modal_add_ok_account()
	{
		Helper::modalView('ok.add_account');
	}

	public function modal_add_pinterest_account()
	{
		Helper::modalView('pinterest.add_account');
	}

	public function modal_add_reddit_account()
	{
		Helper::modalView('reddit.add_account');
	}

	public function modal_add_tumblr_account()
	{
		Helper::modalView('tumblr.add_account');
	}

	public function modal_reddit_add_subreddit()
	{
		Helper::modalView('reddit.add_subreddit');
	}


	public function modal_edit_pinterest_account_board()
	{
		Helper::modalView('pinterest.edit_account_board');
	}

	public function modal_posts_list()
	{
		Helper::modalView('other.posts_list');
	}

	public function modal_add_vk_account()
	{
		Helper::modalView('vk.add_account');
	}

	public function modal_add_instagram_account()
	{
		Helper::modalView('instagram.add_account');
	}

	public function modal_add_instagram_account_case()
	{
		Helper::modalView('instagram.add_account_case');
	}

	public function modal_add_instagram_account_cookies_method()
	{
		Helper::modalView('instagram.add_account_cookies_method');
	}

	public function modal_add_app()
	{
		Helper::modalView('app.add');
	}

	public function modal_add_node_to_list()
	{
		Helper::modalView('other.add_node_to_list');
	}

	public function modal_share_feeds()
	{
		$postId = Request::post('post_id' , '0' , 'num');
		if( !($postId > 0) )
		{
			exit();
		}

		$feeds = DB::fetchAll('feeds' , ['post_id' => $postId , 'is_sended' => 0]);

		Helper::modalView('other.share_feeds' , [
			'feeds' =>  $feeds
		]);
	}

	public function modal_share_saved_post()
	{
		$postId = Request::post('post_id' , '0' , 'num');

		if( !($postId > 0) )
		{
			exit();
		}

		Helper::modalView('other.share_saved_post' , [
			'postId'    =>  $postId
		]);
	}

	public function modal_plan_saved_post()
	{
		$postId1 = Request::post('post_id' , '0' , 'num');

		if( $postId1 > 0 )
		{
			$posts = [ (int)$postId1 ];
		}
		else
		{
			$postIds = Request::post('post_id' , [] , 'array');
			$posts = [];
			foreach( $postIds AS $postId )
			{
				if( is_numeric($postId) && $postId > 0 )
				{
					$posts[] = (int)$postId;
				}
			}
		}

		if( empty($posts) )
		{
			exit();
		}

		Helper::modalView('schedule.plan_post' , [
			'postIds'    =>  $posts
		]);
	}

	public function modal_show_nodes_list()
	{
		Helper::modalView('other.show_nodes_list');
	}

	public function modal_add_schedule()
	{
		Helper::modalView('schedule.add');
	}

	public function modal_activate_with_condition()
	{
		$id = Request::post('id' , '0' , 'num');
		$type = Request::post('type' , '' , 'string');

		$ajaxUrl = $type == 'node' ? 'settings_node_activity_change' : 'account_activity_change';

		Helper::modalView('other.activate_with_condition' , ['id' => $id , 'ajaxUrl' => $ajaxUrl]);
	}

	public function modal_google_show_communities_list()
	{
		Helper::modalView('google.show_communities_list' , []);
	}

	public function modal_google_add_community()
	{
		Helper::modalView('google.add_community' , []);
	}

	public function modal_add_google_b_account()
	{
		Helper::modalView('google.add_account' , []);
	}

	public function modal_add_telegram_bot()
	{
		Helper::modalView('telegram.add_bot' , []);
	}

	public function modal_show_telegram_chats()
	{
		Helper::modalView('telegram.show_chats', []);
	}

	public function modal_telegram_add_chat()
	{
		Helper::modalView('telegram.add_chat', []);
	}

	public function modal_add_medium_account()
	{
		Helper::modalView('medium.add_account', []);
	}

}
