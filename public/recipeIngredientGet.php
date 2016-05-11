<?php
//
// Description
// ===========
// This method will return all the information about an recipe ingredient.
//
// Arguments
// ---------
// api_key:
// auth_token:
// business_id:         The ID of the business the recipe ingredient is attached to.
// recipeingredient_id:          The ID of the recipe ingredient to get the details for.
//
// Returns
// -------
//
function ciniki_herbalist_recipeIngredientGet($ciniki) {
    //
    // Find all the required and optional arguments
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'prepareArgs');
    $rc = ciniki_core_prepareArgs($ciniki, 'no', array(
        'business_id'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Business'),
        'recipeingredient_id'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Recipe Ingredient'),
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
    $rc = ciniki_herbalist_checkAccess($ciniki, $args['business_id'], 'ciniki.herbalist.recipeIngredientGet');
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }

    //
    // Load business settings
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'businesses', 'private', 'intlSettings');
    $rc = ciniki_businesses_intlSettings($ciniki, $args['business_id']);
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    $intl_timezone = $rc['settings']['intl-default-timezone'];
    $intl_currency_fmt = numfmt_create($rc['settings']['intl-default-locale'], NumberFormatter::CURRENCY);
    $intl_currency = $rc['settings']['intl-default-currency'];

    ciniki_core_loadMethod($ciniki, 'ciniki', 'users', 'private', 'datetimeFormat');
    $datetime_format = ciniki_users_datetimeFormat($ciniki, 'php');

    //
    // Return default for new Recipe Ingredient
    //
    if( $args['recipeingredient_id'] == 0 ) {
        $recipeingredient = array('id'=>0,
            'recipe_id'=>'',
            'ingredient_id'=>'',
            'quantity'=>'',
        );
    }

    //
    // Get the details for an existing Recipe Ingredient
    //
    else {
        $strsql = "SELECT ciniki_herbalist_recipe_ingredients.id, "
            . "ciniki_herbalist_recipe_ingredients.recipe_id, "
            . "ciniki_herbalist_recipe_ingredients.ingredient_id, "
            . "ciniki_herbalist_recipe_ingredients.quantity "
            . "FROM ciniki_herbalist_recipe_ingredients "
            . "WHERE ciniki_herbalist_recipe_ingredients.business_id = '" . ciniki_core_dbQuote($ciniki, $args['business_id']) . "' "
            . "AND ciniki_herbalist_recipe_ingredients.id = '" . ciniki_core_dbQuote($ciniki, $args['recipeingredient_id']) . "' "
            . "";
        ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbHashQuery');
        $rc = ciniki_core_dbHashQuery($ciniki, $strsql, 'ciniki.herbalist', 'recipeingredient');
        if( $rc['stat'] != 'ok' ) {
            return array('stat'=>'fail', 'err'=>array('pkg'=>'ciniki', 'code'=>'3421', 'msg'=>'Recipe Ingredient not found', 'err'=>$rc['err']));
        }
        if( !isset($rc['recipeingredient']) ) {
            return array('stat'=>'fail', 'err'=>array('pkg'=>'ciniki', 'code'=>'3422', 'msg'=>'Unable to find Recipe Ingredient'));
        }
        $recipeingredient = $rc['recipeingredient'];
    }

    //
    // Get the list of ingredients
    //
    $strsql = "SELECT ciniki_herbalist_ingredients.id, "
        . "ciniki_herbalist_ingredients.name "
        . "FROM ciniki_herbalist_ingredients "
        . "WHERE ciniki_herbalist_ingredients.business_id = '" . ciniki_core_dbQuote($ciniki, $args['business_id']) . "' "
        . "";
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbQueryList2');
    $rc = ciniki_core_dbQueryList2($ciniki, $strsql, 'ciniki.herbalist', 'ingredients');
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    if( isset($rc['ingredients']) ) {
        $ingredients = $rc['ingredients'];
    } else {
        $ingredients = array();
    }

    return array('stat'=>'ok', 'recipeingredient'=>$recipeingredient, 'ingredients'=>$ingredients);
}
?>
