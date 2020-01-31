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

if ( ! class_exists( '\Designink\WordPress\Framework\v1_0_1\Plugin\Admin\Meta_Box', false ) ) {

	/**
	 * A manager for Meta Boxes that appear on admin edit pages. Manages the form rendering and post save hooking processes.
	 */
	abstract class Meta_Box extends Singleton {

		/**
		 * @var string $context The placement of the meta box in the page, e.g. 'normal', 'side', or 'advanced'.
		 * @var string $priority The priority of the context, e.g. 'normal', 'low', or 'high'.
		 * @var string|array $screen The ID(s) of the screen(s) that the Meta Box should appear on.
		 */
		public $context, $priority, $screen;

		/** @var array The default arguments. */
		private static $default_arguments = array(
			'context'	=> 'normal',
			'priority'	=> 'default',
			'screen'	=> array(),
		);

		/**
		 * The function that will output the Meta Box HTML. Hooked by { $this->add_meta_box() } through { self::_render() }.
		 */
		abstract protected static function render();

		/**
		 * The code to be run after the _save_post hook checks.
		 * 
		 * @param int $post_id The ID of the Post being saved.
		 * @param \WP_Post $Post A copy of the Post instance being saved.
		 */
		abstract protected static function save_post( int $post_id, \WP_Post $Post = null );

		/**
		 * A meta key to use when saving the Post Meta to the database. Should be lowercase and underscored.
		 * 
		 * @return string The lowercase, underscored meta key.
		 */
		abstract public static function meta_key();

		/**
		 * Return the ID of the Meta Box (used in the "id" attribute, says WordPress docs, i.e. the "id" attribute in the HTML DOM for Javascript events).
		 * The ID should be lowercase and underscored to be consistent with the { $this->meta_key() } value.
		 * 
		 * @return string The slug ID of the Meta Box.
		 */
		abstract public static function get_id();

		/**
		 * The title of the Meta Box.
		 * 
		 * @return string The Meta Box title.
		 */
		abstract public static function get_title();

		/**
		 * A generated nonce for super class forms.
		 * 
		 * @return string The nonce.
		 */
		final public static function get_nonce() { return sprintf( '%s_%s', static::get_id(), static::meta_key() ); }

		/**
		 * A generated nonce action for super class forms.
		 * 
		 * @return string The nonce action.
		 */
		final public static function get_nonce_action() { return sprintf( '%s_%s-action', static::get_id(), static::meta_key() ); }

		/**
		 * Get the HTML name attribute prefix for grouping data inputs in this meta box.
		 * 
		 * @param string $input_name The desired key you would like the input value to be submitted with.
		 * 
		 * @return string The name attribute prefix for inputs.
		 */
		final public static function create_input_name( string $input_name ) { return sprintf( '%s[%s][%s]', static::get_id(), static::meta_key(), $input_name ); }

		/**
		 * Construct the Meta_Box. Set the default properties if they are not set.
		 */
		public function __construct() {
			foreach ( self::$default_arguments as $propety => $value ) {
				if ( ! isset( $this->{ $propety } ) ) {
					$this->{ $propety } = $value;
				}
			}

			add_action( 'save_post', array( $this, '_save_post' ) );
		}

		/**
		 * WordPress hook for 'save_post', verify autosaving and Meta Box nonce.
		 * 
		 * @param int $post_id The ID of the Post being saved.
		 * @param \WP_Post $Post A copy of the Post instance being saved.
		 */
		final public static function _save_post( int $post_id, \WP_Post $Post = null ) {

			if ( ! isset( $_POST[ static::get_nonce() ] ) || ! wp_verify_nonce( $_POST[ static::get_nonce() ], self::get_nonce_action() ) ) {
				return;
			}

			if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
				return;
			}

			if ( ! $Post ) {
				$Post = get_post( $post_id );
			}

			// Call Meta Box super class save_post
			static::save_post( $post_id, $Post );
		}

		/**
		 * The hook to use when adding the meta box to the WordPress environment. The gets called in { $this->add_meta_box() }.
		 */
		final public static function _render() {
			wp_nonce_field( static::get_nonce_action(), static::get_nonce() );
			static::render();
		}

		/**
		 * Add the Meta Box to the WordPress environment.
		 */
		final public function add_meta_box() {
			add_meta_box(
				sprintf( '%s_%s', $this->get_id(), $this->meta_key() ),
				$this->get_title(),
				array( static::class, '_render' ),
				$this->screen,
				$this->context,
				$this->priority
			);
		}

	}

}