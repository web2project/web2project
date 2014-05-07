<?php
namespace Web2project\Fields;

class Email extends Text
{
    public function view($value)
    {
        return w2p_email($value);
    }
}