<?php
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}
// @todo    convert to template

// modified later by Pablo Roca (proca) in 18 August 2003 - added page support
// Files modules: index page re-usable sub-table
global $AppUI, $company_id, $project_id, $task_id, $tab;
global $currentTabId, $m, $showProject, $xpg_min, $xpg_pagesize, $page;

$category_id = ($m == 'files') ? $tab-1 : $tab;
$page = w2PgetParam($_GET, 'page', 1);
if (!isset($project_id)) {
	$project_id = w2PgetParam($_REQUEST, 'project_id', 0);
}

$xpg_pagesize = w2PgetConfig('page_size', 50);
$xpg_min = $xpg_pagesize * ($page - 1); // This is where we start our record set from

// counts total recs from selection
$fileList = CFile::getFileList($AppUI, $company_id, $project_id, $task_id, $category_id);
$xpg_totalrecs = count($fileList);
$pageNav = buildPaginationNav($AppUI, $m, $category_id, $xpg_totalrecs, $xpg_pagesize, $page);
echo $pageNav;
?>
<script language="javascript" type="text/javascript">
function expand(id){
  var element = document.getElementById(id);
  element.style.display = (element.style.display == '' || element.style.display == 'none') ? 'block' : 'none';
}
</script>
<table class="tbl list">
    <?php
    $showProject = true;

    echo displayFiles($AppUI, -1, $task_id, $project_id, $company_id, $category_id);
    ?>
</table>
<?php
echo $pageNav;