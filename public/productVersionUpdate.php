<?php
//
// Description
// ===========
//
// Arguments
// ---------
//
// Returns
// -------
// <rsp stat='ok' />
//
function ciniki_herbalist_productVersionUpdate(&$ciniki) {
    //
    // Find all the required and optional arguments
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'prepareArgs');
    $rc = ciniki_core_prepareArgs($ciniki, 'no', array(
        'business_id'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Business'),
        'productversion_id'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Product Version'),
        'name'=>array('required'=>'no', 'blank'=>'no', 'name'=>'Name'),
        'permalink'=>array('required'=>'no', 'blank'=>'no', 'name'=>'Permalink'),
        'flags'=>array('required'=>'no', 'blank'=>'no', 'name'=>'Options'),
        'sequence'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Order'),
        'recipe_id'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Recipe'),
        'recipe_quantity'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Recipe Quantity'),
        'container_id'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Container'),
        'materials_cost_per_container'=>array('required'=>'no', 'blank'=>'yes', 'type'=>'currency', 'name'=>'Materials Cost'),
        'time_cost_per_container'=>array('required'=>'no', 'blank'=>'yes', 'type'=>'currency', 'name'=>'Time Cost'),
        'total_cost_per_container'=>array('required'=>'no', 'blank'=>'yes', 'type'=>'currency', 'name'=>'Total Cost'),
        'inventory'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Inventory'),
        'wholesale_price'=>array('required'=>'no', 'blank'=>'yes', 'type'=>'currency', 'name'=>'Wholesale'),
        'retail_price'=>array('required'=>'no', 'blank'=>'yes', 'type'=>'currency', 'name'=>'Retail'),
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
    $rc = ciniki_herbalist_checkAccess($ciniki, $args['business_id'], 'ciniki.herbalist.productVersionUpdate');
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }

    //
    // Get the current product version
    //
    $strsql = "SELECT ciniki_herbalist_product_versions.id, "
        . "ciniki_herbalist_product_versions.product_id, "
        . "ciniki_herbalist_product_versions.sequence "
        . "FROM ciniki_herbalist_product_versions "
        . "WHERE business_id = '" . ciniki_core_dbQuote($ciniki, $args['business_id']) . "' " 
        . "AND id = '" . ciniki_core_dbQuote($ciniki, $args['productversion_id']) . "' " 
        . "";
    $rc = ciniki_core_dbHashQuery($ciniki, $strsql, 'ciniki.herbalist', 'item');
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    if( !isset($rc['item']) ) {
        return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.herbalist.46', 'msg'=>'The product does not exist'));
    }
    $item = $rc['item'];

    if( isset($args['name']) ) {
        ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'makePermalink');
        $args['permalink'] = ciniki_core_makePermalink($ciniki, $args['name']);
        //
        // Make sure the permalink is unique
        //
        $strsql = "SELECT id, name, permalink "
            . "FROM ciniki_herbalist_product_versions "
            . "WHERE business_id = '" . ciniki_core_dbQuote($ciniki, $args['business_id']) . "' "
            . "AND permalink = '" . ciniki_core_dbQuote($ciniki, $args['permalink']) . "' "
            . "AND product_id = '" . ciniki_core_dbQuote($ciniki, $item['product_id']) . "' "
            . "AND id <> '" . ciniki_core_dbQuote($ciniki, $args['productversion_id']) . "' "
            . "";
        $rc = ciniki_core_dbHashQuery($ciniki, $strsql, 'ciniki.herbalist', 'item');
        if( $rc['stat'] != 'ok' ) {
            return $rc;
        }
        if( $rc['num_rows'] > 0 ) {
            return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.herbalist.47', 'msg'=>'You already have an product version with this name, please choose another.'));
        }
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
    // Update the Product Version in the database
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'objectUpdate');
    $rc = ciniki_core_objectUpdate($ciniki, $args['business_id'], 'ciniki.herbalist.productversion', $args['productversion_id'], $args, 0x04);
    if( $rc['stat'] != 'ok' ) {
        ciniki_core_dbTransactionRollback($ciniki, 'ciniki.herbalist');
        return $rc;
    }

    //
    // Update the sequences
    //
    if( isset($args['sequence']) ) {
        ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'sequencesUpdate');
        $rc = ciniki_core_sequencesUpdate($ciniki, $args['business_id'], 'ciniki.herbalist.productversion', 'product_id', $item['product_id'], $args['sequence'], $item['sequence']);
        if( $rc['stat'] != 'ok' ) {
            ciniki_core_dbTransactionRollback($ciniki, 'ciniki.herbalist');
            return $rc;
        }
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
    ciniki_core_hookExec($ciniki, $args['business_id'], 'ciniki', 'web', 'indexObject', array('object'=>'ciniki.herbalist.productVersion', 'object_id'=>$args['productversion_id']));

    return array('stat'=>'ok');
}
?>
