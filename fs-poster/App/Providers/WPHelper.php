<?php

namespace FSPoster\App\Providers;

trait WPHelper
{

	/**
	 * @param $productInf
	 * @param string $getType
	 *
	 * @return array|string
	 */
	public static function getProductPrice( $productInf, $getType = '' )
	{
		$productRegularPrice = '';
		$productSalePrice = '';
		$productId = $productInf['post_type'] == 'product_variation' ? $productInf['post_parent'] : $productInf['ID'];

		if( ($productInf['post_type'] == 'product' || $productInf['post_type'] == 'product_variation') && function_exists('wc_get_product') )
		{
			$product = wc_get_product( $productId );

			if( $product->is_type( 'variable' ) )
			{
				$variation_id			=	$product->get_children();
				$variable_product		=	new \WC_Product_Variation( reset($variation_id) );

				$productRegularPrice	=	$variable_product->get_regular_price();
				$productSalePrice		=	$variable_product->get_sale_price();
			}
			else //else if ( $product->is_type( 'simple' ) )
			{
				$productRegularPrice = $product->get_regular_price();
				$productSalePrice = $product->get_sale_price();
			}
		}

		if( empty($productRegularPrice) && $productSalePrice > $productRegularPrice )
		{
			$productRegularPrice = $productSalePrice;
		}

		if( $getType == 'price' )
		{
			return empty($productSalePrice) ? $productRegularPrice : $productSalePrice;
		}
		else if( $getType == 'regular' )
		{
			return $productRegularPrice;
		}
		else if( $getType == 'sale' )
		{
			return $productSalePrice;
		}
		else
		{
			return [
				'regular'	=>	$productRegularPrice,
				'sale'		=>	$productSalePrice
			];
		}
	}

	/**
	 * @param $postInf
	 *
	 * @return string
	 */
	public static function getPostTags( $postInf )
	{
		if( get_post_type( $postInf['ID'] ) == 'product' )
		{
			$tags = wp_get_post_terms( $postInf['ID'] ,'product_tag' );
		}
		else if( get_post_type( $postInf['ID'] ) == 'product_variation' )
		{
			$tags = wp_get_post_terms( $postInf['post_parent'] ,'product_tag' );
		}
		else
		{
			$tags = wp_get_post_tags( $postInf['ID'] );
		}


		$tagsString = [];
		foreach( $tags AS $tagInf )
		{
			$tagsString[] = '#' . preg_replace("/[\!\@\#\$\%\^\&\*\(\)\=\+\{\}\[\]\'\"\,\>\/\?\;\:\\\\\s]/" , "" , $tagInf->name);
		}
		$tagsString = implode(' ' , $tagsString);

		return strtolower( $tagsString );
	}

	/**
	 * @param $postId
	 *
	 * @return array|bool
	 */
	public static function getPostCatsArr( $postId )
	{
		$postType = get_post_type($postId);

		if( $postType == 'fs_post' || $postType == 'fs_post_tmp' )
		{
			return false;
		}
		else if( $postType == 'product' )
		{
			return wp_get_post_terms( $postId ,'product_cat' );
		}
		else
		{
			return get_the_category( $postId );
		}
	}

	/**
	 * @param $postInf
	 *
	 * @return string
	 */
	public static function getPostCats( $postInf )
	{
		if( get_post_type($postInf['ID']) == 'product' )
		{
			$cats = wp_get_post_terms( $postInf['ID'] ,'product_cat' );
		}
		else if( get_post_type($postInf['ID']) == 'product_variation' )
		{
			$cats = wp_get_post_terms( $postInf['post_parent'] ,'product_cat' );
		}
		else
		{
			$cats = get_the_category( $postInf['ID'] );
		}

		$catsString = [];
		foreach( $cats AS $catInf )
		{
			$catsString[] = '#' . preg_replace("/[\!\@\#\$\%\^\&\*\(\)\=\+\{\}\[\]\'\"\,\>\/\?\;\:\\\\\s]/" , "" , $catInf->name);
		}
		$catsString = implode(' ' , $catsString);


		return strtolower( $catsString );
	}

