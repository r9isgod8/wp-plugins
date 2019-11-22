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
 +---------------------------------------------------------------------+
*/

// =====================================================================
// Generic.

function nfwjs_switch_tabs( what, list ) {
	// Active tab:
	jQuery('#'+ what +'-options').show();
	jQuery('#tab-'+ what).addClass('nav-tab-active');
	jQuery('#tab-selected').val( what );
	// Inactive tabs:
	var tabs = list.split( ':' );
	var list_length = tabs.length;
	for ( var i = 0; i < list_length; i++ ) {
		if ( tabs[i] != what ) {
			jQuery('#'+ tabs[i] +'-options').hide();
			jQuery('#tab-'+ tabs[i]).removeClass('nav-tab-active');
		}
	}
}

function nfwjs_up_down( id ) {
	if ( jQuery('#'+ id).css('display') == 'none' ) {
		jQuery('#'+ id).slideDown();
	} else {
		jQuery('#'+ id).slideUp();
	}
}

function nfwjs_restore_default() {
   if ( confirm( nfwi18n.restore_default ) ) {
      return true;
   }
	return false;
}

// =====================================================================
// Overview page.

jQuery( document ).ready( function() {
	jQuery( '#nfw-thickbox' ).click( function() {
		var h = jQuery(window).height() - 100;
		var w = jQuery(window).width() - 100;
		tb_show( '', '#TB_inline?width='+ (w - 20) +
			'&amp;height='+ (h - 20) +
			'&amp;inlineId=nfw-thickbox-content', null );
		return false;
	} );
});

function nfwjs_httpserver( what ) {

	if ( what == 6 ) {
		// Openlitespeed only
		jQuery('#diy-div').hide();

	} else {
		jQuery('#diy-div').show();
	}

	if ( what == 1 || what == 5 || what == 6 ) {
		// No INI file
		jQuery('#tr-ini-userini').hide();
		jQuery('#tr-ini-phpini').hide();
		jQuery('#tr-select-ini').hide();
		jQuery('#tr-htaccess-suphp').hide();

		if ( what == 1 ) {
			// mod_php
			jQuery('#tr-htaccess-litespeed').hide();
			jQuery('#tr-htaccess-openlitespeed').hide();
			jQuery('#tr-htaccess-modphp').show();
		} else if ( what == 5 ) {
			// Litespeed
			jQuery('#tr-htaccess-modphp').hide();
			jQuery('#tr-htaccess-openlitespeed').hide();
			jQuery('#tr-htaccess-litespeed').show();
		} else {
			// Openlitespeed
			jQuery('#tr-htaccess-modphp').hide();
			jQuery('#tr-htaccess-litespeed').hide();
			jQuery('#tr-htaccess-openlitespeed').show();
			jQuery('#nfwaf-step2').slideDown();
		}

	} else {
		jQuery( '#tr-select-ini' ).show();

		if ( what == 2 || what == 4 || what == 7 ) {
			// No .htaccess file
			jQuery('#tr-htaccess-modphp').hide();
			jQuery('#tr-htaccess-litespeed').hide();
			jQuery('#tr-htaccess-suphp').hide();
			jQuery('#tr-htaccess-openlitespeed').hide();

		} else if ( what == 3 ) {
			// ini + suPHP
			jQuery('#tr-htaccess-litespeed').hide();
			jQuery('#tr-htaccess-modphp').hide();
			jQuery('#tr-htaccess-openlitespeed').hide();
			jQuery('#tr-htaccess-suphp').show();
		}

		// Which INI?
		if ( jQuery('#ini-type-user').prop('checked') == true ) {
			jQuery('#tr-ini-userini').show();
			jQuery('#tr-ini-phpini').hide();
		} else {
			jQuery('#tr-ini-userini').hide();
			jQuery('#tr-ini-phpini').show();
		}
	}

	if ( jQuery('#diynfw').prop('checked') == true && what != 6 ) {
		nfwjs_diy_chg( 'nfw' );

	} else {
		nfwjs_diy_chg( 'usr' );
	}
}

function nfwjs_radio_ini( what ) {

	if ( what == 1 ) { // .user.ini
		jQuery('#tr-ini-userini').show();
		jQuery('#tr-ini-phpini').hide();

	} else { // php.ini
		jQuery('#tr-ini-userini').hide();
		jQuery('#tr-ini-phpini').show();
	}
}

