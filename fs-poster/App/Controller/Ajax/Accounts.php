<?php

namespace FSPoster\App\Controller\Ajax;

use FSPoster\App\Providers\DB;
use FSPoster\App\Providers\Helper;
use FSPoster\App\Providers\Request;
use FSPoster\App\Lib\fb\FacebookCookieApi;
use FSPoster\App\Lib\fb\FacebookApi;
use FSPoster\App\Lib\google\GoogleMyBusiness;
use FSPoster\App\Lib\instagram\InstagramApi;
use FSPoster\App\Lib\instagram\InstagramCookieApi;
use FSPoster\App\Lib\reddit\Reddit;
use FSPoster\App\Lib\telegram\Telegram;
use FSPoster\App\Lib\vk\Vk;
use InstagramAPI\Devices\GoodDevices;
use InstagramAPI\Signatures;

trait Accounts
{

	public function add_new_fb_account_with_cookie()
	{
		$cookieCuser	= Request::post('cookie_c_user' , '' , 'string');
		$cookieXs		= Request::post('cookie_xs' , '' , 'string');
		$proxy			= Request::post('proxy' , '' , 'string');

		$fb = new FacebookCookieApi($cookieCuser, $cookieXs, $proxy);
		$data = $fb->authorizeFbUser();

		if( $data === false )
		{
			Helper::response(false, 'The entered cookies are wrong!');
		}

		Helper::response(true , ['data' => $data]);
	}

	public function add_new_fb_account_with_at()
	{
		$accessToken = Request::post('access_token' , '' , 'string');
		$proxy       = Request::post('proxy' , '' , 'string');
		$app_id      = Request::post('app_id' , '0' , 'int');

		$accessToken = FacebookApi::extractAccessToken($accessToken);

		if( $accessToken['status'] == false )
		{
			Helper::response(false , $accessToken['message']);
		}

		$accessToken = $accessToken['access_token'];

		$data = FacebookApi::authorizeFbUser( $app_id , $accessToken , $proxy );

		Helper::response(true , ['data' => $data]);
	}

	public function delete_account()
	{
		if( !(isset($_POST['id']) && is_numeric($_POST['id']) && $_POST['id'] > 0) )
		{
			exit();
		}
		$id = (int)$_POST['id'];

		$checkAccount = DB::fetch('accounts' , $id);
		if( !$checkAccount )
		{
			Helper::response(false , esc_html__('Account not found!' , 'fs-poster'));
		}
		else if( $checkAccount['user_id'] != get_current_user_id() )
		{
			Helper::response(false , esc_html__('You do not have a permission to delete this account!' , 'fs-poster'));
		}

		DB::DB()->delete(DB::table('accounts') , ['id' => $id]);
		DB::DB()->delete(DB::table('account_status') , ['account_id' => $id]);
		DB::DB()->delete(DB::table('account_access_tokens') , ['account_id' => $id]);

		DB::DB()->query("DELETE FROM ".DB::table('account_node_status')." WHERE node_id IN (SELECT id FROM ".DB::table('account_nodes')." WHERE account_id='$id')");
		DB::DB()->delete(DB::table('account_nodes') , ['account_id' => $id]);

		if( $checkAccount['driver'] == 'instagram' )
		{
			$checkIfUsernameExist = DB::fetch('accounts' , ['username' => $checkAccount['username'] , 'driver' => $checkAccount['driver']]);

			if( !$checkIfUsernameExist )
			{
				DB::DB()->delete(DB::table('account_sessions') , ['driver' => $checkAccount['driver']  , 'username' => $checkAccount['username']]);
			}
		}

		Helper::response(true);
	}

	public function get_accounts()
	{
		$name = Request::post('name' , '' , 'string');

		$supported = ['fb' , 'twitter' , 'instagram' , 'linkedin', 'vk', 'pinterest' , 'reddit' , 'tumblr' , 'ok', 'google_b', 'telegram', 'medium'];
		if( empty($name) || !in_array( $name , $supported ) )
		{
			Helper::response(false);
		}

		Helper::modalView( '../app_menus/account/' . $name );
	}

