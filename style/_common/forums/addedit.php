<?php

$form = new w2p_Output_HTML_FormHelper($AppUI);

?>
<form name="changeforum" action="?m=<?php echo $m; ?>" method="post" accept-charset="utf-8" class="addedit forums">
    <input type="hidden" name="dosql" value="do_forum_aed" />
    <input type="hidden" name="del" value="0" />
    <input type="hidden" name="forum_unique_update" value="<?php echo uniqid(''); ?>" />
    <input type="hidden" name="forum_id" value="<?php echo $forum_id; ?>" />
    <?php echo $form->addNonce(); ?>

    <div class="std addedit departments">
        <div class="column left">
            <p>
                <?php $form->showLabel('Name'); ?>
                <?php $form->showField('forum_name', $forum->forum_name, array('maxlength' => 50)); ?>
            </p>
            <p>
                <?php $form->showLabel('Related Project'); ?>
                <?php $form->showField('forum_project', $forum->forum_project, array(), $projects); ?>
            </p>
            <p>
                <?php $form->showLabel('Owner'); ?>
                <?php $form->showField('forum_owner', $forum->forum_owner, array(), $users); ?>
            </p>
            <p>
                <?php $form->showLabel('Moderator'); ?>
                <?php echo arraySelect($users, 'forum_moderated', 'size="1" class="text"', $forum->forum_moderated); ?>
            </p>
            <?php if ($forum_id) { ?>
                <p>
                    <?php $form->showLabel('Message Count'); ?>
                    <?php echo (int) $forum->forum_message_count; ?>
                </p>
            <?php } ?>
            <p>
                <?php $form->showCancelButton(); ?>
            </p>
        </div>
        <div class="column right">
            <p>
                <?php $form->showLabel('Description'); ?>
                <?php $form->showField('forum_description', $forum->forum_description); ?>
            </p>
            <?php if ($forum_id) { ?>
                <p>
                    <?php $form->showLabel('Created On'); ?>
                    <?php echo $AppUI->formatTZAwareTime($forum->forum_create_date); ?>
                </p>
                <p>
                    <?php $form->showLabel('Last Post'); ?>
                    <?php echo $AppUI->formatTZAwareTime($forum->forum_last_date); ?>
                </p>
            <?php } ?>
            <?php $form->showSaveButton(); ?>
        </div>
    </div>
</form>