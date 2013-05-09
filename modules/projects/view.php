<?php
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}

$project_id = (int) w2PgetParam($_GET, 'project_id', 0);



$project = new CProject();
$project->project_id = $project_id;

$canEdit   = $project->canEdit();
$canRead   = $project->canView();
$canCreate = $project->canCreate();
$canAccess = $project->canAccess();
$canDelete = $project->canDelete();

if (!$canAccess || !$canRead) {
	$AppUI->redirect(ACCESS_DENIED);
}

$project->loadFull(null, $project_id);
if (!$project) {
	$AppUI->setMsg('Project');
	$AppUI->setMsg('invalidID', UI_MSG_ERROR, true);
	$AppUI->redirect();
} else {
	$AppUI->savePlace();
}

$tab = $AppUI->processIntState('ProjVwTab', $_GET, 'tab', 0);

//TODO: is this different from the above checks for some reason?
// Now check if the proect is editable/viewable.
$denied = $project->getDeniedRecords($AppUI->user_id);
if (in_array($project_id, $denied)) {
	$AppUI->redirect(ACCESS_DENIED);
}

// get ProjectPriority from sysvals
$projectPriority = w2PgetSysVal('ProjectPriority');
$projectPriorityColor = w2PgetSysVal('ProjectPriorityColor');
$billingCategory = w2PgetSysVal('BudgetCategory');

// get the prefered date format
$df = $AppUI->getPref('SHDATEFORMAT');

$criticalTasks = ($project_id > 0) ? $project->getCriticalTasks($project_id) : null;

// create Date objects from the datetime fields
$end_date = intval($project->project_end_date) ? new w2p_Utilities_Date($project->project_end_date) : null;
$actual_end_date = null;
if (isset($criticalTasks)) {
    $actual_end_date = intval($criticalTasks[0]['task_end_date']) ? new w2p_Utilities_Date($criticalTasks[0]['task_end_date']) : null;
}
$style = (($actual_end_date > $end_date) && !empty($end_date)) ? 'style="color:red; font-weight:bold"' : '';

// setup the title block
$titleBlock = new w2p_Theme_TitleBlock('View Project', 'applet3-48.png', $m, $m . '.' . $a);

$canEditT = canAdd('tasks');
if ($canEditT) {
	$titleBlock->addCell('<input type="submit" class="button btn btn-small dropdown-toggle" value="' . $AppUI->_('new task') . '" />', '', '<form action="?m=tasks&a=addedit&task_project=' . $project_id . '" method="post" accept-charset="utf-8">', '</form>');
}
if ($canEdit) {
	$titleBlock->addCell('<input type="submit" class="button btn btn-small dropdown-toggle" value="' . $AppUI->_('new event') . '" />', '', '<form action="?m=calendar&a=addedit&event_project=' . $project_id . '" method="post" accept-charset="utf-8">', '</form>');

	$titleBlock->addCell('<input type="submit" class="button btn btn-small dropdown-toggle" value="' . $AppUI->_('new file') . '" />', '', '<form action="?m=files&a=addedit&project_id=' . $project_id . '" method="post" accept-charset="utf-8">', '</form>');
}
$titleBlock->addCrumb('?m=projects', 'projects list');
if ($canEdit) {
	$titleBlock->addCrumb('?m=projects&a=addedit&project_id=' . $project_id, 'edit this project');
	if ($canDelete) {
		$titleBlock->addCrumbDelete('delete project', $canDelete);
	}
}
$titleBlock->show();

$htmlHelper = new w2p_Output_HTMLHelper($AppUI);
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
<table id="tblProjects" border="0" cellpadding="4" cellspacing="0" width="100%" class="std view">
<tr>
	<td style="border: outset #d1d1cd 1px;background-color:#<?php echo $project->project_color_identifier; ?>" colspan="2" id="view-header">
	<?php
        echo '<font color="' . bestColor($project->project_color_identifier) . '"><strong>' . $project->project_name . '<strong></font>';
    ?>
	</td>
