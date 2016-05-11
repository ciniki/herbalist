<?php
//
// Description
// -----------
// This method will return the list of Recipe Ingredients for a business.
//
// Arguments
// ---------
// api_key:
// auth_token:
// business_id:        The ID of the business to get Recipe Ingredient for.
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
        'business_id'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Business'),
        ));
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    $args = $rc['args'];

    //
    // Check access to business_id as owner, or sys admin.
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'herbalist', 'private', 'checkAccess');
    $rc = ciniki_herbalist_checkAccess($ciniki, $args['business_id'], 'ciniki.herbalist.recipeIngredientList');
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
        . "WHERE ciniki_herbalist_recipe_ingredients.business_id = '" . ciniki_core_dbQuote($ciniki, $args['business_id']) . "' "
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
