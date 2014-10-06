<?php
/**
 * Class w2p_FileSystem_Local
 *
 * This is the base implementation of the storage backend for the Files module.
 *
 * @package     web2project\filesystem
 */
class w2p_FileSystem_Local implements w2p_Interfaces_FileSystem
{
    public function isWritable()
    {
        return (is_writable(W2P_BASE_DIR.'/files'));
    }

    public function move(CFile $file, $old_project_id, $actual_file_name)
    {
        $file->file_project = (int) $file->file_project;

        if (!is_dir(W2P_BASE_DIR . '/files/' . $file->file_project)) {
            $res = mkdir(W2P_BASE_DIR . '/files/' . $file->file_project, 0744);
            if (!$res) {
                die('this directory could not be created');
                return false;
            }
        }

        $res = rename(W2P_BASE_DIR . '/files/' . $old_project_id . '/' . $actual_file_name, W2P_BASE_DIR . '/files/' . $file->file_project . '/' . $actual_file_name);

        if (!$res) {
            return false;
        }
        return true;
    }

    public function duplicate($old_project_id, $actual_file_name, $AppUI)
    {
        if (!is_dir(W2P_BASE_DIR . '/files/0')) {
            $res = mkdir(W2P_BASE_DIR . '/files/0', 0744);
            if (!$res) {
                $AppUI->setMsg('Upload folder not setup to accept uploads - change permission on files/ directory.', UI_MSG_ALLERT);
                return false;
            }
        }
        $dest_realname = uniqid(rand());
        $res = copy(W2P_BASE_DIR . '/files/' . $old_project_id . '/' . $actual_file_name, W2P_BASE_DIR . '/files/0/' . $dest_realname);

        if (!$res) {
            return false;
        }
        return $dest_realname;
    }

    // move a file from a temporary (uploaded) location to the file system
    public function moveTemp(CFile $file, $upload_info, $AppUI)
    {
        $file->file_project = (int) $file->file_project;

        $file->file_real_filename = uniqid(rand());
        // check that directories are created
        if (!is_dir(W2P_BASE_DIR . '/files')) {
            $res = mkdir(W2P_BASE_DIR . '/files', 0744);
            if (!$res) {
                return false;
            }
        }
        if (!is_dir(W2P_BASE_DIR . '/files/' . $file->file_project)) {
            $res = mkdir(W2P_BASE_DIR . '/files/' . $file->file_project, 0744);
            if (!$res) {
                $AppUI->setMsg('Upload folder not setup to accept uploads - change permission on files/ directory.', UI_MSG_ALLERT);
                return false;
            }
        }

        $file->_filepath = W2P_BASE_DIR . '/files/' . (int) $file->file_project . '/' . $file->file_real_filename;
        // move it
        $res = move_uploaded_file($upload_info['tmp_name'], $file->_filepath);
        if (!$res) {
            return false;
        }
        return true;
    }

    public function delete(CFile $file)
    {
        $file->file_project = (int) $file->file_project;

        if ('' == $file->file_real_filename ||
            !file_exists(W2P_BASE_DIR . '/files/' . $file->file_project . '/' . $file->file_real_filename)) {
            return true;
        }

        if ($file->canDelete()) {
            return @unlink(W2P_BASE_DIR . '/files/' . $file->file_project . '/' . $file->file_real_filename);
        }
        return false;
    }

    public function read($project_id, $filename)
    {
        $handle = fopen(W2P_BASE_DIR . '/files/' . (int) $project_id . '/' . $filename, 'rb');
        if ($handle) {
            while (!feof($handle)) {
                print fread($handle, 8192);
            }
            fclose($handle);
        }
        return true;
    }

    public function exists($project_id, $filename)
    {
        $fname = W2P_BASE_DIR . '/files/' . (int) $project_id . '/' . $filename;

        return file_exists($fname);
    }
}