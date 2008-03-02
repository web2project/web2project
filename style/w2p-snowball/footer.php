<?php /* $Id$ $URL$ */
global $a, $AppUI;
if (function_exists('styleRenderBoxBottom') && (w2PgetParam($_GET, 'tab', 0) != -1)) {
	echo styleRenderBoxBottom();
}
$AppUI->loadFooterJS();
echo $AppUI->getMsg();
?>
	</td>
</tr>
</table>
</body>
</html>