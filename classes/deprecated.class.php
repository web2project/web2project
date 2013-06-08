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
    public function __construct($date = null)
    {
        parent::__construct($date);
        trigger_error("CCalendar has been deprecated in v3.0 and will be removed by v4.0. Please use CEvent instead.", E_USER_NOTICE);
    }
}

/**
 * @package web2project\deprecated
 *
 * @deprecated
 */
class CConfig extends w2p_Core_Config
{
    public function __construct() {
        parent::__construct();
        trigger_error("CConfig has been deprecated in v3.0 and will be removed by v4.0. Please use w2p_Core_Config instead.", E_USER_NOTICE);
    }
}

/**
 * @package web2project\deprecated
 *
 * @deprecated
 */
class CDate extends w2p_Utilities_Date
{
    public function __construct($datetime = null, $tz = '')
    {
        parent::__construct($datetime, $tz);
        trigger_error("CDate has been deprecated in v2.0 and will be removed by v4.0. Please use w2p_Utilities_Date instead.", E_USER_NOTICE );
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
class CPreferences extends w2p_Core_Preferences {
	public function __construct() {
		parent::__construct();
        trigger_error("CPreferences has been deprecated in v3.0 and will be removed by v4.0. Please use w2p_Core_Preferences instead.", E_USER_NOTICE );
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
    public function __construct() {
        parent::__construct($title, $icon, $module, $helpref);
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
		parent::__construct($title, $icon, $module, $helpref);
        trigger_error("CTitleBlock_core has been deprecated in v3.0 and will be removed by v4.0. Please use w2p_Theme_TitleBlock instead.", E_USER_NOTICE );
	}
}

/**
 * @package web2project\deprecated
 *
 * @deprecated
 */
class CW2pObject extends w2p_Core_BaseObject
{
	public function __construct($table, $key, $module = '')
	{
		parent::__construct($table, $key, $module);
		trigger_error("CW2pObject has been deprecated in v2.0 and will be removed by v4.0. Please use w2p_Core_BaseObject instead.", E_USER_NOTICE );
	}
}

/**
 * @package web2project\deprecated
 *
 * @deprecated
 */
class CustomFields extends w2p_Core_CustomFields
{
    public function __construct($m, $a, $obj_id = null, $mode = 'edit',
                                $published = 0)
    {
        parent::__construct($m, $a, $obj_id, $mode, $published);
        trigger_error("CustomFields has been deprecated in v2.0 and will be removed by v4.0. Please use w2p_core_CustomFields instead.", E_USER_NOTICE );
    }
}

/**
 * @package web2project\deprecated
 *
 * @deprecated
 */
class DBQuery extends w2p_Database_Query
{
    public function __construct($prefix = null, $query_db = null)
    {
        parent::__construct($prefix, $query_db);
        trigger_error("DBQuery has been deprecated in v2.0 and will be removed by v4.0. Please use w2p_Database_Query instead.", E_USER_NOTICE );
    }
}

/**
 * @package web2project\deprecated
 *
 * @deprecated
 */
class Mail extends w2p_Utilities_Mail
{
    public function __construct()
    {
        parent::__construct();
        trigger_error("Mail has been deprecated in v2.0 and will be removed by v4.0. Please use w2p_Utilities_Mail instead.", E_USER_NOTICE );
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
 *
 * @deprecated
 */
class w2PajaxResponse extends w2p_Extensions_AjaxResponse
{
    public function addCreateOptions($sSelectId, $options)
    {
        parent::addCreateOptions($sSelectId, $options);
        trigger_error("w2PajaxResponse has been deprecated in v2.3 and will be removed by v4.0. Please use w2p_Extensions_AjaxResponse instead.", E_USER_NOTICE );
    }
}