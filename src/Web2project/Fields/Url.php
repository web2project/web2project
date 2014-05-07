<?php
namespace Web2project\Fields;

class Url extends Text
{
    public function view($value)
    {
        $value = str_replace(array('"', '"', '<', '>'), '', $value);
        return w2p_url($value);
    }
}