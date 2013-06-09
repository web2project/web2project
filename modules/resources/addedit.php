<?php
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}

$resource_id = (int) w2PgetParam($_GET, 'resource_id', 0);

$resource = new CResource();
$resource->resource_id = $resource_id;

$obj = $resource;
$canAddEdit = $obj->canAddEdit();
$canAuthor = $obj->canCreate();
$canEdit = $obj->canEdit();
if (!$canAddEdit) {
	$AppUI->redirect(ACCESS_DENIED);
}

// load the record data
$obj = $AppUI->restoreObject();
if ($obj) {
    $resource = $obj;
    $resource_id = $resource->resource_id;
} else {
    $resource->load($resource_id);
}

if (!$resource_id && $resource_id > 0) {
    $AppUI->setMsg('Resource');
    $AppUI->setMsg('invalidID', UI_MSG_ERROR, true);
    $AppUI->redirect();
}

// setup the title block
$ttl = $resource_id ? 'Edit Resource' : 'Add Resource';
$titleBlock = new w2p_Theme_TitleBlock($AppUI->_($ttl), 'resources.png', $m, $m . '.' . $a);
$titleBlock->addCrumb('?m=' . $m, $m . ' list');

if ($resource_id) {
	$titleBlock->addCrumb('?m=resources&a=view&resource_id=' . $resource_id, 'view this resource');
}

$canDelete = $perms->checkModuleItem($m, 'delete', $resource_id);
if ($canDelete && $resource_id) {
    if (!isset($msg)) {
        $msg = '';
    }
	$titleBlock->addCrumbDelete('delete resource', $canDelete, $msg);
}
$titleBlock->show();

$percent = array(0 => '0', 5 => '5', 10 => '10', 15 => '15', 20 => '20', 25 => '25', 30 => '30', 35 => '35', 40 => '40', 45 => '45', 50 => '50', 55 => '55', 60 => '60', 65 => '65', 70 => '70', 75 => '75', 80 => '80', 85 => '85', 90 => '90', 95 => '95', 100 => '100');
$typelist = w2PgetSysVal('ResourceTypes');
?>
<script language="javascript" type="text/javascript">
function submitIt() {
	var form = document.editfrm;
	if (form.resource_name.value.length < 3) {
		alert( "<?php echo $AppUI->_('You must enter a name for the resource', UI_OUTPUT_JS); ?>" );
		form.resource_name.focus();
	} else {
		form.submit();
	}
}
</script>
<?php if ($canDelete) { ?>
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
<form name="editfrm" action="?m=resources" method="post" accept-charset="utf-8">
    <input type="hidden" name="dosql" value="do_resource_aed" />
    <input type="hidden" name="del" value="0" />
    <input type="hidden" name="resource_id" value="<?php echo $resource_id; ?>" />

    <table cellspacing="1" cellpadding="1" border="0" width="100%" class="std addedit">
        <tr>
            <td align="center" >
                <table>
                <tr>
                    <td align="right"><?php echo $AppUI->_('Resource Identifier'); ?></td>
                    <td align="left"><input type="text" class="text" size="15" maxlength="64" name="resource_key" value="<?php echo w2PformSafe($resource->resource_key); ?>" /></td>
                </tr>
                <tr>
                    <td align="right"><?php echo $AppUI->_('Resource Name'); ?></td>
                    <td align="left"><input type="text" class="text" size="30" maxlength="255" name="resource_name" value="<?php echo w2PformSafe($resource->resource_name); ?>" /></td>
                </tr>
                <tr>
                    <td align="right"><?php echo $AppUI->_('Type'); ?></td>
                    <td align="left"><?php echo arraySelect($typelist, 'resource_type', 'class="text"', $resource->resource_type, true); ?></td>
                </tr>
                <?php
                $resource->resource_max_allocation = ($resource->resource_max_allocation) ? $resource->resource_max_allocation : 100;
                ?>
                <tr>
                    <td align="right"><?php echo $AppUI->_('Maximum Allocation Percentage'); ?></td>
                    <td>
                        <?php echo arraySelect($percent, 'resource_max_allocation', 'size="1" class="text"', $resource->resource_max_allocation) . '%'; ?>
                    </td>
                </tr>
                <tr>
                    <td align="right"><?php echo $AppUI->_('Notes'); ?></td>
                    <td><textarea name="resource_note" cols="60" rows="7"><?php echo w2PformSafe($resource->resource_note); ?></textarea></td>
                </tr>
                </table>
            </td>
        </tr>
        <tr>
            <td>
                <input class="button btn btn-danger" type="button" name="cancel" value="<?php echo $AppUI->_('cancel'); ?>" onclick="javascript:if(confirm('<?php echo $AppUI->_('Are you sure you want to cancel?', UI_OUTPUT_JS); ?>')){location.href = './index.php?m=resources';}" />
            </td>
            <td align="right">
                <input type="button" class="button btn btn-primary" value="<?php echo $AppUI->_('submit'); ?>" onclick="submitIt()" />
            </td>
        </tr>
    </table>
</form>