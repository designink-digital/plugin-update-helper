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

namespace Designink\WordPress\Framework\v1_0_1;

use Designink\WordPress\Framework\v1_0_1\Utility;
use Designink\WordPress\Framework\v1_0_1\Framework;
use Designink\WordPress\Framework\v1_0_1\Singleton;

defined( 'ABSPATH' ) or exit;

if ( ! class_exists( '\Designink\WordPress\Framework\v1_0_1\Module', false ) ) {

	/**
	 * A class to represent crucial project file system structures and bind their PHP functionalities to WordPress.
	 * 
	 * @since 3.0.0
	 */
	abstract class Module extends Singleton {

		/**
		 * @var bool Whether or not the module is being included under another module.
		 */
		protected $is_submodule = false;

		/**
		 * @var bool Whether or not to load the current module.
		 */
		protected $enabled = true;

		/**
		 * @var string The default directory to locate class files under.
		 */
		protected static $includes_dir = 'includes';

		/**
		 * @var string The default directory for loading modules. (Must be separate from the rest of the includes since it must necessarily be used.).
		 */
		protected static $modules_dir = 'modules';

		/**
		 * @var array A list of subdirectories under static::$includes_dir to automatically search for autoloading.
		 */
		protected static $includes = array( 'statics', 'abstractions', 'classes' );

		/**
		 * @var array A list of modules which have been successfully imported via the current module.
		 */
		protected $loaded_modules = array();

		/**
		 * @var array A list of autoload include folders which have been successfully registered via the current module.
		 */
		protected $loaded_includes = array();

		/**
		 * A protected constructor to ensure only singleton instances of plugins exist.
		 */
		protected function __construct( bool $is_submodule = false ) {
			$this->is_submodule = $is_submodule;

			if ( $this->enabled ) {
				$this->register_submodules();
				$this->autoload_includes();

				if ( ! $this->is_submodule ) {
					$this::construct();
					$this->construct_submodules();
				}
			}
		}
		
		/**
		 * An empty, placeholder function for overriding, this is where the module "starts" it's WordPress code.
		 */
		public static function construct() { }

		/**
		 * The function which gets called when a module is being constructed. This ensures the submodule construct sequences don't get fired when a submodule is first initialized.
		 */
		public static function submodule_construct() {
			if ( static::instance()->enabled ) {
				$instance = static::instance();
				$instance::construct();
				$instance->construct_submodules();
			}
		}

		/**
		 * The function which gets called if this module is being imported as a submodule.
		 */
		final public static function submodule_instance() {
			return static::instance( true );
		}

		/**
		 * Our special function for searching for submodule folders, loading them into the current module and PHP environment.
		 */
		final protected function register_submodules() {
			$modules_directory = sprintf( '%s%s/%s', plugin_dir_path( $this->get_class_reflection()->getFileName() ), static::$includes_dir, static::$modules_dir );

			if ( is_dir( $modules_directory ) ) {
				$files = \Designink\WordPress\Framework\v1_0_1\Utility::scandir( $modules_directory );

				foreach ( $files as $file ) {
					$file_path = sprintf( '%s/%s', $modules_directory, $file );

					if ( is_dir( $file_path ) ) {
						// If loading a Module from a directory
						// Folder name must be the same as the class name
						$module_file = sprintf( 'class-%s.php', Utility::slugify( $file ) );
						$module_path = sprintf( '%s/%s', $file_path, $module_file );
						$this->import_module( Utility::pascal_underscorify( $file ), $module_path );
					} else if ( is_file( $file_path ) ) {
						
						// If loading a Module from a single file
						if ( preg_match( '/class-([a-z-]+)\.php/i', $file_path, $matches ) ) {
							$module_name = Utility::pascal_underscorify( $matches[1] );
							$this->import_module( $module_name, $file_path );
						} else {
							$message_format = "Found a potential module file, but it does not use the correct naming conventions. Skipping file: %s.";
							trigger_error( __( sprintf( $message_format, $file_path ) ), E_USER_WARNING );
						}

					}

				}
			}
		}

		/**
		 * Loop through all loaded modules and run their initialization functions.
		 */
		final protected function construct_submodules() {
			foreach ( $this->loaded_modules as $Module ) {
				if ( $Module instanceof \Designink\WordPress\Framework\v1_0_1\Module ) {
					$Module::submodule_construct();
				}
			}
		}

		/**
		 * Given a Module class name and a file path, loads the file into the PHP environment, then checks to make sure the given class extends Module and adds it to { $this->loaded_modules }
		 * 
		 * @param string $module_name The short Pascal-underscore-case Module class name.
		 * @param string $module_path The full path/file combination pointing to the module file.
		 */
		final protected function import_module( string $module_name, string $module_path ) {

			if ( is_file( $module_path ) ) {

				require_once ( $module_path );
				$namespace = Utility::get_file_namespace( $module_path );

				if ( empty( $namespace ) ) {
					$qualified_name = sprintf( '%s', $module_name );
				} else {
					$qualified_name = sprintf( '%s\%s', $namespace, $module_name );
				}

				if ( class_exists( $qualified_name ) ) {
					$is_module = is_subclass_of( $qualified_name, 'Designink\WordPress\Framework\v1_0_1\Module' );

					if ( $is_module ) {
						$Module = $qualified_name::submodule_instance();
						$this->loaded_modules[ $qualified_name ] = $Module;
					} else {
						$message_format = "Successfully found class, '%s', but it does not appear to be a Module, make sure you are implementing Designink\WordPress\Framework\v1_0_1\Module in '%s'.";
						trigger_error( __( sprintf( $message_format, $module_name, $module_path ) ), E_USER_WARNING );
					}

				} else {
					$message_format = "Successfully required module file, '%s', but could not find specified Module '%s'.";
					trigger_error( __( sprintf( $message_format, $module_path, $module_name ) ), E_USER_WARNING );
				}

			} else {
				$message_format = "Tried to load module, but could not find the module file. Expected module file: '%s'.";
				trigger_error( __( sprintf( $message_format, $module_path ) ), E_USER_WARNING );
			}

		}

		/**
		 * Will iterate through the static class instance's includes and autoload any directories it finds.
		 */
		final protected function autoload_includes() {
			if ( is_array( static::$includes ) ) {
				foreach ( static::$includes as $include ) {
					$__DIR__ = dirname( $this->get_class_reflection()->getFileName() );
					$includes_path = sprintf( '%s/%s/%s', $__DIR__, static::$includes_dir, $include );
					$loaded_directories = Framework::instance()->get_autoloader()->autoload_directory_recursive( $includes_path );
					$this->loaded_includes = array_merge( $this->loaded_includes, $loaded_directories );
				}
			}
		}

		/**
		 * An alias for Designink\WordPress\Framework\v1_0_1\Autoloader::add_autoload_directory()
		 * 
		 * @param string $directory The directory to be searched for potential new classes.
		 * @return bool Whether or not the directory was successfully added to the autoload array.
		 */
		final protected function add_autoload_directory( string $directory ) {
			$this->loaded_includes[] = $directory;
			return designink_framework()->get_autoloader()->add_autoload_directory( $directory );
		}

		/**
		 * Get the currently loaded modules.
		 * 
		 * @return array The modules currently loaded.
		 */
		final public function get_loaded_modules() {
			return $this->loaded_modules;
		}

	}

}
