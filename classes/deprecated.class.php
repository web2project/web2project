<?php

/**
 * This is the central location for all deprecated classes within web2project.
 *  When you add a class here, you may also have to update our Autoloader
 *  (includes/main_functions.php) to make sure the old class name still resolves
 *  properly.
 */

/**
 * @package web2project\deprecated
 *
 * @deprecated
 */
class bcode extends CSystem_Bcode
{

    public function __construct()
    {
        parent::__construct();
        trigger_error("bcode has been deprecated in v3.0 and will be removed by v4.0. Please use CSystem_Bcode instead.", E_USER_NOTICE);
    }

}

/**
 * @package web2project\deprecated
 *
 * @deprecated
 */
class budgets extends CSystem_Budget
{

    public function __construct()
    {
        parent::__construct();
        trigger_error("budgets has been deprecated in v3.0 and will be removed by v4.0. Please use CSystem_Budget instead.", E_USER_NOTICE);
    }

}

/**
 * @package web2project\deprecated
 *
 * @deprecated
 */
class CAdmin_User extends CUser
{
    public function __construct()
    {
        parent::__construct();
        trigger_error("CAdmin_User has been deprecated in v3.0 and will be removed by v4.0. Please use CUser instead.", E_USER_NOTICE );
    }
}

/**
 * @package web2project\deprecated
 *
 * @deprecated
 */
class CAppUI extends w2p_Core_CAppUI
{
    public function __construct()
    {
        parent::__construct();
        trigger_error("CAppUI has been deprecated in v3.0 and will be removed by v4.0. Please use w2p_Core_CAppUI instead.", E_USER_NOTICE );
    }
}

/**
 * @package web2project\deprecated
 *
 * @deprecated
 */
class CCalendar extends CEvent
{
    public function __construct()
    {
        parent::__construct();
        trigger_error("CCalendar has been deprecated in v3.0 and will be removed by v4.0. Please use CEvent instead.", E_USER_NOTICE);
    }
}

/**
 * @package web2project\deprecated
 *
 * @deprecated
 */
class CConfig extends w2p_System_Config
{
    public function __construct() {
        parent::__construct();
        trigger_error("CConfig has been deprecated in v3.0 and will be removed by v4.0. Please use w2p_System_Config instead.", E_USER_NOTICE);
    }
}

/**
 * @package web2project\deprecated
 *
 * @deprecated
 */
class CFileFolder extends CFile_Folder
{
    public function __construct()
    {
        parent::__construct();
        trigger_error("CFileFolder has been deprecated in v3.0 and will be removed by v4.0. Please use CFile_Folder instead.", E_USER_NOTICE );
    }
}

/**
 * @package web2project\deprecated
 *
 * @deprecated
 */
class CForumMessage extends CForum_Message
{

    public function __construct()
    {
        parent::__construct();
        trigger_error("CForumMessage has been deprecated in v3.0 and will be removed by v4.0. Please use CForum_Message instead.", E_USER_NOTICE);
    }

}

/**
 * @package web2project\deprecated
 *
 * @deprecated
 */
class CInfoTabBox extends w2p_Theme_InfoTabBox
{
    public function show($extra = '', $js_tabs = false, $alignment = 'left') {
        parent::show($extra, $js_tabs, $alignment);
        trigger_error("CInfoTabBox has been deprecated in v3.0 and will be removed by v4.0. Please use w2p_Theme_InfoTabBox instead.", E_USER_NOTICE);
    }

}

/**
 * @package web2project\deprecated
 *
 * @deprecated
 */
class CMonthCalendar extends w2p_Output_MonthCalendar
{

    public function __construct($date = null)
    {
        parent::__construct($date);
        trigger_error("CMonthCalendar has been deprecated in v3.0 and will be removed by v4.0. Please use w2p_Output_MonthCalendar instead.", E_USER_NOTICE);
    }

}

/**
 * @package web2project\deprecated
 *
 * @deprecated
 */
