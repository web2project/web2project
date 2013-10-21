<?php

class w2p_Output_PDFRenderer
{
    private $pdf = null;

    public function __construct($papersize = 'A4', $orientation = 'portrait')
    {
        $this->pdf = new Cezpdf($papersize, $orientation);
    }

    public function getPDF()
    {
        return $this->pdf;
    }
}