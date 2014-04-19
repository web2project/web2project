<table class="std view events">
    <tr>
        <th colspan="2"><?php echo $event->event_name; ?></th>
    </tr>
    <tr>
        <td valign="top" width="50%">
            <strong><?php echo $AppUI->_('Details'); ?></strong>
            <table cellspacing="1" cellpadding="2" width="100%" class="well">
                <tr>
                    <td align="right" nowrap="nowrap"><?php echo $AppUI->_('Type'); ?>:</td>
                    <?php echo $htmlHelper->createCell('event_type', $AppUI->_($types[$event->event_type])); ?>
                </tr>
                <?php if($event->event_project) { ?>
                    <tr>
                        <td align="right" nowrap="nowrap"><?php echo $AppUI->_('Project'); ?>:</td>
                        <td class="hilite" style="background-color:#<?php echo $event->project_color_identifier; ?>">
                            <font color="<?php echo bestColor($event->project_color_identifier); ?>">
                                <a href='?m=projects&a=view&project_id=<?php echo $event->event_project ?>'><?php echo $event_project; ?></a>
                            </font>
                        </td>
                    </tr>
                <?php } ?>
                <tr>
                    <td align="right" nowrap="nowrap"><?php echo $AppUI->_('Starts'); ?>:</td>
                    <?php echo $htmlHelper->createCell('event_start_datetime', $event->event_start_date); ?>
                </tr>
                <tr>
                    <td align="right" nowrap="nowrap"><?php echo $AppUI->_('Ends'); ?>:</td>
                    <?php echo $htmlHelper->createCell('event_end_datetime', $event->event_end_date); ?>
                </tr>
                <tr>
                    <td align="right" nowrap="nowrap"><?php echo $AppUI->_('Recurs'); ?>:</td>
                    <td><?php echo $AppUI->_($recurs[$event->event_recurs]) . ($event->event_recurs ? ' (' . $event->event_times_recuring . '&nbsp;' . $AppUI->_('times') . ')' : ''); ?></td>
                </tr>
                <tr>
                    <td align="right" nowrap="nowrap"><?php echo $AppUI->_('Attendees'); ?>:</td>
                    <td>
                        <?php
                        if (is_array($assigned)) {
                            $start = false;
                            foreach ($assigned as $user) {
                                if ($start)
                                    echo '<br/>';
                                else
                                    $start = true;
                                echo $user;
                            }
                        }
                        ?>
                </tr>
            </table>
        </td>
        <td width="50%" valign="top">
            <strong><?php echo $AppUI->_('Description'); ?></strong>
            <table cellspacing="0" cellpadding="2" border="0" width="100%" class="well">
                <tr>
                    <?php echo $htmlHelper->createCell('event_description', $event->event_description); ?>
                </tr>
            </table>
            <?php
            $custom_fields = new w2p_Core_CustomFields($m, $a, $event->event_id, 'view');
            $custom_fields->printHTML();
            ?>
        </td>
    </tr>
</table>