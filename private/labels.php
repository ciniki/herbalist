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
function ciniki_herbalist_labels($ciniki) {
	$labels = array();
	$labels['avery8927'] = array(
        'name'=>'Avery 8927',
        'cell'=>array(
            'width'=>44,
            'height'=>12.7,
            'sections'=>array(
                '0'=>array(
                    'font'=>array(
                        'size'=>7,
                        'style'=>'B',
                        ),
                    'content'=>'{_title_}',
                    'align'=>'C',
                    'x'=>0,
                    'y'=>0,
                    'width'=>44,
                    'height'=>7.5,
                    ),
                '1'=>array(
                    'font'=>array(
                        'size'=>5,
                        'style'=>'',
                        ),
                    'content'=>'{_ingredients_} ({_batchdate_})',
                    'align'=>'C',
                    'x'=>0,
                    'y'=>3.3,
                    'width'=>44,
                    'height'=>12,
                    ),
                ),
            ),
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
		);

	return array('stat'=>'ok', 'labels'=>$labels);
}
?>
