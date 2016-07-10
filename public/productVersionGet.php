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
// productversion_id:          The ID of the product version to get the details for.
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
        'productversion_id'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Product Version'),
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
    if( $args['productversion_id'] == 0 ) {
        ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'sequencesNext');
        $rc = ciniki_core_sequencesNext($ciniki, $args['business_id'], 'ciniki.herbalist.productversion', 'product_id', $args['product_id']);
        if( $rc['stat'] != 'ok' ) {
            return $rc;
        }
        $productversion = array('id'=>0,
            'product_id'=>'',
            'name'=>'',
            'permalink'=>'',
            'flags'=>1,
            'sequence'=>$rc['sequence'],
            'recipe_id'=>'0',
            'recipe_quantity'=>'0',
            'container_id'=>'0',
            'materials_cost_per_container'=>'',
            'time_cost_per_container'=>'',
            'total_cost_per_container'=>'',
            'total_time_per_container'=>'',
            'inventory'=>'',
            'wholesale_price'=>'',
            'retail_price'=>'',
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
            . "ciniki_herbalist_product_versions.flags, "
            . "ciniki_herbalist_product_versions.sequence, "
            . "ciniki_herbalist_product_versions.recipe_id, "
            . "ciniki_herbalist_product_versions.recipe_quantity, "
            . "ciniki_herbalist_product_versions.container_id, "
            . "ciniki_herbalist_product_versions.materials_cost_per_container, "
            . "ciniki_herbalist_product_versions.time_cost_per_container, "
            . "ciniki_herbalist_product_versions.total_cost_per_container, "
            . "ciniki_herbalist_product_versions.total_time_per_container, "
            . "ciniki_herbalist_product_versions.inventory, "
            . "ciniki_herbalist_product_versions.wholesale_price, "
            . "ciniki_herbalist_product_versions.retail_price "
            . "FROM ciniki_herbalist_product_versions "
            . "WHERE ciniki_herbalist_product_versions.business_id = '" . ciniki_core_dbQuote($ciniki, $args['business_id']) . "' "
            . "AND ciniki_herbalist_product_versions.id = '" . ciniki_core_dbQuote($ciniki, $args['productversion_id']) . "' "
            . "";
        ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbHashQuery');
        $rc = ciniki_core_dbHashQuery($ciniki, $strsql, 'ciniki.herbalist', 'productversion');
        if( $rc['stat'] != 'ok' ) {
            return array('stat'=>'fail', 'err'=>array('pkg'=>'ciniki', 'code'=>'3477', 'msg'=>'Product Version not found', 'err'=>$rc['err']));
        }
        if( !isset($rc['productversion']) ) {
            return array('stat'=>'fail', 'err'=>array('pkg'=>'ciniki', 'code'=>'3478', 'msg'=>'Unable to find Product Version'));
        }
        $productversion = $rc['productversion'];
        $productversion['recipe_quantity'] = (float)$productversion['recipe_quantity'];
        $productversion['materials_cost_per_container'] = numfmt_format_currency($intl_currency_fmt, $productversion['materials_cost_per_container'], $intl_currency);
        $productversion['time_cost_per_container'] = numfmt_format_currency($intl_currency_fmt, $productversion['time_cost_per_container'], $intl_currency);
        $productversion['total_cost_per_container'] = numfmt_format_currency($intl_currency_fmt, $productversion['total_cost_per_container'], $intl_currency);
        $productversion['wholesale_price'] = numfmt_format_currency($intl_currency_fmt, $productversion['wholesale_price'], $intl_currency);
        $productversion['retail_price'] = numfmt_format_currency($intl_currency_fmt, $productversion['retail_price'], $intl_currency);
    }

    //
    // Get the list of recipes
    //
    $strsql = "SELECT ciniki_herbalist_recipes.id, "
        . "ciniki_herbalist_recipes.name, "
        . "ciniki_herbalist_recipes.materials_cost_per_unit, "
        . "ciniki_herbalist_recipes.time_cost_per_unit, "
        . "ciniki_herbalist_recipes.total_cost_per_unit, "
        . "ciniki_herbalist_recipes.total_time_per_unit "
        . "FROM ciniki_herbalist_recipes "
        . "WHERE ciniki_herbalist_recipes.business_id = '" . ciniki_core_dbQuote($ciniki, $args['business_id']) . "' "
        . "ORDER BY name "
        . "";
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbHashQueryArrayTree');
    $rc = ciniki_core_dbHashQueryArrayTree($ciniki, $strsql, 'ciniki.herbalist', array(
        array('container'=>'recipes', 'fname'=>'id', 
            'fields'=>array('id', 'name', 'materials_cost_per_unit', 'time_cost_per_unit', 'total_cost_per_unit', 'total_time_per_unit')),
        ));
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    if( isset($rc['recipes']) ) {
        $recipes = $rc['recipes'];
    } else {
        $recipes = array();
    }
    array_unshift($recipes, array('id'=>'0', 'name'=>'None', 'materials_cost_per_unit'=>'0', 'time_cost_per_unit'=>'0', 'total_cost_per_unit'=>'0', 'total_time_per_unit'=>'0'));

    //
    // Get the list of containers
    //
    $strsql = "SELECT ciniki_herbalist_containers.id, "
        . "ciniki_herbalist_containers.name, "
        . "ciniki_herbalist_containers.cost_per_unit "
        . "FROM ciniki_herbalist_containers "
        . "WHERE ciniki_herbalist_containers.business_id = '" . ciniki_core_dbQuote($ciniki, $args['business_id']) . "' "
        . "ORDER BY CAST(name AS UNSIGNED), name "
        . "";
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbHashQueryArrayTree');
    $rc = ciniki_core_dbHashQueryArrayTree($ciniki, $strsql, 'ciniki.herbalist', array(
        array('container'=>'containers', 'fname'=>'id', 
            'fields'=>array('id', 'name', 'cost_per_unit')),
        ));
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    if( isset($rc['containers']) ) {
        $containers = $rc['containers'];
        usort($containers, function($a, $b) { return strnatcmp($a['name'], $b['name']); });
    } else {
        $containers = array();
    }
    array_unshift($containers, array('id'=>'0', 'name'=>'None', 'cost_per_unit'=>'0'));

    return array('stat'=>'ok', 'productversion'=>$productversion, 'recipes'=>$recipes, 'containers'=>$containers);
}
?>
