<?php
namespace Web2project\Fields;

class Url
{
    public function view($value)
    {
        $value = str_replace(array('"', '"', '<', '>'), '', $value);
        return w2p_url($value);
    }

    public function edit($name, $value, $extraTags)
    {
        return '<input type="text" class="text" name="' . $name . '" value="' . $value . '" ' . $extraTags . ' />';
    }
}