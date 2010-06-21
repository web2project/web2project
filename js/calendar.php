<?php /* $Id$ $URL$ */
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly');
}
global $AppUI, $cal_df, $cf, $df, $tf, $cal_sdf;
$df = $AppUI->getPref('SHDATEFORMAT');
$tf = $AppUI->getPref('TIMEFORMAT');
$cf = $df . ' ' . $tf;

$cal_df = $cf;
$cal_sdf = $df;

//Javascript Long Date Format
$cal_df = str_replace('p', 'a', $cal_df);
$cal_df = str_replace('%I', '%hh', $cal_df);
$cal_df = str_replace('%M', '%mm', $cal_df);
$cal_df = str_replace('%m', '%MM', $cal_df);
$cal_df = str_replace('%MMm', '%mm', $cal_df);
$cal_df = str_replace('%d', '%dd', $cal_df);
$cal_df = str_replace('%b', '%NNN', $cal_df);
$cal_df = str_replace('%', '', $cal_df);

//Javascript Small Date Format
$cal_sdf = str_replace('p', 'a', $cal_sdf);
$cal_sdf = str_replace('%I', '%hh', $cal_sdf);
$cal_sdf = str_replace('%M', '%mm', $cal_sdf);
$cal_sdf = str_replace('%m', '%MM', $cal_sdf);
$cal_sdf = str_replace('%MMm', '%mm', $cal_sdf);
$cal_sdf = str_replace('%d', '%dd', $cal_sdf);
$cal_sdf = str_replace('%b', '%NNN', $cal_sdf);
$cal_sdf = str_replace('%', '', $cal_sdf);
?>
<script language="javascript">
//w2P Related
// ------------------------------------------------------------------
// parseDate( date_string [, prefer_euro_format] )
//
// This function takes a date string and tries to match it to a
// number of possible date formats to get the value. It will try to
// match against the following international formats, in this order:
// y-M-d   MMM d, y   MMM d,y   y-MMM-d   d-MMM-y  MMM d
// M/d/y   M-d-y      M.d.y     MMM-d     M/d      M-d
// d/M/y   d-M-y      d.M.y     d-MMM     d/M      d-M
// A second argument may be passed to instruct the method to search
// for formats like d/M/y (european format) before M/d/y (American).
// Returns a Date object or null if no patterns match.
// ------------------------------------------------------------------
function parseDate(val) {
	var preferEuro=(arguments.length==2)?arguments[1]:false;
	generalFormats=new Array('yyyyMMddHHmm', '<?php echo $cal_df ?>','yyyyMMdd', '<?php echo $cal_sdf ?>');
	monthFirst=new Array();
      dateFirst =new Array();
	var checkList=new Array('generalFormats',preferEuro?'dateFirst':'monthFirst',preferEuro?'monthFirst':'dateFirst');
	var d=null;
	for (var i=0, i_cmp=checkList.length; i<i_cmp; i++) {
		var l=window[checkList[i]];
		for (var j=0, j_cmp=l.length; j<j_cmp; j++) {
			d=getDateFromFormat(val,l[j]);
			if (d!=0) { return new Date(d); }
			}
		}
	return null;
}
</script>