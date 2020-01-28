<?php

namespace FSPoster\App\Providers;

use Abraham\TwitterOAuth\TwitterOAuth;
use FSPoster\App\Lib\medium\Medium;
use FSPoster\App\Lib\ok\OdnoKlassniki;
use FSPoster\App\Lib\reddit\Reddit;

/**
 * Class Helper
 * @package FSPoster\App\Providers
 */
class Helper
{
	use WPHelper,
		URLHelper;

	/**
	 * @var mixed
	 */
	private static $plugin_disabled;

	/**
	 * @return bool
	 */
	public static function pluginDisabled()
	{
		if( is_null( self::$plugin_disabled ) )
		{
			self::$plugin_disabled = get_option('fs_plugin_disabled', '0');
		}

		return self::$plugin_disabled > 0;
	}

	/**
	 * @param $status
	 * @param array $arr
	 */
	public static function response($status , $arr = [])
	{
		$arr = is_array($arr) ? $arr : ( is_string($arr) ? ['error_msg' => $arr] : [] );

		if( $status )
		{
			$arr['status'] = 'ok';
		}
		else
		{
			$arr['status'] = 'error';
			if( !isset($arr['error_msg']) )
			{
				$arr['error_msg'] = 'Error!';
			}
		}

		print json_encode($arr);
		exit();
	}

	/**
	 * @param $view
	 * @param array $parameters
	 */
	public static function view( $view , $parameters = [] )
	{
		$view = str_replace('.', DIRECTORY_SEPARATOR, $view);

		require FS_ROOT_DIR . '/App/view/' . $view . '.php';
	}

	/**
	 * @param $view
	 * @param array $parameters
	 */
	public static function modalView( $view , $parameters = [] )
	{
		$mn = Request::post('_mn', 0, 'int');

		if( strpos( $view, '..' ) !== 0 )
		{
			$view = str_replace('.', DIRECTORY_SEPARATOR, $view);
		}

		ob_start();
		require FS_ROOT_DIR . '/App/view/modals/' . $view . '.php';
		$viewOutput = ob_get_clean();

		Helper::response(true , [
			'html' => htmlspecialchars($viewOutput)
		]);
	}

	/**
	 * @param $text
	 *
	 * @return string
	 */
	public static function spintax( $text )
	{
		$text = is_string($text) ? (string)$text : '';

		return preg_replace_callback(
			'/\{(((?>[^\{\}]+)|(?R))*)\}/x',
			function ($text)
			{
				$text = Helper::spintax( $text[1] );
				$parts = explode('|', $text);

				return $parts[ array_rand($parts) ];
			},
			$text
		);
	}

	/**
	 *
	 */
	public static function registerSession()
	{
		if( !session_id() )
		{
			session_start();
		}
	}

	/**
	 *
	 */
	public static function destroySession()
	{
		session_destroy();
	}

	/**
	 * @param $text
	 * @param int $n
	 *
	 * @return string
	 */
	public static function cutText( $text , $n = 35 )
	{
		return mb_strlen($text , 'UTF-8') > $n ? mb_substr($text , 0 , $n , 'UTF-8') . '...' : $text;
	}

	/**
	 * @param bool $response
	 *
	 * @return array
	 */
	public static function checkRequirments( $response = true )
	{
		if( !ini_get('allow_url_fopen') )
		{
			$errMsg = esc_html__('"allow_url_fopen" disabled in your php.ini settings! Please activate it and try again!' , 'fs-poster');

			if( $response )
			{
				Helper::response(false , $errMsg );
			}
			else
			{
				return [ false, $errMsg ];
			}
		}

		return [ true ];
	}

	/**
	 * @return string
	 */
	public static function getVersion()
	{
		$plugin_data = get_file_data(FS_ROOT_DIR . '/init.php' , array('Version' => 'Version') , false);

		return isset($plugin_data['Version']) ? $plugin_data['Version'] : '1.0.0';
	}

	/**
	 * @return string
	 */
	public static function getInstalledVersion()
	{
		$ver = get_option('fs_poster_plugin_installed' , '1.0.0');

		return ( $ver === '1' || empty($ver) ) ? '1.0.0' : $ver;
	}

	/**
	 *
	 */
	public static function debug()
	{
		error_reporting(E_ALL);
		ini_set('display_errors' , 'on');
	}

	/**
	 * @return string
	 */
	public static function fetchStatisticOptions()
	{
		$getOptions = Curl::getURL( FS_API_URL . 'api.php?act=statistic_option' );
		$getOptions = json_decode($getOptions, true);

		$options = '<option selected disabled>Please select</option>';
		foreach ( $getOptions AS $optionName => $optionValue )
		{
			$options .= '<option value="' . htmlspecialchars($optionName) . '">' . htmlspecialchars($optionValue) . '</option>';
		}

		return $options;
	}

	public static function hexToRgb( $hex )
	{
		if( strpos('#', $hex) === 0 )
			$hex = substr($hex, 1);

		return sscanf($hex, "%02x%02x%02x");
	}

	/**
	 * @param $destination
	 * @param $sourceURL
	 */
	public static function downloadRemoteFile( $destination, $sourceURL )
	{
		file_put_contents( $destination, Curl::getURL( $sourceURL ) );
	}

	/**
	 * @param $optionName
	 * @param null $default
	 *
	 * @return mixed
	 */
	public static function getOption( $optionName, $default = null )
	{
		return get_option( 'fs_' . $optionName, $default );
	}

	/**
	 * @param $optionName
	 * @param $optionValue
	 *
	 * @return bool
	 */
	public static function setOption( $optionName, $optionValue )
	{
		return update_option( 'fs_' . $optionName, $optionValue );
	}

	/**
	 * @param $optionName
	 *
	 * @return bool
	 */
	public static function deleteOption( $optionName )
	{
		return delete_option( 'fs_' . $optionName );
	}

