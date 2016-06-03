<?php
//
// Description
// ===========
// This method will return all the information about an recipe batch.
//
// Arguments
// ---------
// api_key:
// auth_token:
// business_id:         The ID of the business the recipe batch is attached to.
// batch_id:          The ID of the recipe batch to get the details for.
//
// Returns
// -------
//
function ciniki_herbalist_labelsPDF($ciniki) {
    //
    // Find all the required and optional arguments
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'prepareArgs');
    $rc = ciniki_core_prepareArgs($ciniki, 'no', array(
        'business_id'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Business'),
        'label'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Recipe Batch'),
        'title'=>array('required'=>'yes', 'blank'=>'yes', 'name'=>'Title'),
        'content'=>array('required'=>'yes', 'blank'=>'yes', 'name'=>'Content'),
        'start_col'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Start Column'),
        'start_row'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Start Row'),
        'end_col'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'End Column'),
        'end_row'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'End Row'),
        'number'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Number of labels'),
        ));
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    $args = $rc['args'];

    //
    // Make sure this module is activated, and
    // check permission to run this function for this business
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'herbalist', 'private', 'checkAccess');
    $rc = ciniki_herbalist_checkAccess($ciniki, $args['business_id'], 'ciniki.herbalist.labelsPDF');
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }

    ciniki_core_loadMethod($ciniki, 'ciniki', 'herbalist', 'templates', 'labelsPDF');
    $rc = ciniki_herbalist_templates_labelsPDF($ciniki, $args['business_id'], $args);
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }

    if( isset($rc['pdf']) ) {
        $filename = preg_replace('/[^a-zA-Z0-9_]/', '', preg_replace('/ /', '_', $args['title'] . '_' . $args['label']));
        $rc['pdf']->Output($filename . '.pdf', 'D');
    }

    return array('stat'=>'exit');
}
?>
