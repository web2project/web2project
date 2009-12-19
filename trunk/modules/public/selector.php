<?php /* $Id$ $URL$ */
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}

function selPermWhere($obj, $idfld, $namefield, $prefix = '') {
	global $AppUI;

	$allowed = $obj->getAllowedRecords($AppUI->user_id, $idfld . ', ' . $namefield, '', '', '', $prefix);
	if (count($allowed)) {
		return ' ' . $idfld . ' IN (' . implode(',', array_keys($allowed)) . ') ';
	} else {
		return null;
	}
}

$debug = false;
$callback = w2PgetParam($_GET, 'callback', 0);
$table = w2PgetParam($_GET, 'table', 0);
$user_id = w2PgetParam($_GET, 'user_id', 0);

$ok = $callback & $table;

$title = 'Generic Selector';

$q = new DBQuery;
$q->addTable($table);
$query_result = false;

switch ($table) {
	case 'companies':
		$obj = new CCompany;
		$title = 'Company';
		$q->addQuery('company_id, company_name');
		$q->addOrder('company_name');
		$q->addWhere(selPermWhere($obj, 'company_id', 'company_name'));
		$resultList = $q->loadHashList();
		break;
	case 'departments':
		// known issue: does not filter out denied companies
		$title = 'Department';
		$company_id = w2PgetParam($_GET, 'company_id', 0);
		$obj = new CDepartment;
		$q->addWhere(selPermWhere($obj, 'dept_id', 'dept_name'));
		$q->addWhere('dept_company = company_id ');
		$q->addTable('companies', 'b');

		$company = new CCompany();
		$allowed = $company->getAllowedRecords($AppUI->user_id, 'company_id, company_name');
		if (count($allowed)) {
			$q->addWhere('company_id IN (' . implode(',', array_keys($allowed)) . ') ');
		}

		$hide_company = w2PgetParam($_GET, 'hide_company', 0);
		$q->addQuery('dept_id');
		if ($hide_company == 1) {
			$q->addQuery('dept_name');
		} else {
			$q->addQuery('CONCAT_WS(\': \',company_name,dept_name) AS dept_name');
		}
		if ($company_id) {
			$q->addWhere('dept_company = ' . (int)$company_id);
			$q->addOrder('dept_name');
		} else {
			$q->addOrder('company_name, dept_name');
		}
		$resultList = $q->loadHashList();
		break;
	case 'files':
		$title = 'File';
		$q->addQuery('file_id,file_name');
		$q->addOrder('file_name');
		$resultList = $q->loadHashList();
		break;
	case 'forums':
		$title = 'Forum';
		$q->addQuery('forum_id,forum_name');
		$q->addOrder('forum_name');
		$resultList = $q->loadHashList();
		break;
	case 'projects':
		$project_company = w2PgetParam($_GET, 'project_company', 0);

		if ($user_id > 0) {
			$projectList = CContact::getProjects($user_id);
		} else {
			$projectList = CCompany::getProjects($AppUI, $project_company);
		}
		foreach ($projectList as $project) {
			$resultList[$project['project_id']] = $project['project_name'];
		}
		break;

	case 'tasks':
	  $title = 'Task';
	  $task_project = (int) w2PgetParam($_GET, 'task_project', 0);

	  $myTask = new CTask();
	  $task_list = $myTask->getAllowedTaskList($AppUI, $task_project);

      $level = 0;
      $query_result = array();
      $last_parent = 0;
      foreach ($task_list as $task) {
        if ($task['task_parent'] != $task['task_id']) {
      		if ($last_parent != $task['task_parent']) {
      			$last_parent = $task['task_parent'];
      			$level++;
      		}
      	} else {
      		$last_parent = 0;
      		$level = 0;
      	}
      	$query_result[$task['task_id']] = ($level ? str_repeat('&nbsp;&nbsp;', $level) : '') . $task['task_name'];
      }
      break;
	case 'users':
		$title = 'User';
		$q->addQuery('user_id,CONCAT_WS(\' \',contact_first_name,contact_last_name)');
		$q->addOrder('contact_first_name');
		$q->addTable('contacts', 'b');
		$q->addWhere('user_contact = contact_id');
		$resultList = $q->loadHashList();
		break;
	case 'SGD':
		$title = 'Document';
		$q->addQuery('SGD_id, SGD_name');
		$q->addOrder('SGD_name');
		$resultList = $q->loadHashList();
		break;
	default:
		$ok = false;
		break;
}

if (!$ok) {
	echo 'Incorrect parameters passed' . "\n";
	if ($debug) {
		echo '<br />callback = ' . $callback . "\n";
		echo '<br />table = ' . $table . "\n";
		echo '<br />ok = ' . $ok . "\n";
	}
} else {
	$list = arrayMerge(array(0 => $AppUI->_('[none]')), $query_result ? $query_result : $resultList);
	echo db_error();
?>
<script language="javascript">
	function setClose(key, val){
		window.opener.<?php echo $callback; ?>(key,val);
		window.close();
	}

	window.onresize = window.onload = function setHeight(){

		if (document.compatMode && document.compatMode != "BackCompat" && document.documentElement.clientHeight)
			var wh = document.documentElement.clientHeight;
		else
			var wh = document.all ? document.body.clientHeight : window.innerHeight;
   
		var wh = getInnerHeight(window);
		var selector = document.getElementById('selector');
		var count = 0;
		obj = selector;
		while(obj!=null){
			count += obj.offsetTop;
			obj = obj.offsetParent;
		}
		selector.style.height = (wh - count - 5) + 'px';

	}

</script>
<form name="frmSelector" accept-charset="utf-8">
<b><?php echo $AppUI->_('Select') . ' ' . $AppUI->_($title) . ':' ?></b>
<?php
	if (function_exists('styleRenderBoxTop')) {
		echo styleRenderBoxTop();
	}
?>
<table class="std" width="100%">
<tr>
	<td>
		<div style="white-space:normal; overflow:auto; "  id="selector">
		<ul style="padding-left:0px">
		<?php
			if (count($list) > 1) {
				foreach ($list as $key => $val) {
					$name = htmlspecialchars($val, ENT_QUOTES);
					echo '<li><a href="javascript:setClose(\'' . $key . '\',\'' . $name . '\');">' . $val . '</a></li>';
				}
			} else {
				echo $AppUI->_('no' . $table);
			}
		?>
		</ul>
		</div>
	</td>
	<td valign="bottom">
				<input type="button" class="button" value="<?php echo $AppUI->_('cancel'); ?>" onclick="window.close()" />
	</td>
</tr>
</table>
</form>
<?php } ?>