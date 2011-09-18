<?php /* $Id$ $URL$ */
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}

global $AppUI, $project_id, $deny, $canRead, $canEdit, $w2Pconfig, $start_date, $end_date, $this_day, $event_filter, $event_filter_list;

//TODO: This is a hack until we can refactor getEventTooltip() somewhere else..
include 'links_events.php';

$perms = &$AppUI->acl();
$user_id = $AppUI->user_id;
$other_users = false;
$no_modify = false;

$start_date =  new w2p_Utilities_Date('2001-01-01 00:00:00');
$end_date =  new w2p_Utilities_Date('2100-12-31 23:59:59');

// assemble the links for the events
$events = CEvent::getEventsForPeriod($start_date, $end_date, 'all', 0, $project_id);

$start_hour = w2PgetConfig('cal_day_start');
$end_hour = w2PgetConfig('cal_day_end');

$tf = $AppUI->getPref('TIMEFORMAT');
$df = $AppUI->getPref('SHDATEFORMAT');
$types = w2PgetSysVal('EventType');

?>
<a name="calendar-project_view"> </a>
<table width="100%" border="0" cellpadding="2" cellspacing="1" class="tbl">
    <tr>
        <?php
        $fieldList = array();
        $fieldNames = array();
        $fields = w2p_Core_Module::getSettings('calendar', 'project_view');
        if (count($fields) > 0) {
            foreach ($fields as $field => $text) {
                $fieldList[] = $field;
                $fieldNames[] = $text;
            }
        } else {
            // TODO: This is only in place to provide an pre-upgrade-safe 
            //   state for versions earlier than v3.0
            //   At some point at/after v4.0, this should be deprecated
            $fieldList = array('event_start_date', 'event_end_date', 'event_type',
                'event_title');
            $fieldNames = array('Start Date', 'End Date', 'Type', 'Event');
        }
//TODO: The link below is commented out because this view doesn't support sorting... yet.
        foreach ($fieldNames as $index => $name) {
            ?><th nowrap="nowrap">
<!--                <a href="?m=projects&a=view&project_id=<?php echo $project_id; ?>&sort=<?php echo $fieldList[$index]; ?>#calendar-project_view" class="hdr">-->
                    <?php echo $AppUI->_($fieldNames[$index]); ?>
<!--                </a>-->
            </th><?php
        }
        ?>
    </tr>
<?php

$html = '';
if (count($events)) {
    foreach ($events as $row) {
        $html .= '<tr>';
        $start = new w2p_Utilities_Date($row['event_start_date']);
        $end = new w2p_Utilities_Date($row['event_end_date']);
        $html .= '<td width="25%" nowrap="nowrap">' . $start->format($df . ' ' . $tf) . '</td>';
        $html .= '<td width="25%" nowrap="nowrap">' . $end->format($df . ' ' . $tf) . '</td>';

        $href = '?m=calendar&a=view&event_id=' . $row['event_id'];
        $alt = $row['event_description'];

        $html .= '<td width="10%" nowrap="nowrap">';
        $html .= w2PshowImage('event' . $row['event_type'] . '.png', 16, 16, '', '', 'calendar');
        $html .= '&nbsp;<b>' . $AppUI->_($types[$row['event_type']]) . '</b><td>';

        $html .= w2PtoolTip($row['event_title'], getEventTooltip($row['event_id']), true);
        $html .= '<a href="' . $href . '" class="event">';
        $html .= $row['event_title'];
        $html .= '</a>';
        $html .= w2PendTip();

        $html .= '</td></tr>';
    }
} else {
    echo '<tr><td colspan="'.count($fieldNames).'">' . $AppUI->_('No data available') . '</td></tr>';
}
echo $html;
?>
</table>