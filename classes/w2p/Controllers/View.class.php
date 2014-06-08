<?php

class w2p_Controllers_View
{
    protected $AppUI  = null;
    protected $noun   = '';
    protected $action = '';
    protected $dosql  = '';
    protected $key    = '';

    public function __construct(w2p_Core_CAppUI $AppUI, $noun)
    {
        $this->AppUI = $AppUI;
        $this->noun = $noun;

        $this->action = '?m=' . w2p_pluralize(strtolower($noun));
        $this->dosql  = 'do_' . strtolower($noun) . '_aed';
        $this->key    = strtolower($noun) . '_id';
    }

    public function renderDelete(w2p_Core_BaseObject $object)
    {
        $output = '';

        if ($object->canDelete()) {
            $output = "<script language=\"javascript\" type=\"text/javascript\">function delIt()";
            $output .= "{if (confirm('" . $this->AppUI->_('doDelete') . ' ' . $this->noun . "?')){";
            $output .= 'document.frmDelete.submit();';
            $output .= '}}</script>';
            $output .= '<form name="frmDelete" action="' . $this->action . '" method="post" accept-charset="utf-8">';
            $output .= '<input type="hidden" name="dosql" value="' . $this->dosql . '" />';
            $output .= '<input type="hidden" name="del" value="1" />';
            $output .= '<input type="hidden" name="' . $this->key . '" value="' . $object->getId() . '" />';
            $output .= '</form>';
        }

        return $output;
    }
}