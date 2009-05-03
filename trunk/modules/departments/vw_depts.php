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

$types = w2PgetSysVal('DepartmentType');
$dept_type_filter = $currentTabId-1;

// get any records denied from viewing

$dept = new CDepartment();
$allowedDepts = $dept->getAllowedRecords($AppUI->user_id, 'dept_id, dept_name');
$deptList = $dept->getFilteredDepartmentList($AppUI, $dept_type_filter, $search_string, $owner_filter_id, $orderby, $orderdir);
?>

<table width="100%" border="0" cellpadding="2" cellspacing="1" class="tbl">
	<tr>
		<th nowrap="nowrap">
			<a href="?m=companies&orderby=company_name" class="hdr"><?php echo $AppUI->_('Department Name'); ?></a>
		</th>
		<th nowrap="nowrap">
			<a href="?m=companies&orderby=countp" class="hdr"><?php echo $AppUI->_('Active Projects'); ?></a>
		</th>
		<th nowrap="nowrap">
			<a href="?m=companies&orderby=inactive" class="hdr"><?php echo $AppUI->_('Archived Projects'); ?></a>
		</th>
		<th nowrap="nowrap">
			<a href="?m=companies&orderby=company_type" class="hdr"><?php echo $AppUI->_('Type'); ?></a>
		</th>
	</tr>
<?php

if (count($deptList) > 0) {
	foreach ($deptList as $dept) {
		if ($dept['dept_parent'] == 0) {
			showchilddept($dept);
			findchilddept($deptList, $dept['dept_id']);
		}
		echo '<tr><td>' . (trim($dept['dept_description']) ? w2PtoolTip($dept['dept_name'], $dept['dept_description']) : '') . '<a href="./index.php?m=departments&a=view&dept_id=' . $dept['dept_id'] . '" >' . $dept['dept_name'] . '</a>' . (trim($dept['dept_description']) ? w2PendTip() : '') . '</td>';
		echo '<td width="125" align="right" nowrap="nowrap">' . $dept['countp'] . '</td>';
		echo '<td width="125" align="right" nowrap="nowrap">' . $dept['inactive'] . '</td>';
		echo '<td align="left" nowrap="nowrap">' . $AppUI->_($types[$dept['dept_type']]) . '</td></tr>';
	}
} else {
	echo '<td colspan="4">' . $AppUI->_('No data available') . '</td>';
}
echo '</tr></td></tr></table>';