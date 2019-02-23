<?php
//
// Description
// -----------
// The definitions for different label sheets
//
// Arguments
// ---------
//
// Returns
// -------
//
function ciniki_herbalist_labels($ciniki, $tnid, $format='all') {

    //
    // Get the list of names from the database for labels
    //
    $strsql = "SELECT detail_key, detail_value "
        . "FROM ciniki_herbalist_settings "
        . "WHERE tnid = '" . ciniki_core_dbQuote($ciniki, $tnid) . "' "
        . "AND detail_key LIKE 'labels-%' "
        . "";
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbQueryList2');
    $rc = ciniki_core_dbQueryList2($ciniki, $strsql, 'ciniki.herbalist', 'names');
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    if( isset($rc['names']) ) {
        $names = $rc['names'];
    } else {
        $names = array();
    }

    //
    // Define the start of each row/col
    //
    $rowscols = array();
    $rowscols['avery5167'] = array(
        'name'=>((isset($names['labels-avery5167-name']) && $names['labels-avery5167-name'] != '') ? $names['labels-avery5167-name'] . ' - ' : '') . '1/2" x 1 3/4" - Avery Template 5167',
        'rows'=>array(
            '1'=>array('y'=>11.5),
            '2'=>array('y'=>24.2),
            '3'=>array('y'=>36.9),
            '4'=>array('y'=>49.6),
            '5'=>array('y'=>62.3),
            '6'=>array('y'=>75.0),
            '7'=>array('y'=>87.7),
            '8'=>array('y'=>100.4),
            '9'=>array('y'=>113.1),
            '10'=>array('y'=>125.8),
            '11'=>array('y'=>138.5),
            '12'=>array('y'=>151.2),
            '13'=>array('y'=>163.9),
            '14'=>array('y'=>176.6),
            '15'=>array('y'=>189.3),
            '16'=>array('y'=>202.0),
            '17'=>array('y'=>214.7),
            '18'=>array('y'=>227.4),
            '19'=>array('y'=>240.1),
            '20'=>array('y'=>252.8),
            ),
        'cols'=>array(
            '1'=>array('x'=>7),
            '2'=>array('x'=>59),
            '3'=>array('x'=>111),
            '4'=>array('x'=>163),
            ),
        'cell'=>array(
            'width'=>44,
            'height'=>12.7,
            ),
        );
    $rowscols['avery5160'] = array(
        'name'=>((isset($names['labels-avery5160-name']) && $names['labels-avery5160-name'] != '') ? $names['labels-avery5160-name'] . ' - ' : '') . '1" x 2 5/8" - Avery Template 5160',
        'rows'=>array(
            '1'=>array('y'=>13),
            '2'=>array('y'=>38.4),
            '3'=>array('y'=>63.8),
            '4'=>array('y'=>89.2),
            '5'=>array('y'=>114.6),
            '6'=>array('y'=>140),
            '7'=>array('y'=>165.4),
            '8'=>array('y'=>190.8),
            '9'=>array('y'=>216.2),
            '10'=>array('y'=>241.6),
            ),
        'cols'=>array(
            '1'=>array('x'=>5),
            '2'=>array('x'=>75.75),
            '3'=>array('x'=>144.5),
            ),
        'cell'=>array(
            'width'=>66,
            'height'=>25,
            ),
        );
    $rowscols['avery22806'] = array(
        'name'=>((isset($names['labels-avery22806-name']) && $names['labels-avery22806-name'] != '') ? $names['labels-avery22806-name'] . ' - ' : '') . '2" x 2" - Avery Template 22806',
        'rows'=>array(
            '1'=>array('y'=>16.5),
            '2'=>array('y'=>82),
            '3'=>array('y'=>147.8),
            '4'=>array('y'=>213),
            ),
        'cols'=>array(
            '1'=>array('x'=>16),
            '2'=>array('x'=>82.5),
            '3'=>array('x'=>149),
            ),
        'cell'=>array(
            'width'=>50,
            'height'=>50,
            ),
        );
    $rowscols['avery22807'] = array(
        'name'=>((isset($names['labels-avery22807-name']) && $names['labels-avery22807-name'] != '') ? $names['labels-avery22807-name'] . ' - ' : '') . '2" x 2" Round - Avery Template 22807',
        'circle'=>array(
            'radius'=>25,
            ),
        'rows'=>array(
            '1'=>array('y'=>16.5),
            '2'=>array('y'=>82),
            '3'=>array('y'=>147.8),
            '4'=>array('y'=>213),
            ),
        'cols'=>array(
            '1'=>array('x'=>17),
            '2'=>array('x'=>83.5),
            '3'=>array('x'=>150),
            ),
        'cell'=>array(
            'width'=>50,
            'height'=>50,
            ),
        );
    $rowscols['vista2x35'] = array(
        'name'=>((isset($names['labels-vista2x35-name']) && $names['labels-vista2x35-name'] != '') ? $names['labels-vista2x35-name'] . ' - ' : '') . '2" x 3.5" Vista Print Labels',
        'rows'=>array(
            '1'=>array('y'=>21),
            '2'=>array('y'=>75.5),
            '3'=>array('y'=>130),
            '4'=>array('y'=>184.5),
            '5'=>array('y'=>239),
            ),
        'cols'=>array(
            '1'=>array('x'=>24),
            '2'=>array('x'=>122),
            ),
        'cell'=>array(
            'width'=>70,
            'height'=>30,
            ),
        );
/*    $rowscols['avery5164'] = array(
        'name'=>((isset($names['labels-avery5164-name']) && $names['labels-avery5164-name'] != '') ? $names['labels-avery5164-name'] . ' - ' : '') . '3 1/3" x 4" - Avery Template 5164',
        'rows'=>array(
            '1'=>array('y'=>13),
            '2'=>array('y'=>98),
            '3'=>array('y'=>182),
            ),
        'cols'=>array(
            '1'=>array('x'=>4),
            '2'=>array('x'=>110),
            ),
        'cell'=>array(
            'width'=>101,
            'height'=>84,
            ),
        ); */
    $labels = array();

    //
    // Ingredient labels
    //
    if( $format == 'all' || $format == 'ingredients' ) {
        $labels['ingredientsAvery5167'] = $rowscols['avery5167'];
        $labels['ingredientsAvery5167']['sections'] = array(
            '0'=>array(
                'font'=>array('size'=>9, 'style'=>'B',),
                'content'=>'{_title_}',
                'align'=>'C', 'valign'=>'T','x'=>0, 'y'=>0, 'width'=>44, 'height'=>7.5,
                ),
            '1'=>array(
                'font'=>array('size'=>8, 'style'=>'',),
                'content'=>'{_content_}',
                'align'=>'C', 'valign'=>'T','x'=>0, 'y'=>3.3, 'width'=>44, 'height'=>12,
                ),
            );

        //
        // 1" x 2 5/8"
        //
        $labels['ingredientsAvery5160'] = $rowscols['avery5160'];
        $labels['ingredientsAvery5160']['sections'] = array(
            '0'=>array(
                'font'=>array('size'=>12, 'style'=>'B',),
                'content'=>'{_title_}',
                'align'=>'C', 'valign'=>'T','x'=>0, 'y'=>0, 'width'=>66, 'height'=>10,
                ),
            '1'=>array(
                'font'=>array('size'=>10, 'style'=>'',),
                'content'=>'{_content_}',
                'align'=>'C', 'valign'=>'T','x'=>0, 'y'=>7, 'width'=>66, 'height'=>20,
                ),
            );
        //
        // 2" x 2"
        //
        $labels['ingredientsAvery22806'] = $rowscols['avery22806'];
        $labels['ingredientsAvery22806']['sections'] = array(
            '0'=>array(
                'font'=>array('size'=>14, 'style'=>'B',),
                'content'=>'{_title_}',
                'align'=>'C', 'valign'=>'T','x'=>0, 'y'=>0, 'width'=>50, 'height'=>16,
                ),
            '1'=>array(
                'font'=>array('size'=>12, 'style'=>'',),
                'content'=>'{_content_}',
                'align'=>'C', 'valign'=>'T', 'x'=>0, 'y'=>16, 'width'=>50, 'height'=>34,
                ),
            );
        //
        // 2" x 2" Round
        //
        $labels['ingredientsAvery22807'] = $rowscols['avery22807'];
        $labels['ingredientsAvery22807']['sections'] = array(
            '0'=>array(
                'font'=>array('size'=>10, 'style'=>'B',),
                'content'=>'{_title_}',
                'align'=>'C', 'valign'=>'B', 'x'=>0, 'y'=>0, 'width'=>50, 'height'=>20,
                ),
            '1'=>array(
                'font'=>array('size'=>10, 'style'=>'',),
                'content'=>'{_content_}',
                'align'=>'C', 'valign'=>'T', 'x'=>0, 'y'=>20, 'width'=>50, 'height'=>30,
                ),
            );
        //
        // 2" x 3.5" Vista Print
        //
        $labels['ingredientsVista2x35'] = $rowscols['vista2x35'];
        $labels['ingredientsVista2x35']['sections'] = array(
            '0'=>array(
                'font'=>array('size'=>12, 'style'=>'B',),
                'content'=>'{_title_}',
                'align'=>'C', 'valign'=>'B', 'x'=>0, 'y'=>0, 'width'=>70, 'height'=>8,
                ),
            '1'=>array(
                'font'=>array('size'=>10, 'style'=>'',),
                'content'=>'{_content_}',
                'align'=>'C', 'valign'=>'T', 'x'=>0, 'y'=>9, 'width'=>70, 'height'=>23,
                ),
            );
    }

    //
    // Name labels
    //
    if( $format == 'all' || $format == 'names' ) {
        $labels['namesAvery5167'] = $rowscols['avery5167'];
        $labels['namesAvery5167']['sections'] = array(
            '0'=>array(
                'font'=>array('size'=>12, 'style'=>'B',),
                'content'=>'{_title_}',
                'align'=>'C', 'x'=>0, 'y'=>1, 'width'=>44, 'height'=>12,
                ),
            '1'=>array(
                'font'=>array('size'=>8, 'style'=>'I',),
                'content'=>'{_content_}',
                'align'=>'C', 'x'=>0, 'y'=>7, 'width'=>44, 'height'=>7.5,
                ),
            );

        //
        // 1" x 2 5/8"
        //
        $labels['namesAvery5160'] = $rowscols['avery5160'];
        $labels['namesAvery5160']['sections'] = array(
            '0'=>array(
                'font'=>array('size'=>16, 'style'=>'B'),
                'content'=>'{_title_}',
                'align'=>'C', 'x'=>0, 'y'=>5, 'width'=>66, 'height'=>15,
                ),
            '1'=>array(
                'font'=>array('size'=>12, 'style'=>'I'),
                'content'=>'{_content_}',
                'align'=>'C', 'x'=>0, 'y'=>12, 'width'=>66, 'height'=>12,
                ),
            );
    }

    return array('stat'=>'ok', 'labels'=>$labels);
}
?>
