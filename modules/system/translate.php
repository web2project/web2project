<?php
if (!defined('W2P_BASE_DIR')) {
    die('You should not access this file directly.');
}

// check permissions
$perms = &$AppUI->acl();
if (!canEdit('system')) {
    $AppUI->redirect(ACCESS_DENIED);
}

$module = w2PgetParam($_REQUEST, 'module', 'admin');
$lang = w2PgetParam($_REQUEST, 'lang', $AppUI->user_locale);

// read the installed modules
$loader = new w2p_FileSystem_Loader();
$modules = arrayMerge($loader->readDirs('modules'), array('common' => 'common', 'styles' => 'styles'));
asort($modules);

// read the installed languages
$locales = $loader->readDirs('locales');

ob_start();
// read language files from module's locale directory preferably
$localeFile = W2P_BASE_DIR . '/modules/' . $modules[$module] . '/locales/en/' . $modules[$module] . '.inc';
if (file_exists($localeFile)) {
    readfile($localeFile);
} else {
    $localeFile = W2P_BASE_DIR . '/locales/en/' . $modules[$module] . '.inc';
    if (file_exists($localeFile)) {
        readfile($localeFile);
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

if ($lang != 'en') {
    ob_start();
    // read language files from module's locale directory preferably
    $localeFile = W2P_BASE_DIR . '/modules/' . $modules[$module] . '/locales/' . $lang . '/' . $modules[$module] . '.inc';
    if (file_exists($localeFile)) {
        readfile($localeFile);
    } else {
        $localeFile = W2P_BASE_DIR . '/locales/' . $lang . '/' . $modules[$module] . '.inc';
        if (file_exists($localeFile)) {
            readfile($localeFile);
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

/**
 * TODO: I don't like that this error is handled outside the normal flow but
 *   it's better than echo'ing which is what we had before.
 */
$localeFolder = pathinfo($localeFile, PATHINFO_DIRNAME);
if (file_exists($localeFile) && !is_writable($localeFile)) {
    $AppUI->setMsg($AppUI->_("Locales file ($localeFile) is not writable."), UI_MSG_ERROR);
}
echo ('' != $AppUI->msg) ? $AppUI->getMsg() : '';

$titleBlock = new w2p_Theme_TitleBlock('Translation Management', 'rdf2.png', $m);
$titleBlock->addCell(' ', '', '<form action="?m=system&a=translate" method="post" name="modlang" accept-charset="utf-8">', '');
$titleBlock->addCell(arraySelect($modules, 'module', 'size="1" class="text" onchange="document.modlang.submit();"', $module));
$titleBlock->addCell($AppUI->_('Module'));
$temp = $AppUI->setWarning(false);
$titleBlock->addCell(arraySelect($locales, 'lang', 'size="1" class="text" onchange="document.modlang.submit();"', $lang, true), '', '', '</form>');
$titleBlock->addCell($AppUI->_('Language'));
$AppUI->setWarning($temp);

$titleBlock->addCrumb('?m=system', 'system admin');
$titleBlock->show();
?>
<form action="?m=system&a=translate_save" method="post" name="editlang" accept-charset="utf-8">
    <input type="hidden" name="module" value="<?php echo $modules[$module]; ?>" />
    <input type="hidden" name="lang" value="<?php echo $lang; ?>" />
    <table class="tbl list">
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
                if (mb_strlen($langs['english']) < 40) {
                    $s .= '<input type="text" name="trans[' . $index . '][english]" value="' . $langs['english'] . '" size="40" class="text" />';
                } else {
                    $rows = round(mb_strlen($langs['english'] / 35)) + 1;
                    $s .= '<textarea name="trans[' . $index . '][english]"  cols="40" class="small" rows="' . $rows . '">' . $langs['english'] . '</textarea>';
                }
            } else {
                $s .= $langs['english'];
                $s .= '<input type="hidden" name="trans[' . $index . '][english]" value="' . ($k ? $k : $langs['english']) . '" size="20" class="text" />';
            }
            $s .= '</td><td>';
            if ($lang != 'en') {
                $langs['lang'] = w2PformSafe($langs['lang'], true);
                if (mb_strlen($langs['lang']) < 40) {
                    $s .= '<input type="text" name="trans[' . $index . '][lang]" value="' . $langs['lang'] . '" size="40" class="text" />';
                } else {
                    $rows = round(mb_strlen($langs['lang'] / 35)) + 1;
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
                <input type="submit" value="<?php echo $AppUI->_('save'); ?>" class="save button btn btn-primary" />
            </td>
        </tr>
    </table>
</form>
