<?php
/**
 * Class w2p_FileSystem_Loader
 *
 * This class reads the local filesystem. It is used to determine configuration options, available modules, etc.
 *
 * @package     w2p\FileSystem
 */
class w2p_FileSystem_Loader
{

    /**
     * Utility function to read the 'directories' under 'path'
     *
     * This function is used to read the modules or locales installed on the file system.
     * @param string The path to read.
     * @return array A named array of the directories (the key and value are identical).
     */
    public function readDirs($path)
    {
        $dirs = array();

        if (is_dir(W2P_BASE_DIR . '/' . $path)) {
            $d = dir(W2P_BASE_DIR . '/' . $path);
            while (false !== ($name = $d->read())) {
                if (is_dir(W2P_BASE_DIR . '/' . $path . '/' . $name) && $name != '.' && $name != '..' && $name != 'CVS' && $name != '.svn') {
                    $dirs[$name] = $name;
                }
            }
            $d->close();
        }
        return $dirs;
    }

    /**
     * Utility function to read the 'files' under 'path'
     * @param string The path to read.
     * @param string A regular expression to filter by.
     * @return array A named array of the files (the key and value are identical).
     */
    public function readFiles($path, $filter = '.')
    {
        $files = array();

        if (is_dir($path) && ($handle = opendir($path))) {
            while (false !== ($file = readdir($handle))) {
                if ($file != '.' && $file != '..' && preg_match('/' . $filter . '/', $file)) {
                    $files[$file] = $file;
                }
            }
            closedir($handle);
        }
        return $files;
    }

    /**
     * @deprecated
     * @codeCoverageIgnore
     */
    public function checkFileName($file)
    {
        trigger_error(__CLASS__ . " has been deprecated in v4.0 and will be removed by v5.0. Please use makeFileNameSafe instead.", E_USER_NOTICE);

        return $this->makeFileNameSafe($file);
    }

    /**
     * Utility function to make a file name 'safe'
     *
     * Strips out any non-alphanumeric (or underscore) characters
     *
     * @param string The file name.
     * @return string The clean filename
     */
    public function makeFileNameSafe($filename)
    {
        $filename = str_replace('..', '', $filename);
        return preg_replace("/[^a-z0-9_.]/", "", $filename);
    }
}