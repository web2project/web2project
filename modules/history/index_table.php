<?php
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}
// @todo    convert to template

global $AppUI, $filter_param;

$filter_param = ('projects' == $m) ? 'projects' : $filter_param;
$page = (int) w2PgetParam($_GET, 'page', 1);

$history = new CHistory();
$where = (-1 == $filter_param) ? '' : "history_table = '".$filter_param."'";
$histories = $history->loadAll('history_date DESC', $where);

$items = array_values($histories);

$display = array();
$perms = $AppUI->acl();
foreach ($items as $item) {
    if (!$perms->checkModuleItem($item['history_table'], 'view', $item['history_item'])) {
        continue;
    }
    // @note this next line is a little hack so our templating can resolve which history_user is which user
    $item['user_id'] = $item['history_user'];
    $display[] = $item;
}
$items = $display;

$module = new w2p_System_Module();
$fields = $module->loadSettings('history', 'index_list');

if (0 == count($fields)) {
    $fieldList = array('history_date', 'history_description', 'history_user');
    $fieldNames = array('Date', 'Description', 'Owner');

    $module->storeSettings('history', 'index_list', $fieldList, $fieldNames);
    $fields = array_combine($fieldList, $fieldNames);
}

$xpg_pagesize = w2PgetConfig('page_size', 50);
$xpg_min = $xpg_pagesize * ($page - 1); // This is where we start our record set from
$xpg_totalrecs = count($items);
$items = array_slice($items, $xpg_min, $xpg_pagesize);

$m .= '&filter='.$filter_param; // This is a hack to get the pagination to work as expected
$pageNav = buildPaginationNav($AppUI, $m, 0, $xpg_totalrecs, $xpg_pagesize, $page);

$listTable = new w2p_Output_ListTable($AppUI);
$listTable->df .= ' ' . $AppUI->getPref('TIMEFORMAT');

echo $pageNav;
echo $listTable->startTable('tbl list history');
echo $listTable->buildHeader($fields);
echo $listTable->buildRows($items);
echo $listTable->endTable();
echo $pageNav;