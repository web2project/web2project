<?php

/**
 * @package     w2p\Output
 * @author      D. Keith Casey, Jr. <caseydk@users.sourceforge.net>
 */

class w2p_Output_PageHandler
{
    public function __construct($AppUI, $loader = null)
    {
        $this->AppUI = $AppUI;
        $this->loader = is_null($loader) ? new w2p_FileSystem_Loader() : $loader;
    }

    public function resolveParameters($config, $input)
    {
        /**
         * TODO: We should validate that the module identified by $m is actually
         *   installed & active. If not, we should go back to the defaults.
         */
        $perms = $this->AppUI->acl();
        $def_a = 'index';
        if (!isset($input['m']) && !empty($config['default_view_m'])) {
            if (!$perms->checkModule($config['default_view_m'], 'view', $this->AppUI->user_id)) {
                $def_m = 'public';
                $def_a = 'welcome';
            } else {
                $def_m = $config['default_view_m'];
                $def_a = !empty($config['default_view_a']) ? $config['default_view_a'] : $def_a;
                $tab = $config['default_view_tab'];
                $input['tab'] = $tab;
            }
        }

        $m = $this->loader->makeFileNameSafe(w2PgetParam($input, 'm', $def_m));
        $a = $this->loader->makeFileNameSafe(w2PgetParam($input, 'a', $def_a));
        /**
         * This check for $u implies that a file located in a subdirectory of higher depth than 1 in relation to the module base
         *   can't be executed. So it wouldn't be possible to run for example the file module/directory1/directory2/file.php
         */
        $u = $this->loader->makeFileNameSafe(w2PgetParam($input, 'u', ''));

        if ($m == 'projects' && $a == 'view' && $config['projectdesigner_view_project']) {
            if ($this->AppUI->isActiveModule('projectdesigner')) {
                $m = 'projectdesigner';
                $a = 'index';
            }
        }

        $this->includes[] = $this->AppUI->getModuleAjax($m);
        if ($u) {
            $this->includes[] = W2P_BASE_DIR . '/modules/' . $m . '/' . $u . '/' . $u . '.ajax.php';
            $this->includes[] = W2P_BASE_DIR . '/modules/' . $m . '/' . $u . '/' . $u . '.class.php';
        }

        if (isset($input['dosql'])) {
            $this->includes[] = W2P_BASE_DIR . '/modules/' . $m . '/' . ($u ? ($u . '/') : '') . $this->loader->makeFileNameSafe($input['dosql']) . '.php';
        }

        return array($m, $a, $u);
    }

    public function loadExtras(array &$storage, w2p_Core_CAppUI $AppUI, $m, $type = 'tabs')
    {
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
            foreach ($AppUI->getActiveModules() as $dir => $notUsed) {
                if (!canAccess($dir)) {
                    continue;
                }
                $modules_items = $this->loader->readFiles(W2P_BASE_DIR . '/modules/' . $dir . '/',
                        '^' . $m . '_'.substr($type, 0, -1).'.*\.php');
                foreach ($modules_items as $item) {
                    // Get the name as the subextension
                    // cut the module_tab. and the .php parts of the filename
                    // (begining and end)
                    $nameparts = explode('.', $item);
                    $filename = substr($item, 0, -4);
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
        }
    }

    public function loadIncludes()
    {
        foreach($this->includes as $include) {
            if (file_exists($include)) {
                include $include;
            }
        }
    }
}