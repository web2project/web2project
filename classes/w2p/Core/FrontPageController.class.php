<?php

/**
 * @package     w2p\Core
 *
 */

class w2p_Core_FrontPageController
{
    protected $AppUI  = null;
    protected $loader = null;
    protected $includes = array();

    public function __construct($AppUI, $loader)
    {
        $this->AppUI = $AppUI;
        $this->loader = $loader;
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
            $this->includes[] = W2P_BASE_DIR . '/modules/' . $m . '/' . ($u ? ($u . '/') : '') . $loader->makeFileNameSafe($input['dosql']) . '.php';
        }

        return array($m, $a, $u);
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