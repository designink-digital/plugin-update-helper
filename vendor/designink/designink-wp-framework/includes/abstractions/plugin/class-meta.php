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

if ( ! class_exists( '\Designink\WordPress\Framework\v1_0_1\Plugin\Meta', false ) ) {

	/**
	 * An abstract class for other abstract classes to inherit regarding saving meta values in standard form in the WordPress database.
	 * User Meta and Post Meta abstract classes for example will inherently extend this class.
	 */
	abstract class Meta {

		/**
		 * @var string $single Whether the meta data is being updated as a single key or with multiple keys.
		 */
		protected $single = true;

		/**
		 * @var mixed $data The current array of multiple-key meta values, or the current, unseriealized value of a single key.
		 */
		protected $data;

		/**
		 * The meta key used in the database.
		 * 
		 * @return string The meta database key.
		 */
		abstract public static function meta_key();

		/**
		 * The function used to load meta data from the database.
		 * 
		 * @return mixed The meta data from the database.
		 */
		abstract public function get_meta();

		/**
		 * The function used to save meta data.
		 * 
		 * @return bool The result of saving the meta to the database. Nota bene: With WordPress functions, the result may return FALSE if the data was the same.
		 */
		abstract public function save_meta();

		/**
		 * Create a data structure that should be saved in it's own format in the database.
		 * 
		 * @return mixed The data as it should be saved into the database.
		 */
		abstract public function export_meta();

		/**
		 * Get the meta data of this instance.
		 * 
		 * @return mixed The meta data.
		 */
		final protected function get_data() { return $this->data; }

		/**
		 * Construct the Meta. Load the data from the database.
		 */
		public function __construct() {
			$this->import_meta();
		}

		/**
		 * Set the meta data of this instance.
		 * 
		 * @param mixed $data The meta data.
		 */
		final protected function set_data( $data ) {

			if ( $data instanceof object ) {
				trigger_error( __( "It is not recommended to serialize objects into WordPress meta values." ), E_USER_NOTICE );
			}

			$this->data = $data;
		}

		/**
		 * Load the data and set each property that exists on the clas.
		 */
		final protected function import_meta() {
			$this->data = $this->get_meta();

			if ( is_array( $this->data ) ) {
				foreach ( $this->data as $property => $value ) {
					if ( property_exists( $this, $property ) ) {
						$this->{ $property } = $value;
					}
				}
			}
		}

	}

}