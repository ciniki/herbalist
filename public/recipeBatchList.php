<?php
//
// Description
// -----------
// This method will return the list of Recipe Batchs for a business.
//
// Arguments
// ---------
// api_key:
// auth_token:
// business_id:        The ID of the business to get Recipe Batch for.
//
// Returns
// -------
//
function ciniki_herbalist_recipeBatchList($ciniki) {
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
    $rc = ciniki_herbalist_checkAccess($ciniki, $args['business_id'], 'ciniki.herbalist.recipeBatchList');
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }

    //
    // Get the list of recipebatches
    //
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
        . "";
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbHashQueryArrayTree');
    $rc = ciniki_core_dbHashQueryArrayTree($ciniki, $strsql, 'ciniki.herbalist', array(
        array('container'=>'recipebatches', 'fname'=>'id', 
            'fields'=>array('id', 'recipe_id', 'production_date', 'yield', 'production_time', 'materials_cost_per_unit', 'time_cost_per_unit', 'total_cost_per_unit', 'notes')),
        ));
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    if( isset($rc['recipebatches']) ) {
        $recipebatches = $rc['recipebatches'];
    } else {
        $recipebatches = array();
    }

    return array('stat'=>'ok', 'recipebatches'=>$recipebatches);
}
?>