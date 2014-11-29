<?php

$view = new w2p_Output_HTML_ViewHelper($AppUI);

?>
<div class="std addedit companies">
    <div class="column left">
        <p><?php $view->showLabel('Name'); ?>
            <?php $view->showField('company_name', $object->company_name); ?>
        </p>
        <p><?php $view->showLabel('Owner'); ?>
            <?php $view->showField('company_owner', $object->company_owner); ?>
        </p>
        <p><?php $view->showLabel('Email'); ?>
            <?php $view->showField('company_email', $object->company_email); ?>
        </p>
        <p><?php $view->showLabel('Phone'); ?>
            <?php $view->showField('company_phone1', $object->company_phone1); ?>
        </p>
        <p><?php $view->showLabel('Phone2'); ?>
            <?php $view->showField('company_phone2', $object->company_phone2); ?>
        </p>
        <p><?php $view->showLabel('Fax'); ?>
            <?php $view->showField('company_fax', $object->company_fax); ?>
        </p>
        <p><?php $view->showLabel('Address'); ?>
            <?php $view->showAddress('company', $object); ?>
        </p>
        </p>
        <p><?php $view->showLabel('URL'); ?>
            <?php $view->showField('company_primary_url', $object->company_primary_url); ?>
        </p>
        <p><?php $view->showLabel('Type'); ?>
            <?php $view->showField('company_type', $AppUI->_($types[$object->company_type])); ?>
        </p>
    </div>
    <div class="column right">
        <p><?php $view->showLabel('Description'); ?>
            <?php $view->showField('company_description', $object->company_description); ?>
        </p>
        <?php
        $custom_fields = new w2p_Core_CustomFields($m, $a, $object->company_id, 'view');
        $custom_fields->printHTML();
        ?>
    </div>
</div>