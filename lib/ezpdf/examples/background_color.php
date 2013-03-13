<?php
error_reporting(E_ALL);
set_time_limit(1800);
set_include_path('../src/' . PATH_SEPARATOR . get_include_path());

include 'Cezpdf.php';

class Creport extends Cezpdf{
	function Creport($p,$o,$t,$op){
  		$this->__construct($p, $o, $t, $op);
	}
}

$pdf = new Creport('a4','portrait','color',array(0.8,0.8,0.8));
//$pdf->fileIdentifier = md5("Hallo");


$pdf -> ezSetMargins(20,20,20,20);

$mainFont = '../src/fonts/Times-Roman.afm';
// select a font
$pdf->selectFont($mainFont);
$size=12;

$height = $pdf->getFontHeight($size);
// modified to use the local file if it can
$pdf->openHere('Fit');

$pdf->ezText("PDF with gray colored background");
//$pdf->ezImage('images/test_grayscaled.png',0,0,'none','center');
//$pdf->ezText("PNG grayscaled with alpha channel - currently not working");
//$pdf->ezImage('images/test_grayscaled_alpha.png',0,0,'none','center');
//$pdf->ezText("PNG true color plus alpha channel #1");
//$pdf->ezImage('images/test_alpha.png',0,0,'none','left');
//$pdf->ezText("PNG true color plus alpha channel #2");
//$pdf->ezImage('images/test_alpha2.png',0,0,'none','right');

if (isset($_GET['d']) && $_GET['d']){
  $pdfcode = $pdf->ezOutput(1);
  $pdfcode = str_replace("\n","\n<br>",htmlspecialchars($pdfcode));
  echo '<html><body>';
  echo trim($pdfcode);
  echo '</body></html>';
} else {
  $pdf->ezStream(array('compress'=>0));
}

//error_log($pdf->messages);
?>