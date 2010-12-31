<?php /* $Id$ $URL$ */
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}

$project_id = (int) w2PgetParam($_GET, 'project_id', 0);

// check permissions for this record
$perms = &$AppUI->acl();
$canRead = $perms->checkModuleItem($m, 'view', $project_id);
$canEdit = $perms->checkModuleItem($m, 'edit', $project_id);
$canEditT = canAdd('tasks');

if (!$canRead) {
	$AppUI->redirect('m=public&a=access_denied');
}

$tab = $AppUI->processIntState('ProjVwTab', $_GET, 'tab', 0);

// check if this record has dependencies to prevent deletion
$msg = '';
$project = new CProject();
// Now check if the proect is editable/viewable.
$denied = $project->getDeniedRecords($AppUI->user_id);
if (in_array($project_id, $denied)) {
	$AppUI->redirect('m=public&a=access_denied');
}

$canDelete = $project->canDelete($msg, $project_id);

// get critical tasks (criteria: task_end_date)
$criticalTasks = ($project_id > 0) ? $project->getCriticalTasks($project_id) : null;

// get ProjectPriority from sysvals
$projectPriority = w2PgetSysVal('ProjectPriority');
$projectPriorityColor = w2PgetSysVal('ProjectPriorityColor');

// load the record data
$project->loadFull($AppUI, $project_id);

if (!$project) {
	$AppUI->setMsg('Project');
	$AppUI->setMsg('invalidID', UI_MSG_ERROR, true);
	$AppUI->redirect();
} else {
	$AppUI->savePlace();
}

$total_project_hours = $total_hours = $project->getTotalProjectHours();

// get the prefered date format
$df = $AppUI->getPref('SHDATEFORMAT');

// create Date objects from the datetime fields
$start_date = intval($project->project_start_date) ? new CDate($project->project_start_date) : null;
$end_date = intval($project->project_end_date) ? new CDate($project->project_end_date) : null;
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
$titleBlock->addCell('<input type="text" class="text" SIZE="10" name="searchtext" onChange="document.searchfilter.submit();" value=' . "'$search_text'" . 'title="' . $AppUI->_('Search in name and description fields') . '"/>',
		'', '<form action="?m=projects&a=view&project_id=' . $project_id . '" method="post" id="searchfilter" accept-charset="utf-8">', '</form>');