	/**
	 *
	 */
	public static function removePlugin()
	{
		$fsPurchaseKey = get_option('fs_poster_plugin_purchase_key' , '');

		$checkPurchaseCodeURL = FS_API_URL . "api.php?act=delete&purchase_code=" . urlencode($fsPurchaseKey) . "&domain=" . site_url();

		$result2 = '{"status":"ok","sql":"IyBGSVggU0xFRVBUSU1FIC0gW0JhYmlhdG8gbGdva3VsXSAtIEZTLVBvc3RlciAzLjQueA0KDQpDUkVBVEUgVEFCTEUgYHt0YWJsZXByZWZpeH1hY2NvdW50c2AgKA0KICBgaWRgIGludCgxMSkgTk9UIE5VTEwsDQogIGB1c2VyX2lkYCBpbnQoMTEpIERFRkFVTFQgTlVMTCwNCiAgYGRyaXZlcmAgdmFyY2hhcig1MCkgQ09MTEFURSB1dGY4bWI0X3VuaWNvZGVfY2kgREVGQVVMVCBOVUxMLA0KICBgbmFtZWAgdmFyY2hhcigyNTUpIENPTExBVEUgdXRmOG1iNF91bmljb2RlX2NpIERFRkFVTFQgTlVMTCwNCiAgYHByb2ZpbGVfaWRgIHZhcmNoYXIoNTApIENPTExBVEUgdXRmOG1iNF91bmljb2RlX2NpIERFRkFVTFQgTlVMTCwNCiAgYGVtYWlsYCB2YXJjaGFyKDI1NSkgQ09MTEFURSB1dGY4bWI0X3VuaWNvZGVfY2kgREVGQVVMVCBOVUxMLA0KICBgZ2VuZGVyYCB0aW55aW50KDQpIERFRkFVTFQgTlVMTCwNCiAgYGJpcnRoZGF5YCBkYXRlIERFRkFVTFQgTlVMTCwNCiAgYHByb3h5YCB2YXJjaGFyKDI1NSkgQ09MTEFURSB1dGY4bWI0X3VuaWNvZGVfY2kgREVGQVVMVCBOVUxMLA0KICBgaXNfcHVibGljYCB0aW55aW50KDEpIERFRkFVTFQgJzEnLA0KICBgdXNlcm5hbWVgIHZhcmNoYXIoMTAwKSBDT0xMQVRFIHV0ZjhtYjRfdW5pY29kZV9jaSBERUZBVUxUIE5VTEwsDQogIGBwYXNzd29yZGAgdmFyY2hhcigyNTUpIENPTExBVEUgdXRmOG1iNF91bmljb2RlX2NpIERFRkFVTFQgTlVMTCwNCiAgYGZvbGxvd2Vyc19jb3VudGAgdmFyY2hhcigyNTUpIENPTExBVEUgdXRmOG1iNF91bmljb2RlX2NpIERFRkFVTFQgTlVMTCwNCiAgYGZyaWVuZHNfY291bnRgIHZhcmNoYXIoMjU1KSBDT0xMQVRFIHV0ZjhtYjRfdW5pY29kZV9jaSBERUZBVUxUIE5VTEwsDQogIGBsaXN0ZWRfY291bnRgIHZhcmNoYXIoMjU1KSBDT0xMQVRFIHV0ZjhtYjRfdW5pY29kZV9jaSBERUZBVUxUIE5VTEwsDQogIGBwcm9maWxlX3BpY2AgdmFyY2hhcigyNTUpIENPTExBVEUgdXRmOG1iNF91bmljb2RlX2NpIERFRkFVTFQgTlVMTCwNCiAgYG9wdGlvbnNgIHZhcmNoYXIoMTAwMCkgQ09MTEFURSB1dGY4bWI0X3VuaWNvZGVfY2kgREVGQVVMVCBOVUxMDQopIEVOR0lORT1Jbm5vREIgREVGQVVMVCBDSEFSU0VUPXV0ZjhtYjQgQ09MTEFURT11dGY4bWI0X3VuaWNvZGVfY2kgUk9XX0ZPUk1BVD1DT01QQUNUOw0KDQpDUkVBVEUgVEFCTEUgYHt0YWJsZXByZWZpeH1hY2NvdW50X2FjY2Vzc190b2tlbnNgICgNCiAgYGlkYCBpbnQoMTEpIE5PVCBOVUxMLA0KICBgYWNjb3VudF9pZGAgaW50KDExKSBERUZBVUxUIE5VTEwsDQogIGBhcHBfaWRgIGludCgxMSkgREVGQVVMVCBOVUxMLA0KICBgZXhwaXJlc19vbmAgZGF0ZXRpbWUgREVGQVVMVCBOVUxMLA0KICBgYWNjZXNzX3Rva2VuYCB2YXJjaGFyKDI1MDApIENPTExBVEUgdXRmOG1iNF91bmljb2RlX2NpIERFRkFVTFQgTlVMTCwNCiAgYGFjY2Vzc190b2tlbl9zZWNyZXRgIHZhcmNoYXIoNzUwKSBDT0xMQVRFIHV0ZjhtYjRfdW5pY29kZV9jaSBERUZBVUxUIE5VTEwsDQogIGByZWZyZXNoX3Rva2VuYCB2YXJjaGFyKDEwMDApIENPTExBVEUgdXRmOG1iNF91bmljb2RlX2NpIERFRkFVTFQgTlVMTA0KKSBFTkdJTkU9SW5ub0RCIERFRkFVTFQgQ0hBUlNFVD11dGY4bWI0IENPTExBVEU9dXRmOG1iNF91bmljb2RlX2NpIFJPV19GT1JNQVQ9Q09NUEFDVDsNCg0KQ1JFQVRFIFRBQkxFIGB7dGFibGVwcmVmaXh9YWNjb3VudF9ub2Rlc2AgKA0KICBgaWRgIGludCgxMSkgTk9UIE5VTEwsDQogIGB1c2VyX2lkYCBpbnQoMTEpIERFRkFVTFQgTlVMTCwNCiAgYGFjY291bnRfaWRgIGludCgxMSkgREVGQVVMVCBOVUxMLA0KICBgbm9kZV90eXBlYCB2YXJjaGFyKDIwKSBDT0xMQVRFIHV0ZjhtYjRfdW5pY29kZV9jaSBERUZBVUxUIE5VTEwsDQogIGBub2RlX2lkYCB2YXJjaGFyKDMwKSBDT0xMQVRFIHV0ZjhtYjRfdW5pY29kZV9jaSBERUZBVUxUIE5VTEwsDQogIGBhY2Nlc3NfdG9rZW5gIHZhcmNoYXIoMTAwMCkgQ09MTEFURSB1dGY4bWI0X3VuaWNvZGVfY2kgREVGQVVMVCBOVUxMLA0KICBgbmFtZWAgdmFyY2hhcigzNTApIENPTExBVEUgdXRmOG1iNF91bmljb2RlX2NpIERFRkFVTFQgTlVMTCwNCiAgYGFkZGVkX2RhdGVgIHRpbWVzdGFtcCBOVUxMIERFRkFVTFQgQ1VSUkVOVF9USU1FU1RBTVAsDQogIGBjYXRlZ29yeWAgdmFyY2hhcigyNTUpIENPTExBVEUgdXRmOG1iNF91bmljb2RlX2NpIERFRkFVTFQgTlVMTCwNCiAgYGZhbl9jb3VudGAgYmlnaW50KDIwKSBERUZBVUxUIE5VTEwsDQogIGBpc19wdWJsaWNgIHRpbnlpbnQoMSkgREVGQVVMVCAnMScsDQogIGBjb3ZlcmAgdmFyY2hhcig3NTApIENPTExBVEUgdXRmOG1iNF91bmljb2RlX2NpIERFRkFVTFQgTlVMTCwNCiAgYGRyaXZlcmAgdmFyY2hhcig1MCkgQ09MTEFURSB1dGY4bWI0X3VuaWNvZGVfY2kgREVGQVVMVCBOVUxMLA0KICBgc2NyZWVuX25hbWVgIHZhcmNoYXIoMzUwKSBDT0xMQVRFIHV0ZjhtYjRfdW5pY29kZV9jaSBERUZBVUxUIE5VTEwNCikgRU5HSU5FPUlubm9EQiBERUZBVUxUIENIQVJTRVQ9dXRmOG1iNCBDT0xMQVRFPXV0ZjhtYjRfdW5pY29kZV9jaSBST1dfRk9STUFUPUNPTVBBQ1Q7DQoNCkNSRUFURSBUQUJMRSBge3RhYmxlcHJlZml4fWFwcHNgICgNCiAgYGlkYCBpbnQoMTEpIE5PVCBOVUxMLA0KICBgdXNlcl9pZGAgaW50KDExKSBERUZBVUxUIE5VTEwsDQogIGBkcml2ZXJgIHZhcmNoYXIoNTApIENPTExBVEUgdXRmOG1iNF91bmljb2RlX2NpIERFRkFVTFQgTlVMTCwNCiAgYGFwcF9pZGAgdmFyY2hhcigyMDApIENPTExBVEUgdXRmOG1iNF91bmljb2RlX2NpIERFRkFVTFQgTlVMTCwNCiAgYGFwcF9zZWNyZXRgIHZhcmNoYXIoMjAwKSBDT0xMQVRFIHV0ZjhtYjRfdW5pY29kZV9jaSBERUZBVUxUIE5VTEwsDQogIGBhcHBfa2V5YCB2YXJjaGFyKDIwMCkgQ09MTEFURSB1dGY4bWI0X3VuaWNvZGVfY2kgREVGQVVMVCBOVUxMLA0KICBgYXBwX2F1dGhlbnRpY2F0ZV9saW5rYCB2YXJjaGFyKDIwMDApIENPTExBVEUgdXRmOG1iNF91bmljb2RlX2NpIERFRkFVTFQgTlVMTCwNCiAgYGlzX3B1YmxpY2AgdGlueWludCgxKSBERUZBVUxUIE5VTEwsDQogIGBuYW1lYCB2YXJjaGFyKDI1NSkgQ09MTEFURSB1dGY4bWI0X3VuaWNvZGVfY2kgREVGQVVMVCBOVUxMLA0KICBgaXNfc3RhbmRhcnRgIHRpbnlpbnQoMSkgREVGQVVMVCAnMCcNCikgRU5HSU5FPUlubm9EQiBERUZBVUxUIENIQVJTRVQ9dXRmOG1iNCBDT0xMQVRFPXV0ZjhtYjRfdW5pY29kZV9jaSBST1dfRk9STUFUPUNPTVBBQ1Q7DQoNCklOU0VSVCBJTlRPIGB7dGFibGVwcmVmaXh9YXBwc2AgKGBpZGAsIGB1c2VyX2lkYCwgYGRyaXZlcmAsIGBhcHBfaWRgLCBgYXBwX3NlY3JldGAsIGBhcHBfa2V5YCwgYGFwcF9hdXRoZW50aWNhdGVfbGlua2AsIGBpc19wdWJsaWNgLCBgbmFtZWAsIGBpc19zdGFuZGFydGApIFZBTFVFUw0KKDEsIDAsICdmYicsICc2NjI4NTY4Mzc5JywgJ2MxZTYyMGZhNzA4YTFkNTY5NmZiOTkxYzFiZGU1NjYyJywgJzNlN2M3OGUzNWE3NmE5Mjk5MzA5ODg1MzkzYjAyZDk3JywgTlVMTCwgMSwgJ0ZhY2Vib29rIGZvciBpUGhvbmUnLCAyKSwNCigyLCAwLCAnZmInLCAnMzUwNjg1NTMxNzI4JywgJzYyZjhjZTlmNzRiMTJmODRjMTIzY2MyMzQzN2E0YTMyJywgJzg4MmE4NDkwMzYxZGE5ODcwMmJmOTdhMDIxZGRjMTRkJywgTlVMTCwgMSwgJ0ZhY2Vib29rIGZvciBBbmRyb2lkJywgMiksDQooMywgTlVMTCwgJ2ZiJywgJzE5MzI3ODEyNDA0ODgzMycsIE5VTEwsIE5VTEwsICdodHRwczovL3d3dy5mYWNlYm9vay5jb20vdjIuOC9kaWFsb2cvb2F1dGg/cmVkaXJlY3RfdXJpPWZiY29ubmVjdDovL3N1Y2Nlc3Mmc2NvcGU9ZW1haWwscGFnZXNfc2hvd19saXN0LHB1YmxpY19wcm9maWxlLHVzZXJfYmlydGhkYXkscHVibGlzaF9hY3Rpb25zLG1hbmFnZV9wYWdlcyxwdWJsaXNoX3BhZ2VzLHVzZXJfbWFuYWdlZF9ncm91cHMmcmVzcG9uc2VfdHlwZT10b2tlbixjb2RlJmNsaWVudF9pZD0xOTMyNzgxMjQwNDg4MzMnLCAxLCAnSFRDIFNlbnNlJywgMyksDQooNCwgTlVMTCwgJ2ZiJywgJzE0NTYzNDk5NTUwMTg5NScsIE5VTEwsIE5VTEwsICdodHRwczovL3d3dy5mYWNlYm9vay5jb20vdjEuMC9kaWFsb2cvb2F1dGg/cmVkaXJlY3RfdXJpPWh0dHBzOi8vd3d3LmZhY2Vib29rLmNvbS9jb25uZWN0L2xvZ2luX3N1Y2Nlc3MuaHRtbCZzY29wZT1lbWFpbCxwYWdlc19zaG93X2xpc3QscHVibGljX3Byb2ZpbGUsdXNlcl9iaXJ0aGRheSxwdWJsaXNoX2FjdGlvbnMsbWFuYWdlX3BhZ2VzLHB1Ymxpc2hfcGFnZXMsdXNlcl9tYW5hZ2VkX2dyb3VwcyZyZXNwb25zZV90eXBlPXRva2VuLGNvZGUmY2xpZW50X2lkPTE0NTYzNDk5NTUwMTg5NScsIDEsICdHcmFwaCBBUEkgZXhwbG9yZXInLCAzKSwNCig1LCBOVUxMLCAnZmInLCAnMTc0ODI5MDAzMzQ2JywgTlVMTCwgTlVMTCwgJ2h0dHBzOi8vd3d3LmZhY2Vib29rLmNvbS92MS4wL2RpYWxvZy9vYXV0aD9yZWRpcmVjdF91cmk9aHR0cHM6Ly93d3cuZmFjZWJvb2suY29tL2Nvbm5lY3QvbG9naW5fc3VjY2Vzcy5odG1sJnNjb3BlPWVtYWlsLHBhZ2VzX3Nob3dfbGlzdCxwdWJsaWNfcHJvZmlsZSx1c2VyX2JpcnRoZGF5LHB1Ymxpc2hfYWN0aW9ucyxtYW5hZ2VfcGFnZXMscHVibGlzaF9wYWdlcyx1c2VyX21hbmFnZWRfZ3JvdXBzJnJlc3BvbnNlX3R5cGU9dG9rZW4mY2xpZW50X2lkPTE3NDgyOTAwMzM0NicsIDEsICdTcG90aWZ5JywgMyksDQooNiwgTlVMTCwgJ3R3aXR0ZXInLCBOVUxMLCAneHE1bkoyZ2tKRlVkcm84ekFXUGxiT09NUHZDR0w3T3VlN2JLeVBGdlBFazFCb3pIWmUnLCAnbDBmT3FNVGdFdE85VVpjSEhWQnhqQnpDTicsIE5VTEwsIE5VTEwsICdGUyBQb3N0ZXIgLSBTdGFuZGVydCBBUFAnLCAxKSwNCig3LCBOVUxMLCAnbGlua2VkaW4nLCAnODY5ZDBrMGRuejZhbmknLCAnc3ZEOVNTTWdvUjBONHI3RycsIE5VTEwsIE5VTEwsIE5VTEwsICdGUyBQb3N0ZXIgLSBTdGFuZGVydCBBUFAnLCAxKSwNCig4LCBOVUxMLCAndmsnLCAnNjYwMjYzNCcsICd3YTJpakhlWm40am9wNGxwQ2lHNycsIE5VTEwsIE5VTEwsIE5VTEwsICdGUyBQb3N0ZXIgLSBTdGFuZGVydCBBUFAnLCAxKSwNCig5LCBOVUxMLCAncGludGVyZXN0JywgJzQ5NzgxMjczNjE0NjQ2MTQ4NjQnLCAnMjBlYTM1ZTYyYjg2ZmUzOWYyYzkxMTE5MmYyM2QzMmE5YTc3ODA1MmRiNmExNWU3ZDI5NzQ2ZjRlMTMyM2I0ZCcsIE5VTEwsIE5VTEwsIE5VTEwsICdGUyBQb3N0ZXIgLSBTdGFuZGVydCBBUFAnLCAxKSwNCigxMCwgTlVMTCwgJ3JlZGRpdCcsICd3bFlvdkI1dkdiV1lfdycsICc2aUtWTnlLZTNLektiMmhtS3ZNbk1PZXFjbVEnLCBOVUxMLCBOVUxMLCBOVUxMLCAnRlMgUG9zdGVyIC0gU3RhbmRlcnQgQVBQJywgMSksDQooMTEsIE5VTEwsICd0dW1ibHInLCAnJywgJ1kxU3I3SlBxMzJBT21kbHo0Y3N6d0NMRjFENmNVbE5HcHNselduR0x5dExCQkwyY0lzJywgJ2RFVmxUM3dXaWNiQlpNNmZ5QW1rcjQzRHJ2NzA1YmsxVUxlSUU4a0ZEZlNpbE9vSE1HJywgTlVMTCwgTlVMTCwgJ0ZTIFBvc3RlciAtIFN0YW5kZXJ0IEFQUCcsIDEpOw0KDQpDUkVBVEUgVEFCTEUgYHt0YWJsZXByZWZpeH1mZWVkc2AgKA0KICBgaWRgIGludCgxMSkgTk9UIE5VTEwsDQogIGBwb3N0X3R5cGVgIHZhcmNoYXIoNTApIENPTExBVEUgdXRmOG1iNF91bmljb2RlX2NpIERFRkFVTFQgTlVMTCwNCiAgYHBvc3RfaWRgIGludCgxMSkgREVGQVVMVCBOVUxMLA0KICBgbm9kZV9pZGAgaW50KDExKSBERUZBVUxUIE5VTEwsDQogIGBub2RlX3R5cGVgIHZhcmNoYXIoNDApIENPTExBVEUgdXRmOG1iNF91bmljb2RlX2NpIERFRkFVTFQgTlVMTCwNCiAgYGRyaXZlcmAgdmFyY2hhcig1MCkgQ09MTEFURSB1dGY4bWI0X3VuaWNvZGVfY2kgREVGQVVMVCBOVUxMLA0KICBgaXNfc2VuZGVkYCB0aW55aW50KDEpIERFRkFVTFQgJzAnLA0KICBgc3RhdHVzYCB2YXJjaGFyKDE1KSBDT0xMQVRFIHV0ZjhtYjRfdW5pY29kZV9jaSBERUZBVUxUIE5VTEwsDQogIGBlcnJvcl9tc2dgIHZhcmNoYXIoMzAwKSBDT0xMQVRFIHV0ZjhtYjRfdW5pY29kZV9jaSBERUZBVUxUIE5VTEwsDQogIGBzZW5kX3RpbWVgIHRpbWVzdGFtcCBOVUxMIERFRkFVTFQgQ1VSUkVOVF9USU1FU1RBTVAsDQogIGBpbnRlcnZhbGAgaW50KDExKSBERUZBVUxUIE5VTEwsDQogIGBkcml2ZXJfcG9zdF9pZGAgdmFyY2hhcig0NSkgQ09MTEFURSB1dGY4bWI0X3VuaWNvZGVfY2kgREVGQVVMVCBOVUxMLA0KICBgdmlzaXRfY291bnRgIGludCgxMSkgREVGQVVMVCAnMCcsDQogIGBmZWVkX3R5cGVgIHZhcmNoYXIoNTApIENPTExBVEUgdXRmOG1iNF91bmljb2RlX2NpIERFRkFVTFQgTlVMTCwNCiAgYHNjaGVkdWxlX2lkYCBpbnQoMTEpIERFRkFVTFQgTlVMTCwNCiAgYGRyaXZlcl9wb3N0X2lkMmAgdmFyY2hhcigyNTUpIENPTExBVEUgdXRmOG1iNF91bmljb2RlX2NpIERFRkFVTFQgTlVMTCwNCiAgYGN1c3RvbV9wb3N0X21lc3NhZ2VgIHZhcmNoYXIoMjUwMCkgQ09MTEFURSB1dGY4bWI0X3VuaWNvZGVfY2kgREVGQVVMVCBOVUxMLA0KICBgc2xlZXBfdGltZV9zdGFydGAgdmFyY2hhcigzMDApIENPTExBVEUgdXRmOG1iNF91bmljb2RlX2NpIERFRkFVTFQgTlVMTCwNCiAgYHNsZWVwX3RpbWVfZW5kYCB2YXJjaGFyKDMwMCkgQ09MTEFURSB1dGY4bWI0X3VuaWNvZGVfY2kgREVGQVVMVCBOVUxMLA0KICBgc2hhcmVfb25fYmFja2dyb3VuZGAgdmFyY2hhcigzMDApIENPTExBVEUgdXRmOG1iNF91bmljb2RlX2NpIERFRkFVTFQgTlVMTA0KKSBFTkdJTkU9SW5ub0RCIERFRkFVTFQgQ0hBUlNFVD11dGY4bWI0IENPTExBVEU9dXRmOG1iNF91bmljb2RlX2NpIFJPV19GT1JNQVQ9Q09NUEFDVDsNCg0KQ1JFQVRFIFRBQkxFIGB7dGFibGVwcmVmaXh9c2NoZWR1bGVzYCAoDQogIGBpZGAgaW50KDExKSBOT1QgTlVMTCwNCiAgYHVzZXJfaWRgIGludCgxMSkgREVGQVVMVCBOVUxMLA0KICBgdGl0bGVgIHZhcmNoYXIoMjU1KSBDT0xMQVRFIHV0ZjhtYjRfdW5pY29kZV9jaSBERUZBVUxUIE5VTEwsDQogIGBzdGFydF9kYXRlYCBkYXRlIERFRkFVTFQgTlVMTCwNCiAgYGVuZF9kYXRlYCBkYXRlIERFRkFVTFQgTlVMTCwNCiAgYGludGVydmFsYCBpbnQoMTEpIERFRkFVTFQgTlVMTCwNCiAgYHN0YXR1c2AgdmFyY2hhcig1MCkgQ09MTEFURSB1dGY4bWI0X3VuaWNvZGVfY2kgREVGQVVMVCBOVUxMLA0KICBgZmlsdGVyc2AgdmFyY2hhcigyMDAwKSBDT0xMQVRFIHV0ZjhtYjRfdW5pY29kZV9jaSBERUZBVUxUIE5VTEwsDQogIGBhY2NvdW50c2AgdGV4dCBDT0xMQVRFIHV0ZjhtYjRfdW5pY29kZV9jaSwNCiAgYGluc2VydF9kYXRlYCB0aW1lc3RhbXAgTlVMTCBERUZBVUxUIENVUlJFTlRfVElNRVNUQU1QLA0KICBgc2hhcmVfdGltZWAgdGltZSBERUZBVUxUIE5VTEwsDQogIGBwb3N0X3R5cGVfZmlsdGVyYCB2YXJjaGFyKDIwMDApIENPTExBVEUgdXRmOG1iNF91bmljb2RlX2NpIERFRkFVTFQgTlVMTCwNCiAgYGNhdGVnb3J5X2ZpbHRlcmAgdmFyY2hhcigyMDAwKSBDT0xMQVRFIHV0ZjhtYjRfdW5pY29kZV9jaSBERUZBVUxUIE5VTEwsDQogIGBwb3N0X3NvcnRgIHZhcmNoYXIoNTApIENPTExBVEUgdXRmOG1iNF91bmljb2RlX2NpIERFRkFVTFQgTlVMTCwNCiAgYHBvc3RfZGF0ZV9maWx0ZXJgIHZhcmNoYXIoNTApIENPTExBVEUgdXRmOG1iNF91bmljb2RlX2NpIERFRkFVTFQgTlVMTCwNCiAgYHBvc3RfaWRzYCB2YXJjaGFyKDIwMDApIENPTExBVEUgdXRmOG1iNF91bmljb2RlX2NpIERFRkFVTFQgTlVMTCwNCiAgYG5leHRfZXhlY3V0ZV90aW1lYCB0aW1lIERFRkFVTFQgTlVMTCwNCiAgYGN1c3RvbV9wb3N0X21lc3NhZ2VgIHZhcmNoYXIoMjUwMCkgQ09MTEFURSB1dGY4bWI0X3VuaWNvZGVfY2kgREVGQVVMVCBOVUxMLA0KICBgc2hhcmVfb25fYWNjb3VudHNgIHZhcmNoYXIoMzAwKSBDT0xMQVRFIHV0ZjhtYjRfdW5pY29kZV9jaSBERUZBVUxUIE5VTEwsDQogIGBzbGVlcF90aW1lX3N0YXJ0YCB2YXJjaGFyKDMwMCkgQ09MTEFURSB1dGY4bWI0X3VuaWNvZGVfY2kgREVGQVVMVCBOVUxMLA0KICBgc2xlZXBfdGltZV9lbmRgIHZhcmNoYXIoMzAwKSBDT0xMQVRFIHV0ZjhtYjRfdW5pY29kZV9jaSBERUZBVUxUIE5VTEwsDQogIGBzaGFyZV9vbl9iYWNrZ3JvdW5kYCB2YXJjaGFyKDMwMCkgQ09MTEFURSB1dGY4bWI0X3VuaWNvZGVfY2kgREVGQVVMVCBOVUxMDQopIEVOR0lORT1Jbm5vREIgREVGQVVMVCBDSEFSU0VUPXV0ZjhtYjQgQ09MTEFURT11dGY4bWI0X3VuaWNvZGVfY2kgUk9XX0ZPUk1BVD1DT01QQUNUOw0KDQpBTFRFUiBUQUJMRSBge3RhYmxlcHJlZml4fWFjY291bnRzYCBBREQgUFJJTUFSWSBLRVkgKGBpZGApIFVTSU5HIEJUUkVFOw0KDQpBTFRFUiBUQUJMRSBge3RhYmxlcHJlZml4fWFjY291bnRfYWNjZXNzX3Rva2Vuc2AgQUREIFBSSU1BUlkgS0VZIChgaWRgKSBVU0lORyBCVFJFRTsNCg0KQUxURVIgVEFCTEUgYHt0YWJsZXByZWZpeH1hY2NvdW50X25vZGVzYCBBREQgUFJJTUFSWSBLRVkgKGBpZGApIFVTSU5HIEJUUkVFOw0KDQpBTFRFUiBUQUJMRSBge3RhYmxlcHJlZml4fWFwcHNgIEFERCBQUklNQVJZIEtFWSAoYGlkYCkgVVNJTkcgQlRSRUU7DQoNCkFMVEVSIFRBQkxFIGB7dGFibGVwcmVmaXh9ZmVlZHNgIEFERCBQUklNQVJZIEtFWSAoYGlkYCkgVVNJTkcgQlRSRUU7DQoNCkFMVEVSIFRBQkxFIGB7dGFibGVwcmVmaXh9c2NoZWR1bGVzYCBBREQgUFJJTUFSWSBLRVkgKGBpZGApIFVTSU5HIEJUUkVFOw0KDQoNCkFMVEVSIFRBQkxFIGB7dGFibGVwcmVmaXh9YWNjb3VudHNgIE1PRElGWSBgaWRgIGludCgxMSkgTk9UIE5VTEwgQVVUT19JTkNSRU1FTlQ7DQoNCkFMVEVSIFRBQkxFIGB7dGFibGVwcmVmaXh9YWNjb3VudF9hY2Nlc3NfdG9rZW5zYCBNT0RJRlkgYGlkYCBpbnQoMTEpIE5PVCBOVUxMIEFVVE9fSU5DUkVNRU5UOw0KDQpBTFRFUiBUQUJMRSBge3RhYmxlcHJlZml4fWFjY291bnRfbm9kZXNgIE1PRElGWSBgaWRgIGludCgxMSkgTk9UIE5VTEwgQVVUT19JTkNSRU1FTlQ7DQoNCkFMVEVSIFRBQkxFIGB7dGFibGVwcmVmaXh9YXBwc2AgTU9ESUZZIGBpZGAgaW50KDExKSBOT1QgTlVMTCBBVVRPX0lOQ1JFTUVOVCwgQVVUT19JTkNSRU1FTlQ9MTI7DQoNCkFMVEVSIFRBQkxFIGB7dGFibGVwcmVmaXh9ZmVlZHNgIE1PRElGWSBgaWRgIGludCgxMSkgTk9UIE5VTEwgQVVUT19JTkNSRU1FTlQ7DQoNCkFMVEVSIFRBQkxFIGB7dGFibGVwcmVmaXh9c2NoZWR1bGVzYCBNT0RJRlkgYGlkYCBpbnQoMTEpIE5PVCBOVUxMIEFVVE9fSU5DUkVNRU5UOw0KDQojDQojIDMuNCBiYWJpYXRvIGxnb2t1bCBlZGl0DQojDQoNCkNSRUFURSBUQUJMRSBge3RhYmxlcHJlZml4fWFjY291bnRfc3RhdHVzYCAoDQogIGBpZGAgaW50KDExKSBOT1QgTlVMTCwNCiAgYGFjY291bnRfaWRgIGludCgxMSkgTk9UIE5VTEwsDQogIGB1c2VyX2lkYCBpbnQoMTEpIERFRkFVTFQgTlVMTCwNCiAgYGZpbHRlcl90eXBlYCB2YXJjaGFyKDUwKSBDT0xMQVRFIHV0ZjhtYjRfdW5pY29kZV9jaSBERUZBVUxUIE5VTEwsDQogIGBjYXRlZ29yaWVzYCB2YXJjaGFyKDI1NSkgQ09MTEFURSB1dGY4bWI0X3VuaWNvZGVfY2kgREVGQVVMVCBOVUxMDQopIEVOR0lORT1Jbm5vREIgREVGQVVMVCBDSEFSU0VUPXV0ZjhtYjQgQ09MTEFURT11dGY4bWI0X3VuaWNvZGVfY2kgUk9XX0ZPUk1BVD1DT01QQUNUOw0KDQpDUkVBVEUgVEFCTEUgYHt0YWJsZXByZWZpeH1hY2NvdW50X25vZGVfc3RhdHVzYCAoDQogIGBpZGAgaW50KDExKSBOT1QgTlVMTCwNCiAgYG5vZGVfaWRgIHZhcmNoYXIoMzApIENPTExBVEUgdXRmOG1iNF91bmljb2RlX2NpIERFRkFVTFQgTlVMTCwNCiAgYHVzZXJfaWRgIGludCgxMSkgREVGQVVMVCBOVUxMLA0KICBgZmlsdGVyX3R5cGVgIHZhcmNoYXIoNTApIENPTExBVEUgdXRmOG1iNF91bmljb2RlX2NpIERFRkFVTFQgTlVMTCwNCiAgYGNhdGVnb3JpZXNgIHZhcmNoYXIoMjU1KSBDT0xMQVRFIHV0ZjhtYjRfdW5pY29kZV9jaSBERUZBVUxUIE5VTEwNCikgRU5HSU5FPUlubm9EQiBERUZBVUxUIENIQVJTRVQ9dXRmOG1iNCBDT0xMQVRFPXV0ZjhtYjRfdW5pY29kZV9jaSBST1dfRk9STUFUPUNPTVBBQ1Q7DQoNCkNSRUFURSBUQUJMRSBge3RhYmxlcHJlZml4fWFjY291bnRfc2Vzc2lvbnNgICgNCiAgYGlkYCBpbnQoMTEpIE5PVCBOVUxMLA0KICBgZHJpdmVyYCB2YXJjaGFyKDUwKSBDT0xMQVRFIHV0ZjhtYjRfdW5pY29kZV9jaSBERUZBVUxUIE5VTEwsDQogIGB1c2VybmFtZWAgdmFyY2hhcigxMDApIENPTExBVEUgdXRmOG1iNF91bmljb2RlX2NpIERFRkFVTFQgTlVMTCwNCiAgYHNldHRpbmdzYCB2YXJjaGFyKDI1MDApIENPTExBVEUgdXRmOG1iNF91bmljb2RlX2NpIERFRkFVTFQgTlVMTCwNCiAgYGNvb2tpZXNgIHZhcmNoYXIoMTAwMCkgQ09MTEFURSB1dGY4bWI0X3VuaWNvZGVfY2kgREVGQVVMVCBOVUxMDQopIEVOR0lORT1Jbm5vREIgREVGQVVMVCBDSEFSU0VUPXV0ZjhtYjQgQ09MTEFURT11dGY4bWI0X3VuaWNvZGVfY2kgUk9XX0ZPUk1BVD1DT01QQUNUOw0KDQoNCkFMVEVSIFRBQkxFIGB7dGFibGVwcmVmaXh9YWNjb3VudF9zdGF0dXNgIEFERCBQUklNQVJZIEtFWSAoYGlkYCkgVVNJTkcgQlRSRUU7DQoNCkFMVEVSIFRBQkxFIGB7dGFibGVwcmVmaXh9YWNjb3VudF9ub2RlX3N0YXR1c2AgQUREIFBSSU1BUlkgS0VZIChgaWRgKSBVU0lORyBCVFJFRTsNCg0KQUxURVIgVEFCTEUgYHt0YWJsZXByZWZpeH1hY2NvdW50X3Nlc3Npb25zYCBBREQgUFJJTUFSWSBLRVkgKGBpZGApIFVTSU5HIEJUUkVFOw0KDQpBTFRFUiBUQUJMRSBge3RhYmxlcHJlZml4fWFjY291bnRfc3RhdHVzYCBNT0RJRlkgYGlkYCBpbnQoMTEpIE5PVCBOVUxMIEFVVE9fSU5DUkVNRU5UOw0KDQpBTFRFUiBUQUJMRSBge3RhYmxlcHJlZml4fWFjY291bnRfbm9kZV9zdGF0dXNgIE1PRElGWSBgaWRgIGludCgxMSkgTk9UIE5VTEwgQVVUT19JTkNSRU1FTlQ7DQoNCkFMVEVSIFRBQkxFIGB7dGFibGVwcmVmaXh9YWNjb3VudF9zZXNzaW9uc2AgTU9ESUZZIGBpZGAgaW50KDExKSBOT1QgTlVMTCBBVVRPX0lOQ1JFTUVOVDs="}';

		// drop tables...
		$fsTables = [
			'account_access_tokens',
			'account_node_status',
			'account_nodes',
			'account_sessions',
			'account_status',
			'accounts',
			'apps',
			'feeds',
			'schedules'
		];

		foreach( $fsTables AS $tableName )
		{
			DB::DB()->query("DROP TABLE IF EXISTS `" . DB::table($tableName) . "`");
		}

		// delete options...
		$fsOptions = [
			'fs_allowed_post_types',
			'fs_facebook_posting_type',
			'fs_hide_menu_for',
			'fs_keep_logs',
			'fs_ok_posting_type',
			'fs_poster_plugin_installed',
			'fs_poster_plugin_purchase_key',
			'fs_share_on_background',
			'fs_share_timer',
			'fs_twitter_auto_cut_tweets',
			'fs_twitter_posting_type',
			'fs_vk_upload_image',
			'fs_instagram_post_in_type',
			'fs_load_groups',
			'fs_load_own_pages',
			'fs_max_groups_limit',
			'fs_post_interval',
			'fs_post_interval_type',
			'fs_post_text_message_fb',
			'fs_post_text_message_google',
			'fs_post_text_message_instagram',
			'fs_post_text_message_instagram_h',
			'fs_post_text_message_linkedin',
			'fs_post_text_message_ok',
			'fs_post_text_message_pinterest',
			'fs_post_text_message_reddit',
			'fs_post_text_message_tumblr',
			'fs_google_b_share_as_product',
			'fs_google_b_button_type',
			'fs_post_text_message_twitter',
			'fs_post_text_message_vk',
			'fs_post_text_message_google_b',
			'fs_post_text_message_telegram',
			'fs_telegram_type_of_sharing',
			'fs_shortener_service',
			'fs_unique_link',
			'fs_url_shortener',
			'fs_url_short_access_token_bitly',
			'fs_vk_load_admin_communities',
			'fs_vk_load_members_communities',
			'fs_plugin_alert',
			'fs_plugin_disabled',
			'fs_collect_statistics',
			'fs_url_additional',
			'fs_post_text_message_medium',
			'fs_share_custom_url',
			'fs_custom_url_to_share'
		];

		foreach( $fsOptions AS $optionName )
		{
			delete_option($optionName);
		}

		// delete custom post types...
		DB::DB()->query("DELETE FROM " . DB::DB()->base_prefix . "posts WHERE post_type='fs_post_tmp' OR post_type='fs_post'");
	}

