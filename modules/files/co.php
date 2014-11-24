<?php
if (!defined('W2P_BASE_DIR')) {
    die('You should not access this file directly.');
}
// @todo    convert to template
// @todo    remove database query

$file_id = intval(w2PgetParam($_GET, 'file_id', 0));
// check permissions for this record
$perms = &$AppUI->acl();

$canEdit = $perms->checkModuleItem($m, 'edit', $file_id);
if (!$canEdit) {
    $AppUI->redirect(ACCESS_DENIED);
}
$canAdmin = canEdit('system');

$file_parent = intval(w2PgetParam($_GET, 'file_parent', 0));

// check if this record has dependencies to prevent deletion
$msg = '';
$obj = new CFile();

// load the record data
if ($file_id > 0 && !$obj->load($file_id)) {
    $AppUI->setMsg('File');
    $AppUI->setMsg('invalidID', UI_MSG_ERROR, true);
    $AppUI->redirect('m=' . $m);
}

// setup the title block
$titleBlock = new w2p_Theme_TitleBlock('Checkout', 'folder5.png', $m);
$titleBlock->addCrumb('?m=files', 'files list');
$titleBlock->show();

if ($obj->file_project) {
    $file_project = $obj->file_project;
}
if ($obj->file_task) {
    $file_task = $obj->file_task;
    $task_name = $obj->getTaskName();
} elseif ($file_task) {
    $q = new w2p_Database_Query();
    $q->addTable('tasks');
    $q->addQuery('task_name');
    $q->addWhere('task_id=' . (int) $file_task);
    $task_name = $q->loadResult();
    $q->clear();
} else {
    $task_name = '';
}

$extra = array('where' => 'project_active<>0');
$project = new CProject();
$projects = $project->getAllowedRecords($AppUI->user_id, 'projects.project_id,project_name', 'project_name', null, $extra, 'projects');
$projects = arrayMerge(array('0' => $AppUI->_('All')), $projects);
$htmlHelper = new w2p_Output_HTMLHelper($AppUI);
?>

<script language="javascript" type="text/javascript">
function popFile(params)
{
    fileloader = window.open("fileviewer.php?"+params,"mywindow","location=1,status=1,scrollbars=0,width=80,height=80");
    fileloader.moveTo(0,0);
}
</script>

<form name="coFrm" action="?m=files" method="post" accept-charset="utf-8">
	<input type="hidden" name="dosql" value="do_file_co" />
	<input type="hidden" name="del" value="0" />
	<input type="hidden" name="file_id" value="<?php echo $file_id; ?>" />
    <input type="hidden" name="file_checkout" value="<?php echo $AppUI->user_id; ?>" />
    <input type="hidden" name="file_version_id" value="<?php echo $obj->file_version_id; ?>" />

    <table class="std view">
        <tr>
            <td width="100%" valign="top" align="center">
                <table cellspacing="1" cellpadding="2" width="60%">
            <?php if ($file_id) { ?>
                <tr>
                    <td align="right" nowrap="nowrap"><?php echo $AppUI->_('File Name'); ?>:</td>
                    <td align="left" class="hilite"><?php echo strlen($obj->file_name) == 0 ? "n/a" : $obj->file_name; ?></td>
                </tr>
                <tr valign="top">
                    <td align="right" nowrap="nowrap"><?php echo $AppUI->_('Type'); ?>:</td>
                    <?php echo $htmlHelper->createCell('file_type', $obj->file_type); ?>
                </tr>
                <tr>
                    <td align="right" nowrap="nowrap"><?php echo $AppUI->_('Size'); ?>:</td>
                    <?php echo $htmlHelper->createCell('file_size', $obj->file_size); ?>
                </tr>
                <tr>
                    <td align="right" nowrap="nowrap"><?php echo $AppUI->_('Uploaded By'); ?>:</td>
                    <?php echo $htmlHelper->createCell('file_owner', $obj->file_owner); ?>
                </tr>
            <?php } ?>
                <tr>
                    <td align="right" nowrap="nowrap"><?php echo $AppUI->_('CO Reason'); ?>:</td>
                    <td align="left">
                        <textarea name="file_co_reason" class="textarea" rows="4" style="width:270px"><?php echo $obj->file_co_reason; ?></textarea>
                    </td>
                </tr>

                <tr>
                    <td align="right" nowrap="nowrap">&nbsp;</td>
                    <td align="left"><input type="checkbox" name="notify" id="notify" checked="checked" /><label for="notify"><?php echo $AppUI->_('Notify Assignees of Task or Project Owner by Email'); ?></label></td>
                </tr>

                <tr>
                    <td align="right" nowrap="nowrap">&nbsp;</td>
                    <td align="left"><input type="checkbox" name="notify_contacts" id="notify_contacts" checked="checked" /><label for="notify_contacts"><?php echo $AppUI->_('Notify Project and Task Contacts'); ?></label></td>
                </tr>

                </table>
            </td>
        </tr>
        <tr>
            <td>
                <input class="button" type="button" name="cancel" value="<?php echo $AppUI->_('cancel'); ?>" onclick="javascript:if (confirm('<?php echo $AppUI->_('Are you sure you want to cancel?', UI_OUTPUT_JS); ?>')) {location.href = './index.php?m=files';}" />
            </td>
            <td align="right">
                <input type="submit" class="button" value="<?php echo $AppUI->_('submit'); ?>" />
            </td>
        </tr>
    </table>
</form>
