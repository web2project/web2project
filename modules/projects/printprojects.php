<?php /* $Id$ $URL$ */
global $AppUI, $w2Pconfig;
// check permissions for this module
$perms = &$AppUI->acl();
$canView = $perms->checkModule($m, 'view');

if (!$canView) {
	$AppUI->redirect('m=public&a=access_denied');
}
// load the companies class to retrieved denied companies
require_once ($AppUI->getModuleClass('companies'));

// End of project status update
/*if (isset( $_POST['projsearchtext'] )) {
$AppUI->setState( 'projsearchtext', $_POST['projsearchtext']);
} */

$search_text = $AppUI->getState('projsearchtext') ? $AppUI->getState('projsearchtext') : '';

$projectDesigner = $AppUI->getState('ProjIdxProjectDesigner') !== null ? $AppUI->getState('ProjIdxProjectDesigner') : 0;

// retrieve any state parameters
if (isset($_GET['tab'])) {
	$AppUI->setState('ProjIdxTab', w2PgetParam($_GET, 'tab', null));
}
$tab = $AppUI->getState('ProjIdxTab') !== null ? $AppUI->getState('ProjIdxTab') : 1;
$active = intval(!$AppUI->getState('ProjIdxTab'));

if (isset($_POST['company_id'])) {
	$AppUI->setState('ProjIdxCompany', intval($_POST['company_id']));
}
$company_id = $AppUI->getState('ProjIdxCompany') !== null ? $AppUI->getState('ProjIdxCompany') : $AppUI->user_company;

$company_prefix = 'company_';

if (isset($_POST['department'])) {
	$AppUI->setState('ProjIdxDepartment', $_POST['department']);

	//if department is set, ignore the company_id field
	unset($company_id);
}
$department = $AppUI->getState('ProjIdxDepartment') !== null ? $AppUI->getState('ProjIdxDepartment') : $company_prefix . $AppUI->user_company;

//if $department contains the $company_prefix string that it's requesting a company and not a department.  So, clear the
// $department variable, and populate the $company_id variable.
if (!(strpos($department, $company_prefix) === false)) {
	$company_id = substr($department, strlen($company_prefix));
	$AppUI->setState('ProjIdxCompany', $company_id);
	unset($department);
}

if (isset($_GET['orderby'])) {
	$orderdir = $AppUI->getState('ProjIdxOrderDir') ? ($AppUI->getState('ProjIdxOrderDir') == 'asc' ? 'desc' : 'asc') : 'desc';
	$AppUI->setState('ProjIdxOrderBy', w2PgetParam($_GET, 'orderby', null));
	$AppUI->setState('ProjIdxOrderDir', $orderdir);
}
$orderby = $AppUI->getState('ProjIdxOrderBy') ? $AppUI->getState('ProjIdxOrderBy') : 'project_end_date';
$orderdir = $AppUI->getState('ProjIdxOrderDir') ? $AppUI->getState('ProjIdxOrderDir') : 'asc';

if (isset($_POST['project_owner'])) { // this means that
	$AppUI->setState('ProjIdxowner', $_POST['project_owner']);
}
$owner = $AppUI->getState('ProjIdxowner');

// collect the full projects list data via function in projects.class.php
projects_list_data();


?>
<style type="text/css">
/* Standard table 'spreadsheet' style */
TABLE.prjprint {
	background: #ffffff;
}

TABLE.prjprint TH {
	background-color: #ffffff;
	color: black;
	list-style-type: disc;
	list-style-position: inside;
	border:solid 1px;
	font-weight: normal;
	font-size:13px;
}

TABLE.prjprint TD {
	background-color: #ffffff;
	font-size:13px;
}

TABLE.prjprint TR {
	padding:5px;
}
	
</style>
<table width="100%" class="prjprint">

<tr>
	<td style="border: outset #d1d1cd 1px;" colspan="3">  
		<table border="0" cellpadding="0" cellspacing="0" width="100%" class="prjprint">	
            <tr>
            	<td width="22">
            	&nbsp;
            	</td>
            	<td align="center"  colspan="2">
            	<?php
echo '<strong> Projects List <strong>';
?>
            	</td>
      	</tr>
      	</table>
	</td>
</tr>
<tr>
	<td style="border: outset #d1d1cd 1px;" colspan="3">  
		<table border="0" cellpadding="0" cellspacing="0" width="100%" class="prjprint">	
            <tr>
            	<td width="22">
            	&nbsp;
            	</td>
            	<td align="center"  colspan="2">
            	<?php
$project_types = w2PgetSysVal('ProjectStatus');

$active = 0;
$archived = 0;

foreach ($project_types as $key => $value) {
	$counter[$key] = 0;
	if (is_array($projects)) {
		foreach ($projects as $p) {
			if ($p['project_status'] == $key && $p['project_active'] > 0) {
				++$counter[$key];
			}
		}
	}
	$project_types[$key] = $AppUI->_($project_types[$key], UI_OUTPUT_RAW) . ' (' . $counter[$key] . ')';
}

