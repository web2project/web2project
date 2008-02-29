<?php /* $Id$ $URL$ */
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}

$project_id = intval(w2PgetParam($_GET, 'project_id', 0));

// check permissions for this record
$perms = &$AppUI->acl();
$canRead = $perms->checkModuleItem($m, 'view', $project_id);
$canEdit = $perms->checkModuleItem($m, 'edit', $project_id);
$canEditT = $perms->checkModule('tasks', 'add');

if (!$canRead) {
	$AppUI->redirect('m=public&a=access_denied');
}

// retrieve any state parameters
if (isset($_GET['tab'])) {
	$AppUI->setState('ProjVwTab', w2PgetParam($_GET, 'tab', null));
}
$tab = $AppUI->getState('ProjVwTab') !== null ? $AppUI->getState('ProjVwTab') : 0;

// check if this record has dependencies to prevent deletion
$msg = '';
$obj = new CProject();
// Now check if the proect is editable/viewable.
$denied = $obj->getDeniedRecords($AppUI->user_id);
if (in_array($project_id, $denied)) {
	$AppUI->redirect('m=public&a=access_denied');
}

$canDelete = $obj->canDelete($msg, $project_id);

// get critical tasks (criteria: task_end_date)
$criticalTasks = ($project_id > 0) ? $obj->getCriticalTasks($project_id) : null;

// get ProjectPriority from sysvals
$projectPriority = w2PgetSysVal('ProjectPriority');
$projectPriorityColor = w2PgetSysVal('ProjectPriorityColor');

$working_hours = ($w2Pconfig['daily_working_hours'] ? $w2Pconfig['daily_working_hours'] : 8);

$q = new DBQuery;
//check that project has tasks; otherwise run seperate query
$q->addTable('tasks');
$q->addQuery('COUNT(distinct tasks.task_id) AS total_tasks');
$q->addWhere('task_project = ' . (int)$project_id);
$hasTasks = $q->loadResult();
$q->clear();

// load the record data
// GJB: Note that we have to special case duration type 24 and this refers to the hours in a day, NOT 24 hours
$obj = null;
if ($hasTasks) {
	$q->addTable('projects');
	$q->addQuery("company_name, CONCAT_WS(' ',contact_first_name,contact_last_name) user_name, " . 'projects.*,' . " SUM(t1.task_duration * t1.task_percent_complete" . " * IF(t1.task_duration_type = 24, {$working_hours}, t1.task_duration_type))" . " / SUM(t1.task_duration * IF(t1.task_duration_type = 24, {$working_hours}, t1.task_duration_type))" . " AS project_percent_complete");
	$q->addJoin('companies', 'com', 'company_id = project_company', 'inner');
	$q->leftJoin('users', 'u', 'user_id = project_owner');
	$q->leftJoin('contacts', 'con', 'contact_id = user_contact');
	$q->addJoin('tasks', 't1', 'projects.project_id = t1.task_project', 'inner');
	$q->addWhere('project_id = ' . (int)$project_id . ' AND t1.task_id = t1.task_parent');
	$q->addGroup('project_id');
	$q->loadObject($obj);
} else {
	$q->addTable('projects');
	$q->addQuery("company_name, CONCAT_WS(' ',contact_first_name,contact_last_name) user_name, " . 'projects.*, ' . "(0.0) AS project_percent_complete");
	$q->addJoin('companies', 'com', 'company_id = project_company', 'inner');
	$q->leftJoin('users', 'u', 'user_id = project_owner');
	$q->leftJoin('contacts', 'con', 'contact_id = user_contact');
	$q->addWhere('project_id = ' . (int)$project_id);
	$q->addGroup('project_id');
	$q->loadObject($obj);
}
$q->clear();

if (!$obj) {
	$AppUI->setMsg('Project');
	$AppUI->setMsg('invalidID', UI_MSG_ERROR, true);
	$AppUI->redirect();
} else {
	$AppUI->savePlace();
}

