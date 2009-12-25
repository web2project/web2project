<?php /* $Id$ $URL$ */
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}

##
##	Companies: View User sub-table
##

global $AppUI, $company_id;

$userList = CCompany::getUsers($AppUI, $company_id);

if (count($userList) > 0) {
	?>
		<table width="100%" border="0" cellpadding="2" cellspacing="1" class="tbl">
		<tr>
			<th><?php echo $AppUI->_('Username'); ?></td>
			<th><?php echo $AppUI->_('Name'); ?></td>
		</tr>
		<?php
			$s = '';
			foreach ($userList as $user) {
				$s .= '<tr><td>';
				$s .= '<a href="./index.php?m=admin&a=viewuser&user_id=' . $user['user_id'] . '">' . $user['user_username'] . '</a>';
				$s .= '<td>' . $user['contact_first_name'] . ' ' . $user['contact_last_name'] . '</td>';
				$s .= '</tr>';
			}
			echo $s;
		?>
		</table>
	<?php
} else {
	echo $AppUI->_('No data available') . '<br />' . $AppUI->getMsg();
}