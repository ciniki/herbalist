<?php
//
// Description
// -----------
// This method will return the list of Ingredients for a tenant.
//
// Arguments
// ---------
// api_key:
// auth_token:
// tnid:        The ID of the tenant to get Ingredient for.
//
// Returns
// -------
//
function ciniki_herbalist_ingredientSearch($ciniki) {
    //
    // Find all the required and optional arguments
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'prepareArgs');
    $rc = ciniki_core_prepareArgs($ciniki, 'no', array(
        'tnid'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Tenant'),
        'search_str'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Search String'),
        'limit'=>array('required'=>'no', 'blank'=>'yes', 'default'=>'15', 'name'=>'Limit'),
        ));
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    $args = $rc['args'];

    //
    // Check access to tnid as owner, or sys admin.
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'herbalist', 'private', 'checkAccess');
    $rc = ciniki_herbalist_checkAccess($ciniki, $args['tnid'], 'ciniki.herbalist.ingredientSearch');
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
        . "ciniki_herbalist_ingredients.total_time_per_unit, "
        . "ciniki_herbalist_ingredients.warnings "
        . "FROM ciniki_herbalist_ingredients "
        . "WHERE ciniki_herbalist_ingredients.tnid = '" . ciniki_core_dbQuote($ciniki, $args['tnid']) . "' "
        . "";
    if( isset($args['sorttype']) && $args['sorttype'] > 0 ) {
        $strsql .= "AND ciniki_herbalist_ingredients.sorttype = '" . ciniki_core_dbQuote($ciniki, $args['sorttype']) . "' ";
    }
    $args['search_str'] = preg_replace("/\s+/", '%', $args['search_str']);
    $strsql .= "AND (name LIKE '" . ciniki_core_dbQuote($ciniki, $args['search_str']) . "%' "
        . "OR name LIKE '% " . ciniki_core_dbQuote($ciniki, $args['search_str']) . "%' "
        . "OR subname LIKE '" . ciniki_core_dbQuote($ciniki, $args['search_str']) . "%' "
        . "OR subname LIKE '% " . ciniki_core_dbQuote($ciniki, $args['search_str']) . "%' "
        . ") ";
    $strsql .= "ORDER BY name "
        . "";
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbHashQueryArrayTree');
    $rc = ciniki_core_dbHashQueryArrayTree($ciniki, $strsql, 'ciniki.herbalist', array(
        array('container'=>'ingredients', 'fname'=>'id', 
            'fields'=>array('id', 'name', 'subname', 'sorttype', 'plant_id', 'recipe_id', 'units', 'units_display', 'costing_quantity', 'costing_price', 
                'materials_cost_per_unit', 'time_cost_per_unit', 'total_cost_per_unit', 'total_time_per_unit', 'warnings'),
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
        $rc = ciniki_herbalist_labels($ciniki, $args['tnid'], 'names');
        if( $rc['stat'] != 'ok' ) { 
            return $rc;
        }
        if( isset($rc['labels']) ) {
            $rsp['labels'] = $rc['labels'];
        } 
    } elseif( isset($args['worksheet']) && $args['worksheet'] == 'yes' ) {
        ciniki_core_loadMethod($ciniki, 'ciniki', 'herbalist', 'templates', 'ingredientWorksheet');
        $rc = ciniki_herbalist_templates_ingredientWorksheet($ciniki, $args['tnid'], array(
            'ingredients'=>$ingredients,
            ));
        if( $rc['stat'] != 'ok' ) { 
            return $rc;
        }
        if( isset($rc['pdf']) ) {
            $rc['pdf']->Output($rc['filename'], 'D');
            return array('stat'=>'exit');
        } 
    }

    return $rsp;
}
?>
