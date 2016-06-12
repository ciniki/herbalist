<?php
//
// Description
// -----------
// This method will return the list of Ingredients for a business.
//
// Arguments
// ---------
// api_key:
// auth_token:
// business_id:        The ID of the business to get Ingredient for.
//
// Returns
// -------
//
function ciniki_herbalist_ingredientList($ciniki) {
    //
    // Find all the required and optional arguments
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'prepareArgs');
    $rc = ciniki_core_prepareArgs($ciniki, 'no', array(
        'business_id'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Business'),
        'sorttype'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Type'),
        'labels'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Labels'),
        ));
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    $args = $rc['args'];

    //
    // Check access to business_id as owner, or sys admin.
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'herbalist', 'private', 'checkAccess');
    $rc = ciniki_herbalist_checkAccess($ciniki, $args['business_id'], 'ciniki.herbalist.ingredientList');
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

    //
    // Load the maps
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'herbalist', 'private', 'maps');
    $rc = ciniki_herbalist_maps($ciniki);
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    $maps = $rc['maps'];

    //
    // Get the list of ingredients
    //
    $strsql = "SELECT ciniki_herbalist_ingredients.id, "
        . "ciniki_herbalist_ingredients.name, "
        . "ciniki_herbalist_ingredients.subname, "
        . "ciniki_herbalist_ingredients.sorttype, "
        . "ciniki_herbalist_ingredients.plant_id, "
        . "ciniki_herbalist_ingredients.recipe_id, "
        . "ciniki_herbalist_ingredients.units, "
        . "ciniki_herbalist_ingredients.units AS units_display, "
        . "ciniki_herbalist_ingredients.costing_quantity, "
        . "ciniki_herbalist_ingredients.costing_price, "
        . "ciniki_herbalist_ingredients.materials_cost_per_unit, "
        . "ciniki_herbalist_ingredients.time_cost_per_unit, "
        . "ciniki_herbalist_ingredients.total_cost_per_unit, "
        . "ciniki_herbalist_ingredients.warnings "
        . "FROM ciniki_herbalist_ingredients "
        . "WHERE ciniki_herbalist_ingredients.business_id = '" . ciniki_core_dbQuote($ciniki, $args['business_id']) . "' "
        . "";
    if( isset($args['sorttype']) && $args['sorttype'] > 0 ) {
        $strsql .= "AND ciniki_herbalist_ingredients.sorttype = '" . ciniki_core_dbQuote($ciniki, $args['sorttype']) . "' ";
    }
    $strsql .= "ORDER BY name "
        . "";
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbHashQueryArrayTree');
    $rc = ciniki_core_dbHashQueryArrayTree($ciniki, $strsql, 'ciniki.herbalist', array(
        array('container'=>'ingredients', 'fname'=>'id', 
            'fields'=>array('id', 'name', 'subname', 'sorttype', 'plant_id', 'recipe_id', 'units', 'units_display', 'costing_quantity', 'costing_price', 
                'materials_cost_per_unit', 'time_cost_per_unit', 'total_cost_per_unit', 'warnings'),
            'maps'=>array('units_display'=>$maps['ingredient']['units']),
            ),
        ));
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    if( isset($rc['ingredients']) ) {
        $ingredients = $rc['ingredients'];
        $ingredient_ids = array();
        foreach($ingredients as $iid => $ingredient) {
            $ingredients[$iid]['materials_cost_per_unit_display'] = numfmt_format_currency($intl_currency_fmt, $ingredient['materials_cost_per_unit'], $intl_currency) . '/' . $ingredient['units_display'];
            $ingredients[$iid]['time_cost_per_unit_display'] = numfmt_format_currency($intl_currency_fmt, $ingredient['time_cost_per_unit'], $intl_currency) . '/' . $ingredient['units_display'];
            $ingredients[$iid]['total_cost_per_unit_display'] = numfmt_format_currency($intl_currency_fmt, $ingredient['total_cost_per_unit'], $intl_currency) . '/' . $ingredient['units_display'];
            $ingredient_ids[] = $ingredient['id'];
        }
    } else {
        $ingredients = array();
        $ingredient_ids = array();
    }

    $rsp = array('stat'=>'ok', 'ingredients'=>$ingredients, 'nextprevlist'=>$ingredient_ids);

    //
    // Check if list of labels needs to be returned
    //
    if( isset($args['labels']) && $args['labels'] == 'yes' ) {
        ciniki_core_loadMethod($ciniki, 'ciniki', 'herbalist', 'private', 'labels');
        $rc = ciniki_herbalist_labels($ciniki, $args['business_id'], 'names');
        if( $rc['stat'] != 'ok' ) { 
            return $rc;
        }
        if( isset($rc['labels']) ) {
            $rsp['labels'] = $rc['labels'];
        }
    }

    return $rsp;
}
?>
