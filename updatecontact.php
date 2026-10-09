<?php
require_once 'base.php';
require_once W2P_BASE_DIR . '/includes/config.php';
require_once W2P_BASE_DIR . '/includes/main_functions.php';
require_once W2P_BASE_DIR . '/includes/db_adodb.php';
$AppUI = new w2p_Core_CAppUI();
$AppUI->setStyle();
$uistyle = $AppUI->getPref('UISTYLE');

$updatekey = w2PgetParam($_GET, 'updatekey', 0);
$updatekey = preg_replace("/[^A-Za-z0-9]/", "", $updatekey);
$contact_id = CContact::getContactByUpdatekey($updatekey);

$company_id = intval(w2PgetParam($_REQUEST, 'company_id', 0));
$company_name = w2PgetParam($_REQUEST, 'company_name', null);

// check permissions for this record

if (!$contact_id) {
    echo ($AppUI->_('You are not authorized to use this page. If you should be authorized please contact') . ' ' . $w2Pconfig['company_name'] . ' ' . $AppUI->_('to give you another valid link, thank you.'));
    exit;
}

// load the record data
$msg = '';
$row = new CContact();

if (!$row->load($contact_id) && $contact_id > 0) {
    $AppUI->setMsg('Contact');
    $AppUI->setMsg('invalidID', UI_MSG_ERROR, true);
    $AppUI->redirect();
} else {
    //TODO: replace with the proper canEdit()
    if ($row->contact_private && $row->contact_owner != $AppUI->user_id && $row->contact_owner && $contact_id != 0) {
        // check only owner can edit
        $AppUI->redirect(ACCESS_DENIED);
    }
}

// setup the title block
$ttl = $contact_id > 0 ? 'Edit Contact' : 'Add Contact';
$company_detail = $row->getCompanyDetails();
$dept_detail = $row->getDepartmentDetails();
if ($contact_id == 0 && $company_id > 0) {
    $company_detail['company_id'] = $company_id;
    $company_detail['company_name'] = $company_name;
    echo $company_name;
}

$outsider = $row->contact_display_name;
require W2P_BASE_DIR . '/style/' . $uistyle . '/header.php';

echo $AppUI->getTheme()->styleRenderBoxTop();
?>

<script language="javascript">
function submitIt() {
    var form = document.changecontact;
    if (form.contact_last_name.value.length < 1) {
        alert( "<?php echo $AppUI->_('contactsValidName', UI_OUTPUT_JS); ?>" );
        form.contact_last_name.focus();
    } else {
        form.submit();
    }
}

function orderByName( x ){
    var form = document.changecontact;
    if (x == 'name') {
        form.contact_order_by.value = form.contact_last_name.value + ", " + form.contact_first_name.value;
    } else {
        form.contact_order_by.value = form.contact_company_name.value;
    }
}
</script>
<?php

$form = new w2p_Output_HTML_FormHelper($AppUI);

