<?php
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}
// @todo    convert to template

$tab = $AppUI->processIntState('ProjIdxTab', $_GET, 'tab', 1);

$perms = &$AppUI->acl();
$canView = canView('projects');

if (!$canView) {
	$AppUI->redirect(ACCESS_DENIED);
}

$search_text = $AppUI->getState('projsearchtext') ? $AppUI->getState('projsearchtext') : '';

$company_id = $AppUI->processIntState('ProjIdxCompany', $_GET, 'company_id', $AppUI->user_company);
$orderby = $AppUI->processIntState('ProjIdxOrderBy', $_GET, 'orderby', 'project_end_date');
$project_type = $AppUI->processIntState('ProjIdxType', $_GET, 'project_type', -1);
$owner = $AppUI->processIntState('ProjIdxowner', $_GET, 'project_owner', -1);

$orderdir = $AppUI->getState('ProjIdxOrderDir') ? $AppUI->getState('ProjIdxOrderDir') : 'asc';
if (isset($_GET['orderby'])) {
	if ($AppUI->getState('ProjIdxOrderDir') == 'asc') {
		$orderdir = 'desc';
	} else {
		$orderdir = 'asc';
	}
}
$AppUI->setState('ProjIdxOrderDir', $orderdir);

// collect the full projects list data via function in projects.class.php
$projects = projects_list_data();

$project_types = w2PgetSysVal('ProjectType');
$project_statuses = w2PgetSysVal('ProjectStatus');
?>
<style type="text/css">
/* Standard table 'spreadsheet' style */
.prjprint {
	background: #ffffff;
    border-collapse: collapse;
    font-size: 13px;
    padding: 0;
    width: 100%;
}

.prjprint TH {
    border:solid 1px;
	color: black;
    font-weight: bold;
	list-style-type: disc;
	list-style-position: inside;
}
.prjprint TD {
	font-size: 13px;
    text-align: center;
}
</style>
<table class="prjprint">
	<tr><th><?php echo $AppUI->_('Project List'); ?></th></tr>
	<tr>
		<th>
            <?php
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
            echo $AppUI->_('Status') . ' (' . $AppUI->_('Records') . '): ' . $currentTabName;
            ?>
		</th>
	</tr>
	<tr>
		<th>
            <?php
            $txt = '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;' . $AppUI->_('Search') . ':&nbsp;' . '<input type="text" disabled class="text" SIZE="20" name="projsearchtext" onChange="document.searchfilter.submit();" value=' . "'$search_text'" . 'title="' . $AppUI->_('Search in name and description fields') . '"/>&nbsp;';
            $txt .= $AppUI->_('Type') . ':&nbsp;' . arraySelect($project_types, 'project_type', 'size="1" disabled class="text"', $project_type, false);
            $user_list = array(0 => '(all)') + CProject::getOwners();
            $txt .= $AppUI->_('Owner') . ':&nbsp;' . arraySelect($user_list, 'project_owner', 'size="1" disabled class="text"', $owner, false);
            $txt .= $AppUI->_('Company') . ':&nbsp;' . str_replace('<select', '<select disabled="disabled"', $buffer);
            echo $txt;
            ?>
		</th>
	</tr>
</table>
<?php

require (W2P_BASE_DIR . '/modules/projects/vw_projects.php');