	public function account_activity_change()
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
		$filter_type = empty($filter_type) || empty($categoriesArr) ? 'no' : $filter_type;

		$checkAccount = DB::DB()->get_row("SELECT * FROM " . DB::table('accounts') . " WHERE id='" . $id . "'" , ARRAY_A);

		if( !$checkAccount )
		{
			Helper::response(false , 'Account not found!');
		}

		if( $checkAccount['user_id'] != get_current_user_id() && $checkAccount['is_public'] != 1 )
		{
			Helper::response(false , 'Account not found or you do not have a permission for this account!');
		}

		if( $checked )
		{
			$checkIfIsActive = DB::fetch('account_status' , [
				'account_id'	=>	$id,
				'user_id'		=>	get_current_user_id(),
			]);

			if( !$checkIfIsActive )
			{
				DB::DB()->insert(DB::table('account_status') , [
					'account_id'	=>	$id,
					'user_id'		=>	get_current_user_id(),
					'filter_type'	=>	$filter_type,
					'categories'	=>	$categoriesArr
				]);
			}
			else
			{
				DB::DB()->update( DB::table('account_status') , [
					'filter_type'	=>	$filter_type,
					'categories'	=>	$categoriesArr
				] , ['id' => $checkIfIsActive['id']] );
			}
		}
		else
		{
			DB::DB()->delete(DB::table('account_status') , [
				'account_id'	=>	$id,
				'user_id'		=>	get_current_user_id()
			]);
		}

