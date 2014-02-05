<?php
if (!defined('DP_BASE_DIR')) {
	die('You should not access this file directly.');
}

global $users, $event_id, $obj, $currentTabId,$is_clash;

// Need to get all of the resources that this user is allowed to view
$resource = new CResource();
$resources = $resource->loadAll();

$resource_types = w2PgetSysVal('ResourceTypes');

$all_resources = array();
foreach($resources as $row) {
	$all_resources[$row['resource_id']] = $this->_AppUI->_($resource_types[$row['resource_type']]) . ': ' . $row['resource_name'];
}
$assigned_resources = array();

$resources = array();
if ($is_clash) {
	$resources_list = $_SESSION['add_event_resources'];
	if (!empty($resources_list)) {
		$initResAssignment = $resources_list;
		foreach (explode(',',$resources_list) as $resource_id) {
			$resources[$resource_id] = $all_resources[$resource_id];
		}
		$q->clear();
	}
} else if ($event_id == 0) {
} else {
	$initResAssignment = '';
	// Pull resources on this task
	$q = new w2p_Database_Query;
	$q->addTable('event_resources');
	$q->addQuery('resource_id');
	$q->addWhere('event_id = ' . $event_id);
	$sql = $q->prepareSelect();
	$assigned_res = $q->exec();
	while ($row = db_fetch_assoc($assigned_res)) {
		$initResAssignment .= $row['resource_id'].',';
		$resources[$row['resource_id']] = $all_resources[$row['resource_id']];
	}
	$q->clear();
}

$this->_AppUI->getModuleJS('resources', 'event_tabs');
?>
<form action="?m=calendar&amp;a=addedit&amp;event_id=<?php echo $event_id; ?>"
	  method="post" name="otherFrm">
	<input type="hidden" name="sub_form" value="1" />
	<input type="hidden" name="event_id" value="<?php echo $event_id; ?>" />
	<input type="hidden" name="dosql" value="do_event_other_resources_aed" />
	<input name="hresource_assign" type="hidden" value="<?php echo
	$initResAssignment;?>"/>
	<table width="100%" border="1" cellpadding="4" cellspacing="0" class="std">
		<tr>
			<td valign="top" align="center">
				<table cellspacing="0" cellpadding="2" border="0">
					<tr>
						<td><?php echo $this->_AppUI->_('Resources');?>:</td>
						<td><?php echo $this->_AppUI->_('Assigned to event');?>:</td>
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
									<td align="right"><input type="button" class="button" value="&gt;" onclick="javascript:addResource(document.otherFrm)" /></td>
									<td align="left"><input type="button" class="button" value="&lt;" onclick="javascript:removeResource(document.otherFrm)" /></td>
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
	subForm.push(new FormDefinition(<?php echo $currentTabId; ?>, document.otherFrm, checkOther, saveOther));
</script>
