<?php
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}

global $AppUI, $project_id, $deny, $canRead, $canEdit, $w2Pconfig, $start_date, $end_date, $this_day, $event_filter, $event_filter_list;

$perms = &$AppUI->acl();
$user_id = $AppUI->user_id;
$other_users = false;
$no_modify = false;

$start_date =  new w2p_Utilities_Date('2001-01-01 00:00:00');
$end_date =  new w2p_Utilities_Date('2100-12-31 23:59:59');

// assemble the links for the events
$items = CEvent::getEventsForPeriod($start_date, $end_date, 'all', 0, $project_id);

$start_hour = w2PgetConfig('cal_day_start');
$end_hour = w2PgetConfig('cal_day_end');


$types = w2PgetSysVal('EventType');

$fieldList = array();
$fieldNames = array();

$module = new w2p_Core_Module();
$fields = $module->loadSettings('calendar', 'project_view');
if (count($fields) > 0) {
    $fieldList = array_keys($fields);
    $fieldNames = array_values($fields);
} else {
    // TODO: This is only in place to provide an pre-upgrade-safe
    //   state for versions earlier than v3.0
    //   At some point at/after v4.0, this should be deprecated
    $fieldList = array('event_start_date', 'event_end_date', 'event_type',
        'event_name');
    $fieldNames = array('Start Date', 'End Date', 'Type', 'Event');

    //$module->storeSettings('calendar', 'project_view', $fieldList, $fieldNames);

    $fields = array_combine($fieldList, $fieldNames);
}

?><a name="calendar-project_view"> </a><?php

$event_types = w2PgetSysVal('EventType');
$customLookups = array('event_type' => $event_types);

$listTable = new w2p_Output_ListTable($AppUI);
$listTable->df .= ' ' . $AppUI->getPref('TIMEFORMAT');      // @todo cleanup this hack
echo $listTable->startTable();
echo $listTable->buildHeader($fields);
echo $listTable->buildRows($items, $customLookups);
echo $listTable->endTable();