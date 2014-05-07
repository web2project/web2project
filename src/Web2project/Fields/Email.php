<?php
namespace Web2project\Fields;

class Email
{
    public function view($value)
    {
        return w2p_email($value);
    }

    public function edit($name, $value, $extraTags = '')
    {
        return '<input type="text" class="text" name="' . $name . '" value="' . $value . '" ' . $extraTags . ' />';
    }
}