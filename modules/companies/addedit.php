<?php
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}

$company_id = (int) w2PgetParam($_GET, 'company_id', 0);

$company = new CCompany();
$company->company_id = $company_id;

$obj = $company;
$canAddEdit = $obj->canAddEdit();
$canAuthor = $obj->canCreate();
$canEdit = $obj->canEdit();
if (!$canAddEdit) {
	$AppUI->redirect(ACCESS_DENIED);
}

// load the record data
$obj = $AppUI->restoreObject();
if ($obj) {
    $company = $obj;
    $company_id = $company->company_id;
} else {
    $company->loadFull(null, $company_id);
}
if (!$company && $company_id > 0) {
	$AppUI->setMsg('Company');
    $AppUI->setMsg('invalidID', UI_MSG_ERROR, true);
	$AppUI->redirect();
}

// setup the title block
$ttl = $company_id > 0 ? 'Edit Company' : 'Add Company';
$titleBlock = new w2p_Theme_TitleBlock($ttl, 'handshake.png', $m, $m . '.' . $a);
$titleBlock->addCrumb('?m=companies', 'companies list');
if ($company_id != 0) {
	$titleBlock->addCrumb('?m=companies&a=view&company_id=' . $company_id, 'view this company');
}
$titleBlock->show();


// load the company types
$types = w2PgetSysVal('CompanyType');
$countries = array('' => $AppUI->_('(Select a Country)')) + w2PgetSysVal('GlobalCountriesPreferred') +
		array('-' => '----') + w2PgetSysVal('GlobalCountries');
?>

<script language="javascript" type="text/javascript">
function submitIt() {
	var form = document.changeclient;
	if (form.company_name.value.length < 3) {
		alert( "<?php echo $AppUI->_('companyValidName', UI_OUTPUT_JS); ?>" );
		form.company_name.focus();
	} else {
		form.submit();
	}
}

function testURL( x ) {
	var test = 'document.changeclient.company_primary_url.value';
	test = eval(test);
	if (test.length > 6) {
		newwin = window.open( 'http://' + test, 'newwin', '' );
	}
}

function addInactiveUser() {
	var ut = document.getElementById('user_table');
	var bt = document.getElementById('newuser');

	var cb = document.createElement('input');
	cb.setAttribute('type', 'checkbox');
	cb.setAttribute('name', 'newuser_on[]');
	cb.setAttribute('id', 'newuser_on[]');
	cb.setAttribute('checked', 'checked');

	var nm = document.createElement('input');
	nm.setAttribute('type', 'text');
	nm.setAttribute('size', '30');
	nm.setAttribute('name', 'newusername[]');
	nm.setAttribute('id', 'newusername[]');
	nm.setAttribute('class', 'text');

	var pw = document.createElement('input');
	pw.setAttribute('type', 'text');
	pw.setAttribute('size', '40');
	pw.setAttribute('name', 'newemail[]');
	pw.setAttribute('id', 'newemail[]');
	pw.setAttribute('class', 'text');
	
	var td1 = document.createElement('td');
	td1.appendChild(cb);
	var td2 = document.createElement('td');
	td2.appendChild(nm);
	var td3 = document.createElement('td');
	td3.setAttribute('align', 'right');				 
	td3.appendChild(pw);

	var tr = document.createElement('tr');
	tr.appendChild(td1);
	tr.appendChild(td2);
	tr.appendChild(td3);

	addInactiveUser.counter++;

	ut.insertBefore(tr, bt);

	nm.focus();
}

</script>

