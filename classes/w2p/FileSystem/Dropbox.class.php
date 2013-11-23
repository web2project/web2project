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
        return is_object($this->_client);
    }

    public function move(CFile $file, $old_project_id, $actual_file_name)
    {
        error_log(__FILE__ . ' -- ' . __LINE__);
        return false;
    }
    public function duplicate($old_project_id, $actual_file_name, $AppUI)
    {
        error_log(__FILE__ . ' -- ' . __LINE__);
        return false;
    }
    public function moveTemp(CFile $file, $upload_info, $AppUI)
    {
        $file->file_real_filename = uniqid(rand());
        $path = '/' . $file->file_project . '/' . $file->file_real_filename;

        $file_upload = fopen($upload_info['tmp_name'], "rb");
        $result = $this->_client->uploadFile($path, dbx\WriteMode::add(), $file_upload);
        fclose($file_upload);

        return (isset($result['size']));
    }
    public function delete(CFile $file)
    {
        error_log(__FILE__ . ' -- ' . __LINE__);
        return false;
    }
    public function exists($project_id, $filename)
    {
        error_log(__FILE__ . ' -- ' . __LINE__);
        return false;
    }
    public function read($project_id, $filename)
    {
        error_log(__FILE__ . ' -- ' . __LINE__);
        return false;
    }
}