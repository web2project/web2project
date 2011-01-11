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
    protected $accessDeniedPath = 'm=public&a=access_denied';

    public $object = null;
    public $success = false;
    public $resultPath = '';
    public $resultMessage = '';

    public function __construct(w2p_Core_BaseObject $object, $delete,
             $prefix, $successPath, $errorPath) {
        $this->object = $object;
        $this->delete = $delete;
        $this->prefix = $prefix;
        $this->successPath = $successPath;
        $this->errorPath = $errorPath;
    }

    public function setAccessDeniedPath($path) {
        $this->accessDeniedPath = $path;
    }

    public function process(CAppUI $AppUI, array $myArray) {

        if (!$this->object->bind($myArray)) {
            $AppUI->setMsg($this->object->getError(), UI_MSG_ERROR);
            $this->resultPath = $this->errorPath;
            return $AppUI;
        }

        $action = ($this->delete) ? 'deleted' : 'stored';
        $this->success = ($this->delete) ? $this->object->delete($AppUI) : $this->object->store($AppUI);

        if (is_array($this->success)) {
            $AppUI->holdObject($this->object);
            $AppUI->setMsg($this->object->getError(), UI_MSG_ERROR);
            $this->resultPath = $this->errorPath;
            return $AppUI;
        }
        if ($this->success) {
            $AppUI->setMsg($this->prefix.' '.$action, UI_MSG_OK, true);
            $this->resultPath = $this->successPath;
        } else {
            $this->resultPath = $this->accessDeniedPath;
        }
        return $AppUI;
    }
}