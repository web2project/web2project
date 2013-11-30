<?php

$form = new w2p_Output_HTML_FormHelper($AppUI);

?>
<form name="changeclient" action="?m=<?php echo $m; ?>" method="post" accept-charset="utf-8" class="form-horizontal addeidt companies">
	<input type="hidden" name="dosql" value="do_company_aed" />
	<input type="hidden" name="company_id" value="<?php echo $company_id; ?>" />

    <div class="std addedit companies">
        <div class="column left">
            <p>
                <label><?php echo $AppUI->_('Company Name'); ?>:</label>
                <?php echo $form->addField('company_name', $company->company_name, array('maxlength' => 255)); ?>
            </p>
            <p>
                <label><?php echo $AppUI->_('Email'); ?>:</label>
                <?php echo $form->addField('company_email', $company->company_email, array('maxlength' => 255)); ?>
            </p>
            <p>
                <label><?php echo $AppUI->_('Phone'); ?>:</label>
                <?php echo $form->addField('company_phone1', $company->company_phone1, array('maxlength' => 30)); ?>
            </p>
            <p>
                <label><?php echo $AppUI->_('Phone'); ?>2:</label>
                <?php echo $form->addField('company_phone2', $company->company_phone2, array('maxlength' => 50)); ?>
            </p>
            <p>
                <label><?php echo $AppUI->_('URL'); ?>2:</label>
                <?php echo $form->addField('company_primary_url', $company->company_primary_url, array('maxlength' => 255)); ?>
                <a href="javascript: void(0);" onclick="testURL('CompanyURLOne')">[<?php echo $AppUI->_('test'); ?>]</a>
            </p>
            <p>
                <label><?php echo $AppUI->_('Description'); ?>:</label>
                <?php echo $form->addField('company_description', $company->company_description); ?>
            </p>
            <p>
                <?php
                $custom_fields = new w2p_Core_CustomFields($m, $a, $company->company_id, "edit");
                $custom_fields->printHTML();
                ?>
            </p>
            <p>
                <?php echo $form->addCancelButton(); ?>
            </p>
        </div>
        <div class="column right">
            <p>
                <label><?php echo $AppUI->_('Address'); ?>1:</label>
                <?php echo $form->addField('company_address1', $company->company_address1, array('maxlength' => 255)); ?>
            </p>
            <p>
                <label><?php echo $AppUI->_('Address'); ?>2:</label>
                <?php echo $form->addField('company_address2', $company->company_address2, array('maxlength' => 255)); ?>
            </p>
            <p>
                <label><?php echo $AppUI->_('City'); ?>:</label>
                <?php echo $form->addField('company_city', $company->company_city, array('maxlength' => 50)); ?>
            </p>
            <p>
                <label><?php echo $AppUI->_('State'); ?>:</label>
                <?php echo $form->addField('company_state', $company->company_state, array('maxlength' => 50)); ?>
            </p>
            <p>
                <label><?php echo $AppUI->_('Zip'); ?>:</label>
                <?php echo $form->addField('company_zip', $company->company_zip, array('maxlength' => 15)); ?>
            </p>
            <p>
                <label><?php echo $AppUI->_('Country'); ?>:</label>
                <?php
                echo $form->addField('company_country', $company->company_country, array(), $countries);
                ?>
            </p>
            <p>
                <label><?php echo $AppUI->_('Fax'); ?>:</label>
                <?php echo $form->addField('company_fax', $company->company_fax, array('maxlength' => 30)); ?>
            </p>
            <p>
                <label><?php echo $AppUI->_('Company Owner'); ?>:</label>
                <?php
                $perms = &$AppUI->acl();
                $users = $perms->getPermittedUsers('companies');
                echo $form->addField('company_owner', $company->company_owner, array(), $users);
                ?>
            </p>
            <p>
                <label><?php echo $AppUI->_('Type'); ?>:</label>
                <?php
                echo $form->addField('company_type', $company->company_type, array(), $types);
                ?>
            </p>
            <p>
                <?php echo $form->addSaveButton(); ?>
            </p>
        </div>
    </div>
</form>