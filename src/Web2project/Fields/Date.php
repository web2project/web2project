<?php
namespace Web2project\Fields;

class Date
{
    protected $AppUI = null;
    protected $datename;
    protected $format;

    public function edit($name, $value, $extraTags = '')
    {
        $date = ($value) ? new \w2p_Utilities_Date($value) : null;

        $output = '<input type="hidden" name="'.$name.'" id="'.$name.'" value="' . ($date ? $date->format(FMT_TIMESTAMP_DATE) : '') .'" />';
        $output .= '<input type="text" name="'.$this->datename.'" id="'.$this->datename.'" onchange="setDate_new(\'editFrm\', \''.$this->datename.'\');" value="' . ($date ? $date->format($this->format) : '') . '" class="text" />';
        $output .= '<a href="javascript: void(0);" onclick="return showCalendar(\''.$this->datename.'\', \'' . $this->format . '\', \'editFrm\', null, true, true)">';
        $output .= '<img src="' . w2PfindImage('calendar.gif') . '" alt="' . $this->AppUI->_('Calendar') . '" />';
        $output .= '</a>';

        return $output;
    }

    public function setDateInformation($AppUI, $pieces, $format)
    {
        $this->AppUI = $AppUI;

        unset($pieces[0]);
        $this->datename = implode('_', $pieces);

        $this->format = $format;
    }

    public function view($value)
    {
        return $this->format($value);
    }

    protected function format($value)
    {
        $myDate = intval($value) ? new \w2p_Utilities_Date($value) : null;
        return $myDate ? $myDate->format('%Y-%m-%d') : '-';
    }
}