<?php
/**
 * Class Permissions
 * @package Web2project\Controllers
 */
class w2p_Controllers_Permissions extends w2p_Controllers_Base
{
    public function process(\w2p_Core_CAppUI $AppUI, array $myArray)
    {
        if (!canEdit('users')) {
            $this->resultPath = ACCESS_DENIED;
            return $AppUI;
        }

        $action = ($this->delete) ? 'deleted' : 'stored';
        $this->success = ($this->delete) ?
            $this->object->del_acl((int) $myArray['permission_id']) :
            $this->object->addUserPermission();

        if ($this->success) {
            $AppUI->setMsg($this->prefix.' '.$action, UI_MSG_OK, true);
            $this->resultPath = $this->successPath;

            $this->object->recalcPermissions(null, (int) $myArray['permission_user']);
        } else {
            $AppUI->setMsg($this->object->getError(), UI_MSG_ERROR);
            $this->resultPath = $this->errorPath;

            $AppUI->holdObject($this->object);
        }

        return $AppUI;
    }
}