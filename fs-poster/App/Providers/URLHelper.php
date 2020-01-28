<?php

namespace FSPoster\App\Providers;

trait URLHelper
{

	/**
	 * @param $url
	 *
	 * @return string
	 */
	public static function shortenerURL( $url )
	{
		if( !get_option('fs_url_shortener', '0') )
		{
			return $url;
		}

		if( get_option('fs_shortener_service') == 'tinyurl' )
		{
			return self::shortURLtinyurl( $url );
		}
		else if( get_option('fs_shortener_service') == 'bitly' )
		{
			return self::shortURLbitly( $url );
		}

		return $url;
	}

	/**
	 * @param $url
	 *
	 * @return string
	 */
	public static function shortURLtinyurl( $url )
	{
		if( empty( $url ) )
		{
			return $url;
		}

		$data = Curl::getURL('https://tinyurl.com/api-create.php?url=' . $url);

		return $data;
	}

	/**
	 * @param $url
	 *
	 * @return string
	 */
	public static function shortURLbitly( $url )
	{
		$params = [
			'access_token'  =>  get_option('fs_url_short_access_token_bitly', ''),
			'longUrl'       =>  $url
		];

		if( empty( $params['access_token'] ) )
		{
			return $url;
		}

		$requestUrl = 'https://api-ssl.bit.ly/v3/shorten?' . http_build_query( $params );

		$result = json_decode( Curl::getURL( $requestUrl ), true);

		return isset($result['data']['url']) && !empty($result['data']['url']) ? $result['data']['url'] : $url;
	}

	/**
	 * @param $postId
	 * @param $driver
	 * @param string $username
	 * @param string $feedType
	 *
	 * @return string
	 */
	public static function postLink( $postId , $driver , $username = '', $feedType = '' )
	{
		if( $driver == 'fb' )
		{
			return 'https://fb.com/' . $postId;
		}
		else if( $driver == 'twitter' )
		{
			return 'https://twitter.com/' . $username . '/status/' . $postId;
		}
		else if( $driver == 'instagram' )
		{
			if( $feedType == 'story' )
			{
				return 'https://www.instagram.com/stories/' . $username . '/';
			}
			else
			{
				return 'https://www.instagram.com/p/' . $postId . '/';
			}
		}
		else if( $driver == 'linkedin' )
		{
			return 'https://www.linkedin.com/feed/update/' . $postId . '/';
			//return 'https://www.linkedin.com/updates?topic=' . $postId;
		}
		else if( $driver == 'vk' )
		{
			return 'https://vk.com/wall' . $postId;
		}
		else if( $driver == 'pinterest' )
		{
			return 'https://www.pinterest.com/pin/' . $postId;
		}
		else if( $driver == 'reddit' )
		{
			return 'https://www.reddit.com/' . $postId;
		}
		else if( $driver == 'tumblr' )
		{
			return 'https://'.$username.'.tumblr.com/post/' . $postId;
		}
		else if( $driver == 'ok' )
		{
			if( strpos( $postId , 'topic' ) !== false )
			{
				return 'https://ok.ru/group/' . $postId;
			}
			else
			{
				return 'https://ok.ru/profile/' . $postId;
			}
		}
		else if( $driver == 'google_b' )
		{
			return 'https://business.google.com/posts/l/' . esc_html($username);
		}
		else if( $driver == 'telegram' )
		{
			return "http://t.me/" . esc_html($username);
		}
		else if( $driver == 'medium' )
		{
			return "https://medium.com/p/" . esc_html($postId);
		}
	}

}