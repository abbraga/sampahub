<?php
/**
 * Date utility functions used throughout TEC + Addons
 */

// Don't load directly
if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}

if ( ! class_exists( 'Tribe__Date_Utils' ) ) {
	class Tribe__Date_Utils {
		// Default formats, they are overridden by WP options or by arguments to date methods
		const DATEONLYFORMAT        = 'F j, Y';
		const TIMEFORMAT            = 'g:i A';
		const HOURFORMAT            = 'g';
		const MINUTEFORMAT          = 'i';
		const MERIDIANFORMAT        = 'A';
		const DBDATEFORMAT          = 'Y-m-d';
		const DBDATETIMEFORMAT      = 'Y-m-d H:i:s';
		const DBTIMEFORMAT          = 'H:i:s';
		const DBYEARMONTHTIMEFORMAT = 'Y-m';

		/**
		 * Get the datepicker format, that is used to translate the option from the DB to a string
		 *
		 * @param  int $translate The db Option from datepickerFormat
		 * @return string|array            If $translate is not set returns the full array, if not returns the `Y-m-d`
		 */
		public static function datepicker_formats( $translate = null ) {
			$formats = array(
				'Y-m-d',
				'n/j/Y',
				'm/d/Y',
				'j/n/Y',
				'd/m/Y',
				'n-j-Y',
				'm-d-Y',
				'j-n-Y',
				'd-m-Y',
			);

			if ( is_null( $translate ) ) {
				return $formats;
			}

			return isset( $formats[ $translate ] ) ? $formats[ $translate ] : $formats[0];
		}

		/**
		 * As PHP 5.2 doesn't have a good version of `date_parse_from_format`, this is how we deal with
		 * possible weird datepicker formats not working
		 *
		 * @param  string $format The weird format you are using
		 * @param  string $date   The date string to parse
		 *
		 * @return string         A DB formated Date, includes time if possible
		 */
		public static function datetime_from_format( $format, $date ) {
			// Reverse engineer the relevant date formats
			$keys = array(
				// Year with 4 Digits
				'Y' => array( 'year', '\d{4}' ),

				// Year with 2 Digits
				'y' => array( 'year', '\d{2}' ),

				// Month with leading 0
				'm' => array( 'month', '\d{2}' ),

				// Month without the leading 0
				'n' => array( 'month', '\d{1,2}' ),

				// Month ABBR 3 letters
				'M' => array( 'month', '[A-Z][a-z]{2}' ),

				// Month Name
				'F' => array( 'month', '[A-Z][a-z]{2,8}' ),

				// Day with leading 0
				'd' => array( 'day', '\d{2}' ),

				// Day without leading 0
				'j' => array( 'day', '\d{1,2}' ),

				// Day ABBR 3 Letters
				'D' => array( 'day', '[A-Z][a-z]{2}' ),

				// Day Name
				'l' => array( 'day', '[A-Z][a-z]{5,8}' ),

				// Hour 12h formatted, with leading 0
				'h' => array( 'hour', '\d{2}' ),

				// Hour 24h formatted, with leading 0
				'H' => array( 'hour', '\d{2}' ),

				// Hour 12h formatted, without leading 0
				'g' => array( 'hour', '\d{1,2}' ),

				// Hour 24h formatted, without leading 0
				'G' => array( 'hour', '\d{1,2}' ),

				// Minutes with leading 0
				'i' => array( 'minute', '\d{2}' ),

				// Seconds with leading 0
				's' => array( 'second', '\d{2}' ),
			);

			$date_regex = "/{$keys['Y'][1]}-{$keys['m'][1]}-{$keys['d'][1]}( {$keys['H'][1]}:{$keys['i'][1]}:{$keys['s'][1]})?$/";

			// if the date is already in Y-m-d or Y-m-d H:i:s, just return it
			if ( preg_match( $date_regex, $date ) ) {
				return $date;
			}


			// Convert format string to regex
			$regex = '';
			$chars = str_split( $format );
			foreach ( $chars as $n => $char ) {
				$last_char = isset( $chars[ $n - 1 ] ) ? $chars[ $n - 1 ] : '';
				$skip_current = '\\' == $last_char;
				if ( ! $skip_current && isset( $keys[ $char ] ) ) {
					$regex .= '(?P<' . $keys[ $char ][0] . '>' . $keys[ $char ][1] . ')';
				} elseif ( '\\' == $char ) {
					$regex .= $char;
				} else {
					$regex .= preg_quote( $char );
				}
			}

			$dt = array();

			// Now try to match it
			if ( preg_match( '#^' . $regex . '$#', $date, $dt ) ) {
				// Remove unwanted Indexes
				foreach ( $dt as $k => $v ) {
					if ( is_int( $k ) ) {
						unset( $dt[ $k ] );
					}
				}

				// We need at least Month + Day + Year to work with
				if ( ! checkdate( $dt['month'], $dt['day'], $dt['year'] ) ) {
					return false;
				}
			} else {
				return false;
			}

			$dt['month'] = str_pad( $dt['month'], 2, '0', STR_PAD_LEFT );
			$dt['day'] = str_pad( $dt['day'], 2, '0', STR_PAD_LEFT );

			$formatted = '{year}-{month}-{day}' . ( isset( $dt['hour'], $dt['minute'], $dt['second'] ) ? ' {hour}:{minute}:{second}' : '' );
			foreach ( $dt as $key => $value ) {
				$formatted = str_replace( '{' . $key . '}', $value, $formatted );
			}

			return $formatted;
		}

		/**
		 * Returns the date only.
		 *
		 * @param int|string $date        The date (timestamp or string).
		 * @param bool       $isTimestamp Is $date in timestamp format?
		 * @param string|null $format The format used
		 *
		 * @return string The date only in DB format.
		 */
		public static function date_only( $date, $isTimestamp = false, $format = null ) {
			$date = $isTimestamp ? $date : strtotime( $date );

			if ( is_null( $format ) ) {
				$format = self::DBDATEFORMAT;
			}

			return date( $format, $date );
		}

		/**
		 * Returns the time only.
		 *
		 * @param string $date The date.
		 *
		 * @return string The time only in DB format.
		 */
		public static function time_only( $date ) {
			$date = is_numeric( $date ) ? $date : strtotime( $date );
			return date( self::DBTIMEFORMAT, $date );
		}

		/**
		 * Returns the hour only.
		 *
		 * @param string $date The date.
		 *
		 * @return string The hour only.
		 */
		public static function hour_only( $date ) {
			$date = is_numeric( $date ) ? $date : strtotime( $date );
			return date( self::HOURFORMAT, $date );
		}

		/**
		 * Returns the minute only.
		 *
		 * @param string $date The date.
		 *
		 * @return string The minute only.
		 */
		public static function minutes_only( $date ) {
			$date = is_numeric( $date ) ? $date : strtotime( $date );
			return date( self::MINUTEFORMAT, $date );
		}

		/**
		 * Returns the meridian (am or pm) only.
		 *
		 * @param string $date The date.
		 *
		 * @return string The meridian only in DB format.
		 */
		public static function meridian_only( $date ) {
			$date = is_numeric( $date ) ? $date : strtotime( $date );
			return date( self::MERIDIANFORMAT, $date );
		}

		/**
		 * Returns the number of seconds (absolute value) between two dates/times.
		 *
		 * @param string $date1 The first date.
		 * @param string $date2 The second date.
		 *
		 * @return int The number of seconds between the dates.
		 */
		public static function time_between( $date1, $date2 ) {
			return abs( strtotime( $date1 ) - strtotime( $date2 ) );
		}

		/**
		 * The number of days between two arbitrary dates.
		 *
		 * @param string $date1 The first date.
		 * @param string $date2 The second date.
		 *
		 * @return int The number of days between two dates.
		 */
		public static function date_diff( $date1, $date2 ) {
			// Get number of days between by finding seconds between and dividing by # of seconds in a day
			$days = self::time_between( $date1, $date2 ) / ( 60 * 60 * 24 );

			return $days;
		}

		/**
		 * Returns the last day of the month given a php date.
		 *
		 * @param int $timestamp THe timestamp.
		 *
		 * @return string The last day of the month.
		 */
		public static function get_last_day_of_month( $timestamp ) {
			$curmonth  = date( 'n', $timestamp );
			$curYear   = date( 'Y', $timestamp );
			$nextmonth = mktime( 0, 0, 0, $curmonth + 1, 1, $curYear );
			$lastDay   = strtotime( date( self::DBDATETIMEFORMAT, $nextmonth ) . ' - 1 day' );

			return date( 'j', $lastDay );
		}

		/**
		 * Returns true if the timestamp is a weekday.
		 *
		 * @param int $curDate A timestamp.
		 *
		 * @return bool If the timestamp is a weekday.
		 */
		public static function is_weekday( $curdate ) {
			return in_array( date( 'N', $curdate ), array( 1, 2, 3, 4, 5 ) );
		}

		/**
		 * Returns true if the timestamp is a weekend.
		 *
		 * @param int $curDate A timestamp.
		 *
		 * @return bool If the timestamp is a weekend.
		 */
		public static function is_weekend( $curdate ) {
			return in_array( date( 'N', $curdate ), array( 6, 7 ) );
		}

		/**
		 * Gets the last day of the week in a month (ie the last Tuesday).  Passing in -1 gives you the last day in the month.
		 *
		 * @param int $curdate     A timestamp.
		 * @param int $day_of_week The index of the day of the week.
		 *
		 * @return int The timestamp of the date that fits the qualifications.
		 */
		public static function get_last_day_of_week_in_month( $curdate, $day_of_week ) {
			$nextdate = mktime( date( 'H', $curdate ), date( 'i', $curdate ), date( 's', $curdate ), date( 'n', $curdate ), self::get_last_day_of_month( $curdate ), date( 'Y', $curdate ) );;

			while ( date( 'N', $nextdate ) != $day_of_week && $day_of_week != - 1 ) {
				$nextdate = strtotime( date( self::DBDATETIMEFORMAT, $nextdate ) . ' - 1 day' );
			}

			return $nextdate;
		}

		/**
		 * Gets the first day of the week in a month (ie the first Tuesday).
		 *
		 * @param int $curdate     A timestamp.
		 * @param int $day_of_week The index of the day of the week.
		 *
		 * @return int The timestamp of the date that fits the qualifications.
		 */
		public static function get_first_day_of_week_in_month( $curdate, $day_of_week ) {
			$nextdate = mktime( 0, 0, 0, date( 'n', $curdate ), 1, date( 'Y', $curdate ) );

			while ( ! ( $day_of_week > 0 && date( 'N', $nextdate ) == $day_of_week ) &&
					! ( $day_of_week == - 1 && self::is_weekday( $nextdate ) ) &&
					! ( $day_of_week == - 2 && self::is_weekend( $nextdate ) ) ) {
				$nextdate = strtotime( date( self::DBDATETIMEFORMAT, $nextdate ) . ' + 1 day' );
			}

			return $nextdate;
		}

		/**
		 * From http://php.net/manual/en/function.date.php
		 *
		 * @param int $number A number.
		 *
		 * @return string The ordinal for that number.
		 */
		public static function number_to_ordinal( $number ) {
			$output = $number . ( ( ( strlen( $number ) > 1 ) && ( substr( $number, - 2, 1 ) == '1' ) ) ?
					'th' : date( 'S', mktime( 0, 0, 0, 0, substr( $number, - 1 ), 0 ) ) );

			return apply_filters( 'tribe_events_number_to_ordinal', $output, $number );
		}

		/**
		 * check if a given string is a timestamp
		 *
		 * @param $timestamp
		 *
		 * @return bool
		 */
		public static function is_timestamp( $timestamp ) {
			if ( is_numeric( $timestamp ) && (int) $timestamp == $timestamp && date( 'U', $timestamp ) == $timestamp ) {
				return true;
			}

			return false;
		}

		/**
		 * Accepts a string representing a date/time and attempts to convert it to
		 * the specified format, returning an empty string if this is not possible.
		 *
		 * @param $dt_string
		 * @param $new_format
		 *
		 * @return string
		 */
		public static function reformat( $dt_string, $new_format ) {
			$timestamp = strtotime( $dt_string );
			$revised   = date( $new_format, $timestamp );

			return $revised ? $revised : '';
		}

		/**
		 * Accepts a numeric offset (such as "4" or "-6" as stored in the gmt_offset
		 * option) and converts it to a strtotime() style modifier that can be used
		 * to adjust a DateTime object, etc.
		 *
		 * @param $offset
		 *
		 * @return string
		 */
		public static function get_modifier_from_offset( $offset ) {
			$modifier = '';
			$offset   = (float) $offset;

			// Separate out hours, minutes, polarity
			$hours    = (int) $offset;
			$minutes  = (int) ( ( $offset - $hours ) * 60 );
			$polarity = ( $offset >= 0 ) ? '+' : '-';

			// Correct hours and minutes to positive values
			if ( $hours < 0 )   $hours *= -1;
			if ( $minutes < 0 ) $minutes *= -1;

			// Form the modifier string
			if ( $hours >= 0 )  $modifier  = "$polarity $hours hours ";
			if ( $minutes > 0 ) $modifier .= "$minutes minutes";

			return $modifier;
		}

		/**
		 * Returns the weekday of the 1st day of the month in
		 * "w" format (ie, Sunday is 0 and Saturday is 6) or
		 * false if this cannot be established.
		 *
		 * @param  mixed $month
		 * @return int|bool
		 */
		public static function first_day_in_month( $month ) {
			try {
				$date  = new DateTime( $month );
				$day_1 = new DateTime( $date->format( 'Y-m-01 ' ) );
				return $day_1->format( 'w' );
			}
			catch ( Exception $e ) {
				return false;
			}
		}

		/**
		 * Returns the weekday of the last day of the month in
		 * "w" format (ie, Sunday is 0 and Saturday is 6) or
		 * false if this cannot be established.
		 *
		 * @param  mixed $month
		 * @return int|bool
		 */
		public static function last_day_in_month( $month ) {
			try {
				$date  = new DateTime( $month );
				$day_1 = new DateTime( $date->format( 'Y-m-t' ) );
				return $day_1->format( 'w' );
			}
			catch ( Exception $e ) {
				return false;
			}
		}

		/**
		 * Returns the day of the week the week ends on, expressed as a "w" value
		 * (ie, Sunday is 0 and Saturday is 6).
		 *
		 * @param  int $week_starts_on
		 *
		 * @return int
		 */
		public static function week_ends_on( $week_starts_on ) {
			if ( --$week_starts_on < 0 ) $week_starts_on = 6;
			return $week_starts_on;
		}

		/**
		 * Helper method to convert EventAllDay values to a boolean
		 *
		 * @param mixed $all_day_value Value to check for "all day" status. All day values: (true, 'true', 'TRUE', 'yes')
		 *
		 * @return boolean Is value considered "All Day"?
		 */
		public static function is_all_day( $all_day_value ) {
			$all_day_value = trim( $all_day_value );

			return (
				'true' === strtolower( $all_day_value )
				|| 'yes' === strtolower( $all_day_value )
				|| true === $all_day_value
				|| 1 == $all_day_value
			);
		}

		/**
		 * Given 2 datetime ranges, return whether the 2nd one occurs during the 1st one
		 * Note: all params should be unix timestamps
		 *
		 * @param integer $range_1_start timestamp for start of the first range
		 * @param integer $range_1_end timestamp for end of the first range
		 * @param integer $range_2_start timestamp for start of the second range
		 * @param integer $range_2_end timestamp for end of the second range
		 *
		 * @return bool
		 */
		public static function range_coincides( $range_1_start, $range_1_end, $range_2_start, $range_2_end ) {

			// Initialize the return value
			$range_coincides = false;

			/**
			 * conditions:
			 * range 2 starts during range 1 (range 2 start time is between start and end of range 1 )
			 * range 2 ends during range 1 (range 2 end time is between start and end of range 1 )
			 * range 2 encloses range 1 (range 2 starts before range 1 and ends after range 1)
			 */

			$range_2_starts_during_range_1 = $range_2_start >= $range_1_start && $range_2_start < $range_1_end;
			$range_2_ends_during_range_1   = $range_2_end > $range_1_start && $range_2_end <= $range_1_end;
			$range_2_encloses_range_1      = $range_2_start < $range_1_start && $range_2_end > $range_1_end;

			if ( $range_2_starts_during_range_1 || $range_2_ends_during_range_1 || $range_2_encloses_range_1 ) {
				$range_coincides = true;
			}

			return $range_coincides;

		}

		/**
		 * Converts a locally-formatted date to a unix timestamp. This is a drop-in
		 * replacement for `strtotime()`, except that where strtotime assumes GMT, this
		 * assumes local time (as described below). If a timezone is specified, this
		 * function defers to strtotime().
		 *
		 * If there is a timezone_string available, the date is assumed to be in that
		 * timezone, otherwise it simply subtracts the value of the 'gmt_offset'
		 * option.
		 *
		 * @see  strtotime()
		 * @uses get_option() to retrieve the value of 'gmt_offset'
		 *
		 * @param string $string A date/time string. See `strtotime` for valid formats
		 *
		 * @return int UNIX timestamp.
		 */
		public static function wp_strtotime( $string ) {
			// If there's a timezone specified, we shouldn't convert it
			try {
				$test_date = new DateTime( $string );
				if ( 'UTC' != $test_date->getTimezone()->getName() ) {
					return strtotime( $string );
				}
			} catch ( Exception $e ) {
				return strtotime( $string );
			}

			$tz = get_option( 'timezone_string' );
			if ( ! empty( $tz ) ) {
				$date = date_create( $string, new DateTimeZone( $tz ) );
				if ( ! $date ) {
					return strtotime( $string );
				}
				$date->setTimezone( new DateTimeZone( 'UTC' ) );
				return $date->format( 'U' );
			} else {
				$offset = (float) get_option( 'gmt_offset' );
				$seconds = intval( $offset * HOUR_IN_SECONDS );
				$timestamp = strtotime( $string ) - $seconds;
				return $timestamp;
			}
		}

		// DEPRECATED METHODS
		// @codingStandardsIgnoreStart
		/**
		 * Deprecated camelCase version of self::date_only
		 *
		 * @param int|string $date        The date (timestamp or string).
		 * @param bool       $isTimestamp Is $date in timestamp format?
		 *
		 * @return string The date only in DB format.
		 */
		public static function dateOnly( $date, $isTimestamp = false ) {
			_deprecated_function( __METHOD__, '3.11', __CLASS__ . '::date_only' );
			return self::date_only( $date, $isTimestamp );
		}

		/**
		 * Deprecated camelCase version of self::time_only
		 *
		 * @param string $date The date.
		 *
		 * @return string The time only in DB format.
		 */
		public static function timeOnly( $date ) {
			_deprecated_function( __METHOD__, '3.11', __CLASS__ . '::time_only' );
			return self::time_only( $date );
		}

		/**
		 * Deprecated camelCase version of self::hour_only
		 *
		 * @param string $date The date.
		 *
		 * @return string The hour only.
		 */
		public static function hourOnly( $date ) {
			_deprecated_function( __METHOD__, '3.11', __CLASS__ . '::hour_only' );
			return self::hour_only( $date );
		}

		/**
		 * Deprecated camelCase version of self::minutes_only
		 *
		 * @param string $date The date.
		 *
		 * @return string The minute only.
		 */
		public static function minutesOnly( $date ) {
			_deprecated_function( __METHOD__, '3.11', __CLASS__ . '::minutes_only' );
			return self::minutes_only( $date );
		}

		/**
		 * Deprecated camelCase version of self::meridian_only
		 *
		 * @param string $date The date.
		 *
		 * @return string The meridian only in DB format.
		 */
		public static function meridianOnly( $date ) {
			_deprecated_function( __METHOD__, '3.11', __CLASS__ . '::meridian_only' );
			return self::meridian_only( $date );
		}

		/**
		 * Returns the end of a given day.
		 *
		 * @deprecated since 3.10 - use tribe_event_end_of_day()
		 * @todo       remove in 4.1
		 *
		 * @param int|string $date        The date (timestamp or string).
		 * @param bool       $isTimestamp Is $date in timestamp format?
		 *
		 * @return string The date and time of the end of a given day
		 */
		public static function endOfDay( $date, $isTimestamp = false ) {
			_deprecated_function( __METHOD__, '3.10', 'tribe_event_end_of_day' );

			if ( $isTimestamp ) {
				$date = date( self::DBDATEFORMAT, $date );
			}

			return tribe_event_end_of_day( $date, self::DBDATETIMEFORMAT );
		}

		/**
		 * Returns the beginning of a given day.
		 *
		 * @deprecated since 3.10
		 * @todo       remove in 4.1
		 *
		 * @param int|string $date        The date (timestamp or string).
		 * @param bool       $isTimestamp Is $date in timestamp format?
		 *
		 * @return string The date and time of the beginning of a given day.
		 */
		public static function beginningOfDay( $date, $isTimestamp = false ) {
			_deprecated_function( __METHOD__, '3.10', 'tribe_event_beginning_of_day' );

			if ( $isTimestamp ) {
				$date = date( self::DBDATEFORMAT, $date );
			}

			return tribe_event_beginning_of_day( $date, self::DBDATETIMEFORMAT );
		}

		/**
		 * Deprecated camelCase version of self::time_between
		 *
		 * @param string $date1 The first date.
		 * @param string $date2 The second date.
		 *
		 * @return int The number of seconds between the dates.
		 */
		public static function timeBetween( $date1, $date2 ) {
			_deprecated_function( __METHOD__, '3.11', __CLASS__ . '::time_between' );
			return self::time_between( $date1, $date2 );
		}

		/**
		 * Deprecated camelCase version of self::date_diff
		 *
		 * @param string $date1 The first date.
		 * @param string $date2 The second date.
		 *
		 * @return int The number of days between two dates.
		 */
		public static function dateDiff( $date1, $date2 ) {
			_deprecated_function( __METHOD__, '3.11', __CLASS__ . '::date_diff' );
			return self::date_diff( $date1, $date2 );
		}

		/**
		 * Deprecated camelCase version of self::get_last_day_of_month
		 *
		 * @param int $timestamp THe timestamp.
		 *
		 * @return string The last day of the month.
		 */
		public static function getLastDayOfMonth( $timestamp ) {
			_deprecated_function( __METHOD__, '3.11', __CLASS__ . '::get_last_day_of_month' );
			return self::get_last_day_of_month( $timestamp );
		}

		/**
		 * Deprecated camelCase version of self::is_weekday
		 *
		 * @param int $curDate A timestamp.
		 *
		 * @return bool If the timestamp is a weekday.
		 */
		public static function isWeekday( $curdate ) {
			_deprecated_function( __METHOD__, '3.11', __CLASS__ . '::is_weekday' );
			return self::is_weekday( $curdate );
		}

		/**
		 * Deprecated camelCase version of self::is_weekend
		 *
		 * @param int $curDate A timestamp.
		 *
		 * @return bool If the timestamp is a weekend.
		 */
		public static function isWeekend( $curdate ) {
			_deprecated_function( __METHOD__, '3.11', __CLASS__ . '::is_weekend' );
			return self::is_weekend( $curdate );
		}

		/**
		 * Deprecated camelCase version of self::get_last_day_of_week_in_month
		 *
		 * @param int $curdate     A timestamp.
		 * @param int $day_of_week The index of the day of the week.
		 *
		 * @return int The timestamp of the date that fits the qualifications.
		 */
		public static function getLastDayOfWeekInMonth( $curdate, $day_of_week ) {
			_deprecated_function( __METHOD__, '3.11', __CLASS__ . '::get_last_day_of_week_in_month' );
			return self::get_last_day_of_week_in_month( $curdate, $day_of_week );
		}

		/**
		 * Deprecated camelCase version of self::get_first_day_of_week_in_month
		 *
		 * @param int $curdate     A timestamp.
		 * @param int $day_of_week The index of the day of the week.
		 *
		 * @return int The timestamp of the date that fits the qualifications.
		 */
		public static function getFirstDayOfWeekInMonth( $curdate, $day_of_week ) {
			_deprecated_function( __METHOD__, '3.11', __CLASS__ . '::get_fist_day_of_week_in_month' );
			return self::get_first_day_of_week_in_month( $curdate, $day_of_week );
		}

		/**
		 * Deprecated camelCase version of self::number_to_ordinal
		 *
		 * @param int $number A number.
		 *
		 * @return string The ordinal for that number.
		 */
		public static function numberToOrdinal( $number ) {
			_deprecated_function( __METHOD__, '3.11', __CLASS__ . '::number_to_ordinal' );
			return self::number_to_ordinal( $number );
		}

		/**
		 * Deprecated camelCase version of self::is_timestamp
		 *
		 * @param $timestamp
		 *
		 * @return bool
		 */
		public static function isTimestamp( $timestamp ) {
			_deprecated_function( __METHOD__, '3.11', __CLASS__ . '::is_timestamp' );
			return self::is_timestamp( $timestamp );
		}

		/**
		 * Gets the timestamp of a day in week, month and year context.
		 *
		 * Kudos to [icedwater StackOverflow user](http://stackoverflow.com/users/1091386/icedwater) in
		 * [his answer](http://stackoverflow.com/questions/924246/get-the-first-or-last-friday-in-a-month).
		 *
		 * Usage examples:
		 * "The second Wednesday of March 2015" - `get_day_timestamp( 3, 2, 3, 2015, 1)`
		 * "The last Friday of December 2015" - `get_day_timestamp( 5, 1, 12, 2015, -1)`
		 * "The first Monday of April 2016 - `get_day_timestamp( 1, 1, 4, 2016, 1)`
		 * "The penultimate Thursday of January 2012" - `get_day_timestamp( 4, 2, 1, 2012, -1)`
		 *
		 * @param int $day_of_week    The day representing the number in the week, Monday is `1`, Tuesday is `2`, Sunday is `7`
		 * @param int $week_in_month  The week number in the month; first week is `1`, second week is `2`; when direction is reverse
		 *                  then `1` is last week of the month, `2` is penultimate week of the month and so on.
		 * @param int $month          The month number in the year, January is `1`
		 * @param int $year           The year number, e.g. "2015"
		 * @param int $week_direction Either `1` or `-1`; the direction for the search referring to the week, defaults to `1`
		 *                       to specify weeks in natural order so:
		 *                       $week_direction `1` and $week_in_month `1` means "first week of the month"
		 *                       $week_direction `1` and $week_in_month `3` means "third week of the month"
		 *                       $week_direction `-1` and $week_in_month `1` means "last week of the month"
		 *                       $week_direction `-1` and $week_in_month `2` means "penultimmate week of the month"
		 *
		 * @return int The day timestamp
		 */
		public static function get_weekday_timestamp( $day_of_week, $week_in_month, $month, $year, $week_direction = 1 ) {
			if (
				! (
					is_numeric( $day_of_week )
					&& is_numeric( $week_in_month )
					&& is_numeric( $month )
					&& is_numeric( $year )
					&& is_numeric( $week_direction )
					&& in_array( $week_direction, array( - 1, 1 ) )
				)
			) {
				return false;
			}

			if ( $week_direction > 0 ) {
				$startday = 1;
			} else {
				$startday = date( 't', mktime( 0, 0, 0, $month, 1, $year ) );
			}

			$start   = mktime( 0, 0, 0, $month, $startday, $year );
			$weekday = date( 'N', $start );

			if ( $week_direction * $day_of_week >= $week_direction * $weekday ) {
				$offset = - $week_direction * 7;
			} else {
				$offset = 0;
			}

			$offset += $week_direction * ( $week_in_month * 7 ) + ( $day_of_week - $weekday );

			return mktime( 0, 0, 0, $month, $startday + $offset, $year );
		}

		/**
		 * Unescapes date format strings to be used in functions like `date`.
		 *
		 * Double escaping happens when storing a date format in the database.
		 *
		 * @param mixed $date_format A date format string.
		 *
		 * @return mixed Either the original input or an unescaped date format string.
		 */
		public static function unescape_date_format( $date_format ) {
			if ( ! is_string( $date_format ) ) {
				return $date_format;
			}

			// Why so simple? Let's handle other cases as those come up. We have tests in place!
			return str_replace( '\\\\', '\\', $date_format );
		}
		// @codingStandardsIgnoreEnd
	}

}
