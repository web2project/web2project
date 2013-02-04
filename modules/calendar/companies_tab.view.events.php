<?php
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}

global $AppUI, $company_id, $deny, $canRead, $canEdit, $w2Pconfig, $start_date, $end_date, $this_day, $event_filter, $event_filter_list;

$perms = &$AppUI->acl();
$user_id = $AppUI->user_id;
$other_users = false;
$no_modify = false;

$start_date = new w2p_Utilities_Date('1999-12-31 00:00:00');
$end_date = new w2p_Utilities_Date('2036-12-31 00:00:00');

// assemble the links for the events
$events = CEvent::getEventsForPeriod($start_date, $end_date, 'all', 0, 0, $company_id);

$start_hour = w2PgetConfig('cal_day_start');
$end_hour = w2PgetConfig('cal_day_end');

$tf = $AppUI->getPref('TIMEFORMAT');
$df = $AppUI->getPref('SHDATEFORMAT');
$types = w2PgetSysVal('EventType');

$fieldList = array();
$fieldNames = array();

$module = new w2p_Core_Module();
$fields = $module->loadSettings('events', 'company_view');

if (count($fields) > 0) {
    $fieldList = array_keys($fields);
    $fieldNames = array_values($fields);
} else {
    // TODO: This is only in place to provide an pre-upgrade-safe
    //   state for versions earlier than v3.0
    //   At some point at/after v4.0, this should be deprecated
    $fieldList = array('event_start_date', 'event_end_date', 'event_type',
        'event_name');
    $fieldNames = array('Starting Time', 'Ending Time', 'Type', 'Name');

    $module->storeSettings('events', 'company_view', $fieldList, $fieldNames);
}
?>
<table class="tbl list">
    <tr>
        <?php
        foreach ($fieldNames as $index => $name) {
            ?><th nowrap="nowrap">
                    <?php echo $AppUI->_($fieldNames[$index]); ?>
            </th><?php
        }
        ?>
    </tr>
<?php

$html = '';
if (count($events)) {
    $htmlHelper = new w2p_Output_HTMLHelper($AppUI);
    $htmlHelper->df .= ' ' . $AppUI->getPref('TIMEFORMAT');

    foreach ($events as $row) {
        $html .= '<tr>';
        $html .= $htmlHelper->createCell('event_start_date', $row['event_start_date']);
        $html .= $htmlHelper->createCell('event_start_date', $row['event_end_date']);

        $href = '?m=calendar&a=view&event_id=' . $row['event_id'];
        $alt = $row['event_description'];

        $html .= '<td width="10%" nowrap="nowrap">';
        $html .= w2PshowImage('event' . $row['event_type'] . '.png', 16, 16, '', '', 'calendar');
        $html .= '&nbsp;<b>' . $AppUI->_($types[$row['event_type']]) . '</b><td>';
        $html .= $href ? '<a href="' . $href . '" class="event" title="' . $alt . '">' : '';
        $html .= $row['event_name'];
        $html .= $href ? '</a>' : '';

        $html .= '</td></tr>';
    }
} else {
    echo '<tr><td colspan="'.count($fieldNames).'">' . $AppUI->_('No data available') . '</td></tr>';
}
echo $html;
?>
</table>