<?php
namespace Web2project\Fields;

class TextArea extends Text
{
    public function edit($name, $value, $extraTags = '')
    {
        return '<textarea name="' . $name . '" ' . $extraTags . ' class="customfield">' . $value . '</textarea>';
    }
}