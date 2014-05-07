<?php
namespace Web2project\Fields;

class Email
{
    public function view($value)
    {
        return w2p_email($value);
    }
}