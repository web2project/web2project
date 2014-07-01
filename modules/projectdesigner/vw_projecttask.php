<?php
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}

global $project_id;

$project = $obj;
include $AppUI->getTheme()->resolveTemplate('projects/view');

$module = new w2p_System_Module();
$fields = $module->loadSettings('projectdesigner', 'task_list_print');

if (0 == count($fields)) {
    // TODO: This is only in place to provide an pre-upgrade-safe 
    //   state for versions earlier than v3.0
    //   At some point at/after v4.0, this should be deprecated
    $fieldList = array('task_name', 'task_percent_complete', 'task_owner', 'task_start_date', 'task_duration', 'task_end_date');
    $fieldNames = array('Task Name', 'Work', 'Owner', 'Start', 'Duration', 'Finish');

    $module->storeSettings('projectdesigner', 'task_list_print', $fieldList, $fieldNames);
    $fields = array_combine($fieldList, $fieldNames);
}

$taskobj = new CTask();
$taskTree = $taskobj->getTaskTree($project_id);

$listTable = new w2p_Output_HTML_TaskTable($AppUI);

echo $listTable->startTable();
echo $listTable->buildHeader($fields);
echo $listTable->buildRows($taskTree);
echo $listTable->endTable();

?>
<table class="tbl" cellspacing="1" cellpadding="2" border="0" width="100%">
    <tr>
        <td align="center">
            <?php echo '<strong>Gantt Chart</strong>' ?>
        </td>
    </tr>
    <tr>
        <td align="center" colspan="20">
            <?php
            $src = "?m=tasks&a=gantt&suppressHeaders=1&showLabels=1&proFilter=&showInactive=1showAllGantt=1&project_id=$project_id&width=' + ((navigator.appName=='Netscape'?window.innerWidth:document.body.offsetWidth)*0.90) + '";
            echo "<script language=\"javascript\" type=\"text/javascript\">document.write('<img src=\"$src\">')</script>";
            ?>
        </td>
    </tr>
</table>