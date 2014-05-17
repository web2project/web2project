<?php

$form = new w2p_Output_HTML_FormHelper($AppUI);

?>
<form name="editFrm" action="?m=<?php echo $m; ?>" method="post" accept-charset="utf-8" class="addedit events">
    <input type="hidden" name="dosql" value="do_event_aed" />
    <input type="hidden" name="event_id" value="<?php echo $object->getId(); ?>" />
    <input type="hidden" name="event_assigned" value="" />
    <input type="hidden" name="datePicker" value="event" />
    <?php echo $form->addNonce(); ?>

    <div class="std addedit events">
        <div class="column left">
            <p>
                <?php $form->showLabel('Name'); ?>
                <?php $form->showField('event_name', $object->event_name, array('maxlength' => 255)); ?>
            </p>
            <p>
                <?php $form->showLabel('Type'); ?>
                <?php $form->showField('event_type', $object->event_type, array(), $types); ?>
            </p>
            <p>
                <?php $form->showLabel('Project'); ?>
                <?php $form->showField('event_project', $object->event_project, array(), $projects); ?>
            </p>
            <p>
                <?php $form->showLabel('Event Owner'); ?>
                <?php
                $owner = ($object->event_owner) ? $object->event_owner : $AppUI->user_id;
                $form->showField('event_owner', $owner, array(), $users); ?>
            </p>
            <p>
                <?php $form->showLabel('Private Entry'); ?>
                <input type="checkbox" value="1" name="event_private" id="event_private" <?php echo ($object->event_private ? 'checked="checked"' : ''); ?> />
            </p>
            <p>
                <?php $form->showLabel('Start Date'); ?>
                <input type="hidden" name="event_start_date" id="event_start_date" value="<?php echo $start_date ? $start_date->format(FMT_TIMESTAMP_DATE) : ''; ?>" />
                <input type="text" name="start_date" id="start_date" onchange="setDate_new('editFrm', 'start_date');" value="<?php echo $start_date ? $start_date->format($df) : ''; ?>" class="text" />
                <a href="javascript: void(0);" onclick="return showCalendar('start_date', '<?php echo $df ?>', 'editFrm', null, true, true)">
                    <img src="<?php echo w2PfindImage('calendar.gif'); ?>" alt="<?php echo $AppUI->_('Calendar'); ?>" />
                </a>
                <?php echo arraySelect($times, 'start_time', 'size="1" class="text"', $AppUI->formatTZAwareTime($object->event_start_date, '%H%M%S')); ?>
            </p>
            <p>
                <?php $form->showLabel('End Date'); ?>
                <input type="hidden" name="event_end_date" id="event_end_date" value="<?php echo $end_date ? $end_date->format(FMT_TIMESTAMP_DATE) : ''; ?>" />
                <input type="text" name="end_date" id="end_date" onchange="setDate_new('editFrm', 'end_date');" value="<?php echo $end_date ? $end_date->format($df) : ''; ?>" class="text" />
                <a href="javascript: void(0);" onclick="return showCalendar('end_date', '<?php echo $df ?>', 'editFrm', null, true, true)">
                    <img src="<?php echo w2PfindImage('calendar.gif'); ?>" alt="<?php echo $AppUI->_('Calendar'); ?>" />
                </a>
                <?php echo arraySelect($times, 'end_time', 'size="1" class="text"', $AppUI->formatTZAwareTime($object->event_end_date, '%H%M%S')); ?>
            </p>
            <p>
                <?php $form->showLabel('Recurs'); ?>
                <?php echo arraySelect($recurs, 'event_recurs', 'size="1" class="text"', $object->event_recurs, true); ?>
                <input type="text" class="text" name="event_times_recuring" value="<?php echo ((isset($object->event_times_recuring)) ? ($object->event_times_recuring) : '1'); ?>" maxlength="2" size="3" /> <?php echo $AppUI->_('times'); ?>
            </p>
        </div>
        <div class="column right">
            <p>
                <?php $form->showLabel('Description'); ?>
                <?php $form->showField('event_description', $object->event_description); ?>
            </p>
            <p>
                <?php $form->showLabel('Only on Working Days'); ?>
                <input type="checkbox" value="1" name="event_cwd" id="event_cwd" <?php echo ($object->event_cwd ? 'checked="checked"' : ''); ?> />
            </p>
            <p>
                <?php $form->showLabel('Mail Attendees'); ?>
                <input type="checkbox" name="mail_invited" id="mail_invited" checked="checked" />
            </p>
        </div>
    </div>
    <div class="std addedit events">
        <table class="well">
            <tr>
                <td align="right"><?php echo $AppUI->_('People'); ?>:</td>
                <td></td>
                <td align="left"><?php echo $AppUI->_('Invited to Event'); ?>:</td>
                <td></td>
            </tr>
            <tr>
                <td width="50%" colspan="2" align="right">
                    <?php echo arraySelect($users, 'resources', 'style="width:220px" size="10" class="text" multiple="multiple" ', null); ?>
                </td>
                <td width="50%" colspan="2" align="left">
                    <?php echo arraySelect($assigned, 'assigned', 'style="width:220px" size="10" class="text" multiple="multiple" ', null); ?>
                </td>
            </tr>
            <tr>
                <td width="50%" colspan="2" align="right">
                    <input type="button" class="button btn btn-primary" value="&gt;" onclick="addUser()" />
                </td>
                <td width="50%" colspan="2" align="left">
                    <input type="button" class="button btn btn-primary" value="&lt;" onclick="removeUser()" />
                </td>
            </tr>
            <tr>
                <td colspan="2" align="right">
                    <?php
                    $custom_fields = new w2p_Core_CustomFields('events', 'addedit', $object->event_id, 'edit');
                    $custom_fields->printHTML();
                    ?>
                </td>
            <tr>
                <td colspan="2">
                    <?php $form->showCancelButton(); ?>
                </td>
                <td align="right" colspan="2">
                    <?php $form->showSaveButton(); ?>
                </td>
            </tr>
        </table>
    </div>
</form>