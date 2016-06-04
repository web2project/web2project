<?php
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly');
}
// @todo    convert to template

$forum = new CForum();
$canRead = $forum->canView();
$canAdd  = $forum->canCreate();

if (!$canRead) {
	$AppUI->redirect(ACCESS_DENIED);
}

// retrieve any state parameters
if (isset($_GET['orderby'])) {
	$orderdir = $AppUI->getState('ForumIdxOrderDir') ? ($AppUI->getState('ForumIdxOrderDir') == 'asc' ? 'desc' : 'asc') : 'desc';
	$AppUI->setState('ForumIdxOrderBy', w2PgetParam($_GET, 'orderby', null));
	$AppUI->setState('ForumIdxOrderDir', $orderdir);
}
$orderby = $AppUI->getState('ForumIdxOrderBy') ? $AppUI->getState('ForumIdxOrderBy') : 'forum_name';
$orderdir = $AppUI->getState('ForumIdxOrderDir') ? $AppUI->getState('ForumIdxOrderDir') : 'asc';

$f = w2PgetParam($_REQUEST, 'f', 0);

$items = $forum->getAllowedForums($AppUI->user_id, $AppUI->user_company, $f, $orderby, $orderdir);

$filters = array('- Filters -');

if (isset($a) && $a == 'viewer') {
    array_push($filters, 'My Watched', 'Last 30 days');
} else {
    array_push($filters, 'My Forums', 'My Watched', 'My Projects', 'My Company', 'Inactive Projects');
}

// setup the title block
$titleBlock = new w2p_Theme_TitleBlock('Forums', 'icon.png', $m);
$titleBlock->addFilterCell('Filter', 'f', $filters, $f);

if ($canAdd) {
    $titleBlock->addButton('New forum', '?m=forums&a=addedit');
}

//TODO: this is a little hack to make sure the table header gets generated in the show() method below
global $a;
$a = 'list';
// End of little hack

$titleBlock->show();

$tabBox = new CTabBox('?m=forums', W2P_BASE_DIR . '/modules/forums/', $tab);
$tabBox->show();

$module = new w2p_System_Module();
$fields = $module->loadSettings('forums', 'index_list');

if (0 == count($fields)) {
    $fieldList = array('forum_project', 'forum_name', 'forum_description', 'forum_owner',
        'forum_topics', 'forum_replies', 'forum_last_date');
    $fieldNames = array('Project', 'Forum Name', 'Description', 'Owner', 'Topics',
        'Replies', 'Last Post Info');

    $module->storeSettings('forums', 'index_list', $fieldList, $fieldNames);
    $fields = array_combine($fieldList, $fieldNames);
}
?>

<form name="watcher" action="./index.php?m=forums&f=<?php echo $f; ?>" method="post" accept-charset="utf-8">
	<input type="hidden" name="dosql" value="do_watch_forum" />
	<input type="hidden" name="watch" value="forum" />

    <?php
    $listTable = new w2p_Output_ListTable($AppUI);
    $listTable->addBefore('watch', 'forum_id');
    echo $listTable->startTable();
    echo $listTable->buildHeader($fields, true, 'forums&f=' . $f);
    echo $listTable->buildRows($items);
    ?>
    <tr>
        <td colspan="<?php echo $listTable->cellCount; ?>">
            <input type="submit" class="button" value="<?php echo $AppUI->_('update watches'); ?>" />
        </td>
    </tr>
    <?php
    echo $listTable->endTable();
    ?>
</form>