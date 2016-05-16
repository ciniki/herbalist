<?php
//
// Description
// ===========
// This method will return all the information about an product version.
//
// Arguments
// ---------
// api_key:
// auth_token:
// business_id:         The ID of the business the product version is attached to.
// version_id:          The ID of the product version to get the details for.
//
// Returns
// -------
//
function ciniki_herbalist_productVersionGet($ciniki) {
    //
    // Find all the required and optional arguments
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'prepareArgs');
    $rc = ciniki_core_prepareArgs($ciniki, 'no', array(
        'business_id'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Business'),
        'version_id'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Product Version'),
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
    $rc = ciniki_herbalist_checkAccess($ciniki, $args['business_id'], 'ciniki.herbalist.productVersionGet');
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
    // Return default for new Product Version
    //
    if( $args['version_id'] == 0 ) {
        $productversion = array('id'=>0,
            'product_id'=>'',
            'name'=>'',
            'permalink'=>'',
            'recipe_id'=>'0',
            'recipe_quantity'=>'0',
            'container_id'=>'0',
            'cost_per_container'=>'0',
            'inventory'=>'0',
            'wholesale_price'=>'0',
            'retail_price'=>'0',
        );
    }

    //
    // Get the details for an existing Product Version
    //
    else {
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
            . "AND ciniki_herbalist_product_versions.id = '" . ciniki_core_dbQuote($ciniki, $args['version_id']) . "' "
            . "";
        ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbHashQuery');
        $rc = ciniki_core_dbHashQuery($ciniki, $strsql, 'ciniki.herbalist', 'productversion');
        if( $rc['stat'] != 'ok' ) {
            return array('stat'=>'fail', 'err'=>array('pkg'=>'ciniki', 'code'=>'3457', 'msg'=>'Product Version not found', 'err'=>$rc['err']));
        }
        if( !isset($rc['productversion']) ) {
            return array('stat'=>'fail', 'err'=>array('pkg'=>'ciniki', 'code'=>'3458', 'msg'=>'Unable to find Product Version'));
        }
        $productversion = $rc['productversion'];
    }

    return array('stat'=>'ok', 'productversion'=>$productversion);
}
?>
