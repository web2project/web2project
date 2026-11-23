<?php
/**
 * Class w2p_FileSystem_Local
 *
 * This is the base implementation of the storage backend for the Files module.
 *
 * @package web2project\filesystem
 */
class w2p_FileSystem_Local implements w2p_Interfaces_FileSystem
{
    /**
     * Whether a stored file name is a plain name: no directory part, so it
     * stays inside files/<project>/ when joined to it. Names this class
     * generates are hex strings (uniqid(rand())).
     *
     * @param mixed $name
     *
     * @return bool
     */
    public static function isPlainName($name)
    {
        if (!is_string($name) && !is_int($name)) {
            return false;
        }
        $name = (string) $name;

        return '' !== $name && '.' !== $name && '..' !== $name
            && false === strpbrk($name, "/\\\0");
    }

    /**
     * The path of a stored file, or false when the stored name is not a
     * plain name. Every read, copy, move and delete builds its path here,
     * so a name like ../../includes/config.php can never leave the project
     * directory (GHSA-pw38-j7w8-hvpc).
     *
     * @param int|string $project_id
     * @param mixed      $name
     *
     * @return string|false
     */
    public static function path($project_id, $name)
    {
        if (!self::isPlainName($name)) {
            return false;
        }

        return W2P_BASE_DIR . '/files/' . (int) $project_id . '/' . $name;
    }

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
            }
        }

        $from = self::path($old_project_id, $actual_file_name);
        $to = self::path($file->file_project, $actual_file_name);
        if (false === $from || false === $to) {
            return false;
        }
        $res = rename($from, $to);

        if (!$res) {
            return false;
        }
        return true;
    }

    /**
     * Copies a stored file. The copy stays in the same project's directory,
     * because the duplicate keeps the original's project.
     */
    public function duplicate($old_project_id, $actual_file_name, $AppUI)
    {
        $dir = W2P_BASE_DIR . '/files/' . (int) $old_project_id;
        if (!is_dir($dir)) {
            $res = mkdir($dir, 0744);
            if (!$res) {
                $AppUI->setMsg('Upload folder not setup to accept uploads - change permission on files/ directory.', UI_MSG_ALERT);
                return false;
            }
        }
        $source = self::path($old_project_id, $actual_file_name);
        if (false === $source) {
            return false;
        }
        $dest_realname = uniqid(rand());
        $res = copy($source, $dir . '/' . $dest_realname);

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
                $AppUI->setMsg('Upload folder not setup to accept uploads - change permission on files/ directory.', UI_MSG_ALERT);
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

        // A name that is not a plain name was never written by this class,
        // so there is nothing of ours to remove; the record can still go.
        $path = self::path($file->file_project, $file->file_real_filename);
        if (false === $path || !file_exists($path)) {
            return true;
        }

        if ($file->canDelete()) {
            return @unlink($path);
        }
        return false;
    }

    public function read($project_id, $filename)
    {
        $path = self::path($project_id, $filename);
        if (false === $path) {
            return false;
        }
        $handle = fopen($path, 'rb');
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
        $path = self::path($project_id, $filename);

        return false !== $path && is_file($path);
    }
}