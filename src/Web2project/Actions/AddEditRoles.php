<?php

/**
 * Class Roles
 * @package     web2project\Actions
 * @author      Keith Casey <caseydk@sourceforge.net>
 */
class AddEditRoles extends \Web2project\Actions\AddEdit
{
    public function process(\w2p_Core_CAppUI $AppUI, array $myArray)
    {
        if (!canEdit('roles')) {
            $this->resultPath = ACCESS_DENIED;

            return $AppUI;
        }

        $action = ($this->delete) ? 'deleted' : 'stored';
        $this->success = ($this->delete) ?
            $this->object->delete() :
            $this->object->store();

        if ($this->success) {
            $AppUI->setMsg($this->prefix.' '.$action, UI_MSG_OK, true);
            $copy_role_id = (int) w2PgetParam($myArray, 'copy_role_id', 0);

            if ($copy_role_id) {
                $this->object->copyPermissions($copy_role_id, $this->object->role_id);
            }

        } else {
            $AppUI->setMsg($this->object->getError(), UI_MSG_ERROR);
        }

        $this->resultPath = $this->successPath;

        return $AppUI;
    }
}
