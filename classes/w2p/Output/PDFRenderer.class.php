<?php
/**
 * This is primarily a wrapper for the pdf classes so we can generate PDF files
 *   in a relatively neutral way. I chose this because working with it directly
 *   in the modules created a huge amount of duplicated code. In addition, as we
 *   evaluate alternatives, this will let us switch them out more easily.
 *
 * @package     web2project\output
 * @author      D. Keith Casey, Jr. <contrib@caseysoftware.com>
 */

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

    public function addTable($title, $headers, $data, $options)
    {
        $this->pdf->ezText("\n");
        $this->pdf->ezTable($data, $headers, $title, $options);
    }

    public function getStream()
    {
        return $this->pdf->ezStream();
    }
    public function getOutput()
    {
        return $this->pdf->ezOutput();
    }

    /**
     * @param $filename
     *
     * @return bool
     */
    public function writeFile($filename)
    {
        $filepath = $this->temp_dir . '/' . $filename . '.pdf';

        if ($fp = fopen($filepath, 'wb')) {
            fwrite($fp, $this->getOutput());
            fclose($fp);
            return true;
        }

        return false;
    }

    /**
     * This is used to assist in the refactoring effort and should probably not be used.
     *
     * @deprecated
     *
     * @return Cezpdf|null
     */
    public function getPDF()
    {
        return $this->pdf;
    }
}