<?php
namespace Web2project\Fields;

class Email extends Text implements \Web2project\Interfaces\Field
{
    public function view($value)
    {
        return w2p_email($value);
    }
}