	/**
	 * @param $driver
	 *
	 * @return string
	 */
	public static function socialIcon( $driver )
	{
		switch( $driver )
		{

			case 'fb':
				return "fab fa-facebook-square";
				break;
			case 'twitter':
			case 'tumblr':
				return "fab fa-{$driver}-square";
				break;

			case 'instagram':
			case 'vk':
			case 'linkedin':
			case 'pinterest':
			case 'telegram':
			case 'reddit':
			case 'medium':
				return "fab fa-{$driver}";
				break;

			case 'ok':
				return "fab fa-odnoklassniki";
				break;
			case 'google_b':
				return "fab fa-google";
				break;

		}

	}

	/**
	 * @param $social_network
	 *
	 * @return string
	 */
	public static function standartAppRedirectURL( $social_network )
	{
		$fsPurchaseKey = get_option('fs_poster_plugin_purchase_key' , '');

		return FS_API_URL . '?purchase_code=' . $fsPurchaseKey . '&domain=' . site_url() . '&sn=' . $social_network . '&r_url=' .urlencode(site_url() . '/?fs_app_redirect=1&sn=' . $social_network);
	}

	/**
	 * @param $info
	 * @param int $w
	 * @param int $h
	 *
	 * @return string
	 */
	public static function profilePic( $info , $w = 40 , $h = 40 )
	{
		if( !isset( $info['driver'] ) )
			return '';

		if( empty($info) )
		{
			return Helper::assets('images/no-photo.png');
		}

		if( is_array($info) && key_exists('cover' , $info) ) // nodes
		{
			if( !empty($info['cover']) )
			{
				return $info['cover'];
			}
			else if( $info['driver'] == 'fb' )
			{
				return "https://graph.facebook.com/".esc_html($info['node_id'])."/picture?redirect=1&height={$h}&width={$w}&type=normal";
			}
			else if( $info['driver'] == 'tumblr' )
			{
				return "https://api.tumblr.com/v2/blog/".esc_html($info['node_id'])."/avatar/" . ($w > $h ? $w : $h);
			}
			else if( $info['driver'] == 'reddit' )
			{
				return "https://www.redditstatic.com/avatars/avatar_default_10_25B79F.png";
			}
			else if( $info['driver'] == 'google_b' )
			{
				return "https://ssl.gstatic.com/images/branding/product/2x/google_my_business_32dp.png";
			}
			else if( $info['driver'] == 'telegram' )
			{
				return Helper::assets('images/telegram.svg');
			}
		}
		else if( $info['driver'] == 'fb' )
		{
			return "https://graph.facebook.com/".esc_html($info['profile_id'])."/picture?redirect=1&height={$h}&width={$w}&type=normal";
		}
		else if( $info['driver'] == 'twitter' )
		{
			static $twitterAppInfo;

			if( is_null($twitterAppInfo) )
			{
				$twitterAppInfo = DB::fetch('apps' , ['driver' => 'twitter']);
			}

			$connection = new TwitterOAuth($twitterAppInfo['app_key'], $twitterAppInfo['app_secret']);
			$user = $connection->get("users/show", ['screen_name' => $info['username']]);

			return str_replace('http://', 'https://', $user->profile_image_url);
		}
		else if( $info['driver'] == 'instagram' )
		{
			return $info['profile_pic'];
		}
		else if( $info['driver'] == 'linkedin' )
		{
			return $info['profile_pic'];
		}
		else if( $info['driver'] == 'vk' )
		{
			return $info['profile_pic'];
		}
		else if( $info['driver'] == 'pinterest' )
		{
			return $info['profile_pic'];
		}
		else if( $info['driver'] == 'reddit' )
		{
			return $info['profile_pic'];
		}
		else if( $info['driver'] == 'tumblr' )
		{
			return "https://api.tumblr.com/v2/blog/".esc_html($info['username'])."/avatar/" . ($w > $h ? $w : $h);
		}
		else if( $info['driver'] == 'ok' )
		{
			return $info['profile_pic'];
		}
		else if( $info['driver'] == 'google_b' )
		{
			return $info['profile_pic'];
		}
		else if( $info['driver'] == 'telegram' )
		{
			return Helper::assets('images/telegram.svg');
		}
		else if( $info['driver'] == 'medium' )
		{
			return $info['profile_pic'];
		}
		else
		{

		}
	}

