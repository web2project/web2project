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

// a single module or a nested array would make array_flip() fail, so keep only the names
$modules = w2PgetParam($_POST, 'modules', []);
$ssearch['modules'] = array_flip(array_filter((array) $modules, 'is_string'));

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

// a posted keyword, or '' when it's missing or isn't a plain value (e.g. keyword1[]=x)
$postedKeyword = function ($name) {
    return (isset($_POST[$name]) && is_scalar($_POST[$name])) ? trim(strip_tags((string) $_POST[$name])) : '';
};
// the header's Global Search box posts 'keyword'; this page's own form posts 'keyword1'
$keyword1 = isset($_POST['keyword1']) ? $postedKeyword('keyword1') : $postedKeyword('keyword');
$keyword2 = $postedKeyword('keyword2');
$keyword3 = $postedKeyword('keyword3');
$keyword4 = $postedKeyword('keyword4');

if ($ssearch['advanced_search'] != 'on') {
	// keywords 2-4 are part of the advanced search; the page hides them when it's off
	$keyword2 = $keyword3 = $keyword4 = '';
}

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

<?php
    $titleBlock = new w2p_Theme_TitleBlock('SmartSearch', 'icon.png', $m);
    $titleBlock->show();
?>
<?php

$form = new w2p_Output_HTML_FormHelper($AppUI);

?>
<form name="frmSearch" action="?m=<?php echo $m; ?>" method="post" accept-charset="utf-8">
    <?php echo $form->addNonce(); ?>

    <div class="std mb-3 smartsearch">
        <div class="d-flex flex-wrap align-items-center gap-2 mb-2">
            <span class="smartsearch-number">1.</span>
            <input class="text" size="18" type="text" id="keyword1" name="keyword1" value="<?php echo w2PformSafe($keyword1); ?>" />
            <input class="button btn btn-primary btn-sm" type="submit" value="<?php echo $AppUI->_('Search'); ?>" />
            <span class="text-nowrap"><input name="allwords" id="allwords" type="checkbox" <?php echo ($ssearch['all_words'] == "on" ? 'checked="checked"' : ''); ?> /><label for="allwords"><?php echo $AppUI->_('All words'); ?></label></span>
            <span class="text-nowrap"><input name="modselection" id="modselection" type="checkbox" <?php echo ($ssearch['mod_selection'] == "on" ? 'checked="checked"' : ''); ?> /><label for="modselection"><?php echo $AppUI->_('Modules selection'); ?></label></span>
            <span class="text-nowrap"><input name="advancedsearch" id="advancedsearch" type="checkbox" <?php echo ($ssearch['advanced_search'] == "on" ? 'checked="checked"' : ''); ?> /><label for="advancedsearch"><?php echo $AppUI->_('Advanced search'); ?></label></span>
        </div>
        <div class="smartsearch-advanced d-flex flex-wrap align-items-center gap-2 mb-2">
            <span class="text-nowrap">2. <input class="text" size="18" type="text" id="keyword2" name="keyword2" value="<?php echo w2PformSafe($keyword2); ?>" /></span>
            <span class="text-nowrap">3. <input class="text" size="18" type="text" id="keyword3" name="keyword3" value="<?php echo w2PformSafe($keyword3); ?>" /></span>
            <span class="text-nowrap">4. <input class="text" size="18" type="text" id="keyword4" name="keyword4" value="<?php echo w2PformSafe($keyword4); ?>" /></span>
            <span class="text-nowrap"><input name="ignorespecchar" id="ignorespecchar" type="checkbox" <?php echo ($ssearch['ignore_specchar'] == "on" ? 'checked="checked"' : ''); ?> /><label for="ignorespecchar"><?php echo $AppUI->_('Ignore special chars'); ?></label></span>
            <span class="text-nowrap"><input name="ignorecase" id="ignorecase" type="checkbox" <?php echo ($ssearch['ignore_case'] == "on" ? 'checked="checked"' : ''); ?> /><label for="ignorecase"><?php echo $AppUI->_('Ignore case'); ?></label></span>
            <span class="text-nowrap"><input name="displayallflds" id="displayallflds" type="checkbox" <?php echo ($ssearch['display_all_flds'] == "on" ? 'checked="checked"' : ''); ?> /><label for="displayallflds"><?php echo $AppUI->_('Display all fields'); ?></label></span>
            <span class="text-nowrap"><input name="showempty" id="showempty" type="checkbox" <?php echo ($ssearch['show_empty'] == "on" ? 'checked="checked"' : ''); ?> /><label for="showempty"><?php echo $AppUI->_('Show empty'); ?></label></span>
        </div>
        <div class="smartsearch-modules d-flex flex-wrap align-items-start gap-2">
            <select name="modules[]" id="search_modules" multiple size="<?php echo count($hook_modules); ?>" aria-label="<?php echo $AppUI->_('Modules selection'); ?>">
                <?php foreach ($hook_modules as $tmp) { ?>
                    <option value="<?php echo $tmp; ?>" <?php echo isset($ssearch['modules'][$tmp]) ? 'selected' : ''; ?>><?php echo $AppUI->_(ucfirst($tmp)); ?></option>
                <?php } ?>
            </select>
            <button type="button" class="btn btn-outline-secondary btn-sm" data-select-modules="1"><?php echo $AppUI->_('Select all'); ?></button>
            <button type="button" class="btn btn-outline-secondary btn-sm" data-select-modules="0"><?php echo $AppUI->_('Deselect all'); ?></button>
        </div>
    </div>
</form>
<script>
    document.querySelectorAll('[data-select-modules]').forEach(function (button) {
        button.addEventListener('click', function () {
            var select = '1' === button.getAttribute('data-select-modules');
            Array.from(document.getElementById('search_modules').options).forEach(function (option) {
                option.selected = select;
            });
        });
    });
</script>
<?php
if ('' !== $keyword1) {
	if ('' !== $keyword1) {
		$or_keywords = preg_split('/[\s,;]+/', $keyword1, -1, PREG_SPLIT_NO_EMPTY);
		foreach ($or_keywords as $or_keyword) {
			$ssearch['keywords'][$or_keyword] = array($or_keyword);
			$ssearch['keywords'][$or_keyword][1] = 0;
		}
	}

	if ('' !== $keyword2) {
		$or_keywords = preg_split('/[\s,;]+/', $keyword2, -1, PREG_SPLIT_NO_EMPTY);
		foreach ($or_keywords as $or_keyword) {
			$ssearch['keywords'][$or_keyword] = array($or_keyword);
			$ssearch['keywords'][$or_keyword][1] = 1;
		}
	}

	if ('' !== $keyword3) {
		$or_keywords = preg_split('/[\s,;]+/', $keyword3, -1, PREG_SPLIT_NO_EMPTY);
		foreach ($or_keywords as $or_keyword) {
			$ssearch['keywords'][$or_keyword] = array($or_keyword);
			$ssearch['keywords'][$or_keyword][1] = 2;
		}
	}

	if ('' !== $keyword4) {
		$or_keywords = preg_split('/[\s,;]+/', $keyword4, -1, PREG_SPLIT_NO_EMPTY);
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
		// the selection stays filled in when unchecked, but only filters while checked
		$moduleCount = ($ssearch['mod_selection'] == 'on') ? count($ssearch['modules']) : 0;

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