// worked hours
// now milestones are summed up, too, for consistence with the tasks duration sum
// the sums have to be rounded to prevent the sum form having many (unwanted) decimals because of the mysql floating point issue
// more info on http://www.mysql.com/doc/en/Problems_with_float.html
if ($hasTasks) {
	$q->addTable('task_log');
	$q->addTable('tasks');
	$q->addQuery('ROUND(SUM(task_log_hours),2)');
	$q->addWhere('task_log_task = task_id AND task_project = ' . (int)$project_id);
	$worked_hours = $q->loadResult();
	$q->clear();
	$worked_hours = rtrim($worked_hours, '.');

	// total hours
	// same milestone comment as above, also applies to dynamic tasks
	$q->addTable('tasks');
	$q->addQuery('ROUND(SUM(task_duration),2)');
	$q->addWhere('task_project = ' . (int)$project_id . ' AND task_duration_type = 24 AND task_dynamic != 1');
	$days = $q->loadResult();
	$q->clear();

	$q->addTable('tasks');
	$q->addQuery('ROUND(SUM(task_duration),2)');
	$q->addWhere('task_project = ' . (int)$project_id . ' AND task_duration_type = 1 AND task_dynamic != 1');
	$hours = $q->loadResult();
	$q->clear();
	$total_hours = $days * $w2Pconfig['daily_working_hours'] + $hours;

	$total_project_hours = 0;

	$q->addTable('tasks', 't');
	$q->addQuery('ROUND(SUM(t.task_duration*u.perc_assignment/100),2)');
	$q->addJoin('user_tasks', 'u', 't.task_id = u.task_id', 'inner');
	$q->addWhere('t.task_project = ' . (int)$project_id . ' AND t.task_duration_type = 24 AND t.task_dynamic != 1');
	$total_project_days = $q->loadResult();
	$q->clear();

	$q->addTable('tasks', 't');
	$q->addQuery('ROUND(SUM(t.task_duration*u.perc_assignment/100),2)');
	$q->addJoin('user_tasks', 'u', 't.task_id = u.task_id', 'inner');
	$q->addWhere('t.task_project = ' . (int)$project_id . ' AND t.task_duration_type = 1 AND t.task_dynamic != 1');
	$total_project_hours = $q->loadResult();
	$q->clear();

	$total_project_hours = $total_project_days * $w2Pconfig['daily_working_hours'] + $total_project_hours;
	//due to the round above, we don't want to print decimals unless they really exist
	//$total_project_hours = rtrim($total_project_hours, "0");
} else { //no tasks in project so "fake" project data
	$worked_hours = $total_hours = $total_project_hours = 0.00;
}
// get the prefered date format
$df = $AppUI->getPref('SHDATEFORMAT');

// create Date objects from the datetime fields
$start_date = intval($obj->project_start_date) ? new CDate($obj->project_start_date) : null;
$end_date = intval($obj->project_end_date) ? new CDate($obj->project_end_date) : null;
$actual_end_date = intval($criticalTasks[0]['task_end_date']) ? new CDate($criticalTasks[0]['task_end_date']) : null;
$style = (($actual_end_date > $end_date) && !empty($end_date)) ? 'style="color:red; font-weight:bold"' : '';

// setup the title block
$titleBlock = new CTitleBlock('View Project', 'applet3-48.png', $m, $m . '.' . $a);

// patch 2.12.04 text to search entry box
if (isset($_POST['searchtext'])) {
	$AppUI->setState('searchtext', $_POST['searchtext']);
}

