<?php
/** @var w2p_Core_CAppUI $AppUI */
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}
// @todo    convert to template
//--MSy--
$ssearch = array();
$ssearch['keywords'] = array();
$ssearch['advanced_search'] = w2PgetParam($_POST, 'advancedsearch', '');
$ssearch['mod_selection'] = w2PgetParam($_POST, 'modselection', '');

$modules = w2PgetParam($_POST, 'modules', []);
$ssearch['modules'] = array_flip($modules);

$hook_modules = array();
$moduleList = $AppUI->getLoadableModuleList();
asort($moduleList);

foreach ($moduleList as $module) {
    if (class_exists($module['mod_main_class'])) {
        $object = new $module['mod_main_class']();
        if (is_callable(array($object, 'hook_search'))) {
			$hook_modules[] = $module['mod_directory'];
        }
    }
}

$ssearch['all_words'] = w2PgetParam($_POST, 'allwords', '');

$keyword1 = (isset($_POST['keyword1'])) ? strip_tags($_POST['keyword1']) : '';
$keyword2 = (isset($_POST['keyword2'])) ? strip_tags($_POST['keyword2']) : '';
$keyword3 = (isset($_POST['keyword3'])) ? strip_tags($_POST['keyword3']) : '';
$keyword4 = (isset($_POST['keyword4'])) ? strip_tags($_POST['keyword4']) : '';

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
		} else {
			block.style.display='none';

		}
	}
	
	function selModAll() {
		const select = document.getElementById('search_modules');
		Array.from(select.options).forEach(option => option.selected = true);
	}

	function deselModAll() {
		const select = document.getElementById('search_modules');
		Array.from(select.options).forEach(option => option.selected = false);   
	}		

	
	window.onload = focusOnSearchBox;

</script>

<?php
    $titleBlock = new w2p_Theme_TitleBlock('SmartSearch', 'icon.png', $m);
    $titleBlock->show();
?>
<?php

$form = new w2p_Output_HTML_FormHelper($AppUI);

?>
<form name="frmSearch" action="?m=<?php echo $m; ?>"  method="post" accept-charset="utf-8">
    <?php echo $form->addNonce(); ?>

    <table class="tbl view smartsearch">
        <tr><td>
			<table cellspacing="5" cellpadding="0" border="0">
				<tr>
					<td align="left" valign="middle">
					<div id="div_advancedsearch1" style="<?php echo ($ssearch['advanced_search'] == "on" ? 'visibility:visible' : 'visibility:hidden'); ?> "> 1. </div></td>
					<td align="left"><input class="text" size="18" type="text" id="keyword1" name="keyword1" value="<?php echo $keyword1; ?>" /></td>
					<td align="left"><input class="button btn btn-small dropdown-toggle" type="submit" value="<?php echo $AppUI->_('Search'); ?>" /></td>
					<td align="left"><input name="allwords" id="allwords" type="checkbox"  <?php echo ($ssearch['all_words'] == "on" ? 'checked="checked"' : ''); ?> /></td> <td align="left"><label for="allwords"><?php echo $AppUI->_('All words'); ?></label></td>
					<td align="left"><input name="modselection" id="modselection" type="checkbox"  <?php echo ($ssearch['mod_selection'] == "on" ? 'checked="checked"' : ''); ?> onclick="toggleModules(this)" /></td> <td align="left"><label for="modselection"><?php echo $AppUI->_('Modules selection'); ?></label></td>
					<td align="left"><input name="advancedsearch" id="advancedsearch" type="checkbox" <?php echo ($ssearch['advanced_search'] == "on" ? 'checked="checked"' : ''); ?> onclick="toggleStatus(this)" /></td> <td align="left"><label for="advancedsearch"><?php echo $AppUI->_('Advanced search'); ?></label></td>
				</tr>
			</table>
			<div id="div_advancedsearch" style="<?php echo ($ssearch['advanced_search'] == "on" ? 'display:block' : 'display:none'); ?> ">
				<table cellspacing="5" cellpadding="0" border="0">
					<tr>
						<td align="left"> 2. </td>
						<td align="left"><input class="text" size="18" type="text" id="keyword2" name="keyword2" value="<?php echo $keyword2; ?>" /></td>
						<td align="left"> 3. <input class="text" size="18" type="text" id="keyword3" name="keyword3" value="<?php echo $keyword3; ?>" /></td>
						<td align="left"> 4. <input class="text" size="18" type="text" id="keyword4" name="keyword4" value="<?php echo $keyword4; ?>" /></td>
						<td align="left"><input name="ignorespecchar" id="ignorespecchar" type="checkbox"  <?php echo ($ssearch['ignore_specchar'] == "on" ? 'checked="checked"' : ''); ?> /></td> <td align="left"><label for="ignorespecchar"><?php echo $AppUI->_('Ignore special chars'); ?></label></td>
						<td align="left"><input name="ignorecase" id="ignorecase" type="checkbox"  <?php echo ($ssearch['ignore_case'] == "on" ? 'checked="checked"' : ''); ?> /></td> <td align="left"><label for="ignorecase"><?php echo $AppUI->_('Ignore case'); ?></label></td>
						<td align="left"><input name="displayallflds" id="displayallflds" type="checkbox"  <?php echo ($ssearch['display_all_flds'] == "on" ? 'checked="checked"' : ''); ?> /></td> <td align="left"><label for="displayallflds"><?php echo $AppUI->_('Display all fields'); ?></label></td>
						<td align="left"><input name="showempty" id="showempty" type="checkbox"  <?php echo ($ssearch['show_empty'] == "on" ? 'checked="checked"' : ''); ?> /></td> <td align="left"><label for="showempty"><?php echo $AppUI->_('Show empty'); ?></label></td>
					</tr>
				</table>
			</div>
			<div id="div_selmodules" style="<?php echo ($ssearch['mod_selection'] == "on" ? 'display:block' : 'display:none'); ?> ">
				<select name="modules[]" id="search_modules" multiple="true" size="<?php echo count($hook_modules); ?>">
					<?php foreach ($hook_modules as $tmp) { ?>
						<option value="<?php echo $tmp; ?>" <?php echo isset($ssearch['modules'][$tmp]) ? 'selected' : ''; ?> ><?php echo $AppUI->_(ucfirst($tmp)); ?></option>
					<?php } ?>
				</select>
				<a class="button" href="javascript: void(0);" onclick="selModAll(this)"><span><?php echo $AppUI->_('Select all'); ?></span></a>
				<a class="button" href="javascript: void(0);" onclick="deselModAll(this)"><span><?php echo $AppUI->_('Deselect all'); ?></span></a>
			</div>
	</td></tr>
	</table>
