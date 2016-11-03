<?php
//
// Description
// ===========
// This method will return all the information about an product.
//
// Arguments
// ---------
// api_key:
// auth_token:
// business_id:         The ID of the business the product is attached to.
// product_id:          The ID of the product to get the details for.
//
// Returns
// -------
//
function ciniki_herbalist_productGet($ciniki) {
    //
    // Find all the required and optional arguments
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'prepareArgs');
    $rc = ciniki_core_prepareArgs($ciniki, 'no', array(
        'business_id'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Business'),
        'product_id'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Product'),
        'images'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Images'),
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
    $rc = ciniki_herbalist_checkAccess($ciniki, $args['business_id'], 'ciniki.herbalist.productGet');
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
    // Return default for new Product
    //
    if( $args['product_id'] == 0 ) {
        $product = array('id'=>0,
            'name'=>'',
            'permalink'=>'',
            'flags'=>0x01,
            'primary_image_id'=>'0',
            'synopsis'=>'',
            'description'=>'',
            'ingredients'=>'',
            'versions'=>array(),
            'notes'=>array(),
        );
    }

    //
    // Get the details for an existing Product
    //
    else {
        $strsql = "SELECT ciniki_herbalist_products.id, "
            . "ciniki_herbalist_products.name, "
            . "ciniki_herbalist_products.permalink, "
            . "ciniki_herbalist_products.flags, "
            . "ciniki_herbalist_products.category, "
            . "ciniki_herbalist_products.primary_image_id, "
            . "ciniki_herbalist_products.synopsis, "
            . "ciniki_herbalist_products.description, "
            . "ciniki_herbalist_products.ingredients "
            . "FROM ciniki_herbalist_products "
            . "WHERE ciniki_herbalist_products.business_id = '" . ciniki_core_dbQuote($ciniki, $args['business_id']) . "' "
            . "AND ciniki_herbalist_products.id = '" . ciniki_core_dbQuote($ciniki, $args['product_id']) . "' "
            . "";
        ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbHashQuery');
        $rc = ciniki_core_dbHashQuery($ciniki, $strsql, 'ciniki.herbalist', 'product');
        if( $rc['stat'] != 'ok' ) {
            return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.herbalist.30', 'msg'=>'Product not found', 'err'=>$rc['err']));
        }
        if( !isset($rc['product']) ) {
            return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.herbalist.31', 'msg'=>'Unable to find Product'));
        }
        $product = $rc['product'];

        //
        // Get the versions of the product
        //
        $strsql = "SELECT ciniki_herbalist_product_versions.id, "
            . "ciniki_herbalist_product_versions.product_id, "
            . "ciniki_herbalist_product_versions.name, "
            . "ciniki_herbalist_product_versions.permalink, "
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
            . "AND ciniki_herbalist_product_versions.product_id = '" . ciniki_core_dbQuote($ciniki, $args['product_id']) . "' "
            . "ORDER BY sequence, name "
            . "";
        ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbHashQueryArrayTree');
        $rc = ciniki_core_dbHashQueryArrayTree($ciniki, $strsql, 'ciniki.herbalist', array(
            array('container'=>'versions', 'fname'=>'id', 
                'fields'=>array('id', 'product_id', 'name', 'permalink', 'recipe_id', 'recipe_quantity', 'container_id', 
                    'materials_cost_per_container', 'time_cost_per_container', 'total_cost_per_container', 'total_time_per_container', 'inventory', 'wholesale_price', 'retail_price')),
            ));
        if( $rc['stat'] != 'ok' ) {
            return $rc;
        }
        if( isset($rc['versions']) ) {
            $product['versions'] = $rc['versions'];
            foreach($product['versions'] as $vid => $version) {
                $product['versions'][$vid]['materials_cost_per_container_display'] = numfmt_format_currency($intl_currency_fmt, $version['materials_cost_per_container'], $intl_currency);
                $product['versions'][$vid]['time_cost_per_container_display'] = numfmt_format_currency($intl_currency_fmt, $version['time_cost_per_container'], $intl_currency);
                $product['versions'][$vid]['total_cost_per_container_display'] = numfmt_format_currency($intl_currency_fmt, $version['total_cost_per_container'], $intl_currency);
                $product['versions'][$vid]['wholesale_price_display'] = numfmt_format_currency($intl_currency_fmt, $version['wholesale_price'], $intl_currency);
                $product['versions'][$vid]['retail_price_display'] = numfmt_format_currency($intl_currency_fmt, $version['retail_price'], $intl_currency);
            }
        } else {
            $product['versions'] = array();
        }

        //
        // Get the categories
        //
        $strsql = "SELECT tag_type, tag_name AS lists "
            . "FROM ciniki_herbalist_tags "
            . "WHERE ref_id = '" . ciniki_core_dbQuote($ciniki, $args['product_id']) . "' "
            . "AND business_id = '" . ciniki_core_dbQuote($ciniki, $args['business_id']) . "' "
            . "ORDER BY tag_type, tag_name "
            . "";
        ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbHashQueryTree');
        $rc = ciniki_core_dbHashQueryTree($ciniki, $strsql, 'ciniki.herbalist', array(
            array('container'=>'tags', 'fname'=>'tag_type', 'name'=>'tags',
                'fields'=>array('tag_type', 'lists'), 'dlists'=>array('lists'=>'::')),
            ));
        if( $rc['stat'] != 'ok' ) {
            return $rc;
        }
        if( isset($rc['tags']) ) {
            foreach($rc['tags'] as $tags) {
                if( $tags['tags']['tag_type'] == 10 ) {
                    $product['categories'] = $tags['tags']['lists'];
                }
            }
        }

        //
        // Get the images
        //
        if( isset($args['images']) && $args['images'] == 'yes' ) {
            $strsql = "SELECT id, "
                . "name, "
                . "flags, "
                . "image_id, "
                . "description "
                . "FROM ciniki_herbalist_product_images "
                . "WHERE product_id = '" . ciniki_core_dbQuote($ciniki, $args['product_id']) . "' "
                . "AND business_id = '" . ciniki_core_dbQuote($ciniki, $args['business_id']) . "' "
                . "";
            $rc = ciniki_core_dbHashQueryArrayTree($ciniki, $strsql, 'ciniki.herbalist', array(
                array('container'=>'images', 'fname'=>'id', 'fields'=>array('id', 'name', 'flags', 'image_id', 'description')),
            ));
            if( $rc['stat'] != 'ok' ) {
                return $rc;
            }
            if( isset($rc['images']) ) {
                ciniki_core_loadMethod($ciniki, 'ciniki', 'images', 'private', 'loadCacheThumbnail');
                $product['images'] = $rc['images'];
                foreach($product['images'] as $img_id => $img) {
                    if( isset($img['image_id']) && $img['image_id'] > 0 ) {
                        $rc = ciniki_images_loadCacheThumbnail($ciniki, $args['business_id'], $img['image_id'], 75);
                        if( $rc['stat'] != 'ok' ) {
                            return $rc;
                        }
                        $product['images'][$img_id]['image_data'] = 'data:image/jpg;base64,' . base64_encode($rc['image']);
                    }
                }
            } else {
                $product['images'] = array();
            }
        }

        //
        // Get any notes for this product
        //
        ciniki_core_loadMethod($ciniki, 'ciniki', 'herbalist', 'private', 'objectNotes');
        $rc = ciniki_herbalist_objectNotes($ciniki, $args['business_id'], 'ciniki.herbalist.product', $args['product_id']);
        if( $rc['stat'] != 'ok' ) {
            return $rc;
        }
        if( isset($rc['notes']) ) {
            $product['notes'] = $rc['notes'];
        } else {
            $product['notes'] = array();
        }
    }

    //
    // Get the available tags
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbQueryList');
    $strsql = "SELECT DISTINCT tag_name FROM ciniki_herbalist_tags WHERE tag_type = 10 AND business_id = '" . ciniki_core_dbQuote($ciniki, $args['business_id']) . "' ";
    $rc = ciniki_core_dbQueryList($ciniki, $strsql, 'ciniki.herbalist', 'categories', 'tag_name');
    if( $rc['stat'] != 'ok' ) {
        return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.herbalist.32', 'msg'=>'Unable to get list of categories', 'err'=>$rc['err']));
    }
    if( isset($rc['categories']) ) {
        $categories = $rc['categories'];
    } else {
        $categories = array();
    }

    //
    // Get the list of recipes
    //
    $strsql = "SELECT ciniki_herbalist_recipes.id, ciniki_herbalist_recipes.name "
        . "FROM ciniki_herbalist_recipes "
        . "WHERE ciniki_herbalist_recipes.business_id = '" . ciniki_core_dbQuote($ciniki, $args['business_id']) . "' "
        . "ORDER BY name "
        . "";
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbQueryList2');
    $rc = ciniki_core_dbQueryList2($ciniki, $strsql, 'ciniki.herbalist', 'recipes');
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    if( isset($rc['recipes']) ) {
        $recipes = $rc['recipes'];
    } else {
        $recipes = array();
    }
    
    //
    // Get the list of containers
    //
    $strsql = "SELECT ciniki_herbalist_containers.id, ciniki_herbalist_containers.name "
        . "FROM ciniki_herbalist_containers "
        . "WHERE ciniki_herbalist_containers.business_id = '" . ciniki_core_dbQuote($ciniki, $args['business_id']) . "' "
        . "ORDER BY CAST(name AS UNSIGNED), name "
        . "";
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbQueryList2');
    $rc = ciniki_core_dbHashQuery($ciniki, $strsql, 'ciniki.herbalist', 'containers');
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    if( isset($rc['rows']) ) {
        $containers = $rc['rows'];
    } else {
        $containers = array();
    }

    return array('stat'=>'ok', 'product'=>$product, 'recipes'=>$recipes, 'containers'=>$containers, 'categories'=>$categories);
}
?>