function nfwjs_fullwafsubmit() {

	// Security nonce
	var nonce = jQuery('input[name=nfwnonce]').val();
	if ( nonce == '' ) {
		alert( nfwi18n.missing_nonce );
		return false;
	}

	var httpserver = jQuery('select[name=http_server]').val();
	if ( httpserver == '' ) {
		alert( nfwi18n.missing_httpserver );
		return false;
	}
	var initype = jQuery('input[name=ini_type]:checked').val();
	var diy = jQuery('input[name=diy-choice]:checked').val();

	// Ajax
	var data = {
		'action': 'nfw_fullwafsetup',
		'nonce': nonce,
		'httpserver': httpserver,
		'initype': initype,
		'diy': diy
	};
	jQuery.ajax( {
		type: "POST",
		url: ajaxurl,
		headers: {
			'Accept-Language':'en-US,en;q=0.5',
			'User-Agent':'Mozilla/5.0 (X11; Linux x86_64; rv:60.0)',
		},
		data: data,
		dataType: "text",
		success: function( response ) {
			if ( response == '200' ) {
				window.location.href = window.location.href + '&fullwaf=1';
			} else {
				alert( response );
			}
		},
		// Display non-200 HTTP response
		error: function( xhr, status, err ) {
			alert( nfwi18n.http_error +' "'+ xhr.status +' '+ err +'".' );
		}
	});
	return false;
}

function nfwjs_diy_chg( what ) {
	if ( what == 'nfw' ) {
		jQuery('#lmd-msg').slideDown();
		jQuery('#diy-msg').slideUp();
		jQuery('#nfwaf-step2').slideUp();

	} else {
		jQuery('#lmd-msg').slideUp();
		jQuery('#diy-msg').slideDown();
		jQuery('#nfwaf-step2').slideDown();
	}
}

var fullwaf_count;
var fullwaf;
function nfwjs_fullwaf_countdown() {

	if ( fullwaf_count > 1 ) {
		fullwaf_count--;
		jQuery('#nfw-waf-count').html( fullwaf_count  );

	} else {
		clearInterval( fullwaf );
		location.reload();
	}
}

// =====================================================================
// Statistics page.

function nfwjs_stat_redir(where) {
	if (where == '') { return false;}
	document.location.href='?page=NinjaFirewall&tab=statistics&statx='+ where;
}

// =====================================================================
// Firewall Options.

function nfwjs_default_msg() {
	var msg = jQuery('#default-msg').val();
	jQuery('#blocked-msg').val( msg );

}

var restoreconf = 0;

function nfwjs_select_backup( what ) {
	if ( what == 0 ) {
		restoreconf = 0;
	} else {
		restoreconf = 1;
	}
}

function nfwjs_save_options() {
	if ( restoreconf > 0 ) {
		if ( confirm( nfwi18n.restore_warning ) ) {
			return true;
		}
		return false;
	}
	return true;
}

// =====================================================================
// Firewall Policies.

function nfwjs_upload_onoff( id ) {
	if ( id.value == 1 ) {
		jQuery('#san').prop('disabled', false);
		jQuery('#subs').prop('disabled', false);
	} else {
		jQuery('#san').prop('disabled', true);
		jQuery('#subs').prop('disabled', true);
	}
}

function nfwjs_sanitise( cbox ) {
	if ( cbox.checked ) {
		if ( confirm( nfwi18n.warn_sanitise) ) {
			return true;
		}
		return false;
	}
}

function nfwjs_ssl_warn( item, is_ssl ) {
	if ( is_ssl == true || item.checked == false ) {
		return true;
	}
	if ( confirm( nfwi18n.ssl_warning ) ) {
		return true;
	}
	return false;
}

function nfwjs_csp_onoff( id1, id2 ) {
	if ( jQuery('#'+ id1).prop('checked') == true ) {
		jQuery('#'+ id2).prop('readonly', false);
		jQuery('#'+ id2).focus();
	} else {
		jQuery('#'+ id2).prop('readonly', true);
	}
}

function nfwjs_referrer_onoff() {
	if ( jQuery('#referrer_switch').prop('checked') == true ) {
		jQuery('#rp_select').prop('disabled', false);
		jQuery('#rp_select').focus();
	} else {
		jQuery('#rp_select').prop('disabled', true);
	}
}

// =====================================================================
// File Check.

