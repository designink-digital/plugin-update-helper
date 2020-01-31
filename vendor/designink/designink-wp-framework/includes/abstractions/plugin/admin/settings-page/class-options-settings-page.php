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

use Designink\WordPress\Framework\v1_0_1\Plugin\Admin\Settings_Page;

if ( ! class_exists( '\Designink\WordPress\Framework\v1_0_1\Plugin\Admin\Settings_Page\Options_Settings_Page', false ) ) {

	/**
	 * A class to abstract and automate the process of creating a page under the 'settings' menu item.
	 */
	abstract class Options_Settings_Page extends Settings_Page {

		/**
		 * Construct the parent settings page.
		 */
		public function __construct() {
			parent::__construct();
		}

		/**
		 * The inherited, abstract menu item function.
		 */
		final protected static function add_menu_item() {
			add_options_page(
				__( static::page_title() ),
				__( static::menu_title() ),
				static::page_capability(),
				static::page_option_group(),
				array( static::class, 'render')
			);
		}

	}

}
