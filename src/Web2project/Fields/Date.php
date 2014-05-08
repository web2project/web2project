<?php
namespace Web2project\Fields;

class Date
{
    public function edit($name, $value, $extraTags = '')
    {
        return '<input type="text" ' . $extraTags . ' name="' . $name. '" value="' . $value . '" />';
    }
}