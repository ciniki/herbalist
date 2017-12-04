<?php
//
// Description
// ===========
// This will generate a printable worksheet to manage herb inventory with.
//
// Arguments
// ---------
// 
// Returns
// -------
// <rsp stat='ok' id='34' />
//
function ciniki_herbalist_templates_ingredientWorksheet(&$ciniki, $tnid, $args) {
    if( !isset($args['ingredients']) ) {
        return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.herbalist.86', 'msg'=>'No herbs specified'));
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
        public $left_margin = 15;
        public $right_margin = 15;
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

    $pdf->tenant_details = $tenant_details;

    $dt = new DateTime('now', new DateTimezone('America/Toronto'));
    $pdf->printdate = $dt->format('M j, Y');

    //
    // Setup the PDF basics
    //
    $pdf->SetCreator('Achilleam');
    $pdf->SetAuthor($tenant_details['name']);
    $pdf->SetTitle((isset($args['title']) ? $args['title'] : 'Ingredient List'));
    $pdf->SetSubject('');
    $pdf->SetKeywords('');

    // set margins
    $pdf->SetMargins(10, 5, 10);
    $pdf->SetFooterMargin(10);
    $pdf->SetAutoPageBreak(TRUE, 18);

    // set font
    $pdf->SetFont('helvetica', '', 8);
    $pdf->SetTextColor(0);
    $pdf->SetDrawColor(125);
    $pdf->SetLineWidth(0.05);
    $pdf->setListIndentWidth(4);
    $pdf->setCellPaddings(2, 1, 2, 1);

    $pdf->AddPage();
    //
    // Load the list of ingredients
    //
    $w = array(9, 9, 9, 37, 1);

    $col = 0;
    foreach($args['ingredients'] as $ingredient) {
        if( ($col%4) == 0 ) {
            $pdf->Ln(6);
        }

        $pdf->Cell($w[0], 6, ' ', 1, 0, 'C');
        $pdf->Cell($w[1], 6, ' ', 1, 0, 'C');
        $pdf->Cell($w[2], 6, ' ', 1, 0, 'C');
        $pdf->Cell($w[3], 6, $ingredient['name'], 1, 0, 'L');
        if( ($col%4) < 3 ) {
            $pdf->Cell($w[4], 6, '', 1, 0, 'C');
        }
        
        $col++;
    }

    
    return array('stat'=>'ok', 'pdf'=>$pdf, 'filename'=>'ingredients.pdf');
}
?>
