<?php

if (!defined('W2P_BASE_DIR')) {
    die('You should not access this file directly.');
}

// check permissions
if (!canEdit('system')) {
    $AppUI->redirect(ACCESS_DENIED);
}
$reset = (int) w2PgetParam($_GET, 'reset', 0);
if ($reset == 1) {
    $obj = &$AppUI->acl();
    $obj->recalcPermissions();
}

$w2Pcfg = new w2p_System_Config();

// retrieve the system configuration data
$rs = $w2Pcfg->loadAll('config_group');

$tab = $AppUI->processIntState('ConfigIdxTab', $_GET, 'tab', 0);

$active = intval(!$AppUI->getState('ConfigIdxTab'));

$titleBlock = new w2p_Theme_TitleBlock('System Configuration', 'control-center.png', $m);
$titleBlock->addCrumb('?m=system', 'system admin');
$titleBlock->addCrumb('?m=system&a=addeditpref', 'default user preferences');
$titleBlock->show();

// prepare the automated form fields based on db system configuration data
$output = null;
$last_group = '';
foreach ($rs as $c) {

    $tooltip = $AppUI->_($c['config_name'] . '_tooltip');
    // extraparse the checkboxes and the select lists
    $extra = '';
    $value = '';
    switch ($c['config_type']) {
        case 'select':
            // Build the select list.
            if ($c['config_name'] == 'system_timezone') {
                $timezones = w2PgetSysVal('Timezones');
                $entry = arraySelect($timezones, 'w2Pcfg[system_timezone]', 'class=text size=1', w2PgetConfig('system_timezone'), true);
            } else {
                $entry = '<select class="text" name="w2Pcfg[' . $c['config_name'] . ']">';
                // Find the detail relating to this entry.
                $children = $w2Pcfg->getChildren($c['config_id']);
                foreach ($children as $child) {
                    $entry .= '<option value="' . $child['config_list_name'] . '"';
                    if ($child['config_list_name'] == $c['config_value']) {
                        $entry .= ' selected="selected"';
                    }
                    $entry .= '>' . $AppUI->_($child['config_list_name'] . '_item_title') . '</option>';
                }
                $entry .= '</select>';
            }
            break;
        case 'checkbox':
            $extra = ($c['config_value'] == 'true') ? 'checked="checked"' : '';
            $value = 'true';
            // allow to fallthrough
        default:
            if (!$value) {
                $value = $c['config_value'];
            }
            if (strpos($c['config_name'], '_pass') !== false) {
                $c['config_type'] = 'password';
                $value = str_repeat('x', strlen($value));
                $entry = '<input class="text" type="password" name="w2Pcfg[' . $c['config_name'] . ']" value="' . $value . '" ' . $extra . ' onChange="document.getElementById(\''.$c['config_name'].'_mod\').value=\'1\';" />';
                $entry .= '<input type="hidden" name="'.$c['config_name'].'_mod" id="'.$c['config_name'].'_mod" value="" />';
            } else {
                $entry = '<input class="text" type="' . $c['config_type'] . '" name="w2Pcfg[' . $c['config_name'] . ']" id="w2Pcfg[' . $c['config_name'] . ']" value="' . $value . '" ' . $extra . '/>';
            }
            break;
    }

    if ($c['config_group'] != $last_group) {
        $output .= '<tr><td colspan="2"><b>' . $AppUI->_($c['config_group'] . '_group_title') . '</b></td></tr>';
        $last_group = $c['config_group'];
    }
    $output .= '<tr>
                    <td class="item" width="20%"><a name="'.$c['config_name'].'"> </a>' . $AppUI->_($c['config_name'] . '_title') . '</td>' .
                    '<td align="left" width="45%">' .
                        $entry .
                        '<input class="button" type="hidden"  name="w2PcfgId[' . $c['config_name'] . ']" value="' . $c['config_id'] . '" />' .
                    '</td>' .
                    '<td align="left" width="16">' .
                         w2PtoolTip($AppUI->_($c['config_name'] . '_title'), $tooltip, true) . w2PshowImage('log-info.gif') . w2PendTip() .
                    '</td>
                    <td align="left" width="30%">&nbsp;</td>
                </tr>';

}
?>
<form name="cfgFrm" action="index.php?m=system&a=systemconfig" method="post" accept-charset="utf-8">
	<input type="hidden" name="dosql" value="do_systemconfig_aed" />
	<table class="std view well">
		<tr><td colspan="4"><?php echo $AppUI->_('syscfg_intro'); ?></td></tr>
		<?php echo $output; ?>
		<tr>
	 		<td align="right" colspan="4"><input class="save button btn btn-primary" type="submit" name="do_save_cfg" value="<?php echo $AppUI->_('save'); ?>" /></td>
		</tr>
	</table>
</form>
<script language="javascript" type="text/javascript">
        $(document).ready(function () {
            $("#w2Pcfg\\[system_timezone\\]").wrap("<div class='selectborder' />")
            $("#w2Pcfg\\[admin_email\\]").wrap("<div class='selectborder' />")
        });
</script>
