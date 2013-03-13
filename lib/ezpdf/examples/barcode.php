<?php
error_reporting(E_ALL);
set_time_limit(1800);
set_include_path('../src/' . PATH_SEPARATOR . get_include_path());

include 'Cezpdf.php';

function code39($text,$barcodethinwidth=2,$barcodeheight=40,$xpos=0,$ypos=0)
{
    $barcodethickwidth=$barcodethinwidth*3;
    $codingmap  =  Array( "0"=> "000110100", "1"=> "100100001",
        "2"=> "001100001", "3"=> "101100000", "4"=> "000110001",
        "5"=> "100110000", "6"=> "001110000", "7"=> "000100101",
        "8"=> "100100100", "9"=> "001100100", "A"=> "100001001",
        "B"=> "001001001", "C"=> "101001000", "D"=> "000011001",
        "E"=> "100011000", "F"=> "001011000", "G"=> "000001101",
        "H"=> "100001100", "I"=> "001001100", "J"=> "000011100",
        "K"=> "100000011", "L"=> "001000011", "M"=> "101000010",
        "N"=> "000010011", "O"=> "100010010", "P"=> "001010010",
        "Q"=> "000000111", "R"=> "100000110", "S"=> "001000110",
        "T"=> "000010110", "U"=> "110000001", "V"=> "011000001",
        "W"=> "111000000", "X"=> "010010001", "Y"=> "110010000",
        "Z"=> "011010000", " "=> "011000100", "$"=> "010101000",
        "%"=> "000101010", "*"=> "010010100", "+"=> "010001010",
        "-"=> "010000101", "."=> "110000100", "/"=> "010100010");
	$text  =  strtoupper($text);
	$text  =  "*$text*";  //  add  start/stop  chars.
	$textlen  =  strlen($text);
	$barcodewidth  =  ($textlen)*(7*$barcodethinwidth +  3*$barcodethickwidth)-$barcodethinwidth;
	for  ($idx=0;$idx<$textlen;$idx++)  {
        $char  =  substr($text,$idx,1);
        //  make  unknown  chars  a  '-';
        if  (!isset($codingmap[$char]))  $char  =  "-";
        for  ($baridx=0;$baridx<=8;$baridx++)  {
            $elementwidth  =  (substr($codingmap[$char],$baridx,1))  ?
                                                    $barcodethickwidth
:  $barcodethinwidth;
            if  (($baridx+1)%2)  $rectangle[] = array('x'=>$xpos,'y'=>$ypos,'b'=>$elementwidth,'h'=>$barcodeheight);
            $xpos+=$elementwidth;
        }
        $xpos+=$barcodethinwidth;
    }
    return $rectangle;
}

class Creport extends Cezpdf{
	function Creport($p,$o){
  		parent::__construct($p,$o);
	}
	// Rectangle Callback function for Text output
	function rect($info){
  		// this callback records all of the table of contents entries, it also places a destination marker there
  		// so that it can be linked too
  		// parameters
  		$tmp = $info['p'];
  		$r=explode(",",$tmp);
  		if(count($r) >= 4){
  			$this->filledRectangle($info['x']+$r[0],$info['y']+$r[1],$r[2],$r[3]);
		}
	}
}

$pdf = new Creport('a4','portrait');

$pdf -> ezSetMargins(50,70,50,50);

// put a line top and bottom on all the pages
$all = $pdf->openObject();
$pdf->saveState();
$pdf->setStrokeColor(0,0,0,1);
$pdf->line(20,40,578,40);
$pdf->line(20,822,578,822);
$pdf->addText(50,34,6,'http://ros.co.nz/pdf - http://www.sourceforge.net/projects/pdf-php');
$pdf->restoreState();
$pdf->closeObject();
// note that object can be told to appear on just odd or even pages by changing 'all' to 'odd'
// or 'even'.
$pdf->addObject($all,'all');

//$mainFont = './fonts/Helvetica.afm';
$mainFont = '../src/fonts/Times-Roman.afm';
// select a font
$pdf->selectFont($mainFont);
$size=12;
$height = $pdf->getFontHeight($size);
// modified to use the local file if it can
$pdf->openHere('Fit');

$mydata = array();
$MAXcodeWidth = 0;
for($i=0;$i<7;$i++){
	$const='';
	$r = rand(1000,9999);
	// return rectangle array from code39line.php
	$code39RECT = code39($r,0.8,17,0,-5);
	foreach($code39RECT as $v){
		$const.= '<C:rect:'.implode(",",$v).'>';
		// x position + width
		if(($v['x'] + $v['b']) > $MAXcodeWidth)
			$MAXcodeWidth = $v['x'] + $v['b'];
	}

	$mydata[$i]['value'] = $r;
	$mydata[$i]['barcode'] = $const;
}
$pdf->ezText("This example shows you how to implement code39 barcodes in ROS PDF class. It uses the Callback function 'rect' which is defined in the custom class Creport (inhierted from Cezpdf)");
$pdf->ezTable($mydata,array('value'=>'Value','barcode'=>'Barcode'),'',array('showLines'=>3,'shaded'=>0,'rowGap'=>6,'showHeadings'=>1,'cols'=>array('barcode'=>array('width'=>$MAXcodeWidth + 10))));

if (isset($_GET['d']) && $_GET['d']){
  $pdfcode = $pdf->ezOutput(1);
  $pdfcode = str_replace("\n","\n<br>",htmlspecialchars($pdfcode));
  echo '<html><body>';
  echo trim($pdfcode);
  echo '</body></html>';
} else {
  $pdf->ezStream();
}
?>