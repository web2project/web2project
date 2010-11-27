<?php /* $Id$ $URL$ */
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}
class CReport {
  private $reportFilename = '';
  private $tempDir = '';
  private $expiresIn = 30;

  public function __construct()
  {
    $baseString = time();
    $this->reportFilename = md5($baseString);
    $this->tempDir = w2PgetConfig('root_dir').'/files/temp';
  }

  public function getFilename()
  {
    return $this->reportFilename;
  }

  public function hook_cron()
  {
    // number of seconds in $this->expiresIn days
    $expires = 60 * 60 * 24 * $this->expiresIn;

    if ($handle = opendir($this->tempDir)) {
      while (false !== ($file = readdir($handle))) {
        if ('.pdf' == substr($file, -4)) {
          $fullPath = $this->tempDir.'/'.$file;
          $fileAge = filemtime($fullPath);
          if ((time() - $fileAge) >= $expires && is_writable($fullPath)) {
            unlink ($fullPath);
          }
        }
      }
    }
  }
}