<?php

$form = new w2p_Output_HTML_FormHelper($AppUI);

?>
<form name="editFrm" action="?m=<?php echo $m; ?>" method="post" accept-charset="utf-8" class="admin addedit">
    <input type="hidden" name="user_id" value="<?php echo $object->getId(); ?>" />
    <input type="hidden" name="contact_id" value="<?php echo (int) $object->contact_id; ?>" />
    <input type="hidden" name="dosql" value="do_user_aed" />
    <input type="hidden" name="username_min_len" value="<?php echo w2PgetConfig('username_min_len'); ?>)" />
    <input type="hidden" name="password_min_len" value="<?php echo w2PgetConfig('password_min_len'); ?>)" />
    <?php echo $form->addNonce(); ?>

    <div class="std addedit users">
        <div class="column left">
            <p>
                <?php $form->showLabel('Login Name'); ?>
                <?php
                if ($object->user_username) {
                    echo '<input type="hidden" class="text" name="user_username" value="' . $object->user_username . '" />';
                    echo '<strong>' . $object->user_username . '</strong>';
                } else {
                    echo '<input type="text" class="text" name="user_username" value="' . $object->user_username . '" maxlength="255" size="40" />';
                }
                ?>
            </p>
            <?php if (!$object_id) { ?>
                <p>
                    <?php $form->showLabel('Password'); ?>
                    <input type="password" class="text" name="user_password" value="<?php echo $object->user_password; ?>" maxlength="32" size="32" onKeyUp="checkPassword(this.value);" />
                </p>
                <p>
                    <?php $form->showLabel('Confirm Password'); ?>
                    <input type="password" class="text" name="password_check" value="<?php echo $object->user_password; ?>" maxlength="32" size="32" />
                </p>
            <?php } ?>
            <p>
                <?php $form->showLabel('Name'); ?>
                <?php $form->showField('contact_first_name', $object->contact_first_name, array('maxlength' => 50)); ?>
                <?php $form->showField('contact_last_name', $object->contact_last_name, array('maxlength' => 50)); ?>
            </p>
            <p>
                <?php $form->showLabel('Company'); ?>
                <?php
                echo arraySelect($companies, 'contact_company', 'class=text size=1', $object->contact_company);
                ?>
            </p>
            <p>
                <?php $form->showLabel('Department'); ?>
                <input type="hidden" name="contact_department" value="<?php echo $object->contact_department; ?>" />
                <input type="text" class="text" name="dept_name" value="<?php echo $object->dept_name; ?>" size="40" disabled="disabled" />
                <input type="button" class="button btn btn-primary btn-mini" value="<?php echo $AppUI->_('select dept'); ?>..." onclick="popDept()" />
            </p>
            <?php $form->showCancelButton(); ?>
        </div>
        <div class="column right">
            <?php if ($canEdit && !$object_id) { ?>
                <p>
                    <?php $form->showLabel('User Role'); ?>
                    <?php echo arraySelect($roles_arr, 'user_role', 'size="1" class="text"', '', true); ?>
                </p>
            <?php } ?>
            <?php if (!$object_id) { ?>
                <p>
                    <?php $form->showLabel('Password Strength'); ?>
                <div id="password-strength" class="text">
                    <div id="progressBar"></div>
                </div>
                </p>
            <?php } ?>
            <p>
                <?php $form->showLabel('Email'); ?>
                <?php $form->showField('contact_email', $object->contact_email, array('maxlength' => 255)); ?>
            </p>
            <p>
                <?php $form->showLabel('Email Signature'); ?>
                <?php $form->showField('user_signature', $object->user_signature); ?>
            </p>
            <p>
                <?php $form->showLabel('Inform new user of account details?'); ?>
                <input type="checkbox" value="1" name="send_user_mail" id="send_user_mail" />
            </p>
            <?php $form->showSaveButton(); ?>
        </div>
    </div>
</form>
