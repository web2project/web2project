<?php /* $Id$ $URL$ */
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}

$history_id = (int) w2PgetParam($_GET, 'history_id', 0);

$history = new CHistory();
$history->history_id = $history_id;

$obj = $history;
$canAddEdit = $obj->canAddEdit();
$canAuthor = $obj->canCreate();
$canEdit = $obj->canEdit();
if (!$canAddEdit) {
	$AppUI->redirect(ACCESS_DENIED);
}

$action = $_REQUEST['action'];
$q = new w2p_Database_Query;
if ($action) {
	$history_description = w2PgetParam($_POST, 'history_description', '');
	$history_project = (int) w2PgetParam($_POST, 'history_project', 0);
	$userid = $AppUI->user_id;

	$perms = &$AppUI->acl();

	if ($action == 'add') {
		$q->addTable('history');
		$q->addInsert('history_table', "history");
		$q->addInsert('history_action', "add");
		$q->addInsert('history_date', "'".$q->dbfnNowWithTZ()."'");
		$q->addInsert('history_description', $history_description);
		$q->addInsert('history_user', $userid);
		$q->addInsert('history_project', $history_project);
		$okMsg = 'History added';
	} elseif ($action == 'update') {
		$AppUI->setMsg("History cannot be edited");
        $AppUI->redirect(ACCESS_DENIED);
	} elseif ($action == 'del') {
		$AppUI->setMsg("History cannot be deleted");
        $AppUI->redirect(ACCESS_DENIED);
	}
	if (!$q->exec()) {
		$AppUI->setMsg(db_error());
	}

	$AppUI->redirect();
}

$history->load();

$titleBlock = new w2p_Theme_TitleBlock($history_id ? 'Edit history' : 'New history', 'stock_book_blue_48.png', 'history', 'history.' . $a);
$titleBlock->show();
?>

<script language="javascript" type="text/javascript">
	function delIt() {
		document.AddEdit.action.value = 'del';
		document.AddEdit.submit();
	}

    function cancel() {
        if (confirm('<?php echo $AppUI->_('Are you sure you want to cancel?', UI_OUTPUT_JS); ?>')) {
            location.href = '?<?php echo $AppUI->getPlace(); ?>';
        }
    }
</script>

<form name="frmEdit" method="post" accept-charset="utf-8">
	<input name="action" type="hidden" value="<?php echo $history_id ? 'update' : 'add' ?>" />
	<table border="0" cellpadding="4" cellspacing="0" width="100%" class="std addedit">
		<tr>
			<td>
				<table border="1" cellpadding="4" cellspacing="0" width="100%" class="std">		
					<tr>
						<td align="right" nowrap="nowrap"><?php echo $AppUI->_('Project'); ?>:</td>
						<td width="60%">
							<?php
								// pull the projects list
								$project = new CProject();
								$projects = $project->getAllowedProjects($AppUI->user_id, false);
								foreach ($projects as $project_id => $project_info) {
									$projects[$project_id] = $project_info['project_name'];
								}
								$projects = arrayMerge(array(0 => $all_projects), $projects);
								echo arraySelect($projects, 'history_project', 'class="text"', $history->history_project);
							?>
						</td>
					</tr>
					<tr>
						<td align="right" nowrap="nowrap"><?php echo $AppUI->_('Description'); ?>:</td>
						<td width="60%">
							<textarea name="history_description" class="textarea" cols="60" rows="5"><?php echo $history->history_description; ?></textarea>
						</td>
					</tr>
				</table>		
						
				<table border="0" cellspacing="0" cellpadding="3" width="100%">
					<tr>
						<td height="40" width="30%">&nbsp;</td>
						<td  height="40" width="35%" align="right">
							<table>
							<tr>
								<td>
									<input class="button" type="button" name="cancel" value="<?php echo $AppUI->_('cancel'); ?>" onclick="cancel();" />
								</td>
								<td>
									<input class="button" type="button" name="btnFuseAction" value="<?php echo $AppUI->_('save'); ?>" onclick="submit()" />
								</td>
							</tr>
							</table>
						</td>
					</tr>
				</table>		
			</td>
		</tr>
	</table>
</form>