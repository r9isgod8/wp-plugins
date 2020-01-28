<?php

namespace FSPoster\App\Providers;

class ShareService
{

	public static function insertFeeds( $wpPostId, $nodesList, $customMessages, $categoryFilter = true, $schedule_date = null, $shareOnBackground = null, $scheduleId = null, $disableStartInterval = false )
	{
		/**
		 * Accounts, communications list array
		 */
		$nodesList      = is_array( $nodesList ) ? $nodesList : [];

		/**
		 * Filter accounts, communications by post categories
		 */
		$postCats       = $categoryFilter ? Helper::getPostCatsArr( $wpPostId ) : [];

		/**
		 * Instagram, share on:
		 *  - 1: Profile only
		 *  - 2: Story only
		 *  - 3: Profile and Story
		 */
		$igPostType     = get_option('fs_instagram_post_in_type', '1');

		/**
		 * Interval for each publication (sec.)
		 */
		$postInterval           = (int)get_option('fs_post_interval' , '0');
		$postIntervalType       = (int)get_option('fs_post_interval_type', '1');
		$sendDateTime           = Date::dateTimeSQL( is_null( $schedule_date ) ? 'now' : $schedule_date );
		$intervalForNetworks    = [];

		/**
		 * Time interval before start
		 */
		if( !$disableStartInterval )
		{
			$timer = (int)get_option('fs_share_timer', '0');

			if( $timer > 0 )
			{
				$sendDateTime = Date::dateTimeSQL( $sendDateTime, '+' . $timer . ' minutes' );
			}
		}

		$feedsCount = 0;

		if( is_null( $shareOnBackground ) )
		{
			$shareOnBackground = (int)get_option('fs_share_on_background' , '1');
		}

		foreach( $nodesList AS $nodeId )
		{
			if( is_string($nodeId) && strpos( $nodeId , ':' ) !== false )
			{
				$parse = explode(':' , $nodeId);
				$driver = $parse[0];
				$nodeType = $parse[1];
				$nodeId = $parse[2];
				$filterType = isset($parse[3]) ? $parse[3] : 'no';
				$categoriesStr = isset($parse[4]) ? $parse[4] : '';

				if( $categoryFilter ) // manual share panel...
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

				if( $nodeType == 'account' && in_array( $driver, ['tumblr', 'google_b', 'telegram'] ) )
				{
					continue;
				}

				if( !( in_array( $nodeType , ['account' , 'ownpage' , 'page' , 'group' , 'event' , 'blog' , 'company' , 'community', 'subreddit', 'location', 'chat', 'board', 'publication'] ) && is_numeric($nodeId) && $nodeId > 0 ) )
				{
					continue;
				}

				if( $postInterval > 0 )
				{
					$driver2ForArr = $postIntervalType == 1 ? $driver : 'all';
					$dataSendTime = isset( $intervalForNetworks[ $driver2ForArr ] ) ? $intervalForNetworks[ $driver2ForArr ] : $sendDateTime;
				}
				else
				{
					$dataSendTime = $sendDateTime;
				}

				if( !($driver == 'instagram' && $igPostType == '2') )
				{
					$customMessage = isset($customMessages[$driver]) ? $customMessages[$driver] : null;

					if( $customMessage == get_option( 'fs_post_text_message_' . $driver , "{title}" ) )
					{
						$customMessage = null;
					}

					DB::DB()->insert( DB::table('feeds'), [
						'driver'                =>  $driver,
						'post_id'               =>  $wpPostId,
						'node_type'             =>  $nodeType,
						'node_id'               =>  (int)$nodeId,
						'interval'              =>  $postInterval,
						'custom_post_message'   =>  $customMessage,
						'send_time'				=>	$dataSendTime,
						'share_on_background'	=>	$shareOnBackground ? 1 : 0,
						'schedule_id'           =>  $scheduleId
					]);

					$feedsCount++;
				}

				if( $driver == 'instagram' && ($igPostType == '2' || $igPostType == '3') )
				{
					$customMessage = isset($customMessages[$driver . '_h']) ? $customMessages[$driver . '_h'] : null;

					if( $customMessage == get_option( 'fs_post_text_message_' . $driver . '_h' , "{title}" ) )
					{
						$customMessage = null;
					}

					DB::DB()->insert( DB::table('feeds'), [
						'driver'                =>  $driver,
						'post_id'               =>  $wpPostId,
						'node_type'             =>  $nodeType,
						'node_id'               =>  (int)$nodeId,
						'interval'              =>  $postInterval,
						'feed_type'             =>  'story',
						'custom_post_message'   =>  $customMessage,
						'send_time'				=>	$dataSendTime,
						'share_on_background'	=>	$shareOnBackground ? 1 : 0,
						'schedule_id'           =>  $scheduleId
					]);

					$feedsCount++;
				}

				if( $postInterval > 0 )
				{
					$intervalForNetworks[ $driver2ForArr ] = Date::dateTimeSQL( $dataSendTime, '+' . $postInterval . ' second' );
				}
			}
		}

		return $feedsCount;
	}

