<?php /* $Id$ $URL$ */
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}

/* FILES $Id$ */
// modified later by Pablo Roca (proca) in 18 August 2003 - added page support
// Files modules: index page re-usable sub-table
global $AppUI, $deny1, $canRead, $canEdit, $canAdmin;
global $company_id, $project_id, $task_id;

global $currentTabId;
global $currentTabName;
global $tabbed, $m;

$tab = ((!$company_id && !$project_id && !$task_id) || $m == 'files') ? $currentTabId : 0;
$page = w2PgetParam($_GET, 'page', 1);
if (!isset($project_id)) {
	$project_id = w2PgetParam($_REQUEST, 'project_id', 0);
}
if (!isset($showProject)) {
	$showProject = true;
}

$xpg_pagesize = w2PgetConfig('page_size', 50);
$xpg_min = $xpg_pagesize * ($page - 1); // This is where we start our record set from

// load the following classes to retrieved denied records

$project = new CProject();
$task = new CTask();

$df = $AppUI->getPref('SHDATEFORMAT');
$tf = $AppUI->getPref('TIMEFORMAT');

$file_types = w2PgetSysVal('FileType');
if (($company_id || $project_id || $task_id) && !($m == 'files')) {
	$catsql = false;
} elseif ($tabbed) {
	if ($tab <= 0) {
		$catsql = false;
	} else {
		$catsql = 'file_category = ' . ($tab-1);
	}
} else {
	if ($tab < 0) {
		$catsql = false;
	} else {
		$catsql = 'file_category = ' . $tab;
	}
}

// Fetch permissions once for all queries
$allowedProjects = $project->getAllowedSQL($AppUI->user_id, 'file_project');
$allowedTasks = $task->getAllowedSQL($AppUI->user_id, 'file_task');

