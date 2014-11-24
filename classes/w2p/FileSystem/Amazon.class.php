<?php

use Aws\S3\S3Client;
use Aws\Common\Enum\Size;
use Aws\Common\Exception\MultipartUploadException;
use Aws\S3\Model\MultipartUpload\UploadBuilder;

/**
 * Class w2p_FileSystem_Amazon
 *
 * Replace the helper library usage with the Streams implementation.
 */
class w2p_FileSystem_Amazon implements \Web2project\Interfaces\FileSystem
{
    protected $_client = null;
    protected $_bucket = null;

    public function __construct()
    {
        $this->authenticate();
        $this->isWritable();
    }

    public function isWritable()
    {
        $this->authenticate();

        $this->_bucket = w2PgetConfig('aws_bucket_name');
        if ('' == $this->_bucket) {
            $tmp_bucket_name = md5(time());

            $result = $this->_client->createBucket(array(
                'Bucket' => $tmp_bucket_name
            ));

            $this->_client->waitUntil('BucketExists', array('Bucket' => $tmp_bucket_name));

            $obj = new w2p_Core_Config();
            $obj->config_name = 'aws_bucket_name';
            $obj->config_value = $tmp_bucket_name;
            $obj->store();
            $this->_bucket = $tmp_bucket_name;
        }

        return true;
    }

    protected function authenticate()
    {
        if ('' == w2PgetConfig('aws_access_key') || '' == w2PgetConfig('aws_secret_key')) {
            throw new \Web2project\Exceptions\FileSystem("Your Amazon credentials are not configured properly");
        }

        $this->_client = S3Client::factory(array(
            'key'    => w2PgetConfig('aws_access_key'),
            'secret' => w2PgetConfig('aws_secret_key')
        ));
        $this->_client->registerStreamWrapper();

        return true;
    }

    public function move(CFile $file, $old_project_id, $actual_file_name) { return true; }
    public function duplicate($old_project_id, $actual_file_name, $AppUI) { return false; }

    public function moveTemp(CFile $file, $upload_info, $AppUI)
    {
        $file->file_real_filename = uniqid(rand());

        try {
            $this->_client->putObject(array(
                'Bucket' => $this->_bucket,
                'Key'    => $file->file_project . '/' . $file->file_real_filename,
                'Body'   => fopen($upload_info['tmp_name'], 'r'),
                'ACL'    => 'public-read',
            ));
        } catch (S3Exception $e) {
            return false;
        }
        return true;
    }

    public function delete(CFile $file)
    {
        return unlink('s3://' . $this->_bucket . '/' . (int) $file->file_project . '/' . $file->file_real_filename);
    }

    public function exists($project_id, $filename)
    {
        $fullpath = 's3://' . $this->_bucket . '/' . (int) $project_id . '/' .$filename;

        return file_exists($fullpath);
    }

    public function read($project_id, $filename)
    {
        $result = $this->_client->getObject(array(
            'Bucket' => $this->_bucket,
            'Key'    => (int) $project_id . '/' . $filename
        ));

        echo $result['Body'];
    }
}