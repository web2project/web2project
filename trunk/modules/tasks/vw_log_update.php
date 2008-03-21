<?php /* $Id$ $URL$ */
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}

global $AppUI, $task_id, $obj, $percent, $can_edit_time_information, $cal_sdf;
$AppUI->loadCalendarJS();

$perms = &$AppUI->acl();

// check permissions
if (!$perms->checkModuleItem('tasks', 'edit', $task_id)) {
	$AppUI->redirect('m=publice&a=access_denied');
}

$canViewTask = $perms->checkModuleItem('tasks', 'view', $task_id);
$canEdit = $perms->checkModule('task_log', 'edit');
$canAdd = $perms->checkModule('task_log', 'add');

$task_log_id = intval(w2PgetParam($_GET, 'task_log_id', 0));
$log = new CTaskLog();
if ($task_log_id) {
	if (!$canEdit || !$canViewTask) {
		$AppUI->redirect('m=public&a=access_denied');
	}
	$log->load($task_log_id);
} else {
	if (!$canAdd || !$canViewTask) {
		$AppUI->redirect('m=public&a=access_denied');
	}
	$log->task_log_task = $task_id;
	$log->task_log_name = $obj->task_name;
}

// Check that the user is at least assigned to a task
$task = new CTask;
$task->load($task_id);
if (!$task->canAccess($AppUI->user_id)) {
	$AppUI->redirect('m=public&a=access_denied');
}

$proj = &new CProject();
$proj->load($obj->task_project);
$q = new DBQuery();
$q->addTable('billingcode');
$q->addQuery('billingcode_id, billingcode_name');
$q->addWhere('billingcode_status=0');
$q->addWhere('(company_id=' . $proj->project_company . ' OR company_id = 0)');
$q->addOrder('billingcode_name');

$task_log_costcodes[0] = '';
$rows = $q->loadList();
echo db_error();
$nums = 0;
foreach ($rows as $key => $row) {
	$task_log_costcodes[$row['billingcode_id']] = $row['billingcode_name'];
}

$taskLogReference = w2PgetSysVal('TaskLogReference');

// Task Update Form
$df = $AppUI->getPref('SHDATEFORMAT');
$log_date = new CDate($log->task_log_date);
?>

<!-- TIMER RELATED SCRIPTS -->
<script language="JavaScript">
	// please keep these lines on when you copy the source
	// made by: Nicolas - http://www.javascript-page.com
	// adapted by: Juan Carlos Gonzalez jcgonz@users.sourceforge.net
	
	var timerID       = 0;
	var tStart        = null;
    var total_minutes = -1;
	
	function UpdateTimer() {
	   if(timerID) {
	      clearTimeout(timerID);
	      clockID  = 0;
	   }
	
       // One minute has passed
       total_minutes = total_minutes+1;
	   
	   document.getElementById('timerStatus').innerHTML = '( '+total_minutes+' <?php echo $AppUI->_('minutes elapsed'); ?> )';

	   // Lets round hours to two decimals
	   var total_hours   = Math.round( (total_minutes / 60) * 100) / 100;
	   document.editFrm.task_log_hours.value = total_hours;
	   
	   timerID = setTimeout('UpdateTimer()', 60000);
	}
	
	function timerStart() {
		if(!timerID){ // this means that it needs to be started
			timerSet();
			//document.editFrm.timerStartStopButton.value = '<?php echo $AppUI->_('Stop'); ?>';
			button = document.getElementById('timerStartStopButton');
			button.innerHTML = '<?php echo $AppUI->_('Stop'); ?>';
            UpdateTimer();
		} else { // timer must be stoped
			//document.editFrm.timerStartStopButton.value = '<?php echo $AppUI->_('Start'); ?>';
			button = document.getElementById('timerStartStopButton');
			button.innerHTML = '<?php echo $AppUI->_('Start'); ?>';
			document.getElementById('timerStatus').innerHTML = '';
			timerStop();
		}
	}
	
	function timerStop() {
	   if(timerID) {
	      clearTimeout(timerID);
	      timerID  = 0;
          total_minutes = total_minutes-1;
	   }
	}
	
	function timerReset() {
		document.editFrm.task_log_hours.value = '0.00';
        total_minutes = -1;
	}

	function timerSet() {
		total_minutes = Math.round(document.editFrm.task_log_hours.value * 60) -1;
	}

