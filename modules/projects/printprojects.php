<?php /* $Id$ $URL$ */
global $AppUI, $w2Pconfig;
// check permissions for this module
$perms = &$AppUI->acl();
$canView = canView('projects');

if (!$canView) {
	$AppUI->redirect(ACCESS_DENIED);
}

$search_text = $AppUI->getState('projsearchtext') ? $AppUI->getState('projsearchtext') : '';

$projectDesigner = $AppUI->getState('ProjIdxProjectDesigner') !== null ? $AppUI->getState('ProjIdxProjectDesigner') : 0;

$tab = $AppUI->processIntState('ProjIdxTab', $_GET, 'tab', 1);
$active = intval(!$AppUI->getState('ProjIdxTab'));

$company_id = $AppUI->processIntState('ProjIdxCompany', $_POST, 'company_id', $AppUI->user_company);

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

$project_type = $AppUI->getState('ProjIdxType') !== null ? $AppUI->getState('ProjIdxType') : -1;
$project_types = array(-1 => '(all)') + w2PgetSysVal('ProjectType');

// collect the full projects list data via function in projects.class.php
$projects = projects_list_data();


?>
<style type="text/css">
/* Standard table 'spreadsheet' style */
.prjprint {
	background: #ffffff;
    font-size:13px;
    width: 100%;
}

.prjprint TH {
	background-color: #ffffff;
    border:solid 1px;
	color: black;
    font-weight: normal;
	list-style-type: disc;
	list-style-position: inside;
}
.prjprint TD {
	background-color: #ffffff;
	font-size:13px;
    text-align: center;
}
.prjprint a {
    color: black;
    text-decoration: none;
}
.prjprint ._identifier {
    background-color: #0033FF;
    border: 2px outset #EEEEEE;
    text-align: center;
    width: 55px;
}
.prjprint ._name {
    text-align: left;
}
</style>
<table width="100%" class="prjprint">
	<tr>
		<td style="border: outset #d1d1cd 1px;" colspan="3">  
			<table border="0" cellpadding="0" cellspacing="0" width="100%" class="prjprint">	
	      <tr>
	      	<td width="22">&nbsp;</td>
	      	<td align="center"  colspan="2">
	      		<?php echo '<strong> Projects List <strong>'; ?>
	      	</td>
	    	</tr>
			</table>
		</td>
	</tr>
	<tr>
		<td style="border: outset #d1d1cd 1px;" colspan="3">  
			<table border="0" cellpadding="0" cellspacing="0" width="100%" class="prjprint">	
	      <tr>
	      	<td width="22">&nbsp;</td>
	      	<td align="center"  colspan="2">
						<?php
							$project_statuses = w2PgetSysVal('ProjectStatus');
							
							$active = 0;
							$archived = 0;
							
							foreach ($project_statuses as $key => $value) {
								$counter[$key] = 0;
								if (is_array($projects)) {
									foreach ($projects as $p) {
										if ($p['project_status'] == $key && $p['project_active'] > 0) {
											++$counter[$key];
										}
									}
								}
								$project_statuses[$key] = $AppUI->_($project_statuses[$key], UI_OUTPUT_RAW) . ' (' . $counter[$key] . ')';
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
	
							$fixed_project_status_file = array($AppUI->_('In Progress', UI_OUTPUT_RAW) . ' (' . $active . ')' => 'vw_idx_active', $AppUI->_('Complete', UI_OUTPUT_RAW) . ' (' . $complete . ')' => 'vw_idx_complete', $AppUI->_('Archived', UI_OUTPUT_RAW) . ' (' . $archive . ')' => 'vw_idx_archived');
							// we need to manually add Archived project type because this status is defined by
							// other field (Active) in the project table, not project_status
							$project_statuses[] = $AppUI->_('Archived', UI_OUTPUT_RAW) . ' (' . $archived . ')';
							
							// Only display the All option in tabbed view, in plain mode it would just repeat everything else
							// already in the page
							$tabBox = new CTabBox('?m=projects', W2P_BASE_DIR . '/modules/projects/', $tab);
							// This will overwrited the initial tabs, so we need to add that separately.
							$allactive = (int)count($projects) - (int)($archived);
							array_unshift($project_statuses, $AppUI->_('All Projects', UI_OUTPUT_RAW) . ' (' . count($projects) . ')', $AppUI->_('All Active', UI_OUTPUT_RAW) . ' (' . $allactive . ')');
							
							//Tabbed view
							$currentTabId = ($AppUI->getState('ProjIdxTab') !== null ? $AppUI->getState('ProjIdxTab') : 0);
							
							$show_all_projects = false;
							if ($currentTabId == 0 || $currentTabId == -1) {
								$show_all_projects = true;
								//set it to 0 again in case we are on a flat view
								$currentTabId == 0;
							}
							
							$project_status_filter = $currentTabId - 1;
							
							$currentTabName = $project_statuses[$currentTabId];
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
										$txt = '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;' . $AppUI->_('Search') . ':&nbsp;' . '<input type="text" disabled class="text" SIZE="20" name="projsearchtext" onChange="document.searchfilter.submit();" value=' . "'$search_text'" . 'title="' . $AppUI->_('Search in name and description fields') . '"/>&nbsp;';
										$txt .= $AppUI->_('Type') . ':&nbsp;' . arraySelect($project_types, 'project_type', 'size="1" disabled class="text"', $project_type, false);

										$user_list = array(0 => '(all)') + CProject::getOwners();

										$txt .= $AppUI->_('Owner') . ':&nbsp;' . arraySelect($user_list, 'project_owner', 'size="1" disabled class="text"', $owner, false);
										
										$txt .= $AppUI->_('Company') . ':&nbsp;' . str_replace('<select', '<select disabled="disabled"', $buffer);
										echo $txt;
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