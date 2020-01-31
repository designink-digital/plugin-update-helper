<?php
/**
 * DesignInk WordPress Framework
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
 * @package   Designink/WordPress/Framework
 * @author    DesignInk Digital
 * @copyright Copyright (c) 2008-2020, DesignInk, LLC
 * @license   http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License v3.0
 */

namespace Designink\WordPress\Framework\v1_0_1\Plugin\Admin\Settings_Page;

defined( 'ABSPATH' ) or exit;

if ( ! interface_exists( '\Designink\WordPress\Framework\v1_0_1\Plugin\Admin\Settings_Page\Settings_Page_Interface', false ) ) {

	interface Settings_Page_Interface {

		/**
		 * The page slug for registering settings, adding menu items, etc.
		 * 
		 * @return string The page slug.
		 */
		public static function page_option_group();

		/**
		 * The page name/title for display.
		 * 
		 * @return string The page title.
		 */
		public static function page_title();

		/**
		 * The menu name/title for display.
		 * 
		 * @return string The menu title.
		 */
		public static function menu_title();

		/**
		 * The capability required for the page to be displayed to the user.
		 * 
		 * @return string The capability required to display the settings page.
		 */
		public static function page_capability();

	}

}
