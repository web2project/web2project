<?php

/**
 * An extension of TCPDF, overrides TCPDF's
 * Header() and Footer() methods for defining our custom header/footer.
 * 
 *	@package web2project
 *	@subpackage output
 *	@version $Revision$
 */

include_once $AppUI->getLibraryClass('tcpdf/tcpdf');

class w2p_Output_PDF_Gantt extends TCPDF {

    public $header_project_name = '';

    public $header_date = '';

    public function Header() {
        $margins = $this->GetMargins();

        $this->SetFont('freeserif', 'B', 12);

        $page_width = $this->getPageWidth();

        $string_width = $this->GetStringWidth($this->header_project_name);

        $ypos = $this->getHeaderMargin();
        $xpos = round(($page_width - $string_width) / 2, 2);

        $this->Text($xpos, $ypos, $this->header_project_name);

        $this->SetFont('freeserif', '', 10);
        $string_width = $this->GetStringWidth($this->header_date);
        $xpos = round(($page_width - $string_width - $margins['right']), 2);

        $this->Text($xpos, $ypos, $this->header_date);
    }

    public function Footer() {
        $margins = $this->GetMargins();
        
        $this->SetFont('freeserif', '', 10);

        $string = $this->getPage() . ' / ' . $this->getAliasNbPages();
        $string_width = $this->GetStringWidth($string);
        
        $ypos = $this->getPageHeight() - $this->getFooterMargin();
        $xpos = round(($page_width - $string_width - $margins['right']), 2);

        $this->Text($xpos, $ypos, $string);
    }

}