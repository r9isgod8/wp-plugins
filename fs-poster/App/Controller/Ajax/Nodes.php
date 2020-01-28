<?php

namespace FSPoster\App\Controller\Ajax;

use FSPoster\App\Providers\DB;
use FSPoster\App\Providers\Helper;
use FSPoster\App\Providers\Request;

trait Nodes
{

	public function settings_node_activity_change()
	{
		$id = Request::post('id' , '0' , 'num');
		$checked = Request::post('checked' , -1 , 'num' , ['0','1']);
		$filter_type = Request::post('filter_type' , '' , 'string' , ['in' , 'ex']);
		$categories = Request::post('categories' , [], 'array');

		if( !($id > 0 && $checked > -1) )
		{
			Helper::response(false );
		}

		$categoriesArr = [];
		foreach($categories AS $categId)
		{
			if(is_numeric($categId) && $categId > 0)
			{
				$categoriesArr[] = (int)$categId;
			}
		}
		$categoriesArr = implode(',' , $categoriesArr);

		if( (!empty($categoriesArr) && empty($filter_type)) || (empty($categoriesArr) && !empty($filter_type)) )
		{
			Helper::response(false , 'Please select categories and filter type!');
		}

		$categoriesArr = empty($categoriesArr) ? null : $categoriesArr;
		$filter_type = empty($filter_type) ? 'no' : $filter_type;

		$checkAccount = DB::DB()->get_row("SELECT * FROM " . DB::table('account_nodes') . " WHERE id='" . $id . "'" , ARRAY_A);

		if( !$checkAccount )
		{
			Helper::response(false , 'Community not found!');
		}

		if( $checkAccount['user_id'] != get_current_user_id() && $checkAccount['is_public'] != 1 )
		{
			Helper::response(false , 'Community not found or you do not have a permission for this community!');
		}

		if( $checked )
		{
			$checkIfIsActive = DB::fetch('account_node_status' , [
				'node_id'		=>	$id,
				'user_id'		=>	get_current_user_id()
			]);

			if( !$checkIfIsActive )
			{
				DB::DB()->insert(DB::table('account_node_status') , [
					'node_id'		=>	$id,
					'user_id'		=>	get_current_user_id(),
					'filter_type'	=>	$filter_type,
					'categories'	=>	$categoriesArr
				]);
			}
			else
			{
				DB::DB()->update(DB::table('account_node_status') , [
					'filter_type'	=>	$filter_type,
					'categories'	=>	$categoriesArr
				] , ['id' => $checkIfIsActive['id']]);
			}
		}
		else
		{
			DB::DB()->delete(DB::table('account_node_status') , [
				'node_id'		=>	$id,
				'user_id'		=>	get_current_user_id()
			]);
		}

		Helper::response(true);
	}

	public function settings_node_make_public()
	{
		if(!( isset($_POST['id']) && is_numeric($_POST['id']) && $_POST['id'] > 0 ))
		{
			Helper::response(false);
		}

		$id = (int)$_POST['id'];

		$getNodeInf = DB::fetch('account_nodes' , $id);

		if( !$getNodeInf )
		{
			Helper::response(false , 'Community not found!');
		}

		if( $getNodeInf['user_id'] != get_current_user_id() )
		{
			Helper::response(false, 'This is not one of you added comunity. Therefore you do not have a permission for make public/private this community.');
		}

		$newStatus = (int)(!$getNodeInf['is_public']);

		DB::DB()->update(DB::table('account_nodes') , [ 'is_public' => $newStatus ] , [ 'id' => $id ]);

		Helper::response(true);
	}

	public function settings_node_delete()
	{
		if(!( isset($_POST['id']) && is_numeric($_POST['id']) && $_POST['id'] > 0 ))
		{
			Helper::response(false);
		}

		$id = (int)$_POST['id'];

		$checkAccount = DB::DB()->get_row("SELECT * FROM " . DB::table('account_nodes') . " WHERE id='" . $id . "'" , ARRAY_A);

		if( !$checkAccount )
		{
			Helper::response(false , 'Community not found!');
		}

		if( $checkAccount['user_id'] != get_current_user_id() )
		{
			Helper::response(false , 'You do not have a permission for deleting this community!');
		}

		DB::DB()->delete(DB::table('account_nodes'), ['id' => $id]);
		DB::DB()->delete(DB::table('account_node_status'), ['node_id' => $id]);

		Helper::response(true);
	}
}