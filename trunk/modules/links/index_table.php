<?php /* $Id$ $URL$ */
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}
global $AppUI, $deny1, $canRead, $canEdit, $project_id, $task_id, $showProject;

// modified later by Pablo Roca (proca) in 18 August 2003 - added page support
// Files modules: index page re-usable sub-table
$m = 'links';

// ****************************************************************************
// Page numbering variables
// Pablo Roca (pabloroca@Xmvps.org) (Remove the X)
// 19 August 2003
//
// $tab             - file category
// $page            - actual page to show
// $xpg_pagesize    - max rows per page
// $xpg_min         - initial record in the SELECT LIMIT
// $xpg_totalrecs   - total rows selected
// $xpg_sqlrecs     - total rows from SELECT LIMIT
// $xpg_total_pages - total pages
// $xpg_next_page   - next pagenumber
// $xpg_prev_page   - previous pagenumber
// $xpg_break       - stop showing page numbered list?
// $xpg_sqlcount    - SELECT for the COUNT total
// $xpg_sqlquery    - SELECT for the SELECT LIMIT
// $xpg_result      - pointer to results from SELECT LIMIT
function shownavbar_links($xpg_totalrecs, $xpg_pagesize, $xpg_total_pages, $page) {

	global $AppUI, $m;
	$xpg_break = false;
	$xpg_prev_page = $xpg_next_page = 0;

	$s = '<table width="100%" cellspacing="0" cellpadding="0" border="0"><tr>';

	if ($xpg_totalrecs > $xpg_pagesize) {
		$xpg_prev_page = $page - 1;
		$xpg_next_page = $page + 1;
		// left buttoms
		if ($xpg_prev_page > 0) {
			$s .= '<td align="left" width="15%"><a href="./index.php?m=' . $m . 'links&amp;page=1"><img src="' . w2PfindImage('navfirst.gif') . '" border="0" Alt="First Page"></a>&nbsp;&nbsp;';
			$s .= '<a href="./index.php?m=' . $m . '&amp;page=' . $xpg_prev_page . '"><img src="' . w2PfindImage('navleft.gif') . '" border="0" Alt="Previous page (' . $xpg_prev_page . ')"></a></td>';
		} else {
			$s .= '<td width="15%">&nbsp;</td>';
		}

		// central text (files, total pages, ...)
		$s .= '<td align="center" width="70%">' . $xpg_totalrecs . ' ' . $AppUI->_('Link(s)') . ' (' . $xpg_total_pages . ' ' . $AppUI->_('Page(s)') . ')</td>';

		// right buttoms
		if ($xpg_next_page <= $xpg_total_pages) {
			$s .= '<td align="right" width="15%"><a href="./index.php?m=' . $m . '&amp;page=' . $xpg_next_page . '"><img src="' . w2PfindImage('navright.gif') . '" border="0" Alt="Next Page (' . $xpg_next_page . ')"></a>&nbsp;&nbsp;';
			$s .= '<a href="./index.php?m=' . $m . '&amp;page=' . $xpg_total_pages . '"><img src="' . w2PfindImage('navlast.gif') . '" border="0" Alt="Last Page"></a></td>';
		} else {
			$s .= '<td width="15%">&nbsp;</td></tr>';
		}
		// Page numbered list, up to 30 pages
		$s .= '<tr><td colspan="3" align="center"> [ ';

		for ($n = $page > 16 ? $page - 16 : 1; $n <= $xpg_total_pages; $n++) {
			if ($n == $page) {
				$s .= '<b>' . $n . '</b></a>';
			} else {
				$s .= '<a href="./index.php?m=' . $m . '&amp;page=' . $n . '">' . $n . '</a>';
			}
			if ($n >= 30 + $page - 15) {
				$xpg_break = true;
				break;
			} elseif ($n < $xpg_total_pages) {
				$s .= ' | ';
			}
		}

		if (!isset($xpg_break)) { // are we supposed to break ?
			if ($n == $page) {
				$s .= '<' . $n . '</a>';
			} else {
				$s .= '<a href="./index.php?m=' . $m . '&amp;page=' . $xpg_total_pages . '">';
				$s .= $n . '</a>';
			}
		}
		$s .= ' ] </td></tr>';
	} else { // or we dont have any files..
		$s .= '<td align="center">';
		if ($xpg_next_page > $xpg_total_pages) {
			$s .= $xpg_sqlrecs . ' ' . $m . ' ';
		}
		$s .= '</td></tr>';
	}
	$s .= '</table>';
	echo $s;
}

