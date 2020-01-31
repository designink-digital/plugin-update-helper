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

use Designink\WordPress\Framework\v1_0_1\Module;
use Designink\WordPress\Framework\v1_0_1\Admin\Admin_Notice_Queue;

if ( ! class_exists( '\Designink\WordPress\Framework\v1_0_1\Plugin\Admin\Admin_Notice_Module', false ) ) {

	/**
	 * This module holds the logic for saving our admin notices as transients and displaying them on an admin page load.
	 */
	final class Admin_Notice_Module extends Module {

		/**
		 * Module entry point.
		 */
		final public static function construct() {
			add_action( 'admin_notices', array( get_class(), '_admin_notices' ) );
		}

		/**
		 * WordPress 'admin_notices' hook.
		 */
		final public static function _admin_notices() {
			Admin_Notice_Queue::print_notices();
		}

	}

}