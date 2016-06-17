<?php
//
// Description
// ===========
// This method will return all the information about an recipe.
//
// Arguments
// ---------
// api_key:
// auth_token:
// business_id:         The ID of the business the recipe is attached to.
// recipe_id:          The ID of the recipe to get the details for.
//
// Returns
// -------
//
function ciniki_herbalist_recipePDF($ciniki) {
    //
    // Find all the required and optional arguments
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'prepareArgs');
    $rc = ciniki_core_prepareArgs($ciniki, 'no', array(
        'business_id'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Business'),
        'recipe_id'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Recipe'),
        'size'=>array('required'=>'no', 'blank'=>'yes', 'default'=>'1', 'name'=>'Size'),
        ));
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    $args = $rc['args'];
    if( $args['size'] == '' ) {
        $args['size'] = 1;
    }

    //
    // Make sure this module is activated, and
    // check permission to run this function for this business
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'herbalist', 'private', 'checkAccess');
    $rc = ciniki_herbalist_checkAccess($ciniki, $args['business_id'], 'ciniki.herbalist.recipePDF');
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
    ciniki_core_loadMethod($ciniki, 'ciniki', 'users', 'private', 'dateFormat');
    $date_format = ciniki_users_dateFormat($ciniki, 'php');

    //
    // Load the recipe
    //
    $strsql = "SELECT ciniki_herbalist_recipes.id, "
        . "ciniki_herbalist_recipes.name, "
        . "ciniki_herbalist_recipes.units, "
        . "ciniki_herbalist_recipes.yield, "
        . "ciniki_herbalist_recipes.production_time, "
        . "ciniki_herbalist_recipes.materials_cost_per_unit, "
        . "ciniki_herbalist_recipes.time_cost_per_unit, "
        . "ciniki_herbalist_recipes.total_cost_per_unit "
        . "FROM ciniki_herbalist_recipes "
        . "WHERE ciniki_herbalist_recipes.business_id = '" . ciniki_core_dbQuote($ciniki, $args['business_id']) . "' "
        . "AND ciniki_herbalist_recipes.id = '" . ciniki_core_dbQuote($ciniki, $args['recipe_id']) . "' "
        . "";
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbHashQuery');
    $rc = ciniki_core_dbHashQuery($ciniki, $strsql, 'ciniki.herbalist', 'recipe');
    if( $rc['stat'] != 'ok' ) {
        return array('stat'=>'fail', 'err'=>array('pkg'=>'ciniki', 'code'=>'3493', 'msg'=>'Recipe not found', 'err'=>$rc['err']));
    }
    if( !isset($rc['recipe']) ) {
        return array('stat'=>'fail', 'err'=>array('pkg'=>'ciniki', 'code'=>'3494', 'msg'=>'Unable to find Recipe'));
    }
    $recipe = $rc['recipe'];

    //
    // Get the list of recipe ingredients
    //
    $strsql = "SELECT ciniki_herbalist_recipe_ingredients.id, "
        . "ciniki_herbalist_recipe_ingredients.recipe_id, "
        . "ciniki_herbalist_recipe_ingredients.ingredient_id, "
        . "ciniki_herbalist_ingredients.name, "
        . "ciniki_herbalist_ingredients.sorttype, "
        . "ciniki_herbalist_ingredients.units, "
        . "ciniki_herbalist_ingredients.materials_cost_per_unit, "
        . "ciniki_herbalist_ingredients.time_cost_per_unit, "
        . "ciniki_herbalist_ingredients.total_cost_per_unit, "
        . "ciniki_herbalist_recipe_ingredients.quantity "
        . "FROM ciniki_herbalist_recipe_ingredients "
        . "LEFT JOIN ciniki_herbalist_ingredients ON ("
            . "ciniki_herbalist_recipe_ingredients.ingredient_id = ciniki_herbalist_ingredients.id "
            . "AND ciniki_herbalist_ingredients.business_id = '" . ciniki_core_dbQuote($ciniki, $args['business_id']) . "' "
            . ") "
        . "WHERE ciniki_herbalist_recipe_ingredients.business_id = '" . ciniki_core_dbQuote($ciniki, $args['business_id']) . "' "
        . "AND ciniki_herbalist_recipe_ingredients.recipe_id = '" . ciniki_core_dbQuote($ciniki, $args['recipe_id']) . "' "
        . "ORDER BY sorttype, ciniki_herbalist_ingredients.name "
        . "";
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbHashQueryIDTree');
    $rc = ciniki_core_dbHashQueryIDTree($ciniki, $strsql, 'ciniki.herbalist', array(
        array('container'=>'types', 'fname'=>'sorttype', 'fields'=>array('sorttype')),
        array('container'=>'ingredients', 'fname'=>'id', 
            'fields'=>array('id', 'ingredient_id', 'name', 'materials_cost_per_unit', 'time_cost_per_unit', 'total_cost_per_unit', 'units', 'quantity')),
        ));
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    if( isset($rc['types']) ) {
        $recipe['ingredient_types'] = $rc['types'];
        foreach($recipe['ingredient_types'] as $tid => $itype) {    
            foreach($recipe['ingredient_types'][$tid]['ingredients'] as $iid => $ingredient) {    
                $units = '';
                switch ($ingredient['units']) {
                    case '10': $units = 'gm'; break;
                    case '60': $units = 'ml'; break;
                }  
                $recipe['ingredient_types'][$tid]['ingredients'][$iid]['units'] = $units;
                if( isset($args['size']) && $args['size'] > 0 ) {
                    $recipe['ingredient_types'][$tid]['ingredients'][$iid]['quantity'] = bcmul($ingredient['quantity'], $args['size'], 10);
                    $ingredient['quantity'] = bcmul($ingredient['quantity'], $args['size'], 10);
                }
                $recipe['ingredient_types'][$tid]['ingredients'][$iid]['quantity_display'] = (float)$ingredient['quantity'] . ' ' . $units;
                $recipe['ingredient_types'][$tid]['ingredients'][$iid]['quantity'] = (float)$ingredient['quantity'];
                $recipe['ingredient_types'][$tid]['ingredients'][$iid]['total_cost_per_unit_display'] = numfmt_format_currency($intl_currency_fmt, 
                    bcmul($ingredient['total_cost_per_unit'], $ingredient['quantity'], 4), $intl_currency);
            }
        }
    } else {
        $recipe['ingredient_types'] = array();
    }

    if( isset($args['size']) && $args['size'] > 0 ) {
        $recipe['yield'] = bcmul($recipe['yield'], $args['size'], 10);
    }
    switch ($recipe['units']) {
        case '10': $recipe['units_display'] = 'gm'; break;
        case '60': $recipe['units_display'] = 'ml'; break;
    }  

    $dt = new DateTime('now', new DateTimeZone($intl_timezone));
    $recipe['printdate'] = $dt->format($date_format);

    ciniki_core_loadMethod($ciniki, 'ciniki', 'herbalist', 'templates', 'recipePDF');
    $rc = ciniki_herbalist_templates_recipePDF($ciniki, $args['business_id'], $recipe);
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }

    if( isset($rc['pdf']) ) {
        $filename = preg_replace('/[^a-zA-Z0-9_]/', '', preg_replace('/ /', '_', $recipe['name']));
        $rc['pdf']->Output($filename . '.pdf', 'D');
    }

    return array('stat'=>'exit');
}
?>
