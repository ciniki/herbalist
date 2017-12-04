<?php
//
// Description
// -----------
// This method will add a new ingredient for the tenant.
//
// Arguments
// ---------
// api_key:
// auth_token:
// tnid:        The ID of the tenant to add the Ingredient to.
//
// Returns
// -------
// <rsp stat="ok" id="42">
//
function ciniki_herbalist_ingredientAdd(&$ciniki) {
    //
    // Find all the required and optional arguments
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'prepareArgs');
    $rc = ciniki_core_prepareArgs($ciniki, 'no', array(
        'tnid'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Tenant'),
        'name'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Name'),
        'subname'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Sub Name'),
        'sorttype'=>array('required'=>'yes', 'blank'=>'no', 'validlist'=>array('30', '60', '90'), 'name'=>'Type'),
        'plant_id'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Plant'),
        'recipe_id'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Recipe'),
        'units'=>array('required'=>'yes', 'blank'=>'no', 'validlist'=>array('10', '60'), 'name'=>'Units'),
        'costing_quantity'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Purchase Quantity'),
        'costing_price'=>array('required'=>'no', 'blank'=>'yes', 'type'=>'currency', 'name'=>'Purchase Price'),
        'cost_per_unit'=>array('required'=>'no', 'blank'=>'yes', 'type'=>'currency', 'name'=>'Cost per Unit'),
        'warnings'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Warnings'),
        ));
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    $args = $rc['args'];

    //
    // Check access to tnid as owner
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'herbalist', 'private', 'checkAccess');
    $rc = ciniki_herbalist_checkAccess($ciniki, $args['tnid'], 'ciniki.herbalist.ingredientAdd');
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
    // Add the ingredient to the database
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'objectAdd');
    $rc = ciniki_core_objectAdd($ciniki, $args['tnid'], 'ciniki.herbalist.ingredient', $args, 0x04);
    if( $rc['stat'] != 'ok' ) {
        ciniki_core_dbTransactionRollback($ciniki, 'ciniki.herbalist');
        return $rc;
    }
    $ingredient_id = $rc['id'];

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
    ciniki_core_hookExec($ciniki, $args['tnid'], 'ciniki', 'web', 'indexObject', array('object'=>'ciniki.herbalist.ingredient', 'object_id'=>$ingredient_id));

    return array('stat'=>'ok', 'id'=>$ingredient_id);
}
?>
