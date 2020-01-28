<?php

namespace FSPoster\App\Controller\Ajax;

use FSPoster\App\Providers\CronJob;
use FSPoster\App\Providers\DB;
use FSPoster\App\Providers\Helper;
use FSPoster\App\Providers\Request;

trait Schedule
{

	public function schedule_save()
	{
		$title				= Request::post('title' , '' , 'string');
		$start_date			= Request::post('start_date' , '' , 'string');
		$start_time			= Request::post('start_time' , '' , 'string');
		$interval 			= Request::post('interval' , '0' , 'num');
		$share_time 		= Request::post('share_time' , '' , 'string');

		$post_type_filter	= Request::post('post_type_filter' , '' , 'string');
		$category_filter	= Request::post('category_filter' , '0' , 'int');
		$post_sort			= Request::post('post_sort' , 'random' , 'string' , ['random', 'random2' , 'old_first' , 'new_first']);
		$post_date_filter	= Request::post('post_date_filter' , 'all' , 'string' , ['all' , 'this_week' , 'previously_week' , 'this_month' , 'previously_month' , 'this_year' , 'last_30_days' , 'last_60_days']);

		$custom_messages	= Request::post('custom_messages' , '' , 'string');
		$accounts_list		= Request::post('accounts_list' , '' , 'string');

		$sleep_time_start	= Request::post('sleep_time_start' , '' , 'string');
		$sleep_time_end		= Request::post('sleep_time_end' , '' , 'string');

		if( empty($sleep_time_start) || empty($sleep_time_end) )
		{
			$sleep_time_start = null;
			$sleep_time_end = null;
		}
		else
		{
			$sleep_time_start = date('H:i', strtotime( $sleep_time_start ));
			$sleep_time_end = date('H:i', strtotime( $sleep_time_end ));
		}

		$_custom_messages = [];
		if( !empty( $custom_messages ) )
		{
			$custom_messages = json_decode($custom_messages, true);
			$custom_messages = is_array($custom_messages) ? $custom_messages : [];

			foreach ($custom_messages AS $socialNetwork => $message1 )
			{
				if( in_array( $socialNetwork , ['fb', 'instagram', 'instagram_h', 'linkedin', 'twitter', 'pinterest', 'vk', 'ok', 'tumblr', 'reddit', 'google_b', 'telegram', 'medium'] ) && is_string( $message1 ) )
				{
					$_custom_messages[ $socialNetwork ] = $message1;
				}
			}
		}
		$_custom_messages = empty( $_custom_messages ) ? null : json_encode( $_custom_messages );

		$_accounts_list = [];
		if( !empty( $accounts_list ) )
		{
			$accounts_list = json_decode($accounts_list, true);
			$accounts_list = is_array($accounts_list) ? $accounts_list : [];

			foreach ($accounts_list AS $socialAccount )
			{
				if( is_string( $socialAccount ) )
				{
					$socialAccount = explode(':' , $socialAccount);
					if( !(count($socialAccount) == 2 && is_numeric($socialAccount[1])) )
						continue;

					$_accounts_list[] = ($socialAccount[0] == 'account' ? 'account' : 'node') . ':' . $socialAccount[1] ;
				}
			}
		}
		$_accounts_list = empty($_accounts_list) ? null : implode(',' , $_accounts_list);

		// sanitize post types array...
		$allowedPostTypes = explode('|', get_option('fs_allowed_post_types', ''));

		if( !in_array( $post_type_filter , $allowedPostTypes ) )
		{
			$post_type_filter = '';
		}

		if( empty($title) || empty($start_date) || empty($start_time) || !( is_numeric($interval) && $interval > 0 ) )
		{
			Helper::response(false , ['error_msg' => esc_html__('Validation error' , 'fs-poster')]);
		}

		$start_date = date('Y-m-d' , strtotime($start_date));
		$start_time = date('H:i' , strtotime($start_time));

		$cronStartTime = $start_date . ' ' . $start_time;

		DB::DB()->insert(DB::table('schedules') , [
			'title'					=>	$title,
			'start_date'			=>	$start_date,
			'interval'				=>	$interval,
			'status'				=>	'active',
			'insert_date'	 		=>	date('Y-m-d H:i:s'),
			'user_id'				=>	get_current_user_id(),
			'share_time'			=>	$start_time,
			'next_execute_time'		=>	$cronStartTime,

			'post_type_filter'		=>	$post_type_filter,
			'category_filter'		=>	$category_filter > 0 ? $category_filter : null,
			'post_sort'				=>	$post_sort,
			'post_date_filter'		=>	$post_date_filter,

			'custom_post_message'	=>	$_custom_messages,
			'share_on_accounts'		=>	$_accounts_list,

			'sleep_time_start'		=>	$sleep_time_start,
			'sleep_time_end'		=>	$sleep_time_end
		]);

		CronJob::setScheduleTask( DB::DB()->insert_id , $cronStartTime );

		Helper::response(true);
	}

