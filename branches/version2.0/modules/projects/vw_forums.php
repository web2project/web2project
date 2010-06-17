<?php /* $Id$ $URL$ */
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}

global $AppUI, $project_id;
// Forums mini-table in project view action

$forums = CProject::getForums($AppUI, $project_id);
?>

<table width="100%" border="0" cellpadding="2" cellspacing="1" class="tbl">
	<tr>
		<th nowrap="nowrap">&nbsp;</th>
		<th nowrap="nowrap" width="100%"><?php echo $AppUI->_('Forum Name'); ?></th>
		<th nowrap="nowrap"><?php echo $AppUI->_('Messages'); ?></th>
		<th nowrap="nowrap"><?php echo $AppUI->_('Last Post'); ?></th>
	</tr>
	<?php foreach ($forums as $forumId  => $forum_info) {?>
		<tr>
			<td nowrap="nowrap" align="center">
		<?php
			if ($forum_info["forum_owner"] == $AppUI->user_id) { ?>
				<a href="./index.php?m=forums&a=addedit&forum_id=<?php echo $forum_info['forum_id']; ?>"><img src="<?php echo w2PfindImage('icons/pencil.gif'); ?>" alt="expand forum" border="0" width=12 height=12></a>
		<?php } ?>
			</td>
			<td nowrap="nowrap"><a href="./index.php?m=forums&a=viewer&forum_id=<?php echo $forum_info["forum_id"]; ?>"><?php echo $forum_info['forum_name']; ?></a></td>
			<td nowrap="nowrap"><?php echo $forum_info['forum_message_count']; ?></td>
			<td nowrap="nowrap">
				<?php echo (intval($forum_info['forum_last_date']) > 0) ? $forum_info['forum_last_date'] : 'n/a'; ?>
			</td>
		</tr>
		<tr>
			<td></td>
			<td colspan="3"><?php echo $forum_info['forum_description']; ?></td>
		</tr>
	<?php } ?>
</table>