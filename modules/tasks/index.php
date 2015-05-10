<?php
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}

$perms = &$AppUI->acl();
$user_id = (int) w2PgetParam($_POST, 'user_id', $AppUI->user_id);

if (isset($_POST['f'])) {
	$AppUI->setState('TaskIdxFilter', $_POST['f']);
}
$f = $AppUI->getState('TaskIdxFilter') ? $AppUI->getState('TaskIdxFilter') :
        w2PgetConfig('task_filter_default', 'myunfinished');

if (isset($_POST['f2'])) {
	$AppUI->setState('CompanyIdxFilter', $_POST['f2']);
}

$f2 = ($AppUI->getState('CompanyIdxFilter')) ? $AppUI->getState('CompanyIdxFilter') :
        ((w2PgetConfig('company_filter_default', 'user') == 'user') ? $AppUI->user_company : 'allcompanies');

if (isset($_GET['project_id'])) {
	$AppUI->setState('TaskIdxProject', w2PgetParam($_GET, 'project_id', null));
}
$project_id = $AppUI->getState('TaskIdxProject') ? $AppUI->getState('TaskIdxProject') : 0;

// get CCompany() to filter tasks by company
$obj = new CCompany();
$companies = $obj->getAllowedRecords($AppUI->user_id, 'company_id,company_name', 'company_name');
$filters2 = arrayMerge(array('allcompanies' => $AppUI->_('All Companies', UI_OUTPUT_RAW)), $companies);
$filters = array('my' => 'My Tasks', 'myunfinished' => 'My Unfinished Tasks', 'allunfinished' => 'All Unfinished Tasks', 'myproj' => 'My Projects', 'mycomp' => 'All Tasks for my Company', 'unassigned' => 'All Tasks (unassigned)', 'taskowned' => 'All Tasks That I Am Owner', 'taskcreated' => 'All Tasks I Have Created', 'all' => 'All Tasks');

$search_string = w2PgetParam($_POST, 'search_string', '');
$AppUI->setState($m . '_search_string', $search_string);
$search_string = w2PformSafe($search_string, true);

// setup the title block
$titleBlock = new w2p_Theme_TitleBlock('Tasks', 'icon.png', $m);
$titleBlock->addSearchCell($search_string);

// Let's see if this user has admin privileges
if (canView('users')) {
    $user_list = array(0 => 'all users');
    $user_list += $perms->getPermittedUsers('tasks');
    $titleBlock->addFilterCell('User', 'user_id', $user_list, $user_id);
}

$titleBlock->addFilterCell('Company', 'f2', $filters2, $f2);

if (w2PgetParam($_GET, 'inactive', '') == 'toggle') {
	$AppUI->setState('inactive', $AppUI->getState('inactive') == -1 ? 0 : -1);
}
$in = $AppUI->getState('inactive') == -1 ? '' : 'in';

$titleBlock->showhelp = false;
$titleBlock->addCell('<form action="?m=tasks" method="post" name="taskFilter" accept-charset="utf-8">' . arraySelect($filters, 'f', 'size="1" class="text" onChange="document.taskFilter.submit();"', $f, true) . '</form>');
$titleBlock->addCell($AppUI->_('Task Filter') . ':');

$titleBlock->addCrumb('?m=tasks&amp;a=todo&amp;user_id=' . $user_id, 'my todo');
if (w2PgetParam($_GET, 'pinned') == 1) {
	$titleBlock->addCrumb('?m=tasks', 'all tasks');
} else {
	$titleBlock->addCrumb('?m=tasks&amp;pinned=1', 'my pinned tasks');
}
$titleBlock->addCrumb('?m=tasks&amp;inactive=toggle', 'show ' . $in . 'active tasks');
$titleBlock->addCrumb('?m=tasks&amp;a=tasksperuser', 'tasks per user');
$titleBlock->show();

$tabBox = new CTabBox('?m=tasks', W2P_BASE_DIR . '/modules/tasks/', $tab);
$tabBox->show();

// include the re-usable sub view
$min_view = false;
echo $AppUI->getTheme()->styleRenderBoxTop();
include (W2P_BASE_DIR . '/modules/tasks/vw_tasks.php');