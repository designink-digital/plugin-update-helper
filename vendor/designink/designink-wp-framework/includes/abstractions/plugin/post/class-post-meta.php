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

namespace Designink\WordPress\Framework\v1_0_1\Plugin\Post;

defined( 'ABSPATH' ) or exit;

use Designink\WordPress\Framework\v1_0_1\Plugin\Meta;

if ( ! class_exists( '\Designink\WordPress\Framework\v1_0_1\Plugin\Post\Post_Meta', false ) ) {

	/**
	 * An abstract for dealing with Post Meta.
	 */
	abstract class Post_Meta extends Meta {

		/** @var \WP_Post The Post that this Meta belongs to. */
		protected $Post;

		/**
		 * Construct construct the Post Meta, instantiate Post if necessary, call parent constructor.
		 * 
		 * @param int|\WP_Post
		 */
		public function __construct( $post ) {

			// If no Post given.
			if ( empty( $post ) ) {
				trigger_error( __( "Specified post passed to Designink\WordPress\Framework\v1_0_1\Posts\Post_Meta constructor was empty." ), E_USER_WARNING );
				return;
			}

			if ( is_numeric( $post ) ) {
				// Find Post
				$Post = get_post( $post );

				if ( empty( $Post ) ) {
					trigger_error( __( "Could not find Post specified by ID passed Designink\WordPress\Framework\v1_0_1\Posts\Post_Meta constructor." ), E_USER_WARNING );
					return;
				}

				$this->Post = $Post;
			} else if ( $post instanceof \WP_Post ) {
				// Else Post was given
				$this->Post = $post;
			}

			// Construct parent
			parent::__construct();

		}

		/**
		 * The required abstract loading function.
		 * 
		 * @return mixed The Post Meta data.
		 */
		final public function get_meta() {
			return get_post_meta( $this->Post->ID, static::meta_key(), $this->single );
		}

		/**
		 * Save the instance data to the database.
		 * 
		 * @return bool Whether or not the Post data was saved.
		 */
		final public function save_meta() {
			return update_post_meta( $this->Post->ID, static::meta_key(), $this->export_meta() );
		}

	}

}