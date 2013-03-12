<?php
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}

global $this_day, $first_time, $last_time, $company_id, $m, $a;

$links = array();

// assemble the links for the tasks
getTaskLinks($first_time, $last_time, $links, 100, $company_id);

$s = '';
$dayStamp = $this_day->format(FMT_TIMESTAMP_DATE);

$min_view = 1;
include W2P_BASE_DIR . '/modules/tasks/todo.php';

echo '<br><br><table cellspacing="2" cellpadding="4" border="0" width="100%">';

if (isset($links[$dayStamp])) {
	echo '<tr><td><h1>' . $AppUI->_('Tasks assigned to others') . ':</h1></td></tr>';
	foreach ($links[$dayStamp] as $e) {
		$href = isset($e['href']) ? $e['href'] : null;
		$alt = isset($e['alt']) ? $e['alt'] : null;

		$s .= '<tr><td ' . $e['td'] . '>';
		$s .= $href ? '<a href="' . $href . '" class="event" title="' . $alt . '">' : '';
		$s .= $e['text'];
		$s .= $href ? '</a>' : '';
		$s .= '</td></tr>';
	}
}
echo $s;
echo '</table>';