	public function schedule_posts()
	{
		$plan_date			= Request::post('plan_date' , '' , 'string');
		$post_ids_p			= Request::post('post_ids', [], 'array');
		$interval			= Request::post('interval' , '0' , 'num');

		if( !( $interval > 0 ) )
		{
			Helper::response(false , esc_html__('Validation error' , 'fs-poster'));
		}

		if( empty($plan_date) )
		{
			Helper::response(false , 'Schedule date is empty!');
		}
		else if( strtotime($plan_date) - (3600 * 24 * 30 * 24) > time() )
		{
			Helper::response(false , 'Plan date or time is not valid!');
		}
		else if( strtotime($plan_date) < current_time('timestamp') )
		{
			Helper::response(false , 'Plan date or time is not valid!<br>Please select Schedule date/time according to your server time. <br>Your server time is: ' . current_time('Y-m-d g:i:s A'));
		}

		$plan_date = date('Y-m-d H:i' , strtotime($plan_date));

		$post_ids = [];

		foreach( $post_ids_p AS $postId )
		{
			if( is_numeric($postId) && $postId > 0 )
			{
				$post_ids[] = (int)$postId;
			}
		}

		if( empty($post_ids) )
		{
			Helper::response(false , 'Please select at least one post.');
		}
		else if( count( $post_ids ) > 200 )
		{
			Helper::response(false , 'Too many post selected! You can select maximum 200 posts!');
		}

		$custom_messages = Request::post('custom_messages' , '' , 'string');
		$accounts_list = Request::post('accounts_list' , '' , 'string');

		$_custom_messages = [];
		if( !empty( $custom_messages ) )
		{
			$custom_messages = json_decode($custom_messages, true);
			$custom_messages = is_array($custom_messages) ? $custom_messages : [];

			foreach ($custom_messages AS $socialNetwork => $message1 )
			{
				if( in_array( $socialNetwork , ['fb', 'instagram', 'instagram_h', 'linkedin', 'twitter', 'pinterest', 'vk', 'ok', 'tumblr', 'reddit', 'google_b', 'telegram', 'medium'] ) && is_string( $message1 ) )
				{
					$_custom_messages[$socialNetwork] = $message1;
				}
			}
		}
		$_custom_messages = empty($_custom_messages) ? null : json_encode($_custom_messages);

		$_accounts_list = [];
		if( !empty( $accounts_list ) )
		{
			$accounts_list = json_decode($accounts_list, true);
			$accounts_list = is_array($accounts_list) ? $accounts_list : [];

			foreach ($accounts_list AS $socialAccount )
			{
				if( is_string( $socialAccount ) )
				{
					$socialAccount = explode(':' , $socialAccount);
					if( !(count($socialAccount) == 2 && is_numeric($socialAccount[1])) )
						continue;

					$_accounts_list[] = ($socialAccount[0] == 'account' ? 'account' : 'node') . ':' . $socialAccount[1] ;
				}
			}
		}
		$_accounts_list = empty($_accounts_list) ? null : implode(',' , $_accounts_list);

		$postsCount = count($post_ids);

		if( $postsCount == 1 )
		{
			$onePostId = reset($post_ids);
			$onePostInf = get_post( $onePostId, ARRAY_A );

			$title = 'Scheduled post: "' . Helper::cutText( !empty($onePostInf['post_title']) ? $onePostInf['post_title'] : $onePostInf['post_content'] ) . '"';
		}
		else
		{
			$title = 'Schedule ( '.$postsCount.' posts )';
		}

		$post_ids = implode(',' , $post_ids);

		$start_date = date('Y-m-d', strtotime($plan_date));
		$end_date = date('Y-m-d', (strtotime($plan_date) + ($postsCount - 1) * $interval * 60 ));
		$share_time = date('H:i' , strtotime($plan_date));

		$post_type_filter = '';
		$category_filter = '';
		$post_sort = $postsCount == 1 ? 'new_first' : Request::post('post_sort' , 'old_first' , 'string', ['old_first' , 'random' , 'new_first']);
		$post_date_filter = 'all';

		DB::DB()->insert(DB::table('schedules') , [
			'title'					=>	$title,
			'start_date'			=>	$start_date,
			'end_date'				=>	$end_date,
			'interval'				=>	$interval,
			'status'				=>	'active',
			'insert_date'	 		=>	date('Y-m-d H:i:s'),
			'user_id'				=>	get_current_user_id(),
			'share_time'			=>	$share_time,

			'post_type_filter'		=>	$post_type_filter,
			'category_filter'		=>	$category_filter,
			'post_sort'				=>	$post_sort,
			'post_date_filter'		=>	$post_date_filter,

			'post_ids'				=>	$post_ids,
			'next_execute_time'		=>	$plan_date,

			'custom_post_message'	=>	$_custom_messages,
			'share_on_accounts'		=>	$_accounts_list
		]);

		CronJob::setScheduleTask( DB::DB()->insert_id , $plan_date );

		Helper::response(true);
	}