function setDate( frm_name, f_date ) {
	fld_date = eval( 'document.' + frm_name + '.' + f_date );
	fld_real_date = eval( 'document.' + frm_name + '.' + 'task_' + f_date );
	if (fld_date.value.length>0) {
      if ((parseDate(fld_date.value))==null) {
            alert('The Date/Time you typed does not match your prefered format, please retype.');
            fld_real_date.value = '';
            fld_date.style.backgroundColor = 'red';
        } else {
        	fld_real_date.value = formatDate(parseDate(fld_date.value), 'yyyyMMdd');
        	fld_date.value = formatDate(parseDate(fld_date.value), '<?php echo $cal_sdf ?>');
            fld_date.style.backgroundColor = '';
  		}
	} else {
      	fld_real_date.value = '';
	}
}	
</script>
<!-- END OF TIMER RELATED SCRIPTS -->

<a name="log"></a>
<form name="editFrm" action="?m=tasks&a=view&task_id=<?php echo $task_id; ?>" method="post"
  onsubmit='updateEmailContacts();'>
	<input type="hidden" name="uniqueid" value="<?php echo uniqid(''); ?>" />
	<input type="hidden" name="dosql" value="do_updatetask" />
	<input type="hidden" name="task_log_id" value="<?php echo $log->task_log_id; ?>" />
	<input type="hidden" name="task_log_task" value="<?php echo $log->task_log_task; ?>" />
	<input type="hidden" name="task_log_creator" value="<?php if ($log->task_log_creator == 0)
	echo $AppUI->user_id;
else
	echo $log->task_log_creator; ?>" />
	<input type="hidden" name="task_log_name" value="Update :<?php echo $log->task_log_name; ?>" />
<table cellspacing="1" cellpadding="2" border="0" width="100%">
<tr>
    <td width='40%' valign='top'>
      <table width='100%'>
<tr>
	<td align="right">
		<?php echo $AppUI->_('Date'); ?>
	</td>
	<td nowrap="nowrap">
		<input type="hidden" name="task_log_date" id="task_log_date" value="<?php echo $log_date ? $log_date->format(FMT_TIMESTAMP_DATE) : ''; ?>" />
		<input type="text" name="log_date" id="log_date" onchange="setDate('editFrm', 'log_date');" value="<?php echo $log_date ? $log_date->format($df) : ""; ?>" class="text" />
		<a href="javascript: void(0);" onclick="return showCalendar('log_date', '<?php echo $df ?>', 'editFrm', null, true)">
			<img src="<?php echo w2PfindImage('calendar.gif'); ?>" width="24" height="12" alt="<?php echo $AppUI->_('Calendar'); ?>" border="0" />
		</a>
	</td>
</tr>
<tr>
	<td align="right"><?php echo $AppUI->_('Progress'); ?></td>
	<td>
		<table>
		   <tr>
		      <td>
<?php
echo arraySelect($percent, 'task_percent_complete', 'size="1" class="text"', $obj->task_percent_complete) . '%';
?>
		      </td>
		      <td valign="middle" >
			<?php
if ($obj->task_owner != $AppUI->user_id) {
	echo '<input type="checkbox" name="task_log_notify_owner" id="task_log_notify_owner" /></td><td valign="middle"><label for="task_log_notify_owner">' . $AppUI->_('Notify creator') . '</label>';
}
?>		 	
		     </td>
		   </tr>
		</table>
	</td>

