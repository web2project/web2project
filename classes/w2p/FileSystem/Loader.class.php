<?php
/**
 * Class w2p_FileSystem_Loader
 *
 * This class reads the local filesystem. It is used to determine configuration options, available modules, etc.
 *
 * @package     web2project\filesystem
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
     * Utility function to check whether a file name is 'safe'
     *
     * Prevents from access to relative directories (eg ../../dealyfile.php);
     * @param string The file name.
     * @return array A named array of the files (the key and value are identical).
     */
    public function checkFileName($file)
    {
        global $AppUI;

        // define bad characters and their replacement
        $bad_chars = ";/\\";
        $bad_replace = '....'; // Needs the same number of chars as $bad_chars
        // check whether the filename contained bad characters
        if (strpos(strtr($file, $bad_chars, $bad_replace), '.') !== false) {
            $AppUI->redirect(ACCESS_DENIED);
        } else {
            return $file;
        }
    }

    /**
     * Utility function to make a file name 'safe'
     *
     * Strips out mallicious insertion of relative directories (eg ../../dealyfile.php);
     * @param string The file name.
     * @return array A named array of the files (the key and value are identical).
     */
    public function makeFileNameSafe($file)
    {
        $file = str_replace('../', '', $file);
        $file = str_replace('..\\', '', $file);
        return $file;
    }
}