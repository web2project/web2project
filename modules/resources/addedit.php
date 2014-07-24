<?php
if (!defined('W2P_BASE_DIR')) {
    die('You should not access this file directly.');
}
// @todo    convert to template
$object_id = (int) w2PgetParam($_GET, 'resource_id', 0);



$object = new CResource();
$object->setId($object_id);

$obj = $object;
$canAddEdit = $obj->canAddEdit();
$canAuthor = $obj->canCreate();
$canEdit = $obj->canEdit();
if (!$canAddEdit) {
    $AppUI->redirect(ACCESS_DENIED);
}

$obj = $AppUI->restoreObject();
if ($obj) {
    $object = $obj;
    $object_id = $object->getId();
} else {
    $object->load($object_id);
}
if (!$object && $object_id > 0) {
    $AppUI->setMsg('Resource');
    $AppUI->setMsg('invalidID', UI_MSG_ERROR, true);
    $AppUI->redirect('m=' . $m);
}

// setup the title block
$ttl = $object_id ? 'Edit Resource' : 'Add Resource';
$titleBlock = new w2p_Theme_TitleBlock($ttl, 'icon.png', $m);
$titleBlock->addCrumb('?m=' . $m, $m . ' list');
$titleBlock->addViewLink('resource', $object_id);
$titleBlock->show();

$percent = array(0 => '0', 5 => '5', 10 => '10', 15 => '15', 20 => '20', 25 => '25', 30 => '30', 35 => '35', 40 => '40', 45 => '45', 50 => '50', 55 => '55', 60 => '60', 65 => '65', 70 => '70', 75 => '75', 80 => '80', 85 => '85', 90 => '90', 95 => '95', 100 => '100');
$object->resource_max_allocation = ($object->resource_max_allocation) ? $object->resource_max_allocation : 100;
$typelist = w2PgetSysVal('ResourceTypes');
?>
<script language="javascript" type="text/javascript">
function submitIt() {
    var form = document.editFrm;
    if (form.resource_name.value.length < 3) {
        alert( "<?php echo $AppUI->_('You must enter a name for the resource', UI_OUTPUT_JS); ?>" );
        form.resource_name.focus();
    } else {
        form.submit();
    }
}
</script>
<?php
include $AppUI->getTheme()->resolveTemplate('resources/addedit');