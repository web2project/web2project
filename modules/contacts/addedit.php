<?php
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}
// @todo    convert to template
$object_id = (int) w2PgetParam($_GET, 'contact_id', 0);
$company_id = (int) w2PgetParam($_GET, 'company_id', 0);
$dept_id = (int) w2PgetParam($_GET, 'dept_id', 0);

$object = new CContact();
$object->setId($object_id);

$canAddEdit = $object->canAddEdit();
$canAuthor = $object->canCreate();
$canEdit = $object->canEdit();
$canDelete = $object->canDelete();
if (!$canAddEdit) {
	$AppUI->redirect(ACCESS_DENIED);
}

// load the record data
$obj = $AppUI->restoreObject();
if ($obj) {
    $object = $obj;
    $object_id = $object->getId();
} else {
    $object->load($object_id);
}
if (!$object && $object_id > 0) {
    $AppUI->setMsg('Contact');
    $AppUI->setMsg('invalidID', UI_MSG_ERROR, true);
    $AppUI->redirect('m=' . $m);
}

$company = new CCompany();
$company->load($company_id);
$company_name = $company->company_name;

// get a list of permitted companies
$companies = $company->getAllowedRecords($AppUI->user_id, 'company_id,company_name', 'company_name');
$companies = arrayMerge(array('0' => ''), $companies);

$dept = new CDepartment();
$dept->load($dept_id);
$dept_name = $dept->dept_name;

$is_user = $object->isUser($object_id);

$df = $AppUI->getPref('SHDATEFORMAT');
$df .= ' ' . $AppUI->getPref('TIMEFORMAT');


// setup the title block
$ttl = $object_id > 0 ? 'Edit Contact' : 'Add Contact';
$titleBlock = new w2p_Theme_TitleBlock($ttl, 'icon.png', $m);
$titleBlock->addCrumb('?m=' . $m, $m . ' list');
$titleBlock->addViewLink('contact', $object_id);

$titleBlock->show();
$company_detail = $object->getCompanyDetails();
$dept_detail = $object->getDepartmentDetails();
if ($object_id == 0 && $company_id > 0) {
	$company_detail['company_id'] = $company_id;
	$company_detail['company_name'] = $company_name;
	$dept_detail['dept_id'] = $dept_id;
	$dept_detail['dept_name'] = $dept_name;
}

$methods = $object->getContactMethods();
$methodLabels = w2PgetSysVal('ContactMethods');
$countries = array('' => $AppUI->_('(Select a Country)')) + w2PgetSysVal('GlobalCountriesPreferred') +
		array('-' => '----') + w2PgetSysVal('GlobalCountries');

?>

<script language="javascript" type="text/javascript">
<?php
echo 'window.company_id=' . ((int) $company_detail['company_id']) . ";\n";
?>

function submitIt() {
	var form = document.changecontact;
    if (form.contact_birthday.value == '0000-00-00') {
        form.contact_birthday.value = '';
    }
    if (form.contact_last_name.value.length < 1) {
        alert( '<?php echo $AppUI->_('contactsValidName', UI_OUTPUT_JS); ?>' );
        form.contact_last_name.focus();
    } else if (form.contact_first_name.value.length < 1) {
        alert( '<?php echo $AppUI->_('contactsValidName', UI_OUTPUT_JS); ?>' );
        form.contact_first_name.focus();
    } else if (form.contact_birthday.value.length > 1) {
        dar = form.contact_birthday.value.split("-");
        if (dar.length < 3) {
            alert("<?php echo $AppUI->_('adminInvalidBirthday', UI_OUTPUT_JS); ?>");
            form.contact_birthday.focus();
        } else if (isNaN(parseInt(dar[0],10)) || isNaN(parseInt(dar[1],10)) || isNaN(parseInt(dar[2],10))) {
            alert("<?php echo $AppUI->_('adminInvalidBirthday', UI_OUTPUT_JS); ?>");
            form.contact_birthday.focus();
        } else if (parseInt(dar[1],10) < 1 || parseInt(dar[1],10) > 12) {
            alert("<?php echo $AppUI->_('adminInvalidMonth', UI_OUTPUT_JS) . ' ' . $AppUI->_('adminInvalidBirthday', UI_OUTPUT_JS); ?>");
            form.contact_birthday.focus();
        } else if (parseInt(dar[2],10) < 1 || parseInt(dar[2],10) > 31) {
            alert("<?php echo $AppUI->_('adminInvalidDay', UI_OUTPUT_JS) . ' ' . $AppUI->_('adminInvalidBirthday', UI_OUTPUT_JS); ?>");
            form.contact_birthday.focus();
        } else if(parseInt(dar[0],10) < 1900 || parseInt(dar[0],10) > <?php echo date('Y'); ?>) {
            alert("<?php echo $AppUI->_('adminInvalidYear', UI_OUTPUT_JS) . ' ' . $AppUI->_('adminInvalidBirthday', UI_OUTPUT_JS); ?>");
            form.contact_birthday.focus();
        } else {
            form.submit();
        }
	} else if (form.contact_display_name.value.length < 1) {
		orderByName('name');
		form.submit();
	} else {
		form.submit();
	}
}

