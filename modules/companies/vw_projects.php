<?php
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}

##
##	Companies: View Projects sub-table
##
global $AppUI, $company_id, $pstatus, $w2Pconfig, $tab;

$sort = w2PgetParam($_GET, 'sort', 'project_name');
if ($sort == 'project_priority') {
	$sort .= ' DESC';
}

$df = $AppUI->getPref('SHDATEFORMAT');

$projects = CCompany::getProjects($AppUI, $company_id, !$tab, $sort);

$fieldList = array();
$fieldNames = array();

$module = new w2p_Core_Module();
$fields = $module->loadSettings('projects', 'company_view');
if (count($fields) > 0) {
    $fieldList = array_keys($fields);
    $fieldNames = array_values($fields);
} else {
    // TODO: This is only in place to provide an pre-upgrade-safe
    //   state for versions earlier than v3.0
    //   At some point at/after v4.0, this should be deprecated
    $fieldList = array('project_priority', 'project_name', 'user_username',
        'project_start_date', 'project_status', 'project_target_budget');
    $fieldNames = array('P', 'Name', 'Owner', 'Started', 'Status',
        'Budget');

    $module->storeSettings('projects', 'company_view', $fieldList, $fieldNames);
}
?>
<a name="projects-company_view"> </a>
<table class="tbl list">
    <tr>
        <?php
        foreach ($fieldNames as $index => $name) {
            ?><th>
                <a href="?m=companies&a=view&company_id=<?php echo $company_id; ?>&sort=<?php echo $fieldList[$index]; ?>#projects-company_view" class="hdr">
                    <?php echo $AppUI->_($fieldNames[$index]); ?>
                </a>
            </th><?php
        }
        ?>
    </tr>
<?php
if (count($projects) > 0) {
	$htmlHelper = new w2p_Output_HTMLHelper($AppUI);

    $pstatus = w2PgetSysVal('ProjectStatus');
    $countries = w2PgetSysVal('GlobalCountries');
    $company_types = w2PgetSysVal('CompanyType');
    $customLookups = array('project_status' => $pstatus, 
        'company_type' => $company_types, 'company_country' => $countries);

    foreach ($projects as $row) {
        echo '<tr>';
        $htmlHelper->stageRowData($row);
        foreach ($fieldList as $index => $column) {
            echo $htmlHelper->createCell($fieldList[$index], $row[$fieldList[$index]], $customLookups);
        }
        echo '</tr>';
	}
} else {
	echo '<tr><td colspan="'.count($fieldNames).'">' . $AppUI->_('No data available') . '</td></tr>';
}
?>
</table>