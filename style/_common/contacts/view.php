<?php

$view = new w2p_Output_HTML_ViewHelper($AppUI);

?>
<div class="std addedit contacts">
    <div class="column left well">
        <p><?php $view->showLabel('First Name'); ?>
            <?php $view->showField('contact_firstname', $object->contact_first_name); ?>
        </p>
        <p><?php $view->showLabel('Last Name'); ?>
            <?php $view->showField('contact_lastname', $object->contact_last_name); ?>
        </p>
        <p><?php $view->showLabel('Display Name'); ?>
            <?php $view->showField('contact_displayname', $object->contact_display_name); ?>
        </p>
        <p><?php $view->showLabel('Title'); ?>
            <?php $view->showField('contact_title', $object->contact_title); ?>
        </p>
        <p><?php $view->showLabel('Job Title'); ?>
            <?php $view->showField('contact_job', $object->contact_job); ?>
        </p>
        <p><?php $view->showLabel('Company'); ?>
            <?php $view->showField('contact_company', $object->contact_company); ?>
        </p>
        <p><?php $view->showLabel('Department'); ?>
            <?php $view->showField('contact_department', $object->contact_department); ?>
        </p>
        <p><?php $view->showLabel('Address'); ?>
            <?php $view->showAddress('contact', $object); ?>
        </p>
    </div>
    <div class="column right well">
        <p><?php $view->showLabel('Birthday'); ?>
            <?php $view->showField('contact_birthday', $object->contact_birthday); ?>
        </p>
        <p><?php $view->showLabel('Phone'); ?>
            <?php $view->showField('contact_phone', $object->contact_phone); ?>
        </p>
        <p><?php $view->showLabel('Email'); ?>
            <?php $view->showField('contact_email', $object->contact_email); ?>
        </p>
        <p><?php $view->showLabel('Description'); ?>
            <?php $view->showField('contact_notes', $object->contact_notes); ?>
        </p>
        <?php
        $custom_fields = new w2p_Core_CustomFields($m, $a, $object->contact_id, 'view');
        $custom_fields->printHTML();
        ?>
        <?php
        $fields = $methods['fields'];
        foreach ($fields as $key => $field): ?>
            <p><?php $view->showLabel($methodLabels[$field]); ?>
                <?php echo $methods['values'][$key]; ?>
            </p>
        <?php endforeach; ?>
        <p><?php $view->showLabel('Waiting Update'); ?>
            <input type="checkbox" value="1" name="contact_updateasked" disabled="disabled" <?php echo $object->contact_updatekey ? 'checked="checked"' : ''; ?> />
        </p>
        <p><?php $view->showLabel('Last Updated'); ?>
            <?php $view->showField('contact_lastupdate', $object->contact_lastupdate); ?>
        </p>
    </div>
</div>