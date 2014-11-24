<?php
/*  Copyright (c) 2007 Pedro A. (web2Project Development Team Member)
THIS MODULE WAS SPONSORED BY DUSTIN OF PURYEAR-IT.COM

This file is part of the web2Project ProjectDesigner module.

The ProjectDesigner module is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version, as long as you keep this copyright notice as well as
the sponsor.txt file which is also part of this module.

The Project Designer module is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with web2Project; if not, write to the Free Software
Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/
if (!defined('W2P_BASE_DIR')) {
    die('You should not access this file directly');
}
// @todo    convert to template
// @todo    remove database query

global $AppUI, $w2Pconfig, $cal_df, $cf;
// check permissions for this module
$perms = &$AppUI->acl();
$canView = canView($m);
$canAddProjects = $perms->checkModuleItem('projects', 'add');

if (!$canView) {
    $AppUI->redirect(ACCESS_DENIED);
}

$today = new w2p_Utilities_Date();
$today->addDays(1);
$today->setHour($w2Pconfig['cal_day_start']);
$today->setMinute(0);

$view_options = __extract_from_projectdesigner1($AppUI);

$project_id = (int) w2PgetParam($_POST, 'project_id', 0);
$project_id = (int) w2PgetParam($_GET, 'project_id', $project_id);

$extra = array('where' => 'project_active = 1');
$project = new CProject();
$projects = $project->getAllowedRecords($AppUI->user_id, 'projects.project_id,project_name', 'project_name', null, $extra, 'projects');

$idx_companies = __extract_from_projectdesigner2();

foreach ($projects as $prj_id => $prj_name) {
    $projects[$prj_id] = $idx_companies[$prj_id] . ': ' . $prj_name;
}
asort($projects);
$projects = arrayMerge(array('0' => $AppUI->_('(None)', UI_OUTPUT_RAW)), $projects);

$extra = array();
$task = new CTask();
$tasks = $task->getAllowedRecords($AppUI->user_id, 'task_id,task_name', 'task_name', null, $extra);
$tasks = arrayMerge(array('0' => $AppUI->_('(None)', UI_OUTPUT_RAW)), $tasks);

if (!$project_id) {
    // setup the title block
    $ttl = 'ProjectDesigner';
    $titleBlock = new w2p_Theme_TitleBlock($ttl, 'icon.png', $m);
    $titleBlock->addCrumb('?m=projects', 'projects list');
    $titleBlock->addCell();
    if ($canAddProjects) {
        $titleBlock->addButton('New project', '?m=projects&a=addedit');
    }
    $titleBlock->show();
?>
	<script language="javascript" type="text/javascript">
	function submitIt()
	{
		var f = document.prjFrm;
		var msg ='';
		if (f.project_id.value == 0) {
			msg += '<?php echo $AppUI->_('You must select a project first', UI_OUTPUT_JS); ?>';
			f.project_id.focus();
		}

		if (msg.length < 1) {
			f.submit();
		} else {
			alert(msg);
		}
	}
	</script>
<?php
    echo $AppUI->getTheme()->styleRenderBoxTop();
?>
    <form name="prjFrm" action="?m=projectdesigner" method="post" accept-charset="utf-8">
        <table border="1" class="std">
            <tr>
                <td class="projectdesigner">
                    <?php echo $AppUI->_('Project'); ?>: <?php echo arraySelect($projects, 'project_id', 'onchange="submitIt()" class="text"', 0); ?>
                </td>
            </tr>
        </table>
    </form>
<?php
} else {
    // check permissions for this record
    $canReadProject = $perms->checkModuleItem('projects', 'view', $project_id);
    $canEditProject = $perms->checkModuleItem('projects', 'edit', $project_id);
    $canViewTasks = canView('tasks');
    $canAddTasks = canAdd('tasks');
    $canEditTasks = canEdit('tasks');
    $canDeleteTasks = canDelete('tasks');

    if (!$canReadProject) {
        $AppUI->redirect(ACCESS_DENIED);
    }

    // check if this record has dependencies to prevent deletion
    $msg = '';
    $obj = new CProject();
    // Now check if the project is editable/viewable.
    $denied = $obj->getDeniedRecords($AppUI->user_id);
    if (in_array($project_id, $denied)) {
        $AppUI->redirect(ACCESS_DENIED);
    }

    $canDeleteProject = $obj->canDelete($msg, $project_id);

    $obj->load($project_id);

    if (!$obj) {
        $AppUI->setMsg('Project');
        $AppUI->setMsg('invalidID', UI_MSG_ERROR, true);
        $AppUI->redirect('m=' . $m);
    }

    // setup the title block
    $ttl = 'ProjectDesigner';
    $titleBlock = new w2p_Theme_TitleBlock($ttl, 'icon.png', $m);
    $titleBlock->addCrumb('?m=projects', 'projects list');
    $titleBlock->addCrumb('?m=' . $m, 'select another project');
    $titleBlock->addCrumb('?m=projects&a=view&bypass=1&project_id=' . $project_id, 'normal view project');

    $titleBlock->addButton('new link', '?m=links&a=addedit&project_id=' . $project_id);

    if ($canEditProject) {
        $titleBlock->addCell();
        $titleBlock->addButton('New event', '?m=events&a=addedit&event_project=' . $project_id);

        $titleBlock->addCell();
        $titleBlock->addButton('New file', '?m=files&a=addedit&project_id=' . $project_id);
        $titleBlock->addCrumb('?m=projects&a=addedit&project_id=' . $project_id, 'edit this project');
        if ($canDeleteProject) {
            $titleBlock->addCrumbDelete('delete project', false, $msg);
        }
    }
    if ($canAddTasks) {
        $titleBlock->addCell();
        $titleBlock->addButton('New task', '?m=tasks&a=addedit&task_project=' . $project_id);
    }

    $titleBlock->addCell();
    $titleBlock->addCell(w2PtoolTip($m, 'print project') . '<a href="javascript: void(0);" onclick ="window.open(\'index.php?m=projectdesigner&a=printproject&dialog=1&suppressHeaders=1&project_id=' . $project_id . '\', \'printproject\',\'width=1200, height=600, menubar=1, scrollbars=1\')">
      		<img src="' . w2PfindImage('printer.png') . '" />
      		</a>
      		' . w2PendTip());
    $titleBlock->addCell(w2PtoolTip($m, 'expand all panels') . '<a href="javascript: void(0);" onclick ="expandAll()">
      		<img src="' . w2PfindImage('down.png', $m) . '" />
      		</a>
      		' . w2PendTip());
    $titleBlock->addCell(w2PtoolTip($m, 'collapse all panels') . '<a href="javascript: void(0);" onclick ="collapseAll()">
      		<img src="' . w2PfindImage('up.png', $m) . '" />
      		</a>
      		' . w2PendTip());
    $titleBlock->addCell(w2PtoolTip($m, 'save your workspace') . '<a href="javascript: void(0);" onclick ="document.frmWorkspace.submit()">
      		<img src="' . w2PfindImage('filesave.png', $m) . '" />
      		</a>
      		' . w2PendTip());
    $titleBlock->addCell();
    $titleBlock->show();
?>
<form name="frmWorkspace" action="?m=<?php echo $m; ?>" method="post" accept-charset="utf-8">
	<input type="hidden" name="dosql" value="do_projectdesigner_aed" />
	<input type="hidden" name="project_id" value="<?php echo $project_id; ?>" />
	<input type="hidden" name="pd_option_view_project" value="<?php echo (isset($view_options[0]['pd_option_view_project']) ? $view_options[0]['pd_option_view_project'] : 1); ?>" />
	<input type="hidden" name="pd_option_view_gantt" value="<?php echo (isset($view_options[0]['pd_option_view_gantt']) ? $view_options[0]['pd_option_view_gantt'] : 1); ?>" />
	<input type="hidden" name="pd_option_view_tasks" value="<?php echo (isset($view_options[0]['pd_option_view_tasks']) ? $view_options[0]['pd_option_view_tasks'] : 1); ?>" />
	<input type="hidden" name="pd_option_view_actions" value="<?php echo (isset($view_options[0]['pd_option_view_actions']) ? $view_options[0]['pd_option_view_actions'] : 1); ?>" />
	<input type="hidden" name="pd_option_view_addtasks" value="<?php echo (isset($view_options[0]['pd_option_view_addtasks']) ? $view_options[0]['pd_option_view_addtasks'] : 1); ?>" />
	<input type="hidden" name="pd_option_view_files" value="<?php echo (isset($view_options[0]['pd_option_view_files']) ? $view_options[0]['pd_option_view_files'] : 1); ?>" />
</form>

<?php
    $priorities = w2Pgetsysval('TaskPriority');
    $types = w2Pgetsysval('TaskType');
    $durntype = w2PgetSysVal('TaskDurationType');
    $task_access = array(CTask::ACCESS_PUBLIC => 'Public',
        CTask::ACCESS_PROTECTED => 'Protected', CTask::ACCESS_PARTICIPANT => 'Participant',
        CTask::ACCESS_PRIVATE => 'Private');
    $extra = array(0 => '(none)', 1 => 'Milestone', 2 => 'Dynamic Task', 3 => 'Inactive Task');
    $sel_priorities = arraySelect($priorities, 'add_task_priority0', 'style="width:80px" class="text"', '0');
    $sel_types = arraySelect($types, 'add_task_type0', 'style="width:80px" class="text"', '');
    $sel_access = arraySelect($task_access, 'add_task_access0', 'style="width:80px" class="text"', '');
    $sel_extra = arraySelect($extra, 'add_task_extra0', 'style="width:80px" class="text"', '');
    $sel_durntype = arraySelect($durntype, 'add_task_durntype0', 'style="width:80px" class="text"', '', true);
?>
<script language="javascript" type="text/javascript">

$( document ).ready(function () {
    expand_collapse('project', 'tblProjects', 'expand');
});
// security improvement:
// some javascript functions may not appear on client side in case of user not having write permissions
// else users would be able to arbitrarily run 'bad' functions
<?php
    if ($canEdit) {
?>
function delIt()
{
	if (confirm( '<?php echo $AppUI->_('doDelete', UI_OUTPUT_JS) . ' ' . $AppUI->_('Project', UI_OUTPUT_JS) . '?'; ?>' )) {
		document.frmDelete.submit();
	}
}
<?php } ?>

var sel_priorities = '<?php echo mb_str_replace(chr(10), '', $sel_priorities); ?>';
var sel_types = '<?php echo mb_str_replace(chr(10), '', $sel_types); ?>';
var sel_access = '<?php echo mb_str_replace(chr(10), '', $sel_access); ?>';
var sel_extra = '<?php echo mb_str_replace(chr(10), '', $sel_extra); ?>';
var sel_durntype = '<?php echo mb_str_replace(chr(10), '', $sel_durntype); ?>';

function addComponent()
{
	var form = document.editFrm;
	var li = parseInt(form.nrcomponents.value);
	var line_nr = li+1;

	var ni = document.getElementById('tcomponents');
	var li = li+1;

	priorities = sel_priorities.replace('priority0','priority_'+line_nr);
	priorities = priorities.replace('priority0','priority_'+line_nr);
	types = sel_types.replace('type0','type_'+line_nr);
	types = types.replace('type0','type_'+line_nr);
	access = sel_access.replace('access0','access_'+line_nr);
	access = access.replace('access0','access_'+line_nr);
	extra = sel_extra.replace('extra0','extra_'+line_nr);
	extra = extra.replace('extra0','extra_'+line_nr);
	durntype = sel_durntype.replace('durntype0', 'durntype_'+line_nr);
	durntype = durntype.replace('durntype0', 'durntype_'+line_nr);

	eval('oldType_'+line_nr+'=""');

	var trIdName = 'component'+li+'_';
	var newtr = document.createElement('tr');
	var htmltxt = '';
	newtr.setAttribute('id',trIdName);
	oCell = document.createElement('td');
	oCell.setAttribute ('align','left');
	oCell.setAttribute ('width','5');
	htmltxt = '';
	htmltxt +='<a href="javascript: void(0);" onclick="removeComponent(\'component'+line_nr+'_\')"><img src="<?php echo w2PfindImage('remove.png', $m); ?>" /></a>';
	oCell.innerHTML =htmltxt;
	newtr.appendChild(oCell);
	oCell = document.createElement('td');
	htmltxt = '';
	htmltxt +='<input type="hidden" id="add_task_line_'+line_nr+'" name="add_task_line_'+line_nr+'" value="'+line_nr+'" />';
	htmltxt +='<input type="text" class="text" style="width:200px;" name="add_task_name_'+line_nr+'" value="" />';
	htmltxt +='&nbsp;<?php echo w2PtoolTip('add tasks panel', 'click here to add a description to this task and/or edit other available options.<br />click again to collapse it.'); ?><a href="javascript: void(0);" onclick="expand_collapse(\'component'+li+'_desc\', \'tblProjects\')"><img id="component'+li+'_desc_expand" src="<?php echo w2PfindImage('icons/expand.gif', $m); ?>" /><img id="component'+li+'_desc_collapse" src="<?php echo w2PfindImage('icons/collapse.gif', $m); ?>" style="display:none" /></a><?php echo w2PendTip(); ?>';
	oCell.innerHTML =htmltxt;
	newtr.appendChild(oCell);
	oCell = document.createElement('td');
	htmltxt = '';
	htmltxt +='<input type="hidden" id="add_task_start_date_'+line_nr+'" name="add_task_start_date_'+line_nr+'" value="<?php echo $today->format(FMT_TIMESTAMP); ?>" />';
	htmltxt +='<input type="text" onchange="setDate(\'editFrm\', \'start_date_'+line_nr+'\');" class="text" style="width:130px;" id="start_date_'+line_nr+'" name="start_date_'+line_nr+'" value="<?php echo $today->format($cf); ?>" />';
	htmltxt +='<a href="javascript: void(0);" onclick="return showCalendar(\'start_date_'+line_nr+'\', \'<?php echo $cf ?>\', \'editFrm\', \'<?php echo (strpos($cf, '%p') !== false ? '12' : '24') ?>\', true)" >';
	htmltxt +='&nbsp;<img src="<?php echo w2PfindImage('calendar.gif', $m); ?>" />';
	htmltxt +='</a>';
	oCell.innerHTML =htmltxt;
	newtr.appendChild(oCell);
	oCell = document.createElement('td');
	htmltxt = '';
	htmltxt +='<input type="hidden" id="add_task_end_date_'+line_nr+'" name="add_task_end_date_'+line_nr+'" value="<?php $today->setDate($today->getTime() + 60 * 60, DATE_FORMAT_UNIXTIME);
    echo $today->format(FMT_TIMESTAMP); ?>" />';
	htmltxt +='<input type="text" onchange="setDate(\'editFrm\', \'end_date_'+line_nr+'\');" class="text" style="width:130px;" id="end_date_'+line_nr+'" name="end_date_'+line_nr+'" value="<?php echo $today->format($cf); ?>" />';
	htmltxt +='<a href="javascript: void(0);" onclick="return showCalendar(\'end_date_'+line_nr+'\', \'<?php echo $cf ?>\', \'editFrm\', \'<?php echo (strpos($cf, '%p') !== false ? '12' : '24') ?>\', true)" >';
	htmltxt +='&nbsp;<img src="<?php echo w2PfindImage('calendar.gif', $m); ?>" />';
	htmltxt +='</a>';
	oCell.innerHTML =htmltxt;
	newtr.appendChild(oCell);
	oCell = document.createElement('td');
	htmltxt = '';
	htmltxt +='<input type="text" class="text" style="width:40px;text-align:right;" id="add_task_duration_'+line_nr+'" name="add_task_duration_'+line_nr+'" value="1" />';
	htmltxt += '&nbsp;'+durntype ;
	oCell.innerHTML =htmltxt;
	newtr.appendChild(oCell);
	ni.appendChild(newtr);
	oCell = document.createElement('td');
	htmltxt = '';
	htmltxt +=priorities;
	oCell.innerHTML =htmltxt;
	newtr.appendChild(oCell);
	ni.appendChild(newtr);
	oCell = document.createElement('td');
	htmltxt = '';
	htmltxt +=types;
	oCell.innerHTML =htmltxt;
	newtr.appendChild(oCell);
	oCell = document.createElement('td');
	htmltxt = '';
	htmltxt +=access;
	oCell.innerHTML =htmltxt;
	newtr.appendChild(oCell);
	oCell = document.createElement('td');
	htmltxt = '';
	htmltxt +=extra;
	oCell.innerHTML =htmltxt;
	newtr.appendChild(oCell);
	ni.appendChild(newtr);
	var trIdName = 'component'+li+'_desc';
	var newtr = document.createElement('tr');
	newtr.setAttribute ('valign','top');
	newtr.style.display = 'none';
	var htmltxt = '';
	newtr.setAttribute('id',trIdName);
	oCell = document.createElement('td');
	oCell.setAttribute ('align','left');
	oCell.colSpan = 5;
	oCell.setAttribute ('valign','top');
	htmltxt = '';
	htmltxt +='<b><?php echo $AppUI->_('Task Description'); ?></b>:<br />';
	htmltxt +='<textarea cols="80" rows="8" id="add_task_description_'+line_nr+'" name="add_task_description_'+line_nr+'" /></textarea>';
	oCell.innerHTML =htmltxt;
	newtr.appendChild(oCell);
	ni.appendChild(newtr);
	form.nrcomponents.value = li;
	end_date = eval( 'document.editFrm.add_task_end_date_'+line_nr );
	start_date = eval( 'document.editFrm.add_task_start_date_'+line_nr );
	duration_fld = eval( 'document.editFrm.add_task_duration_'+line_nr );
	durntype_fld = eval( 'document.editFrm.add_task_durntype_'+line_nr );
	//calcDuration(document.editFrm, start_date, end_date, duration_fld, durntype_fld);

//activate new tooltips on the fly
    $("span").tipTip({maxWidth: "auto", delay: 200, fadeIn: 150, fadeOut: 150});
}

function removeComponent(tr_id)
{
    var table_row = document.getElementById(tr_id);
    var table_row_description = document.getElementById(tr_id+'desc');
    table = table_row.parentNode;
    table.removeChild(table_row);
    table.removeChild(table_row_description);
//deactivate new tooltips on the fly
	var as = [];
	$$('span').each(function (span) {
		if (span.getAttribute('title')) as.push(span);
	});
	new Tips(as), {}
}

var check_task_dates = <?php
    if (isset($w2Pconfig['check_task_dates']) && $w2Pconfig['check_task_dates'])
        echo 'true';
    else
        echo 'false';
?>;
var can_edit_time_information = <?php echo $can_edit_time_information ? 'true' : 'false'; ?>;

var task_name_msg = '<?php echo $AppUI->_('taskName'); ?>';
var task_start_msg = '<?php echo $AppUI->_('taskValidStartDate'); ?>';
var task_end_msg = '<?php echo $AppUI->_('taskValidEndDate'); ?>';

var workHours = <?php echo w2PgetConfig('daily_working_hours'); ?>;
//working days array from config.php
var working_days = new Array(<?php echo w2PgetConfig('cal_working_days'); ?>);
var cal_day_start = <?php echo (int) w2PgetConfig('cal_day_start'); ?>;
var cal_day_end = <?php echo (int) w2PgetConfig('cal_day_end'); ?>;
var daily_working_hours = <?php echo (int) w2PgetConfig('daily_working_hours'); ?>;
var oldProj = '<?php echo htmlentities($obj->project_name, ENT_QUOTES) . ':'; ?>';

/* TODO: This needs to be refactored to use the core setDate_new function. */
function setDate(frm_name, f_date)
{
	fld_date = eval( 'document.' + frm_name + '.' + f_date );
	fld_task_date = eval( 'document.' + frm_name + '.' + 'add_task_' + f_date );
	if (fld_date.value.length>0) {
		if ((parseDate(fld_date.value))==null) {
			alert('The Date/Time you typed does not match your prefered format, please retype.');
			fld_task_date.value = '';
			fld_date.style.backgroundColor = 'red';
		} else {
			fld_task_date.value = formatDate(parseDate(fld_date.value), 'yyyyMMddHHmm');
			fld_date.value = formatDate(parseDate(fld_date.value), '<?php echo $cal_df ?>');
			fld_date.style.backgroundColor = '';
			if (frm_name.indexOf('editFrm')>-1) {
				if (f_date.indexOf('start_date')>-1) {
					start_date = fld_task_date;
					end_date = eval( 'document.' + frm_name + '.' + 'add_task_' + f_date.replace('start_date','end_date') );
					duration_fld = eval( 'document.' + frm_name + '.' + 'add_task_' + f_date.replace('start_date','duration') );
					durntype_fld = eval( 'document.' + frm_name + '.' + 'add_task_' + f_date.replace('start_date','durntype') );
				} else {
					end_date = fld_task_date;
					start_date = eval( 'document.' + frm_name + '.' + 'add_task_' + f_date.replace('end_date','start_date') );
					duration_fld = eval( 'document.' + frm_name + '.' + 'add_task_' + f_date.replace('end_date','duration') );
					durntype_fld = eval( 'document.' + frm_name + '.' + 'add_task_' + f_date.replace('end_date','durntype') );
				}
//				calcDuration(document.editFrm, start_date, end_date, duration_fld, durntype_fld);
			}
		}
	} else {
		fld_task_date.value = '';
	}
}

