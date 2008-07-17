<?php /* $Id$ $URL$ */
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}

$company_id = intval(w2PgetParam($_GET, 'company_id', 0));

// check permissions for this company
$perms = &$AppUI->acl();
// If the company exists we need edit permission,
// If it is a new company we need add permission on the module.
if ($company_id) {
	$canEdit = $perms->checkModuleItem('companies', 'edit', $company_id);
} else {
	$canEdit = $perms->checkModule('companies', 'add');
}

if (!$canEdit) {
	$AppUI->redirect('m=public&a=access_denied');
}

// load the company types
$types = w2PgetSysVal('CompanyType');
$countries = array('' => $AppUI->_('(Select a Country)')) + w2PgetSysVal('GlobalCountries');

// load the record data
$q = new DBQuery;
$q->addTable('companies');
$q->addQuery('companies.*');
$q->addQuery('con.contact_first_name');
$q->addQuery('con.contact_last_name');
$q->leftJoin('users', 'u', 'u.user_id = companies.company_owner');
$q->leftJoin('contacts', 'con', 'u.user_contact = con.contact_id');
$q->addWhere('companies.company_id = ' . (int)$company_id);
$obj = null;
$q->loadObject($obj);
$q->clear();

if (!$obj && $company_id > 0) {
	// $AppUI->setMsg( '	$qid =& $q->exec(); Company' ); // What is this for?
	$AppUI->setMsg('invalidID', UI_MSG_ERROR, true);
	$AppUI->redirect();
}

// collect all the users for the company owner list
$q = new DBQuery;
$q->addTable('users', 'u');
$q->addTable('contacts', 'con');
$q->addQuery('user_id');
$q->addQuery('CONCAT(contact_first_name, \' \', contact_last_name)');
$q->addOrder('contact_last_name');
$q->addWhere('u.user_contact = con.contact_id');
$owners = $q->loadHashList();

// setup the title block
$ttl = $company_id > 0 ? 'Edit Company' : 'Add Company';
$titleBlock = new CTitleBlock($ttl, 'handshake.png', $m, $m . '.' . $a);
$titleBlock->addCrumb('?m=companies', 'companies list');
if ($company_id != 0) {
	$titleBlock->addCrumb('?m=companies&a=view&company_id=' . $company_id, 'view this company');
}
$titleBlock->show();
?>

<script language="javascript">
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
</script>

