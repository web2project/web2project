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

    public static function formatCalendarItem($calendarItem) {
        global $AppUI;
        $name = $calendarItem['name'];
        $description = '';
        $attachments = '';
        if ($calendarItem['project_id']) {
            $description .= $AppUI->_('Project') . ': ' . $calendarItem['project_name'];
        }
        $description .= '\n--------------------------------------------------------------------------------------------------\n';
        $description .= $AppUI->_('Description');
        $description .= '\n--------------------------------------------------------------------------------------------------\n';
        $description .= strtr($calendarItem['description'], array("\n" => '\n', "\r\n" =>'\n'));
        $description .= '\n--------------------------------------------------------------------------------------------------\n';
        $description .= $AppUI->_('URL');
        $description .= '\n--------------------------------------------------------------------------------------------------\n';
        if ($calendarItem['project_id']) {
            $description .= W2P_BASE_URL . '/index.php?m=projects&a=view&project_id=' . $calendarItem['project_id'] . '\n';
            $attachments .= 'ATTACH:' . W2P_BASE_URL . '/index.php?m=projects&a=view&project_id=' . $calendarItem['project_id'] . "\n";
        }
        $description .= $calendarItem['url'];
        $attachments .= 'ATTACH:' . $calendarItem['url'];
        $startDate = self::formatDate($calendarItem['startDate']);
        $endDate = self::formatDate($calendarItem['endDate']);
        $updatedDate = self::formatDate($calendarItem['updatedDate']);

        $eventItem = "BEGIN:VEVENT\nDTSTART;VALUE=DATE-TIME:{$startDate}\nDTEND;VALUE=DATE-TIME:{$endDate}\nSUMMARY:{$name}\nDESCRIPTION:{$description}\n{$attachments}\nDTSTAMP;VALUE=DATE:{$updatedDate}\nSEQUENCE:{$sequence}\nEND:VEVENT\n";

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