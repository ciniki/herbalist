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
function ciniki_herbalist_productUpdate(&$ciniki) {
    //
    // Find all the required and optional arguments
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'prepareArgs');
    $rc = ciniki_core_prepareArgs($ciniki, 'no', array(
        'tnid'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Tenant'),
        'product_id'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Product'),
        'name'=>array('required'=>'no', 'blank'=>'no', 'name'=>'Name'),
        'permalink'=>array('required'=>'no', 'blank'=>'no', 'name'=>'Permalink'),
        'flags'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Options'),
        'category'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Category'),
        'notes'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Notes'),
        'primary_image_id'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Image'),
        'synopsis'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Synopsis'),
        'description'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Description'),
        'ingredients'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Ingredients'),
        'categories'=>array('required'=>'no', 'blank'=>'yes', 'type'=>'list', 'delimiter'=>'::', 'name'=>'Categories'),
        ));
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    $args = $rc['args'];

    //
    // Make sure this module is activated, and
    // check permission to run this function for this tenant
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'herbalist', 'private', 'checkAccess');
    $rc = ciniki_herbalist_checkAccess($ciniki, $args['tnid'], 'ciniki.herbalist.productUpdate');
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }

    if( isset($args['name']) ) {
        ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'makePermalink');
        $args['permalink'] = ciniki_core_makePermalink($ciniki, $args['name']);
        //
        // Make sure the permalink is unique
        //
        $strsql = "SELECT id, name, permalink "
            . "FROM ciniki_herbalist_products "
            . "WHERE tnid = '" . ciniki_core_dbQuote($ciniki, $args['tnid']) . "' "
            . "AND permalink = '" . ciniki_core_dbQuote($ciniki, $args['permalink']) . "' "
            . "AND id <> '" . ciniki_core_dbQuote($ciniki, $args['product_id']) . "' "
            . "";
        $rc = ciniki_core_dbHashQuery($ciniki, $strsql, 'ciniki.herbalist', 'item');
        if( $rc['stat'] != 'ok' ) {
            return $rc;
        }
        if( $rc['num_rows'] > 0 ) {
            return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.herbalist.41', 'msg'=>'You already have an product with this name, please choose another.'));
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
    // Update the Product in the database
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'objectUpdate');
    $rc = ciniki_core_objectUpdate($ciniki, $args['tnid'], 'ciniki.herbalist.product', $args['product_id'], $args, 0x04);
    if( $rc['stat'] != 'ok' ) {
        ciniki_core_dbTransactionRollback($ciniki, 'ciniki.herbalist');
        return $rc;
    }

    //
    // Update the categories
    //
    if( isset($args['categories']) ) {
        ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'tagsUpdate');
        $rc = ciniki_core_tagsUpdate($ciniki, 'ciniki.herbalist', 'tag', $args['tnid'],
            'ciniki_herbalist_tags', 'ciniki_herbalist_history',
            'ref_id', $args['product_id'], 10, $args['categories']);
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
    // Update the note keywords
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'herbalist', 'private', 'notesUpdateKeywords');
    $rc = ciniki_herbalist_notesUpdateKeywords($ciniki, $args['tnid']);
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }

    //
    // Update the last_change date in the tenant modules
    // Ignore the result, as we don't want to stop user updates if this fails.
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'tenants', 'private', 'updateModuleChangeDate');
    ciniki_tenants_updateModuleChangeDate($ciniki, $args['tnid'], 'ciniki', 'herbalist');

    //
    // Run the costing updates
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'herbalist', 'private', 'costingUpdate');
    $rc = ciniki_herbalist_costingUpdate($ciniki, $args['tnid'], array());
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }

    //
    // Update the web index if enabled
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'hookExec');
    ciniki_core_hookExec($ciniki, $args['tnid'], 'ciniki', 'web', 'indexObject', array('object'=>'ciniki.herbalist.product', 'object_id'=>$args['product_id']));

    return array('stat'=>'ok');
}
?>