function calcDuration(f, start_date, end_date, duration_fld, durntype_fld)
{
    var start_value = start_date.value;
    var end_value = end_date.value;

    xajax_calcDuration(start_value.substring(0,8), start_value.substring(8,10), start_value.substring(10,12),
                       end_value.substring(0,8), end_value.substring(8,10), end_value.substring(10,12),
                       durntype_fld, duration_fld.name);
}

</script>

<?php
    echo $AppUI->getTheme()->styleRenderBoxTop();
?>
<form name="frmDelete" action="./index.php?m=projects" method="post" accept-charset="utf-8">
	<input type="hidden" name="dosql" value="do_project_aed" />
	<input type="hidden" name="del" value="1" />
	<input type="hidden" name="project_id" value="<?php echo $project_id; ?>" />
</form>

<?php
    $project = $obj;

    // get critical tasks (criteria: task_end_date)
    $criticalTasks = ($project_id > 0) ? $project->getCriticalTasks($project_id) : null;
    // create Date objects from the datetime fields
    $end_date = intval($project->project_end_date) ? new w2p_Utilities_Date($project->project_end_date) : null;
    $actual_end_date = null;
    if (isset($criticalTasks)) {
        $actual_end_date = intval($criticalTasks[0]['task_end_date']) ? new w2p_Utilities_Date($criticalTasks[0]['task_end_date']) : null;
    }
    $style = (($actual_end_date > $end_date) && !empty($end_date)) ? 'style="color:red; font-weight:bold"' : '';

    $projectPriority = w2PgetSysVal('ProjectPriority');
    $projectPriorityColor = w2PgetSysVal('ProjectPriorityColor');
    $billingCategory = w2PgetSysVal('BudgetCategory');
    $pstatus = w2PgetSysVal('ProjectStatus');
    $ptype = w2PgetSysVal('ProjectType');

    include $AppUI->getTheme()->resolveTemplate('projects/view');

    echo $AppUI->getTheme()->styleRenderBoxBottom();
    echo $AppUI->getTheme()->styleRenderBoxTop();
