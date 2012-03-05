<?php /* $Id$ $URL$ */
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}

global $AppUI, $users, $task_id, $task_project, $obj;
global $projTasksWithEndDates, $tab, $loadFromTab;

// Need to get all of the resources that this user is allowed to view
$resource = new CResource();

$resource_types = &$resource->typeSelect();
$q = new w2p_Database_Query();

$q->addTable('resources');
$q->addOrder('resource_type', 'resource_name');
$res = $q->exec(ADODB_FETCH_ASSOC);
$all_resources = array();
$resource_max = array();

while ($row = $q->fetchRow()) {
	$type = $row['resource_type'];
	$all_resources[$row['resource_id']] = $resource_types[$row['resource_type']] . ': ' . $row['resource_name'];
	$resource_max[$row['resource_id']] = $row['resource_max_allocation'];
}
$q->clear();

$assigned_resources = array();

$initResAssignment = '';

$resources = array();
if ($loadFromTab && isset($_SESSION['tasks_subform']['hresource_assign'])) {
	$initResAssignment = '';
	foreach (explode(';', $_SESSION['tasks_subform']['hresource_assign']) as $perc) {
		if ($perc) {
			list($rid, $perc) = explode('=', $perc);
			$assigned_resources[$rid] = $perc;
			$initResAssignment .= $rid . '=' . $perc . ';';
			$resources[$rid] = $all_resources[$rid] . ' [' . $perc . '%]';
		}
	}
} elseif ($task_id == 0) {
} else {
	$initResAssignment = '';
	// Pull resources on this task
	$q = new w2p_Database_Query();
	$q->addTable('resource_tasks');
	$q->addQuery('resource_id, percent_allocated');
	$q->addWhere('task_id = ' . (int)$task_id);
	$assigned_res = $q->exec();
	while ($row = $q->fetchRow()) {
		$initResAssignment .= $row['resource_id'] . '=' . $row['percent_allocated'] . ';';
		$resources[$row['resource_id']] = $all_resources[$row['resource_id']] . ' [' . $row['percent_allocated'] . '%]';
	}
	$q->clear();
}

	$AppUI->getModuleJS('resources', 'tabs');
?>
<script language="javascript" type="text/javascript">
<?php
echo "var projTasksWithEndDates=new Array();\n";
$keys = array_keys($projTasksWithEndDates);
for ($i = 1, $i_cmp = sizeof($keys); $i < $i_cmp; $i++) {
	//array[task_is] = end_date, end_hour, end_minutes
	echo 'projTasksWithEndDates[' . $keys[$i] . "]=new Array(\"" . $projTasksWithEndDates[$keys[$i]][1] . "\", \"" . $projTasksWithEndDates[$keys[$i]][2] . "\", \"" . $projTasksWithEndDates[$keys[$i]][3] . "\");\n";
}
?>
</script>
<form action="?m=tasks&a=addedit&task_project=<?php echo $task_project; ?>" method="post" name="otherFrm" accept-charset="utf-8">
<input type="hidden" name="sub_form" value="1" />
<input type="hidden" name="task_id" value="<?php echo $task_id; ?>" />
<input type="hidden" name="dosql" value="do_task_aed" />
	<input name="hresource_assign" type="hidden" value="<?php echo $initResAssignment; ?>"/>
<table width="100%" border="1" cellpadding="4" cellspacing="0" class="std addedit">
<tr>
	<td valign="top" align="center">
		<table cellspacing="0" cellpadding="2" border="0">
			<tr>
				<td><?php echo $AppUI->_('Resources'); ?>:</td>
				<td><?php echo $AppUI->_('Assigned to Task'); ?>:</td>
			</tr>
			<tr>
				<td>
					<?php echo arraySelect($all_resources, 'resources', 'style="width:220px" size="10" class="text" multiple="multiple" ', null); ?>
				</td>
				<td>
					<?php echo arraySelect($resources, 'assigned', 'style="width:220px" size="10" class="text" multiple="multiple" ', null); ?>
				</td>
			<tr>
				<td colspan="2" align="center">
					<table>
					<tr>
						<td align="right"><input type="button" class="button" value="&gt;" onclick="addResource(document.otherFrm)" /></td>
						<td>
							<select name="resource_assignment" class="text">
							<?php
for ($i = 5; $i <= 100; $i += 5) {
	echo "<option " . (($i == 100) ? "selected=\"true\"" : "") . " value=\"" . $i . "\">" . $i . "%</option>";
}
?>
							</select>
						</td>				
						<td align="left"><input type="button" class="button" value="&lt;" onclick="removeResource(document.otherFrm)" /></td>					
					</tr>
					</table>
				</td>
			</tr>
			</tr>
		</table>
	</td>
</tr>
</table>
</form>
<script language="javascript" type="text/javascript">
  subForm.push(new FormDefinition(<?php echo $tab; ?>, document.otherFrm, checkOther, saveOther));
</script>