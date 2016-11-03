<?php
//
// Description
// -----------
// This method will delete an container.
//
// Arguments
// ---------
// api_key:
// auth_token:
// business_id:            The ID of the business the container is attached to.
// container_id:            The ID of the container to be removed.
//
// Returns
// -------
// <rsp stat="ok">
//
function ciniki_herbalist_containerDelete(&$ciniki) {
    //
    // Find all the required and optional arguments
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'prepareArgs');
    $rc = ciniki_core_prepareArgs($ciniki, 'no', array(
        'business_id'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Business'),
        'container_id'=>array('required'=>'yes', 'blank'=>'yes', 'name'=>'Container'),
        ));
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    $args = $rc['args'];

    //
    // Check access to business_id as owner
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'herbalist', 'private', 'checkAccess');
    $rc = ciniki_herbalist_checkAccess($ciniki, $args['business_id'], 'ciniki.herbalist.containerDelete');
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }

    //
    // Get the current settings for the container
    //
    $strsql = "SELECT id, uuid "
        . "FROM ciniki_herbalist_containers "
        . "WHERE business_id = '" . ciniki_core_dbQuote($ciniki, $args['business_id']) . "' "
        . "AND id = '" . ciniki_core_dbQuote($ciniki, $args['container_id']) . "' "
        . "";
    $rc = ciniki_core_dbHashQuery($ciniki, $strsql, 'ciniki.herbalist', 'container');
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    if( !isset($rc['container']) ) {
        return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.herbalist.14', 'msg'=>'Container does not exist.'));
    }
    $container = $rc['container'];

    //
    // Check the container is used in any products
    //
    $strsql = "SELECT COUNT(*) AS recipes "
        . "FROM ciniki_herbalist_product_versions "
        . "WHERE container_id = '" . ciniki_core_dbQuote($ciniki, $args['container_id']) . "' "
        . "AND business_id = '" . ciniki_core_dbQuote($ciniki, $args['business_id']) . "' "
        . "";
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbSingleCount');
    $rc = ciniki_core_dbSingleCount($ciniki, $strsql, 'ciniki.herbalist', 'num');
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    if( $rc['num'] > 0 ) {
        return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.herbalist.15', 'msg'=>'You still have ' . $rc['num'] . ' product' . ($rc['num']>1?'s':'') .' using this container.'));
    }

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
    // Remove the container
    //
    $rc = ciniki_core_objectDelete($ciniki, $args['business_id'], 'ciniki.herbalist.container',
        $args['container_id'], $container['uuid'], 0x04);
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
    // Update the last_change date in the business modules
    // Ignore the result, as we don't want to stop user updates if this fails.
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'businesses', 'private', 'updateModuleChangeDate');
    ciniki_businesses_updateModuleChangeDate($ciniki, $args['business_id'], 'ciniki', 'herbalist');

    return array('stat'=>'ok');
}
?>
