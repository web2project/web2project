<?php
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}

$perms = &$AppUI->acl();

// retrieve any state parameters
$user_id = $AppUI->user_id;
if (canView('admin')) { // Only sysadmins are able to change users
	if (w2PgetParam($_POST, 'user_id', 0) != 0) { // this means that
		$user_id = w2PgetParam($_POST, 'user_id', 0);
		$AppUI->setState('user_id', $_POST['user_id']);
	} elseif ($AppUI->getState('user_id')) {
		$user_id = $AppUI->getState('user_id');
	} else {
		$AppUI->setState('user_id', $user_id);
	}
}

if (isset($_POST['f'])) {
	$AppUI->setState('DelegationIdxFilter', $_POST['f']);
}
$f = $AppUI->getState('DelegationIdxFilter') ? $AppUI->getState('DelegationIdxFilter') :
        w2PgetConfig('task_filter_default', 'myunfinished');

if (isset($_POST['f2'])) {
	$AppUI->setState('CompanyIdxFilter', $_POST['f2']);
}

$f2 = ($AppUI->getState('CompanyIdxFilter')) ? $AppUI->getState('CompanyIdxFilter') :
        ((w2PgetConfig('company_filter_default', 'user') == 'user') ? $AppUI->user_company : 'allcompanies');

if (isset($_POST['show_task_options'])) {
	$AppUI->setState('DelegationShowIncomplete', w2PgetParam($_POST, 'show_incomplete', 0));
}
$showIncomplete = $AppUI->getState('DelegationShowIncomplete', 0);

$tab = $AppUI->processIntState('DelegationsTab', $_GET, 'tab', 0);

// get CCompany() to filter tasks by company
$obj = new CCompany();
$companies = $obj->getAllowedRecords($AppUI->user_id, 'company_id,company_name', 'company_name');
$filters2 = arrayMerge(array('allcompanies' => $AppUI->_('All Companies')), $companies);

$titleBlock = new w2p_Theme_TitleBlock('Delegations', 'delegation.png', $m, $m . '.' . $a);

$search_text = w2PgetParam($_POST, 'searchtext', '');

$titleBlock->addCell('<form action="?m=delegations" method="post" id="searchfilter" accept-charset="utf-8"><input type="text" class="text" size="20" name="searchtext" onChange="document.searchfilter.submit();" value="' . $search_text . '" title="' . $AppUI->_('Search in name and description fields') . '"/></form>');
$titleBlock->addCell($AppUI->_('Search') . ':');

$user_list = $perms->getPermittedUsers('delegations');

// Let's see if this user has admin privileges
if (canView('admin')) {
	$titleBlock->addCell('<form action="?m=delegations" method="post" name="userIdForm" accept-charset="utf-8">' . arraySelect($user_list, 'user_id', 'size="1" class="text" onChange="document.userIdForm.submit();"', $user_id, false) . '</form>');
    $titleBlock->addCell($AppUI->_('User') . ':');
}

$titleBlock->addCell('<form action="?m=delegations" method="post" name="companyFilter" accept-charset="utf-8">' . arraySelect($filters2, 'f2', 'size="1" class="text" onChange="document.companyFilter.submit();"', $f2, false) . '</form>');
$titleBlock->addCell($AppUI->_('Company') . ':');

$titleBlock->showhelp = false;
$filters = $tab == 0 ? $filtersA : $filtersB;
$titleBlock->addCell('<form action="?m=delegations" method="post" name="taskFilter" accept-charset="utf-8">' . arraySelect($filters, 'f', 'size="1" class="text" onChange="document.taskFilter.submit();"', $f, true) . '</form>');
$titleBlock->addCell($AppUI->_($tab == 0 ? 'Task Filter' : 'Delegation Filter') . ':');

$titleBlock->addCell('<form name="task_list_options" method="post" action="?m=delegations" accept-charset="utf-8">
            		<input type="hidden" name="show_task_options" value="1" />
            		<input type="checkbox" name="show_incomplete" id="show_incomplete" onclick="document.task_list_options.submit();"' .
                		($showIncomplete ? 'checked="checked"' : '') . '/>
            		<label for="show_incomplete">' . $AppUI->_($tab == 0 ? "Incomplete Tasks Only" : ($tab == 3 ? "Unvalidated Rejections Only" : "Incomplete Delegations Only")) . '&nbsp;&nbsp;&nbsp;&nbsp;</label>
        	      </form>');

$titleBlock->show();

$tabBox = new CTabBox('?m=delegations','', $tab);
$tabBox->add(W2P_BASE_DIR . '/modules/delegations/ae_assigned_tasks', 'My Assigned Tasks');
$tabBox->add(W2P_BASE_DIR . '/modules/delegations/ae_deleg_tasks', 'My Delegated Tasks');
$tabBox->add(W2P_BASE_DIR . '/modules/delegations/ae_tasks_others', 'My Tasks Delegated To Others');

$proj = new CProject();
if ($proj->canEdit()) {
	$tabBox->add(W2P_BASE_DIR . '/modules/delegations/ae_tasks_rejected', 'Rejected Delegations');
}

$tabBox->show();
