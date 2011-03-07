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

class w2p_Output_PDF_Forum extends TCPDF {

    public function Header() { }

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