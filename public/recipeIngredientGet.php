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
// tnid:         The ID of the tenant the recipe ingredient is attached to.
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
        'tnid'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Tenant'),
        'recipeingredient_id'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Recipe Ingredient'),
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
    $rc = ciniki_herbalist_checkAccess($ciniki, $args['tnid'], 'ciniki.herbalist.recipeIngredientGet');
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }

    //
    // Load tenant settings
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'tenants', 'private', 'intlSettings');
    $rc = ciniki_tenants_intlSettings($ciniki, $args['tnid']);
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    $intl_timezone = $rc['settings']['intl-default-timezone'];
    $intl_currency_fmt = numfmt_create($rc['settings']['intl-default-locale'], NumberFormatter::CURRENCY);
    $intl_currency = $rc['settings']['intl-default-currency'];

    ciniki_core_loadMethod($ciniki, 'ciniki', 'users', 'private', 'dateFormat');
    $date_format = ciniki_users_dateFormat($ciniki, 'mysql');

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
            . "WHERE ciniki_herbalist_recipe_ingredients.tnid = '" . ciniki_core_dbQuote($ciniki, $args['tnid']) . "' "
            . "AND ciniki_herbalist_recipe_ingredients.id = '" . ciniki_core_dbQuote($ciniki, $args['recipeingredient_id']) . "' "
            . "";
        ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbHashQuery');
        $rc = ciniki_core_dbHashQuery($ciniki, $strsql, 'ciniki.herbalist', 'recipeingredient');
        if( $rc['stat'] != 'ok' ) {
            return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.herbalist.57', 'msg'=>'Recipe Ingredient not found', 'err'=>$rc['err']));
        }
        if( !isset($rc['recipeingredient']) ) {
            return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.herbalist.58', 'msg'=>'Unable to find Recipe Ingredient'));
        }
        $recipeingredient = $rc['recipeingredient'];

        //
        // Get any notes for this ingredients
        //
        ciniki_core_loadMethod($ciniki, 'ciniki', 'herbalist', 'private', 'objectNotes');
        $rc = ciniki_herbalist_objectNotes($ciniki, $args['tnid'], 'ciniki.herbalist.ingredient', $recipeingredient['ingredient_id']);
        if( $rc['stat'] != 'ok' ) {
            return $rc;
        }
        if( isset($rc['notes']) ) {
            $recipeingredient['notes'] = $rc['notes'];
        } else {
            $recipeingredient['notes'] = array();
        }
    }

    //
    // Get the list of ingredients
    //
    $ingredients = array();
    $strsql = "SELECT ciniki_herbalist_ingredients.id, "
        . "ciniki_herbalist_ingredients.name, ciniki_herbalist_ingredients.units "
        . "FROM ciniki_herbalist_ingredients "
        . "WHERE ciniki_herbalist_ingredients.tnid = '" . ciniki_core_dbQuote($ciniki, $args['tnid']) . "' "
        . "ORDER BY ciniki_herbalist_ingredients.name "
        . "";
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbHashQuery');
    $rc = ciniki_core_dbHashQuery($ciniki, $strsql, 'ciniki.herbalist', 'ingredient');
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    if( isset($rc['rows']) ) {
        $ingredients = $rc['rows'];
        foreach($ingredients as $iid => $ingredient) {
            switch ($ingredient['units']) {
                case 10: $ingredients[$iid]['name'] .= ' (g)'; break;
                case 60: $ingredients[$iid]['name'] .= ' (ml)'; break;
            }
        }
    }

    return array('stat'=>'ok', 'recipeingredient'=>$recipeingredient, 'ingredients'=>$ingredients);
}
?>