</tr>
<tr>
	<td width="50%" valign="top" class="view-column">
		<strong><?php echo $AppUI->_('Details'); ?></strong>
		<table cellspacing="1" cellpadding="2" border="0" width="100%">
		<tr>
			<td align="right" nowrap="nowrap"><?php echo $AppUI->_('Company'); ?>:</td>
            <?php
            $perms = &$AppUI->acl();
            if ($perms->checkModuleItem('companies', 'access', $project->project_company)) { ?>
                <td class="hilite" width="100%">
                    <?php echo '<a href="?m=companies&a=view&company_id=' . $project->project_company . '">' . htmlspecialchars($project->company_name, ENT_QUOTES) . '</a>'; ?>
                </td>
            <?php } else { ?>
                <?php echo $htmlHelper->createCell('company_name', $project->company_name); ?>
            <?php } ?>
		</tr>
		<tr>
			<td align="right" nowrap="nowrap"><?php echo $AppUI->_('Project Location'); ?>:</td>
            <?php echo $htmlHelper->createCell('project_location', $project->project_location); ?>
		</tr>
		<tr>
			<td align="right" nowrap="nowrap"><?php echo $AppUI->_('Short Name'); ?>:</td>
            <?php

            // TODO Need to rename field to avoid confusing HTMLhelper
            echo $htmlHelper->createCell('project_shortname', $project->project_short_name);
            ?>
		</tr>
		<tr>
			<td align="right" nowrap="nowrap"><?php echo $AppUI->_('Start Date'); ?>:</td>
            <?php echo $htmlHelper->createCell('project_start_date', $project->project_start_date); ?>
		</tr>
		<tr>
			<td align="right" nowrap="nowrap"><?php echo $AppUI->_('Target End Date'); ?>:</td>
            <?php echo $htmlHelper->createCell('project_end_date', $project->project_end_date); ?>
		</tr>
		<tr>
			<td align="right" nowrap="nowrap"><?php echo $AppUI->_('Actual End Date'); ?>:</td>
			<td class="hilite">
				<?php
					if ($project_id > 0 && $project->project_last_task > 0) {
						echo $actual_end_date ? '<a href="?m=tasks&a=view&task_id='. $project->project_last_task . '">' : '';
						echo $actual_end_date ? '<span ' . $style . '>' . $actual_end_date->format($df) . '</span>' : '-';
						echo $actual_end_date ? '</a>' : '';
					} else {
						echo $AppUI->_('Dynamically calculated');
					}
				?>
			</td>
		</tr>
		<tr>
			<td align="right" nowrap="nowrap"><?php echo $AppUI->_('Project Owner'); ?>:</td>
            <td class="hilite">
                <?php
                $pusername = $project->user_name;
                $puserid = $project->project_owner;

                //TODO HTML helper not working properly due to field having suffix _owner, avoiding helper until fix
                echo "<a href=\"?m=contacts&a=view&contact_id=$puserid\" alt=\"$pusername\">$pusername</a>";
                ?>
            </td>
		</tr>
		<tr>
			<td align="right" nowrap="nowrap"><?php echo $AppUI->_('URL'); ?>:</td>
            <?php echo $htmlHelper->createCell('project_url', $project->project_url); ?>
		</tr>
		<tr>
			<td align="right" nowrap="nowrap"><?php echo $AppUI->_('Staging URL'); ?>:</td>
            <?php echo $htmlHelper->createCell('project_demo_url', $project->project_demo_url); ?>
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
                <?php echo $htmlHelper->createCell('project_description', $project->project_description); ?>
			</tr>
			</table>
			</td>
		</tr>
		</table>
	</td>
    <td width="50%" valign="top" rowspan="1" class="view-column">
            <strong><?php echo $AppUI->_('Summary'); ?></strong><br />
            <table cellspacing="1" cellpadding="2" border="0" width="100%">
                <tr>
                    <td align="right" nowrap="nowrap"><?php echo $AppUI->_('Status'); ?>:</td>
                    <?php echo $htmlHelper->createCell('project_status', $AppUI->_($pstatus[$project->project_status])); ?>
                </tr>
                <tr>
                    <td align="right" nowrap="nowrap"><?php echo $AppUI->_('Type'); ?>:</td>
                    <?php echo $htmlHelper->createCell('project_type', $AppUI->_($ptype[$project->project_type])); ?>
                </tr>
                <tr>
                    <td align="right" nowrap="nowrap"><?php echo $AppUI->_('Priority'); ?>:</td>
                    <td class="hilite" width="100%" style="background-color:<?php echo $projectPriorityColor[$project->project_priority] ?>"><?php echo $AppUI->_($projectPriority[$project->project_priority]); ?></td>
                </tr>
                <tr>
                    <td align="right" nowrap="nowrap"><?php echo $AppUI->_('Progress'); ?>:</td>
