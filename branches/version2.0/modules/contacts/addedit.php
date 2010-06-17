<?php /* $Id$ $URL$ */
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}

$contact_id = (int) w2PgetParam($_GET, 'contact_id', 0);
$company_id = (int) w2PgetParam($_GET, 'company_id', 0);
$dept_id = (int) w2PgetParam($_GET, 'dept_id', 0);

$company = new CCompany();
$company->load($company_id);
$company_name = $company->company_name;

$dept = new CDepartment();
$dept->load($dept_id);
$dept_name = $dept->dept_name;

// check permissions for this record
$perms = &$AppUI->acl();
$canAuthor = canAdd('contacts');
$canEdit = $perms->checkModuleItem('contacts', 'edit', $contact_id);

// check permissions
if (!$canAuthor && !$contact_id) {
	$AppUI->redirect('m=public&a=access_denied');
}

if (!$canEdit && $contact_id) {
	$AppUI->redirect('m=public&a=access_denied');
}

if ($msg == $AppUI->_('contactsDeleteUserError', UI_OUTPUT_JS)) {
	$userDeleteProtect = true;
}

// load the record data
$row = new CContact();
$obj = $AppUI->restoreObject();
if ($obj) {
  $row = $obj;
  $contact_id = $row->contact_id;
} else {
  $row->loadFull($AppUI, $contact_id);
}
if (!$row && $contact_id > 0) {
  $AppUI->setMsg('Link');
  $AppUI->setMsg('invalidID', UI_MSG_ERROR, true);
  $AppUI->redirect();
}

$canDelete = $row->canDelete($msg, $contact_id);
$is_user = $row->isUser($contact_id);



$countries = array('' => $AppUI->_('(Select a Country)')) + w2PgetSysVal('GlobalCountries');

$df = $AppUI->getPref('SHDATEFORMAT');
$df .= ' ' . $AppUI->getPref('TIMEFORMAT');

// setup the title block
$ttl = $contact_id > 0 ? 'Edit Contact' : 'Add Contact';
$titleBlock = new CTitleBlock($ttl, 'monkeychat-48.png', $m, $m . '.' . $a);
$titleBlock->addCrumb('?m=contacts', 'contacts list');
if ($canDelete && $contact_id) {
	$titleBlock->addCrumbDelete('delete contact', $canDelete, $msg);
}

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

?>

<script language="javascript">
<?php
echo 'window.company_id=' . ($company_detail['company_id'] ? $company_detail['company_id'] : 0) . ";\n";
echo 'window.company_value=\'' . addslashes(($company_detail['company_name'] ? $company_detail['company_name'] : '')) . "';\n";
?>