</form>
<?php
if ('' !== $keyword1) {
	$search = new CSmartSearch();
    $search->keyword = $keyword1;
    $search->keyword = preg_replace("/[^A-Za-z0-9 ]/", "", $search->keyword);

	if ('' != $keyword1) {
		$or_keywords = preg_split('/[\s,;]+/', $keyword1);
		foreach ($or_keywords as $or_keyword) {
			$ssearch['keywords'][$or_keyword] = array($or_keyword);
			$ssearch['keywords'][$or_keyword][1] = 0;
		}
	}

	if ('' != $keyword2 > 0) {
		$or_keywords = preg_split('/[\s,;]+/', $keyword2);
		foreach ($or_keywords as $or_keyword) {
			$ssearch['keywords'][$or_keyword] = array($or_keyword);
			$ssearch['keywords'][$or_keyword][1] = 1;
		}
	}

	if ('' != $keyword3) {
		$or_keywords = preg_split('/[\s,;]+/', $keyword3);
		foreach ($or_keywords as $or_keyword) {
			$ssearch['keywords'][$or_keyword] = array($or_keyword);
			$ssearch['keywords'][$or_keyword][1] = 2;
		}
	}

	if ('' != $keyword4) {
		$or_keywords = preg_split('/[\s,;]+/', $keyword4);
		foreach ($or_keywords as $or_keyword) {
			$ssearch['keywords'][$or_keyword] = array($or_keyword);
			$ssearch['keywords'][$or_keyword][1] = 3;
		}
	}

  ?>
  <table class="tbl list smartsearch">
  	<?php
    	$perms = &$AppUI->acl();
    	$reccount = 0;
		$moduleCount = count($ssearch['modules']);

        foreach ($moduleList as $module) {
			if (class_exists($module['mod_main_class'])) {
				if ($moduleCount) {
					if (!isset($ssearch['modules'][$module['mod_directory']])) {
						continue;
					}
				}

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
    	echo '<tr><td colspan="25"><b>' . $AppUI->_('Total records found') . ': ' . $reccount . '</b></td></tr>';
    ?>
  </table>
<?php
}