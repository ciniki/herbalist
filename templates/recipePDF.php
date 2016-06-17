<?php
//
// Description
// ===========
// This function will generate the PDF for the review of the presentations to review
// with the submitter name removed.
//
// Arguments
// ---------
// 
// Returns
// -------
// <rsp stat='ok' id='34' />
//
function ciniki_herbalist_templates_recipePDF(&$ciniki, $business_id, $recipe) {
    //
    // Load the business details
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'businesses', 'private', 'businessDetails');
    $rc = ciniki_businesses_businessDetails($ciniki, $business_id);
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    if( isset($rc['details']) && is_array($rc['details']) ) {    
        $business_details = $rc['details'];
    } else {
        $business_details = array();
    }

    //
    // Load TCPDF library
    //
    $rsp = array('stat'=>'ok');
    require_once($ciniki['config']['ciniki.core']['lib_dir'] . '/tcpdf/tcpdf.php');

    class MYPDF extends TCPDF {
        public $left_margin = 25;
        public $right_margin = 25;
        public $top_margin = 15;
        //Page header
        public $header_image = null;
        public $header_name = '';
        public $header_addr = array();
        public $header_details = array();
        public $header_height = 15;        // The height of the image and address
        public $business_details = array();
        public $courses_settings = array();
        public $conference_name = '';
        public $printdate = '';

        public function Header() {
            $this->SetFont('times', 'B', 18);
            $this->Cell(0, 10, $this->recipe_name, 0, false, 'C', 0, '', 0, false, 'T', 'M');
        }

        // Page footer
        public function Footer() {
            $this->SetY(-25);
            $this->SetFont('helvetica', 'I', 8);
            $this->Cell(90, 10, $this->printdate,
                0, false, 'L', 0, '', 0, false, 'T', 'M');
            $this->Cell(85, 10, 'Page ' . $this->pageNo().'/'.$this->getAliasNbPages(), 
                0, false, 'R', 0, '', 0, false, 'T', 'M');
        }
    }

    //
    // Start a new document
    //
    $pdf = new MYPDF('P', PDF_UNIT, 'LETTER', true, 'UTF-8', false);

    $pdf->business_details = $business_details;
    $pdf->recipe_name = $recipe['name'];
    $pdf->printdate = $recipe['printdate'];

    //
    // Setup the PDF basics
    //
    $pdf->SetCreator('Ciniki');
    $pdf->SetAuthor($business_details['name']);
    $pdf->SetTitle($recipe['name']);
    $pdf->SetSubject('');
    $pdf->SetKeywords('');

    // set margins
    $pdf->SetMargins($pdf->left_margin, $pdf->top_margin + $pdf->header_height, $pdf->right_margin);
    $pdf->SetHeaderMargin($pdf->top_margin);

    // set font
    $pdf->AddPage();
    $pdf->SetFont('times', 'B', 12);
//    $pdf->SetCellPaddings(1.5, 1, 1.5, 1);
    $pdf->SetCellPaddings(1, 0, 0, 0);
    $pdf->SetFillColor(255);
    $pdf->SetTextColor(0);
    $pdf->SetDrawColor(125);
    $pdf->SetLineWidth(0.05);

    foreach($recipe['ingredient_types'] as $tid => $type) {
        $pdf->SetFont('', 'B', 15);
        switch($tid) {
            case 30: $pdf->Cell(0, 10, 'Herbs', 0, false, 'L', 0, '', 0, false, 'T', 'M'); break;
            case 60: $pdf->Cell(0, 10, 'Liquids', 0, false, 'L', 0, '', 0, false, 'T', 'M'); break;
            case 90: $pdf->Cell(0, 10, 'Misc', 0, false, 'L', 0, '', 0, false, 'T', 'M'); break;
        }
        $pdf->Ln();
        $pdf->SetFont('', '', 13); 

        foreach($type['ingredients'] as $ingredient) {
            $pdf->Cell(20, 8, $ingredient['quantity'], 0, false, 'R', 0, '', 0, false, 'T', 'M');
            $pdf->Cell(8, 8, $ingredient['units'], 0, false, 'L', 0, '', 0, false, 'T', 'M');
            $pdf->Cell(120, 8, $ingredient['name'], 0, false, 'L', 0, '', 0, false, 'T', 'M');
//            $pdf->Cell(50, 8, $ingredient['quantity_display'], 'TLB', false, 'R', 0, '', 0, false, 'T', 'M');
//            $pdf->Cell(120, 8, $ingredient['name'], 'TRB', false, 'L', 0, '', 0, false, 'T', 'M');
            $pdf->Ln();
        }
        $pdf->Ln();
    }

    $pdf->SetCellPadding(0);
    $pdf->Cell(85, 8, 'Expected Yield: ' . $recipe['yield'] . ' ' . $recipe['units_display'], 0, false, 'L', 0, '', 0, false, 'T', 'M');
    $time = $recipe['production_time'];
    if( $time > 60 ) {
        $time = floor($time/60) . ' hours ' . ($time%60) . ' minutes';
    } else {
        $time = $time . ' minutes';
    }
    $pdf->Cell(85, 8, 'Time: ' . $time, 0, false, 'L', 0, '', 0, false, 'T', 'M');
    $pdf->Ln();

    return array('stat'=>'ok', 'pdf'=>$pdf);
}
?>
