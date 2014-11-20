<?php

/**
 * This is the central location for all deprecated classes within web2project.
 *  When you add a class here, you may also have to update our Autoloader
 *  (includes/main_functions.php) to make sure the old class name still resolves
 *  properly.
 */

/**
 * @package     web2project\deprecated
 * @deprecated  since version 4.0
 * @codeCoverageIgnore
 */
class w2p_Theme_InfoTabBox extends w2p_Theme_TabBox
{
    public function __construct($baseHRef = '', $baseInc = '', $active = 0, $javascript = null)
    {
        trigger_error( __CLASS__ . " has been deprecated in v4.0 and will be removed by v5.0. Please use theme-specific tab boxes instead.", E_USER_NOTICE );

        parent::__construct($baseHRef, $baseInc, $active, $javascript);
    }
}

/**
 * @package     web2project\deprecated
 * @deprecated  since version 4.0
 * @codeCoverageIgnore
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
 * @codeCoverageIgnore
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
 * @codeCoverageIgnore
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
 * @codeCoverageIgnore
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
 * @codeCoverageIgnore
 */
class w2p_Utilities_Paginator extends \Web2project\Utilities\Paginator
{
    public function __construct(array $items, $pagesize = 0)
    {
        trigger_error("w2p_Utilities_Paginator has been deprecated in v4.0 and will be removed by v5.0. Please use \\Web2project\\Utilities\\Paginator instead.", E_USER_NOTICE );

        parent::__construct($items, $pagesize);
    }
}

/**
 * @package     web2project\deprecated
 * @deprecated  since version 4.0
 * @codeCoverageIgnore
 */
class w2p_Actions_ImportTasks extends w2p_Actions_BulkTasks
{
    public function __construct()
    {
        trigger_error("w2p_Actions_ImportTasks has been deprecated in v4.0 and will be removed by v5.0. Please use w2p_Actions_BulkTasks instead.", E_USER_NOTICE );

        parent::__construct();
    }
}

/**
 * @package     web2project\deprecated
 * @deprecated  since version 4.0
 * @codeCoverageIgnore
 */
class w2p_Mocks_Email extends \Web2project\Mocks\Email { }

/**
 * @package     web2project\deprecated
 * @deprecated  since version 4.0
 * @codeCoverageIgnore
 */
class w2p_Mocks_Permissions extends \Web2project\Mocks\Permissions { }

/**
 * @package     web2project\deprecated
 * @deprecated  since version 4.0
 * @codeCoverageIgnore
 */
class w2p_Mocks_Query extends \Web2project\Mocks\Query { }

/**
 * @package     web2project\deprecated
 * @deprecated  since version 4.0
 * @codeCoverageIgnore
 *
 * @todo At some point, these should throw a deprecation notice
 */
class w2p_Utilities_Date extends \Web2project\Utilities\Date { }

/**
 * @package     web2project\deprecated
 * @deprecated  since version 4.0
 * @codeCoverageIgnore
 */
class w2p_Controllers_View extends \Web2project\Controllers\View { }

