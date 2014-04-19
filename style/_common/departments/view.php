<table class="std view departments">
    <tr>
        <th colspan="2"><?php echo $department->dept_name; ?></th>
    </tr>
    <tr valign="top">
        <td width="50%">
            <strong><?php echo $AppUI->_('Details'); ?></strong>
            <table cellspacing="1" cellpadding="2" border="0" width="100%" class="well">
                <tr>
                    <td align="right" nowrap="nowrap"><?php echo $AppUI->_('Company'); ?>:</td>
                    <?php echo $htmlHelper->createCell('dept_company', $department->dept_company); ?>
                </tr>
                <tr>
                    <td align="right" nowrap="nowrap"><?php echo $AppUI->_('Owner'); ?>:</td>
                    <?php echo $htmlHelper->createCell('dept_owner', $department->dept_owner); ?>
                </tr>
                <tr>
                    <td align="right" nowrap="nowrap"><?php echo $AppUI->_('Type'); ?>:</td>
                    <?php echo $htmlHelper->createCell('dept_type', $types[$department->dept_type]); ?>
                </tr>
                <tr>
                    <td align="right" nowrap="nowrap"><?php echo $AppUI->_('Email'); ?>:</td>
                    <?php echo $htmlHelper->createCell('dept_email', $department->dept_email); ?>
                </tr>
                <tr>
                    <td align="right" nowrap="nowrap"><?php echo $AppUI->_('Phone'); ?>:</td>
                    <?php echo $htmlHelper->createCell('dept_phone', $department->dept_phone); ?>
                </tr>
                <tr>
                    <td align="right" nowrap="nowrap"><?php echo $AppUI->_('Fax'); ?>:</td>
                    <?php echo $htmlHelper->createCell('dept_fax', $department->dept_fax); ?>
                </tr>
                <tr valign="top">
                    <td align="right" nowrap="nowrap"><?php echo $AppUI->_('Address'); ?>:</td>
                    <td>
                        <a href="http://maps.google.com/maps?q=<?php echo $department->dept_address1; ?>+<?php echo $department->dept_address2; ?>+<?php echo $department->dept_city; ?>+<?php echo $department->dept_state; ?>+<?php echo $department->dept_zip; ?>+<?php echo $department->dept_country; ?>" target="_blank">
                            <img src="<?php echo w2PfindImage('googlemaps.gif'); ?>" class="right" alt="Find It on Google" /></a>
                        <?php	echo $department->dept_address1 . (($department->dept_address2) ? '<br />' . $department->dept_address2 : '') . '<br />' . $department->dept_city . '&nbsp;&nbsp;' . $department->dept_state . '&nbsp;&nbsp;' . $department->dept_zip . (($department->dept_country) ? '<br />' . $countries[$department->dept_country] : '');?>
                    </td>
                </tr>
                <tr>
                    <td align="right" nowrap="nowrap"><?php echo $AppUI->_('URL'); ?>:</td>
                    <?php echo $htmlHelper->createCell('dept_url', $department->dept_url); ?>
                </tr>
            </table>
        </td>
        <td width="50%">
            <strong><?php echo $AppUI->_('Description'); ?></strong>
            <table cellspacing="1" cellpadding="2" border="0" width="100%" class="well">
                <tr>
                    <?php echo $htmlHelper->createCell('dept_desc', $department->dept_desc); ?>
                </tr>
            </table>
        </td>
    </tr>
</table>