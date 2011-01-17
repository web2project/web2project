<?php /* $Id$ $URL$ */
if (!defined('W2P_BASE_DIR')) {
	die('You should not call this file directly.');
}
$df = $AppUI->getPref('SHDATEFORMAT');
$date = w2PgetParam($_GET, 'date', '');
$field = w2PgetParam($_GET, 'field', '');
$this_day = new w2p_Utilities_Date($date);
$formatted_date = $this_day->format($df);
?>
<script language="javascript" type="text/javascript">
<!--
	window.parent.document.<?php echo $field; ?>.value = '<?php echo $formatted_date; ?>';
//-->
</script>