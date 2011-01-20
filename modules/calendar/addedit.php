<?php /* $Id: addedit.php 1499 2010-11-27 22:45:12Z caseydk $ $URL: https://web2project.svn.sourceforge.net/svnroot/web2project/trunk/modules/calendar/addedit.php $ */
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}
global $AppUI, $cal_sdf;
$AppUI->loadCalendarJS();

$event_id = intval(w2PgetParam($_GET, 'event_id', 0));
$is_clash = isset($_SESSION['event_is_clash']) ? $_SESSION['event_is_clash'] : false;

$perms = &$AppUI->acl();
$canAuthor = canAdd('calendar');
$canEdit = $perms->checkModuleItem('calendar', 'edit', $event_id);

// check permissions
if (!$canAuthor && !$event_id) {
	$AppUI->redirect('m=public&a=access_denied');
}

if (!$canEdit && $event_id) {
	$AppUI->redirect('m=public&a=access_denied');
}

// get the passed timestamp (today if none)
$date = w2PgetParam($_GET, 'date', null);

// load the record data
$obj = new CEvent();

if ($is_clash) {
	$obj->bind($_SESSION['add_event_post']);
} else {
	if (!$obj->load($event_id) && $event_id) {
		$AppUI->setMsg('Event');
		$AppUI->setMsg('invalidID', UI_MSG_ERROR, true);
		$AppUI->redirect();
	}
}

// load the event types
$types = w2PgetSysVal('EventType');

// Load the users
$users = $perms->getPermittedUsers('calendar');

// Load the assignees
$assigned = array();
if ($is_clash) {
	$assignee_list = $_SESSION['add_event_attendees'];
	if (isset($assignee_list) && $assignee_list) {
		$event = new CEvent();
		$assigned = $event->getAssigneeList($assignee_list);
	}
	// Now that we have loaded the possible replacement event,  remove the stored
	// details, NOTE: This could cause using a back button to make things break,
	// but that is the least of our problems.
    unset($_SESSION['add_event_post']);
    unset($_SESSION['add_event_attendees']);
    unset($_SESSION['add_event_mail']);
    unset($_SESSION['add_event_clash']);
    unset($_SESSION['event_is_clash']);
} else {
	if ($event_id == 0) {
		$assigned[$AppUI->user_id] = $AppUI->user_first_name . ' ' . $AppUI->user_last_name;
	} else {
		$assigned = $obj->getAssigned();
	}
}

$obj->event_project = (int) w2PgetParam($_GET, 'event_project', 0);

//check if the user has view permission over the project
if ($obj->event_project && !$perms->checkModuleItem('projects', 'view', $obj->event_project)) {
	$AppUI->redirect('m=public&a=access_denied');
}

// setup the title block
$titleBlock = new CTitleBlock(($event_id ? 'Edit Event' : 'Add Event'), 'myevo-appointments.png', $m, $m . '.' . $a);
$titleBlock->addCrumb('?m=calendar', 'month view');
if ($event_id) {
	$titleBlock->addCrumb('?m=calendar&amp;a=view&event_id=' . $event_id, 'view this event');
}
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

if ($event_id || $is_clash) {
	$start_date = intval($obj->event_start_date) ? new w2p_Utilities_Date($obj->event_start_date) : null;
	$end_date = intval($obj->event_end_date) ? new w2p_Utilities_Date($obj->event_end_date) : $start_date;
} else {
	$start_date = new w2p_Utilities_Date($date);
	$start_date->setTime(8, 0, 0);
	$end_date = new w2p_Utilities_Date($date);
	$end_date->setTime(17, 0, 0);
}

