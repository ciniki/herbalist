<?php
//
// Description
// ===========
// This method will return all the information about an product image.
//
// Arguments
// ---------
// api_key:
// auth_token:
// business_id:         The ID of the business the product image is attached to.
// productimage_id:          The ID of the product image to get the details for.
//
// Returns
// -------
//
function ciniki_herbalist_productImageGet($ciniki) {
    //
    // Find all the required and optional arguments
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'prepareArgs');
    $rc = ciniki_core_prepareArgs($ciniki, 'no', array(
        'business_id'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Business'),
        'productimage_id'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Product Image'),
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
    $rc = ciniki_herbalist_checkAccess($ciniki, $args['business_id'], 'ciniki.herbalist.productImageGet');
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

    ciniki_core_loadMethod($ciniki, 'ciniki', 'users', 'private', 'datetimeFormat');
    $datetime_format = ciniki_users_datetimeFormat($ciniki, 'php');

    //
    // Return default for new Product Image
    //
    if( $args['productimage_id'] == 0 ) {
        $productimage = array('id'=>0,
            'product_id'=>'',
            'name'=>'',
            'permalink'=>'',
            'flags'=>'1',
            'image_id'=>'',
            'description'=>'',
        );
    }

    //
    // Get the details for an existing Product Image
    //
    else {
        $strsql = "SELECT ciniki_herbalist_product_images.id, "
            . "ciniki_herbalist_product_images.product_id, "
            . "ciniki_herbalist_product_images.name, "
            . "ciniki_herbalist_product_images.permalink, "
            . "ciniki_herbalist_product_images.flags, "
            . "ciniki_herbalist_product_images.image_id, "
            . "ciniki_herbalist_product_images.description "
            . "FROM ciniki_herbalist_product_images "
            . "WHERE ciniki_herbalist_product_images.business_id = '" . ciniki_core_dbQuote($ciniki, $args['business_id']) . "' "
            . "AND ciniki_herbalist_product_images.id = '" . ciniki_core_dbQuote($ciniki, $args['productimage_id']) . "' "
            . "";
        ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbHashQuery');
        $rc = ciniki_core_dbHashQuery($ciniki, $strsql, 'ciniki.herbalist', 'productimage');
        if( $rc['stat'] != 'ok' ) {
            return array('stat'=>'fail', 'err'=>array('pkg'=>'ciniki', 'code'=>'3462', 'msg'=>'Product Image not found', 'err'=>$rc['err']));
        }
        if( !isset($rc['productimage']) ) {
            return array('stat'=>'fail', 'err'=>array('pkg'=>'ciniki', 'code'=>'3463', 'msg'=>'Unable to find Product Image'));
        }
        $productimage = $rc['productimage'];
    }

    return array('stat'=>'ok', 'productimage'=>$productimage);
}
?>
