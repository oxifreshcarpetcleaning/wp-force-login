<?php
/*
Plugin Name: Force Login
Plugin URI: http://vess.me/
Description: Easily hide your WordPress site from public viewing by requiring visitors to log in first. Activate to turn on, see FAQ for whitelisting instructions. Modified version by Oxi https://github.com/oxifreshcarpetcleaning/wp-force-login
Version: 3.2.1
Author: Kevin Vess, Modified By Oxi Fresh
Author URI: http://vess.me/
License: GPLv2 or later
*/

/*
This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
*/

function v_forcelogin() {
	if ( ! is_user_logged_in() ) {
		// Get URL
		$url = isset( $_SERVER['HTTPS'] ) && 'on' === $_SERVER['HTTPS'] ? 'https' : 'http';
		$url .= '://' . $_SERVER['HTTP_HOST'];
		$url .= in_array( $_SERVER['SERVER_PORT'], array( '80', '443' ) ) ? '' : ':' . $_SERVER['SERVER_PORT'];
		$url .= $_SERVER['REQUEST_URI'];

		// Apply filters
		$whitelist    = apply_filters( 'v_forcelogin_whitelist', array() );
		$redirect_url = apply_filters( 'v_forcelogin_redirect', $url );

		// Redirect visitors
		// Modified by Oxi - Do not consider query parameters in whitelist matching

		$urlNoQuery = preg_replace( '/\?.*/', '', $url );

		if ( $urlNoQuery != preg_replace( '/\?.*/', '', wp_login_url() ) && ! in_array( $urlNoQuery, $whitelist ) ) {
			// Check for wildcards in the whitelist
			$wildcardMatch = false;
			foreach ( $whitelist as $whitelistItem ) {
				preg_match( "/^" . str_replace( '/', '\\/', $whitelistItem ) . "/", $urlNoQuery, $matches );
				if ( $matches ) {
					$wildcardMatch = true;
					break;
				}
			}

			if ( ! $wildcardMatch ) {
				wp_safe_redirect( wp_login_url( $redirect_url ), 302 );
				exit();
			}
		}
	}
}

add_action( 'init', 'v_forcelogin' );
