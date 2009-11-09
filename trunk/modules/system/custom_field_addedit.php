<?php /* $Id$ $URL$ */
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}

/*
*	Custom Field Add/Edit
*
*/

// check permissions
$perms = &$AppUI->acl();
if (!$perms->checkModule('system', 'edit')) {
	$AppUI->redirect('m=public&a=access_denied');
}

$titleBlock = new CTitleBlock('Custom Fields - Add/Edit', 'customfields.png', 'admin', 'admin.custom_field_addedit');
$titleBlock->addCrumb('?m=system', 'system admin');
$titleBlock->addCrumb('?m=system&a=custom_field_editor', 'custom fields');
$titleBlock->show();

$field_id = w2PgetParam($_POST, 'field_id', null) != null ? w2PgetParam($_POST, 'field_id', null) : w2PgetParam($_GET, 'field_id', 0);
$delete_field = w2PgetParam($_GET, 'delete', 0);
$module = w2PgetParam($_GET, 'module', null) == null ? w2PgetParam($_POST, 'module', null) : w2PgetParam($_GET, 'module', null);

$select_newitem = w2PgetParam($_POST, 'select_newitem', null);
$select_items = w2PgetParam($_POST, 'select_items', array());

$select_delitem = w2PgetParam($_POST, 'delete_item', null);

$yesno = w2PgetSysVal('GlobalYesNo');
if ($select_newitem != null) {
	$select_items[] = $select_newitem;
}

if ($select_delitem != null) {
	$new_selectitems = array();

	foreach ($select_items as $itm) {
		if ($itm != $select_delitem)
			$new_selectitems[] = $itm;
	}

	unset($select_items);
	$select_items = &$new_selectitems;
}

// Loading the page for the first time
if (w2PgetParam($_GET, 'field_id', null) != null) {
	$custom_fields = new CustomFields($module, 'addedit', null, 'edit');

	if ($delete_field) {
		$custom_fields->deleteField($field_id);
		$AppUI->redirect();
	}

	$cf = &$custom_fields->fieldWithId($field_id);

	if (is_object($cf)) {
		$field_name = $cf->fieldName();
		$field_description = $cf->fieldDescription();
		$field_htmltype = $cf->fieldHtmlType();
		$field_extratags = $cf->fieldExtraTags();
		$field_order = $cf->fieldOrder();
		$field_published = $cf->fieldPublished();

		if ($field_htmltype == 'select') {
			$select_options = new CustomOptionList($field_id);
			$select_options->load();
			$select_items = $select_options->getOptions();
		}
	} else {
		//No such field exists with this ID
		$AppUI->setMsg('Couldnt load the Custom Field, It might have been deleted somehow.');
		$AppUI->redirect();
	}

	$edit_title = $AppUI->_('Edit Custom Field In');
} else {
	$edit_title = $AppUI->_('New Custom Field In');

	$field_name = w2PgetParam($_POST, 'field_name', null);
	$field_description = w2PgetParam($_POST, 'field_description', null);
	$field_htmltype = w2PgetParam($_POST, 'field_htmltype', 'textinput');
	$field_extratags = w2PgetParam($_POST, 'field_extratags', null);
	$field_order = w2PgetParam($_POST, 'field_order', null);
	$field_published = w2PGetParam($_POST, 'field_published', 0);
}

$html_types = array('textinput' => $AppUI->_('Text Input'), 'textarea' => $AppUI->_('Text Area'), 'checkbox' => $AppUI->_('Checkbox'), 'select' => $AppUI->_('Select List'), 'label' => $AppUI->_('Label'), 'separator' => $AppUI->_('Separator'), 'href' => $AppUI->_('Weblink'), );

$visible_state = array();

foreach ($html_types as $k => $ht) {
	if ($k == $field_htmltype) {
		$visible_state['div_' . $k] = 'display : block';
	} else {
		$visible_state['div_' . $k] = 'display : none';
	}
}
?>
<script>
	function hideAll() {
		var selobj = document.getElementById('htmltype');
		for (i = 0, i_cmp = selobj.options.length; i < i_cmp; i++) {
			var atbl = document.getElementById('atbl_'+selobj.options[i].value);
			var adiv = document.getElementById('div_'+selobj.options[i].value);

			atbl.style.visibility = 'hidden';
			adiv.style.display = 'none';
		} 
	}

	function showAttribs() {
		hideAll();

		var selobj = document.getElementById('htmltype');
		var seltype = selobj.options[selobj.selectedIndex].value;
		
		var atbl = document.getElementById('atbl_'+seltype);
		var adiv = document.getElementById('div_'+seltype);
		atbl.style.visibility = 'visible';
		adiv.style.display = 'block';
	}

	function addSelectItem() {
		frm = document.getElementById('custform');
		frm.action = '?m=system&a=custom_field_addedit';
		frm.submit();
	}

	function deleteItem( itmname ) {
		del = document.getElementById('delete_item');
		del.value = itmname;
		addSelectItem();
	}

	function postCustomField() {
		frm = document.getElementById('custform');
		frm.action = '?m=system&a=custom_field_editor';
		sql = document.getElementById('dosql');
		sql.name = 'dosql';	
		frm.submit();
	}
