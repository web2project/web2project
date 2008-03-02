<?php /* $Id$ $URL$ */
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}

global $search_string;
global $owner_filter_id;
global $currentTabId;
global $currentTabName;
global $tabbed;
global $type_filter;
global $orderby;
global $orderdir;

// load the company types

$types = w2PgetSysVal('DepartmentType');
// get any records denied from viewing

$obj = new CDepartment();
$allowedDepts = $obj->getAllowedRecords($AppUI->user_id, 'dept_id, dept_name');

$dept_type_filter = $currentTabId;
//Not Defined
$deptsType = true;
if ($currentTabName == 'All Departments') {
	$deptssType = false;
}
if ($currentTabName == 'Not Defined') {
	$dept_type_filter = 0;
}

// retrieve list of records
$q = new DBQuery;
$q->addTable('departments');
$q->addQuery('departments.*, COUNT(ct.contact_department) dept_users, count(distinct p.project_id) as countp, count(distinct p2.project_id) as inactive, con.contact_first_name, con.contact_last_name');
$q->addJoin('project_departments', 'pd', 'pd.department_id = dept_id');
$q->addJoin('projects', 'p', 'pd.project_id = p.project_id AND p.project_active <> 0');
$q->leftJoin('users', 'u', 'dept_owner = u.user_id');
$q->leftJoin('contacts', 'con', 'u.user_contact = con.contact_id');
$q->addJoin('projects', 'p2', 'pd.project_id = p2.project_id AND p2.project_active = 0');
$q->addJoin('contacts', 'ct', 'ct.contact_department = dept_id');
$q->addGroup('dept_id');
$q->addOrder('dept_parent, dept_name');
if (count($allowedDepts) > 0) {
	$q->addWhere('dept_id IN (' . implode(',', array_keys($allowedDepts)) . ')');
} else {
	$q->addWhere('0=1');
}
if (($deptsType && $currentTabId && $tabbed) || (!$tabbed)) {
	$q->addWhere('dept_type = ' . (int)$dept_type_filter);
}
if ($search_string != '') {
	$q->addWhere('dept_name LIKE "%'.$search_string.'%"');
}
if ($owner_filter_id > 0) {
	$q->addWhere('dept_owner = '.$owner_filter_id);
}
$q->addGroup('dept_id');
$q->addOrder($orderby . ' ' . $orderdir);
$rows = $q->loadList();
?>

<?php
echo '
<table width="100%" border="0" cellpadding="2" cellspacing="1" class="tbl">
<tr>
	<th nowrap="nowrap">
		<a href="?m=companies&orderby=company_name" class="hdr">' . $AppUI->_('Department Name') . '</a>
	</th>
	<th nowrap="nowrap">
		<a href="?m=companies&orderby=countp" class="hdr">' . $AppUI->_('Active Projects') . '</a>
	</th>
	<th nowrap="nowrap">
		<a href="?m=companies&orderby=inactive" class="hdr">' . $AppUI->_('Archived Projects') . '</a>
	</th>
	<th nowrap="nowrap">
		<a href="?m=companies&orderby=company_type" class="hdr">' . $AppUI->_('Type') . '</a>
	</th>
</tr>';

if (count($rows)) {
	foreach ($rows as $row) {
		if ($row['dept_parent'] == 0) {
			showchilddept($row);
			findchilddept($rows, $row['dept_id']);
		}
		$none = false;
		echo '<tr>';
		echo '<td>' . (trim($row['dept_description']) ? w2PtoolTip($row['dept_name'], $row['dept_description']) : '') . '<a href="./index.php?m=departments&a=view&dept_id=' . $row['dept_id'] . '" >' . $row['dept_name'] . '</a>' . (trim($row['dept_description']) ? w2PendTip() : '') . '</td>';
		echo '<td width="125" align="right" nowrap="nowrap">' . $row['countp'] . '</td>';
		echo '<td width="125" align="right" nowrap="nowrap">' . $row['inactive'] . '</td>';
		echo '<td align="left" nowrap="nowrap">' . $AppUI->_($types[$row['dept_type']]) . '</td>';
		echo '</tr>';
	}
} else {
	echo '<td colspan="4">' . $AppUI->_('No data available') . '</td>';
}

echo '</tr>';
echo '
	</td>
</tr>
</table>';