<?php
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}

$deleg_id = (int) w2PgetParam($_GET, 'delegation_id', 0);

$obj = new CDelegation();
$obj->load($deleg_id);
if (!$obj) {
	$AppUI->setMsg('Delegation');
	$AppUI->setMsg('invalidID', UI_MSG_ERROR, true);
	$AppUI->redirect();
} else {
	$AppUI->savePlace();
}

$canEdit   = $obj->canEdit();
$canRead   = $obj->canView();
$canAccess = $obj->canAccess();
$canDelete = $obj->canDelete();

if (!$canAccess || !$canRead) {
	$AppUI->redirect(ACCESS_DENIED);
}

// load the associated task and project's objects
$task = new CTask();
$task->load($obj->delegation_task);

$project = new CProject();
$project->load($obj->delegation_project);

//check permissions for the associated project
$canReadProject = canView('projects', $obj->delegation_project);

// setup the title block
$titleBlock = new w2p_Theme_TitleBlock('View Delegation', 'delegation.png', $m, $m . '.' . $a);
$titleBlock->addCell();
if ($canEdit) {
	$titleBlock->addCell('<input type="submit" class="button" value="' . $AppUI->_('new delegation') . '">', '', '<form action="?m=delegations&tab=0" method="post" accept-charset="utf-8">', '</form>');
}
$titleBlock->addCrumb('?m=delegations&tab=0', 'delegations list');
if ($canReadProject) {
	$titleBlock->addCrumb('?m=projects&a=view&project_id=' . $obj->delegation_project, 'view this project');
}
if (0 == $obj->task_represents_project) {
	$titleBlock->addCrumb('?m=tasks&a=view&task_id=' . $task->task_id, 'view this task');
} else {
    $titleBlock->addCrumb('?m=projects&a=view&project_id=' . $obj->task_represents_project, 'view subproject');
}
if ($canEdit) {
	$titleBlock->addCrumb('?m=delegations&a=addedit&delegation_id=' . $obj->delegation_id, 'edit this delegation');
}
if ($canDelete) {
	$titleBlock->addCrumbDelete('delete delegation', $canDelete, $msg);
}
$titleBlock->show();

$durnTypes = w2PgetSysVal('TaskDurationType');

$htmlHelper = new w2p_Output_HTMLHelper($AppUI);
$htmlHelper->stageRowData(array('task_id'=>$task->task_id));
?>

<script language="javascript" type="text/javascript">
<?php
// security improvement:
// some javascript functions may not appear on client side in case of user not having write permissions
// else users would be able to arbitrarily run 'bad' functions
$canDelete = canDelete('delegations');
if ($canDelete) {
?>
function delIt() {
	if (confirm( '<?php echo $AppUI->_('doDelete', UI_OUTPUT_JS) . ' ' . $AppUI->_('Delegation', UI_OUTPUT_JS) . '?'; ?>' )) {
		document.frmDelete.submit();
	}
}
<?php } ?>

</script>

<form name="frmDelete" action="./index.php?m=delegations" method="post" accept-charset="utf-8">
	<input type="hidden" name="dosql" value="do_bulkops_aed" />
	<input type="hidden" name="delegation_to_delete" value="<?php echo $deleg_id; ?>" />
	<input type="hidden" name="delegation_id" value="<?php echo $deleg_id; ?>" />
</form>

