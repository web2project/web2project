<?php /* $Id$ $URL$ */

/**
 *	@package web2project
 *	@subpackage api
 *	@version $Revision$
 */

/*
 *  A simple iCal creator for web2project.
 *
 *  Lots  of thanks to Ben Fortuna for his fantastic iCal Validator - http:
 * //severinghaus.org/projects/icv/  It helped me discover and debug a number
 * of date issues and streamline the whole process.
 *
 */

class w2p_API_iCalendar {

    public static function formatCalendarItem($calendarItem, $module_name) {
        global $AppUI;
        $name = $calendarItem['name'];
        $uid = (isset( $calendarItem['UID'])) ? $calendarItem['UID'] : $module_name.'_'.$calendarItem['id'];
        $description = '';
        $attachments = '';

        if ($calendarItem['project_id']) {
            $description .= $AppUI->_('Project') . ': ' . $calendarItem['project_name'];
            $attachments .= 'ATTACH;VALUE=URL:' . W2P_BASE_URL . '/index.php?m=projects&a=view&project_id=' . $calendarItem['project_id'] . "\r\n";
        }
        $description .= '\r\n----------------------------------------\r\n';
        $description .= $AppUI->_('Description');
        $description .= '\r\n----------------------------------------\r\n';
        $description .= strtr($calendarItem['description'], array("\n" => '\n', "\r\n" =>'\r\n'));
        $description .= '\r\n----------------------------------------\r\n';
        $description .= $AppUI->_('URL');
        $description .= '\r\n----------------------------------------\r\n';
        $description .= $calendarItem['url'];
        $attachments .= 'ATTACH;VALUE=URL:' . $calendarItem['url'];
        $startDate = self::formatDate($calendarItem['startDate']);
        $endDate = self::formatDate($calendarItem['endDate']);
        $updatedDate = self::formatDate($calendarItem['updatedDate']);
        $sequence = 0;

        $eventItem = "BEGIN:VEVENT\r\nDTSTART;VALUE=DATE-TIME:{$startDate}\r\nDTEND;VALUE=DATE-TIME:{$endDate}\r\nUID:{$uid}\r\nSUMMARY:{$name}\r\nDESCRIPTION:{$description}\r\n{$attachments}\r\nDTSTAMP:{$updatedDate}\r\nSEQUENCE:{$sequence}\r\nEND:VEVENT\r\n";

        return $eventItem;
    }

    private function formatDate($mysqlDate) {
        $myDate = new CDate($mysqlDate);

        $myDatetime = $myDate->format('%Y%m%d %T');
        $myDatetime = str_replace(':', '', $myDatetime);
        $myDatetime = str_replace(' ', 'T', $myDatetime).'Z';

        return $myDatetime;
    }
}