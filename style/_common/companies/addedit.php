<?php

$form = new w2p_Output_HTML_FormHelper($AppUI);

?>
<form name="changeclient" action="?m=<?php echo $m; ?>" method="post" accept-charset="utf-8" class="form-horizontal addeidt companies">
	<input type="hidden" name="dosql" value="do_company_aed" />
	<input type="hidden" name="company_id" value="<?php echo $company_id; ?>" />
    <?php echo $form->addNonce(); ?>

    <div class="std addedit companies">
        <div class="column left">
            <p>
                <?php $form->showLabel('Name'); ?>
                <?php $form->showField('company_name', $company->company_name, array('maxlength' => 255)); ?>
            </p>
            <p>
                <?php $form->showLabel('Email'); ?>
                <?php $form->showField('company_email', $company->company_email, array('maxlength' => 255)); ?>
            </p>
            <p>
                <?php $form->showLabel('Phone1'); ?>
                <?php $form->showField('company_phone1', $company->company_phone1, array('maxlength' => 30)); ?>
            </p>
            <p>
                <?php $form->showLabel('Phone2'); ?>
                <?php $form->showField('company_phone2', $company->company_phone2, array('maxlength' => 50)); ?>
            </p>
            <p>
                <?php $form->showLabel('URL'); ?>
                <?php $form->showField('company_primary_url', $company->company_primary_url, array('maxlength' => 255)); ?>
            </p>
            <p>
                <?php $form->showLabel('Description'); ?>
                <?php $form->showField('company_description', $company->company_description); ?>
            </p>
            <?php
            $custom_fields = new w2p_Core_CustomFields($m, $a, $company->company_id, "edit");
            echo $custom_fields->getHTML();
            $form->showCancelButton();
            ?>
        </div>
        <div class="column right">
            <p>
                <?php $form->showLabel('Address1'); ?>
                <?php $form->showField('company_address1', $company->company_address1, array('maxlength' => 255)); ?>
            </p>
            <p>
                <?php $form->showLabel('Address2'); ?>
                <?php $form->showField('company_address2', $company->company_address2, array('maxlength' => 255)); ?>
            </p>
            <p>
                <?php $form->showLabel('City'); ?>
                <?php $form->showField('company_city', $company->company_city, array('maxlength' => 50)); ?>
            </p>
            <p>
                <?php $form->showLabel('State'); ?>
                <?php $form->showField('company_state', $company->company_state, array('maxlength' => 50)); ?>
            </p>
            <p>
                <?php $form->showLabel('Zip'); ?>
                <?php $form->showField('company_zip', $company->company_zip, array('maxlength' => 15)); ?>
            </p>
            <p>
                <?php $form->showLabel('Country'); ?>
                <?php $form->showField('company_country', $company->company_country, array(), $countries); ?>
            </p>
            <p>
                <?php $form->showLabel('Fax'); ?>
                <?php $form->showField('company_fax', $company->company_fax, array('maxlength' => 30)); ?>
            </p>
            <?php
            $perms = &$AppUI->acl();
            $users = $perms->getPermittedUsers('companies');
            ?>
            <p>
                <?php $form->showLabel('Owner'); ?>
                <?php $form->showField('company_owner', $company->company_owner, array(), $users); ?>
            </p>
            <p>
                <?php $form->showLabel('Type'); ?>
                <?php $form->showField('company_type', $company->company_type, array(), $types); ?>
            </p>
            <?php $form->showSaveButton(); ?>
        </div>
    </div>
</form>