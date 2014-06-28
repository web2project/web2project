<?php
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}
// @todo    convert to template
// @todo    remove database query

global $AppUI, $obj, $can_edit_time_information, $cal_sdf, $m;

$percent = array(0 => '0', 5 => '5', 10 => '10', 15 => '15', 20 => '20', 25 => '25', 30 => '30', 35 => '35', 40 => '40', 45 => '45', 50 => '50', 55 => '55', 60 => '60', 65 => '65', 70 => '70', 75 => '75', 80 => '80', 85 => '85', 90 => '90', 95 => '95', 100 => '100');

$task        = $obj;
$task_id     = $task->task_id;
$task_log_id = (int) w2PgetParam($_GET, 'task_log_id', 0);

$log = new CTask_Log();
$log->load($task_log_id);

$canAuthor = $log->canCreate();
if (!$canAuthor && !$task_log_id) {
	$AppUI->redirect(ACCESS_DENIED);
}

$canEdit = $log->canEdit();
if ($task_log_id && !$canEdit) {
	$AppUI->redirect(ACCESS_DENIED);
}

$AppUI->getTheme()->loadCalendarJS();

// check permissions
$perms = &$AppUI->acl();
$canEditTask = $perms->checkModuleItem('tasks', 'edit', $obj->task_id);
$canViewTask = $perms->checkModuleItem('tasks', 'view', $obj->task_id);

if ($task_log_id) {
	if (!$canEdit || !$canViewTask) {
		$AppUI->redirect(ACCESS_DENIED);
	}
	$log->load($task_log_id);
} else {
	if (!$canAuthor || !$canViewTask) {
		$AppUI->redirect(ACCESS_DENIED);
	}
	$log->task_log_task = $obj->task_id;
	$log->task_log_name = $obj->task_name;
}

$project = new CProject();
$project->load($obj->task_project);

$bcode = new CSystem_Bcode();
$companyBC = $bcode->getBillingCodes($project->project_company);
$neutralBC = $bcode->getBillingCodes(0);
$taskLogReference = w2PgetSysVal('TaskLogReference');
$billingCategory = w2PgetSysVal('BudgetCategory');
// Task Update Form
$df = $AppUI->getPref('SHDATEFORMAT');
$log_date = new w2p_Utilities_Date($log->task_log_date);

$tl = $AppUI->getPref('TASKLOGEMAIL');
$ta = $tl & 1;
$tt = $tl & 2;
$tp = $tl & 4;

$task_email_title = array();
$q = new w2p_Database_Query();
$q->addTable('task_contacts', 'tc');
$q->addJoin('contacts', 'c', 'c.contact_id = tc.contact_id', 'inner');
$q->addWhere('tc.task_id = ' . (int)$obj->task_id);
$q->addQuery('tc.contact_id');
$q->addQuery('c.contact_first_name, c.contact_last_name');
$req = &$q->exec();
$cidtc = array();
for ($req; !$req->EOF; $req->MoveNext()) {
    $cidtc[] = $req->fields['contact_id'];
    $task_email_title[] = $req->fields['contact_first_name'] . ' ' . $req->fields['contact_last_name'];
}

$q->clear();
$q->addTable('project_contacts', 'pc');
$q->addJoin('contacts', 'c', 'c.contact_id = pc.contact_id', 'inner');
$q->addWhere('pc.project_id = ' . (int)$obj->task_project);
$q->addQuery('pc.contact_id');
$q->addQuery('c.contact_first_name, c.contact_last_name');
$req = &$q->exec();
$cidpc = array();
$proj_email_title = array();
for ($req; !$req->EOF; $req->MoveNext()) {
    if (!in_array($req->fields['contact_id'], $cidpc)) {
        $cidpc[] = $req->fields['contact_id'];
        $proj_email_title[] = $req->fields['contact_first_name'] . ' ' . $req->fields['contact_last_name'];
    }
}
$q->clear();
?>

<!-- TIMER RELATED SCRIPTS -->
<script language="javascript" type="text/javascript">
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
       total_minutes++;

	   document.getElementById('timerStatus').innerHTML = '( '+total_minutes+' <?php echo $AppUI->_('minutes elapsed'); ?> )';

	   // Lets round hours to two decimals
	   var total_hours   = Math.round( (total_minutes / 60) * 100) / 100;
	   document.editFrm.task_log_hours.value = total_hours;

	   timerID = setTimeout('UpdateTimer()', 60000);
	}

	function timerStart() {
		if(!timerID){ // this means that it needs to be started
			timerSet();
			button = document.getElementById('timerStartStopButton');
			button.innerHTML = '<?php echo $AppUI->_('Stop'); ?>';
            UpdateTimer();
		} else { // timer must be stoped
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
            total_minutes--;
        }
	}

	function timerReset() {
		document.editFrm.task_log_hours.value = '0.00';
        total_minutes = -1;
	}

	function timerSet() {
		total_minutes = Math.round(document.editFrm.task_log_hours.value * 60) -1;
	}