if ($canEdit) {
    $titleBlock = new CTitleBlock( 'Links', 'folder5.png', $m, "$m.$a" );
    $titleBlock->addCell(
        '<input type="submit" class="button" value="'.$AppUI->_('new link').'">', '',
        '<form action="?m=links&a=addedit&project_id='.$project_id.'&task_id='.$task_id.'" method="post">', '</form>'
    );
    $titleBlock->show();
}

$tab = $AppUI->getState('LinkIdxTab') !== null ? $AppUI->getState('LinkIdxTab') : 0;
$page = w2PgetParam($_GET, 'page', 1);
$search = w2PgetParam($_POST, 'search', '');

if (!isset($project_id)) {
	$project_id = w2PgetParam($_POST, 'project_id', 0);
}
if (!isset($showProject)) {
	$showProject = true;
}

$xpg_pagesize = 30;
$xpg_min = $xpg_pagesize * ($page - 1); // This is where we start our record set from

// load the following classes to retrieved denied records
include_once ($AppUI->getModuleClass('projects'));
include_once ($AppUI->getModuleClass('tasks'));

$project = new CProject();
$task = new CTask();

$df = $AppUI->getPref('SHDATEFORMAT');
$tf = $AppUI->getPref('TIMEFORMAT');

$link_types = w2PgetSysVal('LinkType');
if ($tab <= 0) {
	$catsql = '';
} else {
	$catsql = 'link_category = ' . --$tab;
}

// SETUP FOR LINK LIST
$q = new DBQuery();
$q->addQuery('links.*');
$q->addQuery('contact_first_name, contact_last_name');
$q->addQuery('project_name, project_color_identifier, project_status');
$q->addQuery('task_name, task_id');

$q->addTable('links');

$q->leftJoin('users', 'u', 'user_id = link_owner');
$q->leftJoin('contacts', 'c', 'user_contact = contact_id');

if (!empty($search)) {
	$q->addWhere('(link_name LIKE \'%' . $search . '%\' OR link_description LIKE \'%' . $search . '%\')');
}
if ($project_id) { // Project
	$q->addWhere('link_project = ' . (int)$project_id);
}
if ($task_id) { // Task
	$q->addWhere('link_task = ' . (int)$task_id);
}
if ($catsql) { // Category
	$q->addWhere($catsql);
}
// Permissions
$project->setAllowedSQL($AppUI->user_id, $q, 'link_project');
$task->setAllowedSQL($AppUI->user_id, $q, 'link_task and task_project = link_project');
$q->addOrder('project_name, link_name');

//LIMIT ' . $xpg_min . ', ' . $xpg_pagesize ;
if ($canRead) {
	$links = $q->loadList();
} else {
	$AppUI->redirect('m=public&a=access_denied');
}
// counts total recs from selection
$xpg_totalrecs = count($links);

// How many pages are we dealing with here ??
$xpg_total_pages = ($xpg_totalrecs > $xpg_pagesize) ? ceil($xpg_totalrecs / $xpg_pagesize) : 0;

shownavbar_links($xpg_totalrecs, $xpg_pagesize, $xpg_total_pages, $page);

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
	$link_date = new CDate($row['link_date']);

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
	<td width="15%" nowrap="nowrap" align="center"><?php echo $link_date->format($df . ' ' . $tf); ?></td>
</tr>
<?php } ?>
</table>
<?php
shownavbar_links($xpg_totalrecs, $xpg_pagesize, $xpg_total_pages, $page);