<?php

/**
 * @package     web2project\modules\core
 */

class CSystem_Template extends w2p_Core_BaseObject
{
    public $email_template_id = null;
    public $email_template_name = '';
    public $email_template_identifier = null;
    public $email_template_language = null;
    public $email_template_subject = null;
    public $email_template_body = null;

    public function __construct()
    {
        parent::__construct('email_templates', 'email_template_id', 'system');
    }

    public function loadTemplates($language)
    {
        $q = $this->_getQuery();
        $q->addTable($this->_tbl);
        $q->addWhere("email_template_language = '$language'");

        return $q->loadList();
    }

    public function loadTemplate($identifer, $language = 'en-us')
    {
        $q = $this->_getQuery();
        $q->addTable($this->_tbl);
        $q->addWhere("email_template_identifier = '$identifer'");
        $q->addWhere("email_template_language = '$language'");
        $hash = $q->loadHash();

        $q->bindHashToObject($hash, $this);
    }
}