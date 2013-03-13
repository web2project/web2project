<?php
$ext = '../extensions/CezDummy.php';
if(!file_exists($ext)){
	die('This example requires the CezDummy.php extension');
}

include $ext;
$pdf = new CezDummy("a4");
$pdf->selectFont('../src/fonts/Helvetica.afm');

$pdf->ezText("Check the CezDummy.php extension to find the data being displayed\n");
$pdf->ezText("<C:dummy:0>");
$pdf->ezText("<C:dummy:1>");

$pdf->ezStream();
?>