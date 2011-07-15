<?php
require_once 'base.php';
require_once W2P_BASE_DIR . '/includes/config.php';
require_once W2P_BASE_DIR . '/includes/main_functions.php';
require_once W2P_BASE_DIR . '/includes/db_adodb.php';
require_once W2P_BASE_DIR . '/classes/ui.class.php';

$AppUI = new CAppUI;

$token = w2PgetParam($_GET, 'token', '');
$token = preg_replace("/[^A-Za-z0-9]/", "", $token );
$format = w2PgetParam($_GET, 'format', 'ical');

$userId = CUser::getUserIdByToken($token);
$AppUI->loadPrefs($userId);
$AppUI->user_id = $userId;
$AppUI->setUserLocale();
@include_once (W2P_BASE_DIR . '/locales/' . $AppUI->user_locale . '/locales.php');
include_once W2P_BASE_DIR . '/locales/core.php';

$defaultTZ = w2PgetConfig('system_timezone', 'Europe/London');
$defaultTZ = ('' == $defaultTZ) ? 'Europe/London' : $defaultTZ;
date_default_timezone_set($defaultTZ);

switch ($format) {
    //TODO: We only output in vCal, are there others we need to consider?
    case 'vcal':
    default:
        $format = 'vcal';
        header ( 'Content-Type: text/calendar' );
        header ( 'Content-disposition: attachment; filename="calendar.ics"' );
        break;
}

if ($userId > 0) {
    $moduleList = $AppUI->getLoadableModuleList();

    $myTimezoneName = date('e');
    $calendarHeader = "BEGIN:VCALENDAR\nPRODID:-//web2project//EN\nVERSION:2.0\nCALSCALE:GREGORIAN\nMETHOD:PUBLISH\nX-WR-TIMEZONE:Europe/London\n";
    $calendarFooter = "END:VCALENDAR";

    foreach ($moduleList as $module) {
		if (!in_array($module['mod_main_class'], get_declared_classes())) {
			require_once ($AppUI->getModuleClass($module['mod_directory']));
		}
		$object = new $module['mod_main_class']();
        if (is_callable(array($object, 'hook_calendar'))) {
            $itemList = $object->hook_calendar($userId);
            if (is_array($itemList)) {
                foreach ($itemList as $calendarItem) {
                    $buffer .= w2p_API_iCalendar::formatCalendarItem($calendarItem, $module['mod_directory']);
                }
            }
        }
    }
    echo $calendarHeader.$buffer.$calendarFooter;
}