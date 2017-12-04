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
function ciniki_herbalist_templates_ingredientWorksheet(&$ciniki, $business_id, $args) {
    if( !isset($args['ingredients']) ) {
        return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.herbalist.86', 'msg'=>'No herbs specified'));
    }

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
        public $left_margin = 10;
        public $right_margin = 10;
        public $top_margin = 22;
        public $printdate = '';
        public $cols = array();

        public function Header() {
            $this->SetFont('helvetica', '', 8);
            $this->SetTextColor(0);
            $this->SetDrawColor(125);
            $this->SetLineWidth(0.05);
            $this->SetY(10);
            $this->Cell($this->cols[0], 12, ' ', 1, 0, 'C');
            $this->Cell($this->cols[1], 12, ' ', 1, 0, 'C');
            $this->Cell($this->cols[2], 12, ' ', 1, 0, 'C');
            $this->Cell($this->cols[3], 12, ' ', 1, 0, 'C');
            $this->Cell($this->cols[4], 12, ' ', 1, 0, 'L');
            $this->Cell($this->cols[5], 12, ' ', 0, 0, 'C');
            $this->Cell($this->cols[0], 12, ' ', 1, 0, 'C');
            $this->Cell($this->cols[1], 12, ' ', 1, 0, 'C');
            $this->Cell($this->cols[2], 12, ' ', 1, 0, 'C');
            $this->Cell($this->cols[3], 12, ' ', 1, 0, 'C');
            $this->Cell($this->cols[4], 12, ' ', 1, 0, 'L');
            $this->Cell($this->cols[5], 12, ' ', 0, 0, 'C');
            $this->Cell($this->cols[0], 12, ' ', 1, 0, 'C');
            $this->Cell($this->cols[1], 12, ' ', 1, 0, 'C');
            $this->Cell($this->cols[2], 12, ' ', 1, 0, 'C');
            $this->Cell($this->cols[3], 12, ' ', 1, 0, 'C');
            $this->Cell($this->cols[4], 12, ' ', 1, 0, 'L');
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

    $pdf->business_details = $business_details;

    $dt = new DateTime('now', new DateTimezone('America/Toronto'));
    $pdf->printdate = $dt->format('M j, Y');

    //
    // Setup the columns
    //
    $cols = 3;
    if( $cols == 4 ) {
        $w = array(8, 8, 8, 8, 32, 1); // 4 column worksheet
    } else {
        $w = array(12, 12, 12, 12, 38, 1); // 3 column worksheet
        $pdf->cols = array(12, 12, 12, 12, 38, 1); // 3 column worksheet
    }

    //
    // Setup the PDF basics
    //
    $pdf->SetCreator('Achilleam');
    $pdf->SetAuthor($business_details['name']);
    $pdf->SetTitle((isset($args['title']) ? $args['title'] : 'Ingredient List'));
    $pdf->SetSubject('');
    $pdf->SetKeywords('');

    // set margins
    $pdf->SetMargins($pdf->left_margin, $pdf->top_margin, $pdf->right_margin);
    $pdf->SetFooterMargin(10);
    //$pdf->SetAutoPageBreak(TRUE, 18);
    $pdf->SetAutoPageBreak(FALSE, 18);

    // set font
    $pdf->SetFont('helvetica', '', 8);
    $pdf->SetTextColor(0);
    $pdf->SetDrawColor(125);
    $pdf->SetLineWidth(0.05);
    $pdf->setListIndentWidth(4);
    $pdf->setCellPaddings(2, 1, 2, 1);

//    $pdf->AddPage();

//    $col = 0;
    $start_x = $pdf->left_margin;
    $start_y = $pdf->top_margin;
    $cur_x = $start_x;
    $cur_y = $start_y;
    foreach($args['ingredients'] as $ingredient) {
        if( $cur_x == $start_x && $cur_y == $start_y ) {
            $pdf->AddPage();
        }
//        error_log($cur_x . ',' . $cur_y);
        $pdf->SetXY($cur_x, $cur_y);
//        $pdf->SetY($cur_y);
//        if( $col > 0 && ($col%$cols) == 0 ) {
//            $pdf->Ln(6);
//        }
        
        $pdf->Cell($w[0], 6, ' ', 1, 0, 'C');
        $pdf->Cell($w[1], 6, ' ', 1, 0, 'C');
        $pdf->Cell($w[2], 6, ' ', 1, 0, 'C');
        $pdf->Cell($w[3], 6, ' ', 1, 0, 'C');
        $pdf->Cell($w[4], 6, $ingredient['name'], 1, 0, 'L');
        $cur_y += 6;
        if( $cur_y > 190 ) {
            $cur_y = $start_y;
            $cur_x += 87;
        }
        if( $cur_x > 260 ) {
            $cur_x = $start_x;
        }
//        if( ($col%$cols) < ($cols-1) ) {
//            $pdf->Cell($w[5], 6, '', 1, 0, 'C');
//        }
        
//        $col++;
    }

    
    return array('stat'=>'ok', 'pdf'=>$pdf, 'filename'=>'ingredients.pdf');
}
?>
