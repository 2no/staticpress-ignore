<?php
/*
Plugin Name: StaticPress Ignore
Author: 2no
Plugin URI: https://bitbucket.org/2no/staticpress-ignore
Description: .
Version: 0.1.0
Author URI: http://www.wakuworks.com/
Text Domain: static-press-ignore
Domain Path: /languages

License:
 Released under the GPL license
  http://www.gnu.org/copyleft/gpl.html

  Copyright 2014 (email : kazunori.ninomiya@gmail.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/
add_action( 'plugins_loaded', function() {
	global $staticpress;
	if ( ! isset( $staticpress ) ) {
		return;
	}

	if ( ! class_exists( 'staticpress_ignore_admin' ) ) {
		require dirname( __FILE__ ) . '/includes/class-staticpress_ignore_admin.php';
	}
	if ( ! class_exists( 'staticpress_ignore' ) ) {
		require dirname( __FILE__ ) . '/includes/class-staticpress_ignore.php';
	}

	new staticpress_ignore( staticpress_ignore_admin::get_option() );
	if ( is_admin() ) {
		new staticpress_ignore_admin();
	}
} );
