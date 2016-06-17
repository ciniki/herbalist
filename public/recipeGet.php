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
function ciniki_herbalist_recipeGet($ciniki) {
    //
    // Find all the required and optional arguments
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'prepareArgs');
    $rc = ciniki_core_prepareArgs($ciniki, 'no', array(
        'business_id'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Business'),
        'recipe_id'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Recipe'),
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
    $rc = ciniki_herbalist_checkAccess($ciniki, $args['business_id'], 'ciniki.herbalist.recipeGet');
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
    // Return default for new Recipe
    //
    if( $args['recipe_id'] == 0 ) {
        $recipe = array('id'=>0,
            'name'=>'',
            'flags'=>0,
            'units'=>'',
            'yield'=>'',
            'production_time'=>'',
            'materials_cost_per_unit'=>'',
            'time_cost_per_unit'=>'',
            'total_cost_per_unit'=>'',
            'ingredient_types'=>array(),
            'notes'=>array(),
        );
    }

    //
    // Get the details for an existing Recipe
    //
    else {
        $strsql = "SELECT ciniki_herbalist_recipes.id, "
            . "ciniki_herbalist_recipes.name, "
            . "ciniki_herbalist_recipes.flags, "
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
            return array('stat'=>'fail', 'err'=>array('pkg'=>'ciniki', 'code'=>'3384', 'msg'=>'Recipe not found', 'err'=>$rc['err']));
        }
        if( !isset($rc['recipe']) ) {
            return array('stat'=>'fail', 'err'=>array('pkg'=>'ciniki', 'code'=>'3385', 'msg'=>'Unable to find Recipe'));
        }
        $recipe = $rc['recipe'];
        $recipe['materials_cost_per_unit'] = numfmt_format_currency($intl_currency_fmt, $recipe['materials_cost_per_unit'], $intl_currency);
        $recipe['time_cost_per_unit'] = numfmt_format_currency($intl_currency_fmt, $recipe['time_cost_per_unit'], $intl_currency);
        $recipe['total_cost_per_unit'] = numfmt_format_currency($intl_currency_fmt, $recipe['total_cost_per_unit'], $intl_currency);

        //
        // Get the list of recipe ingredients
        //
        $recipe['recipeingredient_ids'] = array();
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
                    $recipe['recipeingredient_ids'][] = $ingredient['id'];
                    $units = '';
                    switch ($ingredient['units']) {
                        case '10': $units = ' g'; break;
                        case '60': $units = ' ml'; break;
                    }  
                    $recipe['ingredient_types'][$tid]['ingredients'][$iid]['quantity_display'] = (float)$ingredient['quantity'] . $units;
                    $recipe['ingredient_types'][$tid]['ingredients'][$iid]['quantity'] = (float)$ingredient['quantity'];
                    $recipe['ingredient_types'][$tid]['ingredients'][$iid]['total_cost_per_unit_display'] = numfmt_format_currency($intl_currency_fmt, 
                        bcmul($ingredient['total_cost_per_unit'], $ingredient['quantity'], 4), $intl_currency);
                }
            }
        } else {
            $recipe['ingredient_types'] = array();
        }

        //
        // Get the list of recipe batches
        //
        $strsql = "SELECT ciniki_herbalist_recipe_batches.id, "
            . "ciniki_herbalist_recipe_batches.production_date, "
            . "ciniki_herbalist_recipe_batches.size, "
            . "ciniki_herbalist_recipe_batches.yield, "
            . "ciniki_herbalist_recipe_batches.production_time, "
            . "ciniki_herbalist_recipe_batches.materials_cost_per_unit, "
            . "ciniki_herbalist_recipe_batches.time_cost_per_unit, "
            . "ciniki_herbalist_recipe_batches.total_cost_per_unit "
            . "FROM ciniki_herbalist_recipe_batches "
            . "WHERE ciniki_herbalist_recipe_batches.business_id = '" . ciniki_core_dbQuote($ciniki, $args['business_id']) . "' "
            . "AND ciniki_herbalist_recipe_batches.recipe_id = '" . ciniki_core_dbQuote($ciniki, $args['recipe_id']) . "' "
            . "ORDER BY ciniki_herbalist_recipe_batches.production_date DESC "
            . "";
        ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbHashQueryArrayTree');
        $rc = ciniki_core_dbHashQueryArrayTree($ciniki, $strsql, 'ciniki.herbalist', array(
            array('container'=>'batches', 'fname'=>'id', 
                'fields'=>array('id', 'production_date', 'size', 'yield', 'production_time', 'materials_cost_per_unit', 'time_cost_per_unit', 'total_cost_per_unit'),
                'utctotz'=>array('production_date'=>array('format'=>$date_format, 'timezone'=>'UTC')),
                ),
            ));
        if( $rc['stat'] != 'ok' ) {
            return $rc;
        }
        if( isset($rc['batches']) ) {
            $recipe['batches'] = $rc['batches'];
            foreach($recipe['batches'] as $bid => $batch) {    
                $recipe['batches'][$bid]['size'] = (float)$batch['size'];
                $recipe['batches'][$bid]['materials_cost_per_unit_display'] = numfmt_format_currency($intl_currency_fmt, $batch['materials_cost_per_unit'], $intl_currency);
                $recipe['batches'][$bid]['time_cost_per_unit_display'] = numfmt_format_currency($intl_currency_fmt, $batch['time_cost_per_unit'], $intl_currency);
                $recipe['batches'][$bid]['total_cost_per_unit_display'] = numfmt_format_currency($intl_currency_fmt, $batch['total_cost_per_unit'], $intl_currency);
            }
        } else {
            $recipe['batches'] = array();
        }

        //
        // Get any notes for this recipe
        //
        ciniki_core_loadMethod($ciniki, 'ciniki', 'herbalist', 'private', 'objectNotes');
        $rc = ciniki_herbalist_objectNotes($ciniki, $args['business_id'], 'ciniki.herbalist.recipe', $args['recipe_id']);
        if( $rc['stat'] != 'ok' ) {
            return $rc;
        }
        if( isset($rc['notes']) ) {
            $recipe['notes'] = $rc['notes'];
        } else {
            $recipe['notes'] = array();
        }
    }

    return array('stat'=>'ok', 'recipe'=>$recipe);
}
?>
