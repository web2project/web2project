<?php

/**
 * @package     w2p\Core
 *
 */

class w2p_Core_FrontPageController
{
    protected $AppUI  = null;
    protected $loader = null;

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
            if (!$perms->checkModule($config['default_view_m'], 'view', $AppUI->user_id)) {
                $m = 'public';
                $def_a = 'welcome';
            } else {
                $m = $config['default_view_m'];
                $def_a = !empty($config['default_view_a']) ? $config['default_view_a'] : $def_a;
                $tab = $config['default_view_tab'];
                $input['tab'] = $tab;
            }
        }

        $loader = new w2p_FileSystem_Loader();
        $m = $loader->makeFileNameSafe(w2PgetParam($input, 'm', 'public'));
        $a = $loader->makeFileNameSafe(w2PgetParam($input, 'a', $def_a));
        /**
         * This check for $u implies that a file located in a subdirectory of higher depth than 1 in relation to the module base
         *   can't be executed. So it wouldn't be possible to run for example the file module/directory1/directory2/file.php
         */
        $u = $loader->makeFileNameSafe(w2PgetParam($input, 'u', ''));

        if ($m == 'projects' && $a == 'view' && $config['projectdesigner_view_project'] && !w2PgetParam($input, 'bypass') && !(isset($input['tab']))) {
            if ($AppUI->isActiveModule('projectdesigner')) {
                $m = 'projectdesigner';
                $a = 'index';
            }
        }

        if ($u && file_exists(W2P_BASE_DIR . '/modules/' . $m . '/' . $u . '/' . $u . '.class.php')) {
            include W2P_BASE_DIR . '/modules/' . $m . '/' . $u . '/' . $u . '.class.php';
        }

// include the module ajax file - we use file_exists instead of @ so  that any parse errors in the file are reported,
//   rather than errors further down the track.
        $modajax = $this->AppUI->getModuleAjax($m);
        if (file_exists($modajax)) {
            include $modajax;
        }
        if ($u && file_exists(W2P_BASE_DIR . '/modules/' . $m . '/' . $u . '/' . $u . '.ajax.php')) {
            include W2P_BASE_DIR . '/modules/' . $m . '/' . $u . '/' . $u . '.ajax.php';
        }

// do some db work if dosql is set
// TODO - MUST MOVE THESE INTO THE MODULE DIRECTORY
        if (isset($input['dosql'])) {
            require W2P_BASE_DIR . '/modules/' . $m . '/' . ($u ? ($u . '/') : '') . $loader->makeFileNameSafe($input['dosql']) . '.php';
        }

        return array($m, $a, $u);
    }

    public function loadIncludes()
    {

    }
}