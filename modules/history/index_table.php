<?php
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}
global $AppUI, $filter_param;

$page = (int) w2PgetParam($_GET, 'page', 1);
$project_id = (int) w2PgetParam($_GET, 'project_id', 0);

$start_date = new w2p_Utilities_Date(w2PgetParam($_REQUEST, 'history_start_date', date('Ymd', time() - 2592000)));
$end_date = new w2p_Utilities_Date(w2PgetParam($_REQUEST, 'history_end_date', date('Ymd')));
$end_date->AddSeconds(86399);

$history = new CHistory();
if ((int)$filter_param == -1) {
    $where = '';
} elseif ($filter_param == 'project_id') {
    $where = 'history_project = ' . $project_id;
} else {
    $where = 'history_table = \'' . $filter_param . '\'';
}
$histories = $history->loadAll('history_date DESC', $where);
$items = array_values($histories);

$perms = $AppUI->acl();

// Strip whatever the user is not allowed to view. 
// Doing it here allows accurate row counts on the page.
for ($i = 0, $i_cmp = count($items); $i < $i_cmp; $i++) {
    $row = $items[$i];
    // Since there no individual permissions for forum messsages...
    if ($row['history_table'] == 'forum_messages') {
	if (!$perms->checkModuleItem($row['history_table'], 'view')) {
	    unset($items[$i]);
	}
    } else if ($row['history_table'] == 'login') {
	if (!$perms->checkModule('users', 'view')) {
	    unset($items[$i]);
	}
    } else if ($row['history_table'] == 'modules') {
	if (!$perms->checkModule('system', 'view')) {
	    unset($items[$i]);
	}
    } else {
	if (!$perms->checkModuleItem($row['history_table'], 'view', $row['history_item'])) {
	    unset($items[$i]);
	}
    }
    // Now check for period
    $hist_date = new w2p_Utilities_Date($row['history_date']);
    if ($start_date->compare($start_date, $hist_date) > 0 ||
        $end_date->compare($end_date, $hist_date) < 0) {
	unset($items[$i]);
    }
}
$items = array_values($items);

$xpg_pagesize = w2PgetConfig('page_size', 50);
$xpg_min = $xpg_pagesize * ($page - 1); // This is where we start our record set from
// counts total recs from selection
$xpg_totalrecs = count($items);
$m .= '&filter='.$filter_param; // This is a hack to get the pagination to work as expected
$pageNav = buildPaginationNav($AppUI, $m, $tab, $xpg_totalrecs, $xpg_pagesize, $page);
echo $pageNav;

$fieldList = array('history_date', 'history_description', 'history_user');
$fieldNames = array('Date', 'Description', 'Owner');

$htmlHelper = new w2p_Output_HTMLHelper($AppUI);
$htmlHelper->df .= ' ' . $AppUI->getPref('TIMEFORMAT');

?>
<table class="tbl list history">
    <tr>
        <?php foreach ($fieldNames as $index => $name) { ?>
            <th><?php echo $AppUI->_($fieldNames[$index]); ?></th>
        <?php } ?>
    </tr>
<?php

for ($i = ($page - 1) * $xpg_pagesize; $i < $page * $xpg_pagesize && $i < $xpg_totalrecs; $i++) {
    $row = $items[$i];
    $row['user_id'] = $row['history_user'];

//TODO: do we care about linking when we have a create/update entry?
    echo '<tr>';
    $htmlHelper->stageRowData($row);
    echo $htmlHelper->createCell('history_datetime', $row['history_date']);
    echo $htmlHelper->createCell('history_description', $row['history_description']);
    echo $htmlHelper->createCell('history_user', $row['history_user']);
    echo '</tr>';
}
?>
</table>
<?php
echo $pageNav;