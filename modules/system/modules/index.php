<?php
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}

// check permissions
$perms = &$AppUI->acl();
$canEdit = canEdit('system');
$canRead = canView('system');
if (!$canRead) {
	$AppUI->redirect(ACCESS_DENIED);
}

$AppUI->savePlace();

$module = new w2p_Core_Module();

$hidden_modules = array('public', 'install', );
$q = new w2p_Database_Query;
$q->addQuery('*');
$q->addTable('modules');
foreach ($hidden_modules as $no_show) {
	$q->addWhere('mod_directory <> \'' . $no_show . '\'');
}
$q->addOrder('mod_ui_order');
$modules = $q->loadList();
// get the modules actually installed on the file system
$modFiles = $AppUI->readDirs('modules');

$titleBlock = new w2p_Theme_TitleBlock('Modules', 'power-management.png', $m, "$m.$a");
$titleBlock->addCrumb('?m=system', 'System Admin');
$titleBlock->show();

$fieldList = array('mod_name', 'mod_active', 'mod_customize', 'mod_type',
    'mod_version', 'mod_ui_name', 'mod_ui_icon', 'mod_ui_active', 'mod_ui_order');
$fieldNames = array('Module', 'Status', 'Customize', 'Type', 'Version',
    'Menu Text', 'Menu Icon', 'Menu Status', 'Order');

$htmlHelper = new w2p_Output_HTMLHelper($AppUI);
?>

