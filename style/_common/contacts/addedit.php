<?php

$form = new w2p_Output_HTML_FormHelper($AppUI);

?>
<form name="changecontact" action="?m=<?php echo $m; ?>" method="post" accept-charset="utf-8" class="contacts addedit">
    <input type="hidden" name="dosql" value="do_contact_aed" />
    <input type="hidden" name="contact_project" value="0" />
    <input type="hidden" name="contact_unique_update" value="<?php echo uniqid(''); ?>" />
    <input type="hidden" name="contact_id" value="<?php echo $object->getId(); ?>" />
    <input type="hidden" name="contact_owner" value="<?php echo $object->contact_owner ? $object->contact_owner : $AppUI->user_id; ?>" />
    <?php echo $form->addNonce(); ?>

    <div class="std addedit contacts">
        <div class="column left well">
            <p>
                <?php $form->showLabel('First Name'); ?>
                <?php $form->showField('contact_first_name', $object->contact_first_name, array('maxlength' => 50)); ?>
            </p>
            <p>
                <?php $form->showLabel('Last Name'); ?>
                <?php
                $options = array('maxlength' => 50);
                if ($object_id == 0) {
                    $options['onBlur'] = "orderByName('name')";
                }
                ?>
                <?php $form->showField('contact_last_name', $object->contact_last_name, $options); ?>
                <a href="javascript: void(0);" onclick="orderByName('name')">[<?php echo $AppUI->_('use in display'); ?>]</a>
            </p>
            <p>
                <?php $form->showLabel('Display Name'); ?>
                <?php $form->showField('contact_display_name', $object->contact_display_name, array('maxlength' => 50)); ?>
            </p>
            <p>
                <?php $form->showLabel('Title'); ?>
                <?php $form->showField('contact_title', $object->contact_title, array('maxlength' => 50)); ?>
            </p>
            <p>
                <?php $form->showLabel('Email'); ?>
                <?php $form->showField('contact_email', $object->contact_email, array('maxlength' => 60)); ?>
            </p>
            <p>
                <?php $form->showLabel('Phone'); ?>
                <?php $form->showField('contact_phone', $object->contact_phone, array('maxlength' => 50)); ?>
            </p>
            <p>
                <?php $form->showLabel('Company'); ?>
                <?php echo arraySelect($companies, 'contact_company', 'size="1" class="text company" onChange="companyChange()"', $object->contact_company); ?>
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
                <?php $form->showField('contact_job', $object->contact_job, array('maxlength' => 100)); ?>
            </p>
            <p>
                <?php $form->showLabel('Contact Notes'); ?>
                <?php $form->showField('contact_notes', $object->contact_notes); ?>
            </p>
            <?php
            $custom_fields = new w2p_Core_CustomFields($m, $a, $object->contact_id, "edit");
            echo $custom_fields->getHTML();
            $form->showCancelButton();
            ?>
        </div>
        <div class="column right well">
            <p>
                <?php $form->showLabel('Address1'); ?>
                <?php $form->showField('contact_address1', $object->contact_address1, array('maxlength' => 60)); ?>
            </p>
            <p>
                <?php $form->showLabel('Address2'); ?>
                <?php $form->showField('contact_address2', $object->contact_address2, array('maxlength' => 60)); ?>
            </p>
            <p>
                <?php $form->showLabel('City'); ?>
                <?php $form->showField('contact_city', $object->contact_city, array('maxlength' => 30)); ?>
            </p>
            <p>
                <?php $form->showLabel('State'); ?>
                <?php $form->showField('contact_state', $object->contact_state, array('maxlength' => 30)); ?>
            </p>
            <p>
                <?php $form->showLabel('Zip'); ?>
                <?php $form->showField('contact_zip', $object->contact_zip, array('maxlength' => 11)); ?>
            </p>
            <p>
                <?php $form->showLabel('Country'); ?>
                <?php $form->showField('contact_country', $object->contact_country, array(), $countries); ?>
            </p>
            <p>
                <?php $form->showLabel('Birthday'); ?>
                <?php $form->showField('contact_birthday', $object->contact_birthday, array('maxlength' => 10)); ?> (<?php echo $AppUI->_('yyyy-mm-dd'); ?>)
            </p>
            <p><strong><?php echo $AppUI->_('Contact Update Info'); ?></strong></p>
            <p>
                <?php $form->showLabel('Awaiting Update'); ?>
                <?php
                $options = array('onclick' => 'updateVerify()');
                if ($object->contact_updatekey) {
                    $options['checked'] = 'checked';
                }
                ?>
                <?php $form->showField('contact_updateask', 1, $options); ?>
            </p>
            <p>
                <?php $form->showLabel('Update Requested'); ?>
                <?php $last_ask = new w2p_Utilities_Date($object->contact_updateasked); ?>
                <?php
                echo $object->contact_updateasked ? $AppUI->formatTZAwareTime($object->contact_updateasked) : '&nbsp;';
                ?>
            </p>
            <p>
                <?php $form->showLabel('Last Updated'); ?>
                <?php $lastupdated = new w2p_Utilities_Date($object->contact_lastupdate);
                echo ($object->contact_lastupdate && !($object->contact_lastupdate == 0)) ? $AppUI->formatTZAwareTime($object->contact_lastupdate) : '&nbsp;';
                ?>
            </p>
            <p>
                <?php $form->showLabel('Private Entry'); ?>
                <?php
                $options = array();
                if ($object->contact_private) {
                    $options['checked'] = 'checked';
                }
                ?>
                <?php $form->showField('contact_private', $object->contact_private, $options); ?>
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