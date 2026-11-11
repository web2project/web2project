<?php
/**
 * Class w2p_Output_HTML_Base
 *
 * @package web2project\output\html
 * @author  D. Keith Casey, Jr. <contrib@caseysoftware.com>
 */
abstract class w2p_Output_HTML_Base
{
    protected $AppUI = null;
    public $df = null;
    protected $dtf = null;

    public function __construct($AppUI)
    {
        $this->AppUI = $AppUI;
        $this->df     = $AppUI->getPref('SHDATEFORMAT');
        $this->dtf    = $this->df . ' ' . $AppUI->getPref('TIMEFORMAT');
    }

    /**
     * Escapes text for use in HTML content or a quoted attribute.
     *
     * @param mixed $text
     *
     * @return string
     */
    protected function escape($text)
    {
        return htmlspecialchars((string) $text, ENT_QUOTES, 'UTF-8');
    }

    public function addLabel($label)
    {
        return '<label>' . $this->AppUI->_($label) . ':</label>';
    }

    public function showLabel($label)
    {
        echo $this->addLabel($label);
    }
}