$search_text = $AppUI->getState('searchtext') ? $AppUI->getState('searchtext') : '';
$titleBlock->addCell('&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;' . $AppUI->_('Search') . ':');
$titleBlock->addCell('<input type="text" class="text" SIZE="10" name="searchtext" onChange="document.searchfilter.submit();" value=' . "'$search_text'" . 'title="' . $AppUI->_('Search in name and description fields') . '"/>
       	<!--<input type="submit" class="button" value=">" title="' . $AppUI->_('Search in name and description fields') . '"/>-->', '', '<form action="?m=projects&a=view&project_id=' . $project_id . '" method="post" id="searchfilter">', '</form>');

if ($canEditT) {
	$titleBlock->addCell();
	$titleBlock->addCell('<input type="submit" class="button" value="' . $AppUI->_('new task') . '" />', '', '<form action="?m=tasks&a=addedit&task_project=' . $project_id . '" method="post">', '</form>');
}
if ($canEdit) {
	$titleBlock->addCell();
	$titleBlock->addCell('<input type="submit" class="button" value="' . $AppUI->_('new event') . '" />', '', '<form action="?m=calendar&a=addedit&event_project=' . $project_id . '" method="post">', '</form>');

	$titleBlock->addCell();
	$titleBlock->addCell('<input type="submit" class="button" value="' . $AppUI->_('new file') . '" />', '', '<form action="?m=files&a=addedit&project_id=' . $project_id . '" method="post">', '</form>');
}
$titleBlock->addCrumb('?m=projects', 'projects list');
if ($canEdit) {
	$titleBlock->addCrumb('?m=projects&a=addedit&project_id=' . $project_id, 'edit this project');
	if ($canDelete) {
		$titleBlock->addCrumbDelete('delete project', $canDelete, $msg);
	}
}
$titleBlock->show();
?>
<script language="javascript">
function expand_multiproject(id, table_name) {
      var trs = document.getElementsByTagName('tr');

      for (var i=0, i_cmp=trs.length;i < i_cmp;i++) {
          var tr_name = trs.item(i).id;

          if (tr_name.indexOf(id) >= 0) {
                 var tr = document.getElementById(tr_name);
                 tr.style.visibility = (tr.style.visibility == '' || tr.style.visibility == 'collapse') ? 'visible' : 'collapse';
                 var img_expand = document.getElementById(id+'_expand');
                 var img_collapse = document.getElementById(id+'_collapse');
                 img_collapse.style.display = (tr.style.visibility == 'visible') ? 'inline' : 'none';
                 img_expand.style.display = (tr.style.visibility == '' || tr.style.visibility == 'collapse') ? 'inline' : 'none';
          }
      }
}
<?php
// security improvement:
// some javascript functions may not appear on client side in case of user not having write permissions
// else users would be able to arbitrarily run 'bad' functions
if ($canEdit) {
?>
function delIt() {
	if (confirm( '<?php echo $AppUI->_('doDelete', UI_OUTPUT_JS) . ' ' . $AppUI->_('Project', UI_OUTPUT_JS) . '?'; ?>' )) {
		document.frmDelete.submit();
	}
}
<?php } ?>
</script>

<form name="frmDelete" action="./index.php?m=projects" method="post">
	<input type="hidden" name="dosql" value="do_project_aed" />
	<input type="hidden" name="del" value="1" />
	<input type="hidden" name="project_id" value="<?php echo $project_id; ?>" />
</form>
<table id="tblProjects" border="0" cellpadding="4" cellspacing="0" width="100%" class="std">
<tr>
	<td style="border: outset #d1d1cd 1px;background-color:#<?php echo $obj->project_color_identifier; ?>" colspan="2">
	<?php
echo '<font color="' . bestColor($obj->project_color_identifier) . '"><strong>' . $obj->project_name . '<strong></font>';
?>
	</td>
</tr>

<tr>
	<td width="50%" valign="top">
		<strong><?php echo $AppUI->_('Details'); ?></strong>
		<table cellspacing="1" cellpadding="2" border="0" width="100%">
		<tr>
			<td align="right" nowrap="nowrap"><?php echo $AppUI->_('Company'); ?>:</td>
			<?php if ($perms->checkModuleItem('companies', 'access', $obj->project_company)) { ?>
            			<td class="hilite" width="100%"> <?php echo '<a href="?m=companies&a=view&company_id=' . $obj->project_company . '">' . htmlspecialchars($obj->company_name, ENT_QUOTES) . '</a>'; ?></td>
			<?php } else { ?>
            			<td class="hilite" width="100%"><?php echo htmlspecialchars($obj->company_name, ENT_QUOTES); ?></td>
			<?php } ?>
		</tr>
		<tr>
			<td align="right" nowrap="nowrap"><?php echo $AppUI->_('Project Location'); ?>:</td>
			<td class="hilite"><?php echo @$obj->project_location; ?></td>
		</tr>
		<tr>
			<td align="right" nowrap="nowrap"><?php echo $AppUI->_('Short Name'); ?>:</td>
			<td class="hilite"><?php echo htmlspecialchars(@$obj->project_short_name, ENT_QUOTES); ?></td>
		</tr>
		<tr>
			<td align="right" nowrap="nowrap"><?php echo $AppUI->_('Start Date'); ?>:</td>
			<td class="hilite"><?php echo $start_date ? $start_date->format($df) : '-'; ?></td>
		</tr>
		<tr>
			<td align="right" nowrap="nowrap"><?php echo $AppUI->_('Target End Date'); ?>:</td>
			<td class="hilite"><?php echo $end_date ? $end_date->format($df) : '-'; ?></td>
		</tr>
		<tr>
			<td align="right" nowrap="nowrap"><?php echo $AppUI->_('Actual End Date'); ?>:</td>
			<td class="hilite">
             <?php if ($project_id > 0) { ?>
                    <?php echo $actual_end_date ? '<a href="?m=tasks&a=view&task_id=' . $criticalTasks[0]['task_id'] . '">' : ''; ?>
                    <?php echo $actual_end_date ? '<span ' . $style . '>' . $actual_end_date->format($df) . '</span>' : '-'; ?>
                    <?php echo $actual_end_date ? '</a>' : ''; ?>
             <?php } else {
						echo $AppUI->_('Dynamically calculated');
} ?>
            </td>
		</tr>
		<tr>
			<td align="right" nowrap="nowrap"><?php echo $AppUI->_('Target Budget'); ?>:</td>
			<td class="hilite"><?php echo $w2Pconfig['currency_symbol'] ?><?php echo @$obj->project_target_budget; ?></td>
		</tr>
		<tr>
			<td align="right" nowrap="nowrap"><?php echo $AppUI->_('Project Owner'); ?>:</td>
			<td class="hilite"><?php echo $obj->user_name; ?></td>
		</tr>
		<tr>
			<td align="right" nowrap="nowrap"><?php echo $AppUI->_('URL'); ?>:</td>
			<td class="hilite"><a href="<?php echo @$obj->project_url; ?>" target="_new"><?php echo @$obj->project_url; ?></a></td>
		</tr>
		<tr>
			<td align="right" nowrap="nowrap"><?php echo $AppUI->_('Staging URL'); ?>:</td>
			<td class="hilite"><a href="<?php echo @$obj->project_demo_url; ?>" target="_new"><?php echo @$obj->project_demo_url; ?></a></td>
		</tr>
		<tr>
			<td colspan="2">
			<?php
require_once ($AppUI->getSystemClass('CustomFields'));
$custom_fields = new CustomFields($m, $a, $obj->project_id, 'view');
$custom_fields->printHTML();
?>
			</td>
		</tr>
		<tr>
			<td colspan="2">
			<strong><?php echo $AppUI->_('Description'); ?></strong><br />
			<table cellspacing="0" cellpadding="2" border="0" width="100%">
			<tr>
				<td class="hilite">
					<?php echo str_replace(chr(10), '<br>', $obj->project_description); ?>&nbsp;
				</td>
			</tr>
			</table>
			</td>
		</tr>
		</table>
	</td>
	<td width="50%" rowspan="1" valign="top">
		<strong><?php echo $AppUI->_('Summary'); ?></strong><br />
		<table cellspacing="1" cellpadding="2" border="0" width="100%">
		<tr>
			<td align="right" nowrap="nowrap"><?php echo $AppUI->_('Status'); ?>:</td>
			<td class="hilite" width="100%"><?php echo $AppUI->_($pstatus[$obj->project_status]); ?></td>
		</tr>
		<tr>
			<td align="right" nowrap="nowrap"><?php echo $AppUI->_('Priority'); ?>:</td>
			<td class="hilite" width="100%" style="background-color:<?php echo $projectPriorityColor[$obj->project_priority] ?>"><?php echo $AppUI->_($projectPriority[$obj->project_priority]); ?></td>
		</tr>
		<tr>
			<td align="right" nowrap="nowrap"><?php echo $AppUI->_('Type'); ?>:</td>
			<td class="hilite" width="100%"><?php echo $AppUI->_($ptype[$obj->project_type]); ?></td>
		</tr>
		<tr>
			<td align="right" nowrap="nowrap"><?php echo $AppUI->_('Progress'); ?>:</td>
			<td class="hilite" width="100%"><?php printf('%.1f%%', $obj->project_percent_complete); ?></td>
		</tr>
		<tr>
			<td align="right" nowrap="nowrap"><?php echo $AppUI->_('Active'); ?>:</td>
			<td class="hilite" width="100%"><?php echo $obj->project_active ? $AppUI->_('Yes') : $AppUI->_('No'); ?></td>
		</tr>
		<tr>
			<td align="right" nowrap="nowrap"><?php echo $AppUI->_('Worked Hours'); ?>:</td>
			<td class="hilite" width="100%"><?php echo $worked_hours ?></td>
		</tr>	
		<tr>
			<td align="right" nowrap="nowrap"><?php echo $AppUI->_('Scheduled Hours'); ?>:</td>
			<td class="hilite" width="100%"><?php echo $total_hours ?></td>
		</tr>
		<tr>
			<td align="right" nowrap="nowrap"><?php echo $AppUI->_('Project Hours'); ?>:</td>
			<td class="hilite" width="100%"><?php echo $total_project_hours ?></td>
		</tr>				
		<?php
$q = new DBQuery;
$q->addTable('departments', 'a');
$q->addTable('project_departments', 'b');
$q->addQuery('a.dept_id, a.dept_name, a.dept_phone');
$q->addWhere('a.dept_id = b.department_id and b.project_id = ' . (int)$project_id);
$department = new CDepartment;
$department->setAllowedSQL($AppUI->user_id, $q);
$depts = $q->loadHashList('dept_id');
if (count($depts) > 0) {
?>
		    <tr>
		    	<td><strong><?php echo $AppUI->_('Departments'); ?></strong></td>
		    </tr>
		    <tr>
		    	<td colspan='3' class="hilite">
		    		<?php
	foreach ($depts as $dept_id => $dept_info) {
		echo '<div>' . $dept_info['dept_name'];
		if ($dept_info['dept_phone'] != '') {
			echo '( ' . $dept_info['dept_phone'] . ' )';
		}
		echo '</div>';
	}
?>
		    	</td>
		    </tr>
	 		<?php
}

$q = new DBQuery;
$q->addTable('contacts', 'a');
$q->addTable('project_contacts', 'b');
$q->addJoin('departments', 'c', 'a.contact_department = c.dept_id', 'left outer');
$q->addQuery('a.contact_id, a.contact_first_name, a.contact_last_name, a.contact_email, a.contact_phone, c.dept_name');
$q->addWhere('a.contact_id = b.contact_id and b.project_id = ' . (int)$project_id . ' AND (contact_owner = ' . (int)$AppUI->user_id . ' OR contact_private = 0)');
$department->setAllowedSQL($AppUI->user_id, $q);
$contacts = $q->loadHashList('contact_id');
if (count($contacts) > 0) {
?>
			    <tr>
			    	<td><strong><?php echo $AppUI->_('Contacts'); ?></strong></td>
			    </tr>
			    <tr>
			    	<td colspan='3' class="hilite">
			    		<?php
	echo '<table cellspacing="1" cellpadding="2" border="0" width="100%" class="tbl">';
	echo '<tr><th>' . $AppUI->_('Name') . '</th><th>' . $AppUI->_('Email') . '</th><th>' . $AppUI->_('Phone') . '</th><th>' . $AppUI->_('Department') . '</th></tr>';
	foreach ($contacts as $contact_id => $contact_data) {
		echo '<tr>';
		echo '<td class="hilite">';
		$canEdit = $perms->checkModuleItem('contacts', 'edit', $contact_id);
		if ($canEdit) {
			echo '<a href="index.php?m=contacts&a=view&contact_id=' . $contact_id . '">';
		}
		echo $contact_data['contact_first_name'] . ' ' . $contact_data['contact_last_name'];
		if ($canEdit) {
			echo '</a>';
		}
		echo '</td>';
		echo '<td class="hilite"><a href="mailto:' . $contact_data['contact_email'] . '">' . $contact_data['contact_email'] . '</a></td>';
		echo '<td class="hilite">' . $contact_data['contact_phone'] . '</td>';
		echo '<td class="hilite">' . $contact_data['dept_name'] . '</td>';
		echo '</tr>';
	}
	echo '</table>';
?>
			    	</td>
			    </tr>
			    <tr>
			    	<td>
		 <?php
} ?>
		</table>
	</td>
</tr>
<?php
//lets add the subprojects table
$q = new DBQuery();
$q->addTable('projects');
$q->addQuery('COUNT(project_id)');
$q->addWhere('project_original_parent = ' . (int)($obj->project_original_parent ? $obj->project_original_parent : $project_id));
$count_projects = $q->loadResult();
$canReadMultiProjects = $perms->checkModule('admin', 'view');
if ($count_projects > 1 && $canReadMultiProjects) {
?>
<tr>
	<td colspan="2">
	<?php
	echo w2PtoolTip('Multiproject', 'Click to Show/Hide Structure', true) . '<a href="#fp' . $row['project_id'] . '" onclick="expand_collapse(\'multiproject\', \'tblProjects\')"><img id="multiproject_expand" src="' . w2PfindImage('icons/expand.gif') . '" width="12" height="12" border="0"><img id="multiproject_collapse" src="' . w2PfindImage('icons/collapse.gif') . '" width="12" height="12" border="0" style="display:none"></a>&nbsp;' . w2PendTip();
	echo '<strong>' . $AppUI->_('This Project is Part of the Following Multi-Project Structure') . ':<strong>';
?>
	</td>
</tr>
<tr id="multiproject" style="visibility:collapse;display:none;">
	<td colspan="2" class="hilite">
	<?php
	require (w2PgetConfig('root_dir') . '/modules/projects/vw_sub_projects.php');
?>
	</td>
</tr>
<?php
}
//here finishes the subproject structure

?>
</table>

<?php
$tabBox = new CTabBox('?m=projects&a=view&project_id=' . $project_id, '', $tab);
$query_string = '?m=projects&a=view&project_id=' . $project_id;
// tabbed information boxes
// Note that we now control these based upon module requirements.
$canViewTask = $perms->checkModule('tasks', 'view');
$canViewTaskLog = $perms->checkModule('task_log', 'view');

if ($canViewTask) {
	$tabBox->add(W2P_BASE_DIR . '/modules/tasks/tasks', 'Tasks');
	$tabBox->add(W2P_BASE_DIR . '/modules/tasks/tasks', 'Tasks (Inactive)');
}
if ($perms->checkModule('forums', 'view'))
	$tabBox->add(W2P_BASE_DIR . '/modules/projects/vw_forums', 'Forums');
//if ($perms->checkModule('files', 'view'))
//	$tabBox->add( W2P_BASE_DIR.'/modules/projects/vw_files', 'Files' );
if ($canViewTask) {
	$tabBox->add(W2P_BASE_DIR . '/modules/tasks/viewgantt', 'Gantt Chart');
	if ($canViewTaskLog) {
		$tabBox->add(W2P_BASE_DIR . '/modules/projects/vw_logs', 'Task Logs');
	}
}
$f = 'all';
$min_view = true;

$tabBox->show();
?>