<?php /* $Id$ $URL$ */

/**
 * @package web2project
 * @subpackage utilites
 */

require_once $AppUI->getLibraryClass('PEAR/Date');

define('FMT_DATEISO', '%Y%m%dT%H%M%S');
define('FMT_DATELDAP', '%Y%m%d%H%M%SZ');
define('FMT_DATETIME_MYSQL', '%Y-%m-%d %H:%M:%S');
define('FMT_DATERFC822', '%a, %d %b %Y %H:%M:%S');
define('FMT_TIMESTAMP', '%Y%m%d%H%M%S');
define('FMT_TIMESTAMP_DATE', '%Y%m%d');
define('FMT_TIMESTAMP_TIME', '%H%M%S');
define('FMT_UNIX', '3');
define('WDAY_SUNDAY', 0);
define('WDAY_MONDAY', 1);
define('WDAY_TUESDAY', 2);
define('WDAY_WEDNESDAY', 3);
define('WDAY_THURSDAY', 4);
define('WDAY_FRIDAY', 5);
define('WDAY_SATURDAY', 6);
define('SEC_MINUTE', 60);
define('SEC_HOUR', 3600);
define('SEC_DAY', 86400);

/**
 * web2Project implementation of the Pear Date class
 *
 * This provides customised extensions to the Date class to leave the
 * Date package as 'pure' as possible
 */
class w2p_Utilities_Date extends Date {

	public function __construct($datetime = null, $tz = '') {

		parent::__construct($datetime);
		if ($tz == '')
		{
			$this->setTZ(date_default_timezone_get());
		} else
		{
			$this->setTZ($tz);
		}
	}
	/**
	 * Overloaded compare method
	 *
	 * The convertTZ calls are time intensive calls.	 When a compare call is
	 * made in a recussive loop the lag can be significant.
	 */
	public function compare($d1, $d2, $convertTZ = false) {
		if ($convertTZ) {
			$d1->convertTZ(new Date_TimeZone('UTC'));
			$d2->convertTZ(new Date_TimeZone('UTC'));
		}

		$days1 = Date_Calc::dateToDays($d1->day, $d1->month, $d1->year);
		$days2 = Date_Calc::dateToDays($d2->day, $d2->month, $d2->year);

        $comp_value = 0;
		if ($days1 - $days2) {
			$comp_value = $days1 - $days2;
		} else {
			if ($d1->hour - $d2->hour) {
				$comp_value = w2Psgn($d1->hour - $d2->hour);
			} else {
				if ($d1->minute - $d2->minute) {
					$comp_value = w2Psgn($d1->minute - $d2->minute);
				} else {
					if ($d1->second - $d2->second) {
						$comp_value = w2Psgn($d1->second - $d2->second);
					}
                }
            }
        }
		return w2Psgn($comp_value);
	}

	/**
	 * Adds (+/-) a number of days to the current date.
	 * @param int Positive or negative number of days
	 * @author J. Christopher Pereira <kripper@users.sf.net>
	 */
	public function addDays($n) {
		$this->addSeconds($n*24*60*60);
	}

	/**
	 * Adds (+/-) a number of months to the current date.
	 * @param int Positive or negative number of months
	 * @author Andrew Eddie <eddieajau@users.sourceforge.net>
	 */
	public function addMonths($n) {
		$an = abs($n);
		$years = floor($an / 12);
		$months = $an % 12;

		if ($n < 0) {
			$this->year -= $years;
			$this->month -= $months;
			if ($this->month < 1) {
				$this->year--;
				$this->month = 12 + $this->month;
			}
		} else {
			$this->year += $years;
			$this->month += $months;
			if ($this->month > 12) {
				$this->year++;
				$this->month -= 12;
			}
		}
	}

	/**
	 * New method to get the difference in days the stored date
	 * @param Date The date to compare to
	 * @author Andrew Eddie <eddieajau@users.sourceforge.net>
	 */
	public function dateDiff($when) {
		if (!is_object($when)) {
			return false;
		}
		return Date_calc::dateDiff($this->getDay(), $this->getMonth(), $this->getYear(), $when->getDay(), $when->getMonth(), $when->getYear());
	}

