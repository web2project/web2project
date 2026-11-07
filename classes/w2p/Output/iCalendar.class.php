<?php

/**
 * A simple iCal creator for web2project.
 *
 * Lots  of thanks to Ben Fortuna for his fantastic iCal Validator -
 * http://severinghaus.org/projects/icv/  It helped me discover and debug a
 * number of date issues and streamline the whole process.
 *
 * @package web2project\output
 */

class w2p_Output_iCalendar
{
    /**
     * This takes a single array item at a time and transforms it into a proper
     *  iCalendar VEVENT string and returns it.
     *
     * @param mixed $calendarItem
     * @param string $module_name
     *
     * @global w2p_Core_CAppUI $AppUI
     *
     * @return string
     */
    public static function formatCalendarItem($calendarItem, $module_name)
    {
        /** @var w2p_Core_CAppUI $AppUI */
        global $AppUI;

        $name = $calendarItem['name'];
        $uid = (isset( $calendarItem['UID'])) ? $calendarItem['UID'] : $module_name.
            '_'.$calendarItem['id'];
        $description = '';
        $attachments = '';

        if (!empty($calendarItem['project_id'])) {
            $description .= $AppUI->_('Project') . ': ' .
                $calendarItem['project_name'];
            $attachments .= 'ATTACH;VALUE=URL:' . W2P_BASE_URL .
                '/index.php?m=projects&a=view&project_id=' .
                $calendarItem['project_id'] . "\r\n";
        }
        $description .= '\r\n----------------------------------------\r\n';
        $description .= $AppUI->_('Description');
        $description .= '\r\n----------------------------------------\r\n';
        $description .= strtr((string) ($calendarItem['description'] ?? ''), array("\n" => '\n',
                                                                  "\r\n" =>'\r\n'));
        $description .= '\r\n----------------------------------------\r\n';
        $description .= $AppUI->_('URL');
        $description .= '\r\n----------------------------------------\r\n';
        $description .= $calendarItem['url'];
        $attachments .= 'ATTACH;VALUE=URL:' . $calendarItem['url'];
        $startDate = self::formatDate($calendarItem['startDate']);
        $endDate = self::formatDate($calendarItem['endDate']);
        $updatedDate = self::formatDate($calendarItem['updatedDate'] ?? null);
        $sequence = (int) ($calendarItem['sequence'] ?? 0);

        $eventItem = "BEGIN:VEVENT\r\nDTSTART;VALUE=DATE-TIME:{$startDate}\r\n" .
            "DTEND;VALUE=DATE-TIME:{$endDate}\r\nUID:{$uid}\r\nSUMMARY:{$name}\r\n" .
            "DESCRIPTION:{$description}\r\n{$attachments}\r\n" .
            "DTSTAMP:{$updatedDate}\r\nSEQUENCE:{$sequence}\r\nEND:VEVENT\r\n";

        return $eventItem;
    }

    /**
     * Formats a database datetime, which web2project stores in UTC, as an
     * iCalendar UTC date-time (20261024T143000Z).
     *
     * @param string|null $mysqlDate
     *
     * @return string
     */
    private static function formatDate($mysqlDate)
    {
        $utc = new DateTimeZone('UTC');
        try {
            $date = new DateTime((string) $mysqlDate !== '' ? (string) $mysqlDate : 'now', $utc);
        } catch (Exception $e) {
            $date = new DateTime('now', $utc);
        }

        return $date->format('Ymd\THis\Z');
    }
}