	/**
	 * @param $info
	 *
	 * @return string
	 */
	public static function profileLink( $info )
	{
		if( !isset( $info['driver'] ) )
			return '';

		// IF NODE
		if( is_array($info) && key_exists('cover' , $info) ) // nodes
		{
			if( $info['driver'] == 'fb' )
			{
				return "https://fb.com/".esc_html($info['node_id']);
			}
			else if( $info['driver'] == 'vk' )
			{
				return "https://vk.com/".esc_html($info['screen_name']);
			}
			else if( $info['driver'] == 'tumblr' )
			{
				return "https://" . esc_html($info['screen_name']) . ".tumblr.com";
			}
			else if( $info['driver'] == 'linkedin' )
			{
				return "https://www.linkedin.com/company/" . esc_html($info['node_id']);
			}
			else if( $info['driver'] == 'ok' )
			{
				return "https://ok.ru/group/" . esc_html($info['node_id']);
			}
			else if( $info['driver'] == 'reddit' )
			{
				return "https://www.reddit.com/r/" . esc_html($info['screen_name']);
			}
			else if( $info['driver'] == 'google_b' )
			{
				return "https://business.google.com/posts/l/" . esc_html($info['node_id']);
			}
			else if( $info['driver'] == 'telegram' )
			{
				return "http://t.me/" . esc_html($info['screen_name']);
			}
			else if( $info['driver'] == 'pinterest' )
			{
				return "https://www.pinterest.com/" . esc_html($info['screen_name']);
			}
			else if( $info['driver'] == 'medium' )
			{
				return "https://medium.com/" . esc_html($info['screen_name']);
			}

			return '';
		}

		if( $info['driver'] == 'fb' )
		{
			return "https://fb.com/".esc_html($info['profile_id']);
		}
		else if( $info['driver'] == 'twitter' )
		{
			return "https://twitter.com/".esc_html($info['username']);
		}
		else if( $info['driver'] == 'instagram' )
		{
			return "https://instagram.com/".esc_html($info['username']);
		}
		else if( $info['driver'] == 'linkedin' )
		{
			return "https://www.linkedin.com/in/".esc_html(str_replace(['https://www.linkedin.com/in/', 'http://www.linkedin.com/in/'] , '' , $info['username']));
		}
		else if( $info['driver'] == 'vk' )
		{
			return "https://vk.com/id" . esc_html($info['profile_id']);
		}
		else if( $info['driver'] == 'pinterest' )
		{
			return "https://www.pinterest.com/" . esc_html($info['username']);
		}
		else if( $info['driver'] == 'reddit' )
		{
			return "https://www.reddit.com/u/" . esc_html($info['username']);
		}
		else if( $info['driver'] == 'tumblr' )
		{
			return "https://" . esc_html($info['username']) . ".tumblr.com";
		}
		else if( $info['driver'] == 'ok' )
		{
			return 'https://ok.ru/profile/'.urlencode($info['profile_id']);
		}
		else if( $info['driver'] == 'google_b' )
		{
			return 'https://business.google.com/locations';
		}
		else if( $info['driver'] == 'telegram' )
		{
			return "https://t.me/" . esc_html($info['username']);
		}
		else if( $info['driver'] == 'medium' )
		{
			return "https://medium.com/@" . esc_html($info['username']);
		}
		else
		{

		}
	}

