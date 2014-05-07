<?php
namespace Web2project\Fields;


class TextArea
{
    public function view($value)
    {
        return w2p_textarea($value);
    }

    public function edit($name, $value, $extraTags)
    {
        return '<textarea name="' . $name . '" ' . $extraTags . ' class="customfield">' . $value . '</textarea>';
    }
}