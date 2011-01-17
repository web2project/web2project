<?php /* $Id$ $URL$ */

/**
 * @package web2project
 * @subpackage utilites
 */

require_once ($AppUI->getLibraryClass('PEAR/Date'));

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
		$timeStamp = $this->getTime();
		$oldHour = $this->getHour();
		$this->setDate($timeStamp + SEC_DAY * ceil($n), DATE_FORMAT_UNIXTIME);

		if (($oldHour - $this->getHour()) || !is_int($n)) {
			$timeStamp += ($oldHour - $this->getHour()) * SEC_HOUR;
			$this->setDate($timeStamp + SEC_DAY * $n, DATE_FORMAT_UNIXTIME);
		}
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

		$working_days = w2PgetConfig('cal_working_days');
		$working_days = ((is_null($working_days)) ? array('1', '2', '3', '4', '5') : explode(',', $working_days));
		return in_array($this->getDayOfWeek(), $working_days);
	}

	public function getAMPM() {
		return (($this->getHour() > 11) ? 'pm' : 'am');
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
	**
	**
	** @param	obj	DateObject	may be viewed as end date
	** @return	int							working duration in hours
	*/
	public function calcDuration($e) {

		// since one will alter the date ($this) one better copies it to a new instance
		$s = new w2p_Utilities_Date();
		$s->copy($this);

		// get w2P time constants
		$cal_day_start = intval(w2PgetConfig('cal_day_start'));
		$cal_day_end = intval(w2PgetConfig('cal_day_end'));
		$dwh = intval(w2PgetConfig('daily_working_hours'));

		// assume start is before end and set a default signum for the duration
		$sgn = 1;

		// check whether start before end, interchange otherwise
		if ($e->before($s)) {
			// calculated duration must be negative, set signum appropriately
			$sgn = -1;

			$dummy = clone $s;
			$s->copy($e);
			$e = $dummy;
		}

		// determine the (working + non-working) day difference between the two dates
		$days = $e->dateDiff($s);

		// if it is an intraday difference one is finished very easily
		if ($days == 0) {
			return min($dwh, abs($e->hour - $s->hour)) * $sgn;
		}

		// initialize the duration var
		$duration = 0;

		// process the first day

		// take into account the first day if it is a working day!
		$duration += $s->isWorkingDay() ? min($dwh, abs($cal_day_end - $s->hour)) : 0;
		$s->addDays(1);

		// end of processing the first day

		// calc workingdays between start and end
		for ($i = 1; $i < $days; $i++) {
			$duration += $s->isWorkingDay() ? $dwh : 0;
			$s->addDays(1);
		}

		// take into account the last day in span only if it is a working day!
		$duration += $s->isWorkingDay() ? min($dwh, abs($e->hour - $cal_day_start)) : 0;

		return $duration * $sgn;
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
        *  This method also appears in the DBQuery and w2p_Core_BaseObject (modified) class.
        */
		return clone ($this);
	}

	/* Calculating a future date considering a given duration
	**
	** Respects non-working days and the working hours and the begining and end of days
	**
	**
	** @param	durn		Duration to be added to the date
	** @param	durnType	Duration Type: 1=hours, 24=days
	** @return	w2p_Utilities_Date		The w2p_Utilities_Date object of the finish date
	*/
	public function calcFinish($durn, $durnType) {

		// since one will alter the date ($this) one better copies it to a new instance
		$f = new w2p_Utilities_Date();
		$f->copy($this);

		// get w2P time constants
		$cal_day_start = intval(w2PgetConfig('cal_day_start'));
		$cal_day_end = intval(w2PgetConfig('cal_day_end'));
		$workHours = intval(w2PgetConfig('daily_working_hours'));
		$workingDays = w2PgetConfig('cal_working_days');
		$working_days = explode(',', $workingDays);

		//temporary variables
		$inc = floor($durn);
		$hoursToAddToLastDay = 0;
		$hoursToAddToFirstDay = $durn;
		$fullWorkingDays = 0;
		$int_st_hour = $f->getHour();
		//catch the gap between the working hours and the open hours (like lunch periods)
		$workGap = $cal_day_end - $cal_day_start - $workHours;

		// calculate the number of non-working days
		$k = 7 - count($working_days);

		$durnMins = ($durn - $inc) * 60;
		if (($f->getMinute() + $durnMins) >= 60) {
			$inc++;
		}

		$mins = ($f->getMinute() + $durnMins) % 60;
		if ($mins > 38) {
			$f->setMinute(45);
		} elseif ($mins > 23) {
			$f->setMinute(30);
		} elseif ($mins > 8) {
			$f->setMinute(15);
		} else {
			$f->setMinute(0);
		}

		// jump over to the first working day
		for ($i = 0; $i < $k; $i++) {
			if (array_search($f->getDayOfWeek(), $working_days) === false) {
				$f->addDays(1);
			}
		}

		if ($durnType == 24) {
			if ($f->getHour() == $cal_day_start && $f->getMinute() == 0) {
				$fullWorkingDays = ceil($inc);
				$f->setMinute(0);
			} else {
				$fullWorkingDays = ceil($inc) + 1;
			}

			// Include start day as a working day (if it is one)
			if (!(array_search($f->getDayOfWeek(), $working_days) === false)) {
				$fullWorkingDays--;
			}

			for ($i = 0; $i < $fullWorkingDays; $i++) {
				$f->addDays(1);
				if (array_search($f->getDayOfWeek(), $working_days) === false) {
					$i--;
				}
			}

			if ($f->getHour() == $cal_day_start && $f->getMinute() == 0) {
				$f->setHour($cal_day_end);
				$f->setMinute(0);
			}
		} else {
			$hoursToAddToFirstDay = $inc;
			if ($f->getHour() + $inc > ($cal_day_end - $workGap)) {
				$hoursToAddToFirstDay = ($cal_day_end - $workGap) - $f->getHour();
			}
			if ($hoursToAddToFirstDay > $workHours) {
				$hoursToAddToFirstDay = $workHours;
			}
			$inc -= $hoursToAddToFirstDay;
			$hoursToAddToLastDay = $inc % $workHours;
            $fullWorkingDays = floor(($inc - $hoursToAddToLastDay) / $workHours);

			if ($hoursToAddToLastDay <= 0 && !($hoursToAddToFirstDay == $workHours)) {
				$f->setHour($f->getHour() + $hoursToAddToFirstDay);
			} elseif ($hoursToAddToLastDay == 0) {
				$f->setHour($f->getHour() + $hoursToAddToFirstDay + $workGap);
            } else {
				$f->setHour($cal_day_start + $hoursToAddToLastDay);
				$f->addDays(1);
			}

			if (($f->getHour() == $cal_day_end || ($f->getHour() - $int_st_hour) == ($workHours + $workGap)) && $mins > 0) {
				$f->addDays(1);
				$f->setHour($cal_day_start);
			}

			// boolean for setting later if we just found a non-working day
			// and therefore do not have to add a day in the next loop
			// (which would have caused to not respecting multiple non-working days after each other)
			$g = false;
			for ($i = 0, $i_cmp = ceil($fullWorkingDays); $i < $i_cmp; $i++) {
				if (!$g) {
					$f->addDays(1);
				}
				$g = false;
				// calculate overriden non-working days
				if (array_search($f->getDayOfWeek(), $working_days) === false) {
					$f->addDays(1);
					$i--;
					$g = true;
				}
			}
		}

		// if there was no fullworkingday we have to check whether the end day is a working day
		// and in the negative case postpone the end date by appropriate days
		for ($i = 0, $i_cmp = 7 - count($working_days); $i < $i_cmp; $i++) {
			// override  possible non-working enddays
			if (array_search($f->getDayOfWeek(), $working_days) === false) {
				$f->addDays(1);
			}
		}

		return $f;
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