	public function delete_schedule()
	{
		$id = Request::post('id' , 0 , 'num');
		if( $id <= 0 )
		{
			Helper::response(false);
		}

		$checkSchedule = DB::fetch('schedules' , $id);
		if( !$checkSchedule )
		{
			Helper::response(false , esc_html__('Schedule not found!' , 'fs-poster'));
		}
		else if( $checkSchedule['user_id'] != get_current_user_id() )
		{
			Helper::response(false , esc_html__('You do not have a permission to delete this schedule!' , 'fs-poster'));
		}

		DB::DB()->delete(DB::table('schedules') , ['id' => $id]);

		CronJob::clearSchedule($id);

		Helper::response(true);
	}

	public function delete_schedules()
	{
		$ids = Request::post('ids' , [] , 'array');
		if( count($ids) == 0 )
		{
			Helper::response(false , 'No schedule selected!');
		}

		foreach ($ids AS $id)
		{
			if( is_numeric($id) && $id > 0 )
			{
				$checkSchedule = DB::fetch('schedules' , $id);
				if( !$checkSchedule )
				{
					Helper::response(false , esc_html__('Schedule not found!' , 'fs-poster'));
				}

				else if( $checkSchedule['user_id'] != get_current_user_id() )
				{
					Helper::response(false , esc_html__('You do not have a permission to delete this schedule!' , 'fs-poster'));
				}

				DB::DB()->delete(DB::table('schedules') , ['id' => $id]);

				CronJob::clearSchedule($id);
			}
		}

		Helper::response(true);
	}

	public function schedule_change_status()
	{
		$id = Request::post('id' , 0 , 'num');

		if( $id <= 0 )
		{
			Helper::response(false);
		}

		$checkSchedule = DB::fetch('schedules' , $id);
		if( !$checkSchedule )
		{
			Helper::response(false , esc_html__('Schedule not found!' , 'fs-poster'));
		}
		else if( $checkSchedule['user_id'] != get_current_user_id() )
		{
			Helper::response(false , esc_html__('You do not have a permission to Pause/Play this schedule!' , 'fs-poster'));
		}

		if( $checkSchedule['status'] != 'paused' && $checkSchedule['status'] != 'active' )
		{
			Helper::response(false , esc_html__('This schedule has finished!' , 'fs-poster'));
		}

		$newStatus = $checkSchedule['status'] == 'active' ? 'paused' : 'active';

		$updateArr = ['status' => $newStatus];

		if( $newStatus == 'paused' )
		{
			wp_clear_scheduled_hook( 'fs_check_scheduled_posts' , [ $id ] );
		}
		else
		{
			$locTime = current_time('timestamp');
			$scheduleStarted = strtotime( $checkSchedule['start_date'] . ' ' . $checkSchedule['share_time'] );

			$dif = $locTime - $scheduleStarted;

			$interval = $checkSchedule['interval'] * 60;

			$nextExecTime = ( $dif % $interval ) === 0 ? $locTime : $locTime + $interval - ( $dif % $interval );

			$updateArr['next_execute_time'] = date('Y-m-d H:i', $nextExecTime);

			CronJob::setScheduleTask( $id , $updateArr['next_execute_time'] );
		}

		DB::DB()->update(DB::table('schedules') , $updateArr , ['id' => $id]);

		Helper::response(true );
	}

	public function schedule_get_calendar()
	{
		$month = (int)Request::post('month' , date('m') , 'num', [1,2,3,4,5,6,7,8,9,10,11,12]);
		$year = (int)Request::post('year' , date('Y') , 'num');

		if( $year > date('Y')+4 || $year < date('Y')-4 )
		{
			Helper::response(false, 'Loooooooooooooooolll :)');
		}

		$firstDate = date('Y-m-01' , strtotime("{$year}-{$month}-01"));
		$lastDate = date('Y-m-t' , strtotime("{$year}-{$month}-01"));
		$myId = (int)get_current_user_id();

		if( strtotime( $firstDate ) < strtotime(date('Y-m-d')) )
		{
			$firstDate = date('Y-m-d');
		}

		$getPlannedDays = DB::DB()->get_results("SELECT * FROM `".DB::table('schedules')."` WHERE `start_date`<='$lastDate' AND `status`='active' AND user_id='$myId'", ARRAY_A);

		$days = [];

		foreach( $getPlannedDays AS $planInf )
		{
			$scheduleId = (int)$planInf['id'];
			$planStart = strtotime($planInf['start_date']);
			$planEnd = strtotime($lastDate);
			$interval = (int)$planInf['interval']>0 ? (int)$planInf['interval'] : 1;

			$postCount = empty($planInf['post_ids']) ? -1 : count( explode(',', $planInf['post_ids']) );

			if( $planStart < strtotime($firstDate) )
			{
				$planStart = strtotime($firstDate);
			}

			if( $planInf['post_sort'] != 'random' && $planInf['post_sort'] != 'random2' )
			{
				$filterQuery = Helper::scheduleFilters( $planInf );
				$calcLimit = 1+(int)(( $planEnd - $planStart ) / 60 / $interval);

				$calcLimit = $calcLimit > 0 ? $calcLimit : 1;

				$getRandomPost = DB::DB()->get_results("SELECT * FROM ".DB::DB()->base_prefix."posts WHERE post_status='publish' {$filterQuery} LIMIT " . $calcLimit , ARRAY_A);
			}

			if( empty($planInf['share_time']) )
			{
				$getLastShareTime = DB::DB()->get_row("SELECT MAX(send_time) AS max_share_time FROM ".DB::table('feeds')." WHERE schedule_id='$scheduleId'", ARRAY_A);
				$planInf['share_time'] = date('H:i:s' , strtotime($getLastShareTime['max_share_time']));
			}

			$cursorDayTimestamp = strtotime( date('Y-m-d', $planStart) . ' ' . $planInf['share_time'] );
			$planEnd = strtotime( date('Y-m-d', $planEnd) . ' 23:59:59' );

			while( $cursorDayTimestamp <= $planEnd )
			{
				$currentDate = date('Y-m-d', $cursorDayTimestamp);
				$time = date('H:i', $cursorDayTimestamp);

				$cursorDayTimestamp += 60 * $interval;

				if( strtotime( $currentDate . ' ' . $time ) < current_time('timestamp') )
				{
					continue;
				}

				if( $postCount === 0 )
					break;

				if( $planInf['post_sort'] == 'random' || $planInf['post_sort'] == 'random2' )
				{
					$postDetails = 'Will select randomly';
					$postId = null;
				}
				else
				{
					$thisPostInf = current( $getRandomPost );
					next( $getRandomPost );

					if( $thisPostInf )
					{
						$postDetails = '<b>Post ID:</b> ' . $thisPostInf['ID'] . "<br><b>Title:</b> " . htmlspecialchars(Helper::cutText($thisPostInf['post_title']) . '<br><br><i>Click to get the post page</i>');
						$postId = $thisPostInf['ID'];
					}
					else
					{
						$postDetails = 'Post not found with your filters for this date!';
						$postId = null;
					}
				}

				$days[] = [
					'id'		=>	$planInf['id'],
					'title'		=>	htmlspecialchars( Helper::cutText($planInf['title'], 22) ),
					'post_data'	=>	$postDetails,
					'post_id'	=>	$postId,
					'date'		=>	$currentDate,
					'time'		=>	$time
				];

				$postCount--;
			}

		}

		Helper::response(true, ['days' => $days]);
	}

	public function calcualte_post_count()
	{
		$post_type_filter = Request::post('post_type_filter', '', 'string');
		$category_filter = Request::post('category_filter', '', 'int');
		$post_date_filter = Request::post('post_date_filter', '', 'string');

		$scheduleInf = [
			'id'                    =>  0,
			'post_type_filter'      =>  $post_type_filter,
			'category_filter'       =>  $category_filter,
			'post_date_filter'      =>  $post_date_filter,
			'post_ids'              =>  ''
		];

		$filterQuery = Helper::scheduleFilters( $scheduleInf );

		$getRandomPost = DB::DB()->get_row("SELECT count(0) AS post_count FROM ".DB::DB()->base_prefix."posts WHERE (post_status='publish' OR post_type='attachment') {$filterQuery}" , ARRAY_A);
		$postsCount = (int)$getRandomPost['post_count'];

		Helper::response( true, [
			'count' =>  $postsCount
		] );
	}

}