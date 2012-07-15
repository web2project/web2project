<?php /* $Id$ $URL$ */
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}

##
## History module
## (c) Copyright
## J. Christopher Pereira (kripper@imatronix.cl)
## IMATRONIX
##

$titleBlock = new w2p_Theme_TitleBlock('History', 'stock_book_blue_48.png', 'history', 'history.' . $a);
$titleBlock->show();

$filter_param = w2PgetParam($_REQUEST, 'filter', ''); 
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

<table width="100%" cellspacing="1" cellpadding="0" border="0">
<tr>
    <td nowrap="nowrap" align="right">
<form name="filter" action="?m=history" method="post" accept-charset="utf-8">
<?php echo $AppUI->_('Changes to'); ?>:
        <select name="filter" class="text" onchange="document.filter.submit()">
                <option value="">(<?php echo $AppUI->_('Select Filter'); ?>)</option>
                <option value="0" <?php if ($in_filter == '0') echo 'selected="selected"'; ?>><?php echo $AppUI->_('Show all'); ?></option>
                <option value="companies" <?php if ($in_filter == 'companies') echo 'selected="selected"'; ?>><?php echo $AppUI->_('Companies'); ?></option>
                <option value="projects" <?php if ($in_filter == 'projects') echo 'selected="selected"'; ?>><?php echo $AppUI->_('Projects'); ?></option>
                <option value="tasks" <?php if ($in_filter == 'tasks') echo 'selected="selected"'; ?>><?php echo $AppUI->_('Tasks'); ?></option>
                <option value="files" <?php if ($in_filter == 'files') echo 'selected="selected"'; ?>><?php echo $AppUI->_('Files'); ?></option>
                <option value="forums" <?php if ($in_filter == 'forums') echo 'selected="selected"'; ?>><?php echo $AppUI->_('Forums'); ?></option>
                <option value="login" <?php if ($in_filter == 'login') echo 'selected="selected"'; ?>><?php echo $AppUI->_('Login/Logouts'); ?></option>
        </select>
	<?php
if ($pages > 1) {
	for ($i = $first_page; $i <= $last_page; $i++) {
		echo '&nbsp;';
		if ($i == $page) {
			echo '<b>' . $i . '</b>';
		} else {
			echo '<a href="?m=history&filter=' . $in_filter . '&page=' . $i . '">' . $i . '</a>';
		}
	}
}
?>
</form>
        </td>
	<td align="right"><input class="button" type="button" value="<?php echo $AppUI->_('Add history'); ?>" onclick="window.location='?m=history&a=addedit'"></td>
</table>

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
        <?php echo $htmlHelper->createCell('contact_name', $row['contact_name']); ?>
    </tr>
    <?php
}
?>
</table>