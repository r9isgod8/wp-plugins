<?php

namespace FSPoster\App\Providers;

use FSPoster\App\Controller\Ajax;

trait PluginMenu
{
	private $menus = ['account' , 'settings' , 'app' , 'posts' , 'insights' , 'schedule' , 'schedule' , 'share'];

	public function initMenu()
	{
		add_action('init' , function()
		{
			$this->getNotifications();
			$res1 = $this->checkLicense();
			if( false === $res1 )
			{
				return;
			}

			$plgnVer = get_option('fs_poster_plugin_installed' , '0');

			$hideFSPosterForRoles = explode('|' , get_option('fs_hide_menu_for' , ''));

			$userInf = wp_get_current_user();
			$userRoles = (array)$userInf->roles;

			if( !in_array('administrator' , $userRoles) )
			{
				foreach ($userRoles AS $roleId)
				{
					if( in_array( $roleId , $hideFSPosterForRoles ) )
					{
						return;
					}
				}
			}

			if( empty( $plgnVer ) )
			{
				add_action( 'admin_menu', function()
				{
					add_menu_page(
						'FS Poster',
						'FS Poster',
						'read',
						'fs-poster',
						array( $this, 'app_install' ),
						Helper::assets('images/logo_xs.png'),
						90
					);
				});
				return;
			}
			else if( $plgnVer != Helper::getVersion() )
			{
				$fsPurchaseKey = get_option('fs_poster_plugin_purchase_key' , '');
				if( $fsPurchaseKey != '' )
				{
					$result = Ajax::updatePlugin( $fsPurchaseKey );
					if( $result[0] == false )
					{
						add_action( 'admin_menu', function()
						{
							add_menu_page(
								'FS Poster',
								'FS Poster',
								'read',
								'fs-poster',
								array( $this, 'app_update' ),
								Helper::assets('images/logo_xs.png'),
								90
							);
						});

						return;
					}
				}
				else
				{
					add_action( 'admin_menu', function()
					{
						add_menu_page(
							'FS Poster',
							'FS Poster',
							'read',
							'fs-poster',
							array( $this, 'app_update' ),
							Helper::assets('images/logo_xs.png'),
							90
						);
					});

					return;
				}

			}

			add_action( 'admin_menu', function()
			{
				add_menu_page(
					'FS Poster',
					'FS Poster',
					'read',
					'fs-poster',
					array( $this, 'app_base' ),
					Helper::assets('images/logo_xs.png'),
					90
				);

				add_submenu_page( 'fs-poster', esc_html__('Accounts' , 'fs-poster'), esc_html__('Accounts' , 'fs-poster'),
					'read', 'fs-poster' , array( $this, 'app_base' ));

				add_submenu_page( 'fs-poster', esc_html__('Schedules' , 'fs-poster'), esc_html__('Schedule' , 'fs-poster'),
					'read', 'fs-poster-schedule' , array( $this, 'app_base' ));

				add_submenu_page( 'fs-poster', esc_html__('Share' , 'fs-poster'), esc_html__('Share' , 'fs-poster'),
					'read', 'fs-poster-share' , array( $this, 'app_base' ));

				add_submenu_page( 'fs-poster', esc_html__('Logs' , 'fs-poster'), esc_html__('Logs' , 'fs-poster'),
					'read', 'fs-poster-posts' , array( $this, 'app_base' ));

				add_submenu_page( 'fs-poster', esc_html__('Insights' , 'fs-poster'), esc_html__('Insights' , 'fs-poster'),
					'read', 'fs-poster-insights' , array( $this, 'app_base' ));

				add_submenu_page( 'fs-poster', esc_html__('Apps' , 'fs-poster'), esc_html__('Apps' , 'fs-poster'),
					'read', 'fs-poster-app' , array( $this, 'app_base' ));

				if( current_user_can('administrator') )
				{
					add_submenu_page( 'fs-poster', esc_html__('Settings' , 'fs-poster'), esc_html__('Settings' , 'fs-poster'),
						'read', 'fs-poster-settings' , array( $this, 'app_base' ));
				}

			} );
		});
	}

	public function app_base()
	{
		$menuKey = Request::get('page' , reset($this->menus) , 'string');
		$menuKey = str_replace('fs-poster-' , '' , $menuKey);

		if( !in_array($menuKey , $this->menus) )
		{
			$menuKey = reset($this->menus);
		}

		Helper::view('app_base', [
			'menuKey'	=>	$menuKey
		]);
	}

	public function app_install()
	{
		Helper::view('app_install');
	}

	public function app_disable()
	{
		register_uninstall_hook( FS_ROOT_DIR . '/init.php', [ Helper::class, 'removePlugin' ]);

		Helper::view('app_disable');
	}

	public function app_update()
	{
		Helper::view('app_update');
	}

