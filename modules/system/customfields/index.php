<?php /* $Id$ $URL$ */
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}

// check permissions
$perms = &$AppUI->acl();
if (!canEdit('system')) {
	$AppUI->redirect('m=public&a=access_denied');
}

$titleBlock = new CTitleBlock('Custom field editor', 'customfields.png', 'admin', 'admin.custom_field_editor');
$titleBlock->addCrumb('?m=system', 'system admin');
$titleBlock->show();

$manager = new w2p_Core_CustomFieldManager();
$modules = $manager->getModuleList();

?>
<table width="100%" border="0" cellpadding="2" cellspacing="1" class="tbl">
    <?php foreach ($modules as $module) { ?>
    <tr>
        <td>
            <h2>
                <?php echo w2PtoolTip($AppUI->_($module['mod_name']), $AppUI->_('Click this icon to Add a new Custom Field to this Module.'), true); ?>
                    <a href="?m=system&a=custom_field_addedit&module=<?php echo $module['mod_id']; ?>">
                        <img src="<?php echo w2PfindImage('icons/edit_add.png'); ?>" border="0" alt=""><?php echo $AppUI->_($module['mod_name']); ?>
                    </a>
                <?php echo w2PendTip(); ?>
            </h2>
        </td>
    </tr>
    <?php }?>
</table>