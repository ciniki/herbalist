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
// tnid:            The ID of the tenant the recipe is attached to.
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
        'tnid'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Tenant'),
        'recipe_id'=>array('required'=>'yes', 'blank'=>'yes', 'name'=>'Recipe'),
        ));
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    $args = $rc['args'];

    //
    // Check access to tnid as owner
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'herbalist', 'private', 'checkAccess');
    $rc = ciniki_herbalist_checkAccess($ciniki, $args['tnid'], 'ciniki.herbalist.recipeDelete');
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }

    //
    // Get the current settings for the recipe
    //
    $strsql = "SELECT id, uuid "
        . "FROM ciniki_herbalist_recipes "
        . "WHERE tnid = '" . ciniki_core_dbQuote($ciniki, $args['tnid']) . "' "
        . "AND id = '" . ciniki_core_dbQuote($ciniki, $args['recipe_id']) . "' "
        . "";
    $rc = ciniki_core_dbHashQuery($ciniki, $strsql, 'ciniki.herbalist', 'recipe');
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    if( !isset($rc['recipe']) ) {
        return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.herbalist.51', 'msg'=>'Recipe does not exist.'));
    }
    $recipe = $rc['recipe'];

    //
    // Check if the recipe is used as any ingredients
    //
    $strsql = "SELECT COUNT(*) AS num_recipes "
        . "FROM ciniki_herbalist_ingredients "
        . "WHERE recipe_id = '" . ciniki_core_dbQuote($ciniki, $args['recipe_id']) . "' "
        . "AND tnid = '" . ciniki_core_dbQuote($ciniki, $args['tnid']) . "' "
        . "";
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbSingleCount');
    $rc = ciniki_core_dbSingleCount($ciniki, $strsql, 'ciniki.herbalist', 'num');
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    if( $rc['num'] > 0 ) {
        return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.herbalist.52', 'msg'=>'You still have ' . $rc['num'] . ' ingredient' . ($rc['num']>1?'s':'') .' using this recipe.'));
    }

    //
    // Check if recipe is used for any products
    //
    $strsql = "SELECT COUNT(*) AS num_recipes "
        . "FROM ciniki_herbalist_product_versions "
        . "WHERE recipe_id = '" . ciniki_core_dbQuote($ciniki, $args['recipe_id']) . "' "
        . "AND tnid = '" . ciniki_core_dbQuote($ciniki, $args['tnid']) . "' "
        . "";
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbSingleCount');
    $rc = ciniki_core_dbSingleCount($ciniki, $strsql, 'ciniki.herbalist', 'num');
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    if( $rc['num'] > 0 ) {
        return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.herbalist.53', 'msg'=>'You still have ' . $rc['num'] . ' product' . ($rc['num']>1?'s':'') .' using this recipe.'));
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
    // Remove the recipe ingredients
    //
    $strsql = "SELECT id, uuid "
        . "FROM ciniki_herbalist_recipe_ingredients "
        . "WHERE recipe_id = '" . ciniki_core_dbQuote($ciniki, $args['recipe_id']) . "' "
        . "AND tnid = '" . ciniki_core_dbQuote($ciniki, $args['tnid']) . "' "
        . "";
    $rc = ciniki_core_dbHashQuery($ciniki, $strsql, 'ciniki.herbalist', 'ingredient');
    if( $rc['stat'] != 'ok' ) {
        ciniki_core_dbTransactionRollback($ciniki, 'ciniki.herbalist');
        return $rc;
    }
    if( isset($rc['rows']) && count($rc['rows']) > 0 ) {
        foreach($rc['rows'] as $row) {
            $rc = ciniki_core_objectDelete($ciniki, $args['tnid'], 'ciniki.herbalist.recipeingredient', $row['id'], $row['uuid'], 0x04);
            if( $rc['stat'] != 'ok' ) {
                ciniki_core_dbTransactionRollback($ciniki, 'ciniki.herbalist');
                return $rc;
            }
        }
    }

    //
    // Remove the recipe
    //
    $rc = ciniki_core_objectDelete($ciniki, $args['tnid'], 'ciniki.herbalist.recipe', $args['recipe_id'], $recipe['uuid'], 0x04);
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
