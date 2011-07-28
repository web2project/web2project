<?php /* $Id$ $URL$ */
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}

##
##	Companies: View Departments sub-table
##

global $AppUI, $company_id, $canEdit;

$depts = CCompany::getDepartments($AppUI, $company_id);

$s = '<table width="100%" border="0" cellpadding="2" cellspacing="1" class="tbl">';
$s .= '<tr>';

if (count($depts)) {
	$s .= '<th>&nbsp;</th>';
	$s .= '<th width="100%">' . $AppUI->_('Name') . '</th>';
	$s .= '<th>' . $AppUI->_('Users') . '</th>';
} else {
	$s .= '<td>' . $AppUI->_('No data available') . '</td>';
}

$s .= '</tr>';
echo $s;

if (count($depts)) {
	foreach ($depts as $dept) {
		if ($dept['dept_parent'] == 0) {
			showchilddept_comp($dept);
			findchilddept_comp($depts, $dept['dept_id']);
		}
	}
}

echo '
<tr>
	<td colspan="3" nowrap="nowrap" rowspan="99" align="right" valign="top" style="background-color:#ffffff">';
if ($canEdit) {
	echo '<input type="button" class=button value="' . $AppUI->_('new department') . '" onclick="javascript:window.location=\'./index.php?m=departments&amp;a=addedit&amp;company_id=' . $company_id . '\';" />';
}
echo '
	</td>
</tr>
</table>';