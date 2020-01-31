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

namespace Designink\WordPress\Framework\v1_0_1\Plugin;

defined( 'ABSPATH' ) or exit;

use Designink\WordPress\Framework\v1_0_1\Plugin;
use Designink\WordPress\Framework\v1_0_1\Utility;

if ( ! class_exists( '\Designink\WordPress\Framework\v1_0_1\Plugin\Admin_Module', false ) ) {

	/**
	 * A class to represent and help deal with common plugin admin functionality.
	 * 
	 * @since 3.0.0
	 */
	abstract class Admin_Module extends Plugin {

		/** @var string The default directory for loading screens. (Will be a subfolder of includes) */
		protected static $screens_dir = 'screens';

		/**
		 * @var array An overridden list of subdirectories under static::$includes_dir to automatically search for autoloading.
		 */
		protected static $includes = array( 'statics', 'abstractions', 'classes', 'meta-boxes' );

		/**
		 * A protected constructor to ensure only singleton instances exist.
		 */
		final protected function __construct() {
			// Code print sugar since admin modules don't have admin modules and this will always be NULL.
			unset( $this->admin_module );
			// TRUE because an admin module is always a submodule.
			parent::__construct( true );
			// Register and construct screens after Modules have been registered and constructed.
			$this->register_available_screens();
		}

		/**
		 * Search for \Designink\WordPress\Framework\v1_0_1\Post_Type classes in the Plugin { static::$post_types_dir } and register them.
		 */
		final private function register_available_screens() {
			$reflection = $this->get_class_reflection();
			$screens_dir = sprintf( '%s%s/%s/', plugin_dir_path( $reflection->getFileName() ), static::$includes_dir, static::$screens_dir );

			if ( is_dir( $screens_dir ) ) {
				$folder_files = Utility::scandir( $screens_dir, 'files' );

				foreach ( $folder_files as $file ) {
					if ( preg_match( '/class-([a-z-]+)\.php/i', $file, $matches ) ) {
						require_once ( $screens_dir . $file );
						$screen_name = Utility::pascal_underscorify( $matches[1] );

						if ( class_exists( $screen_name ) && is_subclass_of( $screen_name, 'Designink\WordPress\Framework\v1_0_1\Plugin\Admin\Screens' ) ) {
							$screen_name::construct();
							$this->loaded_screens[] = $screen_name;
						}
					}
				}
			}
		}

	}

}
