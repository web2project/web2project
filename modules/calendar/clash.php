<?php /* $Id$ $URL$ */
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}
global $AppUI, $cal_sdf;
$AppUI->loadCalendarJS();

if (isset($_REQUEST['clash_action'])) {
	$do_include = false;
	switch ($_REQUEST['clash_action']) {
		case 'suggest':
			clash_suggest($AppUI, $cal_sdf);
			break;
		case 'process':
			clash_process($AppUI);
			break;
		case 'cancel':
			clash_cancel($AppUI);
			break;
		case 'mail':
			clash_mail($AppUI);
			break;
		case 'accept':
			clash_accept($AppUI);
			break;
		default:
			$AppUI->setMsg('Invalid action, event cancelled', UI_MSG_ALERT);
			break;
	}
	// Why do it here?  Because it is in the global scope and requires
	// less hacking of the included file.
	if ($do_include) {
		include $do_include;
	}
} else {

?>
<script language="javascript" type="text/javascript">
  function set_clash_action(action) {
    var f = document.clash_form;
    f.clash_action.value = action;
    f.submit();
  }

</script>
<?php

	$titleBlock = new w2p_Theme_TitleBlock(($obj->event_id ? 'Edit Event' : 'Add Event'), 'myevo-appointments.png', $m, $m.'.'.$a);
	$titleBlock->show();

	$_SESSION['add_event_post'] = get_object_vars($obj);
	$_SESSION['add_event_clash'] = implode(',', array_keys($clash));
	$_SESSION['add_event_caller'] = $last_a;
	$_SESSION['add_event_attendees'] = $_POST['event_assigned'];
	$_SESSION['add_event_mail'] = isset($_POST['mail_invited']) ? $_POST['mail_invited'] : 'off';

	$s = '<table width="100%" class="std"><tr><td><b>' . $AppUI->_('clashEvent') . '</b></tr></tr>';
	foreach ($clash as $user) {
		$s .= '<tr><td>' . $user . '</td></tr>';
	}
	
	$calurl = W2P_BASE_URL . '/index.php?m=calendar&a=clash&event_id=' . $obj->event_id;
    $s .= '<tr><td>';
	$s .= '<a href="javascript: void(0);" onclick="set_clash_action(\'suggest\');">' . $AppUI->_('Suggest Alternative') . '</a> : ';
	$s .= '<a href="javascript: void(0);" onclick="set_clash_action(\'cancel\');">' . $AppUI->_('Cancel') . '</a> : ';
	$s .= '<a href="javascript: void(0);" onclick="set_clash_action(\'mail\');">' . $AppUI->_('Mail Request') . '</a> : ';
	$s .= '<a href="javascript: void(0);" onclick="set_clash_action(\'accept\');">' . $AppUI->_('Book Event Despite Conflict') . '</a>';
    $s .= '</td></tr>';
    $s .= '</table>';
	$s .= '<form name="clash_form" method="post" action="' . $calurl . '" accept-charset="utf-8">';
	$s .= '<input type="hidden" name="clash_action" value="cancel">';
	$s .= '</form>';
	echo $s;
}

