<?php
/**
 * A simple iCal creator for web2project.
 *
 * Lots  of thanks to Ben Fortuna for his fantastic iCal Validator -
 * http://severinghaus.org/projects/icv/  It helped me discover and debug a
 * number of date issues and streamline the whole process.
 *
 * @package     web2project\deprecated
 *
 * @deprecated since version 3.0
 */

class w2p_API_iCalendar extends w2p_Output_iCalendar
{
    public static function formatCalendarItem($calendarItem, $module_name)
    {
        trigger_error("w2p_API_iCalendar has been deprecated in v3.0 and will be removed by v4.0. Please use w2p_Output_iCalendar instead.", E_USER_NOTICE);

        return parent::formatCalendarItem($calendarItem, $module_name);
    }
}