<?php
if (!defined('W2P_BASE_DIR')) {
    die('You should not access this file directly.');
}
// @todo    convert to template
$resource_id = (int) w2PgetParam($_GET, 'resource_id', 0);

$obj = new CResource();

if (!$obj->load($resource_id)) {
    $AppUI->redirect(ACCESS_DENIED);
}

$canEdit   = $obj->canEdit();
$canDelete = $obj->canDelete();

$titleBlock = new w2p_Theme_TitleBlock('View Resource', 'icon.png', $m);
$titleBlock->addCrumb('?m=' . $m, $m . ' list');
if ($canEdit) {
    $titleBlock->addCrumb('?m=resources&a=addedit&resource_id=' . $resource_id, 'edit this resource');

    if ($canDelete) {
        $titleBlock->addCrumbDelete('delete resource', $canDelete, 'no delete permission');
    }
}
$titleBlock->show();

$htmlHelper = new w2p_Output_HTMLHelper($AppUI);
$types = w2PgetSysVal('ResourceTypes');
$types[0] = 'Not Specified';
$customLookups = array('resource_type' => $types);
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

<?php
include $AppUI->getTheme()->resolveTemplate('resources/view');