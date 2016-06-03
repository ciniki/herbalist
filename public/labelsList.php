<?php
//
// Description
// -----------
// This method returns the list of labels available for a particular format.
//
// Arguments
// ---------
// api_key:
// auth_token:
// business_id:        The ID of the business to get Recipe for.
//
// Returns
// -------
//
function ciniki_herbalist_labelsList($ciniki) {
    //
    // Find all the required and optional arguments
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'prepareArgs');
    $rc = ciniki_core_prepareArgs($ciniki, 'no', array(
        'business_id'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Business'),
        'labelformat'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Format'),
        ));
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    $args = $rc['args'];

    //
    // Check access to business_id as owner, or sys admin.
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'herbalist', 'private', 'checkAccess');
    $rc = ciniki_herbalist_checkAccess($ciniki, $args['business_id'], 'ciniki.herbalist.labelsList');
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }

    //
    // Get the list of 
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'herbalist', 'private', 'labels');
    return ciniki_herbalist_labels($ciniki, $args['business_id'], $args['labelformat']);
}
?>
