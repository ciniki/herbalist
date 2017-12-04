<?php
//
// Description
// -----------
// This method will delete an ailment.
//
// Arguments
// ---------
// api_key:
// auth_token:
// tnid:            The ID of the tenant the ailment is attached to.
// ailment_id:            The ID of the ailment to be removed.
//
// Returns
// -------
// <rsp stat="ok">
//
function ciniki_herbalist_ailmentDelete(&$ciniki) {
    //
    // Find all the required and optional arguments
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'prepareArgs');
    $rc = ciniki_core_prepareArgs($ciniki, 'no', array(
        'tnid'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Tenant'),
        'ailment_id'=>array('required'=>'yes', 'blank'=>'yes', 'name'=>'Ailment'),
        ));
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    $args = $rc['args'];

    //
    // Check access to tnid as owner
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'herbalist', 'private', 'checkAccess');
    $rc = ciniki_herbalist_checkAccess($ciniki, $args['tnid'], 'ciniki.herbalist.ailmentDelete');
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }

    //
    // Get the current settings for the ailment
    //
    $strsql = "SELECT id, uuid "
        . "FROM ciniki_herbalist_ailments "
        . "WHERE tnid = '" . ciniki_core_dbQuote($ciniki, $args['tnid']) . "' "
        . "AND id = '" . ciniki_core_dbQuote($ciniki, $args['ailment_id']) . "' "
        . "";
    $rc = ciniki_core_dbHashQuery($ciniki, $strsql, 'ciniki.herbalist', 'ailment');
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    if( !isset($rc['ailment']) ) {
        return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.herbalist.11', 'msg'=>'Ailment does not exist.'));
    }
    $ailment = $rc['ailment'];

    //
    // Start transaction
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbTransactionStart');
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbTransactionRollback');
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbTransactionCommit');
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbDelete');
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'objectDelete');
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbAddModuleHistory');
    $rc = ciniki_core_dbTransactionStart($ciniki, 'ciniki.herbalist');
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }

    //
    // Remove any note references
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'herbalist', 'private', 'objectNotesRefsDelete');
    $rc = ciniki_herbalist_objectNotesRefsDelete($ciniki, $args['tnid'], 'ciniki.herbalist.ailment', $args['ailment_id']);
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }

    //
    // Remove the ailment
    //
    $rc = ciniki_core_objectDelete($ciniki, $args['tnid'], 'ciniki.herbalist.ailment',
        $args['ailment_id'], $ailment['uuid'], 0x04);
    if( $rc['stat'] != 'ok' ) {
        ciniki_core_dbTransactionRollback($ciniki, 'ciniki.herbalist');
        return $rc;
    }

    //
    // Commit the transaction
    //
    $rc = ciniki_core_dbTransactionCommit($ciniki, 'ciniki.herbalist');
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }

    //
    // Update the last_change date in the tenant modules
    // Ignore the result, as we don't want to stop user updates if this fails.
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'tenants', 'private', 'updateModuleChangeDate');
    ciniki_tenants_updateModuleChangeDate($ciniki, $args['tnid'], 'ciniki', 'herbalist');

    return array('stat'=>'ok');
}
?>
