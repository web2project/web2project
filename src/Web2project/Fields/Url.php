<?php
namespace Web2project\Fields;

class Url extends Text implements \Web2project\Interfaces\Field
{
    public function view($value)
    {
        $value = str_replace(array('"', '"', '<', '>'), '', $value);
        return w2p_url($value);
    }
}