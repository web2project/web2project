<?php
namespace Web2project\Fields;

class Checkbox implements \Web2project\Interfaces\Field
{
    public function view($value)
    {
        return $value;
    }

    public function edit($name, $value, $extraTags = '')
    {
        return '<input type="checkbox" name="' . $name . '" value="1" ' . $value . $extraTags . '/>';
    }
}
