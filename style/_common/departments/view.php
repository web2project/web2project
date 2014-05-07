<?php

$view = new w2p_Output_HTML_ViewHelper($AppUI);

?>
<div class="std addedit departments">
    <div class="column left">
        <p><?php $view->showLabel('Name'); ?>
            <?php $view->showField('dept_name', $department->dept_name); ?>
        </p>
        <p><?php $view->showLabel('Company'); ?>
            <?php $view->showField('dept_company', $department->dept_company); ?>
        </p>
        <p><?php $view->showLabel('Owner'); ?>
            <?php $view->showField('dept_owner', $department->dept_owner); ?>
        </p>
        <p><?php $view->showLabel('Type'); ?>
            <?php $view->showField('dept_type', $types[$department->dept_type]); ?>
        </p>
        <p><?php $view->showLabel('Email'); ?>
            <?php $view->showField('dept_email', $department->dept_email); ?>
        </p>
        <p><?php $view->showLabel('Phone'); ?>
            <?php $view->showField('dept_phone', $department->dept_phone); ?>
        </p>
        <p><?php $view->showLabel('Fax'); ?>
            <?php $view->showField('dept_fax', $department->dept_fax); ?>
        </p>
        <p><?php $view->showLabel('Address'); ?>
            <?php $view->showAddress('dept', $department); ?>
        </p>
        <p><?php $view->showLabel('URL'); ?>
            <?php $view->showField('dept_url', $department->dept_url); ?>
        </p>
    </div>
    <div class="column right">
        <p><?php $view->showLabel('Description'); ?>
            <?php $view->showField('dept_desc', $department->dept_desc); ?>
        </p>
        <?php
        $custom_fields = new w2p_Core_CustomFields($m, $a, $department->dept_id, 'view');
        $custom_fields->printHTML();
        ?>
    </div>
</div>