?>
<table class="std">
<tr>
	<td style="border: outset #d1d1cd 1px;" colspan="2">
        <a href="javascript: void(0);" name="fg" style="display:block" onclick="expand_collapse('gantt', 'tblProjects');update_workspace('gantt');">
            <strong class="left"><?php echo $AppUI->_('Gantt Chart'); ?></strong>
            <div class="right">
                <img id="gantt_expand" src="<?php echo w2PfindImage('icons/expand.gif', $m); ?>"
                     alt="" <?php echo (isset($view_options[0]['pd_option_view_gantt']) ? ($view_options[0]['pd_option_view_gantt'] ? 'style="display:none"' : 'style="display:"') : 'style="display:none"') ?>>
                <img id="gantt_collapse" src="<?php echo w2PfindImage('icons/collapse.gif', $m); ?>"
                     <?php echo (isset($view_options[0]['pd_option_view_gantt']) ? ($view_options[0]['pd_option_view_gantt'] ? 'style="display:"' : 'style="display:none"') : 'style="display:"') ?>>
            </div>
        </a>
	</td>
</tr>
<tr id="gantt" <?php echo (isset($view_options[0]['pd_option_view_gantt']) ? ($view_options[0]['pd_option_view_gantt'] ? 'style="visibility:visible;display:"' : 'style="visibility:collapse;display:none"') : 'style="visibility:visible;display:"'); ?>>
	<td colspan="2" class="hilite">
	<?php
    if ($canViewTasks) {
        require (w2PgetConfig('root_dir') . '/modules/projectdesigner/vw_gantt.php');
    } else {
        echo $AppUI->_('You do not have permission to view tasks');
    }
