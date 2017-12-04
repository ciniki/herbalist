<?php
//
// Description
// -----------
// This method will return the list of Recipes for a tenant.
//
// Arguments
// ---------
// api_key:
// auth_token:
// tnid:        The ID of the tenant to get Recipe for.
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
        'tnid'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Tenant'),
        'recipe_type'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Type'),
        ));
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    $args = $rc['args'];

    //
    // Check access to tnid as owner, or sys admin.
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'herbalist', 'private', 'checkAccess');
    $rc = ciniki_herbalist_checkAccess($ciniki, $args['tnid'], 'ciniki.herbalist.recipeList');
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
        . "ciniki_herbalist_recipes.total_cost_per_unit, "
        . "ciniki_herbalist_recipes.total_time_per_unit "
        . "FROM ciniki_herbalist_recipes "
        . "WHERE ciniki_herbalist_recipes.tnid = '" . ciniki_core_dbQuote($ciniki, $args['tnid']) . "' "
        . "";
    if( isset($args['recipe_type']) && $args['recipe_type'] > 0 ) {
        $strsql .= "AND recipe_type = '" . ciniki_core_dbQuote($ciniki, $args['recipe_type']) . "' ";
    }
    $strsql .= "ORDER BY name "
        . "";
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbHashQueryArrayTree');
    $rc = ciniki_core_dbHashQueryArrayTree($ciniki, $strsql, 'ciniki.herbalist', array(
        array('container'=>'recipes', 'fname'=>'id', 
            'fields'=>array('id', 'name', 'units', 'yield', 'materials_cost_per_unit', 'time_cost_per_unit', 'total_cost_per_unit', 'total_time_per_unit')),
        ));
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    $recipe_ids = array();
    if( isset($rc['recipes']) ) {
        $recipes = $rc['recipes'];
        foreach($recipes as $recipe) {
            $recipe_ids[] = $recipe['id'];
        }
    } else {
        $recipes = array();
    }

    return array('stat'=>'ok', 'recipes'=>$recipes, 'nextprevlist'=>$recipe_ids);
}
?>