</tr>
<tr>
	<td align="right">
		<?php echo $AppUI->_('Hours Worked'); ?>
	</td>
	<td nowrap="nowrap">
		<input type="text" style="text-align:right;" class="text" name="task_log_hours" value="<?php echo $log->task_log_hours; ?>" maxlength="8" size="4" /> 
		<a class="button" href="javascript:;" onclick="javascript:timerStart()"><span id="timerStartStopButton"><?php echo $AppUI->_('Start'); ?></span></a>
		<a class="button" href="javascript:;" onclick="javascript:timerReset()"><span id="timerResetButton"><?php echo $AppUI->_('Reset'); ?></span></a>
		<span id='timerStatus'></span>
	</td>
</tr>
<tr>
        <td align="right">
		<?php echo $AppUI->_('Cost Code'); ?>
	</td>
	<td>
<?php
echo arraySelect($task_log_costcodes, 'task_log_costcodes', 'size="1" class="text" onchange="javascript:task_log_costcode.value = this.options[this.selectedIndex].value;"', $log->task_log_costcode);
?>
		-><input type="text" style="text-align:right;" class="text" name="task_log_costcode" value="<?php echo $log->task_log_costcode; ?>" maxlength="8" size="6" />
	</td>
</tr>

<?php
if ($obj->canUserEditTimeInformation()) {
?>
	<tr>
		<td align='right'>
			<?php echo $AppUI->_("Task end date"); ?>
		</td>
		<td>
			<?php
	$end_date = intval($obj->task_end_date) ? new CDate($obj->task_end_date) : null;
?>
			<input type="hidden" name="task_end_date" id="task_end_date" value="<?php echo $end_date ? $end_date->format(FMT_TIMESTAMP_DATE) : ''; ?>" />
			<input type="text" name="end_date" id="end_date" onchange="setDate('editFrm', 'end_date');" value="<?php echo $end_date ? $end_date->format($df) : ""; ?>" class="text" />
			<a href="javascript: void(0);" onclick="return showCalendar('end_date', '<?php echo $df ?>', 'editFrm', null, true)">
				<img src="<?php echo w2PfindImage('calendar.gif'); ?>" width="24" height="12" alt="<?php echo $AppUI->_('Calendar'); ?>" border="0">
			</a>
		</td>
	</tr>
<?php
}
?>
</table>
</td>
<td width='60%' valign='top'>
<table width='100%'>
<tr>
	<td align="right"><?php echo $AppUI->_('Summary'); ?>:</td>
        <td valign="middle">
                <table width="100%">
                        <tr>
                                <td align="left">
                                        <input type="text" class="text" name="task_log_name" value="<?php echo $log->task_log_name; ?>" maxlength="255" size="30" />
                                </td>
                                <td align="center"><label for="task_log_problem"><?php echo $AppUI->_('Problem'); ?>:</label>
                                        <input type="checkbox" value="1" name="task_log_problem" id="task_log_problem" <?php if ($log->task_log_problem) {
	echo 'checked="checked"';
} ?> />
                                </td>
                        </tr>
                </table>
	</td>
</tr>
<tr>
        <td align="right" valign="middle"><?php echo $AppUI->_('Reference'); ?>:</td>
        <td valign="middle">
                <?php echo arraySelect($taskLogReference, 'task_log_reference', 'size="1" class="text"', $log->task_log_reference, true); ?>
	</td>
</tr>
<tr>
        <td align="right">
		<?php echo $AppUI->_('URL'); ?>:
	</td>
        <td>
                <input type="text" class="text" name="task_log_related_url" value="<?php echo ($log->task_log_related_url); ?>" size="50" maxlength="255" title="<?php echo $AppUI->_('Must in general be entered with protocol name, e.g. http://...'); ?>"/>
        </td>
</tr>
<tr>
	<td align="right" valign="top"><?php echo $AppUI->_('Description'); ?>:</td>
	<td>
		<textarea name="task_log_description" class="textarea" cols="50" rows="6"><?php echo $log->task_log_description; ?></textarea>
	</td>
