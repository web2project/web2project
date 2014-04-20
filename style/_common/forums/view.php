<?php

$view = new w2p_Output_HTML_ViewHelper($AppUI);

?>
<div class="std addedit forums">
    <div class="column left">
        <p><?php $view->showLabel('Related Project'); ?>
            <?php $view->showField('forum_project', $forum->forum_project); ?>
        </p>
        <p><?php $view->showLabel('Owner'); ?>
            <?php $view->showField('forum_owner', $forum->forum_owner); ?>
        </p>
        <p><?php $view->showLabel('Created On'); ?>
            <?php $view->showField('forum_create_date', $forum->forum_create_date); ?>
        </p>
    </div>
    <div class="column right">
        <p><?php $view->showLabel('Description'); ?>
            <?php $view->showField('forum_description', $forum->forum_description); ?>
        </p>
    </div>
</div>