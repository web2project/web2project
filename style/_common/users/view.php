<?php

$htmlHelper = new w2p_Output_HTMLHelper($AppUI);
$htmlHelper->stageRowData((array) $user);
?>

<table class="std view admin">
    <tr>
        <th colspan="2"><?php echo $user->user_username; ?></th>
    </tr>
    <tr valign="top">
        <td width="50%">
            <strong><?php echo $AppUI->_('Details'); ?></strong>
            <table cellspacing="1" cellpadding="2" border="0" width="100%" class="well">
                <tr>
                    <td align="right" nowrap="nowrap"><?php echo $AppUI->_('User Type'); ?>:</td>
                    <?php echo $htmlHelper->createCell('user_type', $AppUI->_($utypes[$user->user_type])); ?>
                </tr>
                <tr>
                    <td align="right" nowrap="nowrap"><?php echo $AppUI->_('Real Name'); ?>:</td>
                    <?php echo $htmlHelper->createCell('contact_displayname', $user->contact_display_name); ?>
                </tr>
                <tr>
                    <td align="right" nowrap="nowrap"><?php echo $AppUI->_('Company'); ?>:</td>
                    <?php echo $htmlHelper->createCell('contact_company', $user->contact_company); ?>
                </tr>
                <tr>
                    <td align="right" nowrap="nowrap"><?php echo $AppUI->_('Department'); ?>:</td>
                    <?php echo $htmlHelper->createCell('contact_department', $user->contact_department); ?>
                </tr>
                <tr>
                    <td align="right" nowrap="nowrap"><?php echo $AppUI->_('Phone'); ?>:</td>
                    <?php echo $htmlHelper->createCell('contact_phone', $user->contact_phone); ?>
                </tr>
                <tr>
                    <td align="right" nowrap="nowrap"><?php echo $AppUI->_('Email'); ?>:</td>
                    <?php echo $htmlHelper->createCell('contact_email', $user->contact_email); ?>
                </tr>
                <tr valign="top">
                    <td align="right" nowrap="nowrap"><?php echo $AppUI->_('Address'); ?>:</td>
                    <td width="100%">
                        <?php echo $user->contact_address1; ?><br />
                        <?php echo ($user->contact_address2 == '') ? '' : $user->contact_address2.'<br />'; ?>
                        <?php echo $user->contact_city . ', ' . $user->contact_state . ' ' . $user->contact_zip; ?><br />
                        <?php echo isset($countries[$user->contact_country]) ? $countries[$user->contact_country] : $user->contact_country; ?>
                    </td>
                </tr>
                <tr>
                    <td align="right" nowrap="nowrap"><?php echo $AppUI->_('Birthday'); ?>:</td>
                    <?php echo $htmlHelper->createCell('_date', $user->contact_birthday); ?>
                </tr>
            </table>
        </td>
        <td width="50%">
            <strong><?php echo $AppUI->_('Contact Information'); ?></strong>
            <table cellspacing="1" cellpadding="2" border="0" width="100%" class="well">
                <?php
                $fields = $methods['fields'];
                foreach ($fields as $key => $field): ?>
                    <tr>
                        <td align="right" width="100" nowrap="nowrap"><?php echo $AppUI->_($methodLabels[$field]); ?>:</td>
                        <?php echo $htmlHelper->createCell('_'.substr($field, 0, strpos($field, '_')), $methods['values'][$key]); ?>
                    </tr>
                <?php endforeach; ?>
                <tr>
                    <td align="right" nowrap="nowrap"><?php echo $AppUI->_('Calendar Feed'); ?>:</td>
                    <td width="100%">
                        <?php if ($user->feed_token != '') {
                            $calendarFeed = W2P_BASE_URL.'/calendar.php?token='.$user->feed_token.'&amp;ext=.ics';
                            ?>
                            <a href="<?php echo $calendarFeed; ?>">calendar feed</a>
                        <?php } ?>
                        &nbsp;&nbsp;&nbsp;
                        <form name="regenerateToken" action="./index.php?m=users" method="post" accept-charset="utf-8">
                            <input type="hidden" name="user_id" value="<?php echo (int) $user->user_id; ?>" />
                            <input type="hidden" name="dosql" value="do_user_token" />
                            <input type="hidden" name="token" value="<?php echo $user->feed_token; ?>" />
                            <input type="submit" name="regenerate token" value="<?php echo $AppUI->_('regenerate feed url'); ?>" class="button btn btn-primary btn-mini" />
                        </form>
                    </td>
                </tr>
                <tr>
                    <td colspan="2"><strong><?php echo $AppUI->_('Signature'); ?>:</strong></td>
                </tr>
                <tr>
                    <td width="100%" colspan="2">
                        <?php echo w2p_textarea($user->user_signature); ?>&nbsp;
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>