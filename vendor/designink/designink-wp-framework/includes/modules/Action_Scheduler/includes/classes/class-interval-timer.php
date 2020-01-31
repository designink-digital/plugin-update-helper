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
use Designink_Framework_Shadow_Plugin;

if ( ! class_exists( '\Designink\WordPress\Framework\v1_0_1\Action_Scheduler\Interval_Timer', false ) ) {

	/**
	 * A Timer instance that bases it's run times off of even intervals from a specified start times. It can also return how many missed intervals a Timer may potentially have.
	 */
	final class Interval_Timer extends Timer {

		/**
		 * @var int $multiplier		The amount of seconds/hours/days/weeks to measure by.
		 * @var string $interval	The interval base specifier in seconds for the Timer interval.
		 * @var array $start		The initial start time of the Timer.
		 */
		public $multiplier, $interval, $start;

		/** @var array The default arguments provided to any Action class instance. */
		private static $default_arguments = array(
			'multiplier' => '24',
			'interval' => 'hour',
			'start' => array(
				'date' => '',
				'time' => '12:00'
			)
		);

		/** @var array An associative array linking the interval types to their respective duration in seconds. */
		private static $interval_types = array(
			'minute' =>	( 60 ),
			'hour' =>	( 60 * 60 ),
			'day' =>	( 60 * 60 * 24 ),
			'week' =>	( 60 * 60 * 24 * 7 ),
		);

		/**
		 * Get the interval types.
		 * 
		 * @return array The interval types.
		 */
		final public static function get_interval_types() { return self::$interval_types; }

		/**
		 * Construct the Interval Timer.
		 * 
		 * @param string $timer_id The ID to reference the Timer by.
		 * @param array $options The options to pass into the timer.
		 */
		public function __construct( string $timer_id, array $options ) {
			$date_regex = '/^[0-9]{4}-[0-9]{2}-[0-9]{2}$/';
			$time_regex = '/^[0-9]{2}:[0-9]{2}$/';

			$inteval_correct = in_array( $options['interval'], array_keys( self::$interval_types ) );
			$date_empty = empty( $options['date'] );
			$date_formatted = preg_match( $date_regex, $options['start']['date'] );
			$time_formatted = preg_match( $time_regex, $options['start']['time'] );

			if ( ! $date_empty && ! $date_formatted ) {
				$message_format = "The provided date has an incorrect format. yyyy-mm-dd expected, recieved: %s.";
				trigger_error( __( sprintf( $message_format, $options['start']['date'] ) ), E_USER_WARNING );
				return;
			} else if ( ! $time_formatted ) {
				$message_format = "The provided time has an incorrect format. mm:hh expected, recieved: %s.";
				trigger_error( __( sprintf( $message_format, $options['start']['time'] ) ), E_USER_WARNING );
				return;
			} else if ( ! $inteval_correct ) {
				$message_format = "The provided interval is incorrect. (%s) expected, recieved: %s.";
				trigger_error( __( sprintf( $message_format, implode( ',', array_keys( self::$interval_types ) ), $options['interval'] ) ), E_USER_WARNING );
				return;
			}

			$merged_options = Utility::guided_array_merge( $this::$default_arguments, $options );

			foreach ( $merged_options as $property => $value ) {
				if ( property_exists( $this, $property ) ) {
					$this->{ $property } = $value;
				}
			}

			parent::__construct( $timer_id, $options );
			$this->maybe_create_start_date();
		}

		/**
		 * The inherited abstract function for printing the output for the Form Builder.
		 * 
		 * @param string $group The name of the group the Timer will have in the form.
		 */
		final public static function print_form( string $group ) {
			Designink_Framework_Shadow_Plugin::instance()->get_template( 'interval-timer-form-builder', array( 'group' => $group ) );
		}

		/**
		 * Count how many times the Interval Timer should have run between now and the last time it ran.
		 * 
		 * @return int The number of Timer interval contexts that have passed since the last run.
		 */
		final public function count_runnable_contexts() {
			$start = $this->get_next_run();

			if ( null === $start ) {
				$start = $this->get_start_datetime();
			}

			$diff = time() - ( int ) $start->getTimestamp();
			$interval = ( int ) $this->multiplier * $this->get_interval_value();

			// We can safely use floor() because we will never have negative values
			$missed = ( int ) floor( $diff / $interval );

			if ( $missed >= 0 ) {
				return ( 1 + $missed );
			} else {
				return 0;
			}

		}

		/**
		 * Return the \DateTime instance that represents the next time the Interval Timer is supposed to be run.
		 * 
		 * @return \DateTime The time of next run.
		 */
		final public function get_next_run() {
			$start = $this->get_start_datetime();

			if ( ! $this->last_run ) {
				return $start;
			}

			$interval = ( int ) $this->multiplier * $this->get_interval_value();
			$diff = $this->last_run - $start->getTimestamp();
			$steps = ( int ) ceil( $diff / $interval );
			$total = $interval * $steps;

			$start->add( new \DateInterval( sprintf( 'PT%sS', $total ) ) );
			return $start;
		}

		/**
		 * Get the value in seconds of the interval type of this Interval Timer.
		 * 
		 * @return int The value in seconds of the given interval type.
		 */
		final public function get_interval_value() {
			return self::$interval_types[ $this->interval ];
		}

		/**
		 * Create the start date to be at the next instance of the time specified if it is not already set.
		 */
		final private function maybe_create_start_date() {
			$now = new \DateTimeImmutable( 'now', new \DateTimeZone( 'GMT' ) );
			$start = null;

			if ( ! isset( $this->start['date'] ) || empty( $this->start['date'] ) ) {
				$start = date_create_from_format( 'Y-m-d H:i', sprintf( '%s %s', $now->format( 'Y-m-d' ), $this->start['time'] ), new \DateTimeZone( 'GMT' ) );

				if ( $start->getTimestamp() < $now->getTimestamp() ) {
					$start->add( new \DateInterval( 'P1D' ) );
				}

				$this->start['date'] = $start->format( 'Y-m-d' );
			}
		}

		/**
		 * Return a DateTime instance of the initial start time for this Interval Timer.
		 * 
		 * @return \DateTime The DateTime instance of the start time.
		 */
		final public function get_start_datetime() {
			return date_create_from_format( 'Y-m-d H:i', sprintf( '%s %s', $this->start['date'], $this->start['time'] ), new \DateTimeZone( 'GMT' ) );
		}

		/**
		 * The inherited abstract for exporting the properties created by this class.
		 * 
		 * @return array The exportable properties of this Timer class.
		 */
		final protected function export_array() {
			$timer_export = array();

			foreach ( self::$default_arguments as $property => $value ) {

				if ( property_exists( $this, $property ) ) {
					$timer_export[ $property ] = $this->{ $property };
				} else {
					$timer_export[ $property ] = $value;
				}

			}

			return $timer_export;
		}

	}

}
