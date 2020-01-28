<?php

namespace FSPoster\App\Providers;

use FSPoster\App\Lib\fb\FacebookCookieApi;
use FSPoster\App\Lib\fb\FacebookApi;
use FSPoster\App\Lib\google\GoogleMyBusiness;
use FSPoster\App\Lib\instagram\InstagramApi;
use FSPoster\App\Lib\linkedin\Linkedin;
use FSPoster\App\Lib\medium\Medium;
use FSPoster\App\Lib\ok\OdnoKlassniki;
use FSPoster\App\Lib\pinterest\Pinterest;
use FSPoster\App\Lib\reddit\Reddit;
use FSPoster\App\Lib\telegram\Telegram;
use FSPoster\App\Lib\tumblr\Tumblr;
use FSPoster\App\Lib\twitter\TwitterLib;
use FSPoster\App\Lib\vk\Vk;

class SocialNetworkPost
{

	public static function post( $feedId )
	{
		$feedInf    = DB::fetch("feeds" , $feedId);

		$postId                 = $feedInf['post_id'];
		$custom_post_message    = $feedInf['custom_post_message'];

		$nInf					= Helper::getAccessToken($feedInf['node_type'] , $feedInf['node_id']);

		$nodeProfileId			= $nInf['node_id'];
		$appId					= $nInf['app_id'];
		$driver					= $nInf['driver'];
		$accessToken			= $nInf['access_token'];
		$accessTokenSecret		= $nInf['access_token_secret'];
		$proxy					= $nInf['info']['proxy'];
		$options				= $nInf['options'];
		$accoundId				= $nInf['account_id'];

		$link           		= '';
		$message        		= '';
		$sendType       		= 'status';
		$images         		= null;
		$imagesLocale   		= null;
		$videoURL       		= null;
		$videoURLLocale 		= null;

		$postInf    			= get_post($postId , ARRAY_A);
		$postType   			= $postInf['post_type'];

		$postTitle				= $postInf['post_title'];

		$unlinkTmpFiles 		= [];

		if( $postType == 'attachment' && strpos($postInf['post_mime_type'] , 'image') !== false )
		{
			$sendType = 'image';
			$images[] = $postInf['guid'];
			$imagesLocale[] = get_attached_file( $postId );
		}
		else if( $postType == 'attachment' && strpos($postInf['post_mime_type'] , 'video') !== false )
		{
			$sendType = 'video';
			$videoURL = $postInf['guid'];
			$videoURLLocale = get_attached_file( $postId );
		}
		else
		{
			$sendType = 'link';
		}

		$shortLink = '';
		$longLink = '';

		if( $postType == 'fs_post' || $postType == 'fs_post_tmp' )
		{
			$message = Helper::spintax( $postInf['post_content'] );

			$link1 = get_post_meta( $postId, '_fs_link', true );
			$link1 = Helper::spintax( $link1 );
			$longLink = $link1;

			$mediaId = get_post_thumbnail_id( $postId );
			if( $mediaId > 0 )
			{
				$sendType = 'image';
				$url1 = wp_get_attachment_url($mediaId);
				$url2 = get_attached_file($mediaId);

				$images = [$url1];
				$imagesLocale = [$url2];
			}

			if( !empty($link1) )
			{
				if( get_option('fs_unique_link', '1') == 1 )
				{
					$link1 .= ( strpos($link1 , '?') === false ? '?' : '&' ) . '_unique_id=' . uniqid();
				}

				$link = $link1;
				$shortLink = Helper::shortenerURL( $link1 );
			}
		}
		else
		{
			$link = Helper::getPostLink( $postInf, $feedId, $nInf['info'] );

			if( empty( $custom_post_message ) )
			{
				$custom_post_message = get_option( 'fs_post_text_message_' . $driver . ( $driver == 'instagram' && $feedInf['feed_type'] == 'story' ? '_h' : '' ) , "{title}" );
			}

			$longLink = $link;
			$shortLink = Helper::shortenerURL( $link );

			$message = Helper::replaceTags( $custom_post_message , $postInf , $link , $shortLink );
			$message = Helper::spintax( $message );

			$link = $shortLink;
		}

		if( $driver != 'medium' )
		{
			$message = strip_tags( $message );
			$message = str_replace(['&nbsp;', "\r\n"], ['', "\n"], $message);
			//$message = preg_replace("/(\n\s*)+/", "\n", $message);
		}

		if( $driver == 'fb' )
		{
			$getMediaFuncName = empty( $options ) ? 'wp_get_attachment_url' : 'get_attached_file';

			if( $sendType != 'image' && $sendType != 'video' )
			{
				$pMethod = get_option('fs_facebook_posting_type', '1');
				if( $pMethod == '2' )
				{
					$mediaId = get_post_thumbnail_id($postId);

					if( empty($mediaId) )
					{
						$media = get_attached_media( 'image' , $postId);
						$first = reset($media);
						$mediaId = isset($first->ID) ? $first->ID : 0;
					}

					$url = $mediaId > 0 ? $getMediaFuncName($mediaId) : '';

					if( !empty($url) )
					{
						$sendType = 'image';
						$images = [$url];
					}
				}
				else if( $pMethod == '3' )
				{
					$images = [];

					$mediaId = get_post_thumbnail_id($postId);
					if( $mediaId > 0 )
					{
						$images[] = $getMediaFuncName( $mediaId );
					}

					if( $postType == 'product' || $postType == 'product_variation' )
					{
						$product = wc_get_product( $postId );
						$attachment_ids = $product->get_gallery_attachment_ids();

						foreach( $attachment_ids AS $attachmentId )
						{
							$_imageURL = $getMediaFuncName($attachmentId);
							if( !in_array( $_imageURL, $images ) )
							{
								$images[] = $_imageURL;
							}
						}
					}
					else
					{
						$allImgaes = get_attached_media( 'image' , $postId);
						foreach( $allImgaes AS $mediaInf )
						{
							$mediaId2 = isset($mediaInf->ID) ? $mediaInf->ID : 0;
							if( $mediaId2 > 0 )
							{
								$_imageURL = $getMediaFuncName($mediaId2);
								if( !in_array( $_imageURL, $images ) )
								{
									$images[] = $_imageURL;
								}
							}
						}
					}

					if( !empty($images) )
					{
						$sendType = 'image';
					}
				}
			}

			if( empty( $options ) ) // App method
			{
				$res = FacebookApi::sendPost($nodeProfileId , $sendType , $message , 0 , $link , $images , $videoURL , $accessToken , $proxy);
			}
			else // Cookie method
			{
				$fbDriver = new FacebookCookieApi( $accoundId, $options, $proxy );
				$res = $fbDriver->sendPost($nodeProfileId , $feedInf['node_type'], $sendType , $message , 0 , $link , $images , $videoURL);
			}

		}
		else if( $driver == 'instagram' )
		{
			if( $sendType != 'image' && $sendType != 'video' )
			{
				$mediaId = get_post_thumbnail_id($postId);

				if( empty($mediaId) )
				{
					$media = get_attached_media( 'image' , $postId);
					$first = reset($media);
					$mediaId = isset($first->ID) ? $first->ID : 0;
				}

				$url = $mediaId > 0 ? get_attached_file($mediaId) : '';

				if( !empty($url) )
				{
					if( file_exists( $url ) )
					{
						$sendType = 'image';
						$imagesLocale = [ $url ];
					}
					else
					{
						$tmpImage = tempnam(sys_get_temp_dir(), 'FS_tmpfile_');

						$url = $mediaId > 0 ? wp_get_attachment_url($mediaId) : '';
						Helper::downloadRemoteFile( $tmpImage, $url );

						$sendType = 'image';
						$imagesLocale = [ $tmpImage ];

						$unlinkTmpFiles[] = $tmpImage;
					}
				}
			}

			if( $feedInf['feed_type'] == 'story' )
			{
				$res = InstagramApi::sendStory($nInf['info'] , $sendType , $message , $link , $imagesLocale , $videoURLLocale);
			}
			else
			{
				$res = InstagramApi::sendPost($nInf['info'] , $sendType , $message , $link , $imagesLocale , $videoURLLocale);
			}
		}
		else if( $driver == 'linkedin' )
		{
			$res = Linkedin::sendPost( $accoundId , $nInf['info'] , $sendType , $message , $postInf['post_title'] , $link , $images , $videoURL , $accessToken , $proxy );
		}
		else if( $driver == 'vk' )
		{
			if( get_option('fs_vk_upload_image', '1') == 1 && $sendType != 'image' && $sendType != 'video' )
			{
				$mediaId = get_post_thumbnail_id($postId);

				if( empty($mediaId) )
				{
					$media = get_attached_media( 'image' , $postId);
					$first = reset($media);
					$mediaId = isset($first->ID) ? $first->ID : 0;
				}

				$url = $mediaId > 0 ? get_attached_file($mediaId) : '';

				if( !empty($url) )
				{
					if( file_exists( $url ) )
					{
						$sendType = 'image_link';
						$imagesLocale = [ $url ];
					}
					else
					{
						$tmpImage = tempnam(sys_get_temp_dir(), 'FS_tmpfile_');

						$url = $mediaId > 0 ? wp_get_attachment_url($mediaId) : '';
						Helper::downloadRemoteFile( $tmpImage, $url );

						$sendType = 'image';
						$imagesLocale = [ $tmpImage ];

						$unlinkTmpFiles[] = $tmpImage;
					}
				}
			}

			$res = Vk::sendPost($nodeProfileId , $sendType , $message , $link , $imagesLocale , $videoURLLocale , $accessToken , $proxy);
		}
		else if( $driver == 'pinterest' )
		{
			if( $sendType != 'image' && $sendType != 'video' )
			{
				$mediaId = get_post_thumbnail_id($postId);

				if( empty($mediaId) )
				{
					$media = get_attached_media( 'image' , $postId);
					$first = reset($media);
					$mediaId = isset($first->ID) ? $first->ID : 0;
				}

				$url = $mediaId > 0 ? wp_get_attachment_url($mediaId) : '';

				if( !empty($url) )
				{
					$sendType = 'image';
					$images = [$url];
				}
			}

			$res = Pinterest::sendPost( $nodeProfileId , $sendType , $message , $longLink , $images , $videoURL , $accessToken , $proxy);
		}
		else if( $driver == 'reddit' )
		{
			$res = Reddit::sendPost($nInf['info'] , $sendType , $postTitle , $message , $longLink , $images , $videoURL , $accessToken , $proxy);
		}
		else if( $driver == 'tumblr' )
		{
			if( $sendType != 'image' && $sendType != 'video' )
			{
				$mediaId = get_post_thumbnail_id($postId);

				if( empty($mediaId) )
				{
					$media = get_attached_media( 'image' , $postId);
					$first = reset($media);
					$mediaId = isset($first->ID) ? $first->ID : 0;
				}

				$url = $mediaId > 0 ? wp_get_attachment_url($mediaId) : '';

				if( !empty($url) )
				{
					$sendType = 'image';
					$images = [$url];
				}

				// for <img> tag... link post type
				$imagesLocale = $images;
			}

			$res = Tumblr::sendPost($nInf['info'] , $sendType , $postTitle , $message , $link , $imagesLocale , $videoURLLocale , $accessToken , $accessTokenSecret , $appId , $proxy);
		}
		else if( $driver == 'twitter' )
		{
			if( $sendType != 'image' && $sendType != 'video' )
			{
				$pMethod = get_option('fs_twitter_posting_type', '1');
				if( $pMethod == '2' )
				{
					$mediaId = get_post_thumbnail_id($postId);

					if( empty($mediaId) )
					{
						$media = get_attached_media( 'image' , $postId);
						$first = reset($media);
						$mediaId = isset($first->ID) ? $first->ID : 0;
					}

					$url = $mediaId > 0 ? get_attached_file($mediaId) : '';

					if( !empty($url) )
					{
						if( file_exists( $url ) )
						{
							$sendType = 'image';
							$imagesLocale = [ $url ];
						}
						else
						{
							$tmpImage = tempnam(sys_get_temp_dir(), 'FS_tmpfile_');

							$url = $mediaId > 0 ? wp_get_attachment_url($mediaId) : '';
							Helper::downloadRemoteFile( $tmpImage, $url );

							$sendType = 'image';
							$imagesLocale = [ $tmpImage ];

							$unlinkTmpFiles[] = $tmpImage;
						}
					}
				}
				else if( $pMethod == '3' )
				{
					$imagesLocale = [];

					$mediaId = get_post_thumbnail_id($postId);
					if( $mediaId > 0 )
					{
						$imagesLocale[] = get_attached_file( $mediaId );
					}

					if( $postType == 'product' || $postType == 'product_variation' )
					{
						$product = wc_get_product( $postId );
						$attachment_ids = $product->get_gallery_attachment_ids();

						foreach( $attachment_ids AS $attachmentId )
						{
							$_imagePath = get_attached_file($attachmentId);
							if( !in_array( $_imagePath, $imagesLocale ) )
							{
								$imagesLocale[] = $_imagePath;
							}
						}
					}
					else
					{
						$allImgaes = get_attached_media( 'image' , $postId);
						foreach( $allImgaes AS $mediaInf )
						{
							$mediaId2 = isset($mediaInf->ID) ? $mediaInf->ID : 0;
							if( $mediaId2 > 0 )
							{
								$_imagePath = get_attached_file($mediaId2);
								if( !in_array( $_imagePath, $imagesLocale ) )
								{
									$imagesLocale[] = $_imagePath;
								}
							}
						}
					}

					if( !empty($imagesLocale) )
					{
						$sendType = 'image';
					}
				}
			}

			if( get_option('fs_twitter_auto_cut_tweets', '1') == 1 )
			{
				$limit = 280 - mb_strlen("\n" . $link , 'UTF-8');
				if( $limit < mb_strlen($message , 'UTF-8') )
				{
					$limit -= 3;
					$message = mb_substr($message , 0, $limit, 'UTF-8') . '...';
				}
			}

			$res = TwitterLib::sendPost($appId , $sendType , $message , $link , $imagesLocale , $videoURLLocale , $accessToken , $accessTokenSecret , $proxy);
		}
		else if( $driver == 'ok' )
		{
			if( $sendType != 'image' && $sendType != 'video' )
			{
				$pMethod = get_option('fs_ok_posting_type', '1');
				if( $pMethod == '2' )
				{
					$mediaId = get_post_thumbnail_id($postId);

					if( empty($mediaId) )
					{
						$media = get_attached_media( 'image' , $postId);
						$first = reset($media);
						$mediaId = isset($first->ID) ? $first->ID : 0;
					}

					$url = $mediaId > 0 ? get_attached_file($mediaId) : '';

					if( !empty($url) )
					{
						if( file_exists( $url ) )
						{
							$sendType = 'image';
							$imagesLocale = [ $url ];
						}
						else
						{
							$tmpImage = tempnam(sys_get_temp_dir(), 'FS_tmpfile_');

							$url = $mediaId > 0 ? wp_get_attachment_url($mediaId) : '';
							Helper::downloadRemoteFile( $tmpImage, $url );

							$sendType = 'image';
							$imagesLocale = [ $tmpImage ];

							$unlinkTmpFiles[] = $tmpImage;
						}
					}
				}
				else if( $pMethod == '3' )
				{
					$imagesLocale = [];

					$mediaId = get_post_thumbnail_id($postId);
					if( $mediaId > 0 )
					{
						$imagesLocale[] = get_attached_file( $mediaId );
					}

					if( $postType == 'product' || $postType == 'product_variation' )
					{
						$product = wc_get_product( $postId );
						$attachment_ids = $product->get_gallery_attachment_ids();

						foreach( $attachment_ids AS $attachmentId )
						{
							$_imagePath = get_attached_file($attachmentId);
							if( !in_array( $_imagePath, $imagesLocale ) )
							{
								$imagesLocale[] = $_imagePath;
							}
						}
					}
					else
					{
						$allImgaes = get_attached_media( 'image' , $postId);
						foreach( $allImgaes AS $mediaInf )
						{
							$mediaId2 = isset($mediaInf->ID) ? $mediaInf->ID : 0;
							if( $mediaId2 > 0 )
							{
								$_imagePath = get_attached_file($mediaId2);
								if( !in_array( $_imagePath, $imagesLocale ) )
								{
									$imagesLocale[] = $_imagePath;
								}
							}
						}
					}

					if( !empty($imagesLocale) )
					{
						$sendType = 'image';
					}
				}
			}

			$appInf = DB::fetch('apps' , ['id' => $appId]);

			$res = OdnoKlassniki::sendPost($nInf['info'] , $sendType , $message , $link , $imagesLocale , $videoURLLocale , $accessToken, $appInf['app_key'] , $appInf['app_secret'] , $proxy);
		}
		else if( $driver == 'google_b' )
		{
			if( $sendType == 'video' )
			{
				$res = [ 'status' => 'error' , 'error_msg' => 'Google My Business doesn\'t support video type' ];
			}
			else
			{
				if( $sendType != 'image' )
				{
					$mediaId = get_post_thumbnail_id($postId);

					if( empty($mediaId) )
					{
						$media = get_attached_media( 'image' , $postId);
						$first = reset($media);
						$mediaId = isset($first->ID) ? $first->ID : 0;
					}

					$url = $mediaId > 0 ? wp_get_attachment_url($mediaId) : '';

					if( !empty($url) )
					{
						$sendType = 'image';
						$images = [$url];
					}
				}

				$imageUrl = is_array( $images ) ? reset($images) : '';

				$options		= json_decode( $options, true );
				$cookie_sid		= isset($options['sid']) ? $options['sid'] : '';
				$cookie_hsid	= isset($options['hsid']) ? $options['hsid'] : '';
				$cookie_ssid	= isset($options['ssid']) ? $options['ssid'] : '';

				$linkButton		= get_option('fs_google_b_button_type', 'LEARN_MORE');

				$fs_google_b_share_as_product = ($postType == 'product' || $postType == 'product_variation') && get_option('fs_google_b_share_as_product', '1');

				$productName		= $fs_google_b_share_as_product ? $postTitle : null;
				$productPrice		= $fs_google_b_share_as_product ? Helper::getProductPrice( $postInf, 'price' ) : null;
				$productCurrency	= $fs_google_b_share_as_product ? get_woocommerce_currency( ) : null;

				$google = new GoogleMyBusiness( $cookie_sid, $cookie_hsid, $cookie_ssid, $proxy );
				$res = $google->sendPost( $nodeProfileId , $message , $link , $linkButton, $imageUrl, $productName, $productPrice, $productCurrency );
			}
		}
		else if( $driver == 'telegram' )
		{
			$fs_telegram_type_of_sharing = get_option('fs_telegram_type_of_sharing', '1');

			if( ($fs_telegram_type_of_sharing == '1' || $fs_telegram_type_of_sharing == '4') && !empty( $link ) )
			{
				$message .= "\n" . $link;
			}

			if( ( $fs_telegram_type_of_sharing == '3'  || $fs_telegram_type_of_sharing == '4' ) && $sendType != 'image' && $sendType != 'video' )
			{
				$mediaId = get_post_thumbnail_id($postId);

				if( empty($mediaId) )
				{
					$media = get_attached_media( 'image' , $postId);
					$first = reset($media);
					$mediaId = isset($first->ID) ? $first->ID : 0;
				}

				$url = $mediaId > 0 ? wp_get_attachment_url($mediaId) : '';

				if( !empty($url) )
				{
					$sendType = 'image';
					$images = [$url];
				}
			}

			if( $sendType == 'image' )
			{
				$mediaURL = reset( $images );
			}
			else if( $sendType == 'video' )
			{
				$mediaURL = $videoURL;
			}
			else
			{
				$mediaURL = '';
			}

			$tg = new Telegram( $options, $proxy );
			$res = $tg->sendPost( $nodeProfileId , $message , $sendType, $mediaURL );
		}
		else if( $driver == 'medium' )
		{
			$res = Medium::sendPost($nInf['info'] , $postTitle , $message , $accessToken , $proxy);
		}
		else
		{
			$res = ['status' => 'error' , 'error_msg' => 'Driver error! Driver type: ' . htmlspecialchars($driver)];
		}

		foreach ( $unlinkTmpFiles AS $unlinkFile )
		{
			if( file_exists( $unlinkFile ) )
			{
				unlink( $unlinkFile );
			}
		}

		$udpateDate = [
			'is_sended'         => 1,
			'send_time'         => current_time('Y-m-d H:i:s'),
			'status'            => $res['status'],
			'error_msg'         => isset($res['error_msg']) ? Helper::cutText($res['error_msg'], 250) : '',
			'driver_post_id'    => isset($res['id']) ? $res['id'] : null,
			'driver_post_id2'   => isset($res['id2']) ? $res['id2'] : null
		];

		if( !get_option('fs_keep_logs', '1') )
		{
			DB::DB()->delete(DB::table('feeds') , ['id' => $feedId]);
		}
		else
		{
			DB::DB()->update(DB::table('feeds') , $udpateDate , ['id' => $feedId]);
		}

		if( isset($res['id']) )
		{
			if( $driver == 'google_b' )
			{
				$username = $nodeProfileId;
			}
			else
			{
				$username = isset($nInf['info']['screen_name']) ? $nInf['info']['screen_name'] : $nInf['username'];
			}

			$res['post_link'] = Helper::postLink($res['id'] , $driver , $username, $feedInf['feed_type']);
		}

		return $res;
	}

}