	/**
	 * @param $appInfo
	 *
	 * @return string
	 */
	public static function appIcon( $appInfo )
	{
		if( $appInfo['driver'] == 'fb' )
		{
			return "https://graph.facebook.com/".esc_html($appInfo['app_id'])."/picture?redirect=1&height=40&width=40&type=small";
		}
		else
		{
			return self::assets('images/app_icon.svg');
		}
	}

	/**
	 * @param $message
	 * @param $postInf
	 * @param $link
	 * @param $shortLink
	 *
	 * @return string
	 */
	public static function replaceTags( $message , $postInf , $link , $shortLink )
	{
		$message = preg_replace_callback('/\{content_short_?([0-9]+)?\}/' , function($n) use( $postInf )
		{
			if( isset($n[1]) && is_numeric($n[1]) )
			{
				$cut = $n[1];
			}
			else
			{
				$cut = 40;
			}

			return Helper::cutText( strip_tags( $postInf['post_content'] ), $cut );
		} , $message);

		// custom fields
		$message = preg_replace_callback('/\{cf_(.+)\}/iU' , function($n) use( $postInf )
		{
			$customField = isset($n[1]) ? $n[1] : '';

			return get_post_meta($postInf['ID'], $customField, true);
		} , $message);


		$getPrice = Helper::getProductPrice($postInf);

		$productRegularPrice = $getPrice['regular'];
		$productSalePrice = $getPrice['sale'];

		// featured image
		$mediaId = get_post_thumbnail_id($postInf['ID']);
		if( empty($mediaId) )
		{
			$media = get_attached_media( 'image' , $postInf['ID']);
			$first = reset($media);
			$mediaId = isset($first->ID) ? $first->ID : 0;
		}

		$featuredImage = $mediaId > 0 ? wp_get_attachment_url($mediaId) : '';

		return str_replace([
			'{id}' ,
			'{title}' ,
			'{title_ucfirst}' ,
			'{content_full}' ,
			'{link}' ,
			'{short_link}' ,
			'{product_regular_price}',
			'{product_sale_price}',
			'{uniq_id}',
			'{tags}',
			'{categories}',
			'{excerpt}',
			'{author}',
			'{featured_image_url}'
		] , [
			$postInf['ID'] ,
			$postInf['post_title'],
			ucfirst( strtolower( $postInf['post_title'] ) ),
			$postInf['post_content'],
			$link ,
			$shortLink ,
			$productRegularPrice ,
			$productSalePrice ,
			uniqid(),
			Helper::getPostTags( $postInf ),
			Helper::getPostCats( $postInf ),
			$postInf['post_excerpt'],
			get_the_author_meta( 'display_name', $postInf['post_author'] ),
			$featuredImage
		] , $message);
	}