<table border="0" cellpadding="4" cellspacing="0" width="100%" class="std view">
    <tr>
        <td width="50%" valign="top" class="view-column">
            <table cellspacing="1" cellpadding="2" border="0" width="100%">
                <tr>
                    <td nowrap="nowrap" colspan="2"><strong><?php echo $AppUI->_('Details'); ?></strong></td>
                </tr>
                <tr>
                    <td align="right" nowrap="nowrap"><?php echo $AppUI->_('Project'); ?>:</td>
                    <td style="background-color:#<?php echo $project->project_color_identifier; ?>">
                        <font color="<?php echo bestColor($project->project_color_identifier); ?>">
                            <?php
                            $perms = &$AppUI->acl();
                            if ($perms->checkModuleItem('projects', 'access', $obj->delegation_project)) { ?>
                                <?php echo "<a href='?m=projects&a=view&project_id=" . $obj->delegation_project . "'>" . htmlspecialchars($project->project_name, ENT_QUOTES) . '</a>'; ?>
                            <?php } else { ?>
                                <?php echo htmlspecialchars($project->project_name, ENT_QUOTES); ?>
                            <?php } ?>
                        </font>
                    </td>
                </tr>
                <tr>
                    <td align="right" nowrap="nowrap"><?php echo $AppUI->_('Name'); ?>:</t>
                    <td class="hilite" width="300"><strong><?php echo $obj->delegation_name; ?></strong></td>
                </tr>
                <tr>
                    <td align="right" nowrap="nowrap"><?php echo $AppUI->_('Description'); ?>:</t>
                    <?php echo $htmlHelper->createCell('delegation_description', $obj->delegation_description); ?>
                </tr>
                <tr>
                    <td align="right" nowrap="nowrap"><?php echo $AppUI->_('Delegated From User'); ?>:</t>
                    <?php echo $htmlHelper->createCell('delegated_from_updator', $obj->delegating_user_id); ?>
                </tr>
                <tr>
                    <td align="right" nowrap="nowrap"><?php echo $AppUI->_('Delegated To User'); ?>:</t>
                    <?php echo $htmlHelper->createCell('delegated_to_updator', $obj->delegated_to_user_id); ?>
                </tr>
                <tr>
                    <td align="right" nowrap="nowrap"><?php echo $AppUI->_('Delegation Created By'); ?>:</t>
                    <?php echo $htmlHelper->createCell('delegation_creator', $obj->delegation_creator); ?>
                </tr>
                <tr>
                    <td align="right" nowrap="nowrap"><?php echo $AppUI->_('Delegation Created In'); ?>:</t>
                    <?php echo $htmlHelper->createCell('delegation_start_datetime', $obj->delegation_created); ?>
                </tr>
                <tr>
                    <td align="right" nowrap="nowrap"><?php echo $AppUI->_('Delegation Start Date'); ?>:</td>
                    <?php echo $htmlHelper->createCell('delegation_start_datetime', $obj->delegation_start_date); ?>
                </tr>
                <tr>
                    <td align="right" nowrap="nowrap"><?php echo $AppUI->_('Progress'); ?>:</td>
                    <td class="hilite" width="300"><?php echo ($obj->delegation_percent_complete) ? $obj->delegation_percent_complete : 0; ?>%</td>
                </tr>
		<?php if (!empty($obj->delegation_end_date)) { ?>
                <tr>
                    <td align="right" nowrap="nowrap"><?php echo $AppUI->_('Delegation End Date'); ?>:</td>
                    <?php echo $htmlHelper->createCell('delegation_end_datetime', $obj->delegation_end_date); ?>
                </tr>
		<?php } ?>
		<?php if (!empty($obj->delegation_rejection_date)) { ?>
                <tr>
                    <td align="right" nowrap="nowrap"><?php echo $AppUI->_('Delegation Rejected In'); ?>:</t>
                    <?php echo $htmlHelper->createCell('delegation_start_datetime', $obj->delegation_rejection_date); ?>
                </tr>
                <tr>
                    <td align="right" nowrap="nowrap"><?php echo $AppUI->_('Rejection Reason'); ?>:</t>
                    <?php echo $htmlHelper->createCell('delegation_description', $obj->delegation_rejection_reason); ?>
                </tr>
			<?php if (!empty($obj->delegation_rejection_valdation_date)) { ?>
	                <tr>
        	            <td align="right" nowrap="nowrap"><?php echo $AppUI->_('Delegation Rejection Validated In'); ?>:</t>
                	    <?php echo $htmlHelper->createCell('delegation_start_datetime', $obj->delegation_rejection_validation_date); ?>
	                </tr>
			<?php } ?>
		<?php } ?>
            </table>
        </td>
        <td width="50%" valign="top" class="view-column">
            <table width="100%" cellspacing="1" cellpadding="2">
                <tr>
                    <td nowrap="nowrap" colspan="2"><strong><?php echo $AppUI->_('Task'); ?></strong></td>
                </tr>
                <tr>
                    <td align="right" nowrap="nowrap"><?php echo $AppUI->_('Name'); ?>:</td>
                    <?php echo $htmlHelper->createCell('task_name', $task->task_name); ?>
                </tr>
                <tr>
                    <td align="right" nowrap="nowrap"><?php echo $AppUI->_('Description'); ?>:</td>
                    <?php echo $htmlHelper->createCell('task_description', $task->task_description); ?>
                </tr>
                <tr>
                    <td align="right" nowrap="nowrap"><?php echo $AppUI->_('Progress'); ?>:</td>
                    <td class="hilite" width="300"><?php echo ($task->task_percent_complete) ? $task->task_percent_complete : 0; ?>%</td>
                </tr>
                <tr>
                    <td align="right" nowrap="nowrap"><?php echo $AppUI->_('Time Worked'); ?>:</td>
                    <?php echo $htmlHelper->createCell('task_hours_worked', $task->task_hours_worked . ' h'); ?>
                </tr>
                <tr>
                    <td align="right" nowrap="nowrap"><?php echo $AppUI->_('Start Date'); ?>:</td>
                    <?php echo $htmlHelper->createCell('task_start_datetime', $task->task_start_date); ?>
                </tr>
                <tr>
                    <td align="right" nowrap="nowrap"><?php echo $AppUI->_('Finish Date'); ?>:</td>
                    <?php echo $htmlHelper->createCell('task_end_datetime', $task->task_end_date); ?>
                </tr>
                <tr>
                    <td align="right" nowrap="nowrap" valign="top"><?php echo $AppUI->_('Expected Duration'); ?>:</td>
                    <td class="hilite" width="300"><?php echo $task->task_duration . ' ' . $AppUI->_($durnTypes[$task->task_duration_type]); ?></td>
                </tr>
            </table>
        </td>
    </tr>
</table>
