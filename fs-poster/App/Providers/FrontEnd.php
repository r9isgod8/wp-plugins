<?php

namespace FSPoster\App\Providers;

use Abraham\TwitterOAuth\TwitterOAuth;
use FSPoster\App\Lib\fb\FacebookApi;
use FSPoster\App\Lib\linkedin\Linkedin;
use FSPoster\App\Lib\medium\Medium;
use FSPoster\App\Lib\ok\OdnoKlassniki;
use FSPoster\App\Lib\pinterest\Pinterest;
use FSPoster\App\Lib\reddit\Reddit;
use FSPoster\App\Lib\tumblr\Tumblr;
use FSPoster\App\Lib\twitter\TwitterLib;

class FrontEnd
{

	public function __construct( )
	{
		if( !Helper::pluginDisabled() )
		{
			add_action( 'wp', array( $this, 'constructFns' ) );
		}
	}

	public function constructFns()
	{
		$this->postCounter();

		$this->FBRedirect();
		$this->FBCallback();

		$this->twitterRedirect();
		$this->twitterCallback();

		$this->linkedinRedirect();
		$this->linkedinCallback();

		$this->pinterestRedirect();
		$this->pinterestCallback();

		$this->redditRedirect();
		$this->redditCallback();

		$this->tumblrRedirect();
		$this->tumblrCallback();

		$this->okRedirect();
		$this->okCallback();

		$this->mediumRedirect();
		$this->mediumCallback();

		$this->standartFSApp();
	}

	public function postCounter()
	{
		if( is_single() || is_page() )
		{
			global $post;
			if( isset( $post->ID ) && isset($_GET['feed_id']) && is_numeric($_GET['feed_id']) && $_GET['feed_id'] > 0 )
			{
				$post_id = $post->ID;
				$feed_id = (int)$_GET['feed_id'];

				DB::DB()->query( DB::DB()->prepare("UPDATE " . DB::table('feeds') . " SET visit_count=visit_count+1 WHERE id=%d AND post_id=%d", [$feed_id , $post_id] ) );
			}
		}
	}

	public function FBCallback()
	{
		if( isset($_GET['fb_callback']) && $_GET['fb_callback'] == '1' )
		{
			FacebookApi::getAccessToken();
		}
	}

	public function FBRedirect()
	{
		if( isset($_GET['fb_app_redirect']) && is_numeric($_GET['fb_app_redirect']) && $_GET['fb_app_redirect'] > 0 )
		{
			$appId = (int)$_GET['fb_app_redirect'];

			$link = FacebookApi::getLoginURL($appId);
			header('Location: ' . $link);
			exit();
		}
	}

	public function twitterCallback()
	{
		if( isset($_GET['twitter_callback']) && $_GET['twitter_callback'] == '1'
		    && isset($_GET['oauth_verifier']) && is_string($_GET['oauth_verifier']) && !empty($_GET['oauth_verifier'])
		    && isset($_GET['oauth_token']) && is_string($_GET['oauth_token']) && !empty($_GET['oauth_token'])
		)
		{
			do_action('fs_register_session');

			if( !isset($_SESSION['save_app_id']) || !isset($_SESSION['oauth_token']) || !isset($_SESSION['oauth_token_secret']) || $_GET['oauth_token'] != $_SESSION['oauth_token'] )
			{
				unset($_SESSION['save_app_id']);
				unset($_SESSION['oauth_token']);
				unset($_SESSION['oauth_token_secret']);
				print 'Error!1';
				exit();
			}

			$appId = (int)$_SESSION['save_app_id'];

			$appInf = DB::fetch('apps' , ['id' => $appId , 'driver' => 'twitter']);

			$proxy = '';
			if( isset($_SESSION['fs_proxy_save']) )
			{
				$proxy = is_string($_SESSION['fs_proxy_save']) ? $_SESSION['fs_proxy_save'] : '';

				unset($_SESSION['fs_proxy_save']);
			}

			$connection = new TwitterOAuth($appInf['app_key'], $appInf['app_secret'], $_SESSION['oauth_token'], $_SESSION['oauth_token_secret'] , $proxy);
			$access_token = $connection->oauth("oauth/access_token", array("oauth_verifier" => $_GET['oauth_verifier']));

			unset($_SESSION['save_app_id']);
			unset($_SESSION['oauth_token']);
			unset($_SESSION['oauth_token_secret']);

			if( !( isset($access_token['oauth_token']) && isset($access_token['oauth_token_secret']) ) )
			{
				print 'Error access token!';
				exit();
			}

			TwitterLib::authorizeUser($appInf , $access_token['oauth_token'] , $access_token['oauth_token_secret'] , $proxy);

			print esc_html__('Loading...' , 'fs-poster') . ' <script>if( typeof window.opener.compleateOperation == "function" ){ window.opener.compleateOperation(true);window.close();}else{document.write("'.esc_html__('Error! Please try again!' , 'fs-poster').'");} </script>';
			exit();
		}
	}