function nfwjs_file_info(what, where) {
	if ( what == '' ) { return false; }

	// Because we use a "multiple" select for aesthetic purposes
	// but don't want the user to select multiple files, we focus
	// only on the currently selected one:
	var current_item = jQuery('#select-'+ where ).prop('selectedIndex');
	jQuery('#select-'+ where ).prop('selectedIndex',current_item);

	// New file
	if (where == 1) {
		var nfo = what.split(':');
		jQuery('#new_size').html( nfo[3] );
		jQuery('#new_chmod').html( nfo[0] );
		jQuery('#new_uidgid').html( nfo[1] + ' / ' + nfo[2] );
		jQuery('#new_mtime').html( nfo[4].replace(/~/g, ':') );
		jQuery('#new_ctime').html( nfo[5].replace(/~/g, ':') );
		jQuery('#table_new').show();

	// Modified file
	} else if (where == 2) {
		var all = what.split('::');
		var nfo = all[0].split(':');
		var nfo2 = all[1].split(':');
		jQuery('#mod_size').html( nfo[3] );
		if (nfo[3] != nfo2[3]) {
			jQuery('#mod_size2').html( '<font color="red">'+ nfo2[3] +'</font>' );
		} else {
			jQuery('#mod_size2').html( nfo2[3] );
		}
		jQuery('#mod_chmod').html( nfo[0] );
		if (nfo[0] != nfo2[0]) {
			jQuery('#mod_chmod2').html( '<font color="red">'+ nfo2[0] +'</font>' );
		} else {
			jQuery('#mod_chmod2').html( nfo2[0] );
		}
		jQuery('#mod_uidgid').html( nfo[1] + ' / ' + nfo[2] );
		if ( (nfo[1] != nfo2[1]) || (nfo[2] != nfo2[2]) ) {
			jQuery('#mod_uidgid2').html( '<font color="red">'+ nfo2[1] + '/' + nfo2[2] +'</font>' );
		} else {
			jQuery('#mod_uidgid2').html( nfo2[1] + ' / ' + nfo2[2] );
		}
		jQuery('#mod_mtime').html( nfo[4].replace(/~/g, ':') );
		if (nfo[4] != nfo2[4]) {
			jQuery('#mod_mtime2').html( '<font color="red">'+ nfo2[4].replace(/~/g, ':') +'</font>' );
		} else {
			jQuery('#mod_mtime2').html( nfo2[4].replace(/~/g, ':') );
		}
		jQuery('#mod_ctime').html( nfo[5].replace(/~/g, ':') );
		if (nfo[5] != nfo2[5]) {
			jQuery('#mod_ctime2').html( '<font color="red">'+ nfo2[5].replace(/~/g, ':') +'</font>' );
		} else {
			jQuery('#mod_ctime2').html( nfo2[5].replace(/~/g, ':') );
		}
		jQuery('#table_mod').show();
	}
}

function nfwjs_del_snapshot() {
	if ( confirm( nfwi18n.del_snapshot ) ) {
		return true;
	}
	return false;
}

function nfwjs_show_changes() {
	jQuery('#changes_table').slideDown();
	jQuery('#vcbtn').prop('disabled', true);
}

// =====================================================================
// Event Notifications.

function ac_radio_toogle( on_off, rbutton ) {
	var what = "nfw_options["+rbutton+"]";
	if ( on_off ) {
		document.nfwalerts.elements[what].disabled = false;
		document.nfwalerts.elements[what].focus();
	} else {
		document.nfwalerts.elements[what].disabled = true;
	}
}

// =====================================================================
// Login Protection.

function nfwjs_auth_user_valid() {
	var e = document.bp_form.elements['nfw_options[auth_name]'];
	if ( e.value.match(/[^-\/\\_.a-zA-Z0-9 ]/) ) {
		alert( nfwi18n.invalid_char );
		e.value = e.value.replace(/[^-\/\\_.a-zA-Z0-9 ]/g,'');
		return false;
	}
	if (e.value == 'admin') {
		alert( nfwi18n.no_admin );
		e.value = '';
		return false;
	}
}

function nfwjs_realm_valid() {
	var e = document.getElementById('realm').value;
	if ( e.length >= 1024 ) {
		alert( nfwi18n.max_char );
		return false;
	}
}

function nfwjs_getpost(request){
	if ( request == 'GETPOST' ) {
		request = 'GET/POST';
	}
	document.getElementById('get_post').innerHTML = request;
}

function nfwjs_toggle_submenu( enable ) {
	if ( enable == 0 ) {
		// Disable protection
		bf_enable = 0;
		jQuery('#submenu_table').slideUp();
		jQuery('#bf_table').slideUp();
		jQuery('#bf_table_extra').slideUp();
		jQuery('#bf_table_password').slideUp();
		jQuery('#bf_table_captcha').slideUp();
	} else {
		bf_enable = enable;
		jQuery('#submenu_table').slideDown();
		// Display the right table (captcha or password protection)
		nfwjs_toggle_table( enable, bf_type );
		jQuery('#bf_table_extra').slideDown();
	}
	// Enable/disable write to auth log
	if ( bf_enable == 1 ) {
		jQuery('#nfw-authlog').prop('disabled', false);
	} else {
		jQuery('#nfw-authlog').prop('disabled', true);
	}
}

