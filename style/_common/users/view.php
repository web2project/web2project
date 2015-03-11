<?php

$view = new w2p_Output_HTML_ViewHelper($AppUI);

?>
<div class="std addedit users">
    <div class="column left well">
        <p><?php $view->showLabel('Username'); ?>
            <?php $view->showField('user_username', $user->user_username); ?>
        </p>
        <p><?php $view->showLabel('Real Name'); ?>
            <?php $view->showField('contact_displayname', $user->contact_display_name); ?>
        </p>
        <p><?php $view->showLabel('User Type'); ?>
            <?php $view->showField('user_type', $utypes[$user->user_type]); ?>
        </p>
        <p><?php $view->showLabel('Company'); ?>
            <?php $view->showField('contact_company', $user->contact_company); ?>
        </p>
        <p><?php $view->showLabel('Department'); ?>
            <?php $view->showField('contact_department', $user->contact_department); ?>
        </p>
        <p><?php $view->showLabel('Phone'); ?>
            <?php $view->showField('contact_phone', $user->contact_phone); ?>
        </p>
        <p><?php $view->showLabel('Email'); ?>
            <?php $view->showField('contact_email', $user->contact_email); ?>
        </p>
        <p><?php $view->showLabel('Address'); ?>
            <?php $view->showAddress('contact', $user); ?>
        </p>
        <p><?php $view->showLabel('Birthday'); ?>
            <?php $view->showField('contact_birthday', $user->contact_birthday); ?>
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
            <?php if ($user->feed_token != '') {
                $calendarFeed = W2P_BASE_URL.'/calendar.php?token='.$user->feed_token.'&amp;ext=.ics';
                ?>
                <a href="<?php echo $calendarFeed; ?>">calendar feed</a>
            <?php } ?>
            <form name="regenerateToken" action="./index.php?m=users" method="post" accept-charset="utf-8">
                <input type="hidden" name="user_id" value="<?php echo (int) $user->user_id; ?>" />
                <input type="hidden" name="dosql" value="do_user_token" />
                <input type="hidden" name="token" value="<?php echo $user->feed_token; ?>" />
                <input type="submit" name="regenerate token" value="<?php echo $AppUI->_('regenerate feed url'); ?>" class="button btn btn-primary btn-mini" />
            </form>
        </p>
        <p><?php $view->showLabel('Signature'); ?>
            <?php $view->showField('user_signature', $user->user_signature); ?>
        </p>
    </div>
</div>