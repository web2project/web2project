<?php
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}

//--MSy--
$ssearch = array();
$ssearch['keywords'] = array();
$ssearch['advanced_search'] = w2PgetParam($_POST, 'advancedsearch', '');
$ssearch['mod_selection'] = w2PgetParam($_POST, 'modselection', '');

$hook_modules = array();
$moduleList = $AppUI->getLoadableModuleList();
asort($moduleList);
foreach ($moduleList as $module) {
    if (class_exists($module['mod_main_class'])) {
        $object = new $module['mod_main_class']();
        if (is_callable(array($object, 'hook_search'))) {
            $ssearch['mod_' . $module['mod_directory']] = w2PgetParam($_POST, 'mod_' . $module['mod_directory'], '');
            $hook_modules[] = $module['mod_directory'];
        }
    }
}

$ssearch['all_words'] = w2PgetParam($_POST, 'allwords', '');

$keyword = (isset($_POST['keyword'])) ? strip_tags($_POST['keyword']) : '';

if ($ssearch['advanced_search'] == 'on') {
	$ssearch['ignore_case'] = w2PgetParam($_POST, 'ignorecase', '');
	$ssearch['ignore_specchar'] = w2PgetParam($_POST, 'ignorespecchar', '');
	$ssearch['display_all_flds'] = w2PgetParam($_POST, 'displayallflds', '');
	$ssearch['show_empty'] = w2PgetParam($_POST, 'showempty', '');
} else {
	$ssearch['ignore_case'] = 'on';
	$ssearch['ignore_specchar'] = '';
	$ssearch['display_all_flds'] = '';
	$ssearch['show_empty'] = '';
}

?>
<script language="javascript" type="text/javascript">

	function focusOnSearchBox() {
		document.forms.frmSearch.keyword.focus();
	}
	function toggleStatus(obj) {
		if (obj.checked) {
				var block=document.getElementById('div_advancedsearch');
				block.style.display='block';
				var block1=document.getElementById('div_advancedsearch1');
				block1.style.visibility='visible';
			}
		else {
				var block=document.getElementById('div_advancedsearch');
				block.style.display='none';
				var block1=document.getElementById('div_advancedsearch1');
				block1.style.visibility='hidden';
				var key2=document.getElementById('keyword2');
				key2.value='';
				var key3=document.getElementById('keyword3');
				key3.value='';
				var key4=document.getElementById('keyword4');
				key4.value='';
			}
	}

	function toggleModules(obj) {
		var block=document.getElementById('div_selmodules');
		
		if (obj.checked) {
				block.style.display='block';
			}
		else {
				block.style.display='none';
			}
	}
	
	function selModAll() {
		<?php
foreach ($hook_modules as $tmp) {
	$temp = $temp;
    ?>document.frmSearch.mod_<?php echo $tmp ?>.checked=true;<?php
}
?>
	}		

	function deselModAll() {
		<?php
foreach ($hook_modules as $tmp) {
	$temp = $tmp;
?>
		document.frmSearch.mod_<?php echo $tmp ?>.checked=false;
			<?php
}
?>
	}		

	
	window.onload = focusOnSearchBox;

</script>

<?php
    $titleBlock = new w2p_Theme_TitleBlock('SmartSearch', 'kfind.png', $m, $m . '.' . $a);
    $titleBlock->show();
