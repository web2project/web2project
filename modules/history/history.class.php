<?php
/**
 * @package     web2project\modules\misc
 */

class CHistory extends w2p_Core_BaseObject
{
    public $history_id = null;
    // @todo this should be history_datetime to take advantage of our templating
    public $history_date = null;
    public $history_user = null;
    public $history_action = null;
    public $history_item = null;
    public $history_table = null;
    public $history_project = null;
    public $history_name = null;
    public $history_changes = null;
    public $history_description = null;

    public function __construct() {
        parent::__construct('history', 'history_id');
    }

    /**
     * The history entries are creatable by everyone but not editable or
     *    deletable by anyone. This is to protect its integrity as an audit log.
     *
     * @return boolean
     */
    public function delete()    {   return false;   }
    public function canDelete($notUsed = null, $notUsed2 = null, $notUsed3 = null) {   return false;   }
    public function canCreate() {   return true;    }
    public function canEdit()   {   return false;   }
    public function store()     {   return true;    }

    /**
     * @todo TODO: This should validate that we can actually view this specific
     *    (module-aware) log entry instead of the log entry itself.
     */
    public function canView()   {   return true;    }

    /**
     * @deprecated
     */
    public function show_history()
    {
        return '';
    }
}