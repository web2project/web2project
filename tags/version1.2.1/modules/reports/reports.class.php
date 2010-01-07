<?php /* $Id$ $URL$ */
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}
class CReport {
  private $reportFilename = null;

  public function __construct()
  {
    $baseString = time();
    $this->reportFilename = md5($baseString);
  }

  public function getFilename()
  {
    return $this->reportFilename;
  }
}