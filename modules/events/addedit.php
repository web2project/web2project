<?php
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}
// @todo    convert to template
$object_id = intval(w2PgetParam($_GET, 'event_id', 0));


$object = new CEvent();
$object->setId($object_id);

$canAddEdit = $object->canAddEdit();
$canAuthor = $object->canCreate();
$canEdit = $object->canEdit();

if (!$canAddEdit) {
	$AppUI->redirect(ACCESS_DENIED);
}

global $AppUI, $cal_sdf;
$AppUI->getTheme()->loadCalendarJS();


// get the passed timestamp (today if none)
$date = w2PgetParam($_GET, 'date', null);
// get the passed timestamp (today if none)
$event_project = (int) w2PgetParam($_GET, 'project_id', 0);

$obj = $AppUI->restoreObject();
if ($obj) {
    $object = $obj;
    $object_id = $object->getId();
} else {
    $object->load($object_id);
}
// load the record data
if (!$object && $object_id > 0) {
    $AppUI->setMsg('Event');
    $AppUI->setMsg('invalidID', UI_MSG_ERROR, true);
    $AppUI->redirect('m=' . $m);
}

$object->event_project = ($event_project) ? $event_project : $object->event_project;
$start_date = intval($object->event_start_date) ? new w2p_Utilities_Date($AppUI->formatTZAwareTime($object->event_start_date, '%Y-%m-%d %T')) : new w2p_Utilities_Date();
$end_date = intval($object->event_end_date) ? new w2p_Utilities_Date($AppUI->formatTZAwareTime($object->event_end_date, '%Y-%m-%d %T')) : $start_date;

// load the event types
$types = w2PgetSysVal('EventType');

// Load the users
$perms = &$AppUI->acl();
$users = $perms->getPermittedUsers('events');

// Load the assignees
$assigned = array();
if ($object_id == 0) {
    $assigned[$AppUI->user_id] = $AppUI->user_display_name;
} else {
    $assigned = $object->getAssigned();
}

//check if the user has view permission over the project
if ($object->event_project && !$perms->checkModuleItem('projects', 'view', $object->event_project)) {
	$AppUI->redirect(ACCESS_DENIED);
}

// setup the title block
$titleBlock = new w2p_Theme_TitleBlock(($object_id ? 'Edit Event' : 'Add Event'), 'icon.png', $m);
$titleBlock->addCrumb('?m=events&a=year_view&date=' . $start_date->format(FMT_TIMESTAMP_DATE), 'year view');
$titleBlock->addCrumb('?m=events&amp;date=' . $start_date->format(FMT_TIMESTAMP_DATE), 'month view');
$titleBlock->addCrumb('?m=events&a=week_view&date=' . $start_date->format(FMT_TIMESTAMP_DATE), 'week view');
$titleBlock->addCrumb('?m=events&amp;a=day_view&amp;date=' . $start_date->format(FMT_TIMESTAMP_DATE) . '&amp;tab=0', 'day view');
$titleBlock->addViewLink('event', $object_id);
$titleBlock->show();

// format dates
$df = $AppUI->getPref('SHDATEFORMAT');

// pull projects
$all_projects = '(' . $AppUI->_('All', UI_OUTPUT_RAW) . ')';

$prj = new CProject();
$projects = $prj->getAllowedProjects($AppUI->user_id);
foreach ($projects as $project_id => $project_info) {
	$projects[$project_id] = $project_info['project_name'];
}
$projects = arrayMerge(array(0 => $all_projects), $projects);

