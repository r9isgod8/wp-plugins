<?php

namespace FSPoster\App\Controller\Ajax;

use FSPoster\App\Providers\DB;
use FSPoster\App\Providers\Helper;
use FSPoster\App\Providers\Request;
use FSPoster\App\Lib\fb\FacebookApi;

trait App
{
	public function delete_app()
	{
		$id = Request::post('id', '0', 'int');
		if( !$id )
		{
			exit();
		}

		$checkApp = DB::fetch('apps' , $id);
		if( !$checkApp )
		{
			Helper::response(false , esc_html__('App not found!' , 'fs-poster'));
		}
		else if( $checkApp['user_id'] != get_current_user_id() )
		{
			Helper::response(false , esc_html__('You do not have a permission to delete this app!' , 'fs-poster'));
		}
		else if( $checkApp['is_standart'] > 0 )
		{
			Helper::response(false , esc_html__('You can not delete this app!' , 'fs-poster'));
		}

		DB::DB()->delete(DB::table('apps') , ['id' => $id]);

		Helper::response(true);
	}

	public function add_new_app()
	{
		$data = [];
		$data['app_id'] = Request::post('app_id' , '' , 'string');
		$data['app_key'] = Request::post('app_key' , '' , 'string');
		$data['app_secret'] = Request::post('app_secret' , '' , 'string');
		$driver = Request::post('driver' , '' , 'string');

		$appSupports = [
			'fb'        =>  ['app_id' , 'app_key'],
			'twitter'   =>  ['app_key' , 'app_secret'],
			'linkedin'  =>  ['app_id' , 'app_secret'],
			'vk'        =>  ['app_id' , 'app_secret'],
			'pinterest' =>  ['app_id' , 'app_secret'],
			'reddit'    =>  ['app_id' , 'app_secret'],
			'tumblr'    =>  ['app_key' , 'app_secret'],
			'ok'    	=>  ['app_id' , 'app_key' , 'app_secret'],
			'medium'   	=>  ['app_id' , 'app_secret'],
		];

		if( !isset($appSupports[$driver]) )
		{
			Helper::response(false);
		}

		$checkParams = [];
		$checkParams['user_id'] = get_current_user_id();
		$checkParams['driver'] = $driver;
		foreach( $appSupports[$driver] AS $field1 )
		{
			if( empty($data[$field1]) )
			{
				Helper::response(false , $field1. ' ' . esc_html__('field is empty!' , 'fs-poster'));
			}

			$checkParams[$field1] = $data[$field1];
		}

		$checkAppIdExist = DB::fetch('apps' , $checkParams);
		if( $checkAppIdExist )
		{
			Helper::response(false , ['error_msg'=>esc_html__('This app has already been added.' , 'fs-poster')]);
		}

		if( $driver == 'fb' )
		{
			$validateApp = FacebookApi::validateAppSecret( $data['app_id'] , $data['app_key'] );

			if( !$validateApp )
			{
				Helper::response(false , ['error_msg' => esc_html__('App ID or Secret is invalid!' , 'fs-poster')]);
			}
			else if( ($checkResult = Helper::checkPermission( $validateApp['permissions'] )) !== true )
			{
				Helper::response(false , ['error_msg' => $checkResult]);
			}
		}

		$checkParams['is_public'] = 0;
		$checkParams['is_standart'] = 0;
		$checkParams['name'] = isset($validateApp['name']) ? $validateApp['name'] : (empty($data['app_id']) ? $data['app_key'] : $data['app_id']);

		DB::DB()->insert(DB::table('apps') , $checkParams);

		Helper::response(true, ['id' => DB::DB()->insert_id , 'name' => esc_html($checkParams['name'])]);
	}
}