	/**
	 * @param $link
	 * @param $feedId
	 * @param array $postInf
	 * @param array $accountInf
	 *
	 * @return string
	 */
	public static function customizePostLink( $link , $feedId, $postInf = [], $accountInf = [] )
	{
		$parameters = [];

		if( get_option('fs_collect_statistics', '1') )
		{
			$parameters[] = 'feed_id=' . $feedId;
		}

		if( get_option('fs_unique_link', '1') == 1 )
		{
			$parameters[] = '_unique_id=' . uniqid();
		}

		$fs_url_additional = get_option('fs_url_additional', '');
		if( !empty( $fs_url_additional ) )
		{
			$postId		= isset($postInf['ID']) ? $postInf['ID'] : 0;
			$postTitle	= isset($postInf['post_title']) ? $postInf['post_title'] : '';
			$network	= isset($accountInf['driver']) ? $accountInf['driver'] : '-';

			$networks = [
				'fb'		=> ['FB', 'Facebook'],
				'twitter'	=> ['TW', 'Twitter'],
				'instagram'	=> ['IG', 'Instagram'],
				'linkedin'	=> ['LN', 'LinkedIn'],
				'vk'		=> ['VK', 'VKontakte'],
				'pinterest'	=> ['PI', 'Pinterest'],
				'reddit'	=> ['RE', 'Reddit'],
				'tumblr'	=> ['TU', 'Tumblr'],
				'ok'		=> ['OK', 'OK.ru'],
				'google_b'	=> ['GB', 'Google My Business'],
				'telegram'	=> ['TG', 'Telegram'],
				'medium'	=> ['ME', 'Medium'],
			];

			$networkCode	= isset($networks[$network]) ? $networks[$network][0] : '';
			$networkName	= isset($networks[$network]) ? $networks[$network][1] : '';

			$userInf		= get_userdata( $postInf['post_author'] );
			$accountName	= isset( $userInf->user_login ) ? $userInf->user_login : '-';

			$fs_url_additional = str_replace([
				'{post_id}',
				'{post_title}',
				'{network_name}',
				'{network_code}',
				'{account_name}',
				'{site_name}',
				'{uniq_id}'
			], [
				rawurlencode( $postId ),
				rawurlencode( $postTitle ),
				rawurlencode( $networkName ),
				rawurlencode( $networkCode ),
				rawurlencode( $accountName ),
				rawurlencode( get_bloginfo( 'name' ) ),
				uniqid( )
			], $fs_url_additional);

			$parameters[] = $fs_url_additional;
		}

		if( !empty( $parameters ) )
		{
			$link .= strpos($link , '?') !== false ? '' : '?';

			$parameters = implode('&', $parameters);

			$link .= $parameters;
		}

		return $link;
	}

	/**
	 * @param $postInf
	 * @param $feedId
	 * @param $accountInf
	 *
	 * @return string
	 */
	public static function getPostLink( $postInf, $feedId, $accountInf )
	{
		if( get_option('fs_share_custom_url', '0') )
		{
			$link = get_option('fs_custom_url_to_share', '{site_url}/?feed_id={feed_id}');

			$postId		= isset($postInf['ID']) ? $postInf['ID'] : 0;
			$postTitle	= isset($postInf['post_title']) ? $postInf['post_title'] : '';
			$network	= isset($accountInf['driver']) ? $accountInf['driver'] : '-';

			$networks = [
				'fb'		=> ['FB', 'Facebook'],
				'twitter'	=> ['TW', 'Twitter'],
				'instagram'	=> ['IG', 'Instagram'],
				'linkedin'	=> ['LN', 'LinkedIn'],
				'vk'		=> ['VK', 'VKontakte'],
				'pinterest'	=> ['PI', 'Pinterest'],
				'reddit'	=> ['RE', 'Reddit'],
				'tumblr'	=> ['TU', 'Tumblr'],
				'ok'		=> ['OK', 'OK.ru'],
				'google_b'	=> ['GB', 'Google My Business'],
				'telegram'	=> ['TG', 'Telegram'],
				'medium'	=> ['ME', 'Medium'],
			];

			$networkCode	= isset($networks[$network]) ? $networks[$network][0] : '';
			$networkName	= isset($networks[$network]) ? $networks[$network][1] : '';

			$userInf		= get_userdata( $postInf['post_author'] );
			$accountName	= isset( $userInf->user_login ) ? $userInf->user_login : '-';

			$link = str_replace([
				'{post_id}',
				'{feed_id}',
				'{post_title}',
				'{network_name}',
				'{network_code}',
				'{account_name}',
				'{site_name}',
				'{uniq_id}',
				'{site_url}',
				'{site_url_encoded}',
				'{post_url}',
				'{post_url_encoded}',
			], [
				rawurlencode( $postId ),
				rawurlencode( $feedId ),
				rawurlencode( $postTitle ),
				rawurlencode( $networkName ),
				rawurlencode( $networkCode ),
				rawurlencode( $accountName ),
				rawurlencode( get_bloginfo( 'name' ) ),
				uniqid( ),
				site_url(),
				rawurlencode( site_url() ),
				get_permalink( $postInf['ID'] ),
				rawurlencode( get_permalink( $postInf['ID'] ) )
			], $link);

			// custom fields
			$link = preg_replace_callback('/\{cf_(.+)\}/iU' , function($n) use( $postInf )
			{
				$customField = isset($n[1]) ? $n[1] : '';

				return rawurlencode( get_post_meta($postInf['ID'], $customField, true) );
			} , $link);
		}
		else
		{
			$link = get_permalink( $postInf['ID'] );
			$link = Helper::customizePostLink( $link , $feedId, $postInf, $accountInf );
		}

		return $link;
	}



}