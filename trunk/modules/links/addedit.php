<?php /* $Id$ $URL$ */
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}

$link_id = (int) w2PgetParam($_GET, 'link_id', 0);

// check permissions for this record
$perms = &$AppUI->acl();
$canAuthor = $perms->checkModule('links', 'add');
$canEdit = $perms->checkModuleItem('links', 'edit', $link_id);

// check permissions
if (!$canAuthor && !$link_id) {
	$AppUI->redirect('m=public&a=access_denied');
}

if (!$canEdit && $link_id) {
	$AppUI->redirect('m=public&a=access_denied');
}

$link = new CLink();
$obj = $AppUI->restoreObject();
if ($obj) {
  $link = $obj;
} else {
  $link->loadFull($AppUI, $link_id);
}

// load the record data
if (!$link && $link_id > 0) {
	$AppUI->setMsg('Link');
	$AppUI->setMsg('invalidID', UI_MSG_ERROR, true);
	$AppUI->redirect();
}

// setup the title block
$ttl = $link_id ? 'Edit Link' : 'Add Link';
$titleBlock = new CTitleBlock($ttl, 'folder5.png', $m, $m . '.' . $a);
$titleBlock->addCrumb('?m=' . $m, 'links list');
$canDelete = $perms->checkModuleItem($m, 'delete', $link_id);
if ($canDelete && $link_id > 0) {
	$titleBlock->addCrumbDelete('delete link', $canDelete, $msg);
}
$titleBlock->show();

$prj = new CProject();
$projects = $prj->getAllowedProjects($AppUI->user_id, false);
foreach ($projects as $project_id => $project_info) {
	$projects[$project_id] = $project_info['project_name'];
}
$projects = arrayMerge(array('0' => $AppUI->_('All', UI_OUTPUT_JS)), $projects);

?>
<script language="javascript">
function submitIt() {
	var f = document.uploadFrm;
	f.submit();
}
function delIt() {
	if (confirm( "<?php echo $AppUI->_('linksDelete', UI_OUTPUT_JS); ?>" )) {
		var f = document.uploadFrm;
		f.del.value='1';
		f.submit();
	}
}
function popTask() {
    var f = document.uploadFrm;
    if (f.link_project.selectedIndex == 0) {
        alert( "<?php echo $AppUI->_('Please select a project first!', UI_OUTPUT_JS); ?>" );
    } else {
        window.open('./index.php?m=public&a=selector&dialog=1&callback=setTask&table=tasks&task_project='
            + f.link_project.options[f.link_project.selectedIndex].value, 'task','left=50,top=50,height=250,width=400,resizable')
    }
}

// Callback function for the generic selector
function setTask( key, val ) {
    var f = document.uploadFrm;
    if (val != '') {
        f.link_task.value = key;
        f.task_name.value = val;
    } else {
        f.link_task.value = '0';
        f.task_name.value = '';
    }
}
</script>

<table width="100%" border="0" cellpadding="3" cellspacing="3" class="std">

  <form name="uploadFrm" action="?m=links" method="post" accept-charset="utf-8">
    <input type="hidden" name="dosql" value="do_link_aed" />
    <input type="hidden" name="del" value="0" />
    <input type="hidden" name="link_id" value="<?php echo $link_id; ?>" />
    <input type="hidden" name="link_owner" value="<?php echo $AppUI->user_id; ?>" />
  
    <tr>
    	<td width="100%" valign="top" align="center">
          <table cellspacing="1" cellpadding="2" width="60%">
            <tr>
            	<td align="right" nowrap="nowrap"><?php echo $AppUI->_('Link Name'); ?>:</td>
            	<td align="left"><input type="text" class="text" name="link_name" value="<?php echo $link->link_name; ?>"></td>
              <?php if ($link_id) { ?>
          	  <td>
          		<a href="<?php echo $link->link_url; ?>" target="_blank"><?php echo $AppUI->_('go'); ?></a>
          	  </td>
            	<?php } ?>
            </tr>
            <?php if ($link_id) { ?>
        		<tr>
        			<td align="right" nowrap="nowrap"><?php echo $AppUI->_('Uploaded By'); ?>:</td>
        			<td align="left" class="hilite"><?php echo $link->contact_first_name . ' ' . $link->contact_last_name; ?></td>
        		</tr>
            <?php } ?>
            <tr>
              <td align="right" nowrap="nowrap"><?php echo $AppUI->_('Category'); ?>:</td>
              <td align="left">
                <?php echo arraySelect(w2PgetSysVal('LinkType'), 'link_category', '', $link->link_category, true); ?>
              <td>
            </tr>
            <tr>
              <td align="right" nowrap="nowrap"><?php echo $AppUI->_('Project'); ?>:</td>
              <td align="left">
                  <?php echo arraySelect($projects, 'link_project', 'size="1" class="text" style="width:270px"', $link->link_project); ?>
              </td>
            </tr>
            <tr>
              <td align="right" nowrap="nowrap"><?php echo $AppUI->_('Task'); ?>:</td>
              <td align="left" colspan="2" valign="top">
                <input type="hidden" name="link_task" value="<?php echo $link->link_task; ?>" />
                <input type="text" class="text" name="task_name" value="<?php echo $link->task_name; ?>" size="40" disabled="disabled" />
                <input type="button" class="button" value="<?php echo $AppUI->_('select task'); ?>..." onclick="popTask()" />
              </td>
            </tr>
            <tr>
              <td align="right" nowrap="nowrap"><?php echo $AppUI->_('Description'); ?>:</td>
              <td align="left">
              	<textarea name="link_description" class="textarea" rows="4" style="width:270px"><?php echo $link->link_description; ?></textarea>
              </td>
            </tr>
            <tr>
              <td align="right" nowrap="nowrap"><?php echo $AppUI->_('Link URL'); ?>:</td>
              <td align="left"><input type="field" name="link_url" style="width:270px" value="<?php echo $link->link_url ?>"></td>
            </tr>
		  </table>
    	</td>
    </tr>
    <tr>
    	<td>
    		<input class="button" type="button" name="cancel" value="<?php echo $AppUI->_('cancel'); ?>" onclick="javascript:if(confirm('<?php echo $AppUI->_('Are you sure you want to cancel?', UI_OUTPUT_JS); ?>')){location.href = './index.php?m=links';}" />
    	</td>
    	<td align="right">
    		<input type="button" class="button" value="<?php echo $AppUI->_('submit'); ?>" onclick="submitIt()" />
    	</td>
    </tr>
  </form>
</table>