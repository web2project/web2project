<?php
namespace Web2project\Fields;

class DateTime extends Date implements \Web2project\Interfaces\Field
{
    public function __construct($AppUI)
    {
        $this->AppUI = $AppUI;
        $this->format = $this->AppUI->getPref('SHDATEFORMAT');
        $this->format .= ' ' . $AppUI->getPref('TIMEFORMAT');
    }

    protected function format($value)
    {
        $myDate = intval($value) ? new \w2p_Utilities_Date($this->AppUI->formatTZAwareTime($value, '%Y-%m-%d %T')) : null;

        return $myDate ? $myDate->format($this->format) : '-';
    }
}
