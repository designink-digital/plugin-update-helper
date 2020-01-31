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

namespace Designink\WordPress\Framework\v1_0_1\Plugin\Admin;

defined( 'ABSPATH' ) or exit;

use Designink\WordPress\Framework\v1_0_1\Singleton;
use Designink\WordPress\Framework\v1_0_1\Plugin\Admin\Settings_Page\Settings_Section;
use Designink\WordPress\Framework\v1_0_1\Plugin\Admin\Settings_Page\Settings_Page_Interface;

if ( ! class_exists( '\Designink\WordPress\Framework\v1_0_1\Plugin\Admin\Settings_Page', false ) ) {

	/**
	 * A class to abstract and automate the process of building settings pages.
	 * 
	 * @since 3.0.0
	 */
	abstract class Settings_Page extends Singleton implements Settings_Page_Interface {

		//// - Variables - ////

		/** @var \Designink\WordPress\Framework\v1_0_1\Plugin\Admin\Settings_Page\Settings_Section[] The list of Sections attached to this Page. */
		private $Sections = array();

		/**
		 * Return the Sections associated with this Page.
		 * 
		 * @return \Designink\WordPress\Framework\v1_0_1\Plugin\Admin\Settings_Page\Settings_Section[] The Sections of this Page.
		 */
		final public static function get_sections() { return $this->Sections; }

		//// - Abstracts - ////

		/**
		 * The function which will be called to add the page to the menu.
		 */
		abstract protected static function add_menu_item();

		//// - Constructor - ////

		/**
		 * Add action for creating submenu page.
		 */
		protected function __construct() {
			add_action( 'admin_menu', array( static::class, '_admin_menu' ) );
		}

		/**
		 * The hook for 'admin_menu'. Creates the submenu page.
		 */
		final public static function _admin_menu() {
			static::add_menu_item();
		}

		/**
		 * Get all of the settings for this page and display them.
		 */
		final public static function render() {
			?>

				<form action="options.php" method="POST">
					<!-- Display nonce and hidden inputs for the Page -->
					<?php settings_fields( static::page_option_group() ); ?>
					<!-- Render the sections -->
					<?php do_settings_sections( static::page_option_group() ); ?>
					<!-- Create submit button -->
					<?php submit_button('Save Settings'); ?>
				</form>

			<?php
		}

		/**
		 * Register a section with this Page.
		 * 
		 * @param \Designink\WordPress\Framework\v1_0_1\Plugin\Admin\Settings_Page\Settings_Section $Settings_Section The Section to add to this Page.
		 */
		final public function add_section( Settings_Section $Settings_Section ) {
			$this->Sections[] = $Settings_Section;
		}

	}

}