$inc = intval(w2PgetConfig('cal_day_increment')) ? intval(w2PgetConfig('cal_day_increment')) : 30;
if (!$event_id && !$is_clash) {

	$seldate = new w2p_Utilities_Date($date);
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
		$obj->event_start_date = $seldate->format(FMT_TIMESTAMP);
		$seldate->addSeconds($inc * 60);
		$obj->event_end_date = $seldate->format(FMT_TIMESTAMP);
	} else {
		$seldate->setTime(w2PgetConfig('cal_day_start'), 0, 0);
		$obj->event_start_date = $seldate->format(FMT_TIMESTAMP);
		$seldate->setTime(w2PgetConfig('cal_day_end'), 0, 0);
		$obj->event_end_date = $seldate->format(FMT_TIMESTAMP);
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
	if (form.event_title.value.length < 1) {
		alert('<?php echo $AppUI->_('Please enter a valid event title', UI_OUTPUT_JS); ?>');
		form.event_title.focus();
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

function setDate( frm_name, f_date ) {
	fld_date = eval( 'document.' + frm_name + '.' + f_date );
	fld_real_date = eval( 'document.' + frm_name + '.' + 'event_' + f_date );
	if (fld_date.value.length>0) {
      if ((parseDate(fld_date.value))==null) {
            alert('The Date/Time you typed does not match your prefered format, please retype.');
            fld_real_date.value = '';
            fld_date.style.backgroundColor = 'red';
        } else {
        	fld_real_date.value = formatDate(parseDate(fld_date.value), 'yyyyMMdd');
        	fld_date.value = formatDate(parseDate(fld_date.value), "<?php echo $cal_sdf ?>");
            fld_date.style.backgroundColor = '';
  		}
	} else {
      	fld_real_date.value = '';
	}
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
<table border="0" cellpadding="4" cellspacing="0" width="100%" class="std">
<tr>
	<td colspan="2">
		<form name="editFrm" action="?m=calendar" method="post" accept-charset="utf-8">
			<input type="hidden" name="dosql" value="do_event_aed" />
			<input type="hidden" name="event_id" value="<?php echo $event_id; ?>" />
			<input type="hidden" name="event_assigned" value="" />
            <table width="100%" border="0" cellpadding="1" cellspacing="1">
                <tr>
                    <td width="20%" align="right" nowrap="nowrap"><?php echo $AppUI->_('Event Title'); ?>:</td>
                    <td width="20%">
                        <input type="text" class="text" size="25" name="event_title" value="<?php echo $obj->event_title; ?>" maxlength="255" />
                    </td>
                    <td align="left" rowspan="4" valign="top" colspan="2" width="40%">
                        <?php echo $AppUI->_('Description'); ?> :<br/>
                        <textarea class="textarea" name="event_description" rows="5" cols="45"><?php echo $obj->event_description; ?></textarea>
                    </td>
                </tr>
                <tr>
                    <td align="right"><?php echo $AppUI->_('Type'); ?>:</td>
                    <td>
                        <?php
                        echo arraySelect($types, 'event_type', 'size="1" class="text"', $obj->event_type, true);
                        ?>
                    </td>
                </tr>
                <tr>
                    <td align="right"><?php echo $AppUI->_('Project'); ?>:</td>
                    <td>
                        <?php
                        echo arraySelect($projects, 'event_project', 'size="1" class="text"', ($obj->event_project ? $obj->event_project : 0));
                        ?>
                    </td>
                </tr>
                <tr>
                    <td align="right" nowrap="nowrap"><label for="event_private"><?php echo $AppUI->_('Private Entry'); ?>:</label></td>
                    <td>
                        <input type="checkbox" value="1" name="event_private" id="event_private" <?php echo ($obj->event_private ? 'checked="checked"' : ''); ?> />
                    </td>
                </tr>
                <tr>
                    <td align="right" nowrap="nowrap"><?php echo $AppUI->_('Start Date'); ?>:</td>
                    <td nowrap="nowrap">
                        <input type="hidden" name="event_start_date" id="event_start_date" value="<?php echo $start_date ? $start_date->format(FMT_TIMESTAMP_DATE) : ''; ?>" />
                        <input type="text" name="start_date" id="start_date" onchange="setDate('editFrm', 'start_date');" value="<?php echo $start_date ? $start_date->format($df) : ''; ?>" class="text" />
                        <a href="javascript: void(0);" onclick="return showCalendar('start_date', '<?php echo $df ?>', 'editFrm', null, true)">
                            <img src="<?php echo w2PfindImage('calendar.gif'); ?>" width="24" height="12" alt="<?php echo $AppUI->_('Calendar'); ?>" border="0" />
                        </a>
                    </td>
                    <td align="right" nowrap="nowrap"><?php echo $AppUI->_('Time'); ?>:</td>
                    <td>
                        <?php echo arraySelect($times, 'start_time', 'size="1" class="text"', $AppUI->formatTZAwareTime($obj->event_start_date, '%H%M%S')); ?>
                    </td>
                </tr>

                <tr>
                    <td align="right" nowrap="nowrap"><?php echo $AppUI->_('End Date'); ?>:</td>
                    <td nowrap="nowrap">
                        <input type="hidden" name="event_end_date" id="event_end_date" value="<?php echo $end_date ? $end_date->format(FMT_TIMESTAMP_DATE) : ''; ?>" />
                        <input type="text" name="end_date" id="end_date" onchange="setDate('editFrm', 'end_date');" value="<?php echo $end_date ? $end_date->format($df) : ''; ?>" class="text" />
                        <a href="javascript: void(0);" onclick="return showCalendar('end_date', '<?php echo $df ?>', 'editFrm', null, true)">
                            <img src="<?php echo w2PfindImage('calendar.gif'); ?>" width="24" height="12" alt="<?php echo $AppUI->_('Calendar'); ?>" border="0" />
                        </a>
                    </td>
                    <td align="right" nowrap="nowrap"><?php echo $AppUI->_('Time'); ?>:</td>
                    <td><?php echo arraySelect($times, 'end_time', 'size="1" class="text"', $AppUI->formatTZAwareTime($obj->event_end_date, '%H%M%S')); ?></td>
                </tr>
                <tr>
                    <td align="right" nowrap="nowrap"><?php echo $AppUI->_('Recurs'); ?>:</td>
                    <td><?php echo arraySelect($recurs, 'event_recurs', 'size="1" class="text"', $obj->event_recurs, true); ?></td>
                    <td align="right">x</td>
                    <td>
                        <input type="text" class="text" name="event_times_recuring" value="<?php echo ((isset($obj->event_times_recuring)) ? ($obj->event_times_recuring) : '1'); ?>" maxlength="2" size="3" /> <?php echo $AppUI->_('times'); ?>
                    </td>
                </tr>
                <tr>
                    <td align="right"><?php echo $AppUI->_('Resources'); ?>:</td>
                    <td></td>
                    <td align="left"><?php echo $AppUI->_('Invited to Event'); ?>:</td>
                    <td></td>
                </tr>
                <tr>
                    <td width="50%" colspan="2" align="right">
                    <?php echo arraySelect($users, 'resources', 'style="width:220px" size="10" class="text" multiple="multiple" ', null); ?>
                    </td>
                    <td width="50%" colspan="2" align="left">
                    <?php echo arraySelect($assigned, 'assigned', 'style="width:220px" size="10" class="text" multiple="multiple" ', null); ?>
                    </td>
                </tr>
                <tr>
                    <td width="50%" colspan="2" align="right">
                        <input type="button" class="button" value="&gt;" onclick="addUser()" />
                    </td>
                    <td width="50%" colspan="2" align="left">
                        <input type="button" class="button" value="&lt;" onclick="removeUser()" />
                    </td>
                </tr>
                <tr>
                    <td align="right" nowrap="nowrap"><label for="event_cwd"><?php echo $AppUI->_('Show only on Working Days'); ?>:</label></td>
                    <td>
                        <input type="checkbox" value="1" name="event_cwd" id="event_cwd" <?php echo ($obj->event_cwd ? 'checked="checked"' : ''); ?> />
                    </td>
                    <td align="right"><label for="mail_invited"><?php echo $AppUI->_('Mail Attendees?'); ?></label> <input type="checkbox" name="mail_invited" id="mail_invited" checked="checked" /></td>
                </tr>
                <tr>
                    <td colspan="2" align="right">
                        <?php
                          // $m does not equal 'calendar' here???
                          $custom_fields = new w2p_Core_CustomFields('calendar', 'addedit', $obj->event_id, 'edit');
                          $custom_fields->printHTML();
                          ?>
                    </td>
                <tr>
                    <td colspan="2">
                        <input type="button" value="<?php echo $AppUI->_('back'); ?>" class="button" onclick="javascript:history.back();" />
                    </td>
                    <td align="right" colspan="2">
                        <input type="button" value="<?php echo $AppUI->_('submit'); ?>" class="button" onclick="submitIt()" />
                    </td>
                </tr>
            </table>
        </form>
	</td>
</tr>
</table>