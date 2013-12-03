<table class="std view companies">
    <tr>
        <th colspan="2"><?php echo $company->company_name; ?></th>
    </tr>
	<tr>
		<td valign="top" width="50%">
            <strong><?php echo $AppUI->_('Details'); ?></strong>
			<table cellspacing="1" cellpadding="2" width="100%" class="well">
				<tr>
					<td align="right" nowrap="nowrap"><?php echo $AppUI->_('Owner'); ?>:</td>
                    <?php echo $htmlHelper->createCell('contact_displayname', $company->contact_name); ?>
				</tr>
				<tr>
					<td align="right" nowrap="nowrap"><?php echo $AppUI->_('Email'); ?>:</td>
                    <?php echo $htmlHelper->createCell('company_email', $company->company_email); ?>
				</tr>
				<tr>
					<td align="right" nowrap="nowrap"><?php echo $AppUI->_('Phone'); ?>:</td>
                    <?php echo $htmlHelper->createCell('company_phone1', $company->company_phone1); ?>
				</tr>
				<tr>
					<td align="right" nowrap="nowrap"><?php echo $AppUI->_('Phone'); ?>2:</td>
                    <?php echo $htmlHelper->createCell('company_phone2', $company->company_phone2); ?>
				</tr>
				<tr>
					<td align="right" nowrap="nowrap"><?php echo $AppUI->_('Fax'); ?>:</td>
                    <?php echo $htmlHelper->createCell('company_fax', $company->company_fax); ?>
				</tr>
				<tr valign="top">
					<td align="right" nowrap="nowrap"><?php echo $AppUI->_('Address'); ?>:</td>
					<td>
					<a href="http://maps.google.com/maps?q=<?php echo $company->company_address1; ?>+<?php echo $company->company_address2; ?>+<?php echo $company->company_city; ?>+<?php echo $company->company_state; ?>+<?php echo $company->company_zip; ?>+<?php echo $company->company_country; ?>" target="_blank">
					<img align="right" border="0" src="<?php echo w2PfindImage('googlemaps.gif'); ?>" width="55" height="22" alt="Find It on Google" /></a>
					<?php
						echo $company->company_address1 . (($company->company_address2) ? '<br />' . $company->company_address2 : '') . (($company->company_city) ? '<br />' . $company->company_city : '') . (($company->company_state) ? '<br />' . $company->company_state : '') . (($company->company_zip) ? '<br />' . $company->company_zip : '') . (($company->company_country) ? '<br />' . $countries[$company->company_country] : '');?>
					</td>
				</tr>
				<tr>
					<td align="right" nowrap="nowrap"><?php echo $AppUI->_('URL'); ?>:</td>
                    <?php echo $htmlHelper->createCell('company_primary_url', $company->company_primary_url); ?>
				</tr>
				<tr>
					<td align="right" nowrap="nowrap"><?php echo $AppUI->_('Type'); ?>:</td>
                    <?php echo $htmlHelper->createCell('company_type', $AppUI->_($types[$company->company_type])); ?>
				</tr>
			</table>
		</td>
		<td valign="top" width="50%">
            <strong><?php echo $AppUI->_('Description'); ?></strong>
			<table cellspacing="0" cellpadding="2" border="0" width="100%" class="well">
				<tr>
                    <?php echo $htmlHelper->createCell('company_description', $company->company_description); ?>
				</tr>		
			</table>
			<?php
				$custom_fields = new w2p_Core_CustomFields($m, $a, $company->company_id, 'view');
				$custom_fields->printHTML();
			?>
		</td>
	</tr>
</table>