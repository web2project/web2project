<?php
namespace Web2project\Fields;

class Select
{
    protected $options;

    public function view($value)
    {
        return $value;
    }

    public function edit($name, $value, $extraTags = '')
    {
        $html = '<select name="' . $name . '" class="text" ' . $extraTags . '>';
        foreach ($this->options as $i => $opt) {
            $html .= "\t" . '<option value="' . $i . '"';
            if ($i == $value) {
                $html .= ' selected="selected" ';
            }
            $html .= '>' . $opt . '</option>';
        }
        $html .= '</select>';
        return $html;
    }

    public function setOptions($options)
    {
        $this->options = $options;
    }
}