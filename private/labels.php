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
function ciniki_herbalist_labels($ciniki, $business_id, $format='all') {
    //
    // Define the start of each row/col
    //
    $rowscols = array();
    $rowscols['avery5167'] = array(
        'name'=>'1/2" x 1 3/4" - Avery Template 5167',
        'rows'=>array(
            '1'=>array('y'=>11),
            '2'=>array('y'=>23.7),
            '3'=>array('y'=>36.4),
            '4'=>array('y'=>49.1),
            '5'=>array('y'=>61.8),
            '6'=>array('y'=>74.5),
            '7'=>array('y'=>87.2),
            '8'=>array('y'=>99.9),
            '9'=>array('y'=>112.6),
            '10'=>array('y'=>125.3),
            '11'=>array('y'=>138),
            '12'=>array('y'=>150.7),
            '13'=>array('y'=>163.4),
            '14'=>array('y'=>176.1),
            '15'=>array('y'=>188.8),
            '16'=>array('y'=>201.5),
            '17'=>array('y'=>214.2),
            '18'=>array('y'=>226.9),
            '19'=>array('y'=>239.6),
            '20'=>array('y'=>252.3),
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
        'name'=>'1" x 2 5/8" - Avery Template 5160',
        'rows'=>array(
            '1'=>array('y'=>11),
            '2'=>array('y'=>36.4),
            '3'=>array('y'=>61.8),
            '4'=>array('y'=>87.2),
            '5'=>array('y'=>112.6),
            '6'=>array('y'=>138),
            '7'=>array('y'=>163.4),
            '8'=>array('y'=>188.8),
            '9'=>array('y'=>214.2),
            '10'=>array('y'=>239.6),
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


    $labels = array();
    //
    // Ingredient labels for avery template 5167
    //
    if( $format == 'all' || $format == 'ingredients' ) {
        $labels['ingredientsAvery5167'] = $rowscols['avery5167'];
        $labels['ingredientsAvery5167']['sections'] = array(
            '0'=>array(
                'font'=>array('size'=>9, 'style'=>'B',),
                'content'=>'{_title_}',
                'align'=>'C', 'x'=>0, 'y'=>0, 'width'=>44, 'height'=>7.5,
                ),
            '1'=>array(
                'font'=>array('size'=>8, 'style'=>'',),
                'content'=>'{_content_}',
                'align'=>'C', 'x'=>0, 'y'=>3.3, 'width'=>44, 'height'=>12,
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
                'align'=>'C', 'x'=>0, 'y'=>0, 'width'=>66, 'height'=>10,
                ),
            '1'=>array(
                'font'=>array('size'=>10, 'style'=>'',),
                'content'=>'{_content_}',
                'align'=>'C', 'x'=>0, 'y'=>7, 'width'=>66, 'height'=>20,
                ),
            );
    }

    if( $format == 'all' || $format == 'names' ) {
        $labels['namesAvery5167'] = $rowscols['avery5167'];
        $labels['namesAvery5167']['sections'] = array(
            '0'=>array(
                'font'=>array('size'=>12, 'style'=>'B',),
                'content'=>'{_title_}',
                'align'=>'C', 'x'=>0, 'y'=>0, 'width'=>44, 'height'=>12,
                ),
            '1'=>array(
                'font'=>array('size'=>8, 'style'=>'',),
                'content'=>'{_content_}',
                'align'=>'C', 'x'=>0, 'y'=>10, 'width'=>44, 'height'=>7.5,
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
