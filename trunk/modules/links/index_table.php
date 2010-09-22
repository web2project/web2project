<?php /* $Id$ $URL$ */
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}
global $AppUI, $deny1, $canRead, $canEdit, $project_id, $task_id, $showProject;

if ($task_id && !$project_id) {
    $task = new CTask;
    $task->load($task_id);
    $project_id = $task->task_project;
}
// modified later by Pablo Roca (proca) in 18 August 2003 - added page support
// Files modules: index page re-usable sub-table
$m = 'links';

if ($canEdit) {
    $titleBlock = new CTitleBlock( '', '', $m, "$m.$a" );
    $titleBlock->addCell(
        '<input type="submit" class="button" value="'.$AppUI->_('new link').'">', '',
        '<form action="?m=links&a=addedit&project_id='.$project_id.'&task_id='.$task_id.'" method="post" accept-charset="utf-8">', '</form>'
    );
    $titleBlock->show();
}

$tab = $AppUI->processIntState('LinkIdxTab', $_GET, 'tab', 0);
$page = (int) w2PgetParam($_GET, 'page', 1);
$search = w2PgetParam($_POST, 'search', '');

if (!isset($project_id)) {
	$project_id = (int) w2PgetParam($_POST, 'project_id', 0);
}

$df = $AppUI->getPref('SHDATEFORMAT');
$tf = $AppUI->getPref('TIMEFORMAT');

$link_types = w2PgetSysVal('LinkType');

if ($canRead) {
	$link = new CLink();
	$links = $link->getProjectTaskLinksByCategory($AppUI, $project_id, $task_id, --$tab, $search);
} else {
	$AppUI->redirect('m=public&a=access_denied');
}

$xpg_pagesize = w2PgetConfig('page_size', 50);
$xpg_min = $xpg_pagesize * ($page - 1); // This is where we start our record set from
// counts total recs from selection
$xpg_totalrecs = count($links);
echo buildPaginationNav($AppUI, $m, $tab, $xpg_totalrecs, $xpg_pagesize, $page);

?>
<table width="100%" border="0" cellpadding="2" cellspacing="1" class="tbl">
<tr>
	<th nowrap="nowrap">&nbsp;</th>
	<th nowrap="nowrap"><?php echo $AppUI->_('Link Name'); ?></th>
	<th nowrap="nowrap"><?php echo $AppUI->_('Description'); ?></th>
	<th nowrap="nowrap"><?php echo $AppUI->_('Category'); ?></th>
	<th nowrap="nowrap"><?php echo $AppUI->_('Task Name'); ?></th>
	<th nowrap="nowrap"><?php echo $AppUI->_('Owner'); ?></th>
	<th nowrap="nowrap"><?php echo $AppUI->_('Date'); ?></th>
</tr>
<?php
$fp = -1;

$id = 0;
for ($i = ($page - 1) * $xpg_pagesize; $i < $page * $xpg_pagesize && $i < $xpg_totalrecs; $i++) {
	$row = $links[$i];

	if ($fp != $row['link_project']) {
		if (!$row['project_name']) {
			$row['project_name'] = $AppUI->_('All Projects');
			$row['project_color_identifier'] = 'f4efe3';
		}
		if ($showProject) {
			$s = '<tr>';
			$s .= '<td colspan="10" style="background-color:#' . $row['project_color_identifier'] . '" style="border: outset 2px #eeeeee">';
			$s .= '<font color="' . bestColor($row['project_color_identifier']) . '">';
			if ($row['link_project'] > 0)
				$s .= '<a href="?m=projects&a=view&project_id=' . $row['link_project'] . '">' . $row['project_name'] . '</a>';
			else
				$s .= $row['project_name'];
			$s .= '</font>';
			$s .= '</td></tr>';
			echo $s;
		}
	}
	$fp = $row['link_project'];
?>
<tr>
	<td nowrap="nowrap" width="20">
	<?php if ($canEdit) {
		echo '<a href="./index.php?m=' . $m . '&a=addedit&link_id=' . $row['link_id'] . '">' . w2PshowImage('icons/stock_edit-16.png', '16', '16') . '</a>';
	}
?>
	</td>
	<td nowrap="8%">
		<?php echo '<a href="' . $row['link_url'] . '" target="_blank">' . $row['link_name'] . '</a>'; ?>
	</td>
	<td width="20%"><?php echo $row['link_description']; ?></td>
        <td width="10%" nowrap="nowrap" align="center"><?php echo $link_types[$row['link_category']]; ?></td> 
	<td width="5%" align="left"><a href="./index.php?m=tasks&a=view&task_id=<?php echo $row['task_id']; ?>"><?php echo $row['task_name']; ?></a></td>
	<td width="15%" nowrap="nowrap"><?php echo $row['contact_first_name'] . ' ' . $row['contact_last_name']; ?></td>
	<td width="15%" nowrap="nowrap" align="center">
        <?php
            echo $AppUI->formatTZAwareTime($row['link_date'], $df . ' ' . $tf);
        ?>
    </td>
</tr>
<?php } ?>
</table>
<?php
echo buildPaginationNav($AppUI, $m, $tab, $xpg_totalrecs, $xpg_pagesize, $page);