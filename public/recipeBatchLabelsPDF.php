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
function ciniki_herbalist_recipeBatchLabelsPDF($ciniki) {
    //
    // Find all the required and optional arguments
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'prepareArgs');
    $rc = ciniki_core_prepareArgs($ciniki, 'no', array(
        'business_id'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Business'),
        'batch_id'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Recipe Batch'),
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

    $strsql = "SELECT ciniki_herbalist_recipe_batches.id, "
        . "ciniki_herbalist_recipes.name, "
        . "ciniki_herbalist_recipe_batches.recipe_id, "
        . "ciniki_herbalist_recipe_batches.production_date, "
        . "ciniki_herbalist_recipe_batches.size, "
        . "ciniki_herbalist_recipe_batches.yield, "
        . "ciniki_herbalist_recipe_batches.production_time, "
        . "ciniki_herbalist_recipe_batches.materials_cost_per_unit, "
        . "ciniki_herbalist_recipe_batches.time_cost_per_unit, "
        . "ciniki_herbalist_recipe_batches.total_cost_per_unit, "
        . "ciniki_herbalist_recipe_batches.notes "
        . "FROM ciniki_herbalist_recipe_batches, ciniki_herbalist_recipes "
        . "WHERE ciniki_herbalist_recipe_batches.business_id = '" . ciniki_core_dbQuote($ciniki, $args['business_id']) . "' "
        . "AND ciniki_herbalist_recipe_batches.id = '" . ciniki_core_dbQuote($ciniki, $args['batch_id']) . "' "
        . "AND ciniki_herbalist_recipe_batches.recipe_id = ciniki_herbalist_recipes.id "
        . "AND ciniki_herbalist_recipes.business_id = '" . ciniki_core_dbQuote($ciniki, $args['business_id']) . "' "
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
    $batch['production_date'] = $dt->format('M j, Y');

    //
    // Get the list of recipe ingredients
    //
    $strsql = "SELECT ciniki_herbalist_recipe_ingredients.id, "
        . "ciniki_herbalist_recipe_ingredients.recipe_id, "
        . "ciniki_herbalist_recipe_ingredients.ingredient_id, "
        . "ciniki_herbalist_ingredients.name, "
        . "ciniki_herbalist_ingredients.sorttype, "
        . "ciniki_herbalist_ingredients.units, "
        . "ciniki_herbalist_recipe_ingredients.quantity "
        . "FROM ciniki_herbalist_recipe_ingredients "
        . "LEFT JOIN ciniki_herbalist_ingredients ON ("
            . "ciniki_herbalist_recipe_ingredients.ingredient_id = ciniki_herbalist_ingredients.id "
            . "AND ciniki_herbalist_ingredients.business_id = '" . ciniki_core_dbQuote($ciniki, $args['business_id']) . "' "
            . ") "
        . "WHERE ciniki_herbalist_recipe_ingredients.business_id = '" . ciniki_core_dbQuote($ciniki, $args['business_id']) . "' "
        . "AND ciniki_herbalist_recipe_ingredients.recipe_id = '" . ciniki_core_dbQuote($ciniki, $batch['recipe_id']) . "' "
        . "ORDER BY ciniki_herbalist_recipe_ingredients.quantity DESC, ciniki_herbalist_ingredients.name "
        . "";
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbHashQueryIDTree');
    $rc = ciniki_core_dbHashQueryIDTree($ciniki, $strsql, 'ciniki.herbalist', array(
        array('container'=>'ingredients', 'fname'=>'id', 
            'fields'=>array('id', 'ingredient_id', 'name', 'units', 'quantity')),
        ));
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }

    $ingredients = '';
    if( isset($rc['ingredients']) ) {
        foreach($rc['ingredients'] as $ingredient) {
            $ingredients .= ($ingredients != '' ? ', ' : '') . $ingredient['name'];
        }
    }

    $pdf_args = array(
        'business_id'=>$args['business_id'],
        'title'=>$batch['name'],
        'label'=>array('label'=>'avery8927',
            'title'=>$batch['name'],
            'ingredients'=>$ingredients,
            'batchdate'=>$batch['production_date'],
            ),
        );
    ciniki_core_loadMethod($ciniki, 'ciniki', 'herbalist', 'templates', 'labelsPDF');
    $rc = ciniki_herbalist_templates_labelsPDF($ciniki, $args['business_id'], $pdf_args);
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }

    if( isset($rc['pdf']) ) {
        $filename = preg_replace('/[^a-zA-Z0-9_]/', '', preg_replace('/ /', '_', $batch['name'] . '_' . $pdf_args['label']));
        $rc['pdf']->Output($filename . '.pdf', 'D');
    }

    return array('stat'=>'exit');
}
?>
