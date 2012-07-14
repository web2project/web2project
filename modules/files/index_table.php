<?php /* $Id$ $URL$ */
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}

/* FILES $Id$ */
// modified later by Pablo Roca (proca) in 18 August 2003 - added page support
// Files modules: index page re-usable sub-table
global $AppUI, $deny1, $canRead, $canEdit, $canAdmin;
global $company_id, $project_id, $task_id;

global $currentTabId;
global $currentTabName;
global $tabbed, $m;

$tab = $currentTabId;

// add to allow for returning to other modules besides Files
$current_uriArray = parse_url($_SERVER['REQUEST_URI']);
$current_uri = $current_uriArray['query'];

$tab = ($m == 'files') ? $tab-1 : -1;
$page = w2PgetParam($_GET, 'page', 1);
if (!isset($project_id)) {
	$project_id = w2PgetParam($_REQUEST, 'project_id', 0);
}
if (!isset($showProject)) {
	$showProject = true;
}

$xpg_pagesize = w2PgetConfig('page_size', 50);
$xpg_min = $xpg_pagesize * ($page - 1); // This is where we start our record set from

// load the following classes to retrieved denied records

$project = new CProject();
$task = new CTask();

$df = $AppUI->getPref('SHDATEFORMAT');
$tf = $AppUI->getPref('TIMEFORMAT');

if (($company_id || $project_id || $task_id) && !($m == 'files')) {
	$catsql = false;
} elseif ($tabbed) {
	if ($tab <= 0) {
		$catsql = false;
	} else {
		$catsql = 'file_category = ' . ($tab-1);
	}
} else {
	if ($tab < 0) {
		$catsql = false;
	} else {
		$catsql = 'file_category = ' . $tab;
	}
}

// Fetch permissions once for all queries
$allowedProjects = $project->getAllowedSQL($AppUI->user_id, 'file_project');
$allowedTasks = $task->getAllowedSQL($AppUI->user_id, 'file_task');

// SQL text for count the total recs from the selected option
$q = new w2p_Database_Query;
$q->addQuery('count(file_id)');
$q->addTable('files', 'f');
$q->addJoin('projects', 'p', 'p.project_id = file_project');
$q->addJoin('tasks', 't', 't.task_id = file_task');
$q->leftJoin('project_departments', 'project_departments', 'p.project_id = project_departments.project_id OR project_departments.project_id IS NULL');
$q->leftJoin('departments', 'departments', 'departments.dept_id = project_departments.department_id OR dept_id IS NULL');
if (count($allowedProjects)) {
	$q->addWhere('( ( ' . implode(' AND ', $allowedProjects) . ') OR file_project = 0 )');
}
if (count($allowedTasks)) {
	$q->addWhere('( ( ' . implode(' AND ', $allowedTasks) . ') OR file_task = 0 )');
}
if ($catsql) {
	$q->addWhere($catsql);
}
if ($company_id) {
	$q->addWhere('project_company = ' . (int)$company_id);
}
if ($project_id) {
	$q->addWhere('file_project = ' . (int)$project_id);
}
if ($task_id) {
	$q->addWhere('file_task = ' . (int)$task_id);
}
$q->addGroup('file_version_id');

// counts total recs from selection
$xpg_totalrecs = count($q->loadList());
$pageNav = buildPaginationNav($AppUI, $m, $tab, $xpg_totalrecs, $xpg_pagesize, $page);
echo $pageNav;
?>
<script language="javascript" type="text/javascript">
function expand(id){
  var element = document.getElementById(id);
  element.style.display = (element.style.display == '' || element.style.display == 'none') ? 'block' : 'none';
}
</script>
<table width="100%" border="0" cellpadding="2" cellspacing="1" class="tbl list">
    <?php 
    global $showProject;
    $showProject = true;
    echo displayFiles($AppUI, 0, $task_id, $project_id, $company_id);
    ?>
</table>
<?php
echo $pageNav;