<?php

namespace FSPoster\App\Providers;

use FSPoster\App\Controller\Ajax;
use FSPoster\App\Controller\Modal;

class BackEnd
{

	use PluginMenu;

	private $active_custom_post_types;

	public function __construct()
	{
		if( !Helper::pluginDisabled() )
		{
			new Ajax();
			new Modal();
		}

		$this->initMenu();

		$this->enqueueAssets();
		$this->updateService();
		$this->registerMetaBox();

		$this->registerActions();
		$this->registerBulkAction();

		$this->bulkScheduleAction();
	}

	private function registerMetaBox()
	{
		add_action( 'add_meta_boxes', function()
		{
			add_meta_box( 'fs_poster_meta_box', 'FS Poster', [$this , 'publishMetaBox'], $this->getActiveCustomPostTypes() , 'side' , 'high'  );
		});
	}

	public function publishMetaBox( $post )
	{
		// post creating panel
		if( in_array( $post->post_status , ['new' , 'auto-draft' , 'draft' , 'pending'] ) )
		{
			Helper::view('post_meta_box', [
				'post_id'  =>  $post->ID
			]);
		}
		else // post edit panel
		{
			Helper::view('post_meta_box_edit', [
				'post'  =>  $post
			]);
		}
	}

	private function enqueueAssets()
	{
		add_action('admin_enqueue_scripts' , function()
		{
			wp_register_script('fs-code.js', Helper::assets('js/fs-code.js') , array( 'jquery' ) , Helper::getVersion());
			wp_enqueue_script( 'fs-code.js' );

			wp_enqueue_style( 'fs-code.css', Helper::assets('css/fs-code.css') , [] , Helper::getVersion() );
			wp_enqueue_style( 'font_aweasome', '//use.fontawesome.com/releases/v5.0.13/css/all.css' );
		});
	}

	private function updateService()
	{
		$activationKey = get_option('fs_poster_plugin_purchase_key' , '');

		if( !empty($activationKey) )
		{
			add_action( 'init', function () use ( $activationKey )
			{
				$updater = new FSCodeUpdater( 'fs-poster', FS_API_URL . '/api.php', $activationKey );
			});
		}
	}

	private function registerActions()
	{
		if( get_option('fs_show_fs_poster_column', '1') )
		{
			$usedColumnsSave = [];

			foreach( $this->getActiveCustomPostTypes() AS $postType )
			{
				$postType = preg_replace('/[^a-zA-Z0-9\-\_]/' , '' , $postType);

				switch($postType)
				{
					case 'post':
						$typeName = 'posts';
						break;
					case 'page':
						$typeName = 'pages';
						break;
					case 'attachment':
						$typeName = 'media';
						break;
					default:
						$typeName = $postType . '_posts';
				}

				add_action( 'manage_'.$typeName.'_custom_column', function ( $column_name, $post_id ) use( &$usedColumnsSave )
				{
					if ( $column_name == 'share_btn' && get_post_status($post_id) == 'publish' && !isset($usedColumnsSave[$post_id]) )
					{
						printf( '<img class="fs_post_icons ws_tooltip" data-title="Share" src="'.Helper::assets('images/share_icon.png').'" data-load-modal="share_saved_post" data-parameter-post_id="%d">', $post_id  );
						printf( '<img class="fs_post_icons ws_tooltip" data-title="Schedule" src="'.Helper::assets('images/schedule_icon.png').'" data-load-modal="plan_saved_post" data-parameter-post_id="%d"> ', $post_id  );

						$usedColumnsSave[$post_id] = true;
					}
				}, 10, 2 );

				add_filter('manage_'.$typeName.'_columns', function ( $columns )
				{
					if( is_array( $columns ) && ! isset( $columns['share_btn'] ) )
					{
						$columns['share_btn'] = esc_html__('FS Poster' , 'fs-poster');
					}

					return $columns;
				} );

			}
		}
	}

	private function registerBulkAction()
	{
		foreach( $this->getActiveCustomPostTypes() AS $postType )
		{
			add_filter( 'bulk_actions-edit-' . $postType, function ($bulk_actions)
			{
				$bulk_actions['fs_schedule'] = __( 'FS Poster: Schedule', 'fs_schedule');
				return $bulk_actions;
			} );

			add_filter( 'handle_bulk_actions-edit-' . $postType, function ( $redirect_to, $doaction, $post_ids )
			{
				if ( $doaction !== 'fs_schedule' )
				{
					return $redirect_to;
				}

				$redirect_to = add_query_arg( 'fs_schedule_posts', implode(',' , $post_ids), $redirect_to );
				return $redirect_to;
			}, 10, 3 );

		}
	}

	private function getActiveCustomPostTypes()
	{
		if( is_null( $this->active_custom_post_types ) )
		{
			$this->active_custom_post_types = explode( '|' , get_option('fs_allowed_post_types' , 'post|page|attachment|product') );
		}

		return $this->active_custom_post_types;
	}

	private function bulkScheduleAction()
	{
		$posts = Request::get('fs_schedule_posts', '', 'string');

		if( empty( $posts ) )
		{
			return;
		}

		add_action( 'admin_notices', function () use( $posts )
		{
			$posts = explode(',' , $posts);
			$postIds = [];
			foreach ($posts AS $postId)
			{
				if( is_numeric($postId) && $postId > 0 )
				{
					$postIds[] = (int)$postId;
				}
			}

			print '<script>jQuery(document).ready(function(){ fsCode.loadModal("plan_saved_post" , {"post_id": '.json_encode($postIds).'}) });</script>';
		});
	}

}