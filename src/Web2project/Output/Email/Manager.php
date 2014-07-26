<?php
namespace Web2project\Output\Email;

/**
 * Class Manager
 * @package Web2project\Output\Email
 */
class Manager
{
    protected $sender = null;
    protected $templates = array();

    public function __construct($sender = null)
    {
        $this->sender = is_null($sender) ? new \w2p_Utilities_Mail() : $sender;
    }

    public function send($name, $language, $object)
    {

    }

    public function render($string, $object)
    {
        $properties = get_object_vars($object);

        foreach($properties as $key => $value) {
            $string = str_replace('{{' . $key . '}}', $value, $string);
        }
        return $string;
    }

    public function setTemplate($name, $language, $subject, $body)
    {
        $this->templates[$name][$language] = array('subject' => $subject, 'body' => $body);
    }

    public function loadTemplate($name, $language)
    {
        // todo: retrieve from database
        // todo: load into array
        $this->templates[$name][$language] = array('subject' => 'sample subject', 'body' => 'sample body');
    }
}