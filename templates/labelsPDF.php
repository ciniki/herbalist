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
function ciniki_herbalist_templates_labelsPDF(&$ciniki, $business_id, $args) {

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
    // Load the label definitions
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'herbalist', 'private', 'labels');
    $rc = ciniki_herbalist_labels($ciniki, $business_id, array());
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    $labels = $rc['labels'];

	//
	// Load TCPDF library
	//
	$rsp = array('stat'=>'ok');
	require_once($ciniki['config']['ciniki.core']['lib_dir'] . '/tcpdf/tcpdf.php');

	class MYPDF extends TCPDF {
		public $left_margin = 7;
		public $right_margin = 7;
		public $top_margin = 0;

		public function Header() {
		}

		// Page footer
		public function Footer() {
		}
	}

	//
	// Start a new document
	//
	$pdf = new MYPDF('P', PDF_UNIT, 'LETTER', true, 'UTF-8', false);

	$pdf->business_details = $business_details;
    $pdf->recipe_name = $args['title'];

	//
	// Setup the PDF basics
	//
	$pdf->SetCreator('Ciniki');
	$pdf->SetAuthor($business_details['name']);
	$pdf->SetTitle($args['title']);
	$pdf->SetSubject('');
	$pdf->SetKeywords('');

	// set margins
	$pdf->SetMargins(0, 0, 0);
	$pdf->SetHeaderMargin(0);
    $pdf->SetAutoPageBreak(false);

	// set font
    $pdf->AddPage();
	$pdf->SetFont('helvetica', '', 8);
	$pdf->SetCellPadding(2);
    $pdf->SetFillColor(255);
    $pdf->SetTextColor(0);
    $pdf->SetDrawColor(125);
    $pdf->SetLineWidth(0.05);

    $label = $labels[$args['label']['label']];

    $title = isset($args['label']['title']) ? $args['label']['title'] : '';
    $ingredients = isset($args['label']['ingredients']) ? $args['label']['ingredients'] : '';
    $batchdate = isset($args['label']['batchdate']) ? $args['label']['batchdate'] : '';

    foreach($label['cell']['sections'] as $sid => $section) {
        $label['cell']['sections'][$sid]['content'] = str_replace('{_title_}', $title, $label['cell']['sections'][$sid]['content']);
        $label['cell']['sections'][$sid]['content'] = str_replace('{_ingredients_}', $ingredients, $label['cell']['sections'][$sid]['content']);
        $label['cell']['sections'][$sid]['content'] = str_replace('{_batchdate_}', $batchdate, $label['cell']['sections'][$sid]['content']);
    }

    foreach($label['rows'] as $rownum => $row) {
        $pdf->SetY($row['y']);
        if( isset($args['start_row']) && $args['start_row'] > $rownum ) {
            continue;
        }
        if( isset($args['end_row']) && $args['end_row'] < $rownum ) {
            break;
        }
        foreach($label['cols'] as $colnum => $col) {
            $pdf->SetX($col['x']);
            if( isset($args['start_row']) && $args['start_row'] == $rownum && isset($args['start_col']) && $args['start_col'] > $colnum ) {
                continue;
            }
            if( isset($args['end_row']) && $args['end_row'] == $rownum && isset($args['end_col']) && $args['end_col'] < $colnum ) {
                break;
            }

            foreach($label['cell']['sections'] as $section) {
                $pdf->SetFont('', $section['font']['style'], $section['font']['size']);
                $pdf->SetY($row['y'] + $section['y']);
                $pdf->SetX($col['x'] + $section['x']);
//                $pdf->MultiCell($section['width'], $section['height'], $section['content'], 0, $section['align']);
                $pdf->MultiCell($section['width'], $section['height'], $section['content'], 0, $section['align'], false, 0, '', '', true, 0, false, true, $section['height'], 'T', true);
            }
        }
    }

	return array('stat'=>'ok', 'pdf'=>$pdf);
}
?>
