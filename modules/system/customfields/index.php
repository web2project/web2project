<?php /* $Id$ $URL$ */
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}

// check permissions
$perms = $AppUI->acl();
if (!canEdit('system')) {
	$AppUI->redirect('m=public&a=access_denied');
}

$titleBlock = new CTitleBlock('Custom field editor', 'customfields.png', $m, "$m.$a");
$titleBlock->addCrumb('?m=system', 'system admin');
$titleBlock->show();

$manager = new w2p_Core_CustomFieldManager($AppUI);
$modules = $manager->getModuleList();

?>
<table width="100%" border="0" cellpadding="2" cellspacing="1" class="tbl">
    <?php foreach ($modules as $module) { ?>
    <tr>
        <td colspan="10">
            <h2>
                <?php echo w2PtoolTip($AppUI->_($module['mod_name']), $AppUI->_('Click this icon to Add a new Custom Field to this Module.'), true); ?>
                    <a href="?m=system&a=custom_field_addedit&module=<?php echo $module['mod_id']; ?>">
                        <img src="<?php echo w2PfindImage('icons/edit_add.png'); ?>" border="0" alt=""><?php echo $AppUI->_($module['mod_name']); ?>
                    </a>
                <?php echo w2PendTip(); ?>
            </h2>
        </td>
    </tr>
    <?php
    $custom_fields = $manager->getStructure($module['mod_name']);

    $s = '';
	if (count($custom_fields)) {
		$s .= '<th width="10"></th>';
        $s .= '<th>' . $AppUI->_('Name') . '</th>';
		$s .= '<th>' . $AppUI->_('Description') . '</th>';
		$s .= '<th>' . $AppUI->_('Type') . '</th>';
		$s .= '<th>' . $AppUI->_('Published') . '</th>';
		$s .= '<th>' . $AppUI->_('Order') . '</th>';
        $s .= '<th width="5"></th>';
        foreach ($custom_fields as $field) {
            $s .= '<tr><td class="hilite">';
            $s .= w2PtoolTip('', $AppUI->_('Click this icon to Edit this Custom Field.'), true);
            $s .= '<a href="?m=system&u=customfields&a=addedit&module=' . $module['mod_id'] . '&field_id=' . $field['field_id'] . '"><img src="' . w2PfindImage('icons/stock_edit-16.png') . '" align="center" width="16" height="16" border="0" alt=""></a>';
            $s .= w2PendTip();
            $s .= '<td>'.$field['field_name'].'</td>';
            $s .= '<td>'.$field['field_description'].'</td>';
            $s .= '<td>'.$manager->getType($field['field_htmltype']).'</td>';
            $s .= '<td>'.($field['field_published'] ? $AppUI->_('Yes') : $AppUI->_('No')).'</td>';
            $s .= '<td>'.$field['field_order'].'</td>';
            $s .= '<td>';
            $s .= w2PtoolTip('', $AppUI->_('Click this icon to Delete this Custom Field.'), true);
            $s .= '<a href="?m=system&u=customfields&a=addedit&field_id=' . $field['field_id'] . '&delete=1"><img src="' . w2PfindImage('icons/stock_delete-16.png') . '" align="center" width="16" height="16" border="0" alt=""></a> ';
            $s .= w2PendTip();
            $s .= '</td></tr>';
        }
        echo $s;
	}

    }?>
</table>