</tr>
<tr>
	<td align="right" valign="top"><?php echo $AppUI->_('Email Log to'); ?>:</td>
	<td>
<?php
$tl = $AppUI->getPref('TASKLOGEMAIL');
$ta = $tl & 1;
$tt = $tl & 2;
$tp = $tl & 4;
?>
		<input type="checkbox" name="email_assignees" id="email_assignees" <?php
if ($ta) {
	echo 'checked="checked"';
}
?> /><label for="email_assignees"><?php echo $AppUI->_('Task Assignees'); ?></label>
		<input type="hidden" name="email_task_list" id="email_task_list"
		  value="<?php
$task_email_title = array();
$q = new DBQuery;
$q->addTable('task_contacts', 'tc');
$q->addJoin('contacts', 'c', 'c.contact_id = tc.contact_id', 'inner');
$q->addWhere('tc.task_id = ' . (int)$task_id);
$q->addQuery('tc.contact_id');
$q->addQuery('c.contact_first_name, c.contact_last_name');
$req = &$q->exec();
$cid = array();
for ($req; !$req->EOF; $req->MoveNext()) {
	$cid[] = $req->fields['contact_id'];
	$task_email_title[] = $req->fields['contact_first_name'] . ' ' . $req->fields['contact_last_name'];
}
echo implode(',', $cid);
?>" />
		<input type="checkbox" onmouseover="window.status = '<?php echo addslashes(implode(',', $task_email_title)); ?>';"
		onmouseout="window.status = '';"
		name="email_task_contacts" id="email_task_contacts" <?php
if ($tt) {
	echo 'checked="checked"';
}
?> /><label for="email_task_contacts"><?php echo $AppUI->_('Task Contacts'); ?></label>
		<input type="hidden" name="email_project_list" id="email_project_list"
		  value="<?php
$q->clear();
$q->addTable('project_contacts', 'pc');
$q->addJoin('contacts', 'c', 'c.contact_id = pc.contact_id', 'inner');
$q->addWhere('pc.project_id = ' . (int)$obj->task_project);
$q->addQuery('pc.contact_id');
$q->addQuery('c.contact_first_name, c.contact_last_name');
$req = &$q->exec();
$cid = array();
$proj_email_title = array();
for ($req; !$req->EOF; $req->MoveNext()) {
	if (!in_array($req->fields['contact_id'], $cid)) {
		$cid[] = $req->fields['contact_id'];
		$proj_email_title[] = $req->fields['contact_first_name'] . ' ' . $req->fields['contact_last_name'];
	}
}
echo implode(',', $cid);
$q->clear();
?>" />
		<input type="checkbox" onmouseover="window.status = '<?php echo addslashes(implode(',', $proj_email_title)); ?>';" 
		 onmouseout="window.status = '';"
		 name="email_project_contacts" id="email_project_contacts" <?php
if ($tp) {
	echo 'checked="checked"';
}
?> /><label for="email_project_contacts"><?php echo $AppUI->_('Project Contacts'); ?></label>
		<input type='hidden' name='email_others' id='email_others' value='' />
		<?php
if ($AppUI->isActiveModule('contacts') && $perms->checkModule('contacts', 'view')) {
?>
		<input type='button' class='button' value='<?php echo $AppUI->_('Other Contacts...'); ?>' onclick='javascript:popEmailContacts();' />

		<?php } ?>
	</td>
</tr>
<tr>
	<td align="right" valign="top"><?php echo $AppUI->_('Extra Recipients'); ?>:</td>
	<td>
		<input type="text" class="text" name="email_extras" maxlength="255" size="30" />
	</td>
</tr>
<tr>
	<td colspan="2" valign="bottom" align="right">
		<input type="button" class="button" value="<?php echo $AppUI->_('update task'); ?>" onclick="updateTask()" />
	</td>
</tr>
</td>
</table>
</td>
</tr>
</table>
</form>