class CPreferences extends w2p_System_Preferences {
    public function __construct() {
        parent::__construct();
        trigger_error("CPreferences has been deprecated in v3.0 and will be removed by v4.0. Please use w2p_System_Preferences instead.", E_USER_NOTICE );
    }
}

/**
 * @package web2project\deprecated
 *
 * @deprecated
 */
class CProjectDesignerOptions extends CProjectDesigner
{

    public function __construct()
    {
        parent::__construct();
        trigger_error("CProjectDesignerOptions has been deprecated in v3.0 and will be removed by v4.0. Please use CProjectDesigner instead.", E_USER_NOTICE);
    }

}

/**
 * @package web2project\deprecated
 *
 * @deprecated
 */
class CRole extends CSystem_Role
{
    public function __construct($name = '', $description = '') {
        parent::__construct($name, $description);
        trigger_error("CRole has been deprecated in v3.0 and will be removed by v4.0. Please use CSystem_Role instead.", E_USER_NOTICE );
    }
}

/**
 * @package web2project\deprecated
 *
 * @deprecated
 */
class CSysKey extends CSystem_SysKey
{
    public function __construct($name = null, $label = null, $type = '0',
                                    $sep1 = "\n", $sep2 = '|') {
        parent::__construct($name, $label, $type, $sep1, $sep2);
        trigger_error("CSysKey has been deprecated in v3.0 and will be removed by v4.0. Please use CSystem_SysKey instead.", E_USER_NOTICE );
    }
}

/**
 * @package web2project\deprecated
 *
 * @deprecated
 */
class CSysVal extends CSystem_SysVal
{
    public function __construct($key = null, $title = null, $value = null) {
        parent::__construct($key, $title, $value);
        trigger_error("CSysVal has been deprecated in v3.0 and will be removed by v4.0. Please use CSystem_SysVal instead.", E_USER_NOTICE );
    }
}

/**
 * @package web2project\deprecated
 *
 * @deprecated
 */
class CTabBox_core extends w2p_Theme_TabBox
{
    public function __construct($title, $icon = '', $module = '', $helpref = '') {
        parent::__construct($title, $icon, $module, $helpref);
        trigger_error("CTabBox_core has been deprecated in v3.0 and will be removed by v4.0. Please use w2p_Theme_TabBox instead.", E_USER_NOTICE );
    }
}

/**
 * @package web2project\deprecated
 *
 * @deprecated
 */
class CTaskLog extends CTask_Log
{
    public function __construct()
    {
        parent::__construct();
        trigger_error("CTaskLog has been deprecated in v3.0 and will be removed by v4.0. Please use CTask_Log instead.", E_USER_NOTICE );
    }
}

/**
 * @package web2project\deprecated
 *
 * @deprecated
 */
class CTitleBlock extends w2p_Theme_TitleBlock
{
    public function __construct($title, $icon, $module) {
        parent::__construct($title, $icon, $module);
        trigger_error("CTitleBlock has been deprecated in v3.0 and will be removed by v4.0. Please use w2p_Theme_TitleBlock instead.", E_USER_NOTICE );
    }
}

/**
 * @package web2project\deprecated
 *
 * @deprecated
 */
class CTitleBlock_core extends w2p_Theme_TitleBlock
{
    public function __construct($title, $icon = '', $module = '',
                                    $helpref = '') {
        parent::__construct($title, $icon, $module);
        trigger_error("CTitleBlock_core has been deprecated in v3.0 and will be removed by v4.0. Please use w2p_Theme_TitleBlock instead.", E_USER_NOTICE );
    }
}


/**
 * @package web2project\deprecated
 *
 * @deprecated
 */
class w2Pacl extends w2p_Extensions_Permissions
{
    public function __construct($opts = null)
    {
        parent::__construct($opts);
        trigger_error("w2Pacl has been deprecated in v3.0 and will be removed by v4.0. Please use w2p_Extensions_Permissions instead.", E_USER_NOTICE );
    }
}