?>
	</td>
</tr>
</table>
<?php
    echo $AppUI->getTheme()->styleRenderBoxBottom();
    echo $AppUI->getTheme()->styleRenderBoxTop();
?>
<table class="std">
<tr>
	<td style="border: outset #d1d1cd 1px;" colspan="2">
        <a href="javascript: void(0);" name="fg" style="display:block" onclick="expand_collapse('tasks', 'tblProjects');update_workspace('tasks');">
            <strong class="left"><?php echo $AppUI->_('Tasks'); ?></strong>
            <div class="right">
                <img id="tasks_expand" src="<?php echo w2PfindImage('icons/expand.gif', $m); ?>"
                     alt="" <?php echo (isset($view_options[0]['pd_option_view_tasks']) ? ($view_options[0]['pd_option_view_tasks'] ? 'style="display:none"' : 'style="display:"') : 'style="display:none"') ?>>
                <img id="tasks_collapse" src="<?php echo w2PfindImage('icons/collapse.gif', $m); ?>"
                     <?php echo (isset($view_options[0]['pd_option_view_tasks']) ? ($view_options[0]['pd_option_view_tasks'] ? 'style="display:"' : 'style="display:none"') : 'style="display:"') ?>>
            </div>
        </a>
	</td>
</tr>
<tr id="tasks" <?php echo (isset($view_options[0]['pd_option_view_tasks']) ? ($view_options[0]['pd_option_view_tasks'] ? 'style="visibility:visible;display:"' : 'style="visibility:collapse;display:none"') : 'style="visibility:visible;display:"'); ?>>
	<td colspan="2" class="hilite">
	<?php
    if ($canViewTasks) {
        require (w2PgetConfig('root_dir') . '/modules/tasks/vw_tasks.php');
    } else {
        echo $AppUI->_('You do not have permission to view tasks');
    }
