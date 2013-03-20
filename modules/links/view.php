<?php
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}

$link_id    = (int) w2PgetParam($_GET, 'link_id', 0);

$link = new CLink();
$link->link_id = $link_id;

$obj = $link;
$canView = $obj->canView();
if (!$canView) {
	$AppUI->redirect(ACCESS_DENIED);
}

$obj = $AppUI->restoreObject();
if ($obj) {
    $link = $obj;
    $link_id = $link->link_id;
} else {
    $link->loadFull(null, $link_id);
}
if (!$link && $link_id > 0) {
    $AppUI->setMsg('Link');
    $AppUI->setMsg('invalidID', UI_MSG_ERROR, true);
    $AppUI->redirect();
}

// setup the title block
$titleBlock = new w2p_Theme_TitleBlock($AppUI->_('View Link'), 'folder5.png', $m, $m . '.' . $a);
$titleBlock->addCrumb('?m=' . $m, 'links list');
$canDelete = $link->canDelete();
if ($canDelete && $link_id) {
    if (!isset($msg)) {
        $msg = '';
    }
    $titleBlock->addCrumbDelete('delete link', $canDelete, $msg);
}
$titleBlock->show();

$htmlHelper = new w2p_Output_HTMLHelper($AppUI);
$htmlHelper->stageRowData(array('link_id' => $link_id, 'task_id' => $link->link_task, 'user_id' => $link->link_owner));

$prj = new CProject();
$projects = $prj->getAllowedProjects($AppUI->user_id, false);
$projects = arrayMerge(array('0' => $AppUI->_('None')), $projects);

$link_types = w2PgetSysVal('LinkType');
?>
<script language="javascript" type="text/javascript">
function delIt() {
	if (confirm( "<?php echo $AppUI->_('linksDelete', UI_OUTPUT_JS); ?>" )) {
		var f = document.viewFrm;
		f.del.value='1';
		f.submit();
	}
}
</script>

<form name="viewFrm" action="?m=links" method="post" accept-charset="utf-8">
    <input type="hidden" name="dosql" value="do_link_aed" />
    <input type="hidden" name="del" value="0" />
    <input type="hidden" name="link_id" value="<?php echo $link_id; ?>" />
    <input type="hidden" name="link_owner" value="<?php echo $AppUI->user_id; ?>" />

    <table width="100%" border="0" cellpadding="3" cellspacing="3" class="std addedit">
        <tr>
            <td width="100%" valign="top" align="center">
              <table cellspacing="1" cellpadding="2" width="60%">
                <tr>
                    <td align="right" nowrap="nowrap"><?php echo $AppUI->_('Link Name'); ?>:</td>
                    <?php echo $htmlHelper->createCell('link_name', $link->link_name, $link_id); ?>
                </tr>
                <tr>
                    <td align="right" nowrap="nowrap"><?php echo $AppUI->_('Uploaded By'); ?>:</td>
                    <?php echo $htmlHelper->createCell('user_name', $link->contact_first_name); ?>
                </tr>
                <tr>
                    <td align="right" nowrap="nowrap"><?php echo $AppUI->_('Category'); ?>:</td>
                    <?php echo $htmlHelper->createCell('link_category', $link_types[$link->link_category]); ?>
                </tr>
                <tr>
                    <td align="right" nowrap="nowrap"><?php echo $AppUI->_('Project'); ?>:</td>
                    <?php echo $htmlHelper->createCell('link_project', $project[$link->link_project]['project_name']); ?>
                </tr>
                <tr>
                    <td align="right" nowrap="nowrap"><?php echo $AppUI->_('Task'); ?>:</td>
                    <?php echo $htmlHelper->createCell('task_name', $link->task_name); ?>
                </tr>
                <tr>
                    <td align="right" nowrap="nowrap"><?php echo $AppUI->_('Description'); ?>:</td>
                    <?php echo $htmlHelper->createCell('link_description', $link->link_description); ?>
                </tr>
                <tr>
                  <td align="right" nowrap="nowrap"><?php echo $AppUI->_('Link URL'); ?>:</td>
                    <?php echo $htmlHelper->createCell('link_url', $link->link_url); ?>
                </tr>
              </table>
            </td>
        </tr>
    </table>
</form>