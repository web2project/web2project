<?php /* $Id$ $URL$ */

//Lets require the main classes needed
include_once (W2P_BASE_DIR . '/modules/projectdesigner/config.php');

/**
 * CProjectDesignerOptions Class
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

    public function store()
    {
        $q = $this->_getQuery();
        $q->addTable('project_designer_options');
        $q->addReplace('pd_option_user', $this->pd_option_user);
        $q->addReplace('pd_option_view_project', $this->pd_option_view_project);
        $q->addReplace('pd_option_view_gantt', $this->pd_option_view_gantt);
        $q->addReplace('pd_option_view_tasks', $this->pd_option_view_tasks);
        $q->addReplace('pd_option_view_actions', $this->pd_option_view_actions);
        $q->addReplace('pd_option_view_addtasks', $this->pd_option_view_addtasks);
        $q->addReplace('pd_option_view_files', $this->pd_option_view_files);
        $q->addWhere('pd_option_user = ' . (int) $this->pd_option_user);
        $q->exec();

        return true;
    }

}

/**
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