$inc = intval(w2PgetConfig('cal_day_increment')) ? intval(w2PgetConfig('cal_day_increment')) : 30;
if (!$object_id && !$is_clash) {

	$seldate = new w2p_Utilities_Date($date, $AppUI->getPref('TIMEZONE'));
	// If date is today, set start time to now + inc
	if ($date == date('Ymd')) {
		$h = date('H');
		// an interval after now.
		$min = intval(date('i') / $inc) + 1;
		$min *= $inc;
		if ($min > 60) {
			$min = 0;
			$h++;
		}
	}
	if ($h && $h < w2PgetConfig('cal_day_end')) {
		$seldate->setTime($h, $min, 0);
        $seldate->convertTZ('UTC');
		$object->event_start_date = $seldate->format(FMT_TIMESTAMP);
		$seldate->addSeconds($inc * 60);
        $seldate->convertTZ('UTC');
		$object->event_end_date = $seldate->format(FMT_TIMESTAMP);
	} else {
		$seldate->setTime(w2PgetConfig('cal_day_start'), 0, 0);
        $seldate->convertTZ('UTC');
		$object->event_start_date = $seldate->format(FMT_TIMESTAMP);
        $seldate->convertTZ($AppUI->getPref('TIMEZONE'));
		$seldate->setTime(w2PgetConfig('cal_day_end'), 0, 0);
        $seldate->convertTZ('UTC');
		$object->event_end_date = $seldate->format(FMT_TIMESTAMP);
	}
}

$recurs = array('Never', 'Hourly', 'Daily', 'Weekly', 'Bi-Weekly', 'Every Month', 'Quarterly', 'Every 6 months', 'Every Year');

$remind = array('900' => '15 mins', '1800' => '30 mins', '3600' => '1 hour', '7200' => '2 hours', '14400' => '4 hours', '28800' => '8 hours', '56600' => '16 hours', '86400' => '1 day', '172800' => '2 days');

// build array of times in 30 minute increments
$times = array();
$t = new w2p_Utilities_Date();
$t->setTime(0, 0, 0);
//$m clashes with global $m (module)
for ($minutes = 0; $minutes < ((24 * 60) / $inc); $minutes++) {
	$times[$t->format('%H%M%S')] = $t->format($AppUI->getPref('TIMEFORMAT'));
	$t->addSeconds($inc * 60);
}
?>
<script language="javascript" type="text/javascript">
function submitIt(){
	var form = document.editFrm;
	if (form.event_name.value.length < 1) {
		alert('<?php echo $AppUI->_('Please enter a valid event title', UI_OUTPUT_JS); ?>');
		form.event_name.focus();
		return;
	}
	if (form.event_start_date.value.length < 1){
		alert('<?php echo $AppUI->_('Please enter a start date', UI_OUTPUT_JS); ?>');
		form.event_start_date.focus();
		return;
	}
	if (form.event_end_date.value.length < 1){
		alert('<?php echo $AppUI->_('Please enter an end date', UI_OUTPUT_JS); ?>');
		form.event_end_date.focus();
		return;
	}
	if ( (!(form.event_times_recuring.value>0)) 
		&& (form.event_recurs[0].selected!=true) ) {
		alert("<?php echo $AppUI->_('Please enter number of recurrences', UI_OUTPUT_JS); ?>");
		form.event_times_recuring.value=1;
		form.event_times_recuring.focus();
		return;
	} 
	// Ensure that the assigned values are selected before submitting.
	var assigned = form.assigned;
	var len = assigned.length;
	var users = form.event_assigned;
	users.value = '';
	for (var i = 0; i < len; i++) {
		if (i) {
			users.value += ',';
		}
		users.value += assigned.options[i].value;
	}
	form.submit();
}

function addUser() {
	var form = document.editFrm;
	var fl = form.resources.length -1;
	var au = form.assigned.length -1;
	//gets value of percentage assignment of selected resource

	var users = 'x';

	//build array of assiged users
	for (au; au > -1; au--) {
		users = users + ',' + form.assigned.options[au].value + ','
	}

	//Pull selected resources and add them to list
	for (fl; fl > -1; fl--) {
		if (form.resources.options[fl].selected && users.indexOf( ',' + form.resources.options[fl].value + ',' ) == -1) {
			t = form.assigned.length
			opt = new Option( form.resources.options[fl].text, form.resources.options[fl].value);
			form.assigned.options[t] = opt
		}
	}
}

function removeUser() {
	var form = document.editFrm;
	fl = form.assigned.length -1;
	for (fl; fl > -1; fl--) {
		if (form.assigned.options[fl].selected) {
			//remove from hperc_assign
			var selValue = form.assigned.options[fl].value;			
			var re = ".*("+selValue+"=[0-9]*;).*";
			form.assigned.options[fl] = null;
		}
	}
}
</script>

<?php
include $AppUI->getTheme()->resolveTemplate('events/addedit');