<?php /* $Id$ $URL$ */
global $a, $AppUI;
if (function_exists('styleRenderBoxBottom')) {
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