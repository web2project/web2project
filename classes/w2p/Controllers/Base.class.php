<?php
/**
 * Parent class to all the controllers
 *
 * @package     web2project\controllers
 */
/**
 * This is the most common Controller and used by nearly every module
 *  throughout the core system.
 *
 * @package     web2project\controllers
 * @author      Keith Casey <caseydk@sourceforge.net>
 */
class w2p_Controllers_Base
{
    /** A boolean on whether or not this should delete */
    protected $delete = false;
    /** A string that prepends the status message going back to the user */
    protected $prefix = '';
    /** The path to use if the action succeeded */
    protected $successPath = '';
    /** The path to use if the action failed */
    protected $errorPath = '';

    /** This is the object actually acted upon */
    public $object = null;
    /** A boolean stating whether the desired action worked or not */
    public $success = false;
    /** Either the $successPath or $errorPath depending on what happened */
    public $resultPath = '';

    public function __construct(w2p_Core_BaseObject $object, $delete,
        $prefix, $successPath, $errorPath)
    {
        $this->object = $object;
        $this->delete = $delete;
        $this->prefix = $prefix;
        $this->successPath = $successPath;
        $this->errorPath = $errorPath;
    }

    public function process(w2p_Core_CAppUI $AppUI, array $myArray)
    {
        if (!$this->object->bind($myArray)) {
            $AppUI->setMsg($this->object->getError(), UI_MSG_ERROR);
            $this->resultPath = $this->errorPath;
            return $AppUI;
        }

        $action = ($this->delete) ? 'deleted' : 'stored';
        $this->success = ($this->delete) ? $this->object->delete() :
            $this->object->store();

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