	/**
	 * New method that sets hour, minute and second in a single call
	 * @param int hour
	 * @param int minute
	 * @param int second
	 * @author Andrew Eddie <eddieajau@users.sourceforge.net>
	 */
	public function setTime($h = 0, $m = 0, $s = 0) {
		$this->setHour($h);
		$this->setMinute($m);
		$this->setSecond($s);
	}

	public function isWorkingDay() {
		global $AppUI;

		if ($AppUI->isActiveModule('holiday')) {
		    // Holiday module, check the holiday database
		    require_once W2P_BASE_DIR."/modules/holiday/holiday_functions.class.php";
		    if(HolidayFunctions::isHoliday($this)) {
		        return false;
		    }
		}

		$working_days = w2PgetConfig('cal_working_days');
		$working_days = ((is_null($working_days)) ? array('1', '2', '3', '4', '5') : explode(',', $working_days));
		return in_array($this->getDayOfWeek(), $working_days);
	}

	public function getAMPM() {
		return (($this->getHour() > 11) ? 'pm' : 'am');
	}

	/* Check if two dates belong to the same day */
	public function isSameDay($otherDay) {
		return ($this->getDay() == $otherDay->getDay() &&
			$this->getMonth() == $otherDay->getMonth() &&
			$this->getYear() == $otherDay->getYear());
	}

	/* Return date diff in minutes */
	public function diff($otherDate) {
		return abs($otherDate->getTime() - $this->getTime())/60.0;
	}

	/* Return date obj for the end of the next working day
	** @param	bool	Determine whether to set time to start of day or preserve the time of the given object
	*/
	public function next_working_day($preserveHours = false) {
		global $AppUI;
		$do = clone $this;
		$end = intval(w2PgetConfig('cal_day_end'));
		$start = intval(w2PgetConfig('cal_day_start'));
		while (!$this->isWorkingDay() || $this->getHour() > $end || ($preserveHours == false && $this->getHour() == $end && $this->getMinute() == '0')) {
			$this->addDays(1);
			$this->setTime($start, '0', '0');
		}

		if ($preserveHours) {
			$this->setTime($do->getHour(), '0', '0');
		}

		return $this;
	}

	/* Return date obj for the end of the previous working day
	** @param	bool	Determine whether to set time to end of day or preserve the time of the given object
	*/
	public function prev_working_day($preserveHours = false) {
		global $AppUI;
		$do = clone $this;
		$end = intval(w2PgetConfig('cal_day_end'));
		$start = intval(w2PgetConfig('cal_day_start'));
		while (!$this->isWorkingDay() || ($this->getHour() < $start) || ($this->getHour() == $start && $this->getMinute() == '0')) {
			$this->addDays(-1);
			$this->setTime($end, '0', '0');
		}
		if ($preserveHours) {
			$this->setTime($do->getHour(), '0', '0');
		}

		return $this;
	}

	/* Calculating _robustly_ a date from a given date and duration
	** Works in both directions: forwards/prospective and backwards/retrospective
	** Respects non-working days
	** @param	int	duration	(positive = forward, negative = backward)
	** @param	int	durationType; 1 = hour; 24 = day;
	** @return	obj	Shifted DateObj
	*/

