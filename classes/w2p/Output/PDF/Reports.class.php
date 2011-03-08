<?php

/**
 * An extension of TCPDF, overrides TCPDF's
 * Header() and Footer() methods for defining our custom header/footer.
 * 
 * 	@package web2project
 * 	@subpackage output
 * 	@version $Revision$
 */
include_once $AppUI->getLibraryClass('tcpdf/tcpdf');

class w2p_Output_PDF_Reports extends TCPDF {

    public $header_company_name = '';
    public $header_date = '';

    public function Header() {
        $this->SetFont('freeserif', '', 12);

        $this->Cell(0, 0, $this->header_company_name, 0, 1);

        $this->SetFont('freeserif', '', 10);

        $this->Cell(0, 0, $this->header_date, 0, 1);
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

    public function addHtmlTable($data, $columns=array(), $style=null) {
        if (!is_array($data)) {
            $this->Error('Data should be an array.');
        }

        if ($style === null) {
            $style = '<style>
                    table { border: 1px solid #00000; }
                    td { padding: 4px; border: 1px solid #00000; }
                    </style>';
        }

        $table = $style;
        $table .= '<table border="0">';

        if (is_array($columns) and !empty($columns)) {
            $table .= '<tr>';
            foreach ($columns as $column) {
                $table .= '<td align="center">' . $column . '</td>';
            }
            $table .= '</tr>';
        }

        foreach ($data as $row) {
            $table .= '<tr>';
            foreach ($row as $value) {
                $table .= '<td>' . $value . '</td>';
            }
            $table .= '</tr>';
        }

        $table .= '</table>';

        $this->Ln();
        $this->writeHTML($table, true, false, false, false, '');
    }

}