<table class="tbl list modules">
    <?php
    echo '<tr><th></th>';
    foreach ($fieldNames as $index => $name) {
        echo '<th>' . $AppUI->_($fieldNames[$index]) . '</th>';
    }
    echo '</tr>';

    // do the modules that are installed on the system
    foreach ($modules as $row) {
        // clear the file system entry
        if (isset($modFiles[$row['mod_directory']])) {
            $modFiles[$row['mod_directory']] = '';
        }
        $query_string = '?m=' . $m . '&u=' . $u . '&a=domodsql&mod_id=' . $row['mod_id'];
        $s = '';
        $s .= '<td width="64" align="center">';
        if ($canEdit) {
            $s .= w2PtoolTip('', 'Move to First') . '<a href="' . $query_string . '&cmd=movefirst"><img src="' . w2PfindImage('icons/2uparrow.png') . '" border="0" alt=""/></a>' . w2PendTip();
            $s .= w2PtoolTip('', 'Move Up') . '<a href="' . $query_string . '&cmd=moveup"><img src="' . w2PfindImage('icons/1uparrow.png') . '" border="0" alt=""/></a>' . w2PendTip();
            $s .= w2PtoolTip('', 'Move Down') . '<a href="' . $query_string . '&cmd=movedn"><img src="' . w2PfindImage('icons/1downarrow.png') . '" border="0" alt=""/></a>' . w2PendTip();
            $s .= w2PtoolTip('', 'Move to Last') . '<a href="' . $query_string . '&cmd=movelast"><img src="' . w2PfindImage('icons/2downarrow.png') . '" border="0" alt=""/></a>' . w2PendTip();
        }
        $s .= '</td>';

        $s .= $htmlHelper->createCell('na', $row['mod_name']);
        $s .= '<td>';
        $s .= '<img src="' . w2PfindImage('obj/dot' . ($row['mod_active'] ? 'green' : 'yellowanim') . '.gif') . '" alt="" />&nbsp;';
        if ($canEdit) {
            $s .= '<a href="' . $query_string . '&cmd=toggle&">';
        }
        $s .= ($row['mod_active'] ? $AppUI->_('active') : $AppUI->_('disabled'));
        if ($canEdit) {
            $s .= '</a>';
        }
        if ($row['mod_type'] != 'core' && $canEdit) {
            $s .= ' | <a href="' . $query_string . '&cmd=remove" onclick="return window.confirm(' . "'" . $AppUI->_('This will delete all data associated with the module!') . "\\n\\n" . $AppUI->_('Are you sure?') . "\\n" . "'" . ');">' . $AppUI->_('remove') . '</a>';
        }

        //check for a setup file
        $ok = file_exists(W2P_BASE_DIR . '/modules/' . $row['mod_directory'] . '/setup.php');
        if ($ok) {
            include_once (W2P_BASE_DIR . '/modules/' . $row['mod_directory'] . '/setup.php');

            // check for upgrades
            if ($config['mod_version'] != $row['mod_version'] && $canEdit) {
                $s .= ' | <a href="' . $query_string . '&cmd=upgrade" onclick="return window.confirm(' . "'" . $AppUI->_('Are you sure?') . "'" . ');" >' . $AppUI->_('upgrade') . '</a>';
            }
            // check for configuration
            if (isset($config['mod_config']) && $config['mod_config'] == true && $canEdit) {
                $s .= ' | <a href="' . $query_string . '&cmd=configure">' . $AppUI->_('configure') . '</a>';
            }
        }
        $s .= '</td>';
        $s .= '<td>';
        $views = $module->getCustomizableViews($row['mod_directory']);
        if (count($views)) {
// TODO: Should we have a 'reset to default' for each of these?
            foreach ($views as $view) {
                $s .= '<a href="?m=system&u=modules&a=addedit&mod_id='.$row['mod_id'].'&v='.$view.'">';
                $s .= $view;
                $s .= '</a><br />';
            }
        }
        
        $s .= '</td>';
        $s .= $htmlHelper->createCell('na', $row['mod_type']);
        $s .= $htmlHelper->createCell('na', $row['mod_version']);
        $s .= $htmlHelper->createCell('na', $row['mod_ui_name']);
        $s .= $htmlHelper->createCell('mod_ui_icon', $row['mod_ui_icon']);
        
        $s .= '<td class="data _status">';
        $s .= '<img src="' . w2PfindImage('/obj/' . ($row['mod_ui_active'] ? 'dotgreen.gif' : 'dotredanim.gif')) . '" alt="" />&nbsp;';
        if ($canEdit) {
            $s .= '<a href="' . $query_string . '&cmd=toggleMenu">';
        }
        $s .= ($row['mod_ui_active'] ? $AppUI->_('visible') : $AppUI->_('hidden'));
        if ($canEdit) {
            $s .= '</a>';
        }
        $s .= '</td>';

        $s .= $htmlHelper->createCell('_count', $row['mod_ui_order']);

        echo '<tr>' . $s . '</tr>';
    }

    foreach ($modFiles as $v) {
        // clear the file system entry
        if ($v && !in_array($v, $hidden_modules)) {
            $s = '';
            $s .= '<td></td>';
            $s .= '<td>' . $AppUI->_($v) . '</td>';
            $s .= '<td>';
            $s .= '<img src="' . w2PfindImage('obj/dotgrey.gif') . '" alt="" />&nbsp;';
            if ($canEdit) {
                $s .= '<a href="?m=' . $m . '&u=modules&a=domodsql&cmd=install&mod_directory=' . $v . '">';
            }
            $s .= $AppUI->_('install');
            if ($canEdit) {
                $s .= '</a>';
            }
            $s .= '</td>';
            echo '<tr>' . $s . '</tr>';
        }
    }
    ?>
    <tr>
        <td colspan="10" style="text-align: center;">
            <?php echo $AppUI->_('Select a module to upload'); ?>:
            <form action="./index.php?m=system" method="post" enctype="multipart/form-data">
                <input type="hidden" name="dosql" value="do_module_upload" />
                <input type="file" name="module_upload" size="50" maxlength="1000000" class="text" />
                <?php if (is_writable(W2P_BASE_DIR.'/files')) { ?>
                <input type="submit" value="<?php echo $AppUI->_('Upload'); ?>" class="text" />
                <?php } else { ?>
                    <span class="error">
                        <?php echo $AppUI->_('Module uploads are not allowed. Please check permissions on the /modules directory.'); ?>
                    </span>
                <?php } ?>
            </form>
        </td>
    </tr>
</table>