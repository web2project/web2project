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

$company_type_filter = $currentTabId;
$company_type_filter = ($currentTabName == 'Not Applicable') ? 0 : $company_type_filter;
$company_type_filter = ($currentTabName == 'All Companies') ? -1 : $company_type_filter; 

$company = new CCompany();
$allowedCompanies = $company->getAllowedRecords($AppUI->user_id, 'company_id, company_name');

$companyList = $company->getCompanyList(null, $company_type_filter, $search_string, $owner_filter_id, $orderby, $orderdir);

$fieldList = array();
$fieldNames = array();

$module = new w2p_Core_Module();
$fields = $module->loadSettings('companies', 'index_list');
if (count($fields) > 0) {
    $fieldList = array_keys($fields);
    $fieldNames = array_values($fields);
} else {
    // TODO: This is only in place to provide an pre-upgrade-safe
    //   state for versions earlier than v3.0
    //   At some point at/after v4.0, this should be deprecated
    $fieldList = array('company_name', 'countp', 'inactive', 'company_type');
    $fieldNames = array('Company Name', 'Active Projects', 'Archived Projects', 'Type');

    $module->storeSettings('companies', 'index_list', $fieldList, $fieldNames);
}
?>
<table class="tbl list">
    <tr>
        <?php
        foreach ($fieldNames as $index => $name) {
            ?><th>
                <a href="?m=companies&orderby=<?php echo $fieldList[$index]; ?>" class="hdr">
                    <?php echo $AppUI->_($fieldNames[$index]); ?>
                </a>
            </th><?php
        }
        ?>
    </tr>
    <?php
        if (count($companyList) > 0) {
            $htmlHelper = new w2p_Output_HTMLHelper($AppUI);

            $company_types = w2PgetSysVal('CompanyType');
            $customLookups = array('company_type' => $company_types);

            foreach ($companyList as $row) {
                echo '<tr>';
                $htmlHelper->stageRowData($row);
//TODO: The company_name used to have a Tool Tip with the description.. let's see if anyone notices/cares.
                foreach ($fieldList as $index => $column) {
                    echo $htmlHelper->createCell($fieldList[$index], $row[$fieldList[$index]], $customLookups);
                }
                echo '</tr>';
                
            }

        } else {
            echo '<tr><td colspan="5">' . $AppUI->_('No companies available') . '</td></tr>';
        }
    ?>
</table>