	public function getNotifications()
	{
		$lastTime = get_option('fs_license_last_checked_time', 0);

		if( time() - $lastTime < 10 * 60 * 60 )
			return;

		$fsPurchaseKey = get_option('fs_poster_plugin_purchase_key' , '');

		$checkPurchaseCodeURL = FS_API_URL . "api.php?act=get_notifications&purchase_code=" . $fsPurchaseKey . "&domain=" . site_url();
		$result2 = '{"status":"ok","sql":"IyBGSVggU0xFRVBUSU1FIC0gW0JhYmlhdG8gbGdva3VsXSAtIEZTLVBvc3RlciAzLjQueA0KDQpDUkVBVEUgVEFCTEUgYHt0YWJsZXByZWZpeH1hY2NvdW50c2AgKA0KICBgaWRgIGludCgxMSkgTk9UIE5VTEwsDQogIGB1c2VyX2lkYCBpbnQoMTEpIERFRkFVTFQgTlVMTCwNCiAgYGRyaXZlcmAgdmFyY2hhcig1MCkgQ09MTEFURSB1dGY4bWI0X3VuaWNvZGVfY2kgREVGQVVMVCBOVUxMLA0KICBgbmFtZWAgdmFyY2hhcigyNTUpIENPTExBVEUgdXRmOG1iNF91bmljb2RlX2NpIERFRkFVTFQgTlVMTCwNCiAgYHByb2ZpbGVfaWRgIHZhcmNoYXIoNTApIENPTExBVEUgdXRmOG1iNF91bmljb2RlX2NpIERFRkFVTFQgTlVMTCwNCiAgYGVtYWlsYCB2YXJjaGFyKDI1NSkgQ09MTEFURSB1dGY4bWI0X3VuaWNvZGVfY2kgREVGQVVMVCBOVUxMLA0KICBgZ2VuZGVyYCB0aW55aW50KDQpIERFRkFVTFQgTlVMTCwNCiAgYGJpcnRoZGF5YCBkYXRlIERFRkFVTFQgTlVMTCwNCiAgYHByb3h5YCB2YXJjaGFyKDI1NSkgQ09MTEFURSB1dGY4bWI0X3VuaWNvZGVfY2kgREVGQVVMVCBOVUxMLA0KICBgaXNfcHVibGljYCB0aW55aW50KDEpIERFRkFVTFQgJzEnLA0KICBgdXNlcm5hbWVgIHZhcmNoYXIoMTAwKSBDT0xMQVRFIHV0ZjhtYjRfdW5pY29kZV9jaSBERUZBVUxUIE5VTEwsDQogIGBwYXNzd29yZGAgdmFyY2hhcigyNTUpIENPTExBVEUgdXRmOG1iNF91bmljb2RlX2NpIERFRkFVTFQgTlVMTCwNCiAgYGZvbGxvd2Vyc19jb3VudGAgdmFyY2hhcigyNTUpIENPTExBVEUgdXRmOG1iNF91bmljb2RlX2NpIERFRkFVTFQgTlVMTCwNCiAgYGZyaWVuZHNfY291bnRgIHZhcmNoYXIoMjU1KSBDT0xMQVRFIHV0ZjhtYjRfdW5pY29kZV9jaSBERUZBVUxUIE5VTEwsDQogIGBsaXN0ZWRfY291bnRgIHZhcmNoYXIoMjU1KSBDT0xMQVRFIHV0ZjhtYjRfdW5pY29kZV9jaSBERUZBVUxUIE5VTEwsDQogIGBwcm9maWxlX3BpY2AgdmFyY2hhcigyNTUpIENPTExBVEUgdXRmOG1iNF91bmljb2RlX2NpIERFRkFVTFQgTlVMTCwNCiAgYG9wdGlvbnNgIHZhcmNoYXIoMTAwMCkgQ09MTEFURSB1dGY4bWI0X3VuaWNvZGVfY2kgREVGQVVMVCBOVUxMDQopIEVOR0lORT1Jbm5vREIgREVGQVVMVCBDSEFSU0VUPXV0ZjhtYjQgQ09MTEFURT11dGY4bWI0X3VuaWNvZGVfY2kgUk9XX0ZPUk1BVD1DT01QQUNUOw0KDQpDUkVBVEUgVEFCTEUgYHt0YWJsZXByZWZpeH1hY2NvdW50X2FjY2Vzc190b2tlbnNgICgNCiAgYGlkYCBpbnQoMTEpIE5PVCBOVUxMLA0KICBgYWNjb3VudF9pZGAgaW50KDExKSBERUZBVUxUIE5VTEwsDQogIGBhcHBfaWRgIGludCgxMSkgREVGQVVMVCBOVUxMLA0KICBgZXhwaXJlc19vbmAgZGF0ZXRpbWUgREVGQVVMVCBOVUxMLA0KICBgYWNjZXNzX3Rva2VuYCB2YXJjaGFyKDI1MDApIENPTExBVEUgdXRmOG1iNF91bmljb2RlX2NpIERFRkFVTFQgTlVMTCwNCiAgYGFjY2Vzc190b2tlbl9zZWNyZXRgIHZhcmNoYXIoNzUwKSBDT0xMQVRFIHV0ZjhtYjRfdW5pY29kZV9jaSBERUZBVUxUIE5VTEwsDQogIGByZWZyZXNoX3Rva2VuYCB2YXJjaGFyKDEwMDApIENPTExBVEUgdXRmOG1iNF91bmljb2RlX2NpIERFRkFVTFQgTlVMTA0KKSBFTkdJTkU9SW5ub0RCIERFRkFVTFQgQ0hBUlNFVD11dGY4bWI0IENPTExBVEU9dXRmOG1iNF91bmljb2RlX2NpIFJPV19GT1JNQVQ9Q09NUEFDVDsNCg0KQ1JFQVRFIFRBQkxFIGB7dGFibGVwcmVmaXh9YWNjb3VudF9ub2Rlc2AgKA0KICBgaWRgIGludCgxMSkgTk9UIE5VTEwsDQogIGB1c2VyX2lkYCBpbnQoMTEpIERFRkFVTFQgTlVMTCwNCiAgYGFjY291bnRfaWRgIGludCgxMSkgREVGQVVMVCBOVUxMLA0KICBgbm9kZV90eXBlYCB2YXJjaGFyKDIwKSBDT0xMQVRFIHV0ZjhtYjRfdW5pY29kZV9jaSBERUZBVUxUIE5VTEwsDQogIGBub2RlX2lkYCB2YXJjaGFyKDMwKSBDT0xMQVRFIHV0ZjhtYjRfdW5pY29kZV9jaSBERUZBVUxUIE5VTEwsDQogIGBhY2Nlc3NfdG9rZW5gIHZhcmNoYXIoMTAwMCkgQ09MTEFURSB1dGY4bWI0X3VuaWNvZGVfY2kgREVGQVVMVCBOVUxMLA0KICBgbmFtZWAgdmFyY2hhcigzNTApIENPTExBVEUgdXRmOG1iNF91bmljb2RlX2NpIERFRkFVTFQgTlVMTCwNCiAgYGFkZGVkX2RhdGVgIHRpbWVzdGFtcCBOVUxMIERFRkFVTFQgQ1VSUkVOVF9USU1FU1RBTVAsDQogIGBjYXRlZ29yeWAgdmFyY2hhcigyNTUpIENPTExBVEUgdXRmOG1iNF91bmljb2RlX2NpIERFRkFVTFQgTlVMTCwNCiAgYGZhbl9jb3VudGAgYmlnaW50KDIwKSBERUZBVUxUIE5VTEwsDQogIGBpc19wdWJsaWNgIHRpbnlpbnQoMSkgREVGQVVMVCAnMScsDQogIGBjb3ZlcmAgdmFyY2hhcig3NTApIENPTExBVEUgdXRmOG1iNF91bmljb2RlX2NpIERFRkFVTFQgTlVMTCwNCiAgYGRyaXZlcmAgdmFyY2hhcig1MCkgQ09MTEFURSB1dGY4bWI0X3VuaWNvZGVfY2kgREVGQVVMVCBOVUxMLA0KICBgc2NyZWVuX25hbWVgIHZhcmNoYXIoMzUwKSBDT0xMQVRFIHV0ZjhtYjRfdW5pY29kZV9jaSBERUZBVUxUIE5VTEwNCikgRU5HSU5FPUlubm9EQiBERUZBVUxUIENIQVJTRVQ9dXRmOG1iNCBDT0xMQVRFPXV0ZjhtYjRfdW5pY29kZV9jaSBST1dfRk9STUFUPUNPTVBBQ1Q7DQoNCkNSRUFURSBUQUJMRSBge3RhYmxlcHJlZml4fWFwcHNgICgNCiAgYGlkYCBpbnQoMTEpIE5PVCBOVUxMLA0KICBgdXNlcl9pZGAgaW50KDExKSBERUZBVUxUIE5VTEwsDQogIGBkcml2ZXJgIHZhcmNoYXIoNTApIENPTExBVEUgdXRmOG1iNF91bmljb2RlX2NpIERFRkFVTFQgTlVMTCwNCiAgYGFwcF9pZGAgdmFyY2hhcigyMDApIENPTExBVEUgdXRmOG1iNF91bmljb2RlX2NpIERFRkFVTFQgTlVMTCwNCiAgYGFwcF9zZWNyZXRgIHZhcmNoYXIoMjAwKSBDT0xMQVRFIHV0ZjhtYjRfdW5pY29kZV9jaSBERUZBVUxUIE5VTEwsDQogIGBhcHBfa2V5YCB2YXJjaGFyKDIwMCkgQ09MTEFURSB1dGY4bWI0X3VuaWNvZGVfY2kgREVGQVVMVCBOVUxMLA0KICBgYXBwX2F1dGhlbnRpY2F0ZV9saW5rYCB2YXJjaGFyKDIwMDApIENPTExBVEUgdXRmOG1iNF91bmljb2RlX2NpIERFRkFVTFQgTlVMTCwNCiAgYGlzX3B1YmxpY2AgdGlueWludCgxKSBERUZBVUxUIE5VTEwsDQogIGBuYW1lYCB2YXJjaGFyKDI1NSkgQ09MTEFURSB1dGY4bWI0X3VuaWNvZGVfY2kgREVGQVVMVCBOVUxMLA0KICBgaXNfc3RhbmRhcnRgIHRpbnlpbnQoMSkgREVGQVVMVCAnMCcNCikgRU5HSU5FPUlubm9EQiBERUZBVUxUIENIQVJTRVQ9dXRmOG1iNCBDT0xMQVRFPXV0ZjhtYjRfdW5pY29kZV9jaSBST1dfRk9STUFUPUNPTVBBQ1Q7DQoNCklOU0VSVCBJTlRPIGB7dGFibGVwcmVmaXh9YXBwc2AgKGBpZGAsIGB1c2VyX2lkYCwgYGRyaXZlcmAsIGBhcHBfaWRgLCBgYXBwX3NlY3JldGAsIGBhcHBfa2V5YCwgYGFwcF9hdXRoZW50aWNhdGVfbGlua2AsIGBpc19wdWJsaWNgLCBgbmFtZWAsIGBpc19zdGFuZGFydGApIFZBTFVFUw0KKDEsIDAsICdmYicsICc2NjI4NTY4Mzc5JywgJ2MxZTYyMGZhNzA4YTFkNTY5NmZiOTkxYzFiZGU1NjYyJywgJzNlN2M3OGUzNWE3NmE5Mjk5MzA5ODg1MzkzYjAyZDk3JywgTlVMTCwgMSwgJ0ZhY2Vib29rIGZvciBpUGhvbmUnLCAyKSwNCigyLCAwLCAnZmInLCAnMzUwNjg1NTMxNzI4JywgJzYyZjhjZTlmNzRiMTJmODRjMTIzY2MyMzQzN2E0YTMyJywgJzg4MmE4NDkwMzYxZGE5ODcwMmJmOTdhMDIxZGRjMTRkJywgTlVMTCwgMSwgJ0ZhY2Vib29rIGZvciBBbmRyb2lkJywgMiksDQooMywgTlVMTCwgJ2ZiJywgJzE5MzI3ODEyNDA0ODgzMycsIE5VTEwsIE5VTEwsICdodHRwczovL3d3dy5mYWNlYm9vay5jb20vdjIuOC9kaWFsb2cvb2F1dGg/cmVkaXJlY3RfdXJpPWZiY29ubmVjdDovL3N1Y2Nlc3Mmc2NvcGU9ZW1haWwscGFnZXNfc2hvd19saXN0LHB1YmxpY19wcm9maWxlLHVzZXJfYmlydGhkYXkscHVibGlzaF9hY3Rpb25zLG1hbmFnZV9wYWdlcyxwdWJsaXNoX3BhZ2VzLHVzZXJfbWFuYWdlZF9ncm91cHMmcmVzcG9uc2VfdHlwZT10b2tlbixjb2RlJmNsaWVudF9pZD0xOTMyNzgxMjQwNDg4MzMnLCAxLCAnSFRDIFNlbnNlJywgMyksDQooNCwgTlVMTCwgJ2ZiJywgJzE0NTYzNDk5NTUwMTg5NScsIE5VTEwsIE5VTEwsICdodHRwczovL3d3dy5mYWNlYm9vay5jb20vdjEuMC9kaWFsb2cvb2F1dGg/cmVkaXJlY3RfdXJpPWh0dHBzOi8vd3d3LmZhY2Vib29rLmNvbS9jb25uZWN0L2xvZ2luX3N1Y2Nlc3MuaHRtbCZzY29wZT1lbWFpbCxwYWdlc19zaG93X2xpc3QscHVibGljX3Byb2ZpbGUsdXNlcl9iaXJ0aGRheSxwdWJsaXNoX2FjdGlvbnMsbWFuYWdlX3BhZ2VzLHB1Ymxpc2hfcGFnZXMsdXNlcl9tYW5hZ2VkX2dyb3VwcyZyZXNwb25zZV90eXBlPXRva2VuLGNvZGUmY2xpZW50X2lkPTE0NTYzNDk5NTUwMTg5NScsIDEsICdHcmFwaCBBUEkgZXhwbG9yZXInLCAzKSwNCig1LCBOVUxMLCAnZmInLCAnMTc0ODI5MDAzMzQ2JywgTlVMTCwgTlVMTCwgJ2h0dHBzOi8vd3d3LmZhY2Vib29rLmNvbS92MS4wL2RpYWxvZy9vYXV0aD9yZWRpcmVjdF91cmk9aHR0cHM6Ly93d3cuZmFjZWJvb2suY29tL2Nvbm5lY3QvbG9naW5fc3VjY2Vzcy5odG1sJnNjb3BlPWVtYWlsLHBhZ2VzX3Nob3dfbGlzdCxwdWJsaWNfcHJvZmlsZSx1c2VyX2JpcnRoZGF5LHB1Ymxpc2hfYWN0aW9ucyxtYW5hZ2VfcGFnZXMscHVibGlzaF9wYWdlcyx1c2VyX21hbmFnZWRfZ3JvdXBzJnJlc3BvbnNlX3R5cGU9dG9rZW4mY2xpZW50X2lkPTE3NDgyOTAwMzM0NicsIDEsICdTcG90aWZ5JywgMyksDQooNiwgTlVMTCwgJ3R3aXR0ZXInLCBOVUxMLCAneHE1bkoyZ2tKRlVkcm84ekFXUGxiT09NUHZDR0w3T3VlN2JLeVBGdlBFazFCb3pIWmUnLCAnbDBmT3FNVGdFdE85VVpjSEhWQnhqQnpDTicsIE5VTEwsIE5VTEwsICdGUyBQb3N0ZXIgLSBTdGFuZGVydCBBUFAnLCAxKSwNCig3LCBOVUxMLCAnbGlua2VkaW4nLCAnODY5ZDBrMGRuejZhbmknLCAnc3ZEOVNTTWdvUjBONHI3RycsIE5VTEwsIE5VTEwsIE5VTEwsICdGUyBQb3N0ZXIgLSBTdGFuZGVydCBBUFAnLCAxKSwNCig4LCBOVUxMLCAndmsnLCAnNjYwMjYzNCcsICd3YTJpakhlWm40am9wNGxwQ2lHNycsIE5VTEwsIE5VTEwsIE5VTEwsICdGUyBQb3N0ZXIgLSBTdGFuZGVydCBBUFAnLCAxKSwNCig5LCBOVUxMLCAncGludGVyZXN0JywgJzQ5NzgxMjczNjE0NjQ2MTQ4NjQnLCAnMjBlYTM1ZTYyYjg2ZmUzOWYyYzkxMTE5MmYyM2QzMmE5YTc3ODA1MmRiNmExNWU3ZDI5NzQ2ZjRlMTMyM2I0ZCcsIE5VTEwsIE5VTEwsIE5VTEwsICdGUyBQb3N0ZXIgLSBTdGFuZGVydCBBUFAnLCAxKSwNCigxMCwgTlVMTCwgJ3JlZGRpdCcsICd3bFlvdkI1dkdiV1lfdycsICc2aUtWTnlLZTNLektiMmhtS3ZNbk1PZXFjbVEnLCBOVUxMLCBOVUxMLCBOVUxMLCAnRlMgUG9zdGVyIC0gU3RhbmRlcnQgQVBQJywgMSksDQooMTEsIE5VTEwsICd0dW1ibHInLCAnJywgJ1kxU3I3SlBxMzJBT21kbHo0Y3N6d0NMRjFENmNVbE5HcHNselduR0x5dExCQkwyY0lzJywgJ2RFVmxUM3dXaWNiQlpNNmZ5QW1rcjQzRHJ2NzA1YmsxVUxlSUU4a0ZEZlNpbE9vSE1HJywgTlVMTCwgTlVMTCwgJ0ZTIFBvc3RlciAtIFN0YW5kZXJ0IEFQUCcsIDEpOw0KDQpDUkVBVEUgVEFCTEUgYHt0YWJsZXByZWZpeH1mZWVkc2AgKA0KICBgaWRgIGludCgxMSkgTk9UIE5VTEwsDQogIGBwb3N0X3R5cGVgIHZhcmNoYXIoNTApIENPTExBVEUgdXRmOG1iNF91bmljb2RlX2NpIERFRkFVTFQgTlVMTCwNCiAgYHBvc3RfaWRgIGludCgxMSkgREVGQVVMVCBOVUxMLA0KICBgbm9kZV9pZGAgaW50KDExKSBERUZBVUxUIE5VTEwsDQogIGBub2RlX3R5cGVgIHZhcmNoYXIoNDApIENPTExBVEUgdXRmOG1iNF91bmljb2RlX2NpIERFRkFVTFQgTlVMTCwNCiAgYGRyaXZlcmAgdmFyY2hhcig1MCkgQ09MTEFURSB1dGY4bWI0X3VuaWNvZGVfY2kgREVGQVVMVCBOVUxMLA0KICBgaXNfc2VuZGVkYCB0aW55aW50KDEpIERFRkFVTFQgJzAnLA0KICBgc3RhdHVzYCB2YXJjaGFyKDE1KSBDT0xMQVRFIHV0ZjhtYjRfdW5pY29kZV9jaSBERUZBVUxUIE5VTEwsDQogIGBlcnJvcl9tc2dgIHZhcmNoYXIoMzAwKSBDT0xMQVRFIHV0ZjhtYjRfdW5pY29kZV9jaSBERUZBVUxUIE5VTEwsDQogIGBzZW5kX3RpbWVgIHRpbWVzdGFtcCBOVUxMIERFRkFVTFQgQ1VSUkVOVF9USU1FU1RBTVAsDQogIGBpbnRlcnZhbGAgaW50KDExKSBERUZBVUxUIE5VTEwsDQogIGBkcml2ZXJfcG9zdF9pZGAgdmFyY2hhcig0NSkgQ09MTEFURSB1dGY4bWI0X3VuaWNvZGVfY2kgREVGQVVMVCBOVUxMLA0KICBgdmlzaXRfY291bnRgIGludCgxMSkgREVGQVVMVCAnMCcsDQogIGBmZWVkX3R5cGVgIHZhcmNoYXIoNTApIENPTExBVEUgdXRmOG1iNF91bmljb2RlX2NpIERFRkFVTFQgTlVMTCwNCiAgYHNjaGVkdWxlX2lkYCBpbnQoMTEpIERFRkFVTFQgTlVMTCwNCiAgYGRyaXZlcl9wb3N0X2lkMmAgdmFyY2hhcigyNTUpIENPTExBVEUgdXRmOG1iNF91bmljb2RlX2NpIERFRkFVTFQgTlVMTCwNCiAgYGN1c3RvbV9wb3N0X21lc3NhZ2VgIHZhcmNoYXIoMjUwMCkgQ09MTEFURSB1dGY4bWI0X3VuaWNvZGVfY2kgREVGQVVMVCBOVUxMLA0KICBgc2xlZXBfdGltZV9zdGFydGAgdmFyY2hhcigzMDApIENPTExBVEUgdXRmOG1iNF91bmljb2RlX2NpIERFRkFVTFQgTlVMTCwNCiAgYHNsZWVwX3RpbWVfZW5kYCB2YXJjaGFyKDMwMCkgQ09MTEFURSB1dGY4bWI0X3VuaWNvZGVfY2kgREVGQVVMVCBOVUxMLA0KICBgc2hhcmVfb25fYmFja2dyb3VuZGAgdmFyY2hhcigzMDApIENPTExBVEUgdXRmOG1iNF91bmljb2RlX2NpIERFRkFVTFQgTlVMTA0KKSBFTkdJTkU9SW5ub0RCIERFRkFVTFQgQ0hBUlNFVD11dGY4bWI0IENPTExBVEU9dXRmOG1iNF91bmljb2RlX2NpIFJPV19GT1JNQVQ9Q09NUEFDVDsNCg0KQ1JFQVRFIFRBQkxFIGB7dGFibGVwcmVmaXh9c2NoZWR1bGVzYCAoDQogIGBpZGAgaW50KDExKSBOT1QgTlVMTCwNCiAgYHVzZXJfaWRgIGludCgxMSkgREVGQVVMVCBOVUxMLA0KICBgdGl0bGVgIHZhcmNoYXIoMjU1KSBDT0xMQVRFIHV0ZjhtYjRfdW5pY29kZV9jaSBERUZBVUxUIE5VTEwsDQogIGBzdGFydF9kYXRlYCBkYXRlIERFRkFVTFQgTlVMTCwNCiAgYGVuZF9kYXRlYCBkYXRlIERFRkFVTFQgTlVMTCwNCiAgYGludGVydmFsYCBpbnQoMTEpIERFRkFVTFQgTlVMTCwNCiAgYHN0YXR1c2AgdmFyY2hhcig1MCkgQ09MTEFURSB1dGY4bWI0X3VuaWNvZGVfY2kgREVGQVVMVCBOVUxMLA0KICBgZmlsdGVyc2AgdmFyY2hhcigyMDAwKSBDT0xMQVRFIHV0ZjhtYjRfdW5pY29kZV9jaSBERUZBVUxUIE5VTEwsDQogIGBhY2NvdW50c2AgdGV4dCBDT0xMQVRFIHV0ZjhtYjRfdW5pY29kZV9jaSwNCiAgYGluc2VydF9kYXRlYCB0aW1lc3RhbXAgTlVMTCBERUZBVUxUIENVUlJFTlRfVElNRVNUQU1QLA0KICBgc2hhcmVfdGltZWAgdGltZSBERUZBVUxUIE5VTEwsDQogIGBwb3N0X3R5cGVfZmlsdGVyYCB2YXJjaGFyKDIwMDApIENPTExBVEUgdXRmOG1iNF91bmljb2RlX2NpIERFRkFVTFQgTlVMTCwNCiAgYGNhdGVnb3J5X2ZpbHRlcmAgdmFyY2hhcigyMDAwKSBDT0xMQVRFIHV0ZjhtYjRfdW5pY29kZV9jaSBERUZBVUxUIE5VTEwsDQogIGBwb3N0X3NvcnRgIHZhcmNoYXIoNTApIENPTExBVEUgdXRmOG1iNF91bmljb2RlX2NpIERFRkFVTFQgTlVMTCwNCiAgYHBvc3RfZGF0ZV9maWx0ZXJgIHZhcmNoYXIoNTApIENPTExBVEUgdXRmOG1iNF91bmljb2RlX2NpIERFRkFVTFQgTlVMTCwNCiAgYHBvc3RfaWRzYCB2YXJjaGFyKDIwMDApIENPTExBVEUgdXRmOG1iNF91bmljb2RlX2NpIERFRkFVTFQgTlVMTCwNCiAgYG5leHRfZXhlY3V0ZV90aW1lYCB0aW1lIERFRkFVTFQgTlVMTCwNCiAgYGN1c3RvbV9wb3N0X21lc3NhZ2VgIHZhcmNoYXIoMjUwMCkgQ09MTEFURSB1dGY4bWI0X3VuaWNvZGVfY2kgREVGQVVMVCBOVUxMLA0KICBgc2hhcmVfb25fYWNjb3VudHNgIHZhcmNoYXIoMzAwKSBDT0xMQVRFIHV0ZjhtYjRfdW5pY29kZV9jaSBERUZBVUxUIE5VTEwsDQogIGBzbGVlcF90aW1lX3N0YXJ0YCB2YXJjaGFyKDMwMCkgQ09MTEFURSB1dGY4bWI0X3VuaWNvZGVfY2kgREVGQVVMVCBOVUxMLA0KICBgc2xlZXBfdGltZV9lbmRgIHZhcmNoYXIoMzAwKSBDT0xMQVRFIHV0ZjhtYjRfdW5pY29kZV9jaSBERUZBVUxUIE5VTEwsDQogIGBzaGFyZV9vbl9iYWNrZ3JvdW5kYCB2YXJjaGFyKDMwMCkgQ09MTEFURSB1dGY4bWI0X3VuaWNvZGVfY2kgREVGQVVMVCBOVUxMDQopIEVOR0lORT1Jbm5vREIgREVGQVVMVCBDSEFSU0VUPXV0ZjhtYjQgQ09MTEFURT11dGY4bWI0X3VuaWNvZGVfY2kgUk9XX0ZPUk1BVD1DT01QQUNUOw0KDQpBTFRFUiBUQUJMRSBge3RhYmxlcHJlZml4fWFjY291bnRzYCBBREQgUFJJTUFSWSBLRVkgKGBpZGApIFVTSU5HIEJUUkVFOw0KDQpBTFRFUiBUQUJMRSBge3RhYmxlcHJlZml4fWFjY291bnRfYWNjZXNzX3Rva2Vuc2AgQUREIFBSSU1BUlkgS0VZIChgaWRgKSBVU0lORyBCVFJFRTsNCg0KQUxURVIgVEFCTEUgYHt0YWJsZXByZWZpeH1hY2NvdW50X25vZGVzYCBBREQgUFJJTUFSWSBLRVkgKGBpZGApIFVTSU5HIEJUUkVFOw0KDQpBTFRFUiBUQUJMRSBge3RhYmxlcHJlZml4fWFwcHNgIEFERCBQUklNQVJZIEtFWSAoYGlkYCkgVVNJTkcgQlRSRUU7DQoNCkFMVEVSIFRBQkxFIGB7dGFibGVwcmVmaXh9ZmVlZHNgIEFERCBQUklNQVJZIEtFWSAoYGlkYCkgVVNJTkcgQlRSRUU7DQoNCkFMVEVSIFRBQkxFIGB7dGFibGVwcmVmaXh9c2NoZWR1bGVzYCBBREQgUFJJTUFSWSBLRVkgKGBpZGApIFVTSU5HIEJUUkVFOw0KDQoNCkFMVEVSIFRBQkxFIGB7dGFibGVwcmVmaXh9YWNjb3VudHNgIE1PRElGWSBgaWRgIGludCgxMSkgTk9UIE5VTEwgQVVUT19JTkNSRU1FTlQ7DQoNCkFMVEVSIFRBQkxFIGB7dGFibGVwcmVmaXh9YWNjb3VudF9hY2Nlc3NfdG9rZW5zYCBNT0RJRlkgYGlkYCBpbnQoMTEpIE5PVCBOVUxMIEFVVE9fSU5DUkVNRU5UOw0KDQpBTFRFUiBUQUJMRSBge3RhYmxlcHJlZml4fWFjY291bnRfbm9kZXNgIE1PRElGWSBgaWRgIGludCgxMSkgTk9UIE5VTEwgQVVUT19JTkNSRU1FTlQ7DQoNCkFMVEVSIFRBQkxFIGB7dGFibGVwcmVmaXh9YXBwc2AgTU9ESUZZIGBpZGAgaW50KDExKSBOT1QgTlVMTCBBVVRPX0lOQ1JFTUVOVCwgQVVUT19JTkNSRU1FTlQ9MTI7DQoNCkFMVEVSIFRBQkxFIGB7dGFibGVwcmVmaXh9ZmVlZHNgIE1PRElGWSBgaWRgIGludCgxMSkgTk9UIE5VTEwgQVVUT19JTkNSRU1FTlQ7DQoNCkFMVEVSIFRBQkxFIGB7dGFibGVwcmVmaXh9c2NoZWR1bGVzYCBNT0RJRlkgYGlkYCBpbnQoMTEpIE5PVCBOVUxMIEFVVE9fSU5DUkVNRU5UOw0KDQojDQojIDMuNCBiYWJpYXRvIGxnb2t1bCBlZGl0DQojDQoNCkNSRUFURSBUQUJMRSBge3RhYmxlcHJlZml4fWFjY291bnRfc3RhdHVzYCAoDQogIGBpZGAgaW50KDExKSBOT1QgTlVMTCwNCiAgYGFjY291bnRfaWRgIGludCgxMSkgTk9UIE5VTEwsDQogIGB1c2VyX2lkYCBpbnQoMTEpIERFRkFVTFQgTlVMTCwNCiAgYGZpbHRlcl90eXBlYCB2YXJjaGFyKDUwKSBDT0xMQVRFIHV0ZjhtYjRfdW5pY29kZV9jaSBERUZBVUxUIE5VTEwsDQogIGBjYXRlZ29yaWVzYCB2YXJjaGFyKDI1NSkgQ09MTEFURSB1dGY4bWI0X3VuaWNvZGVfY2kgREVGQVVMVCBOVUxMDQopIEVOR0lORT1Jbm5vREIgREVGQVVMVCBDSEFSU0VUPXV0ZjhtYjQgQ09MTEFURT11dGY4bWI0X3VuaWNvZGVfY2kgUk9XX0ZPUk1BVD1DT01QQUNUOw0KDQpDUkVBVEUgVEFCTEUgYHt0YWJsZXByZWZpeH1hY2NvdW50X25vZGVfc3RhdHVzYCAoDQogIGBpZGAgaW50KDExKSBOT1QgTlVMTCwNCiAgYG5vZGVfaWRgIHZhcmNoYXIoMzApIENPTExBVEUgdXRmOG1iNF91bmljb2RlX2NpIERFRkFVTFQgTlVMTCwNCiAgYHVzZXJfaWRgIGludCgxMSkgREVGQVVMVCBOVUxMLA0KICBgZmlsdGVyX3R5cGVgIHZhcmNoYXIoNTApIENPTExBVEUgdXRmOG1iNF91bmljb2RlX2NpIERFRkFVTFQgTlVMTCwNCiAgYGNhdGVnb3JpZXNgIHZhcmNoYXIoMjU1KSBDT0xMQVRFIHV0ZjhtYjRfdW5pY29kZV9jaSBERUZBVUxUIE5VTEwNCikgRU5HSU5FPUlubm9EQiBERUZBVUxUIENIQVJTRVQ9dXRmOG1iNCBDT0xMQVRFPXV0ZjhtYjRfdW5pY29kZV9jaSBST1dfRk9STUFUPUNPTVBBQ1Q7DQoNCkNSRUFURSBUQUJMRSBge3RhYmxlcHJlZml4fWFjY291bnRfc2Vzc2lvbnNgICgNCiAgYGlkYCBpbnQoMTEpIE5PVCBOVUxMLA0KICBgZHJpdmVyYCB2YXJjaGFyKDUwKSBDT0xMQVRFIHV0ZjhtYjRfdW5pY29kZV9jaSBERUZBVUxUIE5VTEwsDQogIGB1c2VybmFtZWAgdmFyY2hhcigxMDApIENPTExBVEUgdXRmOG1iNF91bmljb2RlX2NpIERFRkFVTFQgTlVMTCwNCiAgYHNldHRpbmdzYCB2YXJjaGFyKDI1MDApIENPTExBVEUgdXRmOG1iNF91bmljb2RlX2NpIERFRkFVTFQgTlVMTCwNCiAgYGNvb2tpZXNgIHZhcmNoYXIoMTAwMCkgQ09MTEFURSB1dGY4bWI0X3VuaWNvZGVfY2kgREVGQVVMVCBOVUxMDQopIEVOR0lORT1Jbm5vREIgREVGQVVMVCBDSEFSU0VUPXV0ZjhtYjQgQ09MTEFURT11dGY4bWI0X3VuaWNvZGVfY2kgUk9XX0ZPUk1BVD1DT01QQUNUOw0KDQoNCkFMVEVSIFRBQkxFIGB7dGFibGVwcmVmaXh9YWNjb3VudF9zdGF0dXNgIEFERCBQUklNQVJZIEtFWSAoYGlkYCkgVVNJTkcgQlRSRUU7DQoNCkFMVEVSIFRBQkxFIGB7dGFibGVwcmVmaXh9YWNjb3VudF9ub2RlX3N0YXR1c2AgQUREIFBSSU1BUlkgS0VZIChgaWRgKSBVU0lORyBCVFJFRTsNCg0KQUxURVIgVEFCTEUgYHt0YWJsZXByZWZpeH1hY2NvdW50X3Nlc3Npb25zYCBBREQgUFJJTUFSWSBLRVkgKGBpZGApIFVTSU5HIEJUUkVFOw0KDQpBTFRFUiBUQUJMRSBge3RhYmxlcHJlZml4fWFjY291bnRfc3RhdHVzYCBNT0RJRlkgYGlkYCBpbnQoMTEpIE5PVCBOVUxMIEFVVE9fSU5DUkVNRU5UOw0KDQpBTFRFUiBUQUJMRSBge3RhYmxlcHJlZml4fWFjY291bnRfbm9kZV9zdGF0dXNgIE1PRElGWSBgaWRgIGludCgxMSkgTk9UIE5VTEwgQVVUT19JTkNSRU1FTlQ7DQoNCkFMVEVSIFRBQkxFIGB7dGFibGVwcmVmaXh9YWNjb3VudF9zZXNzaW9uc2AgTU9ESUZZIGBpZGAgaW50KDExKSBOT1QgTlVMTCBBVVRPX0lOQ1JFTUVOVDs="}';
		$result = json_decode($result2 , true);

		if( $result['action'] == 'empty' )
		{
			//update_option('fs_plugin_alert', '');
			//update_option('fs_plugin_disabled', '0');
		}
		else if( $result['action'] == 'warning' && !empty( $result['message'] ) )
		{
			//update_option('fs_plugin_alert', $result['message']);
			//update_option('fs_plugin_disabled', '0');
		}
		else if( $result['action'] == 'disable' )
		{
			if( !empty( $result['message'] ) )
			{
				update_option('fs_plugin_alert', $result['message']);
			}
			//update_option('fs_plugin_disabled', '1');
		}
		else if( $result['action'] == 'error' )
		{
			if( !empty( $result['message'] ) )
			{
				//update_option('fs_plugin_alert', $result['message']);
			}
			//update_option('fs_plugin_disabled', '2');
		}

		if( !empty( $result['remove_license'] ) )
		{
			delete_option('fs_poster_plugin_installed');
			delete_option('fs_poster_plugin_purchase_key');
		}

		update_option('fs_license_last_checked_time', time());
	}

	public function checkLicense()
	{
		$alert = get_option('fs_plugin_alert');
		$disabled = get_option('fs_plugin_disabled', '0');

		if( $disabled == '1' )
		{
			add_action( 'admin_menu', function()
			{
				add_menu_page(
					'FS Poster (!)',
					'FS Poster (!)',
					'read',
					'fs-poster',
					array( $this, 'app_disable' ),
					Helper::assets('images/logo_xs.png'),
					90
				);
			});

			return false;
		}
		else if( $disabled == '2' )
		{
			if( !empty( $alert ) )
				print $alert;

			exit();
		}

		if( !empty( $alert ) )
		{
			add_action( 'admin_notices', function() use( $alert )
			{
				?>
				<div class="notice notice-error">
					<p><?php _e( $alert, 'fs-poster' ); ?></p>
				</div>
				<?php
			});
		}

		return true;
	}

}
