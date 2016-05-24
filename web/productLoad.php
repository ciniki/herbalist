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
function ciniki_herbalist_web_productLoad($ciniki, $business_id, $args) {
    
    $strsql = "SELECT ciniki_herbalist_products.id, "
        . "ciniki_herbalist_products.name, "
        . "ciniki_herbalist_products.permalink, "
        . "ciniki_herbalist_products.flags, "
        . "ciniki_herbalist_products.notes, "
        . "ciniki_herbalist_products.primary_image_id, "
        . "'' AS primary_image_caption, "
        . "ciniki_herbalist_products.synopsis, "
        . "ciniki_herbalist_products.description, "
        . "ciniki_herbalist_products.ingredients "
        . "FROM ciniki_herbalist_products "
        . "WHERE ciniki_herbalist_products.business_id = '" . ciniki_core_dbQuote($ciniki, $business_id) . "' "
        . "";
    if( isset($args['permalink']) && $args['permalink'] != '' ) {
        $strsql .= "AND ciniki_herbalist_products.permalink = '" . ciniki_core_dbQuote($ciniki, $args['permalink']) . "' ";
    } elseif( isset($args['id']) && $args['id'] > 0 ) {
        $strsql .= "AND ciniki_herbalist_products.id = '" . ciniki_core_dbQuote($ciniki, $args['id']) . "' ";
    } else {
        return array('stat'=>'fail', 'err'=>array('pkg'=>'ciniki', 'code'=>'3432', 'msg'=>'No product specified'));
    }
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbHashQuery');
    $rc = ciniki_core_dbHashQuery($ciniki, $strsql, 'ciniki.herbalist', 'product');
    if( $rc['stat'] != 'ok' ) {
        return array('stat'=>'fail', 'err'=>array('pkg'=>'ciniki', 'code'=>'3452', 'msg'=>'Product not found', 'err'=>$rc['err']));
    }
    if( !isset($rc['product']) ) {
        return array('stat'=>'fail', 'err'=>array('pkg'=>'ciniki', 'code'=>'3453', 'msg'=>'Unable to find Product'));
    }
    $product = $rc['product'];

    //
    // Get the versions of the product
    //
    $strsql = "SELECT ciniki_herbalist_product_versions.id, "
        . "ciniki_herbalist_product_versions.product_id, "
        . "ciniki_herbalist_product_versions.name, "
        . "ciniki_herbalist_product_versions.permalink, "
        . "ciniki_herbalist_product_versions.flags, "
        . "ciniki_herbalist_product_versions.inventory, "
        . "ciniki_herbalist_product_versions.retail_price AS unit_amount "
        . "FROM ciniki_herbalist_product_versions "
        . "WHERE ciniki_herbalist_product_versions.business_id = '" . ciniki_core_dbQuote($ciniki, $business_id) . "' "
        . "AND ciniki_herbalist_product_versions.product_id = '" . ciniki_core_dbQuote($ciniki, $product['id']) . "' "
        . "AND (ciniki_herbalist_product_versions.flags&0x01) = 0x01 "
        . "ORDER BY sequence, name "
        . "";
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbHashQueryArrayTree');
    $rc = ciniki_core_dbHashQueryArrayTree($ciniki, $strsql, 'ciniki.herbalist', array(
        array('container'=>'versions', 'fname'=>'id', 
            'fields'=>array('id', 'product_id', 'name', 'permalink', 'inventory', 'unit_amount')),
        ));
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    if( isset($rc['versions']) ) {
        $product['versions'] = $rc['versions'];
    } else {
        $product['versions'] = array();
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
            . "WHERE product_id = '" . ciniki_core_dbQuote($ciniki, $product['id']) . "' "
            . "AND business_id = '" . ciniki_core_dbQuote($ciniki, $business_id) . "' "
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
                    $rc = ciniki_images_loadCacheThumbnail($ciniki, $business_id, $img['image_id'], 75);
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

    return array('stat'=>'ok', 'product'=>$product);
}
?>