/**
 * @package web2project\deprecated
 * @deprecated
 */
class w2p_Core_Config extends w2p_System_Config
{
    public function __construct()
    {
        parent::__construct();
        trigger_error(get_class($this) . " has been deprecated in v3.1 and will be removed by v4.0. Please use " . get_parent_class($this) . " instead.", E_USER_NOTICE);
    }
}

/**
 * @package web2project\deprecated
 * @deprecated
 */
class w2p_Core_Dispatcher extends w2p_System_Dispatcher
{
    public function __construct()
    {
        parent::__construct();
        trigger_error(get_class($this) . " has been deprecated in v3.1 and will be removed by v4.0. Please use " . get_parent_class($this) . " instead.", E_USER_NOTICE);
    }
}

/**
 * @package web2project\deprecated
 * @deprecated
 */
class w2p_Core_Event extends w2p_System_Event
{
    public function __construct($resourceName, $eventName, $data=null)
    {
        parent::__construct($resourceName, $eventName, $data);
        trigger_error(get_class($this) . " has been deprecated in v3.1 and will be removed by v4.0. Please use " . get_parent_class($this) . " instead.", E_USER_NOTICE);
    }
}

/**
 * @package web2project\deprecated
 * @deprecated
 */
class w2p_Core_EventQueue extends w2p_System_EventQueue
{
    public function __construct()
    {
        parent::__construct();
        trigger_error(get_class($this) . " has been deprecated in v3.1 and will be removed by v4.0. Please use " . get_parent_class($this) . " instead.", E_USER_NOTICE);
    }
}

/**
 * @package web2project\deprecated
 * @deprecated
 */
class w2p_Core_HookHandler extends w2p_System_HookHandler
{
    public function __construct(w2p_Core_CAppUI $AppUI)
    {
        parent::__construct($AppUI);
        trigger_error(get_class($this) . " has been deprecated in v3.1 and will be removed by v4.0. Please use " . get_parent_class($this) . " instead.", E_USER_NOTICE);
    }
}

/**
 * @package web2project\deprecated
 * @deprecated
 */
class w2p_Core_Module extends w2p_System_Module
{
    public function __construct()
    {
        parent::__construct();
        trigger_error(get_class($this) . " has been deprecated in v3.1 and will be removed by v4.0. Please use " . get_parent_class($this) . " instead.", E_USER_NOTICE);
    }
}

/**
 * @package web2project\deprecated
 * @deprecated
 */
class w2p_Core_Preferences extends w2p_System_Preferences
{
    public function __construct()
    {
        parent::__construct();
        trigger_error(get_class($this) . " has been deprecated in v3.1 and will be removed by v4.0. Please use " . get_parent_class($this) . " instead.", E_USER_NOTICE);
    }
}

/**
 * @package web2project\deprecated
 * @deprecated
 */
class w2p_Core_Setup extends w2p_System_Setup
{
    public function __construct(w2p_Core_CAppUI $AppUI = null,
                                array $config = null, w2p_Database_Query $query = null)
    {
        parent::__construct($AppUI, $config, $query);
        trigger_error(get_class($this) . " has been deprecated in v3.1 and will be removed by v4.0. Please use " . get_parent_class($this) . " instead.", E_USER_NOTICE);
    }
}

/**
 * @package web2project\deprecated
 * @deprecated
 */
class w2p_Core_UpgradeManager extends w2p_System_UpgradeManager
{
    public function __construct()
    {
        parent::__construct();
        trigger_error(get_class($this) . " has been deprecated in v3.1 and will be removed by v4.0. Please use " . get_parent_class($this) . " instead.", E_USER_NOTICE);
    }
}

/**
 * @package     web2project\deprecated
 * @deprecated  since version 3.0
 */