	public function twitterRedirect()
	{
		if( isset($_GET['twitter_app_redirect']) && is_numeric($_GET['twitter_app_redirect']) && $_GET['twitter_app_redirect'] > 0 )
		{
			do_action('fs_register_session');
			$appId = (int)$_GET['twitter_app_redirect'];

			$appInf = DB::fetch('apps' , ['id' => $appId , 'driver' => 'twitter']);
			if( !$appInf )
			{
				print 'Error!';
				exit();
			}

			$_SESSION['save_app_id'] = $appId;
			$_SESSION['fs_proxy_save'] = Request::get('proxy' , '' , 'string');

			try
			{
				$connection = new TwitterOAuth($appInf['app_key'], $appInf['app_secret'] , null ,null , $_SESSION['fs_proxy_save']);
				$request_token = $connection->oauth('oauth/request_token', array('oauth_callback' => TwitterLib::callbackURL()));
			}
			catch (\Exception $e)
			{
				print $e->getMessage();
				exit();
			}

			$_SESSION['oauth_token'] = $request_token['oauth_token'];
			$_SESSION['oauth_token_secret'] = $request_token['oauth_token_secret'];
			$url = $connection->url('oauth/authorize', array('oauth_token' => $request_token['oauth_token']));
			header('Location:' . $url);
			exit();
		}
	}

	public function linkedinCallback()
	{
		if( isset($_GET['linkedin_callback']) && $_GET['linkedin_callback'] == '1' )
		{
			Linkedin::getAccessToken();
		}
	}

	public function linkedinRedirect()
	{
		if( isset($_GET['linkedin_app_redirect']) && is_numeric($_GET['linkedin_app_redirect']) && $_GET['linkedin_app_redirect'] > 0 )
		{
			$appId = (int)$_GET['linkedin_app_redirect'];

			$link = Linkedin::getLoginURL($appId);
			header('Location: ' . $link);
			exit();
		}
	}

	public function pinterestCallback()
	{
		if( isset($_GET['pinterest_callback']) && $_GET['pinterest_callback'] == '1' )
		{
			Pinterest::getAccessToken();
		}
	}

	public function pinterestRedirect()
	{
		if( isset($_GET['pinterest_app_redirect']) && is_numeric($_GET['pinterest_app_redirect']) && $_GET['pinterest_app_redirect'] > 0 )
		{
			$appId = (int)$_GET['pinterest_app_redirect'];

			$link = Pinterest::getLoginURL($appId);
			header('Location: ' . $link);
			exit();
		}
	}

	public function redditCallback()
	{
		if( isset($_GET['reddit_callback']) && $_GET['reddit_callback'] == '1' )
		{
			Reddit::getAccessToken();
		}
	}

	public function redditRedirect()
	{
		if( isset($_GET['reddit_app_redirect']) && is_numeric($_GET['reddit_app_redirect']) && $_GET['reddit_app_redirect'] > 0 )
		{
			$appId = (int)$_GET['reddit_app_redirect'];

			$link = Reddit::getLoginURL($appId);
			header('Location: ' . $link);
			exit();
		}
	}

