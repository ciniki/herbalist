<?php
//
// Description
// -----------
// This method will add a new container for the business.
//
// Arguments
// ---------
// api_key:
// auth_token:
// business_id:        The ID of the business to add the Container to.
//
// Returns
// -------
// <rsp stat="ok" id="42">
//
function ciniki_herbalist_containerAdd(&$ciniki) {
    //
    // Find all the required and optional arguments
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'prepareArgs');
    $rc = ciniki_core_prepareArgs($ciniki, 'no', array(
        'business_id'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Business'),
        'name'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Name'),
        'top_quantity'=>array('required'=>'no', 'blank'=>'yes', 'default'=>'0', 'name'=>'Top Quantity'),
        'top_price'=>array('required'=>'no', 'blank'=>'yes', 'default'=>'0', 'type'=>'currency', 'name'=>'Top Price'),
        'bottom_quantity'=>array('required'=>'no', 'blank'=>'yes', 'default'=>'0', 'name'=>'Bottom Quantity'),
        'bottom_price'=>array('required'=>'no', 'blank'=>'yes', 'default'=>'0', 'type'=>'currency', 'name'=>'Bottom Price'),
        'cost_per_unit'=>array('required'=>'no', 'blank'=>'yes', 'type'=>'currency', 'name'=>'Cost per Unit'),
        'notes'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Notes'),
        ));
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    $args = $rc['args'];

    //
    // Check access to business_id as owner
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'herbalist', 'private', 'checkAccess');
    $rc = ciniki_herbalist_checkAccess($ciniki, $args['business_id'], 'ciniki.herbalist.containerAdd');
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }

    //
    // Start transaction
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbTransactionStart');
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbTransactionRollback');
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbTransactionCommit');
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbAddModuleHistory');
    $rc = ciniki_core_dbTransactionStart($ciniki, 'ciniki.herbalist');
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }

    //
    // Calculate cost per unit
    //
    if( !isset($args['cost_per_unit']) ) {
        $args['cost_per_unit'] = 0;
        if( $args['top_price'] > 0 && $args['top_quantity'] > 0 ) {
            $args['cost_per_unit'] += bcdiv($args['top_price'], $args['top_quantity'], 4);
        }
        if( $args['bottom_price'] > 0 && $args['bottom_quantity'] > 0 ) {
            $args['cost_per_unit'] += bcdiv($args['bottom_price'], $args['bottom_quantity'], 4);
        }
    }

    //
    // Add the container to the database
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'objectAdd');
    $rc = ciniki_core_objectAdd($ciniki, $args['business_id'], 'ciniki.herbalist.container', $args, 0x04);
    if( $rc['stat'] != 'ok' ) {
        ciniki_core_dbTransactionRollback($ciniki, 'ciniki.herbalist');
        return $rc;
    }
    $container_id = $rc['id'];

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

    //
    // Run the costing updates
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'herbalist', 'private', 'costingUpdate');
    $rc = ciniki_herbalist_costingUpdate($ciniki, $args['business_id'], array());
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }

    //
    // Update the web index if enabled
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'hookExec');
    ciniki_core_hookExec($ciniki, $args['business_id'], 'ciniki', 'web', 'indexObject', array('object'=>'ciniki.herbalist.container', 'object_id'=>$container_id));

    return array('stat'=>'ok', 'id'=>$container_id);
}
?>
