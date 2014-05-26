<?php

$view = new w2p_Output_HTML_ViewHelper($AppUI);

?>
<div class="std addedit events">
    <div class="column left">
        <p><?php $view->showLabel('Name'); ?>
            <?php $view->showField('company_name', $event->event_name); ?>
        </p>
        <p><?php $view->showLabel('Type'); ?>
            <?php $view->showField('event_type', $types[$event->event_type]); ?>
        </p>
        <p><?php $view->showLabel('Project'); ?>
            <?php $view->showField('event_project', $event->event_project); ?>
        </p>
        <p><?php $view->showLabel('Starts'); ?>
            <?php $view->showField('event_start_datetime', $event->event_start_date); ?>
        </p>
        <p><?php $view->showLabel('Ends'); ?>
            <?php $view->showField('event_end_datetime', $event->event_end_date); ?>
        </p>
        <p><?php $view->showLabel('Recurs'); ?>
            <?php $view->showField('event_recurs', $recurs[$event->event_recurs]); ?>
        </p>
        <p><?php $view->showLabel('Attendees'); ?>
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
            } ?>
        </p>
    </div>
    <div class="column right">
        <p><?php $view->showLabel('Description'); ?>
            <?php $view->showField('event_description', $event->event_description); ?>
        </p>
        <?php
        $custom_fields = new w2p_Core_CustomFields($m, $a, $event->event_id, 'view');
        $custom_fields->printHTML();
        ?>
    </div>
</div>