/*
* display a form
* 
* From modules/calendar/clash.php
* 
* TODO: I wanted to move this one but it's a big hairy mess..
*/
function clash_suggest(w2p_Core_CAppUI $AppUI, $cal_sdf) {
	global $m, $a;
	$obj = new CEvent;
	$obj->bind($_SESSION['add_event_post']);

	$start_date = new w2p_Utilities_Date($obj->event_start_date);
	$end_date = new w2p_Utilities_Date($obj->event_end_date);
	$df = $AppUI->getPref('SHDATEFORMAT');
	$start_secs = $start_date->getTime();
	$end_secs = $end_date->getTime();
	$duration = (int)(($end_secs - $start_secs) / 60);

	$titleBlock = new w2p_Theme_TitleBlock('Suggest Alternative Event Time', 'myevo-appointments.png', $m, $m . '.' . $a);
	$titleBlock->show();
	$calurl = W2P_BASE_URL . '/index.php?m=calendar&a=clash&event_id=' . $obj->event_id;
	$times = array();
	$t = new w2p_Utilities_Date();
	$t->setTime(0, 0, 0);
	if (!defined('LOCALE_TIME_FORMAT')) {
		define('LOCALE_TIME_FORMAT', '%I:%M %p');
    }
	for ($m = 0; $m < 60; $m++) {
		$times[$t->format('%H%M%S')] = $t->format(LOCALE_TIME_FORMAT);
		$t->addSeconds(1800);
	}
/* TODO: This needs to be refactored to use the core setDate_new function. */
    ?>
    <script language="javascript" type="text/javascript">
    function setDate( frm_name, f_date ) {
        fld_date = eval( 'document.' + frm_name + '.' + f_date );
        fld_real_date = eval( 'document.' + frm_name + '.' + 'event_' + f_date );
        if (fld_date.value.length>0) {
          if ((parseDate(fld_date.value))==null) {
                alert('The Date/Time you typed does not match your prefered format, please retype.');
                fld_real_date.value = '';
                fld_date.style.backgroundColor = 'red';
            } else {
                fld_real_date.value = formatDate(parseDate(fld_date.value), 'yyyyMMdd');
                fld_date.value = formatDate(parseDate(fld_date.value), '<?php echo $cal_sdf ?>');
                fld_date.style.backgroundColor = '';
            }
        } else {
            fld_real_date.value = '';
        }
    }

    function set_clash_action(action) {
        document.editFrm.clash_action.value = action;
        document.editFrm.submit();
    }

    </script>
    <form name="editFrm" method="post" action="<?php echo $calurl.'&clash_action=process'; ?>" accept-charset="utf-8">
    <table width='100%' class='std addedit'>
    <tr>
      <td width='50%' align='right'><?php echo $AppUI->_('Earliest Date'); ?>:</td>
      <td width='50%' align='left' nowrap="nowrap">
        <input type="hidden" name="event_start_date" id="event_start_date" value="<?php echo $start_date ? $start_date->format(FMT_TIMESTAMP_DATE) : ''; ?>" />
        <input type="text" name="start_date" id="start_date" onchange="setDate('editFrm', 'start_date');" value="<?php echo $start_date ? $start_date->format($df) : ''; ?>" class="text" />
        <a href="javascript: void(0);" onclick="return showCalendar('start_date', '<?php echo $df ?>', 'editFrm', null, true)">
        <img src="<?php echo w2PfindImage('calendar.gif'); ?>" width="24" height="12" alt="<?php echo $AppUI->_('Calendar'); ?>" border="0" />
          </a>
      </td>
    </tr>
    <tr>
      <td width='50%' align='right'><?php echo $AppUI->_('Latest Date'); ?>:</td>
      <td width='50%' align='left' nowrap="nowrap">
        <input type="hidden" name="event_end_date" id="event_end_date" value="<?php echo $end_date ? $end_date->format(FMT_TIMESTAMP_DATE) : ''; ?>" />
        <input type="text" name="end_date" id="end_date" onchange="setDate('editFrm', 'end_date');" value="<?php echo $end_date ? $end_date->format($df) : ''; ?>" class="text" />
        <a href="javascript: void(0);" onclick="return showCalendar('end_date', '<?php echo $df ?>', 'editFrm', null, true)">
        <img src="<?php echo w2PfindImage('calendar.gif'); ?>" width="24" height="12" alt="<?php echo $AppUI->_('Calendar'); ?>" border="0" />
          </a>
      </td>
    </tr>
    <tr>
      <td width='50%' align='right'><?php echo $AppUI->_('Earliest Start Time'); ?>:</td>
      <td width='50%' align='left'>
        <?php echo arraySelect($times, 'start_time', 'size="1" class="text"', $start_date->format('%H%M%S')); ?>
      </td>
    </tr>
    <tr>
      <td width='50%' align='right'><?php echo $AppUI->_('Latest Finish Time'); ?>:</td>
      <td width='50%' align='left'>
        <?php echo arraySelect($times, 'end_time', 'size="1" class="text"', $end_date->format('%H%M%S')); ?>
      </td>
    </tr>
    <tr>
      <td width='50%' align='right'><?php echo $AppUI->_('Duration'); ?>:</td>
      <td width='50%' align='left'>
        <input type="text" class="text" size="5" name="duration" value="<?php echo $duration; ?>" />
        <?php echo $AppUI->_('minutes'); ?>
      </td>
    </tr>
    <tr>
      <td><input type="button" value="<?php echo $AppUI->_('cancel'); ?>" class="button" onclick="set_clash_action('cancel');" /></td>
      <td align="right"><input type="button" value="<?php echo $AppUI->_('submit'); ?>" class="button" onclick="set_clash_action('process')" /></td>
    </tr>
    </table>
    <input type='hidden' name='clash_action' value='cancel' />
    </form>
    <?php
}