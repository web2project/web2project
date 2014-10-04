<?php

$view = new w2p_Output_HTML_ViewHelper($AppUI);

?>
<div class="std addedit contacts">
    <div class="column left well">
        <p><?php $view->showLabel('First Name'); ?>
            <?php $view->showField('contact_firstname', $contact->contact_first_name); ?>
        </p>
        <p><?php $view->showLabel('Last Name'); ?>
            <?php $view->showField('contact_lastname', $contact->contact_last_name); ?>
        </p>
        <p><?php $view->showLabel('Display Name'); ?>
            <?php $view->showField('contact_displayname', $contact->contact_display_name); ?>
        </p>
        <p><?php $view->showLabel('Title'); ?>
            <?php $view->showField('contact_title', $contact->contact_title); ?>
        </p>
        <p><?php $view->showLabel('Job Title'); ?>
            <?php $view->showField('contact_job', $contact->contact_job); ?>
        </p>
        <p><?php $view->showLabel('Company'); ?>
            <?php $view->showField('contact_company', $contact->contact_company); ?>
        </p>
        <p><?php $view->showLabel('Department'); ?>
            <?php $view->showField('contact_department', $contact->contact_department); ?>
        </p>
        <p><?php $view->showLabel('Address'); ?>
            <?php $view->showAddress('contact', $contact); ?>
        </p>
    </div>
    <div class="column right well">
        <p><?php $view->showLabel('Birthday'); ?>
            <?php $view->showField('contact_birthday', $contact->contact_birthday); ?>
        </p>
        <p><?php $view->showLabel('Phone'); ?>
            <?php $view->showField('contact_phone', $contact->contact_phone); ?>
        </p>
        <p><?php $view->showLabel('Email'); ?>
            <?php $view->showField('contact_email', $contact->contact_email); ?>
        </p>
        <p><?php $view->showLabel('Description'); ?>
            <?php $view->showField('contact_notes', $contact->contact_notes); ?>
        </p>
        <?php
        $custom_fields = new w2p_Core_CustomFields($m, $a, $contact->contact_id, 'view');
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
            <input type="checkbox" value="1" name="contact_updateasked" disabled="disabled" <?php echo $contact->contact_updatekey ? 'checked="checked"' : ''; ?> />
        </p>
        <p><?php $view->showLabel('Last Updated'); ?>
            <?php $view->showField('contact_lastupdate', $contact->contact_lastupdate); ?>
        </p>
    </div>
</div>