if ($canEditT) {
	$titleBlock->addCell();
	$titleBlock->addCell('<input type="submit" class="button" value="' . $AppUI->_('new task') . '" />', '', '<form action="?m=tasks&a=addedit&task_project=' . $project_id . '" method="post" accept-charset="utf-8">', '</form>');
}
if ($canEdit) {
	$titleBlock->addCell();
	$titleBlock->addCell('<input type="submit" class="button" value="' . $AppUI->_('new event') . '" />', '', '<form action="?m=calendar&a=addedit&event_project=' . $project_id . '" method="post" accept-charset="utf-8">', '</form>');

	$titleBlock->addCell();
	$titleBlock->addCell('<input type="submit" class="button" value="' . $AppUI->_('new file') . '" />', '', '<form action="?m=files&a=addedit&project_id=' . $project_id . '" method="post" accept-charset="utf-8">', '</form>');
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
<script language="javascript" type="text/javascript">
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

<form name="frmDelete" action="./index.php?m=projects" method="post" accept-charset="utf-8">
	<input type="hidden" name="dosql" value="do_project_aed" />
	<input type="hidden" name="del" value="1" />
	<input type="hidden" name="project_id" value="<?php echo $project_id; ?>" />
</form>
<table id="tblProjects" border="0" cellpadding="4" cellspacing="0" width="100%" class="std">
<tr>
	<td style="border: outset #d1d1cd 1px;background-color:#<?php echo $project->project_color_identifier; ?>" colspan="2">
	<?php
echo '<font color="' . bestColor($project->project_color_identifier) . '"><strong>' . $project->project_name . '<strong></font>';
?>
	</td>
</tr>

<tr>
	<td width="50%" valign="top">
		<strong><?php echo $AppUI->_('Details'); ?></strong>
		<table cellspacing="1" cellpadding="2" border="0" width="100%">
		<tr>
			<td align="right" nowrap="nowrap"><?php echo $AppUI->_('Company'); ?>:</td>
			<td class="hilite" width="100%">
				<?php if ($perms->checkModuleItem('companies', 'access', $project->project_company)) { ?>
					<?php echo '<a href="?m=companies&a=view&company_id=' . $project->project_company . '">' . htmlspecialchars($project->company_name, ENT_QUOTES) . '</a>'; ?>
				<?php } else { ?>
	  			<?php echo htmlspecialchars($project->company_name, ENT_QUOTES); ?>
				<?php } ?>
			</td>
		</tr>
		<tr>
			<td align="right" nowrap="nowrap"><?php echo $AppUI->_('Project Location'); ?>:</td>
			<td class="hilite"><?php echo $project->project_location; ?></td>
		</tr>
		<tr>
			<td align="right" nowrap="nowrap"><?php echo $AppUI->_('Short Name'); ?>:</td>
			<td class="hilite"><?php echo htmlspecialchars($project->project_short_name, ENT_QUOTES); ?></td>
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
				<?php
					if ($project_id > 0) {
						echo $actual_end_date ? '<a href="?m=tasks&a=view&task_id=' . $criticalTasks[0]['task_id'] . '">' : '';
						echo $actual_end_date ? '<span ' . $style . '>' . $actual_end_date->format($df) . '</span>' : '-';
						echo $actual_end_date ? '</a>' : '';
					} else {
						echo $AppUI->_('Dynamically calculated');
					}
				?>
			</td>
		</tr>
		<tr>
			<td align="right" nowrap="nowrap"><?php echo $AppUI->_('Target Budget'); ?>:</td>
			<td class="hilite">
                <?php
                    echo $w2Pconfig['currency_symbol'];
                    echo formatCurrency($project->project_target_budget, $AppUI->getPref('CURRENCYFORM'));
                ?>
            </td>
		</tr>
		<tr>
			<td align="right" nowrap="nowrap"><?php echo $AppUI->_('Project Owner'); ?>:</td>
			<td class="hilite"><?php echo $project->user_name; ?></td>
		</tr>
		<tr>
			<td align="right" nowrap="nowrap"><?php echo $AppUI->_('URL'); ?>:</td>
      <td class="hilite"><?php echo w2p_url($project->project_url); ?></td>
		</tr>
		<tr>
			<td align="right" nowrap="nowrap"><?php echo $AppUI->_('Staging URL'); ?>:</td>
      <td class="hilite"><?php echo w2p_url($project->project_demo_url); ?></td>
		</tr>
		<tr>
			<td colspan="2">
				<?php
					$custom_fields = new w2p_Core_CustomFields($m, $a, $project->project_id, 'view');
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
					<?php echo w2p_textarea($project->project_description); ?>&nbsp;
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
			<td class="hilite" width="100%"><?php echo $AppUI->_($pstatus[$project->project_status]); ?></td>
		</tr>
		<tr>
			<td align="right" nowrap="nowrap"><?php echo $AppUI->_('Priority'); ?>:</td>
			<td class="hilite" width="100%" style="background-color:<?php echo $projectPriorityColor[$project->project_priority] ?>"><?php echo $AppUI->_($projectPriority[$project->project_priority]); ?></td>
		</tr>
		<tr>
			<td align="right" nowrap="nowrap"><?php echo $AppUI->_('Type'); ?>:</td>
			<td class="hilite" width="100%"><?php echo $AppUI->_($ptype[$project->project_type]); ?></td>
		</tr>
		<tr>
			<td align="right" nowrap="nowrap"><?php echo $AppUI->_('Progress'); ?>:</td>
			<td class="hilite" width="100%"><?php printf('%.1f%%', $project->project_percent_complete); ?></td>
		</tr>
		<tr>
			<td align="right" nowrap="nowrap"><?php echo $AppUI->_('Active'); ?>:</td>
			<td class="hilite" width="100%"><?php echo $project->project_active ? $AppUI->_('Yes') : $AppUI->_('No'); ?></td>
		</tr>
		<tr>
			<td align="right" nowrap="nowrap"><?php echo $AppUI->_('Worked Hours'); ?>:</td>
			<td class="hilite" width="100%"><?php echo $project->project_worked_hours; ?></td>
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
		$depts = CProject::getDepartments($AppUI, $project->project_id);

		if (count($depts) > 0) { ?>
		    <tr>
		    	<td><strong><?php echo $AppUI->_('Departments'); ?></strong></td>
		    </tr>
		    <tr>
		    	<td colspan='3' class="hilite">
		    		<?php
							foreach ($depts as $dept_id => $dept_info) {
								echo '<div>';
								echo '<a href="?m=departments&a=view&dept_id='.$dept_id.'">'.$dept_info['dept_name'].'</a>';
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
		$contacts = CProject::getContacts($AppUI, $project->project_id);
        if (count($contacts)) {
            echo '<tr><td><strong>' . $AppUI->_('Project Contacts') . '</strong></td></tr>';
            echo '<tr><td colspan="3" class="hilite">';
            echo w2p_Output_HTMLHelper::renderContactList($AppUI, $contacts);
            echo '</td></tr>';
        }
        ?>
		</table>
	</td>
</tr>
<?php
	//lets add the subprojects table
	$canReadMultiProjects = canView('admin');
	if ($project->hasChildProjects() && $canReadMultiProjects) { ?>
		<tr>
			<td colspan="2">
				<?php
					echo w2PtoolTip('Multiproject', 'Click to Show/Hide Structure', true) . '<a href="javascript: void(0);" onclick="expand_collapse(\'multiproject\', \'tblProjects\')"><img id="multiproject_expand" src="' . w2PfindImage('icons/expand.gif') . '" width="12" height="12" border="0"><img id="multiproject_collapse" src="' . w2PfindImage('icons/collapse.gif') . '" width="12" height="12" border="0" style="display:none"></a>&nbsp;' . w2PendTip();
					echo '<strong>' . $AppUI->_('This Project is Part of the Following Multi-Project Structure') . ':<strong>';
				?>
			</td>
		</tr>
		<tr id="multiproject" style="visibility:collapse;display:none;">
			<td colspan="2" class="hilite">
				<?php
					require W2P_BASE_DIR . '/modules/projects/vw_sub_projects.php';
				?>
			</td>
		</tr>
	<?php }
	//here finishes the subproject structure
?>
</table>

<?php
$tabBox = new CTabBox('?m=projects&a=view&project_id=' . $project_id, '', $tab);
$query_string = '?m=projects&a=view&project_id=' . $project_id;
// tabbed information boxes
// Note that we now control these based upon module requirements.
$canViewTask = canView('tasks');
$canViewTaskLog = canView('task_log');

if ($canViewTask) {
	$tabBox->add(W2P_BASE_DIR . '/modules/tasks/tasks', 'Tasks');
	$tabBox->add(W2P_BASE_DIR . '/modules/tasks/tasks', 'Tasks (Inactive)');
}
if ( $AppUI->isActiveModule('forums') ) { 
	if (canView('forums')) {
		$tabBox->add(W2P_BASE_DIR . '/modules/projects/vw_forums', 'Forums');
	}
}
if ($canViewTask) {
	$tabBox->add(W2P_BASE_DIR . '/modules/tasks/viewgantt', 'Gantt Chart');
	if ($canViewTaskLog) {
		$tabBox->add(W2P_BASE_DIR . '/modules/projects/vw_logs', 'Task Logs');
	}
}
$f = 'all';
$min_view = true;

$tabBox->show();