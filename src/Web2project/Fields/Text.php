<?php
namespace Web2project\Fields;

class Text
{
    public function view($value)
    {
        return w2p_textarea($value);
    }

    public function edit($name, $value, $extraTags = 'class="text"')
    {
        return '<input type="text" name="' . $name . '" value="' . $value . '" ' . $extraTags . ' />';
    }
}