class w2p_API_iCalendar extends w2p_Output_iCalendar
{
    public static function formatCalendarItem($calendarItem, $module_name)
    {
        trigger_error("w2p_API_iCalendar has been deprecated in v3.0 and will be removed by v4.0. Please use w2p_Output_iCalendar instead.", E_USER_NOTICE);

        return parent::formatCalendarItem($calendarItem, $module_name);
    }
}

/**
 * @package     web2project\deprecated
 * @deprecated  since version 3.0
 */
class smartsearch extends CSmartSearch {

    public function __construct() {
        trigger_error("smartsearch has been deprecated in v3.0 and will be removed by v4.0. Please use CSmartSearch instead.", E_USER_NOTICE );
    }
}

/**
 * @package     web2project\deprecated
 * @deprecated  since version 4.0
 */
class w2p_Controller_Base extends \Web2project\Actions\AddEdit
{
    public function __construct($object, $delete, $prefix, $successPath, $errorPath)
    {
        trigger_error("w2p_Controller_Base has been deprecated in v4.0 and will be removed by v5.0. Please use \\Web2project\\Actions\\AddEdit instead.", E_USER_NOTICE );

        parent::__construct($object, $delete, $prefix, $successPath, $errorPath);
    }
}

/**
 * @package     web2project\deprecated
 * @deprecated  since version 4.0
 */
class w2p_Controller_Permissions extends \Web2project\Actions\AddEditPermissions
{
    public function __construct($object, $delete, $prefix, $successPath, $errorPath)
    {
        trigger_error("w2p_Controller_Permissions has been deprecated in v4.0 and will be removed by v5.0. Please use \\Web2project\\Actions\\AddEditPermissions instead.", E_USER_NOTICE );

        parent::__construct($object, $delete, $prefix, $successPath, $errorPath);
    }
}

/**
 * @package     web2project\deprecated
 * @deprecated  since version 4.0
 */
class w2p_Output_EmailManager extends w2p_Output_Email_Manager
{
    public function __construct(w2p_Core_CAppUI $AppUI = null)
    {
        trigger_error("w2p_Output_EmailManager has been deprecated in v4.0 and will be removed by v5.0. Please use w2p_Output_Email_Manager instead.", E_USER_NOTICE );

        parent::__construct($AppUI);
    }
}

/**
 * @package     web2project\deprecated
 * @deprecated  since version 4.0
 */
class w2p_Output_Email_Template extends \Web2project\Output\Email\Manager
{
    public function __construct()
    {
        trigger_error("w2p_Output_Email_Template has been deprecated in v4.0 and will be removed by v5.0. Please use \\Web2project\\Output\\Email\\Manager instead.", E_USER_NOTICE );

        parent::__construct();
    }
}

/**
 * @package     web2project\deprecated
 * @deprecated  since version 4.0
 */
class w2p_Utilities_Paginator extends \Web2project\Utilities\Paginator
{
    public function __construct(array $items, $pagesize = 0)
    {
        trigger_error("w2p_Utilities_Paginator has been deprecated in v4.0 and will be removed by v5.0. Please use \\Web2project\\Utilities\\Paginator instead.", E_USER_NOTICE );

        parent::__construct($items, $pagesize);
    }
}

class w2p_Actions_ImportTasks extends w2p_Actions_BulkTasks
{
    public function __construct()
    {
        trigger_error("w2p_Actions_ImportTasks has been deprecated in v4.0 and will be removed by v5.0. Please use w2p_Actions_BulkTasks instead.", E_USER_NOTICE );

        parent::__construct();
    }
}

class w2p_Theme_InfoTabBox extends w2p_Theme_TabBox
{
    public function __construct($baseHRef = '', $baseInc = '', $active = 0, $javascript = null)
    {
        trigger_error( __CLASS__ . " has been deprecated in v4.0 and will be removed by v5.0. Please use theme-specific tab boxes instead.", E_USER_NOTICE );

        parent::__construct($baseHRef, $baseInc, $active, $javascript);
    }
}