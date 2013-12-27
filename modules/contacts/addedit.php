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
    $row->loadFull(null, $contact_id);
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
                <label><?php echo $AppUI->_('First Name'); ?>:</label>
                <input type="text" class="text" size="25" name="contact_first_name" value="<?php echo $row->contact_first_name; ?>" maxlength="50" />
            </p>
            <p>
                <label><?php echo $AppUI->_('Last Name'); ?>:</label>
                <input type="text" class="text" size="25" name="contact_last_name" value="<?php echo $row->contact_last_name; ?>" maxlength="50" <?php if ($contact_id == 0) { ?> onBlur="orderByName('name')"<?php } ?> />
                <a href="javascript: void(0);" onclick="orderByName('name')">[<?php echo $AppUI->_('use in display'); ?>]</a>
            </p>
            <p>
                <label><?php echo $AppUI->_('Display Name'); ?>:</label>
                <input type="text" class="text" size="25" name="contact_display_name" value="<?php echo $row->contact_display_name; ?>" maxlength="50" />
            </p>
            <p>
                <label><?php echo $AppUI->_('Title'); ?>:</label>
                <input type="text" class="text" name="contact_title" value="<?php echo $row->contact_title; ?>" maxlength="50" size="25" />
            </p>
            <p>
                <label><?php echo $AppUI->_('Email'); ?>:</label>
                <input type="text" class="text" name="contact_email" value="<?php echo $row->contact_email; ?>" maxlength="60" size="25" />
            </p>
            <p>
                <label><?php echo $AppUI->_('Phone'); ?>:</label>
                <input type="text" class="text" name="contact_phone" value="<?php echo $row->contact_phone; ?>" maxlength="50" size="25" />
            </p>
            <p>
                <label><?php echo $AppUI->_('Company'); ?>:</label>
                <?php echo arraySelect($companies, 'contact_company', 'class="text" size="1" onChange="companyChange();"', $company_detail['company_id']); ?>
            </p>
            <?php if ($AppUI->isActiveModule('departments')) { ?>
            <p>
                <label><?php echo $AppUI->_('Department'); ?>:</label>
                <input type="text" class="text" name="contact_department_name" id="contact_department_name" value="<?php echo $dept_detail['dept_name']; ?>" maxlength="100" size="25" />
                <input type='hidden' name='contact_department' value='<?php echo $dept_detail['dept_id']; ?>' />
                <input type="button" class="button btn btn-primary btn-mini" value="<?php echo $AppUI->_('select department...'); ?>" onclick="popDepartment()" />
            </p>
            <?php } ?>
            <p>
                <label><?php echo $AppUI->_('Job Title'); ?>:</label>
                <input type="text" class="text" name="contact_job" value="<?php echo $row->contact_job; ?>" maxlength="100" size="25" />
            </p>
            <p>
                <label><?php echo $AppUI->_('Contact Notes'); ?>:</label>
                <textarea class="textarea" name="contact_notes" rows="20" cols="40"><?php echo $row->contact_notes; ?></textarea>
            </p>
            <?php
            $custom_fields = new w2p_Core_CustomFields($m, $a, $row->contact_id, "edit");
            echo '<p>' . $custom_fields->getHTML() . '</p>';
            ?>
            <p><input type="button" value="back" class="cancel button btn btn-danger" onclick="javascript:history.back(-1);" /></p>
        </div>
        <div class="column right">
            <p>
                <label><?php echo $AppUI->_('Address'); ?>1:</label>
                <input type="text" class="text" name="contact_address1" value="<?php echo $row->contact_address1; ?>" maxlength="60" size="25" />
            </p>
            <p>
                <label><?php echo $AppUI->_('Address'); ?>2:</label>
                <input type="text" class="text" name="contact_address2" value="<?php echo $row->contact_address2; ?>" maxlength="60" size="25" />
            </p>
            <p>
                <label><?php echo $AppUI->_('City'); ?>:</label>
                <input type="text" class="text" name="contact_city" value="<?php echo $row->contact_city; ?>" maxlength="30" size="25" />
            </p>
            <p>
                <label><?php echo $AppUI->_('State'); ?>:</label>
                <input type="text" class="text" name="contact_state" value="<?php echo $row->contact_state; ?>" maxlength="30" size="25" />
            </p>
            <p>
                <label><?php echo $AppUI->_('Zip'); ?>:</label>
                <input type="text" class="text" name="contact_zip" value="<?php echo $row->contact_zip; ?>" maxlength="11" size="25" />
            </p>
            <p>
                <label><?php echo $AppUI->_('Country'); ?>:</label>
                <?php
                echo arraySelect($countries, 'contact_country', 'size="1" class="text"', $row->contact_country ? $row->contact_country : 0);
                ?>
            </p>
            <p>
                <label><?php echo $AppUI->_('Birthday'); ?>:</label>
                <input type="text" class="text" name="contact_birthday" value="<?php echo @substr($row->contact_birthday, 0, 10); ?>" maxlength="10" size="25" />(<?php echo $AppUI->_('yyyy-mm-dd'); ?>)
            </p>
            <p><strong><?php echo $AppUI->_('Contact Update Info'); ?></strong></p>
            <p>
                <label><?php echo $AppUI->_('Waiting Update'); ?>?:</label>
                <input type="checkbox" value="1" name="contact_updateask" <?php echo $row->contact_updatekey ? 'checked="checked"' : ''; ?> onclick="updateVerify()"/>
            </p>
            <p>
                <label><?php echo $AppUI->_('Update Requested'); ?>:</label>
                <?php $last_ask = new w2p_Utilities_Date($row->contact_updateasked); ?>
                <?php echo $row->contact_updateasked ? $last_ask->format($df) : '&nbsp;'; ?>
            </p>
            <p>
                <label><?php echo $AppUI->_('Last Updated'); ?>:</label>
                <?php $lastupdated = new w2p_Utilities_Date($row->contact_lastupdate);
                echo ($row->contact_lastupdate && !($row->contact_lastupdate == 0)) ? $AppUI->formatTZAwareTime($row->contact_lastupdate) : '&nbsp;';
                ?>
            </p>
            <p>
                <label><?php echo $AppUI->_('Private Entry'); ?>:</label>
                <input type="checkbox" value="1" name="contact_private" id="contact_private" <?php echo ($row->contact_private ? 'checked="checked"' : ''); ?> />
            </p>
            <p>
                <label><?php echo $AppUI->_('Contact Methods'); ?>:</label>
                <?php echo w2PtoolTip('Contact Method', 'add new', false, 'add_contact_method') ?><a href="javascript:addContactMethod();"><img src="<?php echo w2PfindImage('icons/edit_add.png'); ?>" style="border: 0;" alt="" /></a><?php echo w2PendTip() ?>
            </p>
            <p id="custom_fields"></p>

            <p><input type="button" value="save" class="save button btn btn-primary" onclick="submitIt()" /></p>
        </div>
    </div>
</form>