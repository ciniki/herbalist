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
    $rc = ciniki_herbalist_labels($ciniki, $business_id, 'all');
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

    $label = $labels[$args['label']];

    //
    // If the same label is being repeated, run the title/content substitutions now
    //
    if( !isset($args['labels']) ) {
        $title = isset($args['title']) ? $args['title'] : '';
        $content = isset($args['content']) ? $args['content'] : '';

        foreach($label['sections'] as $sid => $section) {
            $label['sections'][$sid]['content'] = str_replace('{_title_}', $title, $label['sections'][$sid]['content']);
            $label['sections'][$sid]['content'] = str_replace('{_content_}', $content, $label['sections'][$sid]['content']);
        }
        $total_number = 1;
    } 
   
    //
    // If each label is different, find the end column and row
    //
    else {
        end($label['rows']);
        $last_row = key($label['rows']);
        end($label['cols']);
        $last_col = key($label['cols']);
        reset($label['rows']);
        reset($label['cols']);
        $total_number = count($args['labels']);
    }

    $count = 0;
    while( $count < $total_number ) {
        foreach($label['rows'] as $rownum => $row) {
            $pdf->SetY($row['y']);
            if( isset($args['start_row']) && $args['start_row'] > $rownum ) {
                continue;
            }
            if( isset($args['end_row']) && $args['end_row'] > 0 && $args['end_row'] < $rownum ) {
                break;
            }
            if( isset($args['labels']) && count($args['labels']) <= $count ) {
                break;
            }
            foreach($label['cols'] as $colnum => $col) {
                $pdf->SetX($col['x']);
                if( isset($args['start_row']) && $args['start_row'] == $rownum && isset($args['start_col']) && $args['start_col'] > 0 && $args['start_col'] > $colnum ) {
                    continue;
                }
                if( isset($args['end_row']) && $args['end_row'] > 0 && $args['end_row'] == $rownum && isset($args['end_col']) && $args['end_col'] > 0 && $args['end_col'] < $colnum ) {
                    break;
                }
                if( isset($args['number']) && $args['number'] > 0 && $args['number'] <= $count ) {
                    break;
                }
                if( isset($args['labels']) && count($args['labels']) <= $count ) {
                    break;
                }

                foreach($label['sections'] as $section) {
                    $pdf->SetFont('', $section['font']['style'], $section['font']['size']);
                    $pdf->SetY($row['y'] + $section['y']);
                    $pdf->SetX($col['x'] + $section['x']);
                    if( isset($args['labels']) ) {
                        $section['content'] = str_replace('{_title_}', $args['labels'][$count]['title'], $section['content']);
                        $section['content'] = str_replace('{_content_}', $args['labels'][$count]['content'], $section['content']);
                    }
                    $pdf->MultiCell($section['width'], $section['height'], $section['content'], 0, $section['align'], false, 0, '', '', true, 0, false, true, $section['height'], 'T', true);
                }
                $count++;
            }
        }
        if( isset($args['labels']) && $total_number > $count && $rownum == $last_row && $colnum == $last_col ) {
            reset($label['rows']);
            reset($label['cols']);
            $args['start_row'] = 0;
            $args['start_col'] = 0;
            $pdf->AddPage();
        }
    }

	return array('stat'=>'ok', 'pdf'=>$pdf);
}
?>
