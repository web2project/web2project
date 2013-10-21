<?php

class w2p_Output_PDFRenderer
{
    protected $pdf = null;
    protected $font_dir = '';
    protected $temp_dir = '';

    public function __construct($papersize = 'A4', $orientation = 'portrait')
    {
        $this->font_dir = W2P_BASE_DIR . '/lib/ezpdf/fonts';
        $this->temp_dir = W2P_BASE_DIR . '/files/temp';

        $this->pdf = new Cezpdf($papersize, $orientation);
        $this->setMargins();
    }

    public function setMargins($top = 1, $bottom = 1, $left = 1, $right = 1)
    {
        $this->pdf->ezSetCmMargins($top, $bottom, $left, $right);
    }

    public function setFont($font = 'Helvetica.afm', $encoding = 'none')
    {
        $this->pdf->selectFont($this->font_dir . '/' . $font, $encoding);
    }

    public function addTitle($title, $fontsize = 15)
    {
        $this->setFont('Helvetica-Bold.afm');
        $this->pdf->ezText($title, $fontsize);
        $this->setFont('Helvetica.afm');
    }

    public function addSubtitle($subtitle, $fontsize = 10)
    {
        $this->pdf->ezText($subtitle, $fontsize);
    }

    public function addDate($dateformat)
    {
        $date = new w2p_Utilities_Date();
        $this->pdf->ezText($date->format($dateformat), 8, array('justification' => 'right', 'width' => 45));
    }

    public function getPDF()
    {
        return $this->pdf;
    }
}