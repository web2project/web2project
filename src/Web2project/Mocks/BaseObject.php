<?php
namespace Web2project\Mocks;
/**
 * @package     Web2project\Mocks
 * @author      D. Keith Casey, Jr. <caseydk@users.sourceforge.net>
 */

class BaseObject extends \w2p_Core_BaseObject
{
    public $deletable  = true;
    public $viewable   = true;
    public $editable   = true;
    public $accessible = true;

    public function __construct()
    {
        parent::__construct('no table', 'no key');
    }

    public function canDelete()
    {
        return $this->deletable;
    }

    public function canView()
    {
        return $this->viewable;
    }

    public function canEdit()
    {
        return $this->editable;
    }

    public function canAccess()
    {
        return $this->accessible;
    }
}