	public static function shareQueuedFeeds()
	{
		$nowDateTime = Date::dateTimeSQL();

		$getFeeds = DB::DB()->prepare('SELECT * FROM `'.DB::table('feeds').'` WHERE `share_on_background`=1 and `is_sended`=0 and `send_time`<=%s', [ $nowDateTime ]);
		$getFeeds = DB::DB()->get_results( $getFeeds, ARRAY_A );

		// for preventing dublicat shares...
		$preventDublicates = DB::DB()->prepare('UPDATE `'.DB::table('feeds').'` SET `is_sended`=2 WHERE `share_on_background`=1 and `is_sended`=0 and `send_time`<=%s', [ $nowDateTime ]);
		DB::DB()->query( $preventDublicates );

		foreach ( $getFeeds AS $feedInf )
		{
			SocialNetworkPost::post( $feedInf['id'], $feedInf );
		}
	}

	public static function postSaveEvent( $new_status, $old_status, $post )
	{
		global $wp_version;

		if( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE )
			return;

		/**
		 * Gutenberg bug...
		 * https://github.com/WordPress/gutenberg/issues/15094
		 */
		if( version_compare( $wp_version, '5.0', '>=' ) && isset($_GET['_locale']) && $_GET['_locale'] == 'user' && empty($_POST) )
			return;

		$metaBoxLoader = (int)Request::get('meta-box-loader', 0, 'num', ['1']);
		$original_post_old_status = Request::post('original_post_status', '', 'string');

		if( $metaBoxLoader === 1 && !empty( $original_post_old_status ) )
			$old_status = $original_post_old_status;

		if( !in_array( $new_status, ['publish', 'future', 'draft'] ) )
			return;

		if( $old_status == 'publish' )
			return;

		$post_id	= $post->ID;

		if( $new_status == 'publish' && $old_status == 'future' )
		{
			delete_post_meta( $post_id, '_fs_poster_schedule_datetime' );
			return;
		}

		if( $new_status == 'future' && $old_status == 'future' )
		{
			$oldScheduleDate = get_post_meta( $post_id, '_fs_poster_schedule_datetime', true );
			$diff = (int)(( strtotime( $post->post_date ) - strtotime( $oldScheduleDate ) ) / 60);

			if( $diff != 0 && abs( $diff ) < 60 * 24 * 30 )
			{
				DB::DB()->query('UPDATE `'.DB::table('feeds').'` SET `send_time`=ADDDATE(`send_time`,INTERVAL '.$diff.' MINUTE) WHERE is_sended=0 and post_id=\''.(int)$post_id.'\'');
			}

			delete_post_meta( $post_id, '_fs_poster_schedule_datetime' );
			add_post_meta( $post_id, '_fs_poster_schedule_datetime', $post->post_date, true );
			return;
		}

		// if not allowed post type...
		if( !in_array( $post->post_type , explode( '|' , get_option('fs_allowed_post_types' , 'post|page|attachment|product') ) ) )
			return;

		$userId		= $post->post_author;

		$post_text_message = [];

		$post_text_message['fb']			= Request::post('fs_post_text_message_fb' , '' , 'string');
		$post_text_message['twitter']		= Request::post('fs_post_text_message_twitter' , '' , 'string');
		$post_text_message['instagram']		= Request::post('fs_post_text_message_instagram' , '' , 'string');
		$post_text_message['instagram_h']	= Request::post('fs_post_text_message_instagram_h' , '' , 'string');
		$post_text_message['linkedin']		= Request::post('fs_post_text_message_linkedin' , '' , 'string');
		$post_text_message['vk']			= Request::post('fs_post_text_message_vk' , '' , 'string');
		$post_text_message['pinterest']		= Request::post('fs_post_text_message_pinterest' , '' , 'string');
		$post_text_message['reddit']		= Request::post('fs_post_text_message_reddit' , '' , 'string');
		$post_text_message['tumblr']		= Request::post('fs_post_text_message_tumblr' , '' , 'string');
		$post_text_message['ok']			= Request::post('fs_post_text_message_ok' , '' , 'string');
		$post_text_message['google_b']		= Request::post('fs_post_text_message_google_b' , '' , 'string');
		$post_text_message['telegram']		= Request::post('fs_post_text_message_telegram' , '' , 'string');
		$post_text_message['medium']		= Request::post('fs_post_text_message_medium' , '' , 'string');

		if( $old_status == 'draft' )
		{
			delete_post_meta( $post_id, '_fs_poster_share' );
			delete_post_meta( $post_id, '_fs_poster_node_list' );

			foreach ( $post_text_message AS $dr => $cmtxt )
			{
				delete_post_meta( $post_id, '_fs_poster_cm_' . $dr );
			}
		}

		// if not checked the 'Share' checkbox exit the function
		$share_checked_inpt = Request::post('share_checked' , null, 'string' , ['on' , 'off']);

		if( is_null( $share_checked_inpt ) )
			$share_checked_inpt = get_option('fs_auto_share_new_posts', '1') ? 'on' : 'off';

		if( $share_checked_inpt !== 'on' )
		{
			if( $new_status == 'draft' )
				add_post_meta( $post_id, '_fs_poster_share', 0, true );

			DB::DB()->delete(DB::table('feeds') , [
				'post_id'       =>  $post_id,
				'is_sended'     =>  '0'
			]);

			return;
		}

		// run share process on background
		if( $new_status == 'future' )
		{
			$backgroundShare = 1;

			add_post_meta( $post_id, '_fs_poster_schedule_datetime', $post->post_date, true );
		}
		else
		{
			$backgroundShare = (int)get_option('fs_share_on_background' , '1');
		}

		// social networks lists
		$nodesList = Request::post('share_on_nodes' , false , 'array' );

		// If from XMLRPC: load all active nodes
		if( $nodesList === false && $new_status != 'draft' && !isset( $_POST['share_checked'] ) )
		{
			$nodesList = [];

			$accounts = DB::DB()->get_results(
				DB::DB()->prepare("
					SELECT tb2.id, tb2.driver, tb1.filter_type, tb1.categories, 'account' AS node_type FROM ".DB::table('account_status')." tb1
					LEFT JOIN ".DB::table('accounts')." tb2 ON tb2.id=tb1.account_id
					WHERE tb1.user_id=%d" , [ $userId ])
				, ARRAY_A
			);

			$activeNodes = DB::DB()->get_results(
				DB::DB()->prepare("
					SELECT tb2.id, tb2.driver, tb2.node_type, tb1.filter_type, tb1.categories FROM ".DB::table('account_node_status')." tb1
					LEFT JOIN ".DB::table('account_nodes')." tb2 ON tb2.id=tb1.node_id
					WHERE tb1.user_id=%d" , [ $userId ])
				, ARRAY_A
			);

			$activeNodes = array_merge($accounts , $activeNodes);

			foreach ($activeNodes AS $nodeInf)
			{
				$nodesList[] = $nodeInf['driver'].':'.$nodeInf['node_type'].':'.$nodeInf['id'].':'.htmlspecialchars($nodeInf['filter_type']).':'.htmlspecialchars($nodeInf['categories']);
			}
		}

		if( $new_status == 'draft' )
		{
			add_post_meta( $post_id, '_fs_poster_share', 1, true );
			add_post_meta( $post_id, '_fs_poster_node_list', $nodesList, true );

			foreach ( $post_text_message AS $dr => $cmtxt )
			{
				add_post_meta( $post_id, '_fs_poster_cm_' . $dr , $cmtxt, true );
			}

			return;
		}

		// Insert queued posts in feeds table
		self::insertFeeds( $post_id, $nodesList, $post_text_message, true, ( $new_status == 'future' ? $post->post_date : null ), $backgroundShare );

		// if not scheduled post then add arguments end of url
		if( $new_status == 'publish' )
		{
			add_filter('redirect_post_location', function($location) use( $backgroundShare )
			{
				return $location . '&share=1&background=' . $backgroundShare;
			});
		}
	}

	public static function deletePostFeeds( $postId )
	{
		DB::DB()->delete( DB::table('feeds'), [
			'post_id'   =>  $postId,
			'is_sended' =>  0
		]);
	}

	public static function shareSchedules()
	{
		$nowDateTime = Date::dateTimeSQL();

		$getSchdules = DB::DB()->prepare('SELECT * FROM `'.DB::table('schedules').'` WHERE `status`=\'active\' and `next_execute_time`<=%s', [ $nowDateTime ]);
		$getSchdules = DB::DB()->get_results( $getSchdules, ARRAY_A );

		// for preventing dublicat shares...
		$preventDublicates = DB::DB()->prepare('UPDATE `'.DB::table('schedules').'` SET `next_execute_time`=DATE_ADD(\'%s\', INTERVAL `interval` MINUTE) WHERE `status`=\'active\' and `next_execute_time`<=%s', [ $nowDateTime, $nowDateTime ]);
		DB::DB()->query( $preventDublicates );

		$result = false;
		foreach ( $getSchdules AS $scheduleInf )
		{
			if( self::scheduledPost( $scheduleInf ) )
			{
				$result = true;
			}
		}

		if( $result )
		{
			self::shareQueuedFeeds();
		}
	}

	public static function scheduledPost( $scheduleInf )
	{
		$scheduleId = $scheduleInf['id'];
		$userId     = $scheduleInf['user_id'];
		$interval   = (int)$scheduleInf['interval'];

		// check if is sleep time...
		if( !empty( $scheduleInf['sleep_time_start'] ) && !empty( $scheduleInf['sleep_time_end'] ) )
		{
			$currentTimestamp   = Date::epoch( Date::timeSQL() );

			$sleepTimeStart	    = Date::epoch( $scheduleInf['sleep_time_start'] );
			$sleepTimeEnd	    = Date::epoch( $scheduleInf['sleep_time_end'] );

			if( $currentTimestamp >= $sleepTimeStart && $currentTimestamp <= $sleepTimeEnd )
			{
				return;
			}
		}

		$filterQuery = Helper::scheduleFilters( $scheduleInf );

		/* End post_sort */
		$getRandomPost = DB::DB()->get_row("SELECT * FROM ".DB::DB()->base_prefix."posts WHERE (post_status='publish' OR post_type='attachment') {$filterQuery} LIMIT 1" , ARRAY_A);
		$postId = $getRandomPost['ID'];

		if( !($postId > 0) )
		{
			DB::DB()->update(DB::table('schedules') , ['status' => 'finished'] , ['id' => $scheduleId]);
			return;
		}

		if( !empty($scheduleInf['post_ids']) )
		{
			DB::DB()->query(DB::DB()->prepare("UPDATE `".DB::table('schedules')."` SET `post_ids`=TRIM(BOTH ',' FROM replace(concat(',',`post_ids`,','), ',%d,',',')), status=IF( `post_ids`='' , 'finished', `status`) WHERE `id`=%d" , [$postId, $scheduleId]));
		}

		$accountsList	= explode(',', $scheduleInf['share_on_accounts']);
		if( !empty($scheduleInf['share_on_accounts']) && is_array( $accountsList ) && !empty( $accountsList ) && count( $accountsList ) > 0 )
		{
			$_accountsList	= [];
			$_nodeList		= [];

			foreach( $accountsList AS $accountN )
			{
				$accountN = explode(':', $accountN);

				if( !isset( $accountN[1] ) )
					continue;

				if( $accountN[0] == 'account' )
				{
					$_accountsList[] = (int)$accountN[1];
				}
				else
				{
					$_nodeList[] = (int)$accountN[1];
				}
			}

			$getActiveAccounts = [];
			$getActiveNodes = [];

			if( !empty($_accountsList) )
			{
				$getActiveAccounts = DB::DB()->get_results(
					DB::DB()->prepare("
						SELECT tb1.*, IFNULL(filter_type,'no') AS filter_type, categories
						FROM ".DB::table('accounts')." tb1
						LEFT JOIN ".DB::table('account_status')." tb2 ON tb1.id=tb2.account_id AND tb2.user_id=%d
						WHERE (tb1.is_public=1 OR tb1.user_id=%d) AND tb1.id in (".implode(',', $_accountsList).")" , [ $userId, $userId ])
					, ARRAY_A
				);
			}

			if( !empty($_nodeList) )
			{
				$getActiveNodes = DB::DB()->get_results(
					DB::DB()->prepare("
						SELECT tb1.*, IFNULL(filter_type,'no') AS filter_type, categories
						FROM ".DB::table('account_nodes')." tb1
						LEFT JOIN ".DB::table('account_node_status')." tb2 ON tb1.id=tb2.node_id AND tb2.user_id=%d
						WHERE (tb1.is_public=1 OR tb1.user_id=%d) AND tb1.id in (".implode(',', $_nodeList).")" , [ $userId, $userId ])
					, ARRAY_A
				);
			}
		}

		$customPostMessages = json_decode($scheduleInf['custom_post_message'] , true);
		$customPostMessages = is_array($customPostMessages) ? $customPostMessages : [];
		$nodesList          = [];

		foreach( $getActiveAccounts AS $accountInf )
		{
			$nodesList[] = $accountInf['driver'] . ':account:' . (int)$accountInf['id'] . ':' . $accountInf['filter_type'] . ':' . $accountInf['categories'];
		}

		foreach( $getActiveNodes AS $nodeInf )
		{
			$nodesList[] = $nodeInf['driver'] . ':'.$nodeInf['node_type'].':' . (int)$nodeInf['id'] . ':' . $nodeInf['filter_type'] . ':' . $nodeInf['categories'];
		}

		if( !empty( $nodesList ) )
		{
			self::insertFeeds($postId, $nodesList, $customPostMessages, true, null, 1, $scheduleId, true);

			return true;
		}

		return false;
	}

}