?>
	</td>
</tr>
</table>
<?php
    echo $AppUI->getTheme()->styleRenderBoxBottom();
    echo $AppUI->getTheme()->styleRenderBoxTop();
?>
<table class="std">
<tr>
	<td style="border: outset #d1d1cd 1px;" colspan="2">
        <a href="javascript: void(0);" name="fg" style="display:block" onclick="expand_collapse('actions', 'tblProjects');update_workspace('actions');">
            <strong class="left"><?php echo $AppUI->_('Actions'); ?></strong>
            <div class="right">
                <img id="actions_expand" src="<?php echo w2PfindImage('icons/expand.gif', $m); ?>"
                     alt="" <?php echo (isset($view_options[0]['pd_option_view_actions']) ? ($view_options[0]['pd_option_view_actions'] ? 'style="display:none"' : 'style="display:"') : 'style="display:none"') ?>>
                <img id="actions_collapse" src="<?php echo w2PfindImage('icons/collapse.gif', $m); ?>"
                     <?php echo (isset($view_options[0]['pd_option_view_actions']) ? ($view_options[0]['pd_option_view_actions'] ? 'style="display:"' : 'style="display:none"') : 'style="display:"') ?>>
            </div>
        </a>
	</td>
</tr>
<tr id="actions" <?php echo (isset($view_options[0]['pd_option_view_actions']) ? ($view_options[0]['pd_option_view_actions'] ? 'style="visibility:visible;display:"' : 'style="visibility:collapse;display:none"') : 'style="visibility:visible;display:"'); ?>>
	<td colspan="2" class="hilite">
	<?php
    if ($canEditTasks) {
        require w2PgetConfig('root_dir') . '/modules/projectdesigner/vw_actions.php';
    } else {
        echo $AppUI->_('You do not have permission to edit tasks');
    }
