<?php
$ext = '../extensions/CezTableImage.php';
if(!file_exists($ext)){
	die('This example requires the CezTableImage.php extension');
}

include $ext;
$pdf = new CezTableImage("a4");

$image = '../ros.jpg';
// test gif file
//$image = 'images/test_alpha.gif';

$data = array(
				array('num'=>1,'name'=>'gandalf','type'=>'<C:showimage:'.$image.' 90>'),
				array('num'=>4,'name'=>'saruman','type'=>'baddude','url'=>'http://sourceforge.net/projects/pdf-php'),
				array('num'=>5,'name'=>'sauron','type'=>'<C:showimage:'.urlencode($image).' 90>'),
				array('num'=>6,'name'=>'sauron','type'=>'<C:showimage:'.$image.'><C:showimage:'.$image.' 90>'."\nadadd"),
				array('num'=>7,'name'=>'sauron','type'=>'<C:showimage:NOIMAGE.jpg 90>'),
				array('num'=>8,'name'=>'sauron','type'=>'<C:showimage:'.$image.' 90>'),
                array('num'=>10,'name'=>'sauron','type'=>'<C:showimage:'.$image.' 50>'),
                array('num'=>11,'name'=>'sauron','type'=>'<C:showimage:'.$image.'>'),
                /* array('num'=>12,'name'=>'sauron','type'=>'<C:showimage:'.urlencode('http://myserver.mytld/myimage.jpeg').'>'), */
			);

$pdf->ezTable($data,'','',array('width'=>400,'showLines'=>2));
$pdf->ezText("\nWithout table width:");
$pdf->ezTable($data,'','',array('showLines'=>2));
$pdf->ezStream();
?>