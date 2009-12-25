<?php /* $Id$ $URL$ */
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}

$history_id = w2PgetParam($_GET, 'history_id', 0);

if (!$canEdit) {
	$AppUI->redirect('m=public&a=access_denied');
}

$action = $_REQUEST['action'];
$q = new DBQuery;
if ($action) {
	$history_description = w2PgetParam($_POST, 'history_description', '');
	$history_project = w2PgetParam($_POST, 'history_project', '');
	$userid = $AppUI->user_id;

	$perms = &$AppUI->acl();

	if ($action == 'add') {
		if (!$perms->checkModule('history', 'add')) {
			$AppUI->redirect('m=public&a=access_denied');
		}
		$q->addTable('history');
		$q->addInsert('history_table', "history");
		$q->addInsert('history_action', "add");
		$q->addInsert('history_date', str_replace("'", '', $db->DBTimeStamp(time())));
		$q->addInsert('history_description', $history_description);
		$q->addInsert('history_user', $userid);
		$q->addInsert('history_project', $history_project);
		$okMsg = 'History added';
	} elseif ($action == 'update') {
		if (!$perms->checkModule('history', 'edit')) {
			$AppUI->redirect('m=public&a=access_denied');
		}
		$q->addTable('history');
		$q->addUpdate('history_description', $history_description);
		$q->addUpdate('history_project', $history_project);
		$q->addWhere('history_id =' . $history_id);
		$okMsg = 'History updated';
	} elseif ($action == 'del') {
		if (!$perms->checkModule('history', 'delete')) {
			$AppUI->redirect('m=public&a=access_denied');
		}
		$q->setDelete('history');
		$q->addWhere('history_id =' . $history_id);
		$okMsg = 'History deleted';
	}
	if (!$q->exec()) {
		$AppUI->setMsg(db_error());
	} else {
		$AppUI->setMsg($okMsg);
		if ($action == 'add') {
			$q->clear();
		}
		$q->addTable('history');
		$q->addUpdate('history_item = history_id');
		$q->addWhere('history_table = \'history\'');
		$okMsg = 'History deleted';
	}
	$q->clear();
	$AppUI->redirect();
}

// pull the history
$q->addTable('history');
$q->addQuery('*');
$q->addWhere('history_id =' . $history_id);
$history = $q->loadHash();
$q->clear();

$titleBlock = new CTitleBlock($history_id ? 'Edit history' : 'New history', 'stock_book_blue_48.png', 'history', 'history.' . $a);
if ($canDelete) {
	$titleBlock->addCrumbDelete('delete history', $canDelete, $msg);
}
$titleBlock->show();
?>

<script>
	function delIt() {
		document.AddEdit.action.value = 'del';
		document.AddEdit.submit();
	}	
</script>

<form name="AddEdit" method="post" accept-charset="utf-8">				
	<input name="action" type="hidden" value="<?php echo $history_id ? 'update' : 'add' ?>" />
	<table border="0" cellpadding="4" cellspacing="0" width="100%" class="std">
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
								echo arraySelect($projects, 'history_project', 'class="text"', $history['history_project']);
							?>
						</td>
					</tr>
					<tr>
						<td align="right" nowrap="nowrap"><?php echo $AppUI->_('Description'); ?>:</td>
						<td width="60%">
							<textarea name="history_description" class="textarea" cols="60" rows="5"><?php echo $history['history_description']; ?></textarea>
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
									<input class="button" type="button" name="cancel" value="<?php echo $AppUI->_('cancel'); ?>" onclick="javascript:if(confirm('<?php echo $AppUI->_('Are you sure you want to cancel?', UI_OUTPUT_JS); ?>')){location.href = '?<?php echo $AppUI->getPlace(); ?>';}" />
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