	public function tumblrCallback()
	{
		if( isset($_GET['tumblr_callback']) && $_GET['tumblr_callback'] == '1' )
		{
			Tumblr::getAccessToken();
		}
	}

	public function tumblrRedirect()
	{
		if( isset($_GET['tumblr_app_redirect']) && is_numeric($_GET['tumblr_app_redirect']) && $_GET['tumblr_app_redirect'] > 0 )
		{
			$appId = (int)$_GET['tumblr_app_redirect'];

			$link = Tumblr::getLoginURL($appId);
			header('Location: ' . $link);
			exit();
		}
	}

	public function okCallback()
	{
		if( isset($_GET['ok_callback']) && $_GET['ok_callback'] == '1' )
		{
			Odnoklassniki::getAccessToken();
		}
	}

	public function okRedirect()
	{
		if( isset($_GET['ok_app_redirect']) && is_numeric($_GET['ok_app_redirect']) && $_GET['ok_app_redirect'] > 0 )
		{
			$appId = (int)$_GET['ok_app_redirect'];

			$link = Odnoklassniki::getLoginURL($appId);
			header('Location: ' . $link);
			exit();
		}
	}

	public function mediumCallback()
	{
		if( isset($_GET['medium_callback']) && $_GET['medium_callback'] == '1' )
		{
			Medium::getAccessToken();
		}
	}

	public function mediumRedirect()
	{
		if( isset($_GET['medium_app_redirect']) && is_numeric($_GET['medium_app_redirect']) && $_GET['medium_app_redirect'] > 0 )
		{
			$appId = (int)$_GET['medium_app_redirect'];

			$link = Medium::getLoginURL($appId);
			header('Location: ' . $link);
			exit();
		}
	}

