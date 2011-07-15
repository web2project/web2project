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

        if (is_array($this->success) || !$this->success) {
            $AppUI->holdObject($this->object);
            /*
             * TODO: This nasty structure was introduced in v3.0 and is only
             *   transitional while the individual modules are updated to
             *   stop using $this->success as both a boolean and the error array.
             *   -- This was due to a bad design decision on my part. -caseydk
             */
            if (is_array($this->object->getError())) {
                $AppUI->setMsg($this->object->getError(), UI_MSG_ERROR);
            } else {
                $AppUI->setMsg($this->success, UI_MSG_ERROR);
            }
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