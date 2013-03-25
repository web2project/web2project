<?php
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}

global $first_time, $last_time, $company_id;

// Get the messages posted in topics/forums watched (and viewable) by the currently logged in user
$msgs = CForum::getWatchedMessages($first_time, $last_time, $AppUI->user_id, $company_id);

echo '<table cellspacing="2" cellpadding="4" border="0" width="100%" class="tbl list">';
if (count($msgs) > 0) {
	echo '<th>' . $AppUI->_('Message title') . '</th>';
	echo '<th>' . $AppUI->_('Message author') . '</th>';
	echo '<th>' . $AppUI->_('Forum name') . '</th>';
	echo '<th>' . $AppUI->_('Project name') . '</th>';
	foreach ($msgs as $msg) {
		echo '<tr><td>' . ($msg['message_parent'] == -1 ? '<strong>' : '') . '<a href="?' . CForum_Message::getHRef($msg['message_forum'],$msg['message_id']) . '" title="' . $msg['message_title'] . '">' . $msg['message_title'] . '</a>' . ($msg['message_parent'] == -1 ? '</strong>' : '') . '</td>';
		echo '<td><a href="?' . CUser::getHRef($msg['message_author']) . '" title="' . $msg['contact_display_name'] . '">' . $msg['contact_display_name'] . '</a></td>';
		echo '<td><a href="?' . CForum::getHRef($msg['message_forum']) . '" title="' . $msg['forum_name'] . '">' . $msg['forum_name'] . '</a></td>';
		if ($msg['forum_project'] != '') {
			echo '<td><a href="?' . CProject::getHRef($msg['forum_project']) . '" title="' . $msg['project_name'] . '">' . $msg['project_name'] . '</a></td></tr>';
		} else {
			echo '<td></td></tr>';
		}
	}
}
echo '</table>';
