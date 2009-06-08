<?php
//===================================================================================================
// this is the php file which creates the readme.pdf file, this is not seriously 
// suggested as a good way to create such a file, nor a great example of prose,
// but hopefully it will be useful
//
// adding ?d=1 to the url calling this will cause the pdf code itself to ve echoed to the 
// browser, this is quite useful for debugging purposes.
// there is no option to save directly to a file here, but this would be trivial to implement.
//
// note that this file comprisises both the demo code, and the generator of the pdf documentation
//
//===================================================================================================


// don't want any warnings turning up in the pdf code if the server is set to 'anal' mode.
//error_reporting(7);
error_reporting(E_ALL);
set_time_limit(1800);

include 'class.ezpdf.php';

// define a clas extension to allow the use of a callback to get the table of contents, and to put the dots in the toc
class Creport extends Cezpdf {

var $reportContents = array();

function Creport($p,$o){
  $this->Cezpdf($p,$o);
}

function rf($info){
  // this callback records all of the table of contents entries, it also places a destination marker there
  // so that it can be linked too
  $tmp = $info['p'];
  $lvl = $tmp[0];
  $lbl = rawurldecode(substr($tmp,1));
  $num=$this->ezWhatPageNumber($this->ezGetCurrentPageNumber());
  $this->reportContents[] = array($lbl,$num,$lvl );
  $this->addDestination('toc'.(count($this->reportContents)-1),'FitH',$info['y']+$info['height']);
}

function dots($info){
  // draw a dotted line over to the right and put on a page number
  $tmp = $info['p'];
  $lvl = $tmp[0];
  $lbl = substr($tmp,1);
  $xpos = 520;

  switch($lvl){
    case '1':
      $size=16;
      $thick=1;
      break;
    case '2':
      $size=12;
      $thick=0.5;
      break;
  }

  $this->saveState();
  $this->setLineStyle($thick,'round','',array(0,10));
  $this->line($xpos,$info['y'],$info['x']+5,$info['y']);
  $this->restoreState();
  $this->addText($xpos+5,$info['y'],$size,$lbl);


}


}
// I am in NZ, so will design my page for A4 paper.. but don't get me started on that.
// (defaults to legal)
// this code has been modified to use ezpdf.

//$pdf = new Cezpdf('a4','portrait');
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

$pdf->ezSetDy(-100);

//$mainFont = './fonts/Helvetica.afm';
$mainFont = './fonts/Times-Roman.afm';
$codeFont = './fonts/Courier.afm';
// select a font
$pdf->selectFont($mainFont);

$pdf->ezText("PHP Pdf Creation\n",30,array('justification'=>'centre'));
$pdf->ezText("Module-free creation of Pdf documents\nfrom within PHP\n",20,array('justification'=>'centre'));
$pdf->ezText("developed by R&OS Ltd\n<c:alink:http://www.ros.co.nz/pdf/>http://www.ros.co.nz/pdf</c:alink>",18,array('justification'=>'centre'));
$pdf->ezText("\n<c:alink:http://sourceforge.net/projects/pdf-php>http://sourceforge.net/projects/pdf-php</c:alink>\n\nversion 0.09",18,array('justification'=>'centre'));

$pdf->ezSetDy(-100);
// modified to use the local file if it can

$pdf->openHere('Fit');

function ros_logo(&$pdf,$x,$y,$height,$wl=0,$wr=0){
  $pdf->saveState();
  $h=100;
  $factor = $height/$h;
  $pdf->selectFont('./fonts/Helvetica-Bold.afm');
  $text = 'R&OS';
  $ts=100*$factor;
  $th = $pdf->getFontHeight($ts);
  $td = $pdf->getFontDecender($ts);
  $tw = $pdf->getTextWidth($ts,$text);
  $pdf->setColor(0.6,0,0);
  $z = 0.86;
  $pdf->filledRectangle($x-$wl,$y-$z*$h*$factor,$tw*1.2+$wr+$wl,$h*$factor*$z);
  $pdf->setColor(1,1,1);
  $pdf->addText($x,$y-$th-$td,$ts,$text);
  $pdf->setColor(0.6,0,0);
  $pdf->addText($x,$y-$th-$td,$ts*0.1,'http://www.ros.co.nz');
  $pdf->restoreState();
  return $height;
}

ros_logo($pdf,150,$pdf->y-100,80,150,200);
$pdf->selectFont($mainFont);

/*
if (file_exists('ros.jpg')){
  $pdf->addJpegFromFile('ros.jpg',199,$pdf->y-100,200,0);
} else {
  // comment out these two lines if you do not have GD jpeg support
  // I couldn't quickly see a way to test for this support from the code.
  // you could also copy the file from the locatioin shown and put it in the directory, then 
  // the code above which doesn't use GD will be activated.
  $img = ImageCreatefromjpeg('http://www.ros.co.nz/pdf/ros.jpg');
  $pdf-> addImage($img,199,$pdf->y-100,200,0);
}
*/
//-----------------------------------------------------------
// load up the document content
$data=file('./data.txt');

