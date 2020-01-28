<?php

namespace FSPoster\App\Providers;

class CronJob
{

	private static $reScheduledList = [];

	/**
	 * @var BackgrouondProcess
	 */
	private static $backgroundProcess;

	public static function init()
	{
		if ( defined( 'DOING_CRON' ) )
		{
			set_time_limit(0);
		}

		update_option('fs_cron_job_runned_on', date('Y-m-d H:i:s'));

		add_action( 'fs_check_scheduled_posts' , [self::class , 'scheduledPost'] );
		add_action( 'fs_check_background_shared_posts' , [self::class , 'sendPostBackground'] );

		self::$backgroundProcess = new BackgrouondProcess();
	}

	public static function setScheduleTask( $scheduleId , $startTime )
	{
		wp_schedule_single_event( Helper::localTime2UTC( $startTime ) , 'fs_check_scheduled_posts' , [ $scheduleId ] );
	}

	public static function setbackgroundTask( $postId )
	{
		self::$backgroundProcess->data(['post_id' => $postId])->dispatch();
	}

	public static function clearSchedule($scheduleId)
	{
		wp_clear_scheduled_hook( 'fs_check_scheduled_posts' , [ $scheduleId ] );
	}

	public static function scheduledPost( $scheduleId )
	{
		set_time_limit(0);

		$scheduleInf = DB::fetch('schedules' , $scheduleId);

		if( !$scheduleInf || $scheduleInf['status'] != 'active' )
		{
			return false;
		}

		$userId = $scheduleInf['user_id'];

		$interval = (int)$scheduleInf['interval'];

		$nextScheduleTime = time() + $interval * 60;

		wp_schedule_single_event( $nextScheduleTime, 'fs_check_scheduled_posts' , [ $scheduleId ] );

		$currentTimestamp = current_time('timestamp');
		$nextScheduleLocalTime = $currentTimestamp + $interval * 60;

		DB::DB()->update(DB::table('schedules') , ['next_execute_time' => date('Y-m-d H:i', $nextScheduleLocalTime)] , ['id' => $scheduleId]);

		// check if is sleep time...
		$sleepTimeStart	= strtotime( $scheduleInf['sleep_time_start'] );
		$sleepTimeEnd	= strtotime( $scheduleInf['sleep_time_end'] );
		if( $currentTimestamp >= $sleepTimeStart && $currentTimestamp <= $sleepTimeEnd )
		{
			return;
		}

		$filterQuery = Helper::scheduleFilters( $scheduleInf );

		/* End post_sort */
		$getRandomPost = DB::DB()->get_row("SELECT * FROM ".DB::DB()->base_prefix."posts WHERE (post_status='publish' OR post_type='attachment') {$filterQuery} LIMIT 1" , ARRAY_A);
		$postId = $getRandomPost['ID'];

		if( !($postId > 0) )
		{
			wp_clear_scheduled_hook( 'fs_check_scheduled_posts' , [$scheduleId] );
			DB::DB()->update(DB::table('schedules') , ['status' => 'finished'] , ['id' => $scheduleId]);

			return;
		}

		if( !empty($scheduleInf['post_ids']) )
		{
			DB::DB()->query(DB::DB()->prepare("UPDATE ".DB::table('schedules')." SET post_ids=TRIM(BOTH ',' FROM replace(concat(',',post_ids,','), ',%d,',',')), status=IF( post_ids='' , 'finished', status) WHERE id=%d" , [$postId, $scheduleId]));
		}

		$accountsList	= explode(',', $scheduleInf['share_on_accounts']);
		if( !empty($scheduleInf['share_on_accounts']) && is_array( $accountsList ) && !empty( $accountsList ) && count( $accountsList ) > 0 )
		{
			$_accountsList	= [];
			$_nodeList		= [];

			foreach( $accountsList AS $accountN )
			{
				$accountN = explode(':', $accountN);

				if( $accountN[0] == 'account' )
				{
					$_accountsList[] = (int)$accountN[1];
				}
				else
				{
					$_nodeList[] = (int)$accountN[1];
				}
			}

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
			else
			{
				$getActiveAccounts = [];
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
			else
			{
				$getActiveNodes = [];
			}
		}
		else
		{
			$getActiveAccounts = DB::DB()->get_results(
				DB::DB()->prepare("
				SELECT tb2.*, filter_type, categories FROM ".DB::table('account_status')." tb1
				LEFT JOIN ".DB::table('accounts')." tb2 ON tb2.id=tb1.account_id
				WHERE tb1.user_id=%d" , [ $userId ])
				, ARRAY_A
			);

			$getActiveNodes = DB::DB()->get_results(
				DB::DB()->prepare("
				SELECT tb2.*, filter_type, categories FROM ".DB::table('account_node_status')." tb1
				LEFT JOIN ".DB::table('account_nodes')." tb2 ON tb2.id=tb1.node_id
				WHERE tb1.user_id=%d" , [ $userId ])
				, ARRAY_A
			);
		}

		$customPostMessages = json_decode($scheduleInf['custom_post_message'] , true);
		$customPostMessages = is_array($customPostMessages) ? $customPostMessages : [];

		$feedsArr = [];

		$postInterval = 1;

		$postCats = Helper::getPostCatsArr( $postId );

		foreach( $getActiveAccounts AS $accountInf )
		{
			if( $postCats !== false )
			{
				$filterType = $accountInf['filter_type'];
				$categoriesFilter = explode(',' , $accountInf['categories']);

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

			$insertData = [
				'driver'        =>  $accountInf['driver'],
				'post_id'       =>  $postId,
				'node_type'     =>  'account',
				'node_id'       =>  (int)$accountInf['id'],
				'interval'      =>  $postInterval,
				'schedule_id'   =>  $scheduleId,
				'is_sended'		=>	2
			];

			if( !($accountInf['driver'] == 'instagram' && get_option('fs_instagram_post_in_type', '1') == '2') )
			{
				if( isset($customPostMessages[ $accountInf['driver'] ]) )
				{
					$insertData['custom_post_message'] = (string)$customPostMessages[ $accountInf['driver'] ];
				}

				if( DB::DB()->insert( DB::table('feeds'), $insertData) )
				{
					$feedsArr[DB::DB()->insert_id] = true;
				}
			}

			if( $accountInf['driver'] == 'instagram' && (get_option('fs_instagram_post_in_type', '1') == '2' || get_option('fs_instagram_post_in_type', '1') == '3') )
			{
				if( isset($customPostMessages[ $accountInf['driver'] . '_h' ]) )
				{
					$insertData['custom_post_message'] = (string)$customPostMessages[ $accountInf['driver'] . '_h' ];
				}

				$insertData['feed_type'] = 'story';

				if( DB::DB()->insert( DB::table('feeds'), $insertData) )
				{
					$feedsArr[DB::DB()->insert_id] = true;
				}
			}
		}

		foreach( $getActiveNodes AS $nodeInf )
		{
			$filterType = $nodeInf['filter_type'];
			$categoriesFilter = explode(',' , $nodeInf['categories']);

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

			$insertData = [
				'driver'        =>  $nodeInf['driver'],
				'post_id'       =>  $postId,
				'node_type'     =>  $nodeInf['node_type'],
				'node_id'       =>  (int)$nodeInf['id'],
				'interval'      =>  $postInterval,
				'schedule_id'   =>  $scheduleId,
				'is_sended'		=>	2
			];

			if( isset($customPostMessages[ $nodeInf['driver'] ]) )
			{
				$insertData['custom_post_message'] = (string)$customPostMessages[ $nodeInf['driver'] ];
			}

			if( DB::DB()->insert( DB::table('feeds'), $insertData) )
			{
				$feedsArr[DB::DB()->insert_id] = true;
			}
		}

		foreach ($feedsArr AS $feedId => $true)
		{
			SocialNetworkPost::post( $feedId );
			sleep($postInterval);
		}
	}

	public static function sendPostBackground( $postId )
	{
		set_time_limit(0);

		$getFeeds = DB::fetchAll('feeds' , ['post_id' => $postId , 'is_sended' => '0']);

		// for preventing dublicat shares...
		DB::DB()->update(DB::table('feeds') , ['is_sended' => '2'] , ['post_id' => $postId , 'is_sended' => '0']);

		$fs_post_interval_type = (int)get_option('fs_post_interval_type' , '1');

		$collectDrivers = [];
		foreach ( $getFeeds AS $feedInf )
		{
			$hasInterval = is_numeric($feedInf['interval']) && $feedInf['interval'] > 0;
			$hasPostedSameSocialNetwork = isset( $collectDrivers[ $feedInf['driver'] ] );

			if( $hasInterval && $fs_post_interval_type == 1 && $hasPostedSameSocialNetwork )
			{
				self::reSchedulePost( $postId, $feedInf['interval'] );
				// change status for next schedule...
				DB::DB()->update(DB::table('feeds') , ['is_sended' => '0'] , ['id' => $feedInf['id']]);
				continue;
			}

			if( $hasInterval && $fs_post_interval_type == 0 && !empty( $collectDrivers ) )
			{
				self::reSchedulePost( $postId, $feedInf['interval'] );
				// change status for next schedule...
				DB::DB()->update(DB::table('feeds') , ['is_sended' => '0'] , ['post_id' => $postId , 'is_sended' => '2']);
				break;
			}

			SocialNetworkPost::post( $feedInf['id'] );
			$collectDrivers[ $feedInf['driver'] ] = true;
		}
	}

	private static function reSchedulePost( $postId, $interval )
	{
		if( isset( self::$reScheduledList[ $postId ] ) )
			return;

		wp_schedule_single_event( time() + (int)$interval ,  'fs_check_background_shared_posts' , [ $postId ] );

		// prevent dubpicate scheduling...
		self::$reScheduledList[ $postId ] = true;
	}

}