<!-- TODO: we can't use the createCell helper here because it centers things while we need it left-aligned -->
                    <td class="hilite" width="100%"><?php printf('%.1f%%', $project->project_percent_complete); ?></td>
                </tr>
                <tr>
                    <td align="right" nowrap="nowrap"><?php echo $AppUI->_('Active'); ?>:</td>
                    <td class="hilite" width="100%"><?php echo $project->project_active ? $AppUI->_('Yes') : $AppUI->_('No'); ?></td>
                </tr>
                <tr>
                    <td align="right" nowrap="nowrap"><?php echo $AppUI->_('Scheduled Hours'); ?>:</td>
                    <?php echo $htmlHelper->createCell('total_hours', $project->project_scheduled_hours); ?>
                </tr>
                <tr>
                    <td align="right" nowrap="nowrap"><?php echo $AppUI->_('Worked Hours'); ?>:</td>
                    <?php echo $htmlHelper->createCell('project_worked_hours', $project->project_worked_hours); ?>
                </tr>
                <?php if (w2PgetConfig('budget_info_display', false)) { ?>
                <tr>
                    <td align="center" nowrap="nowrap"><?php echo $AppUI->_('Finances'); ?>:</td>
                    <td align="center" nowrap="nowrap">
                        <table cellspacing="1" cellpadding="2" border="0" width="100%">
                            <tr>
                                <td class="hilite" align="center">
                                    <?php echo $AppUI->_('Target Budgets'); ?>:
                                </td>
                                <td class="hilite" align="center">
                                    <?php echo $AppUI->_('Actual Costs'); ?>:
                                </td>
                            </tr>
                            <tr>
                                <td class="hilite">
                                    <table cellspacing="1" cellpadding="2" border="0" width="100%">
                                        <?php
                                        $totalBudget = 0;
                                        foreach ($billingCategory as $id => $category) {
                                            $amount = 0;
                                            if (isset($project->budget[$id])) {
                                                $amount = $project->budget[$id]['budget_amount'];
                                            }
                                            $totalBudget += $amount;
                                            ?>
                                            <tr>
                                                <td align="right" nowrap="nowrap">
                                                    <?php echo $AppUI->_($category); ?>
                                                </td>
                                                <td nowrap="nowrap" style="text-align: right; padding-left: 40px;">
                                                    <?php echo $w2Pconfig['currency_symbol'] ?>&nbsp;
                                                    <?php echo formatCurrency($amount, $AppUI->getPref('CURRENCYFORM')); ?>
                                                </td>
                                            </tr>
                                            <?php
                                        }
                                        ?>
                                        <tr>
                                            <td align="right" nowrap="nowrap">&nbsp;</td>
                                            <td align="right" nowrap="nowrap">&nbsp;</td>
                                        </tr>
                                        <tr>
                                            <td align="right" nowrap="nowrap">
                                                <?php echo $AppUI->_('Total Budget'); ?>
                                            </td>
                                            <td nowrap="nowrap" style="text-align: right; padding-left: 40px;">
                                                <?php echo $w2Pconfig['currency_symbol'] ?>&nbsp;
                                                <?php echo formatCurrency($totalBudget, $AppUI->getPref('CURRENCYFORM')); ?>
                                            </td>
                                        </tr>
                                    </table>
                                </td>
                                <td class="hilite">
                                    <table cellspacing="1" cellpadding="2" border="0" width="100%">
                                        <?php
                                        $bcode = new CSystem_Bcode();
                                        $results = $bcode->calculateProjectCost($project_id);
                                        foreach ($billingCategory as $id => $category) {
                                            ?>
                                            <tr>
                                                <td align="right" nowrap="nowrap">
                                                    <?php echo $AppUI->_($category); ?>
                                                </td>
                                                <td nowrap="nowrap" style="text-align: right; padding-left: 40px;">
                                                    <?php echo $w2Pconfig['currency_symbol'] ?>&nbsp;
                                                    <?php
                                                    $amount = 0;
                                                    if (isset($results[$id])) {
                                                        $amount = $results[$id];
                                                    }
                                                    echo formatCurrency($amount, $AppUI->getPref('CURRENCYFORM'));
                                                    ?>
                                                </td>
                                            </tr>
                                            <?php
                                        }
                                        ?>
                                        <tr>
                                            <td align="right" nowrap="nowrap">
                                                <?php echo $AppUI->_('Unidentified Costs'); ?>
                                            </td>
                                            <td nowrap="nowrap" style="text-align: right; padding-left: 40px;">
                                                <?php echo $w2Pconfig['currency_symbol'] ?>&nbsp;
                                                <?php 
                                                $otherCosts = 0;
                                                if (isset($results['otherCosts'])) {
                                                    $otherCosts = $results['otherCosts'];
                                                }
                                                echo formatCurrency($otherCosts, $AppUI->getPref('CURRENCYFORM'));
                                                ?>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td align="right" nowrap="nowrap">
                                                <?php echo $AppUI->_('Total Cost'); ?>
                                            </td>
                                            <td nowrap="nowrap" style="text-align: right; padding-left: 40px;">
                                                <?php echo $w2Pconfig['currency_symbol'] ?>&nbsp;
                                                <?php
                                                $totalCosts = 0;
                                                if (isset($results['totalCosts'])) {
                                                    $totalCosts = $results['totalCosts'];
                                                }
                                                echo formatCurrency($totalCosts, $AppUI->getPref('CURRENCYFORM'));
                                                ?>
                                            </td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>
                            <?php if (isset($results['uncountedHours']) && $results['uncountedHours']) { ?>
                            <tr>
                                <td colspan="2" align="center" class="hilite">
                                    <?php echo '<span style="float:right; font-style: italic;">'.$results['uncountedHours'].' hours without billing codes</span>'; ?>
                                </td>
                            </tr>
                            <?php } ?>
                        </table>
                    </td>
                </tr>
                <?php } ?>
                <?php
                $depts = $project->getDepartmentList();

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

                $contacts = $project->getContactList();
                if (count($contacts)) {
                    echo '<tr><td colspan="3"><strong>' . $AppUI->_('Project Contacts') . '</strong></td></tr>';
                    echo '<tr><td colspan="3" class="hilite">';
                    echo $htmlHelper->renderContactTable('projects', $contacts);
                    echo '</td></tr>';
                }
                ?>
                </table>
            </td>
        </tr>
        <?php
        //lets add the subprojects table
        $canReadMultiProjects = canView('admin');
        if ($project->hasChildProjects($project_id) && $canReadMultiProjects) { ?>
            <tr>
                <td colspan="2">
                    <?php
                        echo w2PtoolTip('Multiproject', 'Click to Show/Hide Structure', true) . '<a href="javascript: void(0);" onclick="expand_collapse(\'multiproject\', \'tblProjects\')"><img id="multiproject_expand" src="' . w2PfindImage('icons/expand.gif') . '" width="12" height="12" border="0" alt=""><img id="multiproject_collapse" src="' . w2PfindImage('icons/collapse.gif') . '" width="12" height="12" border="0" style="display:none"></a>&nbsp;' . w2PendTip();
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

//TODO: This whole structure is hard-coded based on the TaskStatus SelectList.
$status = w2PgetSysVal('TaskStatus');
if ($canViewTask) {
	$tabBox->add(W2P_BASE_DIR . '/modules/tasks/tasks', 'Tasks');
    unset($status[0]);
    $tabBox->add(W2P_BASE_DIR . '/modules/tasks/tasks', 'Tasks (Inactive)');
    unset($status[-1]);

    foreach ($status as $id => $statusName) {
        $tabBox->add(W2P_BASE_DIR . '/modules/tasks/tasks', $AppUI->_('Tasks') . ' (' . $AppUI->_($statusName) . ')');
    }
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
