<?php
if (!defined('W2P_BASE_DIR')) {
    die('You should not access this file directly.');
}
// @todo    convert to template

global $AppUI, $w2Pconfig, $projects, $project_id;
?>

<script language="javascript" type="text/javascript">
function buildTaskName(id)
{
	var f = document.editFrm;
	var taskNameObj = eval('f.add_task_name_'+id);
	var taskName = eval('f.add_task_name_'+id+'.value');
	var oldType = eval('oldType_'+id);
	taskName = taskName.replace(oldProj, '');
	taskName = taskName.replace(oldType, '');
	var taskType = eval('f.add_task_type_'+id+'.options[f.add_task_type_'+id+'.selectedIndex].text');
	var newTaskName = oldProj+taskName+'-'+taskType;
	taskNameObj.value = newTaskName;
	oldTaskName = newTaskName;
	eval("oldType_"+id+" = '-'+'"+taskType+"'");
}

function addTasks()
{
	var f = document.editFrm;
	var ok = true;
	for (i=0,i_cmp=f.length;i<i_cmp;i++) {
		var tempobj=f.elements[i]
		if (tempobj.name.substring(0,14)=='add_task_name_') {
			if (((tempobj.type=='text'||tempobj.type=='textarea')&&tempobj.value=='')||(tempobj.type.toString().charAt(0)=='s'&&tempobj.selectedIndex==-1)) {
				alert('<?php echo $AppUI->_('At least one task name is lacking.');?>');
				f.elements[i].focus();
				ok = false;
				break;
			}
		}
	}
	for (i=0,i_cmp=f.length;i<i_cmp;i++) {
		var tempobj=f.elements[i]
		if (tempobj.name.substring(0,20)=='add_task_start_date_') {
			var int_st_date = new String(tempobj.name);
			var int_en_date = new String(int_st_date.replace(/start_date_/,'end_date_'));
			var st_date = new String(tempobj.name.replace(/add_task_/,''));
			var en_date = new String(st_date.replace(/start_date_/,'end_date_'));
			st_date = eval('f.'+st_date);
			en_date = eval('f.'+en_date);
			int_st_date = eval('f.'+int_st_date+'.value');
			int_en_date = eval('f.'+int_en_date+'.value');
			var sDate = new Date(int_st_date.substring(0,4),(int_st_date.substring(4,6)-1),int_st_date.substring(6,8), int_st_date.substring(8,10), int_st_date.substring(10,12));
			var eDate = new Date(int_en_date.substring(0,4),(int_en_date.substring(4,6)-1),int_en_date.substring(6,8), int_en_date.substring(8,10), int_en_date.substring(10,12));
			var s = Date.UTC(int_st_date.substring(0,4),(int_st_date.substring(4,6)-1),int_st_date.substring(6,8), int_st_date.substring(8,10), int_st_date.substring(10,12));
			var e = Date.UTC(int_en_date.substring(0,4),(int_en_date.substring(4,6)-1),int_en_date.substring(6,8), int_en_date.substring(8,10), int_en_date.substring(10,12));
			if (s > e && int_st_date.length>0 && int_en_date.length>0) {
				st_date.style.backgroundColor = 'red';
				en_date.style.backgroundColor = 'red';
				ok = false;
				break;
			} else {
				st_date.style.backgroundColor = '';
				en_date.style.backgroundColor = '';
			}
		}
	}
	if (ok) {
		f.submit();
	}

}
</script>

<form name="editFrm" action="?m=<?php echo $m; ?>" method="post" accept-charset="utf-8">
	<input type="hidden" name="dosql" value="do_addtasks_aed" />
	<input type="hidden" name="project" value="<?php echo $project_id; ?>" />
	<input type="hidden" name="project_name" value="<?php echo $obj->project_name; ?>" />
	<input type="hidden" name="redirect" value="<?php echo $redirect; ?>" />
	<input type="hidden" name="nrcomponents" value="0" />
	<input type="hidden" name="pd_option_view_project" value="<?php echo (isset($view_options[0]['pd_option_view_project']) ? $view_options[0]['pd_option_view_project'] : 1); ?>" />
	<input type="hidden" name="pd_option_view_gantt" value="<?php echo (isset($view_options[0]['pd_option_view_gantt']) ? $view_options[0]['pd_option_view_gantt'] : 1); ?>" />
	<input type="hidden" name="pd_option_view_tasks" value="<?php echo (isset($view_options[0]['pd_option_view_tasks']) ? $view_options[0]['pd_option_view_tasks'] : 1); ?>" />
	<input type="hidden" name="pd_option_view_actions" value="<?php echo (isset($view_options[0]['pd_option_view_actions']) ? $view_options[0]['pd_option_view_actions'] : 1); ?>" />
	<input type="hidden" name="pd_option_view_addtasks" value="<?php echo (isset($view_options[0]['pd_option_view_addtasks']) ? $view_options[0]['pd_option_view_addtasks'] : 1); ?>" />
	<input type="hidden" name="pd_option_view_files" value="<?php echo (isset($view_options[0]['pd_option_view_files']) ? $view_options[0]['pd_option_view_files'] : 1); ?>" />
    <table cellspacing="1" cellpadding="2" border="0" width="100%" class="tbl" >
        <tr>
            <td width="40%" valign="top" align="">
                <table width="100%">
                    <tbody valign="top" id="tcomponents">
                        <tr>
                            <th width="5">
                                <?php echo w2PtoolTip('add tasks', 'click here to add a new task to this project'); ?><a href="javascript: void(0);" onclick="addComponent()">
                                <img src="<?php echo w2PfindImage('add.png', $m); ?>"  />
                                </a><?php echo w2PendTip(); ?>
                            </th>
                            <th>
                                <b><?php echo $AppUI->_('Task Name'); ?></b>
                            </th>
                            <th>
                                <b><?php echo $AppUI->_('Start'); ?></b>
                            </th>
                            <th>
                                <b><?php echo $AppUI->_('End'); ?></b>
                            </th>
                            <th>
                                <b><?php echo $AppUI->_('Duration'); ?></b>
                            </th>
                            <th>
                                <b><?php echo $AppUI->_('Priority'); ?></b>
                            </th>
                            <th>
                                <b><?php echo $AppUI->_('Type'); ?></b>
                            </th>
                            <th>
                                <b><?php echo $AppUI->_('Access'); ?></b>
                            </th>
                            <th>
                                <b><?php echo $AppUI->_('Extra'); ?></b>
                            </th>
                        </tr>
                    </tbody>
                </table>
                <table width="100%">
                    <tr>
                        <td align="left" width="20">

                        </td>
                    </tr>
                    <tr>
                        <td colspan="5" valign="bottom" align="right">
                            <input type="button" class="button btn btn-primary btn-small" value="<?php echo $AppUI->_('add'); ?>" onclick="addTasks()" />
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</form>
