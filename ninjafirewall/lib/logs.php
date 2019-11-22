<?php
/*
 +---------------------------------------------------------------------+
 | NinjaFirewall (WP Edition)                                          |
 |                                                                     |
 | (c) NinTechNet - https://nintechnet.com/                            |
 +---------------------------------------------------------------------+
 | This program is free software: you can redistribute it and/or       |
 | modify it under the terms of the GNU General Public License as      |
 | published by the Free Software Foundation, either version 3 of      |
 | the License, or (at your option) any later version.                 |
 |                                                                     |
 | This program is distributed in the hope that it will be useful,     |
 | but WITHOUT ANY WARRANTY; without even the implied warranty of      |
 | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the       |
 | GNU General Public License for more details.                        |
 +---------------------------------------------------------------------+ i18n+ / sa / 2
*/

if (! defined( 'NFW_ENGINE_VERSION' ) ) { die( 'Forbidden' ); }

// Tab and div display
if ( empty( $_REQUEST['tab'] ) ) { $_REQUEST['tab'] = 'firewalllog'; }

if ( $_REQUEST['tab'] == 'livelog' ) {
	$firewalllog_tab = ''; $firewalllog_div = ' style="display:none"';
	$livelog_tab = ' nav-tab-active'; $livelog_div = '';
} else {
	$_REQUEST['tab'] = 'firewalllog';
	$firewalllog_tab = ' nav-tab-active'; $firewalllog_div = '';
	$livelog_tab = ''; $livelog_div = ' style="display:none"';
}

?>
<div class="wrap">
	<h1><img style="vertical-align:top;width:33px;height:33px;" src="<?php echo plugins_url( '/ninjafirewall/images/ninjafirewall_32.png' ) ?>">&nbsp;<?php _e('Logs', 'ninjafirewall') ?></h1>
	<br />
	<h2 class="nav-tab-wrapper wp-clearfix" style="cursor:pointer">
		<a id="tab-firewalllog" class="nav-tab<?php echo $firewalllog_tab ?>" onClick="nfwjs_switch_tabs('firewalllog', 'firewalllog:livelog')"><?php _e( 'Firewall Log', 'ninjafirewall' ) ?></a>
		<a id="tab-livelog" class="nav-tab<?php echo $livelog_tab ?>" onClick="nfwjs_switch_tabs('livelog', 'firewalllog:livelog')"><?php _e( 'Live Log', 'ninjafirewall' ) ?></a>
	</h2>
	<br />

	<!-- Firewall Log -->
	<div id="firewalllog-options"<?php echo $firewalllog_div ?>>
		<?php include __DIR__ .'/logs_firewall_log.php'; ?>
	</div>

	<!-- Live Log -->
	<div id="livelog-options"<?php echo $livelog_div ?>>
		<?php include __DIR__ .'/logs_live_log.php'; ?>
	</div>

<?php

// ---------------------------------------------------------------------
// EOF