	public function standartFSApp()
	{
		$supportedFSApps	= ['fb', 'twitter' , 'linkedin' , 'pinterest' , 'reddit' , 'tumblr' , 'ok', 'medium'];

		$sn                 = Request::get('sn' , '' , 'string' , $supportedFSApps);
		$callback           = Request::get('fs_app_redirect' , '0' , 'num' , ['1']);
		$proxy              = Request::get('proxy' , '' , 'string');

		if( !$callback || empty($sn) )
			return;

		$appInf = DB::fetch('apps' , ['driver' => $sn , 'is_standart' => '1']);

		if( $sn == 'fb' )
		{
			$access_token	= Request::get('access_token' , '' , 'string');

			if( empty($access_token) )
				return;

			FacebookApi::authorizeFbUser($appInf['id'] , $access_token , $proxy);

			print esc_html__('Loading...' , 'fs-poster') . ' <script>if( typeof window.opener.compleateOperation == "function" ){ window.opener.compleateOperation(true);window.close();}else{document.write("'.esc_html__('Error! Please try again!' , 'fs-poster').'");} </script>';
			exit();
		}
		else if( $sn == 'twitter' )
		{
			$oauth_token = Request::get('oauth_token' , '' , 'string');
			$oauth_token_secret = Request::get('oauth_token_secret' ,'' , 'string');

			if( empty($oauth_token) || empty($oauth_token_secret) )
				return;

			TwitterLib::authorizeUser($appInf , $oauth_token , $oauth_token_secret , $proxy);

			print esc_html__('Loading...' , 'fs-poster') . ' <script>if( typeof window.opener.compleateOperation == "function" ){ window.opener.compleateOperation(true);window.close();}else{document.write("'.esc_html__('Error! Please try again!' , 'fs-poster').'");} </script>';
			exit();
		}
		else if( $sn == 'linkedin' )
		{
			$access_token = Request::get('access_token' , '' , 'string');
			$expire_in = Request::get('expire_in' ,'' , 'string');

			if( empty($access_token) || empty($expire_in) )
				return;

			Linkedin::authorizeLinkedinUser($appInf['id'] , $access_token , $expire_in , $proxy);

			print esc_html__('Loading...' , 'fs-poster') . ' <script>if( typeof window.opener.compleateOperation == "function" ){ window.opener.compleateOperation(true);window.close();}else{document.write("'.esc_html__('Error! Please try again!' , 'fs-poster').'");} </script>';
			exit();
		}
		else if( $sn == 'pinterest' )
		{
			$access_token = Request::get('access_token' , '' , 'string');

			if( empty($access_token) )
				return;

			Pinterest::authorizePinterestUser($appInf['id'] , $access_token , $proxy);

			print esc_html__('Loading...' , 'fs-poster') . ' <script>if( typeof window.opener.compleateOperation == "function" ){ window.opener.compleateOperation(true);window.close();}else{document.write("'.esc_html__('Error! Please try again!' , 'fs-poster').'");} </script>';
			exit();
		}
		else if( $sn == 'reddit' )
		{
			$access_token = Request::get('access_token' , '' , 'string');
			$refreshToken = Request::get('refresh_token' , '' , 'string');
			$expiresIn = Request::get('expires_in' , '' , 'string');

			if( empty($access_token) || empty($refreshToken) || empty($expiresIn) )
				return;

			Reddit::authorizeRedditUser( $appInf['id'] , $access_token , $refreshToken , $expiresIn , $proxy );

			print esc_html__('Loading...' , 'fs-poster') . ' <script>if( typeof window.opener.compleateOperation == "function" ){ window.opener.compleateOperation(true);window.close();}else{document.write("'.esc_html__('Error! Please try again!' , 'fs-poster').'");} </script>';
			exit();
		}
		else if( $sn == 'tumblr' )
		{
			$access_token = Request::get('access_token' , '' , 'string');
			$access_token_secret = Request::get('access_token_secret' , '' , 'string');

			if( empty($access_token) || empty($access_token_secret) )
				return;

			// sync timezone for FS Poster server...
			date_default_timezone_set('Asia/Baku');

			Tumblr::authorizeTumblrUser( $appInf['id'] , $appInf['app_key'] , $appInf['app_secret'] , $access_token , $access_token_secret , $proxy );

			print esc_html__('Loading...' , 'fs-poster') . ' <script>if( typeof window.opener.compleateOperation == "function" ){ window.opener.compleateOperation(true);window.close();}else{document.write("'.esc_html__('Error! Please try again!' , 'fs-poster').'");} </script>';
			exit();
		}
		else if( $sn == 'ok' )
		{
			$access_token = Request::get('access_token' , '' , 'string');
			$refreshToken = Request::get('refresh_token' , '' , 'string');
			$expiresIn = Request::get('expires_in' , '' , 'string');

			if( empty($access_token) || empty($refreshToken) || empty($expiresIn) )
				return;

			OdnoKlassniki::authorizeOkUser( $appInf['id'] , $appInf['app_key'] , $appInf['app_secret'] , $access_token , $refreshToken , $expiresIn , $proxy );

			print esc_html__('Loading...' , 'fs-poster') . ' <script>if( typeof window.opener.compleateOperation == "function" ){ window.opener.compleateOperation(true);window.close();}else{document.write("'.esc_html__('Error! Please try again!' , 'fs-poster').'");} </script>';
			exit();
		}
		else if( $sn == 'medium' )
		{
			$access_token = Request::get('access_token' , '' , 'string');
			$refreshToken = Request::get('refresh_token' , '' , 'string');
			$expiresIn = Request::get('expires_in' , '' , 'string');

			if( empty($access_token) || empty($refreshToken) || empty($expiresIn) )
				return;

			Medium::authorizeMediumUser( $appInf['id'] , $access_token , $refreshToken , $expiresIn , $proxy );

			print esc_html__('Loading...' , 'fs-poster') . ' <script>if( typeof window.opener.compleateOperation == "function" ){ window.opener.compleateOperation(true);window.close();}else{document.write("'.esc_html__('Error! Please try again!' , 'fs-poster').'");} </script>';
			exit();
		}
	}

}
