<?php

class w2p_Output_PageHandler {

    public function loadExtras(array $storage, CAppUI $AppUI, $m, $type = 'tabs') {

        //Set up extra $type
        if (!isset($storage['all_'.$type][$m])) {
            // For some reason on some systems if you don't set this up
            // first you get recursive pointers to the all_$type array, creating
            // phantom tabs.
            if (!isset($storage['all_'.$type])) {
                $storage['all_'.$type] = array();
            }
            $storage['all_'.$type][$m] = array();
            $all_items = &$storage['all_'.$type][$m];
            foreach ($AppUI->getActiveModules() as $dir => $module) {
                if (!canAccess($dir)) {
                    continue;
                }
                $modules_tabs = $AppUI->readFiles(W2P_BASE_DIR . '/modules/' . $dir . '/', '^' . $m . '_tab.*\.php');
                foreach ($modules_tabs as $tab) {
                    // Get the name as the subextension
                    // cut the module_tab. and the .php parts of the filename
                    // (begining and end)
                    $nameparts = explode('.', $tab);
                    $filename = substr($tab, 0, -4);
                    if (count($nameparts) > 3) {
                        $file = $nameparts[1];
                        if (!isset($all_items[$file])) {
                            $all_items[$file] = array();
                        }
                        $tabArray = &$all_items[$file];
                        $name = $nameparts[2];
                    } else {
                        $tabArray = &$all_items;
                        $name = $nameparts[1];
                    }
                    $tabArray[] = array('name' => ucfirst(str_replace('_', ' ', $name)), 'file' => W2P_BASE_DIR . '/modules/' . $dir . '/' . $filename, 'module' => $dir);
                }
            }
        } else {
            $all_items = &$storage['all_'.$type][$m];
        }
    }
}