		Helper::response(true);
	}

	public function make_account_public()
	{
		if(!( isset($_POST['checked']) && ( $_POST['checked'] == '1' || $_POST['checked'] == '0')
			&& isset($_POST['id']) && is_numeric($_POST['id']) && $_POST['id'] > 0))
		{
			Helper::response(false);
		}

		$id = (int)$_POST['id'];
		$checked = (int)$_POST['checked'];

		$checkAccount = DB::DB()->get_row("SELECT * FROM " . DB::table('accounts') . " WHERE id='" . $id . "'" , ARRAY_A);

		if( !$checkAccount )
		{
			Helper::response(false , 'Account not found!');
		}

		if( $checkAccount['user_id'] != get_current_user_id() )
		{
			Helper::response(false , esc_html__('This is not one of you added account, therefore you do not have a permission for make this profile public or private.' , 'fs-poster'));
		}

		DB::DB()->update(DB::table('accounts') , [
			'is_public' => $checked
		] , [
			'id' => $id,
			'user_id' => get_current_user_id()
		]);

		Helper::response(true);
	}

	public function add_instagram_account($forceLogin = true)
	{
		$username = Request::post('username' , '' , 'string');
		$password = Request::post('password' , '' , 'string');
		$proxy    = Request::post('proxy' , '' , 'string');

		if( empty($username) || empty($password) )
		{
			Helper::response(false, ['error_msg' => esc_html__('Please enter the instagram username and password!' , 'fs-poster')]);
		}

		if( $forceLogin )
		{
			// try to delete old session folder
			DB::DB()->delete(DB::table('account_sessions') , ['driver'=>'instagram' , 'username' => $username]);
		}

		$ig = InstagramApi::login($username , $password , $proxy , $forceLogin);
		if( isset($ig['do']) && $ig['do'] == 'challenge' )
		{
			Helper::response(true , [
				'do'            => 'challenge' ,
				'message'       => htmlspecialchars($ig['message']),
				'user_id'       => $ig['user_id'],
				'nonce_code'    => $ig['nonce_code']
			]);
		}
		else if( isset($ig['do']) && $ig['do'] == 'two_factor' )
		{
			Helper::response(true , [
				'do'                    => 'two_factor' ,
				'message'               => htmlspecialchars($ig['message']),
				'two_factor_identifier' => $ig['two_factor_identifier']
			]);
		}
		else if( $ig['status'] == 'error' )
		{
			Helper::response(false , htmlspecialchars(substr($ig['message'] , strpos($ig['message'] , ':')+2)) );
		}
		else if( !isset($ig['ig']) )
		{
			Helper::response(false);
		}

		$ig = $ig['ig'];

		$data = $ig->people->getSelfInfo()->asArray();
		$data = $data['user'];

		$sqlData = [
			'user_id'           =>  get_current_user_id(),
			'profile_id'        =>  $ig->account_id,
			'username'          =>  $username,
			'password'          =>  $password,
			'proxy'             =>  $proxy,
			'driver'            =>  'instagram',
			'name'              =>  isset($data['full_name']) ? $data['full_name'] : '',
			'followers_count'   =>  isset($data['follower_count']) ? $data['follower_count'] : '0',
			'friends_count'     =>  isset($data['following_count']) ? $data['following_count'] : '0',
			'profile_pic'       =>  isset($data['profile_pic_url']) ? $data['profile_pic_url'] : ''
		];

		$checkIfExists = DB::fetch('accounts' , ['user_id' => get_current_user_id() , 'profile_id' => $ig->account_id]);
		if( $checkIfExists )
		{
			DB::DB()->update(DB::table('accounts') , $sqlData , ['id' => $checkIfExists['id']]);
		}
		else
		{
			DB::DB()->insert(DB::table('accounts') , $sqlData);
		}

		Helper::response(true);
	}

	public function add_instagram_account_cookie_method()
	{
		$cookie_sessionid	= Request::post('cookie_sessionid', '' , 'string');
		$proxy				= Request::post('proxy' , '' , 'string');

		$password			= '*****';

		if( empty($cookie_sessionid) )
		{
			Helper::response(false, ['error_msg' => esc_html__('Please enter the instagram username and password!' , 'fs-poster')]);
		}

		$details			= InstagramCookieApi::getDetailsBySessId($cookie_sessionid, $proxy);

		$username			= $details['username'];
		$cookie_ds_user_id	= $details['id'];
		$cookie_mcd			= '3';
		$cookie_csrftoken	= $details['csrf'];

		$cookiesArr = [
			["Name" => "sessionid", "Value" => $cookie_sessionid, "Domain" => ".instagram.com", "Path" => "/","Max-Age" => null,"Expires" => null,"Secure" => true,"Discard" => false,"HttpOnly" =>	true],
			["Name" => "csrftoken", "Value" => $cookie_csrftoken, "Domain" => ".instagram.com", "Path" => "/","Max-Age" => null,"Expires" => null,"Secure" => true,"Discard" => false,"HttpOnly" => false],
			["Name" => "mcd", "Value" => $cookie_mcd, "Domain" => ".instagram.com", "Path" => "/","Max-Age" => null,"Expires" => null,"Secure" => true,"Discard" => false,"HttpOnly" => false]
		];

		$settingsArr = [
			"devicestring"		=> GoodDevices::getRandomGoodDevice(),
			"device_id"			=> Signatures::generateDeviceId(),
			"phone_id"			=> Signatures::generateUUID(true),
			"uuid"				=> Signatures::generateUUID(true),
			"advertising_id"	=> Signatures::generateUUID(true),
			"session_id"		=> Signatures::generateUUID(true),
			"last_login"		=> time(),
			"last_experiments"	=> time(),
			"account_id"		=> $cookie_ds_user_id
		];

		DB::DB()->delete(DB::table('account_sessions') , ['driver'=>'instagram' , 'username' => $username]);
		DB::DB()->insert(DB::table('account_sessions') , [
			'driver'	=>	'instagram',
			'username'	=>	$username,
			'settings'	=>	json_encode($settingsArr),
			'cookies'	=>	json_encode($cookiesArr)
		]);

		$insertedId = DB::DB()->insert_id;

		$ig = InstagramApi::login($username , $password , $proxy , false);
		if( !isset($ig['ig']) )
		{
			DB::DB()->delete(DB::table('account_sessions') , ['id' => $insertedId]);
			Helper::response(false, ( isset($ig['message']) && is_string($ig['message']) ? htmlspecialchars($ig['message']) : '' ) );
		}

		$ig = $ig['ig'];

		$data = $ig->people->getSelfInfo()->asArray();
		$data = $data['user'];

		$sqlData = [
			'user_id'           =>  get_current_user_id(),
			'profile_id'        =>  $ig->account_id,
			'username'          =>  $username,
			'password'          =>  $password,
			'proxy'             =>  $proxy,
			'driver'            =>  'instagram',
			'name'              =>  isset($data['full_name']) ? $data['full_name'] : '',
			'followers_count'   =>  isset($data['follower_count']) ? $data['follower_count'] : '0',
			'friends_count'     =>  isset($data['following_count']) ? $data['following_count'] : '0',
			'profile_pic'       =>  isset($data['profile_pic_url']) ? $data['profile_pic_url'] : ''
		];

		$checkIfExists = DB::fetch('accounts' , ['user_id' => get_current_user_id() , 'profile_id' => $ig->account_id]);
		if( $checkIfExists )
		{
			DB::DB()->update(DB::table('accounts') , $sqlData , ['id' => $checkIfExists['id']]);
		}
		else
		{
			DB::DB()->insert(DB::table('accounts') , $sqlData);
		}

		Helper::response(true);
	}

	public function confirm_instagram_challenge()
	{
		$username    = Request::post('username' , '' , 'string');
		$password    = Request::post('password' , '' , 'string');
		$proxy       = Request::post('proxy' , '' , 'string');
		$code        = Request::post('code' , '' , 'string');
		$user_id     = Request::post('user_id' , '' , 'string');
		$nonce_code  = Request::post('nonce_code' , '' , 'string');

		if( empty($username) || empty($password) || empty($code) || empty($user_id) || empty($nonce_code) )
		{
			Helper::response(false, ['error_msg' => esc_html__('Please enter the code!' , 'fs-poster')]);
		}

		$ig = InstagramApi::challenge($username , $password , $proxy , $user_id , $nonce_code , $code);

		if( $ig['status'] == 'error' )
		{
			Helper::response(false , htmlspecialchars($ig['message']));
		}

		$this->add_instagram_account(false);
	}

	public function confirm_two_factor()
	{
		$username               = Request::post('username' , '' , 'string');
		$password               = Request::post('password' , '' , 'string');
		$proxy                  = Request::post('proxy' , '' , 'string');
		$code                   = Request::post('code' , '' , 'string');
		$two_factor_identifier  = Request::post('two_factor_identifier' , '' , 'string');

		if( empty($username) || empty($password) || empty($code) || empty($two_factor_identifier) )
		{
			Helper::response(false, ['error_msg' => esc_html__('Please enter the code!' , 'fs-poster')]);
		}

		$ig = InstagramApi::verifyTwoFactor($username , $password , $proxy , $two_factor_identifier , $code);

		if( $ig['status'] == 'error' )
		{
			Helper::response(false , htmlspecialchars($ig['message']));
		}

		$this->add_instagram_account(false);
	}

	public function add_vk_account()
	{
		$accessToken    = Request::post('at' , '' , 'string');
		$app            = Request::post('app' , '0' , 'int');
		$proxy          = Request::post('proxy' , '0' , 'string');

		if( empty($accessToken) )
		{
			Helper::response(false , ['error_msg' => esc_html__('Access token is empty!' , 'fs-poster')]);
		}

		preg_match('/access_token\=([^\&]+)/' , $accessToken , $accessToken2);

		if( isset($accessToken2[1]) )
		{
			$accessToken = $accessToken2[1];
		}

		$getApp = DB::fetch('apps' , ['driver' => 'vk' , 'app_id' => $app]);

		$result = Vk::authorizeVkUser((int)$getApp['id'] , $accessToken , $proxy);

		if( isset($result['error']) )
		{
			Helper::response(false , $result['error']);
		}

		Helper::response(true);
	}

	public function pinterest_account_board_change()
	{
		$accountId = Request::post('account_id' , '0' , 'num');
		$board = Request::post('board' , '' , 'string');

		if( empty($board) || !($accountId > 0) )
		{
			Helper::response(false);
		}

		$boardId = mb_substr($board , 0 , mb_strpos($board , ':' , 0 , 'UTF-8') , 'UTF-8');
		$boardName = mb_substr($board , mb_strpos($board , ':' , 0 , 'UTF-8') + 1 , null , 'UTF-8');

		$board = json_encode(['board' => ['id' => $boardId , 'name' => $boardName]]);

		DB::DB()->update(DB::table('accounts') , ['options' => $board] , [
			'user_id'   =>  get_current_user_id(),
			'id'        =>  $accountId
		]);

		Helper::response(true);
	}

	public function search_subreddits()
	{
		$accountId	= Request::post('account_id' , '0' , 'num');
		$search		= Request::post('search', '', 'string');

		$userId = get_current_user_id();

		$accountInf = DB::DB()->get_row("SELECT * FROM ".DB::table('accounts')." tb1 WHERE id='{$accountId}' AND driver='reddit' AND (user_id='{$userId}' OR is_public=1) " , ARRAY_A);

		if( !$accountInf )
		{
			Helper::response(false, 'You have not a permission for adding subreddit in this account!');
		}

		$accessTokenGet = DB::fetch('account_access_tokens', ['account_id' => $accountId]);

		$accessToken = $accessTokenGet['access_token'];
		if( (time()+30) > strtotime($accessTokenGet['expires_on']) )
		{
			$accessToken = Reddit::refreshToken($accessTokenGet);
		}

		$searchSubreddits = Reddit::cmd('https://oauth.reddit.com/api/search_subreddits', 'POST', $accessToken, [
			'query'						=> $search,
			'include_over_18'			=>	true,
			'exact'						=>	false,
			'include_unadvertisable'	=>	true
		]);

		$newArr = [];
		$preventDublicates = [];

		foreach( $searchSubreddits['subreddits'] AS $subreddit )
		{
			$preventDublicates[ $subreddit['name'] ] = true;

			$newArr[] = [
				'text'	=> htmlspecialchars($subreddit['name'] . ' ( ' . $subreddit['subscriber_count'] . ' subscribers )'),
				'id'	=> htmlspecialchars($subreddit['name'])
			];
		}

		// for fixing Reddit API bug
		$searchSubreddits = Reddit::cmd('https://oauth.reddit.com/api/search_subreddits', 'POST', $accessToken, [
			'query'						=> $search,
			'exact'						=>	true
		]);

		foreach( $searchSubreddits['subreddits'] AS $subreddit )
		{
			if( isset( $preventDublicates[ $subreddit['name'] ] ) )
			{
				continue;
			}

			$newArr[] = [
				'text'	=> htmlspecialchars($subreddit['name'] . ' ( ' . $subreddit['subscriber_count'] . ' subscribers )'),
				'id'	=> htmlspecialchars($subreddit['name'])
			];
		}

		Helper::response(true, ['subreddits' => $newArr]);
	}

	public function reddit_get_subreddt_flairs()
	{
		$accountId	= Request::post('account_id' , '0' , 'num');
		$subreddit	= Request::post('subreddit', '', 'string');

		$subreddit = basename($subreddit);

		$userId = get_current_user_id();

		$accountInf = DB::DB()->get_row("SELECT * FROM ".DB::table('accounts')." tb1 WHERE id='{$accountId}' AND driver='reddit' AND (user_id='{$userId}' OR is_public=1) " , ARRAY_A);

		if( !$accountInf )
		{
			Helper::response(false, 'You have not a permission for adding subreddit in this account!');
		}

		$accessTokenGet = DB::fetch('account_access_tokens', ['account_id' => $accountId]);

		$accessToken = $accessTokenGet['access_token'];
		if( (time()+30) > strtotime($accessTokenGet['expires_on']) )
		{
			$accessToken = Reddit::refreshToken($accessTokenGet);
		}

		$flairs = Reddit::cmd('https://oauth.reddit.com/r/'.$subreddit.'/api/link_flair', 'GET', $accessToken);

		$newArr = [];
		if( !isset($flairs['error']) )
		{
			foreach( $flairs AS $flair )
			{
				$newArr[] = [
					'text'	=> htmlspecialchars($flair['text']),
					'id'	=> htmlspecialchars($flair['id'])
				];
			}
		}

		Helper::response(true, ['flairs' => $newArr]);
	}

	public function reddit_subreddit_save()
	{
		$accountId		= Request::post('account_id' , '0' , 'num');
		$subreddit		= Request::post('subreddit' , '' , 'string');
		$flairId		= Request::post('flair' , '' , 'string');
		$flairName		= Request::post('flair_name' , '' , 'string');

		$filter_type = Request::post('filter_type' , '' , 'string' , ['in' , 'ex']);
		$categories = Request::post('categories' , [], 'array');

		$categoriesArr = [];
		foreach($categories AS $categId)
		{
			if(is_numeric($categId) && $categId > 0)
			{
				$categoriesArr[] = (int)$categId;
			}
		}
		$categoriesArr = implode(',' , $categoriesArr);

		$categoriesArr = empty($categoriesArr) ? null : $categoriesArr;
		$filter_type = empty($filter_type) || empty($categoriesArr) ? 'no' : $filter_type;

		if( !(!empty($subreddit) && $accountId > 0) )
		{
			Helper::response(false);
		}

		$userId = (int)get_current_user_id();

		$accountInf = DB::DB()->get_row("SELECT * FROM ".DB::table('accounts')." WHERE id='{$accountId}' AND driver='reddit' AND (user_id='{$userId}' OR is_public=1) " , ARRAY_A);

		if( !$accountInf )
		{
			Helper::response(false, 'You have not a permission for adding subreddit in this account!');
		}

		DB::DB()->insert(DB::table('account_nodes') , [
			'user_id'			=>	$userId,
			'driver'			=>	'reddit',
			'account_id'		=>	$accountId,
			'node_type'			=>	'subreddit',
			'screen_name'		=>	$subreddit,
			'name'				=>	$subreddit,
			'access_token'		=>	$flairId,
			'category'			=>	$flairName
		]);

		$nodeId = DB::DB()->insert_id;

		// actiavte...
		DB::DB()->insert(DB::table('account_node_status') , [
			'node_id'		=>	$nodeId,
			'user_id'		=>	$userId,
			'filter_type'	=>	$filter_type,
			'categories'	=>	$categoriesArr
		]);

		Helper::response(true , ['id' => $nodeId]);
	}

	public function add_google_b_account()
	{
		$cookie_sid		= Request::post('cookie_sid' , '' , 'string');
		$cookie_hsid	= Request::post('cookie_hsid' , '' , 'string');
		$cookie_ssid	= Request::post('cookie_ssid' , '' , 'string');
		$proxy			= Request::post('proxy' , '' , 'string');

		if( empty( $cookie_sid ) || empty( $cookie_hsid ) || empty( $cookie_ssid ) )
		{
			Helper::response(false, 'Please type your Cookies!');
		}

		$google = new GoogleMyBusiness($cookie_sid, $cookie_hsid, $cookie_ssid, $proxy);
		$data = $google->getUserInfo();

		if( empty( $data['id'] ) )
		{
			Helper::response(false, 'The entered cookies are wrong!');
		}

		$options = json_encode( [
			'sid'	=>	$cookie_sid,
			'hsid'	=>	$cookie_hsid,
			'ssid'	=>	$cookie_ssid,
		] );

		$sqlData = [
			'user_id'           =>  get_current_user_id(),
			'profile_id'        =>  $data['id'],
			'username'          =>  isset($data['email']) ? $data['email'] : '',
			'password'          =>  '',
			'proxy'             =>  $proxy,
			'driver'            =>  'google_b',
			'name'              =>  isset($data['name']) ? $data['name'] : '',
			'profile_pic'       =>  isset($data['profile_image']) ? $data['profile_image'] : '',
			'options'			=>	$options
		];

		$checkIfExists = DB::fetch('accounts' , [ 'driver' => 'google_b', 'user_id' => get_current_user_id() , 'profile_id' => $data['id']]);
		if( $checkIfExists )
		{
			DB::DB()->update(DB::table('accounts') , $sqlData , ['id' => $checkIfExists['id']]);
			$accountId = $checkIfExists['id'];
		}
		else
		{
			DB::DB()->insert(DB::table('accounts') , $sqlData);
			$accountId = DB::DB()->insert_id;
		}

		$locations = $google->getMyLocations();
		foreach ( $locations AS $location )
		{
			DB::DB()->insert(DB::table('account_nodes') , [
				'user_id'		=>  get_current_user_id(),
				'account_id'	=>  $accountId,
				'node_type'		=>  'location',
				'node_id'		=>  $location['id'],
				'name'			=>  $location['name'],
				'category'		=>  $location['category'],
				'driver'		=>	'google_b'
			]);
		}

		Helper::response(true );
	}

	public function add_telegram_bot()
	{
		$bot_token	= Request::post('bot_token' , '' , 'string');
		$proxy		= Request::post('proxy' , '' , 'string');

		if( empty( $bot_token ) )
		{
			Helper::response(false, 'Please type your Bot Token!');
		}

		$tg = new Telegram( $bot_token, $proxy );
		$data = $tg->getBotInfo();

		if( empty( $data['id'] ) )
		{
			Helper::response(false, 'The entered Bot Token is invalid!');
		}

		$sqlData = [
			'user_id'           =>  get_current_user_id(),
			'profile_id'        =>  $data['id'],
			'username'          =>  $data['username'],
			'proxy'             =>  $proxy,
			'driver'            =>  'telegram',
			'name'              =>  $data['name'],
			'options'			=>	$bot_token
		];

		$checkIfExists = DB::fetch('accounts' , [ 'driver' => 'telegram', 'user_id' => get_current_user_id() , 'profile_id' => $data['id']]);
		if( $checkIfExists )
		{
			DB::DB()->update(DB::table('accounts') , $sqlData , ['id' => $checkIfExists['id']]);
		}
		else
		{
			DB::DB()->insert(DB::table('accounts') , $sqlData);
		}

		Helper::response(true );
	}

	public function telegram_chat_save()
	{
		$account_id		= Request::post('account_id' , '' , 'int');
		$chat_id		= Request::post('chat_id' , '' , 'string');

		if( empty($account_id) || empty( $chat_id ) )
		{
			Helper::response( false );
		}

		$accountInf = DB::fetch( 'accounts', ['id' => $account_id] );
		if( !$accountInf )
		{
			Helper::response(false);
		}

		$tg = new Telegram( $accountInf['options'] , $accountInf['proxy'] );
		$data = $tg->getChatInfo( $chat_id );

		if( empty($data['id']) )
		{
			Helper::response(false, 'Chat not found!');
		}

		DB::DB()->insert(DB::table('account_nodes') , [
			'user_id'		=>  get_current_user_id(),
			'account_id'	=>  $account_id,
			'node_type'		=>  'chat',
			'node_id'		=>  $data['id'],
			'name'			=>  $data['name'],
			'screen_name'	=>  $data['username'],
			'category'		=>  $data['type'],
			'driver'		=>	'telegram'
		]);

		Helper::response( true, [
			'id'		=>	DB::DB()->insert_id,
			'chat_pic'	=>	Helper::assets('images/telegram.svg'),
			'chat_name'	=>	htmlspecialchars( $data['name'] ),
			'chat_link'	=>	Helper::profileLink( [ 'driver' => 'telegram', 'username' => $data['username'] ] )
		] );
	}

	public function telegram_last_active_chats()
	{
		$account_id	= Request::post('account' , '' , 'int');

		if( !( is_numeric( $account_id ) && $account_id > 0 ) )
		{
			Helper::response(false);
		}

		$list = [];

		$accountInf = DB::fetch( 'accounts', ['id' => $account_id] );
		if( !$accountInf )
		{
			Helper::response(false);
		}

		$tg = new Telegram( $accountInf['options'] , $accountInf['proxy'] );
		$data = $tg->getActiveChats( );

		Helper::response( true, [ 'list' => $data ] );
	}


}