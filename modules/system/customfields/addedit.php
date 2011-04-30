<?php /* $Id$ $URL$ */
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}

$field_id = (int) w2PgetParam($_GET, 'field_id', 0);
$module_id = (int) w2PgetParam($_GET, 'module', 0);

// check permissions
$perms = $AppUI->acl();
if (!canEdit('system')) {
	$AppUI->redirect('m=public&a=access_denied');
}

// load the record data
$field = new w2p_Core_CustomFieldManager();
$obj = $AppUI->restoreObject();
if ($obj) {
    $field = $obj;
    $field_id = $field->field_id;
} else {
    $field->load($AppUI, $field_id);
}
$module = new w2p_Core_Module();
$module->load($module_id);

$ttl = $field_id ? 'Edit Custom Fields' : 'Add Custom Fields';
$ttl = $AppUI->_($ttl).' - '.$AppUI->_($module->mod_name).' '.$AppUI->_('Module');
$titleBlock = new CTitleBlock($ttl, 'customfields.png', $m, "$m.$a");
$titleBlock->addCrumb('?m=system', 'system admin');
$titleBlock->addCrumb('?m=system&u=customfields', 'custom fields');
$titleBlock->show();

$custom_fields = new w2p_Core_CustomFields($module->mod_name, 'addedit', null, 'edit');

if ($field_id) {
    $cf = $custom_fields->fieldWithId($field_id);

	if (is_object($cf)) {
		$field_name = $cf->fieldName();
		$field_description = $cf->fieldDescription();
		$field_htmltype = $cf->fieldHtmlType();
		$field_extratags = $cf->fieldExtraTags();
		$field_order = $cf->fieldOrder();
		$field_published = $cf->fieldPublished();

		if ($field_htmltype == 'select') {
			$select_options = new w2p_Core_CustomOptionList($field_id);
			$select_options->load();
			$select_items = $select_options->getOptions();
		}
	} else {
		//No such field exists with this ID
		$AppUI->setMsg('Couldnt load the Custom Field, It might have been deleted somehow.');
		$AppUI->redirect();
	}
}

$visible_state = array();
$html_types = $field->getTypes();
foreach ($html_types as $k => $ht) {
	if ($k == $field_htmltype) {
		$visible_state['div_' . $k] = 'display : block';
	} else {
		$visible_state['div_' . $k] = 'display : none';
	}
}
?>
<script language="javascript" type="text/javascript">
function submitIt() {
	var f = document.addEditForm;
	f.submit();
}
function filterFieldName(field) {
    field.value=field.value.replace(/[^a-z|^A-Z|^0-9]*/gi,"");
}
</script>
<form method="post" action="?m=system&u=customfields" id="addEditForm" name="addEditForm" accept-charset="utf-8">
    <input type="hidden" name="field_id" value="<?php echo $field_id; ?>" />
    <input type="hidden" name="module" value="<?php echo $module_id ?>" />
    <input type="hidden" name="dosql" id="dosql" value="do_customfield_aed" />
    <table width="100%" border="0" cellpadding="3" cellspacing="3" class="std">
        <tr>
            <th colspan="2"><?php echo $ttl; ?></th>
        </tr>
        <tr>
            <td><label><?php echo $AppUI->_('Field Name/Identifier') ?>:&nbsp;<?php echo $AppUI->_('(No Spaces)') ?></label></td>
            <td>
                <input type="text" class="text" name="field_name" maxlength="100" value="<?php echo $field_name ?>" onblur="javascript:filterFieldName(this)" />
            </td>
        </tr>
        <tr>
            <td><label><?php echo $AppUI->_('Field Description') ?>:</label></td>
            <td>
            <input type="text" class="text" name="field_description" size="40" maxlength="250" value="<?php echo $field_description ?>" />
            </td>
        </tr>
        <tr>
            <td><label><?php echo $AppUI->_('Field Display Type') ?>:</label></td>
            <td>
            <?php echo arraySelect($html_types, 'field_htmltype', 'id="htmltype" class="text" onChange="javascript:showAttribs()"', $field_htmltype); ?>
            </td>
        </tr>
        <tr>
            <td><label><?php echo $AppUI->_('Field Published') ?>:</label></td>
            <td>
            <?php echo arraySelect(w2PgetSysVal('GlobalYesNo'), 'field_published', 'id="fieldpublished" class="text"', $field_published); ?>
            </td>
        </tr>
        <tr>
            <td><label><?php echo $AppUI->_('Field Display Order') ?>:</label></td>
            <td>
            <input type="text" class="text" name="field_order" size="4" maxlength="3" value="<?php echo (int) $field_order ?>" />
            </td>
        </tr>
        <tr>
            <td><label><?php echo $AppUI->_('HTML Tag Options') ?>:</label></td>
            <td>
                <input type="text" class="text" name="field_extratags" size="80" value="<?php echo $field_extratags ?>" />
            </td>
        </tr>




        <tr>
            <td>
                <input class="button" type="button" name="cancel" value="<?php echo $AppUI->_('cancel'); ?>" onclick="javascript:if(confirm('<?php echo $AppUI->_('Are you sure you want to cancel?', UI_OUTPUT_JS); ?>')){location.href = './index.php?m=system&u=customfields';}" />
            </td>
            <td align="right">
                <input type="button" class="button" value="<?php echo $AppUI->_('submit'); ?>" onclick="submitIt()" />
            </td>
        </tr>
    </table>
</form>