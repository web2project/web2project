<?php
namespace Web2project\Fields;


class Text
{
    public function view($value)
    {
        return w2p_textarea($value);
    }
}