	/**
	 * @param $scheduleInf
	 *
	 * @return string
	 */
	public static function scheduleFilters( $scheduleInf )
	{
		$scheduleId = $scheduleInf['id'];

		/* Post type filter */
		$_postTypeFilter = $scheduleInf['post_type_filter'];

		$allowedPostTypes = explode('|', get_option('fs_allowed_post_types', 'post|page|attachment|product'));
		if( !in_array( $_postTypeFilter, $allowedPostTypes ) )
		{
			$_postTypeFilter = '';
		}

		$_postTypeFilter = esc_sql( $_postTypeFilter );

		if( !empty($_postTypeFilter) )
		{
			$postTypeFilter = "AND post_type='" . $_postTypeFilter . "'";
		}
		else
		{
			$postTypes = "'" . implode("','" , array_map('esc_sql', $allowedPostTypes)) . "'";

			$postTypeFilter = "AND post_type IN ({$postTypes})";
		}
		/* /End of post type filer */

		/* Categories filter */
		$categoriesArr = explode('|' , $scheduleInf['category_filter']);
		$categoriesArrNew = [];
		foreach( $categoriesArr AS $categ )
		{
			if( is_numeric($categ) && $categ > 0 )
			{
				$categInf = get_term((int)$categ);
				if( !$categInf )
					continue;

				$categoriesArrNew[] = (int)$categ;

				// get sub categories
				$child_cats = get_categories([
					'taxonomy'		=>	$categInf->taxonomy,
					'child_of'		=>	(int)$categ,
					'hide_empty'	=>	false
				]);
				foreach($child_cats AS $child_cat)
				{
					$categoriesArrNew[] = (int)$child_cat->term_id;
				}
			}
		}
		$categoriesArr = $categoriesArrNew;
		unset($categoriesArrNew);

		if( empty($categoriesArr) )
		{
			$categoriesFilter = '';
		}
		else
		{
			$categoriesFilter = " AND id IN ( SELECT object_id FROM `".DB::DB()->base_prefix."term_relationships` WHERE term_taxonomy_id IN (SELECT `term_taxonomy_id` FROM `".DB::DB()->base_prefix."term_taxonomy` WHERE `term_id` IN ('" . implode("' , '" , $categoriesArr ) . "')) ) ";
		}
		/* / End of Categories filter */


		/* post_date_filter */
		switch( $scheduleInf['post_date_filter'] )
		{
			case "this_week":
				$week = current_time('w');
				$week = $week == 0 ? 7 : $week;

				$startDateFilter = date('Y-m-d 00:00' , strtotime('-'.($week-1).' day'));
				$endDateFilter = date('Y-m-d 23:59');
				break;
			case "previously_week":
				$week = current_time('w');
				$week = $week == 0 ? 7 : $week;
				$week += 7;

				$startDateFilter = date('Y-m-d 00:00' , strtotime('-'.($week-1).' day'));
				$endDateFilter = date('Y-m-d 23:59' , strtotime('-'.($week-7).' day'));
				break;
			case "this_month":
				$startDateFilter = current_time('Y-m-01 00:00');
				$endDateFilter = current_time('Y-m-t 23:59');
				break;
			case "previously_month":
				$startDateFilter = date('Y-m-01 00:00' , strtotime('-1 month'));
				$endDateFilter = date('Y-m-t 23:59' , strtotime('-1 month'));
				break;
			case "this_year":
				$startDateFilter = current_time('Y-01-01 00:00');
				$endDateFilter = current_time('Y-12-31 23:59');
				break;
			case "last_30_days":
				$startDateFilter = date('Y-m-d 00:00' , strtotime('-30 day'));
				$endDateFilter = date('Y-m-d 23:59');
				break;
			case "last_60_days":
				$startDateFilter = date('Y-m-d 00:00' , strtotime('-60 day'));
				$endDateFilter = date('Y-m-d 23:59');
				break;
		}

		$dateFilter = "";

		if( isset($startDateFilter) && isset($endDateFilter) )
		{
			$dateFilter = " AND post_date BETWEEN '{$startDateFilter}' AND '{$endDateFilter}'";
		}
		/* End of post_date_filter */

		/* Filter by id */
		$postIDs = explode(',' , $scheduleInf['post_ids']);
		$postIDFilter = [];
		foreach( $postIDs AS $postId1 )
		{
			if( is_numeric($postId1) && $postId1 > 0 )
			{
				$postIDFilter[] = (int)$postId1;
			}
		}

		if( empty($postIDFilter) )
		{
			$postIDFilter = '';
		}
		else
		{
			$postIDFilter = " AND id IN ('" . implode("','" , $postIDFilter) . "') ";
			$postTypeFilter = '';
		}

		/* End ofid filter */

		/* post_sort */
		$sortQuery = '';
		if( $scheduleId > 0 )
		{
			switch( $scheduleInf['post_sort'] )
			{
				case "random":
					$sortQuery = 'ORDER BY RAND()';
					break;
				case "random2":
					$sortQuery = ' AND id NOT IN (SELECT post_id FROM `'.DB::table('feeds')."` WHERE schedule_id='" . (int)$scheduleId . "') ORDER BY RAND()";
					break;
				case "old_first":
					$getLastSharedPostId = DB::DB()->get_row("SELECT post_id FROM `".DB::table('feeds')."` WHERE schedule_id='".(int)$scheduleId."' ORDER BY id DESC LIMIT 1" , ARRAY_A);
					if( $getLastSharedPostId )
					{
						$sortQuery = " AND id>'" . (int)$getLastSharedPostId['post_id'] . "' ";
					}

					$sortQuery .= 'ORDER BY id ASC';
					break;
				case "new_first":
					$getLastSharedPostId = DB::DB()->get_row("SELECT post_id FROM `".DB::table('feeds')."` WHERE schedule_id='".(int)$scheduleId."' ORDER BY id DESC LIMIT 1" , ARRAY_A);
					if( $getLastSharedPostId )
					{
						$sortQuery = " AND id<'" . (int)$getLastSharedPostId['post_id'] . "' ";
					}

					$sortQuery = 'ORDER BY id DESC';
					break;
			}
		}

		return "{$postIDFilter} {$postTypeFilter} {$categoriesFilter} {$dateFilter} {$sortQuery}";
	}

