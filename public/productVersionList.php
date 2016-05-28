<?php
//
// Description
// -----------
// This method will return the list of Products for a business.
//
// Arguments
// ---------
// api_key:
// auth_token:
// business_id:        The ID of the business to get Product for.
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
        'category'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Category'),
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
    // Get the list of categories
    //
    $strsql = "SELECT DISTINCT category "
        . "FROM ciniki_herbalist_products "
        . "WHERE ciniki_herbalist_products.business_id = '" . ciniki_core_dbQuote($ciniki, $args['business_id']) . "' "
        . "AND category <> '' "
        . "ORDER BY category "
        . "";
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbHashQueryArrayTree');
    $rc = ciniki_core_dbHashQueryArrayTree($ciniki, $strsql, 'ciniki.herbalist', array(
        array('container'=>'categories', 'fname'=>'category', 'fields'=>array('name'=>'category')),
        ));
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    if( isset($rc['categories']) ) {
        $categories = $rc['categories'];
        array_unshift($categories, array('name'=>'All'));
    } else {
        $categories = array();
    }

    //
    // Get the list of products
    //
    $strsql = "SELECT ciniki_herbalist_products.id, "
        . "ciniki_herbalist_products.name, "
        . "ciniki_herbalist_products.permalink, "
        . "ciniki_herbalist_products.flags, "
        . "ciniki_herbalist_products.category, "
        . "ciniki_herbalist_product_versions.name AS version_name, "
        . "ciniki_herbalist_product_versions.inventory, "
        . "ciniki_herbalist_product_versions.wholesale_price, "
        . "ciniki_herbalist_product_versions.retail_price "
        . "FROM ciniki_herbalist_products, ciniki_herbalist_product_versions "
        . "WHERE ciniki_herbalist_products.business_id = '" . ciniki_core_dbQuote($ciniki, $args['business_id']) . "' ";
    if( isset($args['category']) && $args['category'] != '' && $args['category'] != 'All' ) {
        $strsql .= "AND ciniki_herbalist_products.category = '" . ciniki_core_dbQuote($ciniki, $args['category']) . "' ";
    }
    $strsql .= "AND ciniki_herbalist_products.id = ciniki_herbalist_product_versions.product_id "
        . "AND ciniki_herbalist_product_versions.business_id = '" . ciniki_core_dbQuote($ciniki, $args['business_id']) . "' "
        . "ORDER BY category, name ";
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbHashQueryArrayTree');
    $rc = ciniki_core_dbHashQueryArrayTree($ciniki, $strsql, 'ciniki.herbalist', array(
        array('container'=>'productversions', 'fname'=>'id', 
            'fields'=>array('id', 'category', 'name', 'permalink', 'flags', 'version_name', 'inventory', 'wholesale_price', 'retail_price')),
        ));
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    if( isset($rc['productversions']) ) {
        $productversions = $rc['productversions'];
    } else {
        $productversions = array();
    }

    return array('stat'=>'ok', 'categories'=>$categories, 'productversions'=>$productversions);
}
?>
