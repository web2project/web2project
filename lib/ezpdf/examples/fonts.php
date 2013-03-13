<?php
error_reporting(E_ALL);
set_time_limit(1800);
set_include_path('../src/' . PATH_SEPARATOR . get_include_path());

include 'Cezpdf.php';

class Creport extends Cezpdf{
	function Creport($p,$o){
  		$this->__construct($p, $o,'none',array());
	}
}
$pdf = new Creport('a4','portrait');

$pdf->ezSetMargins(20,20,20,20);
$pdf->openHere('Fit');

$pdf->selectFont('../src/fonts/Helvetica.afm');
$pdf->ezText("Text in Helvetica");
$pdf->selectFont('../src/fonts/Courier.afm');
$pdf->ezText("Text in Courier");
$pdf->selectFont('../src/fonts/Times-Roman.afm');
$pdf->ezText("Text in Times New Roman");
$pdf->selectFont('../src/fonts/ZapfDingbats.afm');
$pdf->ezText("Text in zapfdingbats");

if (isset($_GET['d']) && $_GET['d']){
  $pdfcode = $pdf->ezOutput(1);
  $pdfcode = str_replace("\n","\n<br>",htmlspecialchars($pdfcode));
  echo '<html><body>';
  echo trim($pdfcode);
  echo '</body></html>';
} else {
  $pdf->ezStream(array('compress'=>0));
}
?>