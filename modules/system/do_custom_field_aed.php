<?php /* $Id$ $URL$ */
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}

$perms = &$AppUI->acl();
if (!canEdit('system')) {
	$AppUI->redirect('m=public&a=access_denied');
}

/*
*	do_custom_field_aed.php
*
*/
$edit_field_id = w2PgetParam($_POST, 'field_id', null);

if ($edit_field_id != null) {
	$edit_module = w2PgetParam($_POST, 'module', null);
	$field_name = w2PgetParam($_POST, 'field_name', null);
	$field_description = db_escape(strip_tags(w2PgetParam($_POST, 'field_description', null)));
	$field_htmltype = w2PgetParam($_POST, 'field_htmltype', null);
	$field_datatype = w2PgetParam($_POST, 'field_datatype', 'alpha');
	$field_published = w2PGetParam($_POST, 'field_published', 0);
	$field_order = w2PGetParam($_POST, 'field_order', 0);
	$field_extratags = db_escape(w2PgetParam($_POST, 'field_extratags', null));

	$list_select_items = w2PgetParam($_POST, 'select_items', null);

	$custom_fields = new w2p_Core_CustomFields(strtolower($edit_module), 'addedit', null, null);

	if ($edit_field_id == 0) {
		$fid = $custom_fields->add($field_name, $field_description, $field_htmltype, $field_datatype, $field_extratags, $field_order, $field_published, $msg);
	} else {
		$fid = $custom_fields->update($edit_field_id, $field_name, $field_description, $field_htmltype, $field_datatype, $field_extratags, $field_order, $field_published, $msg);
	}

	// Add or Update a Custom Field
	if ($msg) {
		$AppUI->setMsg($AppUI->_('Error adding custom field:') . $msg, UI_MSG_ALERT, true);
	} else {
		if ($field_htmltype == 'select') {
			$opts = new CustomOptionList($fid);
			$opts->setOptions($list_select_items);

			if ($edit_field_id == 0) {
				$o_msg = $opts->store();
			} else {
				// To update each list would be a lot more complex than rewriting it
				// So it is, but it is needed in order for it to work properly. (Pedro A. Bug 1163)
				$o_msg = $opts->store();
			}

			if ($o_msg) {
				// Select List Failed - Delete w2p_Core_CustomFields also
			}

		}
		$AppUI->setMsg($AppUI->_('Custom field added successfully'), UI_MSG_OK, true);
	}
}