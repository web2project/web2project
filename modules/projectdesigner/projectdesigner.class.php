<?php
/**
 * @package     web2project\modules\misc
 * @todo    remove declarations before the class
 */

include W2P_BASE_DIR . '/modules/projectdesigner/config.php';

class CProjectDesigner extends w2p_Core_BaseObject
{

    public $pd_option_id = 0;
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

    protected function hook_preStore()
    {
        $this->pd_option_user = $this->_AppUI->user_id;

        $pd_options = $this->loadAll(null, 'pd_option_user = ' . $this->pd_option_user);
        if (count($pd_options)) {
            foreach($pd_options as $options) {
                $pd_options_id = $options['pd_option_id'];
            }
        }
        $this->pd_option_id = $pd_options_id;

        parent::hook_preStore();
    }

    /*
     * Since these are user-based settings, we should always allow the user
     *   to create/edit settings as needed.
     *
     */
    public function canCreate() {   return true;    }
    public function canEdit()   {   return true;    }
    public function canDelete($notUsed = null, $notUsed2 = null, $notUsed3 = null) {   return true;    }
}