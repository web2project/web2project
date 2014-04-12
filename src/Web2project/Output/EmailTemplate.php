<?php
namespace Web2project\Output;

/**
 * Class EmailTemplate
 * @package Web2project\Output
 */
class EmailTemplate
{
    /**
     * @param $message
     * @param $object
     * @return mixed
     */
    public function render($message, $object)
    {
        $properties = get_object_vars($object);

        foreach($properties as $key => $value) {
            $message = str_replace($key, $value, $message);
        }
        return $message;
    }
}