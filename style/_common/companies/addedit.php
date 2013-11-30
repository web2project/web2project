<?php

$form = new w2p_Output_HTML_FormHelper($AppUI);

?>
<form name="changeclient" action="?m=<?php echo $m; ?>" method="post" accept-charset="utf-8" class="form-horizontal addeidt companies">
	<input type="hidden" name="dosql" value="do_company_aed" />
	<input type="hidden" name="company_id" value="<?php echo $company_id; ?>" />

    <div class="std addedit companies">
        <div class="column left">
            <?php
            echo $form->addRow('company_name', $company->company_name, array('maxlength' => 255));
            echo $form->addRow('company_email', $company->company_email, array('maxlength' => 255));
            echo $form->addRow('company_phone1', $company->company_phone1, array('maxlength' => 30));
            echo $form->addRow('company_phone2', $company->company_phone2, array('maxlength' => 50));
            echo $form->addRow('company_primary_url', $company->company_primary_url, array('maxlength' => 255));
            echo $form->addRow('company_description', $company->company_description);
            $custom_fields = new w2p_Core_CustomFields($m, $a, $company->company_id, "edit");
            echo '<p>' . $custom_fields->getHTML() . '</p>';
            echo '<p>' . $form->addCancelButton() . '</p>';
            ?>
        </div>
        <div class="column right">
            <?php
            echo $form->addRow('company_address1', $company->company_address1, array('maxlength' => 255));
            echo $form->addRow('company_address2', $company->company_address2, array('maxlength' => 255));
            echo $form->addRow('company_city', $company->company_city, array('maxlength' => 50));
            echo $form->addRow('company_state', $company->company_state, array('maxlength' => 50));
            echo $form->addRow('company_zip', $company->company_zip, array('maxlength' => 15));
            echo $form->addRow('company_country', $company->company_country, array(), $countries);
            echo $form->addRow('company_fax', $company->company_fax, array('maxlength' => 30));

            $perms = &$AppUI->acl();
            $users = $perms->getPermittedUsers('companies');
            echo $form->addRow('company_owner', $company->company_owner, array(), $users);
            echo $form->addRow('company_type', $company->company_type, array(), $types);
            echo '<p>' . $form->addSaveButton() . '</p>';
            ?>
        </div>
    </div>
</form>