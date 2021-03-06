<?php
//
// Description
// -----------
// This method will delete an recipe ingredient.
//
// Arguments
// ---------
// api_key:
// auth_token:
// tnid:            The ID of the tenant the recipe ingredient is attached to.
// recipeingredient_id:            The ID of the recipe ingredient to be removed.
//
// Returns
// -------
// <rsp stat="ok">
//
function ciniki_herbalist_recipeIngredientDelete(&$ciniki) {
    //
    // Find all the required and optional arguments
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'prepareArgs');
    $rc = ciniki_core_prepareArgs($ciniki, 'no', array(
        'tnid'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Tenant'),
        'recipeingredient_id'=>array('required'=>'yes', 'blank'=>'yes', 'name'=>'Recipe Ingredient'),
        ));
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    $args = $rc['args'];

    //
    // Check access to tnid as owner
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'herbalist', 'private', 'checkAccess');
    $rc = ciniki_herbalist_checkAccess($ciniki, $args['tnid'], 'ciniki.herbalist.recipeIngredientDelete');
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }

    //
    // Get the current settings for the recipe ingredient
    //
    $strsql = "SELECT id, uuid "
        . "FROM ciniki_herbalist_recipe_ingredients "
        . "WHERE tnid = '" . ciniki_core_dbQuote($ciniki, $args['tnid']) . "' "
        . "AND id = '" . ciniki_core_dbQuote($ciniki, $args['recipeingredient_id']) . "' "
        . "";
    $rc = ciniki_core_dbHashQuery($ciniki, $strsql, 'ciniki.herbalist', 'recipeingredient');
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    if( !isset($rc['recipeingredient']) ) {
        return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.herbalist.56', 'msg'=>'Recipe Ingredient does not exist.'));
    }
    $recipeingredient = $rc['recipeingredient'];

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
    // Remove the recipeingredient
    //
    $rc = ciniki_core_objectDelete($ciniki, $args['tnid'], 'ciniki.herbalist.recipeingredient',
        $args['recipeingredient_id'], $recipeingredient['uuid'], 0x04);
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
