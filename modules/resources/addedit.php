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
                <?php $form->showField('resource_key', $resource->resource_key, array('maxlength' => 64)); ?>
            </p>
            <p>
                <?php $form->showLabel('Resource Name'); ?>
                <?php $form->showField('resource_name', $resource->resource_name, array('maxlength' => 255)); ?>
            </p>
            <p><?php $form->showLabel('Type'); ?>
                <?php $form->showField('resource_type', $resource->resource_type, array(), $typelist); ?>
            </p>
            <p>
                <?php $form->showLabel('Max Allocation'); ?>
                <?php $form->showField('resource_max_allocation', $resource->resource_max_allocation, array(), $percent); ?>
            </p>
            <p>
                <?php $form->showLabel('Notes'); ?>
                <?php $form->showField('resource_note', $resource->resource_note); ?>
            </p>
            <?php $form->showCancelButton(); ?>
            <?php $form->showSaveButton(); ?>
        </div>
    </div>
</form>