if (is_array($projects)) {
	foreach ($projects as $p) {
		if ($p['project_active'] == 0) {
			++$archived;
		} else {
			++$active;
		}
	}
}

$fixed_project_type_file = array($AppUI->_('In Progress', UI_OUTPUT_RAW) . ' (' . $active . ')' => 'vw_idx_active', $AppUI->_('Complete', UI_OUTPUT_RAW) . ' (' . $complete . ')' => 'vw_idx_complete', $AppUI->_('Archived', UI_OUTPUT_RAW) . ' (' . $archive . ')' => 'vw_idx_archived');
// we need to manually add Archived project type because this status is defined by
// other field (Active) in the project table, not project_status
$project_types[] = $AppUI->_('Archived', UI_OUTPUT_RAW) . ' (' . $archived . ')';

// Only display the All option in tabbed view, in plain mode it would just repeat everything else
// already in the page
$tabBox = new CTabBox('?m=projects', W2P_BASE_DIR . '/modules/projects/', $tab);
// This will overwrited the initial tabs, so we need to add that separately.
$allactive = (int)count($projects) - (int)($archived);
array_unshift($project_types, $AppUI->_('All Projects', UI_OUTPUT_RAW) . ' (' . count($projects) . ')', $AppUI->_('All Active', UI_OUTPUT_RAW) . ' (' . $allactive . ')');

//Tabbed view
$currentTabId = ($AppUI->getState('ProjIdxTab') !== null ? $AppUI->getState('ProjIdxTab') : 0);

$show_all_projects = false;
if ($currentTabId == 0 || $currentTabId == -1) {
	$show_all_projects = true;
	//set it to 0 again in case we are on a flat view
	$currentTabId == 0;
}

$project_status_filter = $currentTabId - 1;

$currentTabName = $project_types[$currentTabId];
echo '<strong>' . $AppUI->_('Status') . ' (' . $AppUI->_('Records') . '): ' . $currentTabName . '<strong>';
?>
            	</td>
      	</tr>
      	</table>
	</td>
</tr>
<tr>
	<td style="border: outset #d1d1cd 1px;" colspan="3">  
		<table border="0" cellpadding="0" cellspacing="0" width="100%" class="prjprint">	
            <tr>
            	<td align="center"  colspan="2">
            	<?php
echo '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;' . $AppUI->_('Search') . ':&nbsp;' . '<input type="text" disabled class="text" SIZE="20" name="projsearchtext" onChange="document.searchfilter.submit();" value=' . "'$search_text'" . 'title="' . $AppUI->_('Search in name and description fields') . '"/>&nbsp;';

$q = new DBQuery();
$q->addTable('projects', 'p');
$q->addQuery('user_id, concat(contact_first_name, \' \', contact_last_name)');
$q->leftJoin('users', 'u', 'u.user_id = p.project_owner');
$q->leftJoin('contacts', 'c', 'c.contact_id = u.user_contact');
$q->addOrder('contact_first_name, contact_last_name');
$q->addWhere('user_id > 0');
$q->addWhere('p.project_owner IS NOT NULL');
$user_list = array(0 => '(all)');
$user_list = $user_list + $q->loadHashList();
echo $AppUI->_('Owner') . ':&nbsp;' . arraySelect($user_list, 'project_owner', 'size="1" disabled class="text"', $project_owner, false);

$q = new DBQuery();
$q->addTable('projects', 'p');
$q->addQuery('user_id, concat(contact_first_name, \' \', contact_last_name)');
$q->leftJoin('users', 'u', 'u.user_id = p.project_owner');
$q->leftJoin('contacts', 'c', 'c.contact_id = u.user_contact');
$q->addOrder('contact_first_name, contact_last_name');
$q->addWhere('user_id > 0');
$user_list = array(0 => '(all)');
$user_list = $user_list + $q->loadHashList();

// requestors combo
/*$q = new DBQuery();
$q->addTable('projects','p');
$q->addQuery('user_id, concat(contact_first_name, \' \', contact_last_name)');
$q->addJoin('users', 'u', 'u.user_id = p.project_requested_by', 'inner');
$q->addJoin('contacts', 'c', 'c.contact_id = u.user_contact', 'inner');
$q->addOrder('contact_first_name, contact_last_name');
$q->addWhere('p.project_requested_by<>0');
$req_list = array (0 =>'(all)');
$req_list = $req_list + $q->loadHashList();
echo $AppUI->_('Requested By') . ':&nbsp;'.
arraySelect($req_list, "project_requested_by", "size='1' class='text' disabled", $project_requested_by, false);*/

echo $AppUI->_('Company') . ':&nbsp;' . str_replace('<select', '<select disabled="disabled"', $buffer);
?>            
            	</td>
      	</tr>
      	</table>
	</td>
</tr>
<?php
require (W2P_BASE_DIR . '/modules/projects/vw_projects.php');
?>
</table>