?>
<form name="frmSearch" action="?m=<?php echo $m; ?>"  method="post" accept-charset="utf-8">
<table class="tbl list">
	<tr><td>
			<table cellspacing="5" cellpadding="0" border="0">
				<tr>
					<td align="left" valign="middle">
					<div id="div_advancedsearch1" id="div_advancedsearch1"  style="<?php echo ($ssearch['advanced_search'] == "on" ? 'visibility:visible' : 'visibility:hidden'); ?> "> 1. </div></td>
					<td align="left"><input class="text" size="18" type="text" id="keyword" name="keyword" value="<?php echo $keyword; ?>" /></td>
					<td align="left"><input class="button" type="submit" value="<?php echo $AppUI->_('Search'); ?>" /></td>
					<td align="left"><input name="allwords" id="allwords" type="checkbox"  <?php echo ($ssearch['all_words'] == "on" ? 'checked="checked"' : ''); ?> /></td> <td align="left"><label for="allwords"><?php echo $AppUI->_('All words'); ?></label></td>
					<td align="left"><input name="modselection" id="modselection" type="checkbox"  <?php echo ($ssearch['mod_selection'] == "on" ? 'checked="checked"' : ''); ?> onclick="toggleModules(this)" /></td> <td align="left"><label for="modselection"><?php echo $AppUI->_('Modules selection'); ?></label></td>
					<td align="left"><input name="advancedsearch" id="advancedsearch" type="checkbox" <?php echo ($ssearch['advanced_search'] == "on" ? 'checked="checked"' : ''); ?> onclick="toggleStatus(this)" /></td> <td align="left"><label for="advancedsearch"><?php echo $AppUI->_('Advanced search'); ?></label></td>
				</tr>
			</table>
			<div id="div_advancedsearch" id="div_advancedsearch"  style="<?php echo ($ssearch['advanced_search'] == "on" ? 'display:block' : 'display:none'); ?> ">
				<table cellspacing="5" cellpadding="0" border="0">
					<tr>
						<td align="left"> 2. </td>
						<td align="left"><input class="text" size="18" type="text" id="keyword2" name="keyword2" value="<?php echo stripslashes($_POST['keyword2']); ?>" /></td>
						<td align="left"> 3. <input class="text" size="18" type="text" id="keyword3" name="keyword3" value="<?php echo stripslashes($_POST['keyword3']); ?>" /></td>
						<td align="left"> 4. <input class="text" size="18" type="text" id="keyword4" name="keyword4" value="<?php echo stripslashes($_POST['keyword4']); ?>" /></td>
						<td align="left"><input name="ignorespecchar" id="ignorespecchar" type="checkbox"  <?php echo ($ssearch['ignore_specchar'] == "on" ? 'checked="checked"' : ''); ?> /></td> <td align="left"><label for="ignorespecchar"><?php echo $AppUI->_('Ignore special chars'); ?></label></td>
						<td align="left"><input name="ignorecase" id="ignorecase" type="checkbox"  <?php echo ($ssearch['ignore_case'] == "on" ? 'checked="checked"' : ''); ?> /></td> <td align="left"><label for="ignorecase"><?php echo $AppUI->_('Ignore case'); ?></label></td>
						<td align="left"><input name="displayallflds" id="displayallflds" type="checkbox"  <?php echo ($ssearch['display_all_flds'] == "on" ? 'checked="checked"' : ''); ?> /></td> <td align="left"><label for="displayallflds"><?php echo $AppUI->_('Display all fields'); ?></label></td>
						<td align="left"><input name="showempty" id="showempty" type="checkbox"  <?php echo ($ssearch['show_empty'] == "on" ? 'checked="checked"' : ''); ?> /></td> <td align="left"><label for="showempty"><?php echo $AppUI->_('Show empty'); ?></label></td>
					</tr>
				</table>
			</div>
			<div id="div_selmodules" style="<?php echo ($ssearch['mod_selection'] == "on" ? 'display:block' : 'display:none'); ?> ">
				<table cellspacing="0" cellpadding="0" border="0">
  				<tr>
            <td nowrap="nowrap" colspan="2"><a class="button" href="javascript: void(0);" onclick="selModAll(this)"><span><?php echo $AppUI->_('Select all'); ?></span></a><a class="button" href="javascript: void(0);" onclick="deselModAll(this)"><span><?php echo $AppUI->_('Deselect all'); ?></span></a></td>
          </tr>
          <?php
          foreach ($hook_modules as $tmp) {
          ?>
    		<tr>
              <td width="10" align="left"><input name="mod_<?php echo $tmp; ?>" id="mod_<?php echo $tmp; ?>" type="checkbox"
      				  <?php
                  echo ($ssearch['mod_' . $tmp] == 'on') ? 'checked="checked"' : '';
    	             echo ' /></td><td align="left"><label for="mod_' . $tmp . '">' . $AppUI->_(ucfirst($tmp)) . '</label>';
                ?>
    				  </td>
            </tr>
		  <?php } ?>
				</table>
			</div>
	</td></tr>
	</table>