function nfwjs_toggle_table( enable, type ) {
	if ( type == 1 ) {
		// Captcha
		bf_type = 1;
		if ( enable == 1 ) {
			// Yes, if under attack
			jQuery('#bf_table').slideDown();
		} else {
			// Always ON
			jQuery('#bf_table').slideUp();
		}
		jQuery('#bf_table_password').slideUp();
		jQuery('#bf_table_captcha').slideDown();
	} else { // type == 2
		//  Password
		bf_type = 0;
		if ( enable == 1 ) {
			// Yes, if under attack
			jQuery('#bf_table').slideDown();
		} else {
			// Always ON
			jQuery('#bf_table').slideUp();
		}
		jQuery('#bf_table_password').slideDown();
		jQuery('#bf_table_captcha').slideUp();
	}
}

function check_login_fields() {

	if ( jQuery('#ui-enabled').prop('checked') == false ) {
		return true;
	}

	if ( bf_enable < 1 ) {
		alert( nfwi18n.select_when );
		return false;
	}

	if ( document.bp_form.elements['nfw_options[bf_type]'].value == 0 ) {
		if ( document.bp_form.elements['nfw_options[auth_name]'].value == '' && document.bp_form.elements['nfw_options[auth_pass]'].value == '' ) {
			alert( nfwi18n.missing_auth );
			return false;
		}
	}
	return true;
}

// =====================================================================
// Firewall Log.

function nfwjs_check_key() {

	var pubkey = jQuery('#clogs-pubkey').val();
	if ( pubkey == '' ) {
		return false;
	}
	if (! pubkey.match( /^[a-f0-9]{40}:(?:[a-f0-9:.]{3,39}|\*)$/) ) {
		jQuery('#clogs-pubkey').focus();
		alert( nfwi18n.invalid_key );
		return false;
	}
}

// =====================================================================
// Live Log.

var livelog = 0;
var livecls = 0;
var lines = 0;
var livecount = 1;
var counter = 0;

function nfwjs_livelog() {

	if ( jQuery('#livelog-switch').prop('checked') == false ) {
		nfwjs_livelog_stop();
		return true;
	}
	jQuery('#idtxtlog').prop('disabled', false);
	if ( jQuery('#idtxtlog').val() == '' ) {
		jQuery('#idtxtlog').val( nfwi18n.no_traffic +' '+ liveinterval/1000 + nfwi18n.seconds +"\n" );
	}
	if ( scroll == 1 ) {
		document.getElementById('idtxtlog').scrollTop = document.getElementById('idtxtlog').scrollHeight;
	}
	jQuery('#nfw-progress').show();

	counter = setInterval( nfwjs_countdown, 1000 );
	livelog = setInterval( nfwjs_start_livelog, liveinterval );
}

function nfwjs_countdown() {
	if ( livecount <= ( liveinterval/1000 ) ) {
		jQuery('#nfw-progress').val( livecount++ );
	}
	jQuery('#nfw-progress').attr( 'max', liveinterval/1000 );
}

function nfwjs_start_livelog() {
	// Send HTTP request
	var data = {
		'livecls': livecls,
		'lines': lines
	};
	jQuery.ajax( {
		type: "POST",
		url: site_url,
		data: data,
		dataType: "text",
		success: function( response ) {
			if ( response == '' ) {
				jQuery('#idtxtlog').val( nfwi18n.no_traffic +' '+ liveinterval/1000 + nfwi18n.seconds +"\n" );
			} else if ( response != '*' ) {
				if ( response.charAt(0) != '^' ) {
					jQuery('#idtxtlog').val( nfwi18n.err_unexpected + "\n\n" + response );
					// Stop
					nfwjs_livelog_stop( 'force' );
					return;
				} else {
					var line = response.substr(1);
					// Get number of lines
					var res = line.split(/\n/).length - 1;
					// Work around for old IE bug
					if (! res ) { res = 1; }
					if ( lines == 0 ) {
						document.liveform.txtlog.value = line;
					} else {
						document.liveform.txtlog.value += line;
					}
					lines += res;
					if ( scroll ) {
						document.getElementById('idtxtlog').scrollTop = document.getElementById('idtxtlog').scrollHeight;
					}
				}
			}
		},
		// Display non-200 HTTP response
		error: function( xhr, status, err ) {
			if ( xhr.status == 404 ) {
				document.liveform.txtlog.value += nfwi18n.error_404 +' '+ site_url + "\n";
			} else if ( xhr.status == 503 ) {
				document.liveform.txtlog.value += nfwi18n.log_not_found + "\n";
			} else {
				document.liveform.txtlog.value += nfwi18n.http_error +' '+ xhr.status + "\n";
			}
			// Stop
			nfwjs_livelog_stop( 'force' );
			return;
		}
	});
	livecls = 0;
	livecount = 1;

	if ( jQuery('#idtxtlog').val() == '' ) {
 		jQuery('#idtxtlog').val( nfwi18n.no_traffic +' '+ liveinterval/1000 + nfwi18n.seconds +"\n" );
	}
	return false;
}

