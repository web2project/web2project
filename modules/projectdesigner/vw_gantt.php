<?php
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}
global $project_id, $m;
global $st_projects_arr;

$df = $AppUI->getPref('SHDATEFORMAT');
$projectPriority = w2PgetSysVal('ProjectPriority');
$projectStatus = w2PgetSysVal('ProjectStatus');
$src = '?m=tasks&amp;a=gantt&amp;suppressHeaders=1&amp;showLabels=0&amp;proFilter=&amp;showInactive=1&amp;showAllGantt=1&amp;project_id=' . $project_id;
?>
<div id="gantt_holder">
    <img src="<?= $src ?>" />
    <script type="text/javascript">
    $(function(){ $('#gantt_holder img')[0].src += '&width=' + ((window.innerWidth?window.innerWidth:document.body.offsetWidth)*0.90) });
    </script>
</div>
