<?php
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}
// @todo    convert to template
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
$titleBlock = new w2p_Theme_TitleBlock($AppUI->_($ttl), 'icon.png', $m, $m . '.' . $a);
$titleBlock->addCrumb('?m=' . $m, $m . ' list');

if ($resource_id) {
	$titleBlock->addCrumb('?m=resources&a=view&resource_id=' . $resource_id, 'view this resource');
}
$titleBlock->show();

$percent = array(0 => '0', 5 => '5', 10 => '10', 15 => '15', 20 => '20', 25 => '25', 30 => '30', 35 => '35', 40 => '40', 45 => '45', 50 => '50', 55 => '55', 60 => '60', 65 => '65', 70 => '70', 75 => '75', 80 => '80', 85 => '85', 90 => '90', 95 => '95', 100 => '100');
$resource->resource_max_allocation = ($resource->resource_max_allocation) ? $resource->resource_max_allocation : 100;
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
<?php

$form = new w2p_Output_HTML_FormHelper($AppUI);

?>
<form name="editfrm" action="?m=<?php echo $m; ?>" method="post" accept-charset="utf-8" class="addedit resources">
    <input type="hidden" name="dosql" value="do_resource_aed" />
    <input type="hidden" name="del" value="0" />
    <input type="hidden" name="resource_id" value="<?php echo $resource_id; ?>" />
    <?php echo $form->addNonce(); ?>

    <div class="std addedit resources">
        <div class="column left">
            <p>
                <?php $form->showLabel('Resource Identifier'); ?>
                <input type="text" class="text" size="15" maxlength="64" name="resource_key" value="<?php echo w2PformSafe($resource->resource_key); ?>" />
            </p>
            <p>
                <?php $form->showLabel('Resource Name'); ?>
                <input type="text" class="text" size="30" maxlength="255" name="resource_name" value="<?php echo w2PformSafe($resource->resource_name); ?>" />
            </p>
            <p><?php $form->showLabel('Type'); ?>
                <?php echo arraySelect($typelist, 'resource_type', 'class="text"', $resource->resource_type, true); ?>
            </p>
            <p>
                <?php $form->showLabel('Max Allocation'); ?>
                <?php echo arraySelect($percent, 'resource_max_allocation', 'size="1" class="text"', $resource->resource_max_allocation) . '%'; ?>
            </p>
            <p>
                <?php $form->showLabel('Notes'); ?>
                <textarea name="resource_note" cols="60" rows="7"><?php echo w2PformSafe($resource->resource_note); ?></textarea>
            </p>
            <input class="button btn btn-danger cancel" type="button" name="cancel" value="<?php echo $AppUI->_('cancel'); ?>" onclick="javascript:if(confirm('<?php echo $AppUI->_('Are you sure you want to cancel?', UI_OUTPUT_JS); ?>')){location.href = './index.php?m=resources';}" />
            <input style="float: right;" type="button" class="button btn btn-primary save" value="<?php echo $AppUI->_('save'); ?>" onclick="submitIt()" />
        </div>
    </div>
</form>