<?php
$form = new w2p_Output_HTML_FormHelper($AppUI);
?>
<form name="editFrm" action="?m=<?php echo $m; ?>" method="post" accept-charset="utf-8" class="addedit departments">
    <input type="hidden" name="dosql" value="do_dept_aed" />
    <input type="hidden" name="dept_id" value="<?php echo $dept_id; ?>" />
    <input type="hidden" name="dept_company" value="<?php echo $company_id; ?>" />
    <?php echo $form->addNonce(); ?>

    <div class="std addedit departments">
        <div class="column left">
            <p>
                <?php $form->showLabel('Company Name'); ?>
                <?php echo $companyName; ?>
            </p>
            <p>
                <?php $form->showLabel('Name'); ?>
                <?php $form->showField('dept_name', $department->dept_name, array('maxlength' => 255)); ?>
            </p>
            <?php
            if (count($depts) > 0) {
                ?>
                <p>
                    <?php $form->showLabel('Parent'); ?>
                    <?php $form->showField('dept_parent', $department->dept_parent, array(), $depts); ?>
                </p>
            <?php
            } else {
                echo '<input type="hidden" name="dept_parent" value="0">';
            }
            ?>
            <p>
                <?php $form->showLabel('Email'); ?>
                <?php $form->showField('dept_email', $department->dept_email, array('maxlength' => 255)); ?>
            </p>
            <p>
                <?php $form->showLabel('Phone'); ?>
                <?php $form->showField('dept_phone', $department->dept_phone, array('maxlength' => 30)); ?>
            </p>
            <p>
                <?php $form->showLabel('URL'); ?>
                <?php $form->showField('dept_url', $department->dept_url, array('maxlength' => 255)); ?>
            </p>
            <p>
                <?php $form->showLabel('Description'); ?>
                <?php $form->showField('dept_desc', $department->dept_desc); ?>
            </p>
            <?php $form->showCancelButton(); ?>
        </div>
        <div class="column right">
            <p>
                <?php $form->showLabel('Address1'); ?>
                <?php $form->showField('dept_address1', $department->dept_address1, array('maxlength' => 255)); ?>
            </p>
            <p>
                <?php $form->showLabel('Address2'); ?>
                <?php $form->showField('dept_address2', $department->dept_address2, array('maxlength' => 255)); ?>
            </p>
            <p>
                <?php $form->showLabel('City'); ?>
                <?php $form->showField('dept_city', $department->dept_city, array('maxlength' => 50)); ?>
            </p>
            <p>
                <?php $form->showLabel('State'); ?>
                <?php $form->showField('dept_state', $department->dept_state, array('maxlength' => 50)); ?>
            </p>
            <p>
                <?php $form->showLabel('Zip'); ?>
                <?php $form->showField('dept_zip', $department->dept_zip, array('maxlength' => 15)); ?>
            </p>
            <p>
                <?php $form->showLabel('Country'); ?>
                <?php $form->showField('dept_country', $department->dept_country, array(), $countries); ?>
            </p>
            <p>
                <?php $form->showLabel('Fax'); ?>
                <?php $form->showField('dept_fax', $department->dept_fax, array('maxlength' => 30)); ?>
            </p>
            <p>
                <?php $form->showLabel('Owner'); ?>
                <?php
                $perms = &$AppUI->acl();
                $users = $perms->getPermittedUsers('departments');
                ?>
                <?php $form->showField('dept_owner', $department->dept_owner, array(), $users); ?>
            </p>
            <p>
                <?php $form->showLabel('Type'); ?>
                <?php $form->showField('dept_type', $department->dept_type, array(), $types); ?>
            </p>
            <?php $form->showSaveButton(); ?>
        </div>
    </div>
</form>