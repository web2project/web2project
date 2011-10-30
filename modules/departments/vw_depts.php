<?php /* $Id$ $URL$ */
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}

global $search_string, $owner_filter_id, $currentTabId, $orderby, $orderdir;

$types = w2PgetSysVal('DepartmentType');
$dept_type_filter = $currentTabId-1;

// get any records denied from viewing

$dept = new CDepartment();
$deptList = $dept->getFilteredDepartmentList($AppUI, $dept_type_filter, $search_string, $owner_filter_id, $orderby, $orderdir);
$htmlHelper = new w2p_Output_HTMLHelper($AppUI);
?>
<table width="100%" border="0" cellpadding="2" cellspacing="1" class="tbl">
    <tr>
        <?php
        $fieldList = array();
        $fieldNames = array();
        $fields = w2p_Core_Module::getSettings('departments', 'index_table');
        if (count($fields) > 0) {
            foreach ($fields as $field => $text) {
                $fieldList[] = $field;
                $fieldNames[] = $text;
            }
        } else {
            // TODO: This is only in place to provide an pre-upgrade-safe 
            //   state for versions earlier than v3.0
            //   At some point at/after v4.0, this should be deprecated
            $fieldList = array('dept_name', 'countp', 'inactive', 'dept_type');
            $fieldNames = array('Department Name', 'Active Projects', 'Archived Projects', 'Type');
        }
//TODO: The link below is commented out because this module doesn't support sorting... yet.
        foreach ($fieldNames as $index => $name) {
            ?><th nowrap="nowrap">
<!--                <a href="?m=departments&orderby=<?php echo $fieldList[$index]; ?>" class="hdr">-->
                    <?php echo $AppUI->_($fieldNames[$index]); ?>
<!--                </a>-->
            </th><?php
        }
        ?>
    </tr>
<?php
if (count($deptList) > 0) {
	$displayList = array();
    foreach ($deptList as $dept) {
		if ($dept['dept_parent'] == 0) {
			findchilddept($deptList, $dept['dept_id']);
		}
		echo '<tr><td>' . (mb_trim($dept['dept_desc']) ? w2PtoolTip($dept['dept_name'], $dept['dept_desc']) : '') . '<a href="./index.php?m=departments&a=view&dept_id=' . $dept['dept_id'] . '" >' . $dept['dept_name'] . '</a>' . (mb_trim($dept['dept_desc']) ? w2PendTip() : '') . '</td>';
        echo $htmlHelper->createCell('project_count', $dept['countp']);
        echo $htmlHelper->createCell('project_count', $dept['inactive']);
        echo $htmlHelper->createCell('dept_type', $AppUI->_($types[$dept['dept_type']]));
        echo '</tr>';
	}
} else {
	echo '<tr><td colspan="4">' . $AppUI->_('No data available') . '</td></tr>';
}
?>
</table>