</form>
<?php
if (isset($_POST['keyword'])) {
	$search = new CSmartSearch();
  $search->keyword = addslashes($_POST['keyword']);

	if (isset($_POST['keyword']) && mb_strlen($_POST['keyword']) > 0) {
		$or_keywords = preg_split('/[\s,;]+/', addslashes($_POST['keyword']));
		foreach ($or_keywords as $or_keyword) {
			$ssearch['keywords'][$or_keyword] = array($or_keyword);
			$ssearch['keywords'][$or_keyword][1] = 0;
		}
	} else {
		$or_keywords = preg_split('/[\s,;]+/', addslashes($_POST['keyword']));
		foreach ($or_keywords as $or_keyword) {
			unset($ssearch['keywords'][$or_keyword]);
		}
	}

	if (isset($_POST['keyword2']) && mb_strlen($_POST['keyword2']) > 0) {
		$or_keywords = preg_split('/[\s,;]+/', addslashes($_POST['keyword2']));
		foreach ($or_keywords as $or_keyword) {
			$ssearch['keywords'][$or_keyword] = array($or_keyword);
			$ssearch['keywords'][$or_keyword][1] = 1;
		}
	} else {
		$or_keywords = preg_split('/[\s,;]+/', addslashes($_POST['keyword2']));
		foreach ($or_keywords as $or_keyword) {
			unset($ssearch['keywords'][$or_keyword]);
		}
	}

	if (isset($_POST['keyword3']) && mb_strlen($_POST['keyword3']) > 0) {
		$or_keywords = preg_split('/[\s,;]+/', addslashes($_POST['keyword3']));
		foreach ($or_keywords as $or_keyword) {
			$ssearch['keywords'][$or_keyword] = array($or_keyword);
			$ssearch['keywords'][$or_keyword][1] = 2;
		}
	} else {
		$or_keywords = preg_split('/[\s,;]+/', addslashes($_POST['keyword3']));
		foreach ($or_keywords as $or_keyword) {
			unset($ssearch['keywords'][$or_keyword]);
		}
	}

	if (isset($_POST['keyword4']) && mb_strlen($_POST['keyword4']) > 0) {
		$or_keywords = preg_split('/[\s,;]+/', addslashes($_POST['keyword4']));
		foreach ($or_keywords as $or_keyword) {
			$ssearch['keywords'][$or_keyword] = array($or_keyword);
			$ssearch['keywords'][$or_keyword][1] = 3;
		}
	} else {
		$or_keywords = preg_split('/[\s,;]+/', addslashes($_POST['keyword4']));
		foreach ($or_keywords as $or_keyword) {
			unset($ssearch['keywords'][$or_keyword]);
		}
	}

  ?>
  <table class="tbl list">
  	<?php
    	$perms = &$AppUI->acl();
    	$reccount = 0;

		reset($moduleList);
        foreach ($moduleList as $module) {
    		if ($ssearch['mod_selection'] == '' || $ssearch['mod_' . $module['mod_directory']] == 'on') {
				if (class_exists($module['mod_main_class'])) {
                    $object = new $module['mod_main_class']();
                    if (is_callable(array($object, 'hook_search'))) {
                        $search = new CSmartSearch();
                        $searchArray = $object->hook_search();
                        foreach($searchArray as $key => $value) {
                            $search->{$key} = $value;
                        }
                        $search->setKeyword($search->keyword);
                        $search->setAdvanced($ssearch);
                        echo $search->fetchResults($perms, $reccount);
                    }
                }
			}
        }
    	echo '<tr><td colspan="25"><b>' . $AppUI->_('Total records found') . ': ' . $reccount . '</b></td></tr>';
    ?>
  </table>
<?php
}