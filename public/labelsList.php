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
// tnid:        The ID of the tenant to get Recipe for.
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
        'tnid'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Tenant'),
        'labelformat'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Format'),
        ));
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    $args = $rc['args'];

    //
    // Check access to tnid as owner, or sys admin.
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'herbalist', 'private', 'checkAccess');
    $rc = ciniki_herbalist_checkAccess($ciniki, $args['tnid'], 'ciniki.herbalist.labelsList');
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }

    //
    // Get the list of 
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'herbalist', 'private', 'labels');
    return ciniki_herbalist_labels($ciniki, $args['tnid'], $args['labelformat']);
}
?>
