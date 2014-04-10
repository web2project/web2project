<?php

namespace Web2project\Output;

class EmailTemplate
{
    public function render($message, $object)
    {
        $properties = get_object_vars($object);

        foreach($properties as $key => $value) {
            $message = str_replace($key, $value, $message);
        }
        return $message;
    }
}