	public function addDuration($duration = '8', $durationType = '1') {
		// using a sgn function lets us easily cover
		// prospective and retrospective calcs at the same time

		// get signum of the duration
		$sgn = w2Psgn($duration);

		// make duration positive
		$duration = abs($duration);

		if ($durationType == '24') { // duration type is 24, full days, we're finished very quickly
			$full_working_days = $duration;
		} else
			if ($durationType == '1') { // durationType is 1 hour
				// get w2P time constants
				$cal_day_start = intval(w2PgetConfig('cal_day_start'));
				$cal_day_end = intval(w2PgetConfig('cal_day_end'));
				$dwh = intval(w2PgetConfig('daily_working_hours'));

				// move to the next working day if the first day is a non-working day
				($sgn > 0) ? $this->next_working_day() : $this->prev_working_day();

				// calculate the hours spent on the first day
				$firstDay = ($sgn > 0) ? min($cal_day_end - $this->hour, $dwh) : min($this->hour - $cal_day_start, $dwh);

				/*
				** Catch some possible inconsistencies:
				** If we're later than cal_end_day or sooner than cal_start_day then we don't need to
				** subtract any time from duration. The difference is greater than the # of daily working hours
				*/
				if ($firstDay < 0) {
					$firstDay = 0;
				}
				// Intraday additions are handled easily by just changing the hour value
				if ($duration <= $firstDay) {
					($sgn > 0) ? $this->setHour($this->hour + $duration) : $this->setHour($this->hour - $duration);
					return $this;
				}

				// the effective first day hours value
				$firstAdj = min($dwh, $firstDay);

				// subtract the first day hours from the total duration
				$duration -= $firstAdj;

				// we've already processed the first day; move by one day!
				$this->addDays(1 * $sgn);

				// make sure that we didn't move to a non-working day
				($sgn > 0) ? $this->next_working_day() : $this->prev_working_day();

				// end of proceeding the first day

				// calc the remaining time and the full working days part of this residual
				$hoursRemaining = ($duration > $dwh) ? ($duration % $dwh) : $duration;
				$full_working_days = round(($duration - $hoursRemaining) / $dwh);

				// (proceed the full days later)

				// proceed the last day now

				// we prefer wed 16:00 over thu 08:00 as end date :)
				if ($hoursRemaining == 0 && $full_working_day > 0) {
					$full_working_days--;
					($sgn > 0) ? $this->setHour($cal_day_start + $dwh) : $this->setHour($cal_day_end - $dwh);
				} else {
					($sgn > 0) ? $this->setHour($cal_day_start + $hoursRemaining) : $this->setHour($cal_day_end - $hoursRemaining);
				}
				//end of proceeding the last day
			}

		// proceeding the fulldays finally which is easy
		// Full days
		for ($i = 0; $i < $full_working_days; $i++) {
			$this->addDays(1 * $sgn);
			if (!$this->isWorkingDay()) {
				// just 'ignore' this non-working day
				$full_working_days++;
			}
		}
		//end of proceeding the fulldays

		return $this->next_working_day();
	}

	/* Calculating _robustly_ the working duration between two dates
	**
	** Works in both directions: forwards/prospective and backwards/retrospective
	** Respects non-working days
	** SantosDiez - Credit for better variable names
	**
	** @param	obj	DateObject	may be viewed as end date
	** @return	float				working duration in hours
	*/
	public function calcDuration($endDate) {

		// since one will alter the date ($this) one better copies it to a new instance
		$startDate = new w2p_Utilities_Date();
		$startDate->copy($this);
		
		// get w2P time constants
		$day_start_hour = intval(w2PgetConfig('cal_day_start'));
		$day_end_hour = intval(w2PgetConfig('cal_day_end'));
		$work_hours = intval(w2PgetConfig('daily_working_hours'));
		
		// It will change the resulting duration sign (backward/forward durations)
		$sign = 1;
	
		// If end date is earlier than start date
		if($endDate->before($startDate)) {
			// Change sign and switch dates
			$sign = -1;
			$tmp = $endDate;
			$endDate = $startDate;
			$startDate = $tmp;
		}
	
		$duration = 0.0;

		if($startDate->isWorkingDay()) {
			// Start date time in minutes
			$dateDiff = $startDate->diff($endDate);
			$start_date_minutes = $startDate->getHour()*60 + $startDate->getMinute();
			
			// Calculate the time worked the first day
			$duration += min($work_hours*60, $day_end_hour*60 - $start_date_minutes, $dateDiff);
		
			// Jump to the second day
			$startDate->addDays(1);
		} else {
			// Jump to the first working day
			while(!$startDate->isWorkingDay()) {
				$startDate->addDays(1);
			}
		}
	
		// Reset time to the beginning of the working day (just for safety)
		$startDate->setTime($day_start_hour);
	
		// While we don't reach the end date		
		while($startDate->before($endDate)) {
			
			// Just do things when the day is a working day
			if($startDate->isWorkingDay()) {
			
				// Check if we're at the last day. If that's the case, just calculate hour differences
				if($startDate->isSameDay($endDate)) {
					$duration += min($endDate->getHour()*60 + $endDate->getMinute() - $day_start_hour*60, $work_hours*60);
				} else {	// Else, add a whole working day
					$duration += $work_hours*60;
				}
			}
			
			// Increment a day
			$startDate->addDays(1);
		}

		return $sign * $duration / 60.0;
	}