<form name="changeclient" action="?m=companies" method="post" accept-charset="utf-8">
	<input type="hidden" name="dosql" value="do_company_aed" />
	<input type="hidden" name="company_id" value="<?php echo $company_id; ?>" />

	<table cellspacing="1" cellpadding="1" border="0" width="100%" class="std addedit">
		<tr>
			<td>
				<table>				
					<tr>
						<td align="right"><?php echo $AppUI->_('Company Name'); ?>:</td>
						<td>
							<input type="text" class="text" name="company_name" value="<?php echo w2PformSafe($company->company_name); ?>" size="50" maxlength="255" /> (<?php echo $AppUI->_('required'); ?>)
						</td>
					</tr>
					<tr>
						<td align="right"><?php echo $AppUI->_('Email'); ?>:</td>
						<td>
							<input type="text" class="text" name="company_email" value="<?php echo w2PformSafe($company->company_email); ?>" size="30" maxlength="255" />
						</td>
					</tr>
					<tr>
						<td align="right"><?php echo $AppUI->_('Phone'); ?>:</td>
						<td>
							<input type="text" class="text" name="company_phone1" value="<?php echo w2PformSafe($company->company_phone1); ?>" maxlength="30" />
						</td>
					</tr>
					<tr>
						<td align="right"><?php echo $AppUI->_('Phone'); ?>2:</td>
						<td>
							<input type="text" class="text" name="company_phone2" value="<?php echo w2PformSafe($company->company_phone2); ?>" maxlength="50" />
						</td>
					</tr>
					<tr>
						<td align="right"><?php echo $AppUI->_('Fax'); ?>:</td>
						<td>
							<input type="text" class="text" name="company_fax" value="<?php echo w2PformSafe($company->company_fax); ?>" maxlength="30" />
						</td>
					</tr>
					<tr>
						<td colspan="2" align="center">
							<img src="<?php echo w2PfindImage('shim.gif'); ?>" width="50" height="1" alt="" /><?php echo $AppUI->_('Address'); ?><br />
							<hr width="500" align="center" size="1" />
						</td>
					</tr>
					<tr>
						<td align="right"><?php echo $AppUI->_('Address'); ?>1:</td>
						<td><input type="text" class="text" name="company_address1" value="<?php echo w2PformSafe($company->company_address1); ?>" size="50" maxlength="255" /></td>
					</tr>
					<tr>
						<td align="right"><?php echo $AppUI->_('Address'); ?>2:</td>
						<td><input type="text" class="text" name="company_address2" value="<?php echo w2PformSafe($company->company_address2); ?>" size="50" maxlength="255" /></td>
					</tr>
					<tr>
						<td align="right"><?php echo $AppUI->_('City'); ?>:</td>
						<td><input type="text" class="text" name="company_city" value="<?php echo w2PformSafe($company->company_city); ?>" size="50" maxlength="50" /></td>
					</tr>
					<tr>
						<td align="right"><?php echo $AppUI->_('State'); ?>:</td>
						<td><input type="text" class="text" name="company_state" value="<?php echo w2PformSafe($company->company_state); ?>" maxlength="50" /></td>
					</tr>
					<tr>
						<td align="right"><?php echo $AppUI->_('Zip'); ?>:</td>
						<td><input type="text" class="text" name="company_zip" value="<?php echo w2PformSafe($company->company_zip); ?>" maxlength="15" /></td>
					</tr>
					<tr>
						<td align="right"><?php echo $AppUI->_('Country'); ?>:</td>
						<td>
							<?php
								echo arraySelect($countries, 'company_country', 'size="1" class="text"', $company->company_country ? $company->company_country : 0);
							?>
						</td>
					</tr>
					<tr>
						<td align="right">
							URL http://<a name="x"></a></td><td><input type="text" class="text" value="<?php echo w2PformSafe($company->company_primary_url); ?>" name="company_primary_url" size="50" maxlength="255" />
							<a href="javascript: void(0);" onclick="testURL('CompanyURLOne')">[<?php echo $AppUI->_('test'); ?>]</a>
						</td>
					</tr>	
					<tr>
						<td align="right"><?php echo $AppUI->_('Company Owner'); ?>:</td>
						<td>
							<?php
								$perms = &$AppUI->acl();
                                $users = $perms->getPermittedUsers('companies');
								echo arraySelect($users, 'company_owner', 'size="1" class="text"', $company->company_owner ? $company->company_owner : $AppUI->user_id);
							?>
						</td>
					</tr>
					<tr>
						<td align="right"><?php echo $AppUI->_('Type'); ?>:</td>
						<td>
							<?php
								echo arraySelect($types, 'company_type', 'size="1" class="text"', $company->company_type, true);
							?>
						</td>
					</tr>
					<tr>
						<td align="right" valign="top"><?php echo $AppUI->_('Description'); ?>:</td>
						<td align="left">
							<textarea cols="70" rows="10" class="textarea" name="company_description"><?php echo $company->company_description; ?></textarea>
						</td>
					</tr>
				</table>
			</td>
			<td align='left' valign="top">
				<table><tbody id="user_table">
					<tr><td colspan="3" align="center"><?php echo $AppUI->_('Associated inactive users'); ?></td></tr>
					<tr><td colspan="3"><hr width="500" align="center" size="1" /></td></tr>
					<?php
					if ($company_id > 0) {
						$users = w2PgetUsersList();
						foreach ($users as $user) {
							if (($user['contact_company'] == $company_id) && (!$perms->isUserPermitted($user['user_id']))) {
								echo '<tr>';
								echo '<input type="hidden" name="user_id[]" value="' . $user['user_id'] . '">';
								echo '<td><input type="checkbox" name="user_on[]" id="user_on[]" checked="checked" value="' . $user['user_id'] . '">&nbsp;</td>';
								echo '<td><input type="text" size="30" name="username[]" id="username[]" readonly="readonly" value="' . $user['contact_display_name'] . '" class="text">&nbsp;</td>';
								echo '<td align="right"><input type="text" size="40" name="email[]" id="email[]" readonly="readonly" value="' . $user['contact_email'] . '" class="text"></td>';
								echo '</tr>';
							}
						}
					}
					?>
					<tr id="newuser"><td colspan="3" align="right"><input type="button" value="<?php echo $AppUI->_('add inactive user'); ?>" class="button" onclick="javascript:addInactiveUser();" /></td></tr>
				</tbody></table>
				<?php
					$custom_fields = new w2p_Core_CustomFields($m, $a, $company->company_id, "edit");
					$custom_fields->printHTML();
				?>
			</td>
		</tr>
		<tr>
			<td><input type="button" value="<?php echo $AppUI->_('back'); ?>" class="button" onclick="javascript:history.back(-1);" /></td>
			<td align="right"><input type="button" value="<?php echo $AppUI->_('submit'); ?>" class="button" onclick="submitIt()" /></td>
		</tr>
	</table>
</form>