<?php /* $Id$ $URL$ */
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}
global $AppUI, $deny1, $canRead, $canEdit, $project_id, $task_id, $showProject, $tab;
global $filter_param;

echo 'x'.$filter_param.'x';

$filter = array();
if ($filter_param) {
	$in_filter = $_REQUEST['filter'];
	$filter[] = 'history_table = \'' . $_REQUEST['filter'] . '\' ';
} else {
	$in_filter = '';
}

if (!empty($_REQUEST['project_id'])) {
	$project_id = w2PgetParam($_REQUEST, 'project_id', 0);

	$q = new w2p_Database_Query;
	$q->addTable('tasks');
	$q->addQuery('task_id');
	$q->addWhere('task_project = ' . (int)$project_id);
	$project_tasks = implode(',', $q->loadColumn());
	if (!empty($project_tasks)) {
		$project_tasks = 'OR (history_table = \'tasks\' AND history_item IN (' . $project_tasks . '))';
	}

	$q->addTable('files');
	$q->addQuery('file_id');
	$q->addWhere('file_project = ' . (int)$project_id);
	$project_files = implode(',', $q->loadColumn());
	if (!empty($project_files)) {
		$project_files = 'OR (history_table = \'files\' AND history_item IN (' . $project_files . '))';
	}

	$filter[] = '((history_table = \'projects\' AND history_item = \'' . (int)$project_id .'\') ' . $project_tasks . ' ' . $project_files . ')';
}

$page = (int) w2PgetParam($_GET, 'page', 1);
$limit = (int) w2PgetParam($_GET, 'limit', 100);
$offset = ($page - 1) * $limit;
if ($filter_param != '' || $page) {
	$q = new w2p_Database_Query;
	$q->addQuery('COUNT(history_id) AS hits');
	$q->addTable('history', 'h');
	$q->addTable('users');
	$q->addWhere('history_user = user_id');
	$q->addTable('contacts');
	$q->addWhere('contact_id = user_contact');
	$q->addWhere($filter);
	$count = (int) $q->loadResult();

	$q = new w2p_Database_Query;
	$q->addQuery('history_date as history_datetime, history_id, history_item, history_table, history_description, history_action');
	$q->addQuery('contact_display_name AS contact_name');
	$q->addTable('history', 'h');
	$q->addTable('users');
	$q->addWhere('history_user = user_id');
	$q->addTable('contacts');
	$q->addWhere('contact_id = user_contact');
	$q->addWhere($filter);
	$q->addOrder('history_date DESC');
	$q->setLimit($limit, $offset);
	$history = $q->loadList();
} else {
	$history = array();
}

$pages = (int)($count / $limit) + 1;
$max_pages = 20;
if ($pages > $max_pages) {
	$first_page = max($page - (int)($max_pages / 2), 1);
	$last_page = min($first_page + $max_pages - 1, $pages);
} else {
	$first_page = 1;
	$last_page = $pages;
}
?>



<table class="tbl list">
<tr>
	<th width="10">&nbsp;</th>
	<th width="200"><?php echo $AppUI->_('Date'); ?></th>
	<th nowrap="nowrap"><?php echo $AppUI->_('Description'); ?></th>
	<th nowrap="nowrap"><?php echo $AppUI->_('User'); ?>&nbsp;&nbsp;</th>
</tr>
<?php
// Checking permissions.
// TODO: Enable the lines below to activate new permissions.
$perms = &$AppUI->acl();

$historyItem = new CHistory();
$htmlHelper = new w2p_Output_HTMLHelper($AppUI);

foreach ($history as $row) {
	$module = $row['history_table'] == 'task_log' ? 'tasks' : $row['history_table'];
    ?>
    <tr>
        <td align="center"><a href='<?php echo '?m=history&a=addedit&history_id=' . $row['history_id'] ?>'><img src="<?php echo w2PfindImage('icons/pencil.gif'); ?>" alt="<?php echo $AppUI->_('Edit History') ?>" border="0" width="12" height="12" /></a></td>
        <?php echo $htmlHelper->createCell('history_datetime', $row['history_datetime']); ?>
        <td><?php echo $historyItem->show_history($row) ?></td>
        <?php echo $htmlHelper->createCell('contact_name-unformatted', $row['contact_name']); ?>
    </tr>
    <?php
}
?>
</table>