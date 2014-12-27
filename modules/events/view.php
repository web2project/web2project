<?php
if (!defined('W2P_BASE_DIR')) {
    die('You should not access this file directly.');
}
// @todo    convert to template
$object_id = (int) w2PgetParam($_GET, 'event_id', 0);

$object = new CEvent();

if (!$object->load($object_id)) {
    $AppUI->redirect(ACCESS_DENIED);
}

$canEdit   = $object->canEdit();
$canDelete = $object->canDelete();

// load the event recurs types
$recurs = array('Never', 'Hourly', 'Daily', 'Weekly', 'Bi-Weekly', 'Every Month', 'Quarterly', 'Every 6 months', 'Every Year');

$assigned = $object->getAssigned();

$start_date = $object->event_start_date ? new w2p_Utilities_Date($object->event_start_date) : new w2p_Utilities_Date();
$end_date = $object->event_end_date ? new w2p_Utilities_Date($object->event_end_date) : new w2p_Utilities_Date();
if ($object->event_project) {
    $project = new CProject();
    $event_project = $project->load($object->event_project)->project_name;
}

// setup the title block
$titleBlock = new w2p_Theme_TitleBlock('View Event', 'icon.png', $m);
$titleBlock->addCrumb('?m=events&a=year_view&date=' . $start_date->format(FMT_TIMESTAMP_DATE), 'year view');
$titleBlock->addCrumb('?m=events&amp;date=' . $start_date->format(FMT_TIMESTAMP_DATE), 'month view');
$titleBlock->addCrumb('?m=events&a=week_view&date=' . $start_date->format(FMT_TIMESTAMP_DATE), 'week view');
$titleBlock->addCrumb('?m=events&amp;a=day_view&amp;date=' . $start_date->format(FMT_TIMESTAMP_DATE) . '&amp;tab=0', 'day view');

if ($canEdit) {
    $titleBlock->addCell();
    $titleBlock->addCell('
		<form action="?m=events&amp;a=addedit" method="post" accept-charset="utf-8">
			<input type="submit" class="button" value="' . $AppUI->_('New event') . '" />
		</form>', '', '', '');
    $titleBlock->addCrumb('?m=events&amp;a=addedit&amp;event_id=' . $object_id, 'edit this event');
    if ($canDelete) {
        $titleBlock->addCrumbDelete('delete event', $canDelete, $msg);
    }
}
$titleBlock->show();

$view = new \Web2project\Output\HTML\View($AppUI, $object, 'Event');
echo $view->renderDelete();

$types = w2PgetSysVal('EventType');

include $AppUI->getTheme()->resolveTemplate($m . '/' . $a);