<table cellspacing="1" cellpadding="1" border="0" width='100%' class="std">
<tr>
<td>
<table>
	<form name="changeclient" action="?m=companies" method="post">
	<input type="hidden" name="dosql" value="do_company_aed" />
	<input type="hidden" name="company_id" value="<?php echo $company_id; ?>" />
	<tr>
		<td align="right"><?php echo $AppUI->_('Company Name'); ?>:</td>
		<td>
			<input type="text" class="text" name="company_name" value="<?php echo w2PformSafe($obj->company_name); ?>" size="50" maxlength="255" /> (<?php echo $AppUI->_('required'); ?>)
		</td>
	</tr>
	<tr>
		<td align="right"><?php echo $AppUI->_('Email'); ?>:</td>
		<td>
			<input type="text" class="text" name="company_email" value="<?php echo w2PformSafe($obj->company_email); ?>" size="30" maxlength="255" />
		</td>
	</tr>
	<tr>
		<td align="right"><?php echo $AppUI->_('Phone'); ?>:</td>
		<td>
			<input type="text" class="text" name="company_phone1" value="<?php echo w2PformSafe($obj->company_phone1); ?>" maxlength="30" />
		</td>
	</tr>
	<tr>
		<td align="right"><?php echo $AppUI->_('Phone'); ?>2:</td>
		<td>
			<input type="text" class="text" name="company_phone2" value="<?php echo w2PformSafe($obj->company_phone2); ?>" maxlength="50" />
		</td>
	</tr>
	<tr>
		<td align="right"><?php echo $AppUI->_('Fax'); ?>:</td>
		<td>
			<input type="text" class="text" name="company_fax" value="<?php echo w2PformSafe($obj->company_fax); ?>" maxlength="30" />
		</td>
	</tr>
	<tr>
		<td colspan="2" align="center">
			<img src="<?php echo w2PfindImage('shim.gif'); ?>" width="50" height="1" /><?php echo $AppUI->_('Address'); ?><br />
			<hr width="500" align="center" size="1" />
		</td>
	</tr>
	<tr>
		<td align="right"><?php echo $AppUI->_('Address'); ?>1:</td>
		<td><input type="text" class="text" name="company_address1" value="<?php echo w2PformSafe($obj->company_address1); ?>" size="50" maxlength="255" /></td>
	</tr>
	<tr>
		<td align="right"><?php echo $AppUI->_('Address'); ?>2:</td>
		<td><input type="text" class="text" name="company_address2" value="<?php echo w2PformSafe($obj->company_address2); ?>" size="50" maxlength="255" /></td>
	</tr>
	<tr>
		<td align="right"><?php echo $AppUI->_('City'); ?>:</td>
		<td><input type="text" class="text" name="company_city" value="<?php echo w2PformSafe($obj->company_city); ?>" size="50" maxlength="50" /></td>
	</tr>
	<tr>
		<td align="right"><?php echo $AppUI->_('State'); ?>:</td>
		<td><input type="text" class="text" name="company_state" value="<?php echo w2PformSafe($obj->company_state); ?>" maxlength="50" /></td>
	</tr>
	<tr>
		<td align="right"><?php echo $AppUI->_('Zip'); ?>:</td>
		<td><input type="text" class="text" name="company_zip" value="<?php echo w2PformSafe($obj->company_zip); ?>" maxlength="15" /></td>
	</tr>
	<tr>
		<td align="right"><?php echo $AppUI->_('Country'); ?>:</td>
		<td>
<?php
			echo arraySelect($countries, 'company_country', 'size="1" class="text"', $obj->company_country ? $obj->company_country : 0);
?>
		</td>
	</tr>
	<tr>
		<td align="right">
			URL http://<a name="x"></a></td><td><input type="text" class="text" value="<?php echo w2PformSafe($obj->company_primary_url); ?>" name="company_primary_url" size="50" maxlength="255" />
			<a href="javascript: void(0);" onclick="testURL('CompanyURLOne')">[<?php echo $AppUI->_('test'); ?>]</a>
		</td>
	</tr>
	
	<tr>
		<td align="right"><?php echo $AppUI->_('Company Owner'); ?>:</td>
		<td>
	<?php
echo arraySelect($owners, 'company_owner', 'size="1" class="text"', $obj->company_owner ? $obj->company_owner : $AppUI->user_id);
?>
		</td>
	</tr>
	
	<tr>
		<td align="right"><?php echo $AppUI->_('Type'); ?>:</td>
		<td>
	<?php
echo arraySelect($types, 'company_type', 'size="1" class="text"', $obj->company_type, true);
?>
		</td>
	</tr>
	
	<tr>
		<td align="right" valign="top"><?php echo $AppUI->_('Description'); ?>:</td>
		<td align="left">
			<textarea cols="70" rows="10" class="textarea" name="company_description"><?php echo $obj->company_description; ?></textarea>
		</td>
	</tr>
</table>


</td>
	<td align='left'>
		<?php
require_once ($AppUI->getSystemClass('CustomFields'));
$custom_fields = new CustomFields($m, $a, $obj->company_id, "edit");
$custom_fields->printHTML();
?>		
	</td>
</tr>
<tr>
	<td><input type="button" value="<?php echo $AppUI->_('back'); ?>" class="button" onclick="javascript:history.back(-1);" /></td>
	<td align="right"><input type="button" value="<?php echo $AppUI->_('submit'); ?>" class="button" onclick="submitIt()" /></td>
</tr>
</form>
</table>