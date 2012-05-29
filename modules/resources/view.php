<?php /* $Id$ $URL$ */
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}

$resource_id = (int) w2PgetParam($_GET, 'resource_id', 0);

// check permissions for this record
$perms = &$AppUI->acl();

$canView = $perms->checkModuleItem($m, 'view', $resource_id);
if (!$canView) {
  $AppUI->redirect('m=public&a=access_denied');
}

$canAdd = $perms->checkModuleItem($m, 'add');
$canEdit = $perms->checkModuleItem($m, 'edit', $resource_id);
$canDelete = $perms->checkModuleItem($m, 'delete', $resource_id);

$resource = new CResource();
$resource->load($resource_id);

if (!$resource) {
	$AppUI->setMsg('Resource');
	$AppUI->setMsg('invalidID', UI_MSG_ERROR, true);
	$AppUI->redirect();
} else {
	$AppUI->savePlace();
}

// setup the title block
$titleBlock = new w2p_Theme_TitleBlock('View Resource', 'resources.png', $m, $m . '.' . $a);
$titleBlock->addCell();
if ($canAdd) {
	$titleBlock->addCell('<input type="submit" class="button" value="' . $AppUI->_('new resource') . '" />', '', '<form action="?m=resources&a=addedit" method="post" accept-charset="utf-8">', '</form>');
}

$titleBlock->addCrumb('?m=' . $m, 'resource list');
if ($canEdit) {
	$titleBlock->addCrumb('?m=resources&a=addedit&resource_id=' . $resource_id, 'edit this resource');

    if ($canDelete) {
        $titleBlock->addCrumbDelete('delete resource', $canDelete, 'no delete permission');
    }
}
$titleBlock->show();

$htmlHelper = new w2p_Output_HTMLHelper($AppUI);
// security improvement:
// some javascript functions may not appear on client side in case of user not having write permissions
// else users would be able to arbitrarily run 'bad' functions
if ($canDelete) {
?>
  <script language="javascript" type="text/javascript">
    function delIt() {
    	if (confirm( '<?php echo $AppUI->_('doDelete') . ' ' . $AppUI->_('Resource') . '?'; ?>' )) {
    		document.frmDelete.submit();
    	}
    }
  </script>

	<form name="frmDelete" action="./index.php?m=resources" method="post" accept-charset="utf-8">
		<input type="hidden" name="dosql" value="do_resource_aed" />
		<input type="hidden" name="del" value="1" />
		<input type="hidden" name="resource_id" value="<?php echo $resource_id; ?>" />
	</form>
<?php } ?>

<table border="0" cellpadding="4" cellspacing="0" width="100%" class="std view">
    <tr>
        <td valign="top" width="100%">
            <strong><?php echo $AppUI->_('Details'); ?></strong>
            <table cellspacing="1" cellpadding="2" width="100%">
                <tr>
                    <td align="right" nowrap="nowrap"><?php echo $AppUI->_('Resource ID'); ?>:</td>
                    <?php echo $htmlHelper->createCell('resource_key', $resource->resource_key); ?>
                </tr>
                <tr>
                    <td align="right" nowrap="nowrap"><?php echo $AppUI->_('Resource Name'); ?>:</td>
                    <?php echo $htmlHelper->createCell('resource_name-nolink', $resource->resource_name); ?>
                </tr>
                <tr>
                    <td align="right" nowrap="nowrap"><?php echo $AppUI->_('Type'); ?>:</td>
                    <td class="hilite" width="100%"><?php echo $resource->getTypeName(); ?></td>
                </tr>
                <tr>
                    <td align="right" nowrap="nowrap"><?php echo $AppUI->_('Max Allocation %'); ?>:</td>
                    <?php echo $htmlHelper->createCell('allocation_assignment', $resource->resource_max_allocation); ?>
                </tr>
            </table>
        </td>
    </tr>
    <tr>
        <td width="100%" valign="top">
            <strong><?php echo $AppUI->_('Description'); ?></strong>
            <table cellspacing="0" cellpadding="2" border="0" width="100%">
                <tr>
                    <td class="hilite">
                        <?php echo w2p_textarea($resource->resource_note); ?>&nbsp;
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>