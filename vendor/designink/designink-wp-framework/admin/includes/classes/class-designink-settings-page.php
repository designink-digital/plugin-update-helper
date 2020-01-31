<?php
/**
 * DesignInk Utilities Plugin
 *
 * This source file is subject to the GNU General Public License v3.0
 * that is bundled with this package in the file license.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.gnu.org/licenses/gpl-3.0.html
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to answers@designinkdigital.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade the plugin to newer
 * versions in the future. If you wish to customize the plugin for your
 * needs please refer to https://designinkdigital.com
 *
 * @author    DesignInk Digital
 * @copyright Copyright (c) 2008-2020, DesignInk, LLC
 * @license   http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License v3.0
 */

namespace Designink\WordPress\Framework\v1_0_1\Plugin\Admin\Settings_Page;

defined( 'ABSPATH' ) or exit;

use Designink\WordPress\Framework\v1_0_1\Plugin\Admin\Settings_Page\Options_Settings_Page;

if ( ! class_exists( '\Designink\WordPress\Framework\v1_0_1\Plugin\Admin\Settings_Page\Designink_Settings_Page', false ) ) {

	/**
	 * The options page configuration for general settings regarding the modules included in this plugin.
	 */
	final class Designink_Settings_Page extends Options_Settings_Page {

		/** @var string The page option group. */
		final public static function page_option_group() { return 'designink-settings'; }

		/** @var string The page title. */
		final public static function page_title() { return 'DesignInk Settings'; }

		/** @var string The page menu title. */
		final public static function menu_title() { return 'DesignInk Settings'; }

		/** @var string The page capability. */
		final public static function page_capability() { return 'manage_options'; }

		/**
		 * Construct the parent model.
		 */
		final public function __construct() {
			parent::__construct();
		}

	}

}
