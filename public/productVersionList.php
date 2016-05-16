<?php
//
// Description
// -----------
// This method will return the list of Product Versions for a business.
//
// Arguments
// ---------
// api_key:
// auth_token:
// business_id:        The ID of the business to get Product Version for.
//
// Returns
// -------
//
function ciniki_herbalist_productVersionList($ciniki) {
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
    $rc = ciniki_herbalist_checkAccess($ciniki, $args['business_id'], 'ciniki.herbalist.productVersionList');
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }

    //
    // Get the list of productversions
    //
    $strsql = "SELECT ciniki_herbalist_product_versions.id, "
        . "ciniki_herbalist_product_versions.product_id, "
        . "ciniki_herbalist_product_versions.name, "
        . "ciniki_herbalist_product_versions.permalink, "
        . "ciniki_herbalist_product_versions.recipe_id, "
        . "ciniki_herbalist_product_versions.recipe_quantity, "
        . "ciniki_herbalist_product_versions.container_id, "
        . "ciniki_herbalist_product_versions.cost_per_container, "
        . "ciniki_herbalist_product_versions.inventory, "
        . "ciniki_herbalist_product_versions.wholesale_price, "
        . "ciniki_herbalist_product_versions.retail_price "
        . "FROM ciniki_herbalist_product_versions "
        . "WHERE ciniki_herbalist_product_versions.business_id = '" . ciniki_core_dbQuote($ciniki, $args['business_id']) . "' "
        . "";
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbHashQueryArrayTree');
    $rc = ciniki_core_dbHashQueryArrayTree($ciniki, $strsql, 'ciniki.herbalist', array(
        array('container'=>'productversions', 'fname'=>'id', 
            'fields'=>array('id', 'product_id', 'name', 'permalink', 'recipe_id', 'recipe_quantity', 'container_id', 'cost_per_container', 'inventory', 'wholesale_price', 'retail_price')),
        ));
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    if( isset($rc['productversions']) ) {
        $productversions = $rc['productversions'];
    } else {
        $productversions = array();
    }

    return array('stat'=>'ok', 'productversions'=>$productversions);
}
?>