?>
<form name="changecontact" action="do_updatecontact.php" method="post" accept-charset="utf-8">
    <input type="hidden" name="contact_project" value="0" />
    <input type="hidden" name="contact_unique_update" value="<?php echo uniqid(''); ?>" />
    <input type="hidden" name="updatekey" value="<?php echo $updatekey; ?>" />
    <input type="hidden" name="contact_id" value="<?php echo $contact_id; ?>" />
    <input type="hidden" name="contact_owner" value="<?php echo $row->contact_owner ? $row->contact_owner : $AppUI->user_id; ?>" />
    <input type="hidden" name="contact_company" value="<?php echo $row->contact_company ? $row->contact_company : 0; ?>" />
    <input type="hidden" name="contact_department" value="<?php echo $row->contact_department ? $row->contact_department : 0; ?>" />
    <?php echo $form->addNonce(); ?>

    <div class="std addedit contacts">
        <div class="column left w2p-wide">
            <strong><?php echo $AppUI->_('Please Edit Your Contact Information Below:'); ?></strong>
            <input type="hidden" class="text" size="25" name="contact_first_name" value="<?php echo $row->contact_first_name; ?>" maxlength="50" />
            <input type="hidden" class="text" size="25" name="contact_last_name" value="<?php echo $row->contact_last_name; ?>" maxlength="50" <?php if ($contact_id == 0) { ?> onblur="orderByName('name')"<?php } ?> />
        </div>
        <div class="column left">
            <p>
                <label><?php echo $AppUI->_('Job Title'); ?>:</label>
                <input type="text" class="text" name="contact_job" value="<?php echo $row->contact_job; ?>" maxlength="100" size="25" />
            </p>
            <p>
                <label><?php echo $AppUI->_('Title'); ?>:</label>
                <input type="text" class="text" name="contact_title" value="<?php echo $row->contact_title; ?>" maxlength="50" size="25" />
            </p>
            <p>
                <label><?php echo $AppUI->_('Type'); ?>:</label>
                <input type="text" class="text" name="contact_type" value="<?php echo $row->contact_type; ?>" maxlength="50" size="25" />
            </p>
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
                <label><?php echo $AppUI->_('Postcode') . ' / ' . $AppUI->_('Zip'); ?>:</label>
                <input type="text" class="text" name="contact_zip" value="<?php echo $row->contact_zip; ?>" maxlength="11" size="25" />
            </p>
            <p>
                <label><?php echo $AppUI->_('Country'); ?>:</label>
                <?php
                $countries = array('' => $AppUI->_('(Select a Country)')) + w2PgetSysVal('GlobalCountries');
                echo arraySelect($countries, 'contact_country', 'size="1" class="text"', $row->contact_country ? $row->contact_country : 0);
                ?>
            </p>
            <p>
                <label><?php echo $AppUI->_('Work Phone'); ?>:</label>
                <input type="text" class="text" name="contact_phone" value="<?php echo $row->contact_phone; ?>" maxlength="30" size="25" />
            </p>
            <p>
                <label><?php echo $AppUI->_('Home Phone'); ?>:</label>
                <input type="text" class="text" name="contact_phone2" value="<?php echo $row->contact_phone2; ?>" maxlength="30" size="25" />
            </p>
            <p>
                <label><?php echo $AppUI->_('Fax'); ?>:</label>
                <input type="text" class="text" name="contact_fax" value="<?php echo $row->contact_fax; ?>" maxlength="30" size="25" />
            </p>
            <p>
                <label><?php echo $AppUI->_('Mobile Phone'); ?>:</label>
                <input type="text" class="text" name="contact_mobile" value="<?php echo $row->contact_mobile; ?>" maxlength="30" size="25" />
            </p>
            <p>
                <label><?php echo $AppUI->_('Email'); ?>:</label>
                <input type="text" class="text" name="contact_email" value="<?php echo $row->contact_email; ?>" maxlength="255" size="25" />
            </p>
            <p>
                <label><?php echo $AppUI->_('Email'); ?>2:</label>
                <input type="text" class="text" name="contact_email2" value="<?php echo $row->contact_email2; ?>" maxlength="255" size="25" />
            </p>
            <p>
                <label><?php echo $AppUI->_('Home Page'); ?>2:</label>
                <input type="text" class="text" name="contact_url" value="<?php echo $row->contact_url; ?>" maxlength="255" size="25" />
            </p>
            <p>
                <label>Jabber:</label>
                <input type="text" class="text" name="contact_jabber" value="<?php echo $row->contact_jabber; ?>" maxlength="255" size="25" />
            </p>
            <p>
                <label>ICQ:</label>
                <input type="text" class="text" name="contact_icq" value="<?php echo $row->contact_icq; ?>" maxlength="20" size="25" />
            </p>
            <p>
                <label>AOL:</label>
                <input type="text" class="text" name="contact_aol" value="<?php echo $row->contact_aol; ?>" maxlength="20" size="25" />
            </p>
            <p>
                <label>MSN:</label>
                <input type="text" class="text" name="contact_msn" value="<?php echo $row->contact_msn; ?>" maxlength="255" size="25" />
            </p>
            <p>
                <label>Yahoo:</label>
                <input type="text" class="text" name="contact_yahoo" value="<?php echo $row->contact_yahoo; ?>" maxlength="255" size="25" />
            </p>
            <p>
                <label><?php echo $AppUI->_('Birthday'); ?>:</label>
                <input type="text" class="text" name="contact_birthday" value="<?php echo substr($row->contact_birthday, 0, 10); ?>" maxlength="10" size="25" />(<?php echo $AppUI->_('yyyy-mm-dd'); ?>)
            </p>
            <?php
                                $custom_fields = new w2p_Core_CustomFields('contacts', 'addedit', $row->contact_id, "edit", 1);
                                $custom_fields->printHTML();
                            ?>
        </div>
        <div class="column right">
            <p>
                <label><?php echo $AppUI->_('Contact Notes'); ?></label>
                <textarea class="textarea" name="contact_notes" rows="20" cols="40"><?php echo $row->contact_notes; ?></textarea>
            </p>
        </div>
        <div class="w2p-form-actions justify-content-end">
            <input type="button" value="<?php echo $AppUI->_('submit'); ?>" class="button" onclick="submitIt()" />
        </div>
    </div>
</form>
<?php
echo $AppUI->getTheme()->styleRenderBoxBottom();