// SQL text for count the total recs from the selected option
$q = new w2p_Database_Query;
$q->addQuery('count(file_id)');
$q->addTable('files', 'f');
$q->addJoin('projects', 'p', 'p.project_id = file_project');
$q->addJoin('tasks', 't', 't.task_id = file_task');
$q->leftJoin('project_departments', 'project_departments', 'p.project_id = project_departments.project_id OR project_departments.project_id IS NULL');
$q->leftJoin('departments', 'departments', 'departments.dept_id = project_departments.department_id OR dept_id IS NULL');
if (count($allowedProjects)) {
	$q->addWhere('( ( ' . implode(' AND ', $allowedProjects) . ') OR file_project = 0 )');
}
if (count($allowedTasks)) {
	$q->addWhere('( ( ' . implode(' AND ', $allowedTasks) . ') OR file_task = 0 )');
}
if ($catsql) {
	$q->addWhere($catsql);
}
if ($company_id) {
	$q->addWhere('project_company = ' . (int)$company_id);
}
if ($project_id) {
	$q->addWhere('file_project = ' . (int)$project_id);
}
if ($task_id) {
	$q->addWhere('file_task = ' . (int)$task_id);
}
$q->addGroup('file_version_id');

	// SETUP FOR FILE LIST
	$q2 = new w2p_Database_Query;
	$q2->addQuery('f.*, max(f.file_id) as latest_id, count(f.file_version) as file_versions, round(max(f.file_version),2) as file_lastversion');
	$q2->addQuery('ff.*');
	$q2->addTable('files', 'f');
	$q2->addJoin('file_folders', 'ff', 'ff.file_folder_id = file_folder');
	$q2->addJoin('projects', 'p', 'p.project_id = file_project');
	$q2->addJoin('tasks', 't', 't.task_id = file_task');
	$q2->leftJoin('project_departments', 'project_departments', 'p.project_id = project_departments.project_id OR project_departments.project_id IS NULL');
	$q2->leftJoin('departments', 'departments', 'departments.dept_id = project_departments.department_id OR dept_id IS NULL');
	if (count($allowedProjects)) {
		$q2->addWhere('( ( ' . implode(' AND ', $allowedProjects) . ') OR file_project = 0 )');
	}
	if (count($allowedTasks)) {
		$q2->addWhere('( ( ' . implode(' AND ', $allowedTasks) . ') OR file_task = 0 )');
	}
	if ($project_id) {
		$q2->addWhere('file_project = ' . (int)$project_id);
	}
	if ($task_id) {
		$q2->addWhere('file_task = ' . (int)$task_id);
	}
	if ($company_id) {
		$q2->addWhere('project_company = ' . (int)$company_id);
	}
	if ($catsql) {
		$q2->addWhere($catsql);
	}
	$q2->setLimit($xpg_pagesize, $xpg_min);
	// Adding an Order by that is different to a group by can cause
	// performance issues. It is far better to rearrange the group
	// by to get the correct ordering.
	$q2->addGroup('p.project_id');
	$q2->addGroup('file_version_id DESC');

	$q3 = new w2p_Database_Query;
	$q3->addTable('files');
	$q3->addQuery('file_id, file_version, file_project, file_name, file_task,
		file_description, u.user_username as file_owner, file_size, file_category,
		task_name, file_version_id,  file_checkout, file_co_reason, file_type,
		file_date, cu.user_username as co_user, project_name,
		project_color_identifier, project_owner, con.contact_first_name,
		con.contact_last_name, co.contact_first_name as co_contact_first_name,
		co.contact_last_name as co_contact_last_name ');
	$q3->addJoin('projects', 'p', 'p.project_id = file_project');
	$q3->addJoin('users', 'u', 'u.user_id = file_owner');
	$q3->addJoin('contacts', 'con', 'con.contact_id = u.user_contact');
	$q3->addJoin('tasks', 't', 't.task_id = file_task');
	$q3->addJoin('file_folders', 'ff', 'ff.file_folder_id = file_folder');
	if ($project_id) {
		$q3->addWhere('file_project = ' . (int)$project_id);
	}
	if ($task_id) {
		$q3->addWhere('file_task = ' . (int)$task_id);
	}
	if ($company_id) {
		$q3->addWhere('project_company = ' . (int)$company_id);
	}
	$q3->leftJoin('users', 'cu', 'cu.user_id = file_checkout');
	$q3->leftJoin('contacts', 'co', 'co.contact_id = cu.user_contact');

	$q3->leftJoin('project_departments', 'project_departments', 'p.project_id = project_departments.project_id OR project_departments.project_id IS NULL');
	$q3->leftJoin('departments', 'departments', 'departments.dept_id = project_departments.department_id OR dept_id IS NULL');
if (count($allowedProjects)) {
	$q3->addWhere('( ( ' . implode(' AND ', $allowedProjects) . ') OR file_project = 0 )');
}
if (count($allowedTasks)) {
	$q3->addWhere('( ( ' . implode(' AND ', $allowedTasks) . ') OR file_task = 0 )');
}
if ($catsql) {
	$q3->addWhere($catsql);
}

$files = array();
$file_versions = array();
if ($canRead) {
	$files = $q2->loadList();
	$file_versions = $q3->loadHashList('file_id');
}
// counts total recs from selection
$xpg_totalrecs = count($q->loadList());
//TODO: I don't like the ++$tab construct here... seems kludgy.
echo buildPaginationNav($AppUI, $m, $tab, $xpg_totalrecs, $xpg_pagesize, $page);
?>
<script language="javascript" type="text/javascript">
function expand(id){
  var element = document.getElementById(id);
  element.style.display = (element.style.display == '' || element.style.display == 'none') ? 'block' : 'none';
}
</script>

<table width="100%" border="0" cellpadding="2" cellspacing="1" class="tbl">
<tr>
	<th nowrap="nowrap"><?php echo $AppUI->_('File Name'); ?></th>
	<th nowrap="nowrap"><?php echo $AppUI->_('Description'); ?></th>
	<th nowrap="nowrap"><?php echo $AppUI->_('Versions'); ?></th>
	<th nowrap="nowrap"><?php echo $AppUI->_('Category'); ?></th>
	<th nowrap="nowrap"><?php echo $AppUI->_('Folder'); ?></th>
	<th nowrap="nowrap"><?php echo $AppUI->_('Task Name'); ?></th>
	<th nowrap="nowrap"><?php echo $AppUI->_('Owner'); ?></th>
	<th nowrap="nowrap"><?php echo $AppUI->_('Size'); ?></th>
	<th nowrap="nowrap"><?php echo $AppUI->_('Type'); ?></a></th>
	<th nowrap="nowrap"><?php echo $AppUI->_('Date'); ?></th>
	<th nowrap="nowrap"><?php echo $AppUI->_('Checkout Reason') ?></th>
	<th nowrap="nowrap"><?php echo $AppUI->_('co') ?></th>
	<th nowrap="nowrap">&nbsp;</th>
</tr>
<?php
	$fp = -1;
	$file_date = new w2p_Utilities_Date();

	$id = 0;
	foreach ($files as $file_row) {
		$latest_file = $file_versions[$file_row['latest_id']];
		$file_date = new w2p_Utilities_Date($latest_file['file_date']);

		if ($fp != $latest_file['file_project']) {
			if (!$latest_file['file_project']) {
				$latest_file['project_name'] = $AppUI->_('Not attached to a project');
				$latest_file['project_color_identifier'] = 'f4efe3';
			}
			if ($showProject) {
				$style = 'background-color:#' . $latest_file['project_color_identifier'] . ';color:' . bestColor($latest_file['project_color_identifier']);
				$s = '<tr>';
				$s .= '<td colspan="20" style="border: outset 2px #eeeeee;' . $style . '">';
				if ($latest_file['file_project'] > 0) {
					$href = './index.php?m=projects&a=view&project_id=' . $latest_file['file_project'];
				} else {
					$href = './index.php?m=projects';
				}
				$s .= '<a href="' . $href . '">';
				$s .= '<span style="' . $style . '">' . $latest_file['project_name'] . '</span></a>';
				$s .= '</td></tr>';
				echo $s;
			}
		}
		$fp = $latest_file['file_project'];
	?>
	<tr>
		<td nowrap="8%">
			<?php
				$fnamelen = 32;
				$filename = $latest_file['file_name'];
				if (strlen($latest_file['file_name']) > $fnamelen + 9) {
					$ext = substr($filename, strrpos($filename, '.') + 1);
					$filename = substr($filename, 0, $fnamelen);
					$filename .= '[...].' . $ext;
				}
				$myFile = new CFile();
				$file_icon = getIcon($file_row['file_type']);
				echo '<a href="./fileviewer.php?file_id=' . $latest_file['file_id'] . '"><img border="0" width="16" heigth="16" src="' . w2PfindImage($file_icon, 'files') . '" alt="" />&nbsp;' . $filename . '</a>';
			?>
		</td>
		<td width="20%"><?php echo w2p_textarea($latest_file['file_description']); ?></td>
		<td width="5%" nowrap="nowrap" align="right">
			<?php
		$hidden_table = '';
		echo $file_row['file_lastversion'];
		if ($file_row['file_versions'] > 1) {
			echo ' <a href="javascript: void(0);" onclick="expand(\'versions_' . $latest_file['file_id'] . '\'); ">(' . $file_row['file_versions'] . ')</a>';
			$hidden_table = '<tr><td colspan="20">
							<table style="display: none" id="versions_' . $latest_file['file_id'] . '" width="100%" border="0" cellpadding="2" cellspacing="1" class="tbl">
							<tr>
							        <th nowrap="nowrap">' . $AppUI->_('File Name') . '</th>
							        <th>' . $AppUI->_('Description') . '</th>
							        <th>' . $AppUI->_('Versions') . '</th>
							        <th>' . $AppUI->_('Category') . '</th>
									<th>' . $AppUI->_('Folder') . '</th>
							        <th nowrap="nowrap">' . $AppUI->_('Task Name') . '</th>
							        <th>' . $AppUI->_('Owner') . '</th>
							        <th>' . $AppUI->_('Size') . '</th>
							        <th>' . $AppUI->_('Type') . '</a></th>
							        <th>' . $AppUI->_('Date') . '</th>
						    		<th nowrap="nowrap">&nbsp;</th>
							</tr>';
			foreach ($file_versions as $file) {
				if ($file['file_version_id'] == $latest_file['file_version_id']) {
					$file_icon = getIcon($file['file_type']);
					$hdate = new w2p_Utilities_Date($file['file_date']);
					$hidden_table .= '<tr><td nowrap="8%"><a href="./fileviewer.php?file_id=' . $file['file_id'] . '" title="' . $file['file_description'] . '">' . '<img border="0" width="16" heigth="16" src="' . w2PfindImage($file_icon, 'files') . '" alt="" />&nbsp;' . $file['file_name'] . '
					  </a></td>
					  <td width="20%">' . $file['file_description'] . '</td>
					  <td width="5%" nowrap="nowrap" align="right">' . $file['file_version'] . '</td>
					  <td width="10%" nowrap="nowrap" align="left">' . $file_types[$file['file_category']] . '</td>
					  <td width="10%" nowrap="nowrap" align="left">' . (($file['file_folder_name'] != '') ? '<a href="' . W2P_BASE_URL . '/index.php?m=files&tab=' . (count($file_types) + 1) . '&folder=' . $file['file_folder_id'] . '">' . w2PshowImage('folder5_small.png', '16', '16', 'folder icon', 'show only this folder', 'files') . $file['file_folder_name'] . '</a>' : 'Root') . '</td>
					  <td width="5%" align="center"><a href="./index.php?m=tasks&a=view&task_id=' . $file['file_task'] . '">' . $file['task_name'] . '</a></td>
					  <td width="15%" nowrap="nowrap">' . $file['contact_first_name'] . ' ' . $file['contact_last_name'] . '</td>
					  <td width="5%" nowrap="nowrap" align="right">' . file_size(intval($file['file_size'])) . '</td>
					  <td nowrap="nowrap">' . $file['file_type'] . '</td>
					  <td width="15%" nowrap="nowrap" align="center">' . $AppUI->formatTZAwareTime($file['file_date'], $df . ' ' . $tf) . '</td>
					  <td nowrap="nowrap" width="20">';
					if ($canEdit && $w2Pconfig['files_show_versions_edit']) {
						$hidden_table .= '<a href="./index.php?m=files&a=addedit&file_id=' . $file['file_id'] . '">' . w2PshowImage('kedit.png', '16', '16', 'edit file', 'edit file', 'files') . "</a>";
					}
					$hidden_table .= '</td><tr>';
				}
			}
			$hidden_table .= '</table>';
		}
	?>
		</td>
		<td width="10%" nowrap="nowrap" align="left"><?php echo $file_types[$latest_file['file_category']]; ?></td>
		<td width="10%" nowrap="nowrap" align="left"><?php
		echo ($latest_file['file_folder_name'] != '') ? '<a href="' . W2P_BASE_URL . '/index.php?m=files&tab=' . (count($file_types) + 1) . '&folder=' . $latest_file['file_folder_id'] . '">' . w2PshowImage('folder5_small.png', '16', '16', 'folder icon', 'show only this folder', 'files') . $latest_file['file_folder_name'] . '</a>' : 'Root';?>
		</td>
		<td width="5%" align="left"><a href="./index.php?m=tasks&a=view&task_id=<?php echo $latest_file['file_task']; ?>"><?php echo $latest_file["task_name"]; ?></a></td>
		<td width="15%" nowrap="nowrap"><?php echo $latest_file['contact_first_name'] . ' ' . $latest_file['contact_last_name']; ?></td>
		<td width="5%" nowrap="nowrap" align="right"><?php echo file_size(intval($latest_file["file_size"])); ?></td>
		<td nowrap="nowrap"><?php echo $file['file_type']; ?></td>
		<td width="15%" nowrap="nowrap" align="center"><?php echo $AppUI->formatTZAwareTime($latest_file['file_date'], $df . ' ' . $tf); ?></td>
		<td width="10%"><?php echo $latest_file['file_co_reason']; ?></td>
		<td nowrap="nowrap">
		<?php if ($canEdit && empty($latest_file['file_checkout'])) {
	?>
				<a href="?m=files&a=co&file_id=<?php echo $latest_file['file_id']; ?>"><?php echo w2PshowImage('up.png', '16', '16', 'checkout', 'checkout file', 'files'); ?></a>
		<?php } else
		if ($latest_file['file_checkout'] == $AppUI->user_id) { ?>
				<a href="?m=files&a=addedit&ci=1&file_id=<?php echo $latest_file['file_id']; ?>"><?php echo w2PshowImage('down.png', '16', '16', 'checkin', 'checkin file', 'files'); ?></a>
		<?php } else {
			if ($latest_file['file_checkout'] == 'final') {
				echo 'final';
			} else {
				echo $latest_file['co_contact_first_name'] . ' ' . $latest_file['co_contact_last_name'] . '<br>(' . $latest_file['co_user'] . ')';
			}
		}
	?>

		</td>
		<td nowrap="nowrap" width="20">
		<?php if ($canEdit && (empty($latest_file['file_checkout']) || ($latest_file['file_checkout'] == 'final' && ($canEdit || $latest_file['project_owner'] == $AppUI->user_id)))) {
			echo '<a href="./index.php?m=files&a=addedit&file_id=' . $latest_file['file_id'] . '">' . w2PshowImage('kedit.png', '16', '16', 'edit file', 'edit file', 'files') . '</a>';
		}
	?>
		</td>
	</tr>
	<?php
		echo $hidden_table;
		$hidden_table = '';
	} ?>
</table>
<?php
echo buildPaginationNav($AppUI, $m, $tab, $xpg_totalrecs, $xpg_pagesize, $page);