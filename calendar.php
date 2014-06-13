<?php
require_once 'base.php';
require_once W2P_BASE_DIR . '/includes/config.php';
require_once W2P_BASE_DIR . '/includes/main_functions.php';
require_once W2P_BASE_DIR . '/includes/db_adodb.php';

$AppUI = new w2p_Core_CAppUI();

$token = w2PgetParam($_GET, 'token', '');
$token = preg_replace("/[^A-Za-z0-9]/", "", $token);
$format = w2PgetParam($_GET, 'format', 'ical');

$user = new CUser();
$userId = $user->getIdByToken($token);
$AppUI->loadPrefs($userId);
$AppUI->user_id = $userId;
$AppUI->setUserLocale();
include W2P_BASE_DIR . '/locales/' . $AppUI->user_locale . '/locales.php';
include W2P_BASE_DIR . '/locales/core.php';

$defaultTZ = w2PgetConfig('system_timezone', 'UTC');
$defaultTZ = ('' == $defaultTZ) ? 'UTC' : $defaultTZ;
date_default_timezone_set($defaultTZ);

switch ($format) {
    //TODO: We only output in vCal, are there others we need to consider?
    case 'vcal':
    default:
        $format = 'vcal';
        header('Content-Type: text/calendar');
        header('Content-disposition: attachment; filename="calendar.ics"');
        break;
}

if ($userId > 0) {
    $myTimezoneName = date('e');
    $calendarHeader = "BEGIN:VCALENDAR\nPRODID:-//web2project//EN\nVERSION:2.0\nCALSCALE:GREGORIAN\nMETHOD:PUBLISH\nX-WR-TIMEZONE:UTC\n";
    $calendarFooter = "END:VCALENDAR";

    $hooks = new w2p_System_HookHandler($AppUI);
    $buffer = $hooks->calendar();

    echo $calendarHeader.$buffer.$calendarFooter;
}