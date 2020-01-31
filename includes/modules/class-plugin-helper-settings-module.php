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

use Designink\WordPress\Framework\v1_0_1\Module;
use Designink\WordPress\Framework\v1_0_1\Plugin\Admin\Settings_Page\Settings_Section;
use Designink\WordPress\Framework\v1_0_1\Plugin\Admin\Settings_Page\Designink_Settings_Page_Module;

if ( ! class_exists( 'Designink\WordPress\Plugin_Update_Helper\v1_0_0\Plugin_Helper_Settings_Module', false ) ) {

	/**
	 * The module controls the loading of the SSL settings into the DesignInk settings page.
	 */
	final class Plugin_Helper_Settings_Module extends Module {

		/** @var string The name of the plugin updates section in the DesignInk settings page. */
		const SSL_SECTION_NAME = 'designink_plugin_updates_ssl';

		/**
		 * Module entry point.
		 */
		final public static function construct() {
			add_action( 'admin_init', array( __CLASS__, '_admin_init' ) );
		}

		/**
		 * WordPress 'admin_init' hook
		 */
		public static function _admin_init() {
			self::add_ssl_settings();
		}

		/**
		 * Add the SSL key settings to the DesignInk Settings Page.
		 */
		final private static function add_ssl_settings() {
			$Settings_Page = Designink_Settings_Page_Module::$Settings_Page;
			$section_description = 'The SSL key and initialization vector for self-hosted plugin updates if using encryption for a private key.';

			$Settings_Page->add_section( new Settings_Section(
				$Settings_Page,
				self::SSL_SECTION_NAME,
				array(
					'label' => __( "Plugin Update Server Settings" ),
					'description' => __( $section_description ),
					'inputs' => array(
						array(
							'label' => __( "SSL Key" ),
							'name' => 'key'
						)
					),
				)
			) );
		}

		/**
		 * Get the SSL key saved from the DesignInk Settings Page.
		 * 
		 * @return false|string Return the saved SSL key or FALSE if it does not exist.
		 */
		final public static function get_ssl_key() {
			return get_option( '_' . self::SSL_SECTION_NAME, false );
		}

	}

}