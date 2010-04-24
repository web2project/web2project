<?php /* $Id$ $URL$ */
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}

// Copyright 2004 Adam Donnison <adam@saki.com.au>
$resource_id = intval(w2PgetParam($_GET, 'resource_id', null));
$perms = &$AppUI->acl();
$canDelete = $perms->checkModuleItem('resources', 'delete', $resource_id);
if ((!$resource_id && !canAdd('resources')) || !$canEdit) {
	$AppUI->redirect('m=public&a=access_denied');
}

$obj = new CResource();
if ($resource_id && !$obj->load($resource_id)) {
	$AppUI->setMsg('Resource');
	$AppUI->setMsg('invalidID', UI_MSG_ERROR, true);
	$AppUI->redirect();
}

$titleBlock = new CTitleBlock(($resource_id ? 'Edit Resource' : 'Add Resource'), 'resources.png', $m, $m . '.' . $a);
$titleBlock->addCrumb('?m=resources', 'resource list');
if ($resource_id) {
	$titleBlock->addCrumb('?m=resources&a=view&resource_id=' . $resource_id, 'view this resource');
}
$titleBlock->show();

$typelist = $obj->typeSelect();
?>
<form name="editfrm" action="?m=resources" method="post" accept-charset="utf-8">
<input type="hidden" name="dosql" value="do_resource_aed" />
<input type="hidden" name="resource_id" value="<?php echo w2PformSafe($resource_id); ?>" />
<table cellspacing="1" cellpadding="1" border="0" width="100%" class="std">
<tr>
<td align="center" >
	<table>
	<tr>
		<td align="right"><?php echo $AppUI->_('Resource ID'); ?></td>
		<td align="left"><input type="text" class="text" size="15" maxlength="64" name="resource_key" value="<?php echo w2PformSafe($obj->resource_key); ?>" /></td>
	</tr>
	<tr>
		<td align="right"><?php echo $AppUI->_('Resource Name'); ?></td>
		<td align="left"><input type="text" class="text" size="30" maxlength="255" name="resource_name" value="<?php echo w2PformSafe($obj->resource_name); ?>" /></td>
	</tr>
	<tr>
		<td align="right"><?php echo $AppUI->_('Type'); ?></td>
		<td align="left"><?php echo arraySelect($typelist, 'resource_type', 'class="text"', $obj->resource_type, true); ?></td>
	</tr>
	<tr>
		<td align="right"><?php echo $AppUI->_('Maximum Allocation Percentage'); ?></td>
		<td><input type="text" class="text" style="text-align:right;" size="5" maxlength="5" value="<?php
if ($obj->resource_max_allocation) {
	echo w2PformSafe($obj->resource_max_allocation);
} else {
	echo '100';
} 
	?>"
		name="resource_max_allocation" /></td>
	</tr>
	<tr>
		<td align="right"><?php echo $AppUI->_('Notes'); ?></td>
		<td><textarea name="resource_note" cols="60" rows="7"><?php echo w2PformSafe($obj->resource_note); ?></textarea></td>
	</tr>	
	</table>
</td>
</tr>
<tr>
	<td>
    	<input type="button" value="<?php echo $AppUI->_('back'); ?>" class="button" onclick="javascript:history.back(-1);" />
	</td>
  	<td align="right">
    	<input type="button" value="<?php echo $AppUI->_('submit'); ?>" class="button" onclick="submitIt(document.editfrm);" />
	</td>
</tr>
</table>
</form>