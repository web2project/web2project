<?php

/**
 * Class Role Permissions
 * @package Web2project\Controllers
 */
class AddEditRolePermissions extends \Web2project\Actions\AddEdit
{
    public function process(\w2p_Core_CAppUI $AppUI, array $myArray)
    {
        if (!canEdit('system')) {
            $this->resultPath = ACCESS_DENIED;
            return $AppUI;
        }

        $action = ($this->delete) ? 'deleted' : 'stored';
        $this->success = ($this->delete) ?
            $this->object->del_acl((int) $myArray['permission_id']) :
            $this->object->addRolePermission();

        if ($this->success) {
            $AppUI->setMsg($this->prefix.' '.$action, UI_MSG_OK, true);

            if ($this->delete) {
                $this->object->removeACLPermissions(w2PgetParam($_POST, 'permission_id', null));
            }
        } else {
            $AppUI->setMsg($this->object->getError(), UI_MSG_ERROR);
        }

        $this->resultPath = $this->successPath;

        return $AppUI;
    }
}