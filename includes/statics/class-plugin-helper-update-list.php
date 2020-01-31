<?php
/**
 * DesignInk Plugin Update Helper
 *
 * This source file is subject to the GNU General Public License v3.0
 * that is bundled with this package in the file license.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.gnu.org/licenses/gpl-3.0.html
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to answers@designdigitalsolutions.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade the plugin to newer
 * versions in the future. If you wish to customize the plugin for your
 * needs please refer to https://designinkdigital.com
 *
 * @package   Designink/WordPress/Plugin_Update_Helper
 * @author    DesignInk Digital
 * @copyright Copyright (c) 2008-2020, DesignInk, LLC
 * @license   http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License v3.0
 */

namespace Designink\WordPress\Plugin_Update_Helper\v1_0_0;

defined( 'ABSPATH' ) or exit;

if ( ! class_exists( 'Designink\WordPress\Plugin_Update_Helper\v1_0_0\Plugin_Helper_Update_List', false ) ) {

	/**
	 * A class to control the filter containing the plugins registering to be checked.
	 */
	final class Plugin_Helper_Update_List {

		/** @var string The name of the filter used to store hosted plugin information. */
		const PLUGIN_UPDATE_LIST_FILTER = 'designink_plugin_update_list';

		/**
		 * Return the filter list holding all registered plugins.
		 */
		final public static function get_list() {
			return apply_filters( self::PLUGIN_UPDATE_LIST_FILTER, array() );
		}

		/**
		 * Add a plugin to the list of plugins checking for updates.
		 * 
		 * @param string $slug The slug of the plugin checking for updates.
		 * @param string $url The URL the plugin should check for updates at.
		 */
		final public static function add_plugin( string $slug, string $url ) {
			add_filter( self::PLUGIN_UPDATE_LIST_FILTER, function( array $plugins ) use( $slug, $url ) {
				if ( ! array_key_exists( $slug, $plugins ) ) {
					$plugins[ $slug ] = $url;
				}

				return $plugins;
			} );
		}

	}

}