</script>
<!-- END OF TIMER RELATED SCRIPTS -->

<a name="log"></a>
<form name="editFrm" action="?m=<?php echo $m; ?>&amp;a=view&amp;task_id=<?php echo $obj->task_id; ?>" method="post"
    onsubmit="updateEmailContacts();" accept-charset="utf-8" class="addedit tasks-tasklog">
	<input type="hidden" name="uniqueid" value="<?php echo uniqid(''); ?>" />
	<input type="hidden" name="dosql" value="do_updatetask" />
	<input type="hidden" name="task_log_id" value="<?php echo $log->task_log_id; ?>" />
	<input type="hidden" name="task_log_task" value="<?php echo $log->task_log_task; ?>" />
	<input type="hidden" name="task_log_name" value="Update :<?php echo $log->task_log_name; ?>" />
    <input type="hidden" name="task_log_record_creator" value="<?php echo (0 == $task_log_id ? $AppUI->user_id : $log->task_log_record_creator); ?>" />
    <input type="hidden" name="datePicker" value="task" />

    <div class="addedit tasks-tasklog">
        <div class="column left">
            <p>
                <label><?php echo $AppUI->_('Date'); ?>:</label>
                <input type="hidden" name="task_log_date" id="task_log_date" value="<?php echo $log_date ? $log_date->format(FMT_TIMESTAMP_DATE) : ''; ?>" />
                <input type="text" name="log_date" id="log_date" onchange="setDate_new('editFrm', 'log_date');" value="<?php echo $log_date ? $log_date->format($df) : ''; ?>" class="text" />
                <a href="javascript: void(0);" onclick="return showCalendar('log_date', '<?php echo $df ?>', 'editFrm', null, true, true)">
                    <img src="<?php echo w2PfindImage('calendar.gif'); ?>" alt="<?php echo $AppUI->_('Calendar'); ?>" />
                </a>
            </p>
            <p>
                <label><?php echo ($canEditTask ? $AppUI->_('Progress') : ''); ?>:</label>
                <?php
                echo ($canEditTask ? arraySelect($percent, 'task_percent_complete', 'size="1" class="text"', $obj->task_percent_complete) . '%' : '<input type="hidden" name="task_percent_complete" value="0" />');
                ?>
            </p>
            <p>
                <label><?php echo $AppUI->_('Hours Worked'); ?>:</label>
                <input type="text" style="text-align:right;" class="text" name="task_log_hours" value="<?php echo $log->task_log_hours; ?>" maxlength="8" size="4" />
                <a class="button" href="#" onclick="javascript:timerStart()"><span id="timerStartStopButton"><?php echo $AppUI->_('Start'); ?></span></a>
                <a class="button" href="#" onclick="javascript:timerReset()"><span id="timerResetButton"><?php echo $AppUI->_('Reset'); ?></span></a>
                <span id='timerStatus'></span>
            </p>
            <?php if ($obj->task_owner != $AppUI->user_id) { ?>
            <p>
                <label><?php echo $AppUI->_('Notify creator'); ?>:</label>
                <input type="checkbox" name="task_log_notify_owner" id="task_log_notify_owner" />
            </p>
            <?php } ?>
            <?php if (!$task->task_allow_other_user_tasklogs) { ?>
                <input type="hidden" name="task_log_creator" value="<?php echo ($log->task_log_creator == 0 ? $AppUI->user_id : $log->task_log_creator); ?>" />
            <?php } else { ?>
                <?php ($obj->task_log_creator == 0) ? $user_id = $AppUI->user_id : $user_id = $obj->task_log_creator; ?>
            <p>
                <label><?php echo $AppUI->_('User'); ?>:</label>
                <?php
                //TODO: update for arraySelect()
                foreach ($task->assignees($task_id) as $task_user) {
                    $task_user['user_id'] == $user_id ? $selected = 'selected="selected"' : $selected = '';
                    ?>
                    <option <?php echo $selected; ?> value="<?php echo $task_user['user_id']; ?>"><?php echo $task_user['contact_first_name'] . ' ' . $task_user['contact_last_name']; ?></option>
                <?php
                }
                ?>
            </p>
            <?php } ?>
            <p>
                <label><?php echo $AppUI->_('Billing Code'); ?>:</label>
                <select name="task_log_costcode" id="task_log_costcode" size="1" class="text">
                    <option value="0"></option>
                    <?php
                    if (count($companyBC)) {
                        $myKeys = array_keys($companyBC);
                        echo '<optgroup label="'.$companyBC[$myKeys[0]]['company_name'].'" />';
                        foreach($companyBC as $bcode) {
                            echo '<option value="'.$bcode['billingcode_id'].'">'.$bcode['billingcode_name'];
                            echo ('' != $bcode['billingcode_category']) ? ' ('.$billingCategory[$bcode['billingcode_category']].')' : '';
                            echo '</option>';
                        }
                    }
                    if (count($neutralBC)) {
                        echo '<optgroup label="'.$AppUI->_('No company specified').'" />';
                        foreach($neutralBC as $bcode) {
                            echo '<option value="'.$bcode['billingcode_id'].'">'.$bcode['billingcode_name'];
                            echo ('' != $bcode['billingcode_category']) ? ' ('.$billingCategory[$bcode['billingcode_category']].')' : '';
                            echo '</option>';
                        }
                    }
                    ?>
                </select>
            </p>
            <?php
            if ($obj->canUserEditTimeInformation($project->project_owner, $AppUI->user_id) && $canEditTask) {
                $end_date = intval($obj->task_end_date) ? new w2p_Utilities_Date($obj->task_end_date) : null;
            ?>
            <p>
                <label><?php echo $AppUI->_('Task end date'); ?>:</label>
                <input type="hidden" name="task_end_date" id="task_end_date" value="<?php echo $end_date ? $end_date->format(FMT_TIMESTAMP_DATE) : ''; ?>" />
                <input type="text" name="end_date" id="end_date" onchange="setDate_new('editFrm', 'end_date', 'task');" value="<?php echo $end_date ? $end_date->format($df) : ''; ?>" class="text" />
                <a href="javascript: void(0);" onclick="return showCalendar('end_date', '<?php echo $df ?>', 'editFrm', null, true, true)">
                    <img src="<?php echo w2PfindImage('calendar.gif'); ?>" alt="<?php echo $AppUI->_('Calendar'); ?>" />
                </a>
            </p>
            <?php } ?>
            <p>
                <label><?php echo $AppUI->_('User Assigned to Log'); ?>:</label>
                <input type="checkbox" name="email_log_user" id="email_log_user" />
                <input type='hidden' name='email_others' id='email_others' value='' />
            </p>
            <p>
                <label><?php echo $AppUI->_('Task Assignees'); ?>:</label>
                <input type="checkbox" name="email_assignees" id="email_assignees" <?php echo ($ta ? 'checked="checked"' : '');?> />
                <input type="hidden" name="email_task_list" id="email_task_list" value="<?php echo implode(',', $cidtc);?>" />
            </p>
            <p>
                <label><?php echo $AppUI->_('Task Contacts'); ?>:</label>
                <input type="checkbox" onmouseover="window.status = '<?php echo addslashes(implode(',', $task_email_title)); ?>';" onmouseout="window.status = '';" name="email_task_contacts" id="email_task_contacts" <?php echo ($tt ? 'checked="checked"' : ''); ?> />
            </p>
        </div>
        <div class="column right">
            <p>
                <label><?php echo $AppUI->_('Summary'); ?>:</label>
                <input type="text" class="text" name="task_log_name" value="<?php echo htmlentities($log->task_log_name, ENT_COMPAT, 'UTF-8'); ?>" maxlength="255" size="30" />
            </p>
            <p>
                <label><?php echo $AppUI->_('Reference'); ?>:</label>
                <?php echo arraySelect($taskLogReference, 'task_log_reference', 'size="1" class="text"', $log->task_log_reference, true); ?>
            </p>
            <p>
                <label><?php echo $AppUI->_('URL'); ?>:</label>
                <input type="text" class="text" name="task_log_related_url" value="<?php echo ($log->task_log_related_url); ?>" size="50" maxlength="255" title="<?php echo $AppUI->_('Must in general be entered with protocol name, e.g. http://...'); ?>"/>
            </p>
            <p>
                <label><?php echo $AppUI->_('Description'); ?>:</label>
                <textarea name="task_log_description" class="textarea"><?php echo $log->task_log_description; ?></textarea>
            </p>
            <p>
                <label><?php echo $AppUI->_('Project Contacts'); ?>:</label>
                <input type="checkbox" onmouseover="window.status = '<?php echo addslashes(implode(',', $proj_email_title)); ?>';" onmouseout="window.status = '';" name="email_project_contacts" id="email_project_contacts" <?php echo ($tp ? 'checked="checked"' : ''); ?> />
                <input type="hidden" name="email_project_list" id="email_project_list" value="<?php echo implode(',', $cidpc); ?>" />
            </p>
            <p>
                <label><?php echo $AppUI->_('Extra Recipients'); ?>:</label>
                <input type="text" class="text" name="email_extras" maxlength="255" size="30" />
            </p>
            <p><input type="button" class="button btn btn-primary" value="<?php echo $AppUI->_('update task'); ?>" onclick="updateTask()" style="float: right;" /></p>
        </div>
    </div>
</form>
<script language="javascript" type="text/javascript">
    document.getElementById('task_log_costcode').value = <?php echo $log->task_log_costcode; ?>;
</script>
