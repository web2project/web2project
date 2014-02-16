<?php

$view = new w2p_Output_HTML_ViewHelper($AppUI);

?>
<div class="std addedit companies">
    <div class="column left">
        <p><?php $view->showLabel('Name'); ?>
            <?php $view->showField('company_name', $company->company_name); ?>
        </p>
        <p><?php $view->showLabel('Owner'); ?>
            <?php $view->showField('company_owner', $company->company_owner); ?>
        </p>
        <p><?php $view->showLabel('Email'); ?>
            <?php $view->showField('company_email', $company->company_email); ?>
        </p>
        <p><?php $view->showLabel('Phone'); ?>
            <?php $view->showField('company_phone1', $company->company_phone1); ?>
        </p>
        <p><?php $view->showLabel('Phone2'); ?>
            <?php $view->showField('company_phone2', $company->company_phone2); ?>
        </p>
        <p><?php $view->showLabel('Fax'); ?>
            <?php $view->showField('company_fax', $company->company_fax); ?>
        </p>
        <p><?php $view->showLabel('Address'); ?>
            <div style="margin-left: 10em;">
                <a href="http://maps.google.com/maps?q=<?php echo $company->company_address1; ?>+<?php echo $company->company_address2; ?>+<?php echo $company->company_city; ?>+<?php echo $company->company_state; ?>+<?php echo $company->company_zip; ?>+<?php echo $company->company_country; ?>" target="_blank">
                    <img align="right" border="0" src="<?php echo w2PfindImage('googlemaps.gif'); ?>" width="55" height="22" alt="Find It on Google" />
                </a>
                <?php echo $company->company_address1 . (($company->company_address2) ? '<br />' . $company->company_address2 : '') . (($company->company_city) ? '<br />' . $company->company_city : '') . (($company->company_state) ? '<br />' . $company->company_state : '') . (($company->company_zip) ? '<br />' . $company->company_zip : '') . (($company->company_country) ? '<br />' . $countries[$company->company_country] : '');?>
            </div>
        </p>
        <p><?php $view->showLabel('URL'); ?>
            <?php $view->showField('company_primary_url', $company->company_primary_url); ?>
        </p>
        <p><?php $view->showLabel('Type'); ?>
            <?php $view->showField('company_type', $AppUI->_($types[$company->company_type])); ?>
        </p>
    </div>
    <div class="column right">
        <p><?php $view->showLabel('Description'); ?>
            <?php $view->showField('company_description', $company->company_description); ?>
        </p>
        <p>
            <?php
            $custom_fields = new w2p_Core_CustomFields($m, $a, $company->company_id, 'view');
            $custom_fields->printHTML();
            ?>
        </p>
    </div>
</div>