<?php
//
// Description
// ===========
// This method will return all the information about an recipe batch.
//
// Arguments
// ---------
// api_key:
// auth_token:
// business_id:         The ID of the business the recipe batch is attached to.
// batch_id:          The ID of the recipe batch to get the details for.
//
// Returns
// -------
//
function ciniki_herbalist_recipeBatchGet($ciniki) {
    //
    // Find all the required and optional arguments
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'prepareArgs');
    $rc = ciniki_core_prepareArgs($ciniki, 'no', array(
        'business_id'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Business'),
        'batch_id'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Recipe Batch'),
        'recipe_id'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Recipe ID'),
        'labels'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Labels'),
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
    $rc = ciniki_herbalist_checkAccess($ciniki, $args['business_id'], 'ciniki.herbalist.recipeBatchGet');
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }

	//
	// Get the settings
	//
    $settings = array();
	$rc = ciniki_core_dbDetailsQueryDash($ciniki, 'ciniki_herbalist_settings', 'business_id', $args['business_id'], 'ciniki.herbalist', 'settings', '');
	if( $rc['stat'] == 'ok' && isset($rc['settings']) ) {
		$settings = $rc['settings'];
	}

    //
    // Load the production hourly wage
    //
    $hourly_wage = 0;
    if( isset($settings['production-hourly-wage']) && $settings['production-hourly-wage'] > 0 ) {
        $hourly_wage = $settings['production-hourly-wage'];
    }
    $minute_wage = 0;
    if( $hourly_wage > 0 ) {
        $minute_wage = bcdiv($hourly_wage, 60, 10);
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
    // Return default for new Recipe Batch
    //
    if( $args['batch_id'] == 0 ) {
        $batch = array('id'=>0,
            'recipe_id'=>$args['recipe_id'],
            'production_date'=>'',
            'size'=>1,
            'yield'=>'',
            'production_time'=>'',
            'materials_cost_per_unit'=>'0',
            'time_cost_per_unit'=>'0',
            'total_cost_per_unit'=>'0',
            'notes'=>'',
        );
        $dt = new DateTime('now', new DateTimeZone($intl_timezone));
        $batch['production_date'] = $dt->format($date_format);

        $strsql = "SELECT yield, production_time "
            . "FROM ciniki_herbalist_recipes "
            . "WHERE ciniki_herbalist_recipes.business_id = '" . ciniki_core_dbQuote($ciniki, $args['business_id']) . "' "
            . "AND ciniki_herbalist_recipes.id = '" . ciniki_core_dbQuote($ciniki, $args['recipe_id']) . "' "
            . "";
        $rc = ciniki_core_dbHashQuery($ciniki, $strsql, 'ciniki.herbalist', 'recipe');
        if( $rc['stat'] != 'ok' ) {
            return $rc;
        }
        if( isset($rc['recipe']) ) {
            $batch['yield'] = $rc['recipe']['yield'];
            $batch['production_time'] = $rc['recipe']['production_time'];
        }
    }

    //
    // Get the details for an existing Recipe Batch
    //
    else {
        $strsql = "SELECT ciniki_herbalist_recipe_batches.id, "
            . "ciniki_herbalist_recipe_batches.recipe_id, "
            . "ciniki_herbalist_recipe_batches.production_date, "
            . "ciniki_herbalist_recipe_batches.size, "
            . "ciniki_herbalist_recipe_batches.yield, "
            . "ciniki_herbalist_recipe_batches.production_time, "
            . "ciniki_herbalist_recipe_batches.materials_cost_per_unit, "
            . "ciniki_herbalist_recipe_batches.time_cost_per_unit, "
            . "ciniki_herbalist_recipe_batches.total_cost_per_unit, "
            . "ciniki_herbalist_recipe_batches.notes "
            . "FROM ciniki_herbalist_recipe_batches "
            . "WHERE ciniki_herbalist_recipe_batches.business_id = '" . ciniki_core_dbQuote($ciniki, $args['business_id']) . "' "
            . "AND ciniki_herbalist_recipe_batches.id = '" . ciniki_core_dbQuote($ciniki, $args['batch_id']) . "' "
            . "";
        ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbHashQuery');
        $rc = ciniki_core_dbHashQuery($ciniki, $strsql, 'ciniki.herbalist', 'batch');
        if( $rc['stat'] != 'ok' ) {
            return array('stat'=>'fail', 'err'=>array('pkg'=>'ciniki', 'code'=>'3496', 'msg'=>'Recipe Batch not found', 'err'=>$rc['err']));
        }
        if( !isset($rc['batch']) ) {
            return array('stat'=>'fail', 'err'=>array('pkg'=>'ciniki', 'code'=>'3497', 'msg'=>'Unable to find Recipe Batch'));
        }
        $batch = $rc['batch'];

        $dt = new DateTime($batch['production_date'], new DateTimeZone('UTC'));
        $batch['production_date'] = $dt->format($date_format);
    }

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
        . "AND ciniki_herbalist_recipe_ingredients.recipe_id = '" . ciniki_core_dbQuote($ciniki, $batch['recipe_id']) . "' "
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
    $materials_cost = 0;
    $time_cost = 0;
    $ingredients = array();
    if( isset($rc['types']) ) {
        $batch['ingredient_types'] = $rc['types'];
        //
        // Setup the ingredients for display
        //
        foreach($batch['ingredient_types'] as $tid => $itype) {    
            foreach($batch['ingredient_types'][$tid]['ingredients'] as $iid => $ingredient) {    
                $units = '';
                switch ($ingredient['units']) {
                    case '10': $units = 'gm'; break;
                    case '60': $units = 'ml'; break;
                }
                $ingredients[] = $ingredient;
                $quantity = bcmul($ingredient['quantity'], $batch['size'], 4);
                $materials_cost = bcadd($materials_cost, bcmul($quantity, $ingredient['materials_cost_per_unit'], 10), 10);
                $time_cost = bcadd($time_cost, bcmul($quantity, $ingredient['time_cost_per_unit'], 10), 10);
                $batch['ingredient_types'][$tid]['ingredients'][$iid]['units'] = $units;
                $batch['ingredient_types'][$tid]['ingredients'][$iid]['quantity_display'] = (float)$quantity . ' ' . $units;
                $batch['ingredient_types'][$tid]['ingredients'][$iid]['quantity'] = (float)$ingredient['quantity'];
                $batch['ingredient_types'][$tid]['ingredients'][$iid]['total_cost_per_unit_display'] = numfmt_format_currency($intl_currency_fmt, 
                    bcmul($ingredient['total_cost_per_unit'], $quantity, 4), $intl_currency);
            }
        }
        //
        // sort the ingredients by quantity
        //
        uasort($ingredients, function($a, $b) { 
            if( $a == $b ) {
                return 0;
            } 
            return ($a < $b ? -1 : 1);
        });
       
    } else {
        $batch['ingredient_types'] = array();
    }

    if( $batch['yield'] > 0 ) {
        $materials_cost_per_unit = bcdiv($materials_cost, $batch['yield'], 10);
        $time_cost_per_unit = bcdiv($time_cost, $batch['yield'], 10);
    } else {
        $materials_cost_per_unit = 0;
        $time_cost_per_unit = 0;
    }
    $time_cost = bcadd($time_cost, bcmul($minute_wage, $batch['production_time'], 10), 10);
    $total_cost = bcadd($materials_cost, $time_cost, 10);
    $total_cost_per_unit = bcadd($materials_cost_per_unit, $time_cost_per_unit, 10);


    //
    // Get the product versions that use this recipe
    //
    $strsql = "SELECT ciniki_herbalist_product_versions.id, "
        . "ciniki_herbalist_product_versions.name, "
        . "ciniki_herbalist_product_versions.recipe_quantity, "
        . "IFNULL(ciniki_herbalist_containers.cost_per_unit, 0) AS container_cost, "
        . "ciniki_herbalist_product_versions.wholesale_price, "
        . "ciniki_herbalist_product_versions.retail_price "
        . "FROM ciniki_herbalist_product_versions "
        . "LEFT JOIN ciniki_herbalist_containers ON ("
            . "ciniki_herbalist_product_versions.container_id = ciniki_herbalist_containers.id "
            . "AND ciniki_herbalist_containers.business_id = '" . ciniki_core_dbQuote($ciniki, $args['business_id']) . "' "
            . ") "
        . "WHERE ciniki_herbalist_product_versions.business_id = '" . ciniki_core_dbQuote($ciniki, $args['business_id']) . "' "
        . "AND ciniki_herbalist_product_versions.recipe_id = '" . ciniki_core_dbQuote($ciniki, $batch['recipe_id']) . "' "
        . "ORDER BY ciniki_herbalist_product_versions.name "
        . "";
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbHashQueryArrayTree');
    $rc = ciniki_core_dbHashQueryArrayTree($ciniki, $strsql, 'ciniki.herbalist', array(
        array('container'=>'productversions', 'fname'=>'id', 
            'fields'=>array('id', 'name', 'recipe_quantity', 'container_cost', 'wholesale_price', 'retail_price')),
        ));
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    if( isset($rc['productversions']) ) {
        $batch['productversions'] = $rc['productversions'];
        foreach($batch['productversions'] as $vid => $version) {
            $total_cost = $version['container_cost'] + bcmul($version['recipe_quantity'], $total_cost_per_unit, 10);
            $batch['productversions'][$vid]['total_cost'] = $total_cost;
            $batch['productversions'][$vid]['total_cost_display'] = numfmt_format_currency($intl_currency_fmt, $total_cost, $intl_currency);
        }
    } else {
        $batch['productversions'] = array();
    }

    //
    // Prepare batch for display
    //
    $batch['size'] = (float)$batch['size'];
    $batch['materials_cost_per_unit'] = numfmt_format_currency($intl_currency_fmt, $batch['materials_cost_per_unit'], $intl_currency);
    $batch['time_cost_per_unit'] = numfmt_format_currency($intl_currency_fmt, $batch['time_cost_per_unit'], $intl_currency);
    $batch['total_cost_per_unit'] = numfmt_format_currency($intl_currency_fmt, $batch['total_cost_per_unit'], $intl_currency);

    //
    // Setup the labels if requested
    //
    if( isset($args['labels']) && $args['labels'] == 'yes' ) {
        $strsql = "SELECT name "
            . "FROM ciniki_herbalist_recipes "
            . "WHERE id = '" . ciniki_core_dbQuote($ciniki, $batch['recipe_id']) . "' "
            . "AND business_id = '" . ciniki_core_dbQuote($ciniki, $args['business_id']) . "' "
            . "";
        $rc = ciniki_core_dbHashQuery($ciniki, $strsql, 'ciniki.herbalist', 'recipe');
        if( $rc['stat'] != 'ok' ) {
            return $rc;
        }
        $recipe = $rc['recipe'];

        $batch['label'] = array(
            'title'=>$recipe['name'],
            'ingredients'=>'', 
            'batchdate'=>$batch['production_date'],
            );
        foreach($ingredients as $ingredient) {
            $batch['label']['ingredients'] .= ($batch['label']['ingredients'] != '' ? ', ' : '') . $ingredient['name'];
        }
    }

    return array('stat'=>'ok', 'batch'=>$batch);
}
?>
