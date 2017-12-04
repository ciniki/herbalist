<?php
//
// Description
// -----------
// This method will return the list of Recipe Ingredients for a tenant.
//
// Arguments
// ---------
// api_key:
// auth_token:
// tnid:        The ID of the tenant to get Recipe Ingredient for.
//
// Returns
// -------
//
function ciniki_herbalist_recipeIngredientList($ciniki) {
    //
    // Find all the required and optional arguments
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'prepareArgs');
    $rc = ciniki_core_prepareArgs($ciniki, 'no', array(
        'tnid'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Tenant'),
        ));
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    $args = $rc['args'];

    //
    // Check access to tnid as owner, or sys admin.
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'herbalist', 'private', 'checkAccess');
    $rc = ciniki_herbalist_checkAccess($ciniki, $args['tnid'], 'ciniki.herbalist.recipeIngredientList');
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }

    //
    // Get the list of recipeingredients
    //
    $strsql = "SELECT ciniki_herbalist_recipe_ingredients.id, "
        . "ciniki_herbalist_recipe_ingredients.recipe_id, "
        . "ciniki_herbalist_recipe_ingredients.ingredient_id, "
        . "ciniki_herbalist_recipe_ingredients.quantity "
        . "FROM ciniki_herbalist_recipe_ingredients "
        . "WHERE ciniki_herbalist_recipe_ingredients.tnid = '" . ciniki_core_dbQuote($ciniki, $args['tnid']) . "' "
        . "";
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbHashQueryArrayTree');
    $rc = ciniki_core_dbHashQueryArrayTree($ciniki, $strsql, 'ciniki.herbalist', array(
        array('container'=>'recipeingredients', 'fname'=>'id', 
            'fields'=>array('id', 'recipe_id', 'ingredient_id', 'quantity')),
        ));
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    if( isset($rc['recipeingredients']) ) {
        $recipeingredients = $rc['recipeingredients'];
    } else {
        $recipeingredients = array();
    }

    return array('stat'=>'ok', 'recipeingredients'=>$recipeingredients);
}
?>
