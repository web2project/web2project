<?php /* $Id$ $URL$ */
if (!defined('W2P_BASE_DIR')) {
	die('You should not call this file directly.');
}
$df = $AppUI->getPref('SHDATEFORMAT');
$date = w2PgetParam($_GET, 'date', '');
$field = w2PgetParam($_GET, 'field', '');
$this_day = new CDate($date);
$formatted_date = $this_day->format($df);
?>
<script language="JavaScript" type="text/javascript">
<!--
	window.parent.document.<?php echo $field; ?>.value = '<?php echo $formatted_date; ?>';
//-->
</script>