function submitIt() {
	var form = document.changecontact;
	if (form.contact_last_name.value.length < 1) {
		alert( '<?php echo $AppUI->_('contactsValidName', UI_OUTPUT_JS); ?>' );
		form.contact_last_name.focus();
	} else if (form.contact_order_by.value.length < 1) {
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

function setDepartment( key, val ){
	var f = document.changecontact;
 	if (val != '') {
    	f.contact_department.value = key;
			f.contact_department_name.value = val;
    }
}

function popCompany() {
	var f = document.changecontact;
	window.open('./index.php?m=contacts&a=select_contact_company&dialog=1&table_name=companies&company_id=<?php echo $company_detail['company_id']; ?>', 'company', 'left=50,top=50,height=320,width=400,resizable');
}

function setCompany( key, val ){
	var f = document.changecontact;
 	if (val != '') {
    	f.contact_company.value = key;
			f.contact_company_name.value = val;
    	if ( window.company_id != key ){
    		f.contact_department.value = '';
				f.contact_department_name.value = '';
    	}
    	window.company_id = key;
    	window.company_value = val;
    }
}

function delIt(){
<?php
if ($userDeleteProtect) {
?>
	alert('<?php echo $AppUI->_('contactsDeleteUserError', UI_OUTPUT_JS); ?>');
<?php
} else {
?>
	var form = document.changecontact;
	if(confirm('<?php echo $AppUI->_('contactsDelete', UI_OUTPUT_JS); ?>')) {
		form.del.value = '<?php echo $contact_id; ?>';
		form.submit();
	}
<?php
}
?>
}

function orderByName( x ){
	var form = document.changecontact;
	if (x == 'name') {
		form.contact_order_by.value = form.contact_first_name.value + ' ' + form.contact_last_name.value;
	} else {
		form.contact_order_by.value = form.contact_company_name.value;
	}
}

function companyChange() {
	var f = document.changecontact;
	if ( f.contact_company.value != window.company_value ){
		f.contact_department.value = '';
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
        option = new Element("option");
        option.setAttribute("value", value);
        option.innerHTML = text;
        option.selected = (value == selected);
        select.appendChild(option);
    }

    select = new Element('select', {
        name: "contact_methods[field][" + index + "]",
        size: "1",
        class: "text"
    });

    addOption(select, "", "");
    <?php foreach ($methodLabels as $value => $text): ?> 
    addOption(select, "<?php echo $value; ?>", "<?php echo $text; ?>", field);
    <?php endforeach; ?> 

    /* Create a new table row */
    tr = new Element('tr', { id: "contact_methods[" + index + "]" });

    /* Add contact method type menu to the table row */
    td = new Element('td', { align: "right" });
    td.appendChild(select);
    tr.appendChild(td);

    /* Add text field for the contact method value to the table row */
    td = new Element('td');
    td.appendChild(new Element('input', {
        type: "text",
        class: "text",
        name: "contact_methods[value][" + index + "]",
        value: value ? value : "",
        maxlength: "255",
        size: "25"
    }));
    tr.appendChild(td);

    /* Add a button to remove the table row */
    a = new Element("a", {
        id: "remove_contact_method",
        href: "javascript:removeContactMethod('" + index + "');"
    });

    img = new Element("img", {
        src: "<?php echo w2PfindImage('icons/remove.png'); ?>",
        style: "border: 0;"
    });

    a.appendChild(img);
    td.appendChild(a);

    /* Add or relocate the button to add new row */
    add = document.getElementById("add_contact_method");
    if (!add) {
        add = new Element("span", {
            id: "add_contact_method" }
        );

        a = new Element("a", {
            href: "javascript:addContactMethod();"
        });

        img = new Element("img", {
            src: "<?php echo w2PfindImage('icons/edit_add.png'); ?>",
            style: "border: 0;"
        });

        a.appendChild(img);

        add.appendChild(a);
    } else {
        add.parentNode.removeChild(add);
    }
    td.appendChild(add);

    /* Add the new table row to the table */
    bottomRow = document.getElementById("custom_fields");
    bottomRow.parentNode.insertBefore(tr, bottomRow);
}

function removeContactMethod(index) {
    tr = document.getElementById("contact_methods[" + index + "]");
    tr.parentNode.removeChild(tr);
}

window.addEvent("domready", function() {
<?php foreach ($methods as $method => $value): ?>
    addContactMethod("<?php echo $method; ?>", "<?php echo $value; ?>");
<?php endforeach; ?>
    addContactMethod();
});
</script>

<form name="changecontact" action="?m=contacts" method="post" accept-charset="utf-8">
    <input type="hidden" name="dosql" value="do_contact_aed" />
    <input type="hidden" name="del" value="0" />
    <input type="hidden" name="contact_project" value="0" />
    <input type="hidden" name="contact_unique_update" value="<?php echo uniqid(''); ?>" />
    <input type="hidden" name="contact_id" value="<?php echo $contact_id; ?>" />
    <input type="hidden" name="contact_owner" value="<?php echo $row->contact_owner ? $row->contact_owner : $AppUI->user_id; ?>" />

    <table border="0" cellpadding="4" cellspacing="0" width="100%" class="std">
        <tr>
            <td>
                <table border="0" cellpadding="1" cellspacing="1">

                <tr>
                    <td align="right"><?php echo $AppUI->_('First Name'); ?>:</td>
                    <td>
                        <input type="text" class="text" size="25" name="contact_first_name" value="<?php echo $row->contact_first_name; ?>" maxlength="50" />
                    </td>
                </tr>
                <tr>
                    <td align="right">&nbsp;&nbsp;<?php echo $AppUI->_('Last Name'); ?>:</td>
                    <td>
                        <input type="text" class="text" size="25" name="contact_last_name" value="<?php echo $row->contact_last_name; ?>" maxlength="50" <?php if ($contact_id == 0) { ?> onBlur="orderByName('name')"<?php } ?> />
                        <a href="javascript: void(0);" onclick="orderByName('name')">[<?php echo $AppUI->_('use in display'); ?>]</a>
                    </td>
                </tr>
                <tr>
                    <td align="right"><?php echo $AppUI->_('Display Name'); ?>: </td>
                    <td>
                        <input type="text" class="text" size="25" name="contact_order_by" value="<?php echo $row->contact_order_by; ?>" maxlength="50" />
                    </td>
                </tr>
                <tr>
                    <td align="right"><label for="contact_private"><?php echo $AppUI->_('Private Entry'); ?>:</label> </td>
                    <td>
                        <input type="checkbox" value="1" name="contact_private" id="contact_private" <?php echo ($row->contact_private ? 'checked="checked"' : ''); ?> />
                    </td>
                </tr>
                </table>
            </td>
            <td valign="top" align="right">
                <table border="0" cellpadding="1" cellspacing="1">
                <th colspan="2">
                    <strong><?php echo $AppUI->_('Contact Update Info'); ?></strong>
                </th>
                <tr>
                    <td align="right" width="100" nowrap="nowrap"><?php echo $AppUI->_('Waiting Update'); ?>?:</td>
                    <td align="center">
                        <input type="checkbox" value="1" name="contact_updateask" <?php echo $row->contact_updatekey ? 'checked="checked"' : ''; ?> onclick="updateVerify()"/>
                    </td>
                </tr>
                <tr>
                    <?php $last_ask = new CDate($row->contact_updateasked); ?>
                    <td align="right" nowrap="nowrap"><?php echo $AppUI->_('Last Update Requested'); ?>:</td>
                    <td align="center" nowrap="nowrap"><?php echo $row->contact_updateasked ? $last_ask->format($df) : ''; ?></td>
                </tr>
                <tr>
                    <?php $lastupdated = new CDate($row->contact_lastupdate); ?>
                    <td align="right" width="100" nowrap="nowrap"><?php echo $AppUI->_('Last Updated'); ?>:</td>
					<td align="center" nowrap="nowrap">
                        <?php
                            echo ($row->contact_lastupdate && !($row->contact_lastupdate == 0)) ? $AppUI->formatTZAwareTime($row->contact_lastupdate) : '';
                        ?>
                    </td>
                </tr>
                </table>
            </td>
        </tr>
        <tr>
            <td valign="top" width="50%">
                <table border="0" cellpadding="1" cellspacing="1" class="details" width="100%">
                    <tr>
                        <td align="right"><?php echo $AppUI->_('Job Title'); ?>:</td>
                        <td nowrap="nowrap">
                            <input type="text" class="text" name="contact_job" value="<?php echo $row->contact_job; ?>" maxlength="100" size="25" />
                        </td>
                    </tr>
                    <tr>
                        <td align="right"><?php echo $AppUI->_('Company'); ?>:</td>
                        <td nowrap="nowrap">
                            <input type="text" class="text" name="contact_company_name" value="<?php echo $company_detail['company_name'];?>" maxlength="100" size="25" />
                            <input type="button" class="button" value="<?php echo $AppUI->_('select company...'); ?>..." onclick="popCompany()" />
                            <input type='hidden' name='contact_company' value="<?php echo $company_detail['company_id']; ?>" />
                            <a href="javascript: void(0);" onclick="orderByName('company')">[<?php echo $AppUI->_('use in display'); ?>]</a>
                            </td>
                    </tr>
                    <tr>
                        <td align="right"><?php echo $AppUI->_('Department'); ?>:</td>
                        <td nowrap="nowrap">
                            <input type="text" class="text" name="contact_department_name" value="<?php echo $dept_detail['dept_name']; ?>" maxlength="100" size="25" />
                            <input type='hidden' name='contact_department' value='<?php echo $dept_detail['dept_id']; ?>' />
                            <input type="button" class="button" value="<?php echo $AppUI->_('select department...'); ?>" onclick="popDepartment()" />
                            </td>
                    </tr>
                    <tr>
                        <td align="right"><?php echo $AppUI->_('Title'); ?>:</td>
                        <td><input type="text" class="text" name="contact_title" value="<?php echo $row->contact_title; ?>" maxlength="50" size="25" /></td>
                    </tr>
                    <tr>
                        <td align="right"><?php echo $AppUI->_('Type'); ?>:</td>
                        <td><input type="text" class="text" name="contact_type" value="<?php echo $row->contact_type; ?>" maxlength="50" size="25" /></td>
                    </tr>
                    <tr>
                        <td align="right"><?php echo $AppUI->_('Address'); ?>1:</td>
                        <td><input type="text" class="text" name="contact_address1" value="<?php echo $row->contact_address1; ?>" maxlength="60" size="25" /></td>
                    </tr>
                    <tr>
                        <td align="right"><?php echo $AppUI->_('Address'); ?>2:</td>
                        <td><input type="text" class="text" name="contact_address2" value="<?php echo $row->contact_address2; ?>" maxlength="60" size="25" /></td>
                    </tr>
                    <tr>
                        <td align="right"><?php echo $AppUI->_('City'); ?>:</td>
                        <td><input type="text" class="text" name="contact_city" value="<?php echo $row->contact_city; ?>" maxlength="30" size="25" /></td>
                    </tr>
                    <tr>
                        <td align="right"><?php echo $AppUI->_('State'); ?>:</td>
                        <td><input type="text" class="text" name="contact_state" value="<?php echo $row->contact_state; ?>" maxlength="30" size="25" /></td>
                    </tr>
                    <tr>
                        <td align="right"><?php echo $AppUI->_('Postcode') . ' / ' . $AppUI->_('Zip'); ?>:</td>
                        <td><input type="text" class="text" name="contact_zip" value="<?php echo $row->contact_zip; ?>" maxlength="11" size="25" /></td>
                    </tr>
                    <tr>
                        <td align="right"><?php echo $AppUI->_('Country'); ?>:</td>
                        <td>
                        <?php
                        echo arraySelect($countries, 'contact_country', 'size="1" class="text"', $row->contact_country ? $row->contact_country : 0);
                        ?>
                        </td>
                    </tr>
                    <tr>
                        <td align="right"><?php echo $AppUI->_('Birthday'); ?>:</td>
                        <td nowrap="nowrap">
                            <input type="text" class="text" name="contact_birthday" value="<?php echo @substr($row->contact_birthday, 0, 10); ?>" maxlength="10" size="25" />(<?php echo $AppUI->_('yyyy-mm-dd'); ?>)
                        </td>
                    </tr>
					<tr id="custom_fields">
						<th colspan="2">
							<strong><?php echo $AppUI->_('Contacts Custom Fields'); ?></strong>
						</th>
					</tr>
                    <tr>
                        <td align="right" colspan="3">
                        <?php
                        $custom_fields = new w2p_Core_CustomFields($m, $a, $row->contact_id, "edit");
                        $custom_fields->printHTML();
                        ?>
                        </td>
                    </tr>
                </table>
            </td>
            <td valign="top" width="50%">
                <strong><?php echo $AppUI->_('Contact Notes'); ?></strong><br />
                <textarea class="textarea" name="contact_notes" rows="20" cols="40"><?php echo $row->contact_notes; ?></textarea></td>
            </td>
        </tr>
        <tr>
            <td>
                <input type="button" value="<?php echo $AppUI->_('back'); ?>" class="button" onclick="javascript:window.location='./index.php?m=contacts';" />
            </td>
            <td colspan="2" align="right">
                <input type="button" value="<?php echo $AppUI->_('submit'); ?>" class="button" onclick="submitIt()" />
            </td>
        </tr>
    </table>
</form>