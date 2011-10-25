<?php /* $Id$ $URL$ */
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}

##
##	Companies: View User sub-table
##

global $AppUI, $company_id;

$userList = CCompany::getUsers($AppUI, $company_id);
?>
<a name="users-company_view"> </a>
<table width="100%" border="0" cellpadding="2" cellspacing="1" class="tbl">
    <tr>
        <?php
        $fieldList = array();
        $fieldNames = array();
        $fields = w2p_Core_Module::getSettings('users', 'company_view');
        if (count($fields) > 0) {
            foreach ($fields as $field => $text) {
                $fieldList[] = $field;
                $fieldNames[] = $text;
            }
        } else {
            // TODO: This is only in place to provide an pre-upgrade-safe 
            //   state for versions earlier than v3.0
            //   At some point at/after v4.0, this should be deprecated
            $fieldList = array('user_username', 'contact_last_name');
            $fieldNames = array('Username', 'Name');
        }
//TODO: The link below is commented out because this module doesn't support sorting... yet.
        foreach ($fieldNames as $index => $name) {
            ?><th nowrap="nowrap">
<!--                <a href="?m=companies&a=view&company_id=<?php echo $company_id; ?>&sort=<?php echo $fieldList[$index]; ?>#users-company_view" class="hdr">-->
                    <?php echo $AppUI->_($fieldNames[$index]); ?>
<!--                </a>-->
            </th><?php
        }
        ?>
    </tr>
<?php

if (count($userList) > 0) {
    $htmlHelper = new w2p_Output_HTMLHelper($AppUI);
    foreach ($userList as $user) {
        echo '<tr><td>';
        echo '<a href="./index.php?m=admin&a=viewuser&user_id=' . $user['user_id'] . '">' . $user['user_username'] . '</a>';
        echo $htmlHelper->createCell('contact_name', $user['contact_name']);
        echo '</tr>';
    }
} else {
	echo '<tr><td colspan="'.count($fieldNames).'">' . $AppUI->_('No data available') . '</td></tr>';
}
?>
</table>