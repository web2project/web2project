<?php
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}
// @todo    convert to template

global $AppUI, $project_id;

$start_date = new w2p_Utilities_Date('2001-01-01 00:00:00');
$end_date = new w2p_Utilities_Date('2100-12-31 23:59:59');

$items = CEvent::getEventsForPeriod($start_date, $end_date, 'all', 0, $project_id);

$module = new w2p_System_Module();
$fields = $module->loadSettings('events', 'project_view');

if (0 == count($fields)) {
    $fieldList = array('event_start_date', 'event_end_date', 'event_type',
        'event_name');
    $fieldNames = array('Start Date', 'End Date', 'Type', 'Event');

    $module->storeSettings('events', 'project_view', $fieldList, $fieldNames);

    $fields = array_combine($fieldList, $fieldNames);
}

?><a name="events-project_view"> </a><?php

$event_types = w2PgetSysVal('EventType');
$customLookups = array('event_type' => $event_types);

$listTable = new w2p_Output_ListTable($AppUI);
$listTable->df .= ' ' . $AppUI->getPref('TIMEFORMAT');      // @todo cleanup this hack
echo $listTable->startTable();
echo $listTable->buildHeader($fields);
echo $listTable->buildRows($items, $customLookups);
echo $listTable->endTable();