<?php
//
// Description
// -----------
// This method will delete an ingredient.
//
// Arguments
// ---------
// api_key:
// auth_token:
// tnid:            The ID of the tenant the ingredient is attached to.
// ingredient_id:            The ID of the ingredient to be removed.
//
// Returns
// -------
// <rsp stat="ok">
//
function ciniki_herbalist_ingredientDelete(&$ciniki) {
    //
    // Find all the required and optional arguments
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'prepareArgs');
    $rc = ciniki_core_prepareArgs($ciniki, 'no', array(
        'tnid'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Tenant'),
        'ingredient_id'=>array('required'=>'yes', 'blank'=>'yes', 'name'=>'Ingredient'),
        ));
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    $args = $rc['args'];

    //
    // Check access to tnid as owner
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'herbalist', 'private', 'checkAccess');
    $rc = ciniki_herbalist_checkAccess($ciniki, $args['tnid'], 'ciniki.herbalist.ingredientDelete');
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }

    //
    // Get the current settings for the ingredient
    //
    $strsql = "SELECT id, uuid "
        . "FROM ciniki_herbalist_ingredients "
        . "WHERE tnid = '" . ciniki_core_dbQuote($ciniki, $args['tnid']) . "' "
        . "AND id = '" . ciniki_core_dbQuote($ciniki, $args['ingredient_id']) . "' "
        . "";
    $rc = ciniki_core_dbHashQuery($ciniki, $strsql, 'ciniki.herbalist', 'ingredient');
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    if( !isset($rc['ingredient']) ) {
        return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.herbalist.18', 'msg'=>'Ingredient does not exist.'));
    }
    $ingredient = $rc['ingredient'];

    //
    // Check to make sure the ingredient is not used in any recipes
    //
    $strsql = "SELECT COUNT(*) AS num_recipes "
        . "FROM ciniki_herbalist_recipe_ingredients "
        . "WHERE ingredient_id = '" . ciniki_core_dbQuote($ciniki, $args['ingredient_id']) . "' "
        . "AND tnid = '" . ciniki_core_dbQuote($ciniki, $args['tnid']) . "' "
        . "";
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbSingleCount');
    $rc = ciniki_core_dbSingleCount($ciniki, $strsql, 'ciniki.herbalist', 'num');
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    if( $rc['num'] > 0 ) {
        return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.herbalist.19', 'msg'=>'You still have ' . $rc['num'] . ' recipe' . ($rc['num']>1?'s':'') .' using this ingredient.'));
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
    // Remove any note references
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'herbalist', 'private', 'objectNotesRefsDelete');
    $rc = ciniki_herbalist_objectNotesRefsDelete($ciniki, $args['tnid'], 'ciniki.herbalist.ingredient', $args['ingredient_id']);
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }

    //
    // Remove the ingredient
    //
    $rc = ciniki_core_objectDelete($ciniki, $args['tnid'], 'ciniki.herbalist.ingredient',
        $args['ingredient_id'], $ingredient['uuid'], 0x04);
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
