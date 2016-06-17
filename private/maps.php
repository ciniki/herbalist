<?php
//
// Description
// -----------
// The module flags
//
// Arguments
// ---------
//
// Returns
// -------
//
function ciniki_herbalist_maps($ciniki) {
    $maps = array();
    $maps['ingredient'] = array('units'=>array(
        '10'=>'g',
        '60'=>'ml',
        ));

    return array('stat'=>'ok', 'maps'=>$maps);
}
?>
