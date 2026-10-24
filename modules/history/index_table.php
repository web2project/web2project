<?php
/** @var w2p_Core_CAppUI $AppUI */
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}
// @todo    convert to template

global $AppUI, $filter_param, $m, $project_id, $tab;

// On a project's History tab, show only that project's own changes.
$project_tab = ('projects' == $m && (int) $project_id > 0);
$filter_param = ('projects' == $m) ? 'projects' : $filter_param;
$page = (int) w2PgetParam($_GET, 'page', 1);

$perms = $AppUI->acl();

$module_translate = ['modules' => 'system', 'custom_fields_struct' => 'system',
    'budgets' => 'system', 'billingcode' => 'system',
    'forum_messages' => 'forums', 'login' => 'admin'];

// Which rows to look at: one module, or one project on its History tab.
$baseQuery = new w2p_Database_Query();
$baseWhere = array();
if (-1 != $filter_param) {
    $baseWhere[] = 'history_table = ' . $baseQuery->quote((string) $filter_param);
}
if ($project_tab) {
    $baseWhere[] = 'history_item = ' . (int) $project_id;
}

// Filter by permission in SQL, one condition per module the rows belong
// to, rather than loading every row and checking each one (which also
// worked out the module's allowed items again for every row). The rules
// are checkModuleItem($module, 'view', $item)'s: System entries need
// System view, entries without an item need module view, and the others
// need their item to be one the user may view.
$q = new w2p_Database_Query();
$q->addTable('history');
$q->addQuery('DISTINCT history_table');
foreach ($baseWhere as $condition) {
    $q->addWhere($condition);
}
$tables = $q->loadColumn();

$allowed = array();
foreach ($tables as $table) {
    $_module = isset($module_translate[$table]) ? $module_translate[$table] : $table;
    $inTable = 'history_table = ' . $q->quote((string) $table);

    if ('system' == $_module) {
        if (canView('system')) {
            $allowed[] = $inTable;
        }
        continue;
    }
    if ('login' == $table) {
        // Logins and logouts (history_item is the user). Every user may see
        // their own; other users' need the Users module. checkModuleItem()
        // would allow them all, because CUser::getAllowedRecords() lists
        // every user for the user pickers.
        $allowed[] = canView('users') ? $inTable
            : '(' . $inTable . ' AND history_item = ' . (int) $AppUI->user_id . ')';
        continue;
    }
    $items = array();
    if ($perms->checkModule($_module, 'view')) {
        $items[] = 0;
    }
    foreach (array_keys($perms->getViewableItems($_module)) as $itemId) {
        $items[] = (int) $itemId;
    }
    if (count($items)) {
        $allowed[] = '(' . $inTable . ' AND history_item IN (' . implode(',', $items) . '))';
    }
}
$where = $baseWhere;
$where[] = count($allowed) ? '(' . implode(' OR ', $allowed) . ')' : '1 = 0';

$xpg_pagesize = (int) w2PgetConfig('page_size', 50);
$xpg_pagesize = ($xpg_pagesize > 0) ? $xpg_pagesize : 50;
$page = max(1, $page);
$xpg_min = $xpg_pagesize * ($page - 1); // This is where we start our record set from

$q = new w2p_Database_Query();
$q->addTable('history');
$q->addQuery('COUNT(*)');
$q->addWhere(implode(' AND ', $where));
$xpg_totalrecs = (int) $q->loadResult();

$q = new w2p_Database_Query();
$q->addTable('history');
$q->addQuery('*');
$q->addWhere(implode(' AND ', $where));
$q->addOrder('history_datetime');
$q->addOrder('history_id');
$q->setLimit($xpg_pagesize, $xpg_min);
$items = array();
foreach ($q->loadList() as $item) {
    // @note this next line is a little hack so our templating can resolve which history_user is which user
    $item['user_id'] = $item['history_user'];
    $items[] = $item;
}

$module = new w2p_System_Module();
$fields = $module->loadSettings('history', 'index_list');

if (0 == count($fields)) {
    $fieldList = array('history_datetime', 'history_table', 'history_action', 'history_description', 'history_user');
    $fieldNames = array('Date', 'Module', 'Action', 'Item', 'Actor');

    $module->storeSettings('history', 'index_list', $fieldList, $fieldNames);
    $fields = array_combine($fieldList, $fieldNames);
}

if ($project_tab) {
    // keep the page links on this project's History tab
    $pageNav = buildPaginationNav($AppUI, $m . '&amp;a=view&amp;project_id=' . (int) $project_id, (int) $tab, $xpg_totalrecs, $xpg_pagesize, $page);
} else {
    $m .= '&filter='.$filter_param; // This is a hack to get the pagination to work as expected
    $pageNav = buildPaginationNav($AppUI, $m, 0, $xpg_totalrecs, $xpg_pagesize, $page);
}

$listTable = new w2p_Output_ListTable($AppUI);
$listTable->df .= ' ' . $AppUI->getPref('TIMEFORMAT');

echo $pageNav;
echo $listTable->startTable('history-list');
echo $listTable->buildHeader($fields);
echo $listTable->buildRows($items);
echo $listTable->endTable();
echo $pageNav;