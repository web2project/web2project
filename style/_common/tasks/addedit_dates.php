<form name="datesFrm" action="?m=tasks&a=addedit&task_project=<?php echo $task_project; ?>" method="post" accept-charset="utf-8">
    <input name="dosql" type="hidden" value="do_task_aed" />
    <input name="task_id" type="hidden" value="<?php echo $object->getId(); ?>" />
    <input type="hidden" name="datePicker" value="task" />

    <div class="std addedit tasks-dates">
        <div class="column left">
            <?php if ($can_edit_time_information) { ?>
                <p>
                    <?php $form->showLabel('Start Date'); ?>
                    <input type='hidden' id='task_start_date' name='task_start_date' value='<?php echo $start_date ? $start_date->format(FMT_TIMESTAMP_DATE) : ''; ?>' />
                    <input type='text' onchange="setDate_new('datesFrm', 'start_date');" class='text' style='width:120px;' id='start_date' name='start_date' value='<?php echo $start_date ? $start_date->format($df) : ''; ?>' />
                    <a onclick="return showCalendar('start_date', '<?php echo $df ?>', 'datesFrm', null, true, true)" href="javascript: void(0);">
                        <img src="<?php echo w2PfindImage('calendar.gif'); ?>" alt="<?php echo $AppUI->_('Calendar'); ?>" />
                    </a>
                    <?php
                    echo arraySelect($hours, 'start_hour', 'size="1" onchange="setAMPM(this)" class="text"', $start_date ? $start_date->getHour() : $start);
                    echo arraySelect($minutes, 'start_minute', 'size="1" class="text"', $start_date ? $start_date->getMinute() : '00');
                    if (stristr($AppUI->getPref('TIMEFORMAT'), '%p')) {
                        echo '<input type="text" name="start_hour_ampm" id="start_hour_ampm" value="' . ($start_date ? $start_date->getAMPM() : ($start > 11 ? 'pm' : 'am')) . '" disabled="disabled" class="text" size="2" />';
                    }

                    ?>
                </p>
                <p>
                    <?php $form->showLabel('Finish Date'); ?>
                    <input type='hidden' id='task_end_date' name='task_end_date' value='<?php echo $end_date ? $end_date->format(FMT_TIMESTAMP_DATE) : ''; ?>' />
                    <input type='text' onchange="setDate_new('datesFrm', 'end_date');" class='text' style='width:120px;' id='end_date' name='end_date' value='<?php echo $end_date ? $end_date->format($df) : ''; ?>' />
                    <a onclick="return showCalendar('end_date', '<?php echo $df ?>', 'datesFrm', null, true, true)" href="javascript: void(0);">
                        <img src="<?php echo w2PfindImage('calendar.gif'); ?>" alt="<?php echo $AppUI->_('Calendar'); ?>" />
                    </a>
                    <?php
                    echo arraySelect($hours, 'end_hour', 'size="1" onchange="setAMPM(this)" class="text"', $end_date ? $end_date->getHour() : $end);
                    echo arraySelect($minutes, 'end_minute', 'size="1" class="text"', $end_date ? $end_date->getMinute() : '00');
                    if (stristr($AppUI->getPref('TIMEFORMAT'), '%p')) {
                        echo '<input type="text" name="end_hour_ampm" id="end_hour_ampm" value="' . ($end_date ? $end_date->getAMPM() : ($end > 11 ? 'pm' : 'am')) . '" disabled="disabled" class="text" size="2" />';
                    }
                    ?>
                </p>
                <p>
                    <?php $form->showLabel('Calculate'); ?>
                    <input type="button" value="<?php echo $AppUI->_('Duration'); ?>" onclick="xajax_calcDuration(document.datesFrm.task_start_date.value,document.datesFrm.start_hour.value,document.datesFrm.start_minute.value,document.datesFrm.task_end_date.value,document.datesFrm.end_hour.value,document.datesFrm.end_minute.value,document.datesFrm.task_duration_type.value);" class="button btn btn-primary btn-mini" />
                    <input type="button" value="<?php echo $AppUI->_('Finish Date'); ?>" onclick="xajax_calcFinish(document.datesFrm.task_start_date.value,document.datesFrm.start_hour.value,document.datesFrm.start_minute.value,document.datesFrm.task_duration_type.value,document.datesFrm.task_duration.value)" class="button btn btn-primary btn-mini" />
                </p>
            <?php } ?>
        </div>
        <div class="column right">
            <p>
                <?php $form->showLabel('Expected Duration'); ?>
                <input type="text" class="text" name="task_duration" id="task_duration" maxlength="8" size="6" value="<?php echo $object->task_duration; ?>" />
                <?php
                echo arraySelect($durnTypes, 'task_duration_type', 'class="text"', $object->task_duration_type, true);
                ?>
            </p>
            <p>
                <?php $form->showLabel('Daily Working Hours'); ?>
                <?php echo $w2Pconfig['daily_working_hours']; ?>
            </p>
            <p>
                <?php $form->showLabel('Working Days'); ?>
                <?php echo $cwd_hr; ?>
            </p>
        </div>
    </div>
</form>