<?php

class w2p_Controllers_View
{
    protected $AppUI  = null;
    protected $object = null;
    protected $noun   = '';
    protected $action = '';
    protected $dosql  = '';
    protected $key    = '';
    protected $fields = '';

    public function __construct(w2p_Core_CAppUI $AppUI, w2p_Core_BaseObject $object, $noun)
    {
        $this->AppUI = $AppUI;
        $this->object = $object;
        $this->noun = $noun;

        $this->action = '?m=' . w2p_pluralize(strtolower($noun));
        $this->dosql  = 'do_' . strtolower($noun) . '_aed';
        $this->key    = strtolower($noun) . '_id';
    }

    public function renderDelete()
    {
        $output = '';

        if ($this->object->canDelete()) {
            $output = "<script language=\"javascript\" type=\"text/javascript\">function delIt()";
            $output .= "{if (confirm('" . $this->AppUI->_('doDelete') . ' ' . $this->noun . "?')){";
            $output .= 'document.frmDelete.submit();';
            $output .= '}}</script>';
            $output .= '<form name="frmDelete" action="' . $this->action . '" method="post" accept-charset="utf-8">';
            $output .= '<input type="hidden" name="dosql" value="' . $this->dosql . '" />';
            $output .= '<input type="hidden" name="del" value="1" />';
            $output .= $this->fields;
            $output .= '<input type="hidden" name="' . $this->key . '" value="' . $this->object->getId() . '" />';
            $output .= '</form>';
        }

        return $output;
    }

    public function setDoSQL($dosql)
    {
        $this->dosql = $dosql;
    }
    public function setKey($key)
    {
        $this->key = $key;
    }

    public function addField($name, $value)
    {
        $this->fields .= '<input type="hidden" name="' . $name . '" value="' . $value . '" />';
    }
}