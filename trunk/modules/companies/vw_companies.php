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

$types = w2PgetSysVal('CompanyType');

$company_type_filter = $currentTabId;
$company_type_filter = ($currentTabName == 'Not Applicable') ? 0 : $company_type_filter;
$company_type_filter = ($currentTabName == 'All Companies') ? -1 : $company_type_filter; 

$company = new CCompany();
$allowedCompanies = $company->getAllowedRecords($AppUI->user_id, 'company_id, company_name');

$companyList = $company->getCompanyList($AppUI, $company_type_filter, $search_string, $owner_filter_id, $orderby, $orderdir);
?>
<table width="100%" border="0" cellpadding="2" cellspacing="1" class="tbl">
<tr>
	<th nowrap="nowrap">
		<a href="?m=companies&orderby=company_name" class="hdr"><?php echo $AppUI->_('Company Name'); ?></a>
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
	if (count($companyList) > 0) {
		foreach ($companyList as $company) {
			echo '<tr>';
			echo '<td>' . (trim($company['company_description']) ? w2PtoolTip($company['company_name'], $company['company_description']) : '') . '<a href="./index.php?m=companies&a=view&company_id=' . $company['company_id'] . '" >' . $company['company_name'] . '</a>' . (trim($company['company_description']) ? w2PendTip() : '') . '</td>';
			echo '<td width="125" align="right" nowrap="nowrap">' . $company['countp'] . '</td>';
			echo '<td width="125" align="right" nowrap="nowrap">' . $company['inactive'] . '</td>';
			echo '<td align="left" nowrap="nowrap">' . $AppUI->_($types[$company['company_type']]) . '</td>';
			echo '</tr>';
		}
	} else {
		echo '<tr><td colspan="5">' . $AppUI->_('No companies available') . '</td></tr>';
	}
?>
</table>