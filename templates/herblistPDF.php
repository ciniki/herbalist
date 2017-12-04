<?php
//
// Description
// ===========
// This function will generate a sheet of labels.
//
// Arguments
// ---------
// 
// Returns
// -------
// <rsp stat='ok' id='34' />
//
function ciniki_herbalist_templates_herblistPDF(&$ciniki, $tnid, $args) {
    if( !isset($args['herbs']) ) {
        return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.herbalist.85', 'msg'=>'No herbs specified'));
    }

    //
    // Load the tenant details
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'tenants', 'private', 'tenantDetails');
    $rc = ciniki_tenants_tenantDetails($ciniki, $tnid);
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    if( isset($rc['details']) && is_array($rc['details']) ) {    
        $tenant_details = $rc['details'];
    } else {
        $tenant_details = array();
    }

    //
    // Load TCPDF library
    //
    $rsp = array('stat'=>'ok');
    require_once($ciniki['config']['ciniki.core']['lib_dir'] . '/tcpdf/tcpdf.php');

    class MYPDF extends TCPDF {
        public $left_margin = 7;
        public $right_margin = 7;
        public $top_margin = 0;
        public $printdate = '';

        public function Header() {
        }

        // Page footer
        public function Footer() {
            $this->SetY(-15);
            $this->SetFont('helvetica', 'I', 8);
            $this->Cell(90, 10, $this->printdate,
                0, false, 'L', 0, '', 0, false, 'T', 'M');
            $this->Cell(173, 10, 'Page ' . $this->pageNo() . ' of ' . $this->getAliasNbPages(), 
                0, false, 'R', 0, '', 0, false, 'T', 'M');
        }
    }

    //
    // Start a new document
    //
    $pdf = new MYPDF('L', PDF_UNIT, 'LETTER', true, 'UTF-8', false);
//    $pdf = new TCPDF('L', PDF_UNIT, 'LETTER', true, 'UTF-8', false);

    $pdf->tenant_details = $tenant_details;

    $dt = new DateTime('now', new DateTimezone('America/Toronto'));
    $pdf->printdate = $dt->format('M j, Y');

    //
    // Setup the PDF basics
    //
    $pdf->SetCreator('Achilleam');
    $pdf->SetAuthor($tenant_details['name']);
    $pdf->SetTitle((isset($args['title']) ? $args['title'] : 'Herbs'));
    $pdf->SetSubject('');
    $pdf->SetKeywords('');

    // set margins
    $pdf->SetMargins(10, 10, 10);
    $pdf->SetFooterMargin(10);
    $pdf->SetAutoPageBreak(TRUE, 18);

    // set font
    $pdf->SetFont('helvetica', '', 8);
    $pdf->AddPage();
    $pdf->SetTextColor(0);
    $pdf->SetDrawColor(125);
    $pdf->SetLineWidth(0.05);
    $pdf->setListIndentWidth(4);

    //
    // Setup the HTML table
    //
    $style = "border: 0.1px solid #aaa;";
    $table = '<table border="0" cellspacing="0" cellpadding="5" style="' . $style . '">';
    $table .= '<thead><tr>'
        . '<th style="' . $style . '">Herb</th>'
        . '<th style="' . $style . '">Safety</th>'
        . '<th style="' . $style . '">Actions</th>'
        . '<th style="' . $style . '">Ailments</th>'
        . '<th style="' . $style . '">Energetics</th>'
        . '</tr></thead>';
    foreach($args['herbs'] as $herb) {
        $table .= '<tr nobr="true"><td style="' . $style . '">'
            . '<table border="0" cellspacing="1" cellpadding="2">'
            . "<tr><td>"
            . "<i>" . $herb['latin_name'] . "</i>"
            . "</td></tr><tr><td>"
            . "<b>" . $herb['common_name'] . "</b>"
            . "</td></tr><tr><td>"
            . $herb['dose']
            . "</td></tr><tr><td>Dry: "
            . $herb['dry']
            . "</td></tr><tr><td>Tincture: "
            . $herb['tincture'] 
            . "</td></tr>"
            . "</table>"
            . '</td><td style="' . $style . '">'
            . $herb['safety']
            . '</td><td style="' . $style . '">'
            . $herb['actions']
            . '</td><td style="' . $style . '">'
            . $herb['ailments']
            . '</td><td style="' . $style . '">'
            . $herb['energetics']
            . "</td></tr>"
            . "";
    }
    $table .= "</table>";

    $pdf->writeHTML($table, true, false, true, false, '');

    return array('stat'=>'ok', 'pdf'=>$pdf);
}
?>
