<?php
//
// Description
// ===========
// This method will return all the information about an ingredient.
//
// Arguments
// ---------
// api_key:
// auth_token:
// business_id:         The ID of the business the ingredient is attached to.
// ingredient_id:          The ID of the ingredient to get the details for.
//
// Returns
// -------
//
function ciniki_herbalist_ingredientGet($ciniki) {
    //
    // Find all the required and optional arguments
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'prepareArgs');
    $rc = ciniki_core_prepareArgs($ciniki, 'no', array(
        'business_id'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Business'),
        'ingredient_id'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Ingredient'),
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
    $rc = ciniki_herbalist_checkAccess($ciniki, $args['business_id'], 'ciniki.herbalist.ingredientGet');
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

    ciniki_core_loadMethod($ciniki, 'ciniki', 'users', 'private', 'dateFormat');
    $date_format = ciniki_users_dateFormat($ciniki, 'mysql');

    //
    // Return default for new Ingredient
    //
    if( $args['ingredient_id'] == 0 ) {
        $ingredient = array('id'=>0,
            'name'=>'',
            'subname'=>'',
            'sorttype'=>0,
            'plant_id'=>'0',
            'recipe_id'=>'0',
            'units'=>'',
            'costing_quantity'=>'0',
            'costing_price'=>'0',
            'cost_per_unit'=>'0',
            'warnings'=>'',
            'notes'=>array(),
        );
    }

    //
    // Get the details for an existing Ingredient
    //
    else {
        $strsql = "SELECT ciniki_herbalist_ingredients.id, "
            . "ciniki_herbalist_ingredients.name, "
            . "ciniki_herbalist_ingredients.subname, "
            . "ciniki_herbalist_ingredients.sorttype, "
            . "ciniki_herbalist_ingredients.plant_id, "
            . "ciniki_herbalist_ingredients.recipe_id, "
            . "ciniki_herbalist_ingredients.units, "
            . "ciniki_herbalist_ingredients.costing_quantity, "
            . "ciniki_herbalist_ingredients.costing_time, "
            . "ciniki_herbalist_ingredients.costing_price, "
            . "ciniki_herbalist_ingredients.materials_cost_per_unit, "
            . "ciniki_herbalist_ingredients.time_cost_per_unit, "
            . "ciniki_herbalist_ingredients.total_cost_per_unit, "
            . "ciniki_herbalist_ingredients.total_time_per_unit, "
            . "ciniki_herbalist_ingredients.warnings "
            . "FROM ciniki_herbalist_ingredients "
            . "WHERE ciniki_herbalist_ingredients.business_id = '" . ciniki_core_dbQuote($ciniki, $args['business_id']) . "' "
            . "AND ciniki_herbalist_ingredients.id = '" . ciniki_core_dbQuote($ciniki, $args['ingredient_id']) . "' "
            . "";
        ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbHashQuery');
        $rc = ciniki_core_dbHashQuery($ciniki, $strsql, 'ciniki.herbalist', 'ingredient');
        if( $rc['stat'] != 'ok' ) {
            return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.herbalist.20', 'msg'=>'Ingredient not found', 'err'=>$rc['err']));
        }
        if( !isset($rc['ingredient']) ) {
            return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.herbalist.21', 'msg'=>'Unable to find Ingredient'));
        }
        $ingredient = $rc['ingredient'];
        $ingredient['costing_quantity'] = (float)$ingredient['costing_quantity'];
        $ingredient['costing_price'] = numfmt_format_currency($intl_currency_fmt, $ingredient['costing_price'], $intl_currency);
        $ingredient['materials_cost_per_unit'] = numfmt_format_currency($intl_currency_fmt, $ingredient['materials_cost_per_unit'], $intl_currency);
        $ingredient['time_cost_per_unit'] = numfmt_format_currency($intl_currency_fmt, $ingredient['time_cost_per_unit'], $intl_currency);
        $ingredient['total_cost_per_unit'] = numfmt_format_currency($intl_currency_fmt, $ingredient['total_cost_per_unit'], $intl_currency);

        //
        // Get any notes for this ingredients
        //
        ciniki_core_loadMethod($ciniki, 'ciniki', 'herbalist', 'private', 'objectNotes');
        $rc = ciniki_herbalist_objectNotes($ciniki, $args['business_id'], 'ciniki.herbalist.ingredient', $args['ingredient_id']);
        if( $rc['stat'] != 'ok' ) {
            return $rc;
        }
        if( isset($rc['notes']) ) {
            $ingredient['notes'] = $rc['notes'];
        } else {
            $ingredient['notes'] = array();
        }
    }

    //
    // Get the list of recipes
    //
    $strsql = "SELECT ciniki_herbalist_recipes.id, ciniki_herbalist_recipes.name "
        . "FROM ciniki_herbalist_recipes "
        . "WHERE ciniki_herbalist_recipes.business_id = '" . ciniki_core_dbQuote($ciniki, $args['business_id']) . "' "
        . "ORDER BY name "
        . "";
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbQueryList2');
    $rc = ciniki_core_dbQueryList2($ciniki, $strsql, 'ciniki.herbalist', 'recipes');
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    if( isset($rc['recipes']) ) {
        $recipes = $rc['recipes'];
    } else {
        $recipes = array();
    }

    return array('stat'=>'ok', 'ingredient'=>$ingredient, 'recipes'=>$recipes);
}
?>