?>
	</td>
</tr>
</table>
<?php
    echo $AppUI->getTheme()->styleRenderBoxBottom();
    echo $AppUI->getTheme()->styleRenderBoxTop();
?>
<table class="std">
<tr>
	<td style="border: outset #d1d1cd 1px;" colspan="2">
        <a href="javascript: void(0);" name="fg" style="display:block" onclick="expand_collapse('addtasks', 'tblProjects');update_workspace('addtasks');">
            <strong class="left"><?php echo $AppUI->_('Add Tasks'); ?></strong>
            <div class="right">
                <img id="addtasks_expand" src="<?php echo w2PfindImage('icons/expand.gif', $m); ?>"
                     alt="" <?php echo (isset($view_options[0]['pd_option_view_addtasks']) ? ($view_options[0]['pd_option_view_addtasks'] ? 'style="display:none"' : 'style="display:"') : 'style="display:none"') ?>>
                <img id="addtasks_collapse" src="<?php echo w2PfindImage('icons/collapse.gif', $m); ?>"
                     <?php echo (isset($view_options[0]['pd_option_view_addtasks']) ? ($view_options[0]['pd_option_view_addtasks'] ? 'style="display:"' : 'style="display:none"') : 'style="display:"') ?>>
            </div>
        </a>
	</td>
