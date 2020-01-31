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

// Require the DesignInk Framework for settings pages and utilities.
require_once __DIR__ . '/vendor/designink/designink-wp-framework/index.php';

use Designink\WordPress\Framework\v1_0_1\Module;

if ( ! class_exists( 'Designink\WordPress\Plugin_Update_Helper\v1_0_0\Plugin_Update_Helper', false ) ) {

	/**
	 * This helper module helps plugins hosted using the DesignInk Plugin Update Server connect and get the latest information about releases from their servers.
	 */
	final class Plugin_Update_Helper extends Module { }

	// Load helper
	Plugin_Update_Helper::instance();

}
