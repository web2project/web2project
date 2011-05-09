<?php /* $Id: index.php 1522 2010-12-08 05:08:07Z caseydk $ $URL: https://web2project.svn.sourceforge.net/svnroot/web2project/trunk/modules/reports/index.php $ */
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}

$project_id = (int) w2PgetParam($_REQUEST, 'project_id', 0);
$report_type = w2PgetParam($_REQUEST, 'report_type', '');

$canReport = canView('reports');
$canRead = canView('projects', $project_id);
if (!$canReport || !$canRead) {
	$AppUI->redirect('m=public&a=access_denied');
}

$project_list = array('0' => $AppUI->_('All', UI_OUTPUT_RAW));

$projectObj = new CProject();
$projectList = $projectObj->getAllowedProjects($AppUI->user_id, false);

$company = new CCompany();
$companyList = $company->getCompanies($AppUI);

foreach ($projectList as $pr) {
    if ($pr['project_id'] == $project_id) {
        $display_project_name = '(' . $companyList[$pr['project_company']]['company_name'] . ') ' . $pr['project_name'];
    }
    $project_list[$pr['project_id']] = '(' . $companyList[$pr['project_company']]['company_name'] . ') ' . $pr['project_name'];
}

if (!$suppressHeaders) {
?>
<script language="javascript" type="text/javascript">
                                                                                
function changeIt() {
        var f=document.changeMe;
        f.submit();
}
</script>

<?php
}
// get the prefered date format
$df = $AppUI->getPref('SHDATEFORMAT');

$reports = $AppUI->readFiles(W2P_BASE_DIR . '/modules/reports/reports', '\.php$');

// setup the title block
if (!$suppressHeaders) {
	$titleBlock = new CTitleBlock('Project Reports', 'printer.png', $m, $m . '.' . $a);
	$titleBlock->addCrumb('?m=projects', 'projects list');
	if ($project_id) {
		$titleBlock->addCrumb('?m=projects&a=view&project_id=' . $project_id, 'view this project');
	}
	if ($report_type) {
		$titleBlock->addCrumb('?m=reports&project_id=' . $project_id, 'reports index');
	}
	$titleBlock->show();
}

$report_type_var = w2PgetParam($_GET, 'report_type', '');
if (!empty($report_type_var)) {
	$report_type_var = '&report_type=' . $report_type;
}

if (!$suppressHeaders) {
	if (!isset($display_project_name)) {
		$display_project_name = $AppUI->_('All');
	}
	echo $AppUI->_('Selected Project') . ': <b>' . $display_project_name . '</b>';
?>
<form name="changeMe" action="./index.php?m=reports<?php echo $report_type_var; ?>" method="post" accept-charset="utf-8">
<?php echo $AppUI->_('Projects') . ':' . arraySelect($project_list, 'project_id', 'size="1" class="text" onchange="changeIt();"', $project_id, false); ?>
</form>

<?php
}
if ($report_type) {
	$report_type = $AppUI->checkFileName($report_type);
	$report_type = str_replace(' ', '_', $report_type);
	require W2P_BASE_DIR . '/modules/reports/reports/' . $report_type . '.php';
} else {
	if (function_exists('styleRenderBoxTop')) {
		echo styleRenderBoxTop();
	}
	$s = '';
	$s .= '<table width="100%" class="std">';
	$s .= '<tr><td><h2>' . $AppUI->_('Reports Available') . '</h2></td></tr>';

	foreach ($reports as $key => $v) {
		$type = str_replace('.php', '', $v);
        $link = 'index.php?m=reports&project_id=' . $project_id . '&report_type=' . $type;

        /*
         * TODO: There needs to be a better approach to adding the suppressHeaders
         *   part but I can't come up with anything better at the moment..
         *
         *   ~ caseydk, 08 May 2011
         */
        $suppressHeaderReports = array('completed', 'upcoming', 'overdue');
        if (in_array($type, $suppressHeaderReports)) {
            $link .= '&suppressHeaders=1';
        }

		$s .= '<tr><td><a href="'.$link.'">'.$AppUI->_($type.'_name') . '</a></td>';
		$s .= '<td>' . $AppUI->_($type.'_desc') . '</td></tr>';
	}
	$s .= '</table>';
	echo $s;
}