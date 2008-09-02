<?php /* $Id$ $URL$ */
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}

##
##	Companies: View Archived Projects sub-table
##

global $AppUI, $company_id;

$q = new DBQuery;
$q->addTable('projects', 'pr');
$q->addQuery('pr.project_id, project_name, project_start_date, project_status, project_target_budget, project_start_date, project_priority, contact_first_name, contact_last_name');
$q->leftJoin('users', 'u', 'u.user_id = pr.project_owner');
$q->leftJoin('contacts', 'con', 'u.user_contact = con.contact_id');
$q->addWhere('pr.project_company = ' . (int)$company_id);

include_once ($AppUI->getModuleClass('projects'));
$projObj = new CProject();
$projObj->setAllowedSQL($AppUI->user_id, $q, null, 'pr');

$q->addWhere('pr.project_active = 0');
$q->addOrder('project_name');
$s = '';

if (!($rows = $q->loadList())) {
	$s .= $AppUI->_('No data available') . '<br />' . $AppUI->getMsg();
} else {
	$s .= '<tr>' . '<th>' . $AppUI->_('Name') . '</td>' . '<th>' . $AppUI->_('Owner') . '</td>' . '</tr>';

	foreach ($rows as $row) {
		$s .= '<tr><td>';
		$s .= '<a href="?m=projects&a=view&project_id=' . $row['project_id'] . '">' . $row['project_name'] . '</a>';
		$s .= '<td>' . $row['contact_first_name'] . '&nbsp;' . $row['contact_last_name'] . '</td>';
		$s .= '</tr>';
	}
}
echo '<table cellpadding="2" cellspacing="1" border="0" width="100%" class="tbl">' . $s . '</table>';
?>