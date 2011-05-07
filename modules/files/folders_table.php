<?php /* $Id$ $URL$ */
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}
global $AppUI, $deny1, $canRead, $canEdit, $allowed_folders_ary, $denied_folders_ary, $tab, $folder, $cfObj, $m, $a, $company_id, $allowed_companies, $showProject;

// Files modules: index page re-usable sub-table

// add to allow for returning to other modules besides Files
$current_uriArray = parse_url($_SERVER['REQUEST_URI']);
$current_uri = $current_uriArray['query'];

$page = (int) w2PgetParam($_GET, 'page', 1);

if (!isset($project_id)) {
	$project_id = (int) w2PgetParam($_REQUEST, 'project_id', 0);
}
if (!$project_id) {
	$showProject = true;
}

if (!isset($company_id)) {
	$company_id = (int) w2PgetParam($_REQUEST, 'company_id', 0);
}

$obj = new CCompany();
$allowed_companies_ary = $obj->getAllowedRecords($AppUI->user_id, 'company_id,company_name', 'company_name');
$allowed_companies = implode(',', array_keys($allowed_companies_ary));

if (!isset($task_id)) {
	$task_id = (int) w2PgetParam($_REQUEST, 'task_id', 0);
}

$xpg_pagesize = w2PgetConfig('page_size', 50);
$xpg_min = $xpg_pagesize * ($page - 1); // This is where we start our record set from

$project = new CProject();
$deny1 = $project->getDeniedRecords($AppUI->user_id);

$task = new CTask();
$deny2 = $task->getDeniedRecords($AppUI->user_id);

global $file_types;
$file_types = w2PgetSysVal('FileType');

$folder = $folder ? $folder : 0;

// SQL text for count the total recs from the selected option
$q = new w2p_Database_Query();
$q->addTable('files');
$q->addQuery('count(files.file_id)');
$q->addJoin('projects', 'p', 'p.project_id = file_project');
$q->addJoin('users', 'u', 'u.user_id = file_owner');
$q->addJoin('tasks', 't', 't.task_id = file_task');
$q->addJoin('file_folders', 'ff', 'ff.file_folder_id = file_folder');
$q->addWhere('file_folder = ' . (int)$folder);
if (count($deny1) > 0) {
	$q->addWhere('file_project NOT IN (' . implode(',', $deny1) . ')');
}
if (count($deny2) > 0) {
	$q->addWhere('file_task NOT IN (' . implode(',', $deny2) . ')');
}
if ($project_id) {
	$q->addWhere('file_project = ' . (int)$project_id);
}
if ($task_id) {
	$q->addWhere('file_task = ' . (int)$task_id);
}
if ($company_id) {
	$q->innerJoin('companies', 'co', 'co.company_id = p.project_company');
	$q->addWhere('company_id = ' . (int)$company_id);
	$q->addWhere('company_id IN (' . $allowed_companies . ')');
}

$q->addGroup('file_folder_name');
$q->addGroup('project_name');
$q->addGroup('file_name');

// counts total recs from selection
$xpg_totalrecs = count($q->loadList());
$q->clear();
$junkFile = new CFileFolder();    //This line is total junk.. it's just here so getFolderSelectList() can be included.
?>
<script language="javascript" type="text/javascript">
function expand(id){
	var element = document.getElementById(id);
	element.style.display = (element.style.display == '' || element.style.display == 'none') ? 'block' : 'none';
}
function addBulkComponent(li) {
//IE
	if (document.all || navigator.appName == 'Microsoft Internet Explorer') {
		var form = document.frm_bulk;
		var ni = document.getElementById('tbl_bulk');
		var newitem = document.createElement('input');
		var htmltxt = '';
		newitem.id = 'bulk_selected_file['+li+']';
		newitem.name = 'bulk_selected_file['+li+']';
		newitem.type = 'hidden';
		ni.appendChild(newitem);
	} else {
//Non IE
		var form = document.frm_bulk;
		var ni = document.getElementById('tbl_bulk');
		var newitem = document.createElement('input');
		newitem.setAttribute('id', 'bulk_selected_file['+li+']');
		newitem.setAttribute('name', 'bulk_selected_file['+li+']');
		newitem.setAttribute('type', 'hidden');
		ni.appendChild(newitem);
	}
}

function removeBulkComponent(li) {
	var t = document.getElementById('tbl_bulk');
	var old = document.getElementById('bulk_selected_file['+li+']');
	t.removeChild(old);
}
</script>
<style>
#folder-list {
/*  margin-left: -25px;*/
}
#folder-list ul {
	padding: 0;
	margin: 0;
}
#folder-list ul li {
	list-style: none;
	margin-top: -1px;
	margin-bottom: 0px;
	border: 0px solid #CCC;
}
#folder-list ul li ul li {
	margin-left: 25px;
}

.folder-name {
	display: block;
	height: 16px;
	padding-top: 0px;
	background: white;
	border-bottom: 1px solid #333;
	border-right: 1px solid #333;
	margin-bottom: 0px;
}

