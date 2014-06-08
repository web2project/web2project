<?php
/**
 * Class Template
 * @package Web2project\Output\Email
 */
class w2p_Output_Email_Template
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