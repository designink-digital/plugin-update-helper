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

use Designink\WordPress\Framework\v1_0_1\Utility;
use Designink\WordPress\Framework\v1_0_1\Singleton;
use Designink\WordPress\Framework\v1_0_1\Plugin\Admin\Meta_Box;

if ( ! class_exists( '\Designink\WordPress\Framework\v1_0_1\Plugin\Post_Type', false ) ) {

	/**
	 * In this class, we attempt to define and encapsulate the functionalities of a single "Post Type" in WordPress.
	 * 
	 * @since 3.0.0
	 */
	abstract class Post_Type extends Singleton {

		/** @var array The arguments passed in for registering the Post Type. */
		protected $args;

		/** @var Meta_Box[] A list of Meta Boxes to display for this Post Type. */
		protected $Meta_Boxes = array();

		/**
		 * Get the name of the Post Type.
		 * 
		 * @return string The Post Type name.
		 */
		abstract public static function post_type();

		/**
		 * Get the options for the Post Type.
		 * 
		 * @return array The Post Type options.
		 */
		abstract protected function post_type_options();

		/** @var array The default arguments. */
		private static $default_arguments = array(
			'labels'				=> array(),
			'singular_name'			=> 'Post',
			'plural_name' 			=> 'Posts',
			'description'           => '',
			'public'                => false,
			'hierarchical'          => false,
			'exclude_from_search'   => null,
			'publicly_queryable'    => null,
			'show_ui'               => null,
			'show_in_menu'          => null,
			'show_in_nav_menus'     => null,
			'show_in_admin_bar'     => null,
			'menu_position'         => null,
			'menu_icon'             => null,
			'capability_type'       => 'post',
			'capabilities'          => array(),
			'map_meta_cap'          => null,
			'supports'              => array(),
			'register_meta_box_cb'  => null,
			'taxonomies'            => array(),
			'has_archive'           => false,
			'rewrite'               => true,
			'query_var'             => true,
			'can_export'            => true,
			'delete_with_user'      => null,
			'show_in_rest'          => false,
			'rest_base'             => false,
			'rest_controller_class' => false,
			'_builtin'              => false,
			'_edit_link'            => 'post.php?post=%d',
		);

		/**
		 * Construct the Post Type Singleton.
		 */
		public function __construct() {
			add_action( 'init', array( $this, '_init' ) );
		}

		/**
		 * The WordPress hook for 'init'.
		 */
		final public function _init() {
			$this->register();
		}

		/**
		 * Register the Post Type with WordPress.
		 */
		final protected function register() {
			if ( ! post_type_exists( $this->post_type() ) ) {
				register_post_type( $this->post_type(), $this->export_args() );
			}
		}

		/**
		 * Return the Post Type options and the merged labels.
		 * 
		 * @return array The Post Type modified options.
		 */
		final protected function export_args() {
			$options = $this->post_type_options();
			$merged_labels = array_merge( $this->export_labels(), $options['labels'] );

			return Utility::guided_array_merge(
				self::$default_arguments,
				$options,
				array(
					'labels' => $merged_labels,
				),
				array(
					'register_meta_box_cb' => array( $this, 'register_meta_boxes' ),
				)
			);
		}

		/**
		 * Return the array reperesentation of labels with all the data associated with registering a Post Type.
		 * 
		 * @return array An associative array consisting of the key/value pairs expected with registering a Post Type.
		 */
		final public function export_labels() {
			$options = $this->post_type_options();

			return array(
				'name'						=> _x( $options['plural_name'], 'post type general name' ),
				'singular_name'				=> _x( $options['singular_name'], 'post type singular name' ),
				'menu_name'					=> _x( $options['singular_name'], 'post type singular name' ),
				'add_new'					=> _x( 'Add New', 'post' ),
				'add_new_item'				=> __( sprintf( 'Add New %s', $options['singular_name'] ) ),
				'edit_item'					=> __( sprintf( 'Edit Post', $options['singular_name'] ) ),
				'new_item'					=> __( sprintf( 'New %s', $options['singular_name'] ) ),
				'view_item'					=> __( sprintf( 'View %s', $options['singular_name'] ) ),
				'view_items'				=> __( sprintf( 'View %s', $options['plural_name'] ) ),
				'search_items'				=> __( sprintf( 'Search %s', $options['plural_name'] ) ),
				'not_found'					=> __( sprintf( 'No %s found.', $options['plural_name'] ) ),
				'not_found_in_trash'		=> __( sprintf( 'No %s found in Trash.', $options['plural_name'] ) ),
				'parent_item_colon'			=> null,
				// Conflicts with menu_name
				// 'all_items'					=>  __( sprintf( 'All %s', $options['plural_name'] ) ),
				'archives'					=> __( sprintf( '%s Archives', $options['singular_name'] ) ),
				'attributes'				=> __( sprintf( '%s Attributes', $options['singular_name'] ) ),
				'insert_into_item'			=> __( sprintf( 'Insert into %s', $options['singular_name'] ) ),
				'uploaded_to_this_item'		=> __( 'Uploaded to this post' ),
				'featured_image'			=> _x( 'Featured Image', 'post' ),
				'set_featured_image'		=> _x( 'Set featured image', 'post' ),
				'remove_featured_image'		=> _x( 'Remove featured image', 'post' ),
				'use_featured_image'		=> _x( 'Use as featured image', 'post' ),
				'filter_items_list'			=> __( 'Filter posts list' ),
				'items_list_navigation'		=> __( 'Posts list navigation' ),
				'items_list'				=> __( 'Posts list' ),
				'item_published'			=> __( 'Post published.' ),
				'item_published_privately'	=> __( 'Post published privately.' ),
				'item_reverted_to_draft'	=> __( 'Post reverted to draft.' ),
				'item_scheduled'			=> __( 'Post scheduled.' ),
				'item_updated'				=> __( 'Post updated.' ),
			);
		}

		/**
		 * Add a Meta Box to this Post Type if it is not already added.
		 * 
		 * @param \Designink\WordPress\Framework\v1_0_1\Plugin\Admin\Meta_Box
		 */
		final public function add_meta_box( Meta_Box $Meta_Box ) {
			if ( ! array_key_exists( $Meta_Box->get_id(), $this->Meta_Boxes ) ) {
				$this->Meta_Boxes[ $Meta_Box->get_id() ] = $Meta_Box;
			}
		}

		/**
		 * 
		 * 
		 * @param \Designink\WordPress\Framework\v1_0_1\Plugin\Admin\Meta_Box
		 */
		final public function register_meta_boxes() {
			foreach ( $this->Meta_Boxes as $Meta_Box ) {
				$Meta_Box::instance()->add_meta_box();
			}
		}

	}

}