	public function workingDaysInSpan($e) {
		global $AppUI;

		// assume start is before end and set a default signum for the duration
		$sgn = 1;

		// check whether start before end, interchange otherwise
		if ($e->before($this)) {
			// duration is negative, set signum appropriately
			$sgn = -1;
		}

		$wd = 0;
		$days = $e->dateDiff($this);
		$start = $this;

		for ($i = 0; $i <= $days; $i++) {
			if ($start->isWorkingDay()) {
				$wd++;
			}
			$start->addDays(1 * $sgn);
		}

		return $wd;
	}

	/**
	 *	Clone the current w2p_Utilities_Date object
	 *
	 *	@return	object	The new record object or null if error
	 **/
	public function duplicate() {

        /*
        *  PHP4 is no longer supported or allowed. The
        *    installer/upgrader/converter simply stops executing.
        *  This method also appears in the w2p_Database_Query and w2p_Core_BaseObject (modified) class.
        */
		return clone ($this);
	}

	/* Calculating a future date considering a given duration
	**
	** Respects non-working days and the working hours and the begining and end of days
	** SantosDiez - Credit for better variable names
	**
	** @param	duration		Duration to be added to the date
	** @param	durationType	Duration Type: 1=hours, 24=days
	** @return	w2p_Utilities_Date		The w2p_Utilities_Date object of the finish date
	*/
	public function calcFinish($duration, $durationType) {
		
		// since one will alter the date ($this) one better copies it to a new instance
		$finishDate = new w2p_Utilities_Date();
		$finishDate->copy($this);
		
		// get w2P time constants
		$day_start_hour = intval(w2PgetConfig('cal_day_start'));
		$day_end_hour = intval(w2PgetConfig('cal_day_end'));
		$work_hours = intval(w2PgetConfig('daily_working_hours'));
		
		$duration_in_minutes = ($durationType == 24) ? $duration*$work_hours*60 : $duration*60;
		
		// Jump to the first working day
		while(!$finishDate->isWorkingDay()) {
			$finishDate->addDays(1);
		}
		
		$first_day_minutes = min($day_end_hour*60 - $finishDate->getHour()*60 - $finishDate->getMinute(), $work_hours*60, $duration_in_minutes);
		
		$finishDate->addSeconds($first_day_minutes*60);
		
		$duration_in_minutes -= $first_day_minutes;
		
		while($duration_in_minutes != 0) {
			// Jump to the next day
			$finishDate->addDays(1);
			// Reset date's time to the first hour in the morning
			$finishDate->setTime($day_start_hour);
			
			// Jump all non-working days
			while(!$finishDate->isWorkingDay()) {
				$finishDate->addDays(1);
			}
			
			$day_work_minutes = min($work_hours*60, $duration_in_minutes);
			
			$finishDate->addSeconds($day_work_minutes*60);
			$duration_in_minutes -= $day_work_minutes;
		}
		
		return $finishDate;
				
	}

	/**
	 * Converts this date to a new time zone
	 *
	 * Converts this date to a new time zone.
	 * WARNING: This may not work correctly if your system does not allow
	 * putenv() or if localtime() does not work in your environment.
	 *
	 * @access public
	 * @param string $tz the time zone ID - index in $GLOBALS['_DATE_TIMEZONE_DATA']
	 */
	public function convertTZ($tz)
	{
		// convert to UTC
		$offset = intval($this->tz['offset'] / 1000);
		if ($this->tz['hasdst']) {
			$offset += 3600;
		}
		$this->addSeconds(0 - $offset);

		// convert UTC to new timezone
		$tzID = (is_object($tz)) ? $tz->id : $tz;

        $offset = intval($GLOBALS['_DATE_TIMEZONE_DATA'][$tzID]['offset'] / 1000);
		if ($this->tz['hasdst']) {
			$offset += 3600;
		}
		$this->addSeconds($offset);
        $this->setTZ((is_object($tz)) ? $tz->id : $tz);

        return $this;
	}

	public function setTZ($tz)
	{
		$tz_array = $GLOBALS['_DATE_TIMEZONE_DATA'][$tz];
		$tz_array['id'] = $tz;
		$this->tz = $tz_array;
	}
	public function addSeconds( $n )
	{
		$this->setDate( $this->getTime() + $n, DATE_FORMAT_UNIXTIME);
	}
}
