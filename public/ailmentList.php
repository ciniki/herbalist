<?php
//
// Description
// -----------
// This method will return the list of Ailments for a business.
//
// Arguments
// ---------
// api_key:
// auth_token:
// business_id:        The ID of the business to get Ailment for.
//
// Returns
// -------
//
function ciniki_herbalist_ailmentList($ciniki) {
    //
    // Find all the required and optional arguments
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'prepareArgs');
    $rc = ciniki_core_prepareArgs($ciniki, 'no', array(
        'business_id'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Business'),
        ));
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    $args = $rc['args'];

    //
    // Check access to business_id as owner, or sys admin.
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'herbalist', 'private', 'checkAccess');
    $rc = ciniki_herbalist_checkAccess($ciniki, $args['business_id'], 'ciniki.herbalist.ailmentList');
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }

    //
    // Get the list of ailments
    //
    $strsql = "SELECT ciniki_herbalist_ailments.id, "
        . "ciniki_herbalist_ailments.name, "
        . "ciniki_herbalist_ailments.description "
        . "FROM ciniki_herbalist_ailments "
        . "WHERE ciniki_herbalist_ailments.business_id = '" . ciniki_core_dbQuote($ciniki, $args['business_id']) . "' "
        . "";
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbHashQueryArrayTree');
    $rc = ciniki_core_dbHashQueryArrayTree($ciniki, $strsql, 'ciniki.herbalist', array(
        array('container'=>'ailments', 'fname'=>'id', 
            'fields'=>array('id', 'name', 'description')),
        ));
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    $ailment_ids = array();
    if( isset($rc['ailments']) ) {
        $ailments = $rc['ailments'];
        foreach($ailments as $ailment) {
            $ailment_ids[] = $ailment['id'];
        }
    } else {
        $ailments = array();
    }

    return array('stat'=>'ok', 'ailments'=>$ailments, 'nextprevlist'=>$ailment_ids);
}
?>