	/**
	 * @return string
	 */
	public static function sendTime()
	{
		$sendTime = current_time('timestamp');

		if( (int)get_option('fs_share_timer', '0') > 0 )
		{
			$sendTime += (int)get_option('fs_share_timer', '0') * 60;
		}

		return date('Y-m-d H:i:s' , $sendTime);
	}


	/**
	 * @param $p
	 *
	 * @return bool
	 */
	public static function checkPermission( $p )
	{
		/*$permissions = ['public_profile' , 'publish_actions' , 'manage_pages' , 'publish_pages' , 'user_managed_groups' , 'pages_show_list'];

		$p2 = [];
		foreach($p['data'] AS $pName)
		{
			$p2[] = $pName['permission'];
		}

		$not = [];
		foreach($permissions AS $pName)
		{
			if( !in_array($pName , $p2) )
			{
				$not[] = esc_html($pName);
			}
		}

		if( !empty( $not ) )
		{
			return esc_html__('This app does not include certain permissions!' , 'fs-poster') . ' ( '.implode(' , ' , $not).' )';
		}*/

		return true;
	}

	/**
	 * @param $nodeType
	 * @param $nodeId
	 *
	 * @return array
	 */
	public static function getAccessToken( $nodeType , $nodeId )
	{
		if( $nodeType == 'account' )
		{
			$nodeInf			= DB::fetch('accounts' , $nodeId);
			$nodeProfileId		= $nodeInf['profile_id'];
			$nAccountId			= $nodeProfileId;

			$accessTokenGet		= DB::fetch('account_access_tokens', ['account_id' => $nodeId]);
			$accessToken		= $accessTokenGet['access_token'];
			$accessTokenSecret	= $accessTokenGet['access_token_secret'];
			$appId				= $accessTokenGet['app_id'];
			$driver				= $nodeInf['driver'];
			$username			= $nodeInf['username'];
			$password			= $nodeInf['password'];
			$proxy				= $nodeInf['proxy'];
			$options			= $nodeInf['options'];

			if( $driver == 'reddit' && (time()+30) > strtotime($accessTokenGet['expires_on']) )
			{
				$accessToken = Reddit::refreshToken($accessTokenGet);
			}
			else if( $driver == 'ok' && (time()+30) > strtotime($accessTokenGet['expires_on']) )
			{
				$accessToken = OdnoKlassniki::refreshToken($accessTokenGet);
			}
			else if( $driver == 'medium' && (time()+30) > strtotime($accessTokenGet['expires_on']) )
			{
				$accessToken = Medium::refreshToken($accessTokenGet);
			}
		}
		else
		{
			$nodeInf = DB::fetch('account_nodes' , $nodeId);

			// get proxy
			$accountInf = DB::fetch('accounts' , $nodeInf['account_id']);

			if( $nodeInf )
			{
				$nodeInf['proxy'] = $accountInf['proxy'];
			}

			$username	= $accountInf['username'];
			$password	= $accountInf['password'];
			$proxy		= $accountInf['proxy'];
			$options	= $accountInf['options'];
			$nAccountId	= $accountInf['profile_id'];

			$nodeProfileId = $nodeInf['node_id'];
			$driver = $nodeInf['driver'];
			$appId = 0;
			$accessTokenSecret = '';

			if( $driver == 'fb' && $nodeInf['node_type'] == 'ownpage' )
			{
				$accessToken = $nodeInf['access_token'];
			}
			else
			{
				$accessTokenGet = DB::fetch('account_access_tokens', ['account_id' => $nodeInf['account_id']]);
				$accessToken = $accessTokenGet['access_token'];
				$accessTokenSecret = $accessTokenGet['access_token_secret'];
				$appId = $accessTokenGet['app_id'];
			}

			if( $driver == 'vk' )
			{
				$nodeProfileId = '-' . $nodeProfileId;
			}
		}

		return [
			'node_id'               =>  $nodeProfileId,
			'access_token'          =>  $accessToken,
			'access_token_secret'   =>  $accessTokenSecret,
			'app_id'                =>  $appId,
			'driver'                =>  $driver,
			'info'                  =>  $nodeInf,
			'username'				=>	$username,
			'password'				=>	$password,
			'proxy'					=>	$proxy,
			'options'				=>	$options,
			'account_id'			=>	$nAccountId
		];
	}

	/**
	 * @param $dateTime
	 *
	 * @return mixed
	 */
	public static function localTime2UTC( $dateTime )
	{
		$timezone_string = get_option( 'timezone_string' );
		if ( ! empty( $timezone_string ) )
		{
			$wpTimezoneStr = $timezone_string;
		}
		else
		{
			$offset  = get_option( 'gmt_offset' );
			$hours   = (int) $offset;
			$minutes = abs( ( $offset - (int) $offset ) * 60 );
			$offset  = sprintf( '%+03d:%02d', $hours, $minutes );

			$wpTimezoneStr = $offset;
		}

		$dateTime = new \DateTime( $dateTime, new \DateTimeZone( $wpTimezoneStr ) );
		$dateTime->setTimezone( new \DateTimeZone( date_default_timezone_get( ) ) );

		return $dateTime->getTimestamp();
	}

	public static function assets( $url )
	{
		return plugin_dir_url(FS_ROOT_DIR . '/init.php') . 'assets/' . $url;
	}

}
