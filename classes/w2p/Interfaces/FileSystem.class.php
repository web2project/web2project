<?php
/**
 * Class w2p_FileSystem_Interface
 *
 * This is the standard interface for all of the filesystem operations.
 *
 * @package     web2project\filesystem
 */
interface w2p_Interfaces_FileSystem
{
    public function isWritable();
    public function move(CFile $file, $old_project_id, $actual_file_name);
    public function duplicate($old_project_id, $actual_file_name, $AppUI);
    public function moveTemp(CFile $file, $upload_info, $AppUI);
    public function delete(CFile $file);
    public function exists($project_id, $filename);
    public function read($project_id, $filename);
}