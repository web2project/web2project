<?php
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly');
}

	/*
	 *  A simple iCal creator for web2project.
	 * 
	 *  Lots  of thanks to Ben Fortuna for his fantastic iCal Validator - http:
	 * //severinghaus.org/projects/icv/  It helped me discover and debug a number
	 * of date issues and streamline the whole process.
	 * 
	 */

	class w2piCal {
		
		public static function formatCalendarItem($calendarItem, $myTimezoneOffset) {

			$name = $calendarItem['name'];
			$startDate = self::formatDate($calendarItem['startDate'], $myTimezoneOffset);
			$endDate = self::formatDate($calendarItem['endDate'], $myTimezoneOffset);
			$updatedDate = self::formatDate($calendarItem['updatedDate'], $myTimezoneOffset);

			$eventItem = "BEGIN:VEVENT\nDTSTART;VALUE=DATE-TIME:{$startDate}\nDTEND;VALUE=DATE-TIME:{$endDate}\nSUMMARY:{$name}\nDTSTAMP;VALUE=DATE:{$updatedDate}\nSEQUENCE:{$sequence}\nEND:VEVENT\n";

			return $eventItem;
		}
		
		private function formatDate($mysqlDate, $myTimezoneOffset) {
			$rawDate = strtotime($mysqlDate);
			$rawDate += -$myTimezoneOffset;

			$myDatetime = date('Ymd His', $rawDate);
			$myDatetime = str_replace(' ', 'T', $myDatetime).'Z';

			return $myDatetime;
		}
	}
?>