<?php

namespace FSPoster\App\Providers;

/**
 * Class Bootstrap
 * @package FSPoster\App\Providers
 */
class Bootstrap
{

	/**
	 * Bootstrap constructor.
	 */
	public function __construct()
	{
		CronJob::init();
		$this->registerDefines();

		$this->loadPluginTextdomaion();
		$this->loadPluginLinks();
		$this->createCustomPostTypes();
		$this->createPostSaveEvent();

		if( is_admin() )
		{
			new BackEnd();
		}
		else
		{
			new FrontEnd();
		}
	}

	private function registerDefines()
	{
		define('FS_ROOT_DIR' , dirname(dirname(__DIR__)));
		define('FS_API_URL' , 'https://www.fs-poster.com/api/');

		add_action('fs_register_session', [ Helper::class, 'registerSession' ]);
	}

	private function loadPluginLinks()
	{
		add_filter('plugin_action_links_' . plugin_basename(__FILE__), function ($links)
		{
			$newLinks = [
				'<a href="https://support.fs-code.com" target="_blank">' . __('Support', 'fs-poster') . '</a>',
				'<a href="https://www.fs-poster.com/doc/" target="_blank">' . __('Doc', 'fs-poster') . '</a>'
			];

			return array_merge($newLinks, $links);
		});
	}

	private function loadPluginTextdomaion()
	{
		add_action( 'plugins_loaded', function()
		{
			load_plugin_textdomain( 'fs-poster', FALSE, basename( dirname( __FILE__ ) ) . '/languages/' );
		});
	}

	private function createCustomPostTypes()
	{
		add_action( 'init', function ()
		{
			register_post_type( 'fs_post', [
				'labels'		=> [
					'name'			=> __( 'FS Posts' ),
					'singular_name'	=> __( 'FS Post' )
				],
				'public'		=> false,
				'has_archive'	=> true
			]);
			register_post_type( 'fs_post_tmp', [
				'labels'		=> [
					'name'			=> __( 'FS Posts' ),
					'singular_name'	=> __( 'FS Post' )
				],
				'public'		=> false,
				'has_archive'	=> true
			]);
		});
	}

	private function createPostSaveEvent()
	{
		add_action( 'transition_post_status', [$this , 'postSaveEvent'], 10, 3 );
	}

