<?php
namespace Web2project\Fields;

class TextArea extends Text implements \Web2project\Interfaces\Field
{
    public function edit($name, $value, $extraTags = '')
    {
        return '<textarea name="' . $name . '" ' . $extraTags . '>' . $value . '</textarea>';
    }
}