function popDepartment() {
	var f = document.changecontact;
	window.open('./index.php?m=contacts&a=select_contact_company&dialog=1&table_name=departments&company_id='+f.contact_company.value+'&dept_id='+f.contact_department.value, 'company', 'left=50,top=50,height=320,width=400,resizable');
}

function setDepartment( key ){
	var f = document.changecontact;

    f.contact_department.value = key;
    xajax_getDepartment(f.contact_department.value, 'contact_department_name');
}

<?php if ($canDelete) { ?>
function delIt(){
	var form = document.changecontact;
	if(confirm('<?php echo $AppUI->_('contactsDelete', UI_OUTPUT_JS); ?>')) {
		form.del.value = '<?php echo $object_id; ?>';
		form.submit();
	}
}
<?php } ?>

function orderByName( x ){
	var form = document.changecontact;
	if (x == 'name') {
		form.contact_display_name.value = form.contact_first_name.value + ' ' + form.contact_last_name.value;
	} else {
		form.contact_display_name.value = form.contact_company_name.value;
	}
}

function companyChange() {
	var f = document.changecontact;

	if ( f.contact_company.value != window.company_id ){
        f.contact_department_name.value = '';
		f.contact_department.value = '0';
	}
}

function updateVerify() {
	var form = document.changecontact;
	if (form.contact_email.value.length < 1 && form.contact_updateask.checked) {
		alert('<?php echo $AppUI->_('You must enter a valid email before using this feature.', UI_OUTPUT_JS); ?>');
		form.contact_updateask.checked = false;
		form.contact_email.focus();
	}
}

function addContactMethod(field, value) {
    var selects, index, select, tr, td;

    /* Determine how many contact method rows exist */
    index = 0;
    selects = document.getElementsByTagName("select");
    for (i = 0; i < selects.length; i++) {
        select = selects[i];
        if (select.getAttribute("name").indexOf("contact_methods") == 0) {
            index++;
        }
    }

    /* Create select menu for contact method type */
    function addOption(select, value, text, selected) {
        var option = document.createElement('option');
        option.setAttribute("value", value);
        option.innerHTML = text;
        option.selected = (value == selected);
        $(select).append(option);
    }

    /* Create a new table row */
    $('<p id="contact_methods_' + index + '_" />').insertBefore('#custom_fields');

    /* Add contact method type menu to the table row */
    $('#contact_methods_' + index + '_').append('<label><select id="method_select_' + index + '" name="contact_methods[field][' + index + ']" size="1" class="text" /></label>');
    /* Add text field for the contact method value to the table row */
    $('#contact_methods_' + index + '_').append('<input type="text" name="contact_methods[value][' + index + ']" size="25" maxlength="255" class="text" value="' + (value ? value : "") + '" /><?php echo w2PtoolTip('Contact Method', 'Remove') ?><a id="remove_contact_method" href="javascript:removeContactMethod(\'' + index + '\')"><img src="<?php echo w2PfindImage('icons/remove.png'); ?>" alt="" /></a><?php echo w2PendTip() ?>');
    addOption('#method_select_' + index, "", "");
    <?php foreach ($methodLabels as $value => $text): ?>
    addOption('#method_select_' + index, "<?php echo $value; ?>", "<?php echo $text; ?>", field);
    <?php endforeach; ?>
    /* Make sure the newly added remove span has its tooltip working*/
    $("span").tipTip({maxWidth: "auto", delay: 200, fadeIn: 150, fadeOut: 150});
}

function removeContactMethod(index) {
    tr = document.getElementById("contact_methods_" + index + "_");
    tr.parentNode.removeChild(tr);
}

$(document).ready(function() {
<?php
$fields = $methods['fields'];
foreach ($fields as $key => $field): ?>
    addContactMethod("<?php echo $field; ?>", "<?php echo $methods['values'][$key]; ?>");
<?php endforeach; ?>
    addContactMethod();
});
</script>
<?php

include $AppUI->getTheme()->resolveTemplate('contacts/addedit');