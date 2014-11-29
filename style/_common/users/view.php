<?php

$view = new w2p_Output_HTML_ViewHelper($AppUI);

?>
<div class="std addedit users">
    <div class="column left well">
        <p><?php $view->showLabel('Username'); ?>
            <?php $view->showField('user_username', $object->user_username); ?>
        </p>
        <p><?php $view->showLabel('Real Name'); ?>
            <?php $view->showField('contact_displayname', $object->contact_display_name); ?>
        </p>
        <p><?php $view->showLabel('User Type'); ?>
            <?php $view->showField('user_type', $utypes[$object->user_type]); ?>
        </p>
        <p><?php $view->showLabel('Company'); ?>
            <?php $view->showField('contact_company', $object->contact_company); ?>
        </p>
        <p><?php $view->showLabel('Department'); ?>
            <?php $view->showField('contact_department', $object->contact_department); ?>
        </p>
        <p><?php $view->showLabel('Phone'); ?>
            <?php $view->showField('contact_phone', $object->contact_phone); ?>
        </p>
        <p><?php $view->showLabel('Email'); ?>
            <?php $view->showField('contact_email', $object->contact_email); ?>
        </p>
        <p><?php $view->showLabel('Address'); ?>
            <?php $view->showAddress('contact', $object); ?>
        </p>
        <p><?php $view->showLabel('Birthday'); ?>
            <?php $view->showField('contact_birthday', $object->contact_birthday); ?>
        </p>
    </div>
    <div class="column right well">
        <?php
        $fields = $methods['fields'];
        foreach ($fields as $key => $field): ?>
            <p>
                <?php $view->showLabel($methodLabels[$field]); ?>
                <?php echo $methods['values'][$key]; ?>
            </p>
        <?php endforeach; ?>
        <p><?php $view->showLabel('Calendar Feed'); ?>
            <?php if ($object->feed_token != '') {
                $calendarFeed = W2P_BASE_URL.'/calendar.php?token='.$object->feed_token.'&amp;ext=.ics';
                ?>
                <form name="regenerateToken" action="./index.php?m=users" method="post" accept-charset="utf-8">
                    <input type="hidden" name="user_id" value="<?php echo $object_id; ?>" />
                    <input type="hidden" name="dosql" value="do_user_token" />
                    <input type="hidden" name="token" value="<?php echo $object->feed_token; ?>" />
                    <input type="submit" name="regenerate token" value="<?php echo $AppUI->_('regenerate feed url'); ?>" class="button btn btn-primary btn-mini" />
                </form>
                <a href="<?php echo $calendarFeed; ?>">calendar feed</a>
            <?php } ?>
        </p>
        <p><?php $view->showLabel('Signature'); ?>
            <?php $view->showField('user_signature', $object->user_signature); ?>
        </p>
    </div>
</div>