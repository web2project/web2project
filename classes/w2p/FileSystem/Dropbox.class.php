<?php

use \Dropbox as dbx;

class w2p_FileSystem_Dropbox implements w2p_FileSystem_Interface
{
    protected $_client = null;
    protected $_folder = null;

    public function __construct()
    {
        $this->authenticate();
    }

    protected function authenticate()
    {
        if ('' == w2PgetConfig('dropbox_key') || '' == w2PgetConfig('dropbox_secret')) {
            throw new w2p_FileSystem_Exception("Your Dropbox credentials are not configured properly");
        }

        $accessToken = w2PgetConfig('dropbox_access_token');

        try {
            $this->_client = new dbx\Client($accessToken, "PHP-Example/1.0");
            $accountInfo = $this->_client->getAccountInfo();
        } catch (Exception $exc) {
            throw new w2p_FileSystem_Exception("Your Dropbox access token is invalid or not configured properly");
        }

        return is_array($accountInfo);
    }

    public function isWritable()
    {
        //todo: create folder
        //todo: return success or not
        return is_object($this->_client);
    }

    public function move(CFile $file, $old_project_id, $actual_file_name)
    {
        return false;
    }
    public function duplicate($old_project_id, $actual_file_name, $AppUI)
    {
        return false;
    }
    public function moveTemp(CFile $file, $upload_info, $AppUI)
    {
        return false;
    }
    public function delete(CFile $file)
    {
        return false;
    }
    public function exists($project_id, $filename)
    {
        return false;
    }
    public function read($project_id, $filename)
    {
        return false;
    }
}