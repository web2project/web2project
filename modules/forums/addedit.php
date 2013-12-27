<?php
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}
// @todo    convert to template
$forum_id = (int) w2PgetParam($_GET, 'forum_id', 0);



$forum = new CForum();
$forum->forum_id = $forum_id;

$obj = $forum;
$canAddEdit = $obj->canAddEdit();
$canAuthor = $obj->canCreate();
$canEdit = $obj->canEdit();
if (!$canAddEdit) {
	$AppUI->redirect(ACCESS_DENIED);
}

$obj = $AppUI->restoreObject();
if ($obj) {
    $forum = $obj;
    $forum_id = $forum->forum_id;
} else {
    $forum->load($forum_id);
}
if (!$forum && $forum_id > 0) {
    $AppUI->setMsg('Forum');
    $AppUI->setMsg('invalidID', UI_MSG_ERROR, true);
    $AppUI->redirect();
}

$status = isset($forum->forum_status) ? $forum->forum_status : -1;

$prj = new CProject();
if ($forum_id) {
    $projects = $prj->getAllowedProjects($AppUI->user_id, false);
} else {
    $projects = $prj->getAllowedProjects($AppUI->user_id, true);
}
foreach ($projects as $project_id => $project_info) {
	$projects[$project_id] = $project_info['project_name'];
}
$projects = arrayMerge(array(0 => $AppUI->_('All Projects')), $projects);

// check permissions for this record
$perms = &$AppUI->acl();
$users = $perms->getPermittedUsers('forums');


// setup the title block
$ttl = $forum_id > 0 ? 'Edit Forum' : 'Add Forum';
$titleBlock = new w2p_Theme_TitleBlock($ttl, 'icon.png', $m, $m . '.' . $a);
$titleBlock->addCrumb('?m=forums', 'forums list');
if ($forum_id) {
    $titleBlock->addCrumb('?m=forums&a=viewer&forum_id=' . $forum_id, 'view this forum');
}
$titleBlock->show();
?>
<script language="javascript" type="text/javascript">
function submitIt(){
	var form = document.changeforum;
	if(form.forum_name.value.search(/^\s*$/) >= 0 ) {
		alert("<?php echo $AppUI->_('forumName', UI_OUTPUT_JS); ?>");
		form.forum_name.focus();
	} else if(form.forum_owner.value < 1) {
		alert("<?php echo $AppUI->_('forumSelectOwner', UI_OUTPUT_JS); ?>");
		form.forum_owner.focus();
	} else {
		form.submit();
	}
}
</script>
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
                <label><?php echo $AppUI->_('Forum Name'); ?>:</label>
                <input type="text" class="text" size="25" name="forum_name" value="<?php echo $forum->forum_name; ?>" maxlength="50" style="width:200px;" />
            </p>
            <p>
                <label><?php echo $AppUI->_('Related Project'); ?>:</label>
                <?php echo arraySelect($projects, 'forum_project', 'size="1" class="text"', $forum->forum_project); ?>
            </p>
            <p>
                <label><?php echo $AppUI->_('Owner'); ?>:</label>
                <?php echo arraySelect($users, 'forum_owner', 'size="1" class="text"', $forum->forum_owner ? $forum->forum_owner : $AppUI->user_id); ?>
            </p>
            <p>
                <label><?php echo $AppUI->_('Moderator'); ?>:</label>
                <?php echo arraySelect($users, 'forum_moderated', 'size="1" class="text"', $forum->forum_moderated); ?>
            </p>
            <?php if ($forum_id) { ?>
                <p>
                    <label><?php echo $AppUI->_('Message Count'); ?>:</label>
                    <?php echo $forum->forum_message_count; ?>
                </p>
            <?php } ?>
            <p>
                <input type="button" value="<?php echo $AppUI->_('back'); ?>" class="button" onclick="javascript:window.location='./index.php?m=forums';" />
            </p>
        </div>
        <div class="column right">
            <p>
                <label><?php echo $AppUI->_('Description'); ?>:</label>
                <textarea class="textarea" cols="50" rows="7" name="forum_description"><?php echo $forum->forum_description; ?></textarea>
            </p>
            <?php if ($forum_id) { ?>
                <p>
                    <label><?php echo $AppUI->_('Created On'); ?>:</label>
                    <?php echo $AppUI->formatTZAwareTime($forum->forum_create_date); ?>
                </p>
                <p>
                    <label><?php echo $AppUI->_('Last Post'); ?>:</label>
                    <?php echo $AppUI->formatTZAwareTime($forum->forum_last_date); ?>
                </p>
            <?php } ?>
            <p><input type="button" value="save" class="save button btn btn-primary" onclick="submitIt()" /></p>
        </div>
    </div>
</form>