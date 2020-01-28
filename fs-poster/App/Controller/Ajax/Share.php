<?php

namespace FSPoster\App\Controller\Ajax;

use FSPoster\App\Providers\CronJob;
use FSPoster\App\Providers\DB;
use FSPoster\App\Providers\Helper;
use FSPoster\App\Providers\Request;
use FSPoster\App\Providers\SocialNetworkPost;

trait Share
{
	public function share_post()
	{
		error_reporting(E_ALL);
		ini_set('display_errors','on');
		if( !(isset($_POST['id']) && is_numeric($_POST['id']) && $_POST['id'] > 0) )
		{
			exit();
		}

		$feedId = (int)$_POST['id'];

		$res = SocialNetworkPost::post($feedId);

		Helper::response(true, ['result' => $res]);
	}

	public function share_saved_post()
	{
		$postId				=	Request::post('post_id' , '0' , 'num');
		$nodes				=	Request::post('nodes' , [] , 'array');
		$background			=	!(Request::post('background' , '0' , 'string')) ? 0 : 1;
		$custom_messages	=	Request::post('custom_messages' , [] , 'array');

		if( empty($postId) || empty($nodes) || $postId <= 0 )
		{
			Helper::response(false);
		}

		$postInterval = (int)get_option('fs_post_interval' , '0');

		$postCats = Helper::getPostCatsArr( $postId );
		$insertedCount = 0;

		foreach( $nodes AS $nodeId )
		{
			if( is_string($nodeId) && strpos( $nodeId , ':' ) !== false )
			{
				$parse = explode(':' , $nodeId);
				$driver = $parse[0];
				$nodeType = $parse[1];
				$nodeId = $parse[2];
				$filterType = isset($parse[3]) ? $parse[3] : 'no';
				$categoriesStr = isset($parse[4]) ? $parse[4] : '';

				if( $postCats !== false )
				{
					$categoriesFilter = [];

					if( !empty($categoriesStr) && $filterType != 'no' )
					{
						foreach( explode(',' , $categoriesStr) AS $termId )
						{
							if( is_numeric($termId) && $termId > 0 )
							{
								$categoriesFilter[] = (int)$termId;
							}
						}
					}
					else
					{
						$filterType = 'no';
					}

					if( $filterType == 'in' )
					{
						$checkFilter = false;
						foreach( $postCats AS $termInf )
						{
							if( in_array( $termInf->term_id , $categoriesFilter ) )
							{
								$checkFilter = true;
								break;
							}
						}

						if( !$checkFilter )
						{
							continue;
						}
					}
					else if( $filterType == 'ex' )
					{
						$checkFilter = true;
						foreach( $postCats AS $termInf )
						{
							if( in_array( $termInf->term_id , $categoriesFilter ) )
							{
								$checkFilter = false;
								break;
							}
						}

						if( !$checkFilter )
						{
							continue;
						}
					}
				}

				if( ( $driver == 'tumblr' || $driver == 'google_b' || $driver == 'telegram' ) && $nodeType == 'account' )
				{
					continue;
				}

				if( !( in_array( $nodeType , ['account' , 'ownpage' , 'page' , 'group' , 'event' , 'blog' , 'company' , 'community', 'subreddit', 'location', 'chat', 'board', 'publication'] ) && is_numeric($nodeId) && $nodeId > 0 ) )
				{
					continue;
				}



				$insertedCount++;

				if( !($driver == 'instagram' && get_option('fs_instagram_post_in_type', '1') == '2') )
				{
					$customMessage = isset($custom_messages[$driver]) && is_string($custom_messages[$driver]) ? $custom_messages[$driver] : null;

					if( $customMessage == get_option( 'fs_post_text_message_' . $driver , "{title}" ) )
					{
						$customMessage = null;
					}

					DB::DB()->insert( DB::table('feeds'), [
						'driver'                =>  $driver,
						'post_id'               =>  $postId,
						'node_type'             =>  $nodeType,
						'node_id'               =>  (int)$nodeId,
						'interval'              =>  $postInterval,
						'custom_post_message'   =>  $customMessage,
						'send_time'				=>	Helper::sendTime()
					]);
				}

				if( $driver == 'instagram' && (get_option('fs_instagram_post_in_type', '1') == '2' || get_option('fs_instagram_post_in_type', '1') == '3') )
				{
					$customMessage = isset($custom_messages[$driver . '_h']) && is_string($custom_messages[$driver . '_h']) ? $custom_messages[$driver . '_h'] : null;

					if( $customMessage == get_option( 'fs_post_text_message_' . $driver . '_h' , "{title}" ) )
					{
						$customMessage = null;
					}

					DB::DB()->insert( DB::table('feeds'), [
						'driver'                =>  $driver,
						'post_id'               =>  $postId,
						'node_type'             =>  $nodeType,
						'node_id'               =>  (int)$nodeId,
						'interval'              =>  $postInterval,
						'feed_type'             =>  'story',
						'custom_post_message'   =>  $customMessage,
						'send_time'				=>	Helper::sendTime()
					]);
				}
			}
		}

		if( !$insertedCount )
		{
			Helper::response(false, 'Not found active account or cammunity for shareing this post!');
		}

		if( $background )
		{
			CronJob::setbackgroundTask( $postId );
		}

		Helper::response(true);
	}


}