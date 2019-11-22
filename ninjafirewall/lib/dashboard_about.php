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
?>
<div class="card">
	<p style="text-align:center;font-size: 1.8em; font-weight: bold">NinjaFirewall (WP Edition)</p>
	<p style="text-align:center"><img src="<?php echo plugins_url() ?>/ninjafirewall/images/ninjafirewall_100.png" /></p>
	<p style="text-align:center;font-size: 1.2em;"><font onContextMenu="nfw_eg();return false;">&copy;</font> 2012-<?php echo date( 'Y' ) ?> <a href="https://nintechnet.com/" target="_blank" title="The Ninja Technologies Network"><strong>NinTechNet</strong></a><br />The Ninja Technologies Network	</p>
	<br />
	<font style="font-size: 1.1em;">
	<ul style="list-style: disc;">
		<li><?php _e('Our blog:', 'ninjafirewall') ?> <a href="https://blog.nintechnet.com/">https://blog.nintechnet.com/</a></li>
		<li><?php _e('Stay informed about the latest vulnerabilities in WordPress plugins and themes:', 'ninjafirewall') ?> <a href="https://twitter.com/nintechnet">https://twitter.com/nintechnet</a></li>
		<li><a href="https://blog.nintechnet.com/ninjafirewall-general-data-protection-regulation-compliance/"><?php _e('GDPR Compliance', 'ninjafirewall') ?></a></li>
		<li><a href="https://wordpress.org/support/view/plugin-reviews/ninjafirewall?rate=5#postform"><?php _e('Rate it on WordPress.org!', 'ninjafirewall') ?></a></li>
		<li><a href="https://nintechnet.com/referral/"><?php _e('NinjaFirewall Referral Program', 'ninjafirewall') ?></a></li>
	</ul>
	</font>
</div>
<?php

// ---------------------------------------------------------------------
// EOF
