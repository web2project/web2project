<?php

use \Dropbox as dbx;

class w2p_FileSystem_Dropbox implements \Web2project\Interfaces\Filesystem
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
        $path = '/' . $file->file_project . '/' . $file->file_real_filename;
        try {
            $fileMetadata = $this->_client->delete($path);
        } catch (Dropbox\Exception_BadResponse $exc) {
            $message = $exc->getMessage();
            if (strpos($message, '404')) {
                /**
                 * The file was not found.. so we're going to assume that the delete actually worked, it's just trying
                 *   to double-delete for some reason.
                 */
                return true;
            }
        }

        return (1 == $fileMetadata['is_deleted']);
    }

    public function exists($project_id, $filename)
    {
        $path = '/' . $project_id . '/' . $filename;
        $fileMetadata = $this->_client->getMetadata($path);;

        return (isset($fileMetadata['size']));
    }

    public function read($project_id, $filename)
    {
        $path = '/' . $project_id . '/' . $filename;

        $f = fopen("php://memory", "w+b");
        $this->_client->getFile($path, $f);
        rewind($f);
        echo stream_get_contents($f);

        return true;
    }
}