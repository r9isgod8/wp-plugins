<?php

namespace FSPoster\App\Controller\Ajax;

use FSPoster\App\Providers\DB;
use FSPoster\App\Providers\Helper;
use FSPoster\App\Providers\Request;

trait SharePanel
{

	public function manual_share_save()
	{
		$id			= Request::post('id' , '0' , 'num');
		$link		= Request::post('link' , '' , 'string');
		$message	= Request::post('message' , '' , 'string');
		$image		= Request::post('image' , '0' , 'num');
		$tmp		= Request::post('tmp' , '0' , 'num', ['0', '1']);

		$sqlData = [
			'post_type'			=>	'fs_post' . ( $tmp ? '_tmp' : '' ),
			'post_content'		=>	$message,
			'post_status'		=>	'publish',
			'comment_status'	=>	'closed',
			'ping_status'		=>	'closed'
		];

		if( $id > 0 )
		{
			$sqlData['ID'] = $id;

			wp_insert_post( $sqlData );

			delete_post_meta($id, '_fs_link');
			delete_post_meta($id, '_thumbnail_id');
		}
		else
		{
			$id = wp_insert_post( $sqlData );
		}

		add_post_meta($id, '_fs_link', $link);

		if( $image > 0 )
		{
			add_post_meta($id, '_thumbnail_id', $image);
		}
		else
		{
			delete_post_meta( $id, '_thumbnail_id' );
		}

		Helper::response(true , ['id'		=>	$id]);
	}

	public function manual_share_delete()
	{
		$id	= Request::post('id' , '0' , 'num');

		if( !($id > 0) )
			Helper::response(false);

		$currentUserId = (int)get_current_user_id();

		$checkPost = DB::DB()->get_row('SELECT * FROM ' . DB::DB()->base_prefix . "posts WHERE post_type='fs_post' AND post_author='{$currentUserId}' AND ID='{$id}'", ARRAY_A);

		if( !$checkPost )
			Helper::response(false, 'Post not found!');

		delete_post_meta($id, '_fs_link');
		delete_post_meta($id, '_thumbnail_id');
		wp_delete_post($id);

		Helper::response(true , ['id'		=>	$id]);
	}

	public function check_post_is_published()
	{
		$id			= Request::post('id' , '0' , 'num');

		$postStatus = get_post_status( $id );

		Helper::response(true, [
			'post_status' => $postStatus=='publish' ? true : false
		]);
	}

}