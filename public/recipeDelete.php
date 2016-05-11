<?php
//
// Description
// -----------
// This method will delete an recipe.
//
// Arguments
// ---------
// api_key:
// auth_token:
// business_id:            The ID of the business the recipe is attached to.
// recipe_id:            The ID of the recipe to be removed.
//
// Returns
// -------
// <rsp stat="ok">
//
function ciniki_herbalist_recipeDelete(&$ciniki) {
    //
    // Find all the required and optional arguments
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'prepareArgs');
    $rc = ciniki_core_prepareArgs($ciniki, 'no', array(
        'business_id'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Business'),
        'recipe_id'=>array('required'=>'yes', 'blank'=>'yes', 'name'=>'Recipe'),
        ));
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    $args = $rc['args'];

    //
    // Check access to business_id as owner
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'herbalist', 'private', 'checkAccess');
    $rc = ciniki_herbalist_checkAccess($ciniki, $args['business_id'], 'ciniki.herbalist.recipeDelete');
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }

    //
    // Get the current settings for the recipe
    //
    $strsql = "SELECT id, uuid "
        . "FROM ciniki_herbalist_recipes "
        . "WHERE business_id = '" . ciniki_core_dbQuote($ciniki, $args['business_id']) . "' "
        . "AND id = '" . ciniki_core_dbQuote($ciniki, $args['recipe_id']) . "' "
        . "";
    $rc = ciniki_core_dbHashQuery($ciniki, $strsql, 'ciniki.herbalist', 'recipe');
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    if( !isset($rc['recipe']) ) {
        return array('stat'=>'fail', 'err'=>array('pkg'=>'ciniki', 'code'=>'3383', 'msg'=>'Recipe does not exist.'));
    }
    $recipe = $rc['recipe'];

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
    // Remove the recipe
    //
    $rc = ciniki_core_objectDelete($ciniki, $args['business_id'], 'ciniki.herbalist.recipe',
        $args['recipe_id'], $recipe['uuid'], 0x04);
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
