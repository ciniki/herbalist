<?php
//
// Description
// -----------
// This method will return the list of Recipes for a business.
//
// Arguments
// ---------
// api_key:
// auth_token:
// business_id:        The ID of the business to get Recipe for.
//
// Returns
// -------
//
function ciniki_herbalist_recipeList($ciniki) {
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
    $rc = ciniki_herbalist_checkAccess($ciniki, $args['business_id'], 'ciniki.herbalist.recipeList');
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }

    //
    // Get the list of recipes
    //
    $strsql = "SELECT ciniki_herbalist_recipes.id, "
        . "ciniki_herbalist_recipes.name, "
        . "ciniki_herbalist_recipes.units, "
        . "ciniki_herbalist_recipes.yield, "
        . "ciniki_herbalist_recipes.materials_cost_per_unit, "
        . "ciniki_herbalist_recipes.time_cost_per_unit, "
        . "ciniki_herbalist_recipes.total_cost_per_unit "
        . "FROM ciniki_herbalist_recipes "
        . "WHERE ciniki_herbalist_recipes.business_id = '" . ciniki_core_dbQuote($ciniki, $args['business_id']) . "' "
        . "";
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbHashQueryArrayTree');
    $rc = ciniki_core_dbHashQueryArrayTree($ciniki, $strsql, 'ciniki.herbalist', array(
        array('container'=>'recipes', 'fname'=>'id', 
            'fields'=>array('id', 'name', 'units', 'yield', 'materials_cost_per_unit', 'time_cost_per_unit', 'total_cost_per_unit')),
        ));
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    if( isset($rc['recipes']) ) {
        $recipes = $rc['recipes'];
    } else {
        $recipes = array();
    }

    return array('stat'=>'ok', 'recipes'=>$recipes);
}
?>
