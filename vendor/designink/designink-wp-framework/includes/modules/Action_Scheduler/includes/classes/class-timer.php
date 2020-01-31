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

namespace Designink\WordPress\Framework\v1_0_1\Action_Scheduler;

defined( 'ABSPATH' ) or exit;

use Designink\WordPress\Framework\v1_0_1\Utility;
use Designink\WordPress\Framework\v1_0_1\Action_Scheduler\Action;

if ( ! class_exists( '\Designink\WordPress\Framework\v1_0_1\Action_Scheduler\Timer', false ) ) {

	/**
	 * A Timer template for the Action Scheduler system that other timer classes can extend and implement their own settings for.
	 */
	abstract class Timer {

		/**
		 * The exportable properties.
		 * 
	 	 * @var string	$id				The unique timer string ID.
		 * @var int		$last_run		The timestamp for when the Timer last ran its Actions.
		 * @var array	$timer_class	The name of the Timer super-class that this instance refers to.
		 */
		public $id, $last_run, $timer_class;

		/** @var array The default arguments for this class. Should match the exportable properties. */
		private static $default_arguments = array(
			'actions_data'	=> array(),
			'id'			=> null,
			'last_run'		=> null,
			'timer_class'	=> null,
		);

		/** @var Action[] $Actions The instantiated list of actions to fire for this timer. */
		protected $Actions;

		/** @var array $actions_data The bare array representation of Action instances. */
		protected $actions_data = array();

		/**
		 * Abstract function to print form options for the form builder.
		 * 
		 * @param string $group The form name to use when building the form.
		 */
		abstract public static function print_form( string $group );

		/**
		 * Abstract function which returns a timestamp of when the last run of Actions is/was supposed to be. This allows timers to implement their own system for deciding the timer sequence without being bound to a static interval.
		 * 
		 * @return \DateTime The next run time.
		 */
		abstract public function get_next_run();

		/**
		 * Abstract function to cover the exporting of properties of super classes in { $this->to_array() }
		 * 
		 * @return array The exportable properties and values of the super class.
		 */
		abstract protected function export_array();

		/**
		 * Get the last run time. Returns in GMT.
		 * 
		 * @return \DateTime The last run time.
		 */
		final public function get_last_run() {

			if ( null === $this->last_run ) {
				return null;
			} else {
				return new \DateTime( sprintf( '@%s', $this->last_run ), new \DateTimeZone( 'GMT' ) );
			}

		}

		/**
		 * Construct a Timer given an ID and a set of options.
		 * 
		 * @param string $timer_id The ID of the Timer to construct.
		 * @param array $options A list of options to provide the Timer and it's super-class constructors.
		 */
		public function __construct( string $timer_id, array $options ) {

			$options = Utility::guided_array_merge( self::$default_arguments, $options );

			if ( empty( $timer_id ) ) {
				trigger_error( __( "A valid id must be passed to Designink\\Action_Scheduler\\Timer constructor." ), E_USER_WARNING );
				return false;
			}

			foreach ( $options as $property => $value ) {
				if ( property_exists( $this, $property ) ) {
					$this->{ $property } = $value;
				}
			}

			$this->id = $timer_id;
			$this->timer_class = ( new \ReflectionClass( $this ) )->getName();
			$this->create_action_instances();
		}

		/**
		 * Loop through the initial array of actions and load their instances.
		 */
		final private function create_action_instances() {
			$this->Actions = array();

			foreach ( $this->actions_data as $action_id => $action ) {
				$Action = new Action( $action_id, $action );
				$this->Actions[] = $Action;
			}
		}

		/**
		 * Check if the Timers next run time is past and fire the run function if it is.
		 */
		final public function maybe_run_timer() {
			$next = $this->get_next_run();
			$run = $next->getTimestamp() < time();

			if ( $run ) {
				$this->run();
			}
		}

		/**
		 * Run each action individually, set the last run time, and save the Timer.
		 */
		final private function run() {
			foreach ( $this->Actions as $Action ) {
				$Action->do();
			}

			$this->last_run = time();
			$this->save();
		}

		/**
		 * Add an Action to the Timer instance, optionally update the Action and { $this->action_data } if it already exists.
		 * 
		 * @param \Designink\WordPress\Framework\v1_0_1\Action_Scheduler\Action $Action The Action to try and add.
		 * @param bool $update Whether or not to replace the Action if it already exists by ID (default FALSE)
		 * 
		 * @return bool Whether or not the action was added.
		 */
		final public function add_action( Action $Action, bool $update = false ) {
			if ( true === $update || ! $this->has_action( $Action->id ) ) {
				$this->Actions[ $Action->id ] = $Action;
				$this->actions_data[ $Action->id ] = $Action->to_array();
				return true;
			}

			return false;
		}

		/**
		 * Return an Action instance from this Timer, if it exists, else return NULL.
		 * 
		 * @param string $action_id The ID of the Action to look for.
		 * 
		 * @return null|\Designink\WordPress\Framework\v1_0_1\Action_Scheduler\Action The Action instance or NULL.
		 */
		final public function get_action( string $action_id ) {
			if ( $this->Actions[ $action_id ] ) {
				return $this->Actions[ $action_id ];
			}

			return null;
		}

		/**
		 * Check whether an action exists in { $this->action_data }.
		 * 
		 * @param string $action_id The ID of the Action to search for.
		 * 
		 * @return bool Whether the Action exists or not.
		 */
		final public function has_action( $action_id ) {
			return array_key_exists( $action_id, $this->Actions );
		}

		/**
		 * A wrapper function for \Designink\WordPress\Framework\v1_0_1\Action_Scheduler\Timer_Manager::update_timer().
		 * 
		 * @param bool $merge Whether or not to merge existing Actions if the Timer already exists.
		 * 
		 * @return bool Whether or not the option meta was persisted.
		 */
		final public function save( bool $merge = false ) {
			$result = Timer_Manager::update_timer( $this, $merge );

			if ( false === $result ) {
				if ( Timer_Manager::get_timer( $this->id ) ) {
					return true;
				}
			} else {
				return true;
			}

			return false;
		}

		/**
		 * Merge Actions from another Timer instance into this instance.
		 * 
		 * @param \Designink\WordPress\Framework\v1_0_1\Action_Scheduler\Timer $Timer The Timer instance to merge Actions from.
		 */
		final public function merge_actions( Timer $Timer ) {
			foreach ( $Timer->get_actions() as $Action ) {
				$this->add_action( $Action, true );
			}
		}

		/**
		 * Convert this object into an array for export/import.
		 * 
		 * @return array The array representation of this Timer.
		 */
		final public function to_array() {
			$timer_export = array();

			foreach ( self::$default_arguments as $property => $value ) {
				if ( property_exists( $this, $property ) ) {
					$timer_export[ $property ] = $this->{ $property };
				} else {
					$timer_export[ $property ] = $value;
				}
			}

			// Call super class exports
			$timer_export = array_merge( $timer_export, $this->export_array() );

			return $timer_export;
		}

		/**
		 * Attempt to create a Timer super class instance using the 'timer_class' property associated with the Timer.
		 * 
		 * @param string $timer_id The ID of the Timer.
		 * 
		 * @return null|\Designink\WordPress\Framework\v1_0_1\Action_Scheduler\Timer The timer that was instantiated or FALSE.
		 */
		final public static function instantiate_timer( string $timer_id, array $timer_options ) {

			if ( ! isset( $timer_options['timer_class'] ) ) {
				$message = "Tried to instantiate a Timer super class without providing the 'timer_class' property.";
				trigger_error( __( $message ), E_USER_WARNING );
				return null;
			} else if ( ! class_exists( $timer_options['timer_class'] ) ) {
				$message_format = "Tried to instantiate a Timer super class using a class that could not be found. Tried to instantiate: %s";
				trigger_error( __( sprintf( $message_format, $timer_options['timer_class'] ) ), E_USER_WARNING );
				return null;
			} else if ( ! is_a( $timer_options['timer_class'], __CLASS__, true ) ) {
				$message_format = "Tried to instantiate a Timer super class using a class that does not inherit Timer. Provided class: %s";
				trigger_error( __( sprintf( $message_format, $timer_options['timer_class'] ) ), E_USER_WARNING );
				return null;
			}

			$Timer_Class = $timer_options['timer_class'];
			return new $Timer_Class( $timer_id, $timer_options );
		}

	}

}