// try adding the faq's to the document, this will not work for people re-building the file from the 
// download as I am not going to put in the faq file with that
$faqFile = '../ros/pdf/faqs.inc';
if (file_exists($faqFile)){
  // then the file is there.
  $data[] = "#NP\n";
  $data[] = "1<FAQs>\n";
  include($faqFile);
  foreach($faqs as $v){
    $data[] = "2<".$v['t'].">\n";
    // these are some VERY rough conversions, I am not recommending others do this
    $s = array('<br>','<p>','<pre>','</pre>','<ul>','</ul>','<li>');
    $r = array("\n","\n\n","\n#C","\n#c","","","   - ");
    if (is_array($v['d'])){
      $tmp='';
      foreach($v['d'] as $section){
        $tmp.=$section."\n\n";
      }
    } else {
      $tmp = $v['d'];
    }
    $tmp = str_replace($s,$r,$tmp);
    $bits = explode("\n",$tmp);
    foreach($bits as $bit){
      $data[] = $bit."\n";
    }
  }
}

$pdf->ezNewPage();

$pdf->ezStartPageNumbers(500,28,10,'','',1);

$size=12;
$height = $pdf->getFontHeight($size);
$textOptions = array('justification'=>'full');
$collecting=0;
$code='';

foreach ($data as $line){
  // go through each line, showing it as required, if it is surrounded by '<>' then 
  // assume that it is a title
  $line=chop($line);
  if (strlen($line) && $line[0]=='#'){
    // comment, or new page request
    switch($line){
      case '#NP':
        $pdf->ezNewPage();
        break;
      case '#C':
        $pdf->selectFont($codeFont);
        $textOptions = array('justification'=>'left','left'=>20,'right'=>20);
        $size=10;
        break;
      case '#c':
        $pdf->selectFont($mainFont);
        $textOptions = array('justification'=>'full');
        $size=12;
        break;
      case '#X':
        $collecting=1;
        break;
      case '#x':
        $pdf->saveState();
        eval($code);
        $pdf->restoreState();
        $pdf->selectFont($mainFont);
        $code='';
        $collecting=0;
        break;
    }
  } else if ($collecting){
    $code.=$line;
//  } else if (((strlen($line)>1 && $line[1]=='<') || (strlen($line) && $line[0]=='<')) && $line[strlen($line)-1]=='>') {
  } else if (((strlen($line)>1 && $line[1]=='<') ) && $line[strlen($line)-1]=='>') {
    // then this is a title
    switch($line[0]){
      case '1':
        $tmp = substr($line,2,strlen($line)-3);
        $tmp2 = $tmp.'<C:rf:1'.rawurlencode($tmp).'>';
        $pdf->ezText($tmp2,26,array('justification'=>'centre'));
        break;
      default:
        $tmp = substr($line,2,strlen($line)-3);
        // add a grey bar, highlighting the change
        $tmp2 = $tmp.'<C:rf:2'.rawurlencode($tmp).'>';
        $pdf->transaction('start');
        $ok=0;
        while (!$ok){
          $thisPageNum = $pdf->ezPageCount;
          $pdf->saveState();
          $pdf->setColor(0.9,0.9,0.9);
          $pdf->filledRectangle($pdf->ez['leftMargin'],$pdf->y-$pdf->getFontHeight(18)+$pdf->getFontDecender(18),$pdf->ez['pageWidth']-$pdf->ez['leftMargin']-$pdf->ez['rightMargin'],$pdf->getFontHeight(18));
          $pdf->restoreState();
          $pdf->ezText($tmp2,18,array('justification'=>'left'));
          if ($pdf->ezPageCount==$thisPageNum){
            $pdf->transaction('commit');
            $ok=1;
          } else {
            // then we have moved onto a new page, bad bad, as the background colour will be on the old one
            $pdf->transaction('rewind');
            $pdf->ezNewPage();
          }
        }
        break;
    }
  } else {
    // then this is just text
    // the ezpdf function will take care of all of the wrapping etc.
    $pdf->ezText($line,$size,$textOptions);
  }
  
}

$pdf->ezStopPageNumbers(1,1);

// now add the table of contents, including internal links
$pdf->ezInsertMode(1,1,'after');
$pdf->ezNewPage();
$pdf->ezText("Contents\n",26,array('justification'=>'centre'));
$xpos = 520;
$contents = $pdf->reportContents;
foreach($contents as $k=>$v){
  switch ($v[2]){
    case '1':
      $y=$pdf->ezText('<c:ilink:toc'.$k.'>'.$v[0].'</c:ilink><C:dots:1'.$v[1].'>',16,array('aright'=>$xpos));
//      $y=$pdf->ezText($v[0].'<C:dots:1'.$v[1].'>',16,array('aright'=>$xpos));
      break;
    case '2':
      $pdf->ezText('<c:ilink:toc'.$k.'>'.$v[0].'</c:ilink><C:dots:2'.$v[1].'>',12,array('left'=>50,'aright'=>$xpos));
      break;
  }
}


if (isset($d) && $d){
  $pdfcode = $pdf->ezOutput(1);
  $pdfcode = str_replace("\n","\n<br>",htmlspecialchars($pdfcode));
  echo '<html><body>';
  echo trim($pdfcode);
  echo '</body></html>';
} else {
  $pdf->ezStream();
}
?>