</script>
<form method="POST" action="?m=system&a=custom_field_editor" id="custform" accept-charset="utf-8">
<table class="std" width="100%">
	<th colspan="2">
		<?php echo $edit_title ?> <?php echo $AppUI->_($module) ?> <?php echo $AppUI->_('Module') ?>
		<input type="hidden" name="field_id" value="<?php echo $field_id; ?>" />
		<input type="hidden" name="module" value="<?php echo $module ?>" /> 
		<input type="hidden" name="dontdosql" id="dosql" value="do_custom_field_aed" />
	</td></tr>
	<tr><td>
		<?php echo $AppUI->_('Field Name/Identifier') ?>:
		&nbsp;
		<?php echo $AppUI->_('(No Spaces)') ?>
		</td><td>
		<input type="text" class="text" name="field_name" maxlength="100" value="<?php echo $field_name ?>" onblur='this.value=this.value.replace(/[^a-z|^A-Z|^0-9]*/gi,"");' />
	</td></tr>
	<tr><td>
		<?php echo $AppUI->_('Field Description') ?>:
		</td><td>
		<input type="text" class="text" name="field_description" size="40" maxlength="250" value="<?php echo $field_description ?>" />
	</td></tr>
	<tr><td>
		<?php echo $AppUI->_('Field Display Type') ?>:
		</td><td>
		<?php echo arraySelect($html_types, 'field_htmltype', 'id="htmltype" class="text" onChange="javascript:showAttribs()"', $field_htmltype); ?>
	</td></tr>
	<?php if (count($yesno)) { ?>
	<tr><td>
		<?php echo $AppUI->_('Field Published') ?>:
		</td><td>
		<?php echo arraySelect($yesno, 'field_published', 'id="fieldpublished" class="text"', $field_published); ?>
	</td></tr>
	<?php } ?>
	<tr><td>
		<?php echo $AppUI->_('Field Display Order') ?>:
		</td><td>
		<input style="text-align:right" type="text" class="text" size="4" name="field_order" maxlength="3" value="<?php echo $field_order ?>" />
	</td></tr>
	<tr><td colspan="2">
		<hr />
		<tr><td>
			<?php echo $AppUI->_('HTML Tag Options') ?>:
		</td>
		<td>
			<input type="text" class="text" size="80" name="field_extratags" value="<?php echo $field_extratags ?>" />
		</td></tr>
	</td></tr>
	<tr><td colspan="2">
	<div id="div_select" style="<?php echo $visible_state['div_select'] ?>">
		<table id="atbl_select">
		<tr><td colspan="2">
			<b><?php echo $AppUI->_('List of Options') ?>:</b> 
		</td></tr>
		<tr><td colspan="2">
			<input type="hidden" name="delete_item" value="0" id="delete_item" />
			<table>
			<?php
$s = '';
foreach ($select_items as $itm) {
	$s .= '<tr><td>';
	$s .= '<li>' . $itm . '</li>';
	$s .= '<input type="hidden" name="select_items[]" value="' . $itm . '" />';
	$s .= '</td><td>';
	$s .= '<a href="javascript:deleteItem(\'' . $itm . '\')">[Delete]</a>';
	$s .= '</td></tr>';
}
echo $s;
?>
			<tr><td>
			<li><input type="text" name="select_newitem" /></td><td><input type="button" value="<?php echo $AppUI->_('Add') ?>" onclick="javascript:addSelectItem()" /></li>
			</td></tr>
			</table>
		</td></tr>
		</table>
		<hr />
	</div>
	<div id="div_textinput" style="<?php echo $visible_state['div_textinput'] ?>">
		<table id="atbl_textinput">
		</table>
	</div>
	<div id="div_textarea" style="<?php echo $visible_state['div_textarea'] ?>">
		<table id="atbl_textarea">
		</table>
	</div>
	<div id="div_checkbox" style="<?php echo $visible_state['div_checkbox'] ?>">
		<table id="atbl_checkbox">
		</table>
	</div>
	<div id="div_label" style="<?php echo $visible_state['div_label'] ?>">
		<table id="atbl_label">
		</table>
	</div>
	<div id="div_separator" style="<?php echo $visible_state['div_separator'] ?>">
		<table id="atbl_separator">
		</table>
	</div>
	</td></tr>
	<tr><td colspan="2" align="right">
		<input type="button" value="Cancel" onclick="location = '?m=system&a=custom_field_editor';" />
		<input type="button" value="Save" onclick="javascript:postCustomField()" />
	</td></tr>
	</form>
</table>