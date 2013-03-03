<?php
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}
global $AppUI, $filter_param;

$page = (int) w2PgetParam($_GET, 'page', 1);

$history = new CHistory();
$where = (-1 == $filter_param) ? '' : "history_table = '".$filter_param."'";
$histories = $history->loadAll('history_date DESC', $where);
$items = array_values($histories);

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

$perms = $AppUI->acl();

for ($i = ($page - 1) * $xpg_pagesize; $i < $page * $xpg_pagesize && $i < $xpg_totalrecs; $i++) {
    $row = $items[$i];
    $row['user_id'] = $row['history_user'];

//TODO: we need to make sure sub-modules are handled properly
    if (!$perms->checkModuleItem($row['history_table'], 'view', $row['history_item'])) {
        continue;
    }
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