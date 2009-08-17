<?php /* $Id$ $URL$ */
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}

// check permissions
$perms = &$AppUI->acl();
if (!$perms->checkModule('system', 'edit')) {
	$AppUI->redirect('m=public&a=access_denied');
}

$module = w2PgetParam($_REQUEST, 'module', 'admin');
$lang = w2PgetParam($_REQUEST, 'lang', $AppUI->user_locale);

$AppUI->savePlace('m=system&a=translate&module=' . $module . '&lang=' . $lang);

// read the installed modules
$modules = arrayMerge($AppUI->readDirs('modules'), array('common' => 'common', 'styles' => 'styles'));
asort($modules);

// read the installed languages
$locales = $AppUI->readDirs('locales');

ob_start();
// read language files from module's locale directory preferrably
if (file_exists(W2P_BASE_DIR . '/modules/' . $modules[$module] . '/locales/en/' . $modules[$module] . '.inc')) {
	readfile(W2P_BASE_DIR . '/modules/' . $modules[$module] . '/locales/en/' . $modules[$module] . '.inc');
} else {
	if (file_exists(W2P_BASE_DIR . '/locales/en/' . $modules[$module] . '.inc')) {
		readfile(W2P_BASE_DIR . '/locales/en/' . $modules[$module] . '.inc');
	}
}
eval("\$english=array(" . ob_get_contents() . "\n'0');");
ob_end_clean();

$trans = array();
foreach ($english as $k => $v) {
	if ($v != '0') {
		$trans[(is_int($k) ? $v : $k)] = array('english' => $v);
	}
}

//echo "<pre>";print_r($trans);echo "</pre>";die;

if ($lang != 'en') {
	ob_start();
	// read language files from module's locale directory preferrably
	if (file_exists(W2P_BASE_DIR . '/modules/' . $modules[$module] . '/locales/' . $lang . '/' . $modules[$module] . '.inc')) {
		readfile(W2P_BASE_DIR . '/modules/' . $modules[$module] . '/locales/' . $lang . '/' . $modules[$module] . '.inc');
	} else {
		if (file_exists(W2P_BASE_DIR . '/locales/' . $lang . '/' . $modules[$module] . '.inc')) {
			readfile(W2P_BASE_DIR . '/locales/' . $lang . '/' . $modules[$module] . '.inc');
		}
	}
	eval("\$locale=array(" . ob_get_contents() . "\n'0');");
	ob_end_clean();

	foreach ($locale as $k => $v) {
		if ($v != '0') {
			$trans[$k]['lang'] = $v;
		}
	}
}
ksort($trans);

$titleBlock = new CTitleBlock('Translation Management', 'rdf2.png', $m, $m . '.' . $a);
$titleBlock->addCell($AppUI->_('Module'), '', '<form action="?m=system&a=translate" method="post" name="modlang" accept-charset="utf-8">', '');
$titleBlock->addCell(arraySelect($modules, 'module', 'size="1" class="text" onchange="document.modlang.submit();"', $module));
$titleBlock->addCell($AppUI->_('Language'));
$temp = $AppUI->setWarning(false);
$titleBlock->addCell(arraySelect($locales, 'lang', 'size="1" class="text" onchange="document.modlang.submit();"', $lang, true), '', '', '</form>');
$AppUI->setWarning($temp);

$titleBlock->addCrumb('?m=system', 'system admin');
$titleBlock->show();
?>

<form action="?m=system&a=translate_save" method="post" name="editlang" accept-charset="utf-8">
<input type="hidden" name="module" value="<?php echo $modules[$module]; ?>" />
<input type="hidden" name="lang" value="<?php echo $lang; ?>" />
<table width="100%" border="0" cellpadding="1" cellspacing="1" class="tbl">
<tr>
	<th width="15%" nowrap="nowrap"><?php echo $AppUI->_('Abbreviation'); ?></th>
	<th width="40%" nowrap="nowrap"><?php echo $AppUI->_('English String'); ?></th>
	<th width="40%" nowrap="nowrap"><?php echo $AppUI->_('String') . ': ' . $AppUI->_($locales[$lang]); ?></th>
	<th width="5%" nowrap="nowrap"><?php echo $AppUI->_('delete'); ?></th>
</tr>
<?php
$s = '';
$index = 0;
if ($lang == 'en') {
	$s .= '<tr>';
	$s .= '<td><input type="text" name="trans[' . $index . '][abbrev]" value="" size="20" class="text" /></td>';
	$s .= '<td><input type="text" name="trans[' . $index . '][english]" value="" size="40" class="text" /></td>';
	$s .= '<td colspan="2">' . $AppUI->_('New Entry') . '</td>';
	$s .= '</tr>';
}

$index++;
foreach ($trans as $k => $langs) {
	$s .= '<tr><td>';
	if ($k != $langs['english']) {
		$k = w2PformSafe($k, true);
		if ($lang == 'en') {
			$s .= '<input type="text" name="trans[' . $index . '][abbrev]" value="' . $k . '" size="20" class="text" />';
		} else {
			$s .= $k;
		}
	} else {
		$s .= '&nbsp;';
	}
	$s .= '</td><td>';
	$langs['english'] = w2PformSafe($langs['english'], true);
	if ($lang == 'en') {
		if (w2PUTF8strlen($langs['english']) < 40) {
			$s .= '<input type="text" name="trans[' . $index . '][english]" value="' . $langs['english'] . '" size="40" class="text" />';
		} else {
			$rows = round(w2PUTF8strlen($langs['english'] / 35)) + 1;
			$s .= '<textarea name="trans[' . $index . '][english]"  cols="40" class="small" rows="' . $rows . '">' . $langs['english'] . '</textarea>';
		}
	} else {
		$s .= $langs['english'];
		$s .= '<input type="hidden" name="trans[' . $index . '][english]" value="' . ($k ? $k : $langs['english']) . '" size="20" class="text" />';
	}
	$s .= '</td><td>';
	if ($lang != 'en') {
		$langs['lang'] = w2PformSafe($langs['lang'], true);
		if (w2PUTF8strlen($langs['lang']) < 40) {
			$s .= '<input type="text" name="trans[' . $index . '][lang]" value="' . $langs['lang'] . '" size="40" class="text" />';
		} else {
			$rows = round(w2PUTF8strlen($langs['lang'] / 35)) + 1;
			$s .= '<textarea name="trans[' . $index . '][lang]" cols="40" class="small" rows="' . $rows . '">' . $langs['lang'] . '</textarea>';
		}
	}
	$s .= '</td><td align="center"><input type="checkbox" name="trans[' . $index . '][del]" /></td></tr>';
	$index++;
}
echo $s;
?>
<tr>
	<td colspan="4" align="right">
		<input type="submit" value="<?php echo $AppUI->_('submit'); ?>" class="button" />
	</td>
</tr>
</form>
</table>