	public function postSaveEvent( $new_status, $old_status, $post )
	{
		global $wp_version;

		$isAutoSave = defined('DOING_AUTOSAVE') && DOING_AUTOSAVE;

		if( $isAutoSave )
			return;

		// For WordPress 5 (Gutenberg)...
		if( version_compare( $wp_version, '5.0', '>=' ) && isset($_GET['_locale']) && $_GET['_locale'] == 'user' && empty($_POST) )
		{
			return;
		}

		$metaBoxLoader = (int)Request::get('meta-box-loader', 0, 'num', ['1']);
		if( $metaBoxLoader === 1 && Request::post('original_post_status', '', 'string') == 'publish' )
		{
			$metaBoxLoader = 0;
		}

		if( !( ($new_status == 'publish' || $new_status == 'future' || $new_status == 'draft') && ( $old_status != 'publish' || $metaBoxLoader === 1 ) ) )
		{
			return;
		}

		// if not allowed post type...
		if( !in_array( $post->post_type , explode( '|' , get_option('fs_allowed_post_types' , 'post|page|attachment|product') ) ) )
		{
			return;
		}

		$post_id	= $post->ID;
		$userId		= $post->post_author;

		// if not checked the 'Share' checkbox exit the function
		$share_checked_inpt = Request::post('share_checked' , ( get_option('fs_auto_share_new_posts', '1') ? 'on' : 'off' ) , 'string' , ['on' , 'off']);
		$share_checked = $share_checked_inpt === 'on' ? 1 : 0;

		if( !$share_checked )
		{
			DB::DB()->delete(DB::table('feeds') , [
				'post_id'       =>  $post_id,
				'is_sended'     =>  '0'
			]);

			return;
		}

		// if scheduled post, publish it using cron and exit the function
		if( $new_status == 'publish' && $old_status == 'future' )
		{
			$checkFeedsExist = DB::fetch('feeds' , [
				'post_id'       =>  $post_id,
				'is_sended'     =>  '0'
			]);

			if( $checkFeedsExist )
			{
				CronJob::setbackgroundTask( $post_id );
			}

			return;
		}

		// interval for each publication
		$postInterval = (int)get_option('fs_post_interval' , '0');

		// run share process on background
		$backgroundShare = (int)get_option('fs_share_on_background' , '1');

		// social networks lists
		$nodesList = Request::post('share_on_nodes' , false , 'array' );

		// if false, may be from xmlrpc, external application or etc... then load ol active nodes
		if( $nodesList === false && !isset($_POST['share_checked']) && $new_status != 'draft' )
		{
			$nodesList = [];

			$accounts = DB::DB()->get_results(
				DB::DB()->prepare("
					SELECT tb2.id, tb2.driver, tb1.filter_type, tb1.categories, 'account' AS node_type FROM ".DB::table('account_status')." tb1
					LEFT JOIN ".DB::table('accounts')." tb2 ON tb2.id=tb1.account_id
					WHERE tb1.user_id=%d" , [ $userId ])
				, ARRAY_A
			);

			$activeNodes = DB::DB()->get_results(
				DB::DB()->prepare("
					SELECT tb2.id, tb2.driver, tb2.node_type, tb1.filter_type, tb1.categories FROM ".DB::table('account_node_status')." tb1
					LEFT JOIN ".DB::table('account_nodes')." tb2 ON tb2.id=tb1.node_id
					WHERE tb1.user_id=%d" , [ $userId ])
				, ARRAY_A
			);

			$activeNodes = array_merge($accounts , $activeNodes);

			foreach ($activeNodes AS $nodeInf)
			{
				$nodesList[] = $nodeInf['driver'].':'.$nodeInf['node_type'].':'.$nodeInf['id'].':'.htmlspecialchars($nodeInf['filter_type']).':'.htmlspecialchars($nodeInf['categories']);
			}
		}

		if( !empty( $nodesList ) /*|| $metaBoxLoader === 1 */)
		{
			DB::DB()->delete(DB::table('feeds') , [
				'post_id'       =>  $post_id,
				'is_sended'     =>  '0'
			]);
		}

		$post_text_message = [];

		$post_text_message['fb']			= Request::post('fs_post_text_message_fb' , '' , 'string');
		$post_text_message['twitter']		= Request::post('fs_post_text_message_twitter' , '' , 'string');
		$post_text_message['instagram']		= Request::post('fs_post_text_message_instagram' , '' , 'string');
		$post_text_message['instagram_h']	= Request::post('fs_post_text_message_instagram_h' , '' , 'string');
		$post_text_message['linkedin']		= Request::post('fs_post_text_message_linkedin' , '' , 'string');
		$post_text_message['vk']			= Request::post('fs_post_text_message_vk' , '' , 'string');
		$post_text_message['pinterest']		= Request::post('fs_post_text_message_pinterest' , '' , 'string');
		$post_text_message['reddit']		= Request::post('fs_post_text_message_reddit' , '' , 'string');
		$post_text_message['tumblr']		= Request::post('fs_post_text_message_tumblr' , '' , 'string');
		$post_text_message['ok']			= Request::post('fs_post_text_message_ok' , '' , 'string');
		$post_text_message['google_b']		= Request::post('fs_post_text_message_google_b' , '' , 'string');
		$post_text_message['telegram']		= Request::post('fs_post_text_message_telegram' , '' , 'string');
		$post_text_message['medium']		= Request::post('fs_post_text_message_medium' , '' , 'string');

		if( $old_status == 'draft' )
		{

			delete_post_meta( $post_id, '_fs_poster_share' );
			delete_post_meta( $post_id, '_fs_poster_node_list' );

			foreach ( $post_text_message AS $dr => $cmtxt )
			{
				delete_post_meta( $post_id, '_fs_poster_cm_' . $dr );
			}
		}

		if( $new_status == 'draft' )
		{
			add_post_meta( $post_id, '_fs_poster_share', $share_checked, true );
			add_post_meta( $post_id, '_fs_poster_node_list', $nodesList, true );

			foreach ( $post_text_message AS $dr => $cmtxt )
			{
				add_post_meta( $post_id, '_fs_poster_cm_' . $dr , $cmtxt, true );
			}
			return;
		}

		$postCats = Helper::getPostCatsArr( $post_id );

		if( !is_array( $nodesList ) )
		{
			$nodesList = [];
		}

		foreach( $nodesList AS $nodeId )
		{
			if( is_string($nodeId) && strpos( $nodeId , ':' ) !== false )
			{
				$parse = explode(':' , $nodeId);
				$driver = $parse[0];
				$nodeType = $parse[1];
				$nodeId = $parse[2];
				$filterType = isset($parse[3]) ? $parse[3] : 'no';
				$categoriesStr = isset($parse[4]) ? $parse[4] : '';

				if( $postCats !== false ) // manual share panel...
				{
					$categoriesFilter = [];

					if( !empty($categoriesStr) && $filterType != 'no' )
					{
						foreach( explode(',' , $categoriesStr) AS $termId )
						{
							if( is_numeric($termId) && $termId > 0 )
							{
								$categoriesFilter[] = (int)$termId;
							}
						}
					}
					else
					{
						$filterType = 'no';
					}

					if( $filterType == 'in' )
					{
						$checkFilter = false;
						foreach( $postCats AS $termInf )
						{
							if( in_array( $termInf->term_id , $categoriesFilter ) )
							{
								$checkFilter = true;
								break;
							}
						}

						if( !$checkFilter )
						{
							continue;
						}
					}
					else if( $filterType == 'ex' )
					{
						$checkFilter = true;
						foreach( $postCats AS $termInf )
						{
							if( in_array( $termInf->term_id , $categoriesFilter ) )
							{
								$checkFilter = false;
								break;
							}
						}

						if( !$checkFilter )
						{
							continue;
						}
					}
				}

				if( ( $driver == 'tumblr' || $driver == 'google_b' || $driver == 'telegram' ) && $nodeType == 'account' )
				{
					continue;
				}

				if( !( in_array( $nodeType , ['account' , 'ownpage' , 'page' , 'group' , 'event' , 'blog' , 'company' , 'community', 'subreddit', 'location', 'chat', 'board', 'publication'] ) && is_numeric($nodeId) && $nodeId > 0 ) )
				{
					continue;
				}



				if( !($driver == 'instagram' && get_option('fs_instagram_post_in_type', '1') == '2') )
				{
					$customMessage = isset($post_text_message[$driver]) ? $post_text_message[$driver] : null;

					if( $customMessage == get_option( 'fs_post_text_message_' . $driver , "{title}" ) )
					{
						$customMessage = null;
					}

					DB::DB()->insert( DB::table('feeds'), [
						'driver'                =>  $driver,
						'post_id'               =>  $post_id,
						'node_type'             =>  $nodeType,
						'node_id'               =>  (int)$nodeId,
						'interval'              =>  $postInterval,
						'custom_post_message'   =>  $customMessage,
						'send_time'				=>	Helper::sendTime()
					]);
				}

				if( $driver == 'instagram' && (get_option('fs_instagram_post_in_type', '1') == '2' || get_option('fs_instagram_post_in_type', '1') == '3') )
				{
					$customMessage = isset($post_text_message[$driver . '_h']) ? $post_text_message[$driver . '_h'] : null;

					if( $customMessage == get_option( 'fs_post_text_message_' . $driver . '_h' , "{title}" ) )
					{
						$customMessage = null;
					}

					DB::DB()->insert( DB::table('feeds'), [
						'driver'                =>  $driver,
						'post_id'               =>  $post_id,
						'node_type'             =>  $nodeType,
						'node_id'               =>  (int)$nodeId,
						'interval'              =>  $postInterval,
						'feed_type'             =>  'story',
						'custom_post_message'   =>  $customMessage,
						'send_time'				=>	Helper::sendTime()
					]);
				}
			}
		}

		// if backround process activated then create a new cron job
		if( $backgroundShare && $new_status == 'publish' )
		{
			CronJob::setbackgroundTask( $post_id );
		}

		// if not scheduled post then add arguments end of url
		if( $new_status == 'publish' )
		{
			add_filter('redirect_post_location', function($location) use( $backgroundShare )
			{
				return $location . '&share=1&background=' . $backgroundShare;
			});
		}

	}

}