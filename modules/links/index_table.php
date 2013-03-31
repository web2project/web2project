<?php
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}
global $AppUI, $canRead, $canEdit, $project_id, $task_id, $showProject, $tab;

$type_filter = ($m == 'links') ? $tab-1 : -1;

if ($task_id && !$project_id) {
    $task = new CTask;
    $task->load($task_id);
    $project_id = $task->task_project;
}

$page = (int) w2PgetParam($_GET, 'page', 1);
$search = w2PgetParam($_POST, 'search', '');

if (!isset($project_id)) {
	$project_id = (int) w2PgetParam($_POST, 'project_id', 0);
}

if ($canRead) {
	$link = new CLink();
	$links = $link->getProjectTaskLinksByCategory(null, $project_id, $task_id, $type_filter, $search);
} else {
	$AppUI->redirect(ACCESS_DENIED);
}

$xpg_pagesize = w2PgetConfig('page_size', 50);
$xpg_min = $xpg_pagesize * ($page - 1); // This is where we start our record set from
// counts total recs from selection
$xpg_totalrecs = count($links);
$pageNav = buildPaginationNav($AppUI, $m, $tab, $xpg_totalrecs, $xpg_pagesize, $page);
echo $pageNav;

$fieldList = array();
$fieldNames = array();

$module = new w2p_Core_Module();
$fields = $module->loadSettings('links', 'index_list');

if (count($fields) > 0) {
    $fieldList = array_keys($fields);
    $fieldNames = array_values($fields);
} else {
    // TODO: This is only in place to provide an pre-upgrade-safe
    //   state for versions earlier than v3.0
    //   At some point at/after v4.0, this should be deprecated
    $fieldList = array('link_name', 'link_description', 'link_category', 'link_task', 'link_owner', 'link_date');
    $fieldNames = array('Link Name', 'Description', 'Category', 'Task Name', 'Owner', 'Date');

    $module->storeSettings('links', 'index_list', $fieldList, $fieldNames);
}
$columnCount = 2 + count($fieldList);
?>
<table class="tbl list">
    <tr>
        <th></th><th></th>
        <?php foreach ($fieldNames as $index => $name) { ?>
            <th><?php echo $AppUI->_($fieldNames[$index]); ?></th>
        <?php } ?>
    </tr>
<?php
$fp = -1;
$htmlHelper = new w2p_Output_HTMLHelper($AppUI);
$htmlHelper->df .= ' ' . $AppUI->getPref('TIMEFORMAT');

$link_types = w2PgetSysVal('LinkType');
$customLookups = array('link_category' => $link_types);

$id = 0;
//TODO:  put columns in order
for ($i = ($page - 1) * $xpg_pagesize; $i < $page * $xpg_pagesize && $i < $xpg_totalrecs; $i++) {
	$row = $links[$i];

	if ($fp != $row['link_project']) {
		if (!$row['project_name']) {
			$row['project_name'] = $AppUI->_('No Project Specified');
			$row['project_color_identifier'] = 'f4efe3';
		}
		if ($showProject) {
			$s = '<tr>';
			$s .= '<td colspan="' . $columnCount . '" style=" border: outset #d1d1cd 1px; background-color:#' . $row['project_color_identifier'] . ';">';
			if ($row['link_project'] > 0) {
				$s .= '<a href="?m=projects&a=view&project_id=' . $row['link_project'] . '" style="color:'.bestColor($row['project_color_identifier']) . '; font-weight: bold; padding-top: 2px;">' . $row['project_name'] . '</a>';
            } else {
				$s .= $row['project_name'];
            }
			$s .= '</td></tr>';
			echo $s;
		}
	}
	$fp = $row['link_project'];
    ?>
    <tr>
        <td class="data _edit">
        <?php if ($canEdit) {
            echo '<a href="./index.php?m=' . $m . '&a=addedit&link_id=' . $row['link_id'] . '">' . w2PshowImage('icons/stock_edit-16.png', '16', '16') . '</a>';
        }
        echo '</td><td class="data">';
        echo '<a href="' . $row['link_url'] . '" target="_blank">' . w2PshowImage('forward.png', '16', '16') . '</a>';
        ?>
        </td>
        <?php
        $htmlHelper->stageRowData($row);
        foreach ($fieldList as $index => $column) {
            echo $htmlHelper->createCell($fieldList[$index], $row[$fieldList[$index]], $customLookups);
        }
        ?>
    </tr>
<?php }

    if ($canEdit && 'links' != $m) { ?>
	<tr>
		<td colspan="<?php echo $columnCount; ?>" align="right" valign="top" style="background-color:#ffffff">
			<input type="button" class=button value="<?php echo $AppUI->_('new link') ?>" onClick="javascript:window.location='./index.php?m=links&a=addedit&project_id=<?php echo $project_id; ?>&task_id=<?php echo $task_id; ?>'">
		</td>
	</tr>
    <?php } ?>
</table>
<?php
echo $pageNav;