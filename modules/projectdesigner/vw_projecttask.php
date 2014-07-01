<?php
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}

global $project_id;

$project_statuses = w2PgetSysVal('ProjectStatus');
$project_types = w2PgetSysVal('ProjectType');
$customLookups = array('project_status' => $pstatus, 'project_type' => $ptype);

$params = get_object_vars($obj);

$htmlHelper = new w2p_Output_HTMLHelper($AppUI);
$htmlHelper->stageRowData($params);
?>	
<table width="100%" border="0" cellpadding="1" cellspacing="3" class="prjprint">
<tr>
	<td width="50%" valign="top">
		<strong><?php echo $AppUI->_('Details'); ?></strong>
		<table cellspacing="1" cellpadding="2" border="0" width="100%">
		<tr>
			<td align="right" nowrap="nowrap"><strong><?php echo $AppUI->_('Project Name'); ?>:&nbsp;</strong></td>
            <?php echo $htmlHelper->createCell('project_name', $obj->project_name); ?>
		</tr>
		<tr>
			<td align="right" nowrap="nowrap"><?php echo $AppUI->_('Company'); ?>:</td>
            <?php echo $htmlHelper->createCell('company_name', $obj->company_name); ?>
		</tr>
		<tr>
			<td align="right" nowrap="nowrap"><?php echo $AppUI->_('Short Name'); ?>:</td>
            <?php echo $htmlHelper->createCell('project_short_name', $obj->project_short_name); ?>
		</tr>
		<tr>
			<td align="right" nowrap="nowrap"><?php echo $AppUI->_('Start Date'); ?>:</td>
            <?php echo $htmlHelper->createCell('project_start_date', $obj->project_start_date); ?>
		</tr>
		<tr>
			<td align="right" nowrap="nowrap"><strong><?php echo $AppUI->_('Target End Date'); ?>:&nbsp;</strong></td>
            <?php echo $htmlHelper->createCell('project_start_date', $obj->project_start_date); ?>
		</tr>
		<tr>
			<td align="right" nowrap="nowrap"><strong><?php echo $AppUI->_('Status'); ?>:&nbsp;</strong></td>
            <?php echo $htmlHelper->createCell('project_status', $obj->project_status, $customLookups); ?>
		</tr>
		<tr>
			<td align="right" nowrap="nowrap"><strong><?php echo $AppUI->_('Progress'); ?>:&nbsp;</strong></td>
            <?php echo $htmlHelper->createCell('project_percent_complete', $obj->project_percent_complete); ?>
		</tr>
<?php

$module = new w2p_System_Module();
$fields = $module->loadSettings('projectdesigner', 'task_list_print');

if (0 == count($fields)) {
    // TODO: This is only in place to provide an pre-upgrade-safe 
    //   state for versions earlier than v3.0
    //   At some point at/after v4.0, this should be deprecated
    $fieldList = array('task_name', 'task_percent_complete', 'task_owner', 'task_start_date', 'task_duration', 'task_end_date');
    $fieldNames = array('Task Name', 'Work', 'Owner', 'Start', 'Duration', 'Finish');

    $module->storeSettings('projectdesigner', 'task_list_print', $fieldList, $fieldNames);
    $fields = array_combine($fieldList, $fieldNames);
}

$taskobj = new CTask();
$taskTree = $taskobj->getTaskTree($project_id);

$listTable = new w2p_Output_HTML_TaskTable($AppUI);

echo $listTable->startTable();
echo $listTable->buildHeader($fields);
echo $listTable->buildRows($taskTree);
echo $listTable->endTable();

$df = $AppUI->getPref('SHDATEFORMAT');
$projectPriority = w2PgetSysVal('ProjectPriority');
$projectStatus = w2PgetSysVal('ProjectStatus');
?>
<table class="tbl" cellspacing="1" cellpadding="2" border="0" width="100%">
    <tr>
        <td align="center">
            <?php echo '<strong>Gantt Chart</strong>' ?>
        </td>
    </tr>
    <tr>
        <td align="center" colspan="20">
            <?php
            $src = "?m=tasks&a=gantt&suppressHeaders=1&showLabels=1&proFilter=&showInactive=1showAllGantt=1&project_id=$project_id&width=' + ((navigator.appName=='Netscape'?window.innerWidth:document.body.offsetWidth)*0.90) + '";
            echo "<script language=\"javascript\" type=\"text/javascript\">document.write('<img src=\"$src\">')</script>";
            ?>
        </td>
    </tr>
</table>