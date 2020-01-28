<?php

namespace FSPoster\App\view;

use FSPoster\App\Providers\DB;
use FSPoster\App\Providers\Helper;
use FSPoster\App\Providers\Request;

defined( 'ABSPATH' ) or exit;

$view = Request::get('view' , 'list' , 'string' , ['list', 'calendar']);

switch ($view)
{
	case 'list':
		Helper::view('app_menus.schedule.list_view');
		break;
	case 'calendar':
		Helper::view('app_menus.schedule.calendar_view');
		break;
}

