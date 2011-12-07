<?php /* $Id$ $URL$ */

class CFileFolder extends CFile_Folder
{
    public function __construct()
    {
        parent::__construct();
        trigger_error("CFileFolder has been deprecated in v3.0 and will be removed by v4.0. Please use CFile_Folder instead.", E_USER_NOTICE );
    }
}