<?php /* $Id$ $URL$ */

/**
 *	@package web2project
 *	@subpackage controllers
 *	@version $Revision$
 */

/**
 *	w2p_Controllers_Base Class.
 *
 *	Parent class to all the controllers
 *	@author Keith Casey <caseydk@sourceforge.net>
 *
 */
class w2p_Controllers_Base
{
    protected $delete = false;
    protected $prefix = '';
    protected $successPath = '';
    protected $errorPath = '';

    public $object = null;
    public $success = false;
    public $resultPath = '';

    public function __construct(w2p_Core_BaseObject $object, $delete,
             $prefix, $successPath, $errorPath) {
        $this->object = $object;
        $this->delete = $delete;
        $this->prefix = $prefix;
        $this->successPath = $successPath;
        $this->errorPath = $errorPath;
    }

    public function process(w2p_Core_CAppUI $AppUI, array $myArray) {

        if (!$this->object->bind($myArray)) {
            $AppUI->setMsg($this->object->getError(), UI_MSG_ERROR);
            $this->resultPath = $this->errorPath;
            return $AppUI;
        }

        $action = ($this->delete) ? 'deleted' : 'stored';
        $this->success = ($this->delete) ? $this->object->delete() : $this->object->store();

        if ($this->success) {
            $AppUI->setMsg($this->prefix.' '.$action, UI_MSG_OK, true);
            $this->resultPath = $this->successPath;
        } else {
            $AppUI->holdObject($this->object);
            $AppUI->setMsg($this->object->getError(), UI_MSG_ERROR);

            $this->resultPath = $this->errorPath;
        }

        return $AppUI;
    }
}