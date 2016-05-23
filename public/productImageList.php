<?php
//
// Description
// -----------
// This method will return the list of Product Images for a business.
//
// Arguments
// ---------
// api_key:
// auth_token:
// business_id:        The ID of the business to get Product Image for.
//
// Returns
// -------
//
function ciniki_herbalist_productImageList($ciniki) {
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
    $rc = ciniki_herbalist_checkAccess($ciniki, $args['business_id'], 'ciniki.herbalist.productImageList');
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }

    //
    // Get the list of productimages
    //
    $strsql = "SELECT ciniki_herbalist_product_images.id, "
        . "ciniki_herbalist_product_images.product_id, "
        . "ciniki_herbalist_product_images.name, "
        . "ciniki_herbalist_product_images.permalink, "
        . "ciniki_herbalist_product_images.flags, "
        . "ciniki_herbalist_product_images.image_id, "
        . "ciniki_herbalist_product_images.description "
        . "FROM ciniki_herbalist_product_images "
        . "WHERE ciniki_herbalist_product_images.business_id = '" . ciniki_core_dbQuote($ciniki, $args['business_id']) . "' "
        . "";
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbHashQueryArrayTree');
    $rc = ciniki_core_dbHashQueryArrayTree($ciniki, $strsql, 'ciniki.herbalist', array(
        array('container'=>'productimages', 'fname'=>'id', 
            'fields'=>array('id', 'product_id', 'name', 'permalink', 'flags', 'image_id', 'description')),
        ));
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    if( isset($rc['productimages']) ) {
        $productimages = $rc['productimages'];
    } else {
        $productimages = array();
    }

    return array('stat'=>'ok', 'productimages'=>$productimages);
}
?>
