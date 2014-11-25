<?php
namespace Web2project\Fields;

class Date implements \Web2project\Interfaces\Field
{
    protected $AppUI = null;
    protected $datename;
    protected $format;

    public function __construct($AppUI)
    {
        $this->AppUI = $AppUI;
        $this->format = $this->AppUI->getPref('SHDATEFORMAT');
    }

    public function edit($name, $value, $extraTags = '')
    {
        $date = ($value) ? new \w2p_Utilities_Date($value) : null;

        $output = '<input type="hidden" name="'.$name.'" id="'.$name.'" value="' . ($date ? $date->format(FMT_TIMESTAMP_DATE) : '') .'" />';
        $output .= '<input type="text" name="'.$this->datename.'" id="'.$this->datename.'" onchange="setDate_new(\'editFrm\', \''.$this->datename.'\');" value="' . ($date ? $date->format($this->format) : '') . '" class="text" />';
        $output .= '<a href="javascript: void(0);" onclick="return showCalendar(\''.$this->datename.'\', \'' . $this->format . '\', \'editFrm\', null, true, true)">';
        $output .= '<img style="vertical-align: middle" src="' . w2PfindImage('calendar.gif') . '" alt="' . $this->AppUI->_('Calendar') . '" />';
        $output .= '</a>';

        return $output;
    }

    public function setDateInformation($pieces)
    {
        unset($pieces[0]);
        $this->datename = implode('_', $pieces);
    }

    public function view($value)
    {
        return $this->format($value);
    }

    protected function format($value)
    {
        $myDate = intval($value) ? new \w2p_Utilities_Date($value) : null;

        return $myDate ? $myDate->format($this->format) : '-';
    }
}
