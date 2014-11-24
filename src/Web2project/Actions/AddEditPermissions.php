<?php
namespace Web2project\Actions;

/**
 * Class Permissions
 * @package     web2project\Actions
 * @author      Keith Casey <caseydk@sourceforge.net>
 */
class AddEditPermissions extends AddEdit
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
            $AppUI->setMsg('Modifying the permissions was not successful', UI_MSG_ERROR);
            $this->resultPath = $this->errorPath;

            $AppUI->holdObject($this->object);
        }

        return $AppUI;
    }
}
