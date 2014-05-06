<?php

function web2project_autoload($className) {
    $library_name = 'w2p_';

    /**
     * This portion of the autoloader is non-standard and exists only to catch our deprecated classes.
     */
    switch($className)
    {
        case 'w2p_API_iCalendar':
        case 'w2p_Core_Config':
        case 'w2p_Core_Dispatcher':
        case 'w2p_Core_Event':
        case 'w2p_Core_EventQueue':
        case 'w2p_Core_HookHandler':
        case 'w2p_Core_Module':
        case 'w2p_Core_Preferences':
        case 'w2p_Core_Setup':
        case 'w2p_Core_UpgradeManager':
            return include W2P_BASE_DIR . '/classes/deprecated.class.php';
        default:
            //fall through
    }

    if (substr($className, 0, strlen($library_name)) != $library_name) {
        return false;
    }
    $file = str_replace('_', '/', $className);
    $file = str_replace('w2p/', '', $file);
    return include dirname(__FILE__) . "/$file.class.php";
}