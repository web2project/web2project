<?php /* $Id$ $URL$ */

//Lets require the main classes needed
include_once (W2P_BASE_DIR . '/modules/projectdesigner/config.php');

/**
 * CProjectDesigner Class
 */
class CProjectDesigner extends w2p_Core_BaseObject
{

    public $pd_option_id = null;
    public $pd_option_user = null;
    public $pd_option_view_project = null;
    public $pd_option_view_gantt = null;
    public $pd_option_view_tasks = null;
    public $pd_option_view_actions = null;
    public $pd_option_view_addtasks = null;
    public $pd_option_view_files = null;

    public function __construct()
    {
        parent::__construct('project_designer_options', 'pd_option_id');
    }

    /*
     * Since these are user-based settings, we should always allow the user
     *   to create/edit settings as needed.
     *
     */
    public function canCreate() {   return true;    }
    public function canEdit()   {   return true;    }
    public function canDelete() {   return true;    }
}