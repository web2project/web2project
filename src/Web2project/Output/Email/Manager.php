<?php
namespace Web2project\Output\Email;

/**
 * Class Manager
 * @package Web2project\Output\Email
 */
class Manager
{
    protected $sender = null;
    protected $templater = null;

    public $subject = '';
    public $body    = '';

    public function __construct($sender = null, $templater = null)
    {
        $this->sender = is_null($sender) ? new \w2p_Utilities_Mail() : $sender;
        $this->templater = is_null($templater) ? new \CSystem_Template() : $templater;
    }

    public function sendAll($to_array, $substitutions)
    {
        $result = array();

        foreach($to_array as $to) {
            $result[$to] = $this->send($to, $substitutions);
        }

        return $result;
    }

    public function send($to, $substitutions)
    {
        $subject = $this->render($this->subject, $substitutions);
        $body = $this->render($this->body, $substitutions);

        $this->sender->To($to);
        $this->sender->Subject($subject);
        $this->sender->Body($body);

        return $this->sender->Send();
    }

    public function render($string, $substitutions)
    {
        foreach ($substitutions as $key => $value) {
            $string = str_replace('{{' . $key . '}}', $value, $string);
        }

        return $string;
    }

    public function loadTemplate($name, $language)
    {
        $this->templater->loadTemplate($name, $language);
        if (!$this->templater->email_template_id) {
            $this->templater->loadTemplate($name, 'en_US');
        }

        $this->subject  = $this->templater->email_template_subject;
        $this->body     = $this->templater->email_template_body;
    }
}