.folder-name-current {
	display: block;
	margin-bottom: 5px;
	font-weight: bold;
	border-bottom: 1px solid #333;
}

.has-files {
	font-weight: bold;
}

#folder-list .tbl {
	margin-top: 2px;
}
#folder-list .tbl th {
	border: none;
}

#folder-list p {
	padding: 3px 5px;
	margin-top: -5px;
	margin-left: 25px;
	margin-right: 25px;
	border: 1px solid #CCC;
	border-top: none;
	background: #F9F9F9;
}
</style>
<table id="tblFolders" width="100%" border="0" cellpadding="3" cellspacing="1" class="tbl">
<?php
/**** Main Program ****/
$canEdit = canEdit($m);
$canRead = canView($m);

if ($folder > 0) {
	$cfObj->load($folder);
	$msg = '';
	$canDelete = $cfObj->canDelete($msg, $folder);
}

if ($folder) { ?>
	<tr>
		<td nowrap="nowrap">
			<a href="./index.php?m=<?php echo $m; ?>&amp;&a=<?php echo $a; ?>&amp;tab=<?php echo $tab; ?>&folder=0"><?php echo w2PshowImage('home.png', '22', '22', 'folder icon', 'back to root folder', 'files'); ?></a>
			<?php if (array_key_exists($cfObj->file_folder_parent, $allowed_folders_ary)): ?>
			<a href="./index.php?m=<?php echo $m; ?>&amp;&a=<?php echo $a; ?>&amp;tab=<?php echo $tab; ?>&folder=<?php echo $cfObj->file_folder_parent; ?>"><?php echo w2PshowImage('back.png', '22', '22', 'folder icon', 'back to parent folder', 'files'); ?></a>
			<?php endif; ?>
			<a href="./index.php?m=<?php echo $m; ?>&amp;tab=<?php echo $tab; ?>&a=addedit_folder&folder=<?php echo $cfObj->file_folder_id; ?>" title="edit the <?php echo $cfObj->file_folder_name; ?> folder"><?php echo w2PshowImage('filesaveas.png', '22', '22', 'folder icon', 'edit folder', 'files'); ?></a>
		</td>
	</tr>
<?php
}

?>
    <tr>
        <td colspan="20">
            <span class="folder-name-current">
                <img src="<?php echo w2PfindImage('modules/files/folder5_small.png'); ?>" width="16" height="16" />
                <?php echo (isset($cfObj) && $cfObj->file_folder_name) ? $cfObj->file_folder_name : "Root"; ?>
            </span>
        </td>
    </tr>
    <tr>
        <td colspan="20">
            <div id="folder-list" style="background-color:white;layer-background-color:white;">
            <?php
            //	endif;
            if (isset($cfObj) && $cfObj->file_folder_description != ''): ?>
                    <p><?php echo w2p_textarea($cfObj->file_folder_description); ?></p>
            <?php
            endif;
            if (countFiles($folder) > 0) {
                echo displayfiles($folder);
            } elseif (!$limited or $folder != 0) {
                echo $AppUI->_('no files');
            }
            echo getFolders($folder);
            ?>
            </div>
        </td>
    </tr>

<?php
//Lets add our bulk form
$folders_avail = getFolderSelectList();
//used O (uppercase 0)instead of 0 (zero) to keep things in place
$folders = array('-1' => array(0 => 'O', 1 => '(Move to Folder)', 2 => -1)) + array('0' => array(0 => 0, 1 => 'Root', 2 => -1)) + $folders_avail;

$project = new CProject();
$sprojects = $project->getAllowedProjects($AppUI, false);
foreach ($sprojects as $prj_id => $proj_info) {
	$sprojects[$prj_id] = $idx_companies[$prj_id] . ': ' . $proj_info['project_name'];
}
asort($sprojects);
$sprojects = array('O' => '(' . $AppUI->_('Move to Project', UI_OUTPUT_RAW) . ')') + array('0' => '(' . $AppUI->_('All Projects', UI_OUTPUT_RAW) . ')') + $sprojects;
?>
	<tr>
	    <td colspan="50" align="right">
	          <form name="frm_bulk" method="post" action="?m=files&a=do_files_bulk_aed" accept-charset="utf-8">
                  <input type="hidden" name="redirect" value="<?php echo $current_uri; ?>" />
                  <table id="tbl_bulk">
                      <tr>
                            <td><?php echo arraySelect($sprojects, 'bulk_file_project', 'style="width:180px" class="text"', 'O'); ?></td>
                            <td><?php echo arraySelectTree($folders, 'bulk_file_folder', 'style="width:180px;" class="text"', 'O'); ?></td>
                            <td align="right"><input type="button" class="button" value="<?php echo $AppUI->_('Go'); ?>" onclick="if (confirm('Are you sure you wish to apply the options on the selected files?')) document.frm_bulk.submit();" /></td>
                      </tr>
                  </table>
	          </form>
	    </td>
	</tr>
</table>