<?php

class w2p_Output_PDFRenderer
{
    protected $pdf = null;

    public function __construct($papersize = 'A4', $orientation = 'portrait')
    {
        $this->pdf = new Cezpdf($papersize, $orientation);
        $this->setMargins();
    }

    public function setMargins($top = 1, $bottom = 1, $left = 1, $right = 1)
    {
        $this->pdf->ezSetCmMargins($top, $bottom, $left, $right);
    }

    public function getPDF()
    {
        return $this->pdf;
    }
}