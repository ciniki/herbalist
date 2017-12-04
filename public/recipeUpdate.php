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
function ciniki_herbalist_recipeUpdate(&$ciniki) {
    //
    // Find all the required and optional arguments
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'prepareArgs');
    $rc = ciniki_core_prepareArgs($ciniki, 'no', array(
        'tnid'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Tenant'),
        'recipe_id'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Recipe'),
        'name'=>array('required'=>'no', 'blank'=>'no', 'name'=>'Name'),
        'recipe_type'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Type'),
        'flags'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Options'),
        'units'=>array('required'=>'no', 'blank'=>'no', 'name'=>'Units'),
        'yield'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Yield'),
        'production_time'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Production Time'),
        'materials_cost_per_unit'=>array('required'=>'no', 'blank'=>'yes', 'type'=>'currency', 'name'=>'Materials Cost per Unit'),
        'time_cost_per_unit'=>array('required'=>'no', 'blank'=>'yes', 'type'=>'currency', 'name'=>'Time Cost per Unit'),
        'total_cost_per_unit'=>array('required'=>'no', 'blank'=>'yes', 'type'=>'currency', 'name'=>'Total Cost per Unit'),
        'total_time_per_unit'=>array('required'=>'no', 'blank'=>'yes', 'type'=>'currency', 'name'=>'Total Time per Unit'),
        'notes'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Notes'),
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
    $rc = ciniki_herbalist_checkAccess($ciniki, $args['tnid'], 'ciniki.herbalist.recipeUpdate');
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
    // Update the Recipe in the database
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'objectUpdate');
    $rc = ciniki_core_objectUpdate($ciniki, $args['tnid'], 'ciniki.herbalist.recipe', $args['recipe_id'], $args, 0x04);
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
    ciniki_core_hookExec($ciniki, $args['tnid'], 'ciniki', 'web', 'indexObject', array('object'=>'ciniki.herbalist.recipe', 'object_id'=>$args['recipe_id']));

    return array('stat'=>'ok');
}
?>
