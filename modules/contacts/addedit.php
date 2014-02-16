<?php
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}
// @todo    convert to template
$contact_id = (int) w2PgetParam($_GET, 'contact_id', 0);
$company_id = (int) w2PgetParam($_GET, 'company_id', 0);
$dept_id = (int) w2PgetParam($_GET, 'dept_id', 0);

$row = new CContact();
$row->contact_id = $contact_id;

$obj = $row;
$canAddEdit = $obj->canAddEdit();
$canAuthor = $obj->canCreate();
$canEdit = $obj->canEdit();
if (!$canAddEdit) {
	$AppUI->redirect(ACCESS_DENIED);
}

// load the record data
$obj = $AppUI->restoreObject();
if ($obj) {
    $row = $obj;
    $contact_id = $row->contact_id;
} else {
    $row->load($contact_id);
}
if (!$row && $contact_id > 0) {
    $AppUI->setMsg('Contact');
    $AppUI->setMsg('invalidID', UI_MSG_ERROR, true);
    $AppUI->redirect();
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

$is_user = $row->isUser($contact_id);

$df = $AppUI->getPref('SHDATEFORMAT');
$df .= ' ' . $AppUI->getPref('TIMEFORMAT');






// setup the title block
$ttl = $contact_id > 0 ? 'Edit Contact' : 'Add Contact';
$titleBlock = new w2p_Theme_TitleBlock($ttl, 'icon.png', $m, $m . '.' . $a);
$titleBlock->addCrumb('?m=contacts', 'contacts list');
$titleBlock->addCrumb('?m=contacts&a=view&contact_id=' . $contact_id, 'view contact');
$canDelete = $row->canDelete();

$titleBlock->show();
$company_detail = $row->getCompanyDetails();
$dept_detail = $row->getDepartmentDetails();
if ($contact_id == 0 && $company_id > 0) {
	$company_detail['company_id'] = $company_id;
	$company_detail['company_name'] = $company_name;
	$dept_detail['dept_id'] = $dept_id;
	$dept_detail['dept_name'] = $dept_name;
}

$methods = $row->getContactMethods();
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
    } else if (form.contact_birthday.value.length > 0) {
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
		form.del.value = '<?php echo $contact_id; ?>';
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
    $('#contact_methods_' + index + '_').append('<input type="text" name="contact_methods[value][' + index + ']" size="25" maxlength="255" class="text" value="' + (value ? value : "") + '" /><?php echo w2PtoolTip('Contact Method', 'Remove') ?><a id="remove_contact_method" href="javascript:removeContactMethod(\'' + index + '\')"><img src="<?php echo w2PfindImage('icons/remove.png'); ?>" style="border: 0;" alt="" /></a><?php echo w2PendTip() ?>');
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

$form = new w2p_Output_HTML_FormHelper($AppUI);

?>
<form name="changecontact" action="?m=<?php echo $m; ?>" method="post" accept-charset="utf-8" class="contacts addedit">
    <input type="hidden" name="dosql" value="do_contact_aed" />
    <input type="hidden" name="del" value="0" />
    <input type="hidden" name="contact_project" value="0" />
    <input type="hidden" name="contact_unique_update" value="<?php echo uniqid(''); ?>" />
    <input type="hidden" name="contact_id" value="<?php echo $contact_id; ?>" />
    <input type="hidden" name="contact_owner" value="<?php echo $row->contact_owner ? $row->contact_owner : $AppUI->user_id; ?>" />
    <?php echo $form->addNonce(); ?>

    <div class="std addedit contacts">
        <div class="column left">
            <p>
                <?php $form->showLabel('First Name'); ?>
                <?php $form->showField('contact_first_name', $row->contact_first_name, array('maxlength' => 50)); ?>
            </p>
            <p>
                <?php $form->showLabel('Last Name'); ?>
                <?php
                $options = array('maxlength' => 50);
                if ($contact_id == 0) {
                    $options['onBlur'] = "orderByName('name')";
                }
                ?>
                <?php $form->showField('contact_last_name', $row->contact_last_name, $options); ?>
                <a href="javascript: void(0);" onclick="orderByName('name')">[<?php echo $AppUI->_('use in display'); ?>]</a>
            </p>
            <p>
                <?php $form->showLabel('Display Name'); ?>
                <?php $form->showField('contact_display_name', $row->contact_display_name, array('maxlength' => 50)); ?>
            </p>
            <p>
                <?php $form->showLabel('Title'); ?>
                <?php $form->showField('contact_title', $row->contact_title, array('maxlength' => 50)); ?>
            </p>
            <p>
                <?php $form->showLabel('Email'); ?>
                <?php $form->showField('contact_email', $row->contact_email, array('maxlength' => 60)); ?>
            </p>
            <p>
                <?php $form->showLabel('Phone'); ?>
                <?php $form->showField('contact_phone', $row->contact_phone, array('maxlength' => 50)); ?>
            </p>
            <p>
                <?php $form->showLabel('Company'); ?>
                <?php echo arraySelect($companies, 'contact_company', 'size="1" class="text company" onChange="companyChange()"', $row->contact_company); ?>
            </p>
            <?php if ($AppUI->isActiveModule('departments')) { ?>
            <p>
                <?php $form->showLabel('Department'); ?>
                <input type="text" class="text" name="contact_department_name" id="contact_department_name" value="<?php echo $dept_detail['dept_name']; ?>" maxlength="100" size="25" />
                <input type='hidden' name='contact_department' value='<?php echo $dept_detail['dept_id']; ?>' />
                <input type="button" class="button btn btn-primary btn-mini" value="<?php echo $AppUI->_('select department...'); ?>" onclick="popDepartment()" />
            </p>
            <?php } ?>
            <p>
                <?php $form->showLabel('Job Title'); ?>
                <?php $form->showField('contact_job', $row->contact_job, array('maxlength' => 100)); ?>
            </p>
            <p>
                <?php $form->showLabel('Contact Notes'); ?>
                <?php $form->showField('contact_notes', $row->contact_notes); ?>
            </p>
            <?php
            $custom_fields = new w2p_Core_CustomFields($m, $a, $row->contact_id, "edit");
            echo '<p>' . $custom_fields->getHTML() . '</p>';
            $form->showCancelButton();
            ?>
        </div>
        <div class="column right">
            <p>
                <?php $form->showLabel('Address1'); ?>
                <?php $form->showField('contact_address1', $row->contact_address1, array('maxlength' => 60)); ?>
            </p>
            <p>
                <?php $form->showLabel('Address2'); ?>
                <?php $form->showField('contact_address2', $row->contact_address2, array('maxlength' => 60)); ?>
            </p>
            <p>
                <?php $form->showLabel('City'); ?>
                <?php $form->showField('contact_city', $row->contact_city, array('maxlength' => 30)); ?>
            </p>
            <p>
                <?php $form->showLabel('State'); ?>
                <?php $form->showField('contact_state', $row->contact_state, array('maxlength' => 30)); ?>
            </p>
            <p>
                <?php $form->showLabel('Zip'); ?>
                <?php $form->showField('contact_zip', $row->contact_zip, array('maxlength' => 11)); ?>
            </p>
            <p>
                <?php $form->showLabel('Country'); ?>
                <?php $form->showField('contact_country', $row->contact_country, array(), $countries); ?>
            </p>
            <p>
                <?php $form->showLabel('Birthday'); ?>
                <?php $form->showField('contact_birthday', $row->contact_birthday, array('maxlength' => 10)); ?> (<?php echo $AppUI->_('yyyy-mm-dd'); ?>)
            </p>
            <p><strong><?php echo $AppUI->_('Contact Update Info'); ?></strong></p>
            <p>
                <?php $form->showLabel('Awaiting Update'); ?>
                <?php
                $options = array('onclick' => 'updateVerify()');
                if ($row->contact_updatekey) {
                    $options['checked'] = 'checked';
                }
                ?>
                <?php $form->showField('contact_updateask', 1, $options); ?>
            </p>
            <p>
                <?php $form->showLabel('Update Requested'); ?>
                <?php $last_ask = new w2p_Utilities_Date($row->contact_updateasked); ?>
                <?php
                echo $row->contact_updateasked ? $AppUI->formatTZAwareTime($row->contact_updateasked) : '&nbsp;';
                ?>
            </p>
            <p>
                <?php $form->showLabel('Last Updated'); ?>
                <?php $lastupdated = new w2p_Utilities_Date($row->contact_lastupdate);
                echo ($row->contact_lastupdate && !($row->contact_lastupdate == 0)) ? $AppUI->formatTZAwareTime($row->contact_lastupdate) : '&nbsp;';
                ?>
            </p>
            <p>
                <?php $form->showLabel('Private Entry'); ?>
                <?php
                $options = array();
                if ($row->contact_private) {
                    $options['checked'] = 'checked';
                }
                ?>
                <?php $form->showField('contact_private', $row->contact_private, $options); ?>
            </p>
            <p>
                <?php $form->showLabel('Contact Methods'); ?>
                <?php echo w2PtoolTip('Contact Method', 'add new', false, 'add_contact_method') ?><a href="javascript:addContactMethod();"><img src="<?php echo w2PfindImage('icons/edit_add.png'); ?>" style="border: 0;" alt="" /></a><?php echo w2PendTip() ?>
            </p>
            <p id="custom_fields"></p>
            <?php $form->showSaveButton(); ?>
        </div>
    </div>
</form>