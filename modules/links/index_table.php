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
    $titleBlock = new w2p_Theme_TitleBlock( '', '', $m, "$m.$a" );
    $titleBlock->addCell(
        '<input type="submit" class="button" value="'.$AppUI->_('new link').'">', '',
        '<form action="?m=links&a=addedit&project_id='.$project_id.'&task_id='.$task_id.'" method="post" accept-charset="utf-8">', '</form>'
    );
    $titleBlock->show();
}

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
	$links = $link->getProjectTaskLinksByCategory($AppUI, $project_id, $task_id, $tab-1, $search);
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
        <?php
        $fieldList = array();
        $fieldNames = array();
        $fields = w2p_Core_Module::getSettings('links', 'index_table');
        if (count($fields) > 0) {
            foreach ($fields as $field => $text) {
                $fieldList[] = $field;
                $fieldNames[] = $text;
            }
        } else {
            // TODO: This is only in place to provide an pre-upgrade-safe 
            //   state for versions earlier than v3.0
            //   At some point at/after v4.0, this should be deprecated
            $fieldList = array('', 'link_name', 'link_description', 'link_category', 'link_task', 'link_owner', 'link_date');
            $fieldNames = array('', 'Link Name', 'Description', 'Category', 'Task Name', 'Owner', 'Date');
        }
//TODO: The link below is commented out because this module doesn't support sorting... yet.
        foreach ($fieldNames as $index => $name) {
            ?><th nowrap="nowrap">
<!--                <a href="?m=links&orderby=<?php echo $fieldList[$index]; ?>" class="hdr">-->
                    <?php echo $AppUI->_($fieldNames[$index]); ?>
<!--                </a>-->
            </th><?php
        }
        ?>
    </tr>
<?php
$fp = -1;

$id = 0;
for ($i = ($page - 1) * $xpg_pagesize; $i < $page * $xpg_pagesize && $i < $xpg_totalrecs; $i++) {
	$row = $links[$i];

	if ($fp != $row['link_project']) {
		if (!$row['project_name']) {
			$row['project_name'] = $AppUI->_('No Project Specified');
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