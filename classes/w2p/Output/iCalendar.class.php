<?php

/**
 * A simple iCal creator for web2project.
 *
 * Lots  of thanks to Ben Fortuna for his fantastic iCal Validator -
 * http://severinghaus.org/projects/icv/  It helped me discover and debug a
 * number of date issues and streamline the whole process.
 *
 * @package     web2project\output
 */

class w2p_Output_iCalendar
{
    /**
     * This takes a single array item at a time and transforms it into a proper
     *  iCalendar VEVENT string and returns it.
     *
     * @global type $AppUI
     * @param type $calendarItem
     * @param type $module_name
     * @return string
     */
    public static function formatCalendarItem($calendarItem, $module_name)
    {
        global $AppUI;

        $name = $calendarItem['name'];
        $uid = (isset( $calendarItem['UID'])) ? $calendarItem['UID'] : $module_name.
            '_'.$calendarItem['id'];
        $description = '';
        $attachments = '';

        if ($calendarItem['project_id']) {
            $description .= $AppUI->_('Project') . ': ' .
                $calendarItem['project_name'];
            $attachments .= 'ATTACH;VALUE=URL:' . W2P_BASE_URL .
                '/index.php?m=projects&a=view&project_id=' .
                $calendarItem['project_id'] . "\r\n";
        }
        $description .= '\r\n----------------------------------------\r\n';
        $description .= $AppUI->_('Description');
        $description .= '\r\n----------------------------------------\r\n';
        $description .= strtr($calendarItem['description'], array("\n" => '\n',
                                                                  "\r\n" =>'\r\n'));
        $description .= '\r\n----------------------------------------\r\n';
        $description .= $AppUI->_('URL');
        $description .= '\r\n----------------------------------------\r\n';
        $description .= $calendarItem['url'];
        $attachments .= 'ATTACH;VALUE=URL:' . $calendarItem['url'];
        $startDate = self::formatDate($calendarItem['startDate']);
        $endDate = self::formatDate($calendarItem['endDate']);
        $updatedDate = self::formatDate($calendarItem['updatedDate']);
        $sequence = (int) $calendarItem['sequence'];

        $eventItem = "BEGIN:VEVENT\r\nDTSTART;VALUE=DATE-TIME:{$startDate}\r\n" .
            "DTEND;VALUE=DATE-TIME:{$endDate}\r\nUID:{$uid}\r\nSUMMARY:{$name}\r\n" .
            "DESCRIPTION:{$description}\r\n{$attachments}\r\n" .
            "DTSTAMP:{$updatedDate}\r\nSEQUENCE:{$sequence}\r\nEND:VEVENT\r\n";

        return $eventItem;
    }

    /**
     * This is our kludgy pre-5.3 way of formatting datetimes.
     *
     * @todo This should get a review once we can make 5.3 our minimum version.
     */
    private function formatDate($mysqlDate)
    {
        $myDate = new DateTime($mysqlDate);

        $timestamp = strtotime($mysqlDate);
        // The following line checks to see if the date is in DST range.
        $timestamp -= $myDate->format('I')*60*60;
        $myDatetime = date('Ymd His', $timestamp);
        $myDatetime = str_replace(' ', 'T', $myDatetime).'Z';

        return $myDatetime;
    }
}