function nfwjs_livelog_stop( force ) {
	// Clear timer
	if ( livelog != 0 ) {
		clearInterval( livelog );
		livelog = 0;

		clearInterval( counter );
		livecount = 1;
	}

	jQuery('#nfw-progress').hide();
	jQuery('#nfw-progress').val( 0 );

	var textarea = jQuery('#idtxtlog').val();
	if ( textarea.includes( nfwi18n.no_traffic ) ) {
		jQuery('#idtxtlog').val( '' );
		jQuery('#idtxtlog').prop( 'disabled', true );
	}

	lines = 0;

	if ( force == 'force' ) {
		jQuery('#livelog-switch').prop('checked', false);
	}
}

function nfwjs_cls() {
	if ( jQuery('#livelog-switch').prop('checked') == true ) {
		jQuery('#idtxtlog').val( nfwi18n.no_traffic +' '+ liveinterval/1000 + nfwi18n.seconds +"\n" );
	} else {
		jQuery('#idtxtlog').val( '' );
	}
	livecls = 1;
	lines = 0;
}

function nfwjs_change_int( intv ) {
	liveinterval = intv;
	nfwjs_create_cookie( 'nfwintval', intv );
	// We must restart
	if ( livelog != 0 ) {
		clearInterval( livelog );
		livelog = 0;
		var textarea = jQuery('#idtxtlog').val();
		if ( textarea.includes( nfwi18n.no_traffic ) ) {
			jQuery('#idtxtlog').val( nfwi18n.no_traffic +' '+ liveinterval/1000 + nfwi18n.seconds +"\n" );
		}
		livelog = setInterval( nfwjs_start_livelog, liveinterval );

		clearInterval( counter );
		livecount = 1;
		counter = setInterval( nfwjs_countdown, 1000 );
	}
}

function nfwjs_is_scroll() {
	if ( jQuery('#livescroll').prop('checked') == true ) {
		scroll = 1;
		if ( livelog != 0 ) {
			document.getElementById('idtxtlog').scrollTop = document.getElementById('idtxtlog').scrollHeight;
		}
		nfwjs_create_cookie( 'nfwscroll', scroll );
	} else {
		scroll = 0;
		nfwjs_delete_cookie( 'nfwscroll' )
	}
}

function nfwjs_create_cookie( name, value ) {
	var d = new Date();
	d.setTime(d.getTime() + ( 365 * 24 * 60 * 60 * 1000) );
	var expires = "expires=" + d.toUTCString();
	document.cookie = name +'=' + value + "; " + expires;
}

function nfwjs_delete_cookie( name ) {
	document.cookie = name + '=; expires=Thu, 01 Jan 1970 00:00:01 GMT;';
}

function nfwjs_lv_select( value ) {
	if ( value > 0 ) {
		jQuery('#lr-disabled').prop('disabled', false);
		jQuery('#lr-disabled').focus();
	} else {
		jQuery('#lr-disabled').prop('disabled', true);
	}
}

// =====================================================================
// About.

function nfwjs_about_table(table_id) {
	var av_table = [11, 12, 13, 14, 15];
	for (var i = 0; i < av_table.length; ++i) {
		if ( table_id == av_table[i] ) {
			jQuery("#" + table_id).slideDown();
		} else {
			jQuery("#" + av_table[i]).slideUp();
		}
	};
}
var dgs=0;
function nfw_eg() {
	setTimeout('nfw_eg()',5);if(dgs<180){++dgs;
		document.body.style.webkitTransform = 'rotate('+dgs+'deg)';
		document.body.style.msTransform = 'rotate('+dgs+'deg)';
		document.body.style.transform = 'rotate('+dgs+'deg)';
	}
	document.body.style.overflow='hidden';
}

// =====================================================================
// EOF