</tr>
<tr id="addtasks" <?php echo (isset($view_options[0]['pd_option_view_addtasks']) ? ($view_options[0]['pd_option_view_addtasks'] ? 'style="visibility:visible;display:"' : 'style="visibility:collapse;display:none"') : 'style="visibility:visible;display:"'); ?>>
	<td colspan="2" class="hilite">
	<?php
    if ($canAddTasks) {
        require w2PgetConfig('root_dir') . '/modules/projectdesigner/vw_addtasks.php';
    } else {
        echo $AppUI->_('You do not have permission to add tasks');
    }
?>
	</td>
</tr>
</table>
<?php
    echo $AppUI->getTheme()->styleRenderBoxBottom();
    echo $AppUI->getTheme()->styleRenderBoxTop();
?>
<table class="std">
<tr>
	<td style="border: outset #d1d1cd 1px;" colspan="2">
        <a href="javascript: void(0);" name="fg" style="display:block" onclick="expand_collapse('files', 'tblProjects');update_workspace('files');">
            <strong class="left"><?php echo $AppUI->_('Files'); ?></strong>
            <div class="right">
                <img id="files_expand" src="<?php echo w2PfindImage('icons/expand.gif', $m); ?>"
                     alt="" <?php echo (isset($view_options[0]['pd_option_view_files']) ? ($view_options[0]['pd_option_view_files'] ? 'style="display:none"' : 'style="display:"') : 'style="display:none"') ?>>
                <img id="files_collapse" src="<?php echo w2PfindImage('icons/collapse.gif', $m); ?>"
                     <?php echo (isset($view_options[0]['pd_option_view_files']) ? ($view_options[0]['pd_option_view_files'] ? 'style="display:"' : 'style="display:none"') : 'style="display:"') ?>>
            </div>
        </a>
	</td>
</tr>
<tr id="files" <?php echo (isset($view_options[0]['pd_option_view_files']) ? ($view_options[0]['pd_option_view_files'] ? 'style="visibility:visible;display:"' : 'style="visibility:collapse;display:none"') : 'style="visibility:visible;display:"'); ?>>
	<td colspan="2" class="hilite">
	<?php
    //Permission check here
    $canViewFiles = canView('files');
    if ($canViewFiles) {
        require w2PgetConfig('root_dir') . '/modules/projectdesigner/vw_files.php';
    } else {
        echo $AppUI->_('You do not have permission to view files');
    }
?>
	</td>
</tr>
</table>

<script language="javascript" type="text/javascript">
    var original_bgc = getStyle('td_sample', 'background-color', 'backgroundColor');
</script>
<?php
}
