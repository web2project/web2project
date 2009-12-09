<?php /* $Id$ $URL$ */
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}
global $project_id, $m;
global $st_projects_arr;

$df = $AppUI->getPref('SHDATEFORMAT');
$projectPriority = w2PgetSysVal('ProjectPriority');
$projectStatus = w2PgetSysVal('ProjectStatus');
?>
<table width="100%" border="0" cellpadding="5" cellspacing="1">
<tr>
    <td align="center" colspan="20">
<?php
$src = '?m=projectdesigner&a=gantt&suppressHeaders=1&showLabels=1&proFilter=&showInactive=1showAllGantt=1&project_id=' . $project_id . '&width=\' + ((navigator.appName==\'Netscape\'?window.innerWidth:document.body.offsetWidth)*0.90) + \'';
echo '<script>document.write(\'<img src="' . $src . '">\')</script>';
?>
</td>
</table>