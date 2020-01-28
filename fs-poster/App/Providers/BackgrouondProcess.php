<?php

namespace FSPoster\App\Providers;


class BackgrouondProcess extends \WP_Async_Request
{

	/**
	 * @var string
	 */
	protected $action = 'fs_poster_background_process';

	/**
	 * Handle
	 *
	 * Override this method to perform any actions required
	 * during the async request.
	 */
	protected function handle()
	{
		$postId = Request::post('post_id', '0', 'int');

		if( !( $postId > 0 ) )
			return;

		$timer = (int)get_option('fs_share_timer', '0');

		if( $timer > 0 )
		{
			// prevent other plugins conflicts - bugs...
			set_time_limit(30);
			sleep(20);

			$shareOn = time() + $timer * 60 - 20;

			wp_schedule_single_event( $shareOn ,  'fs_check_background_shared_posts' , [ $postId ] );
		}
		else
		{
			CronJob::sendPostBackground( $postId );
		}
	}

}