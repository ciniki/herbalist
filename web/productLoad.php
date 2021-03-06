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
// tnid:         The ID of the tenant the product is attached to.
// product_id:          The ID of the product to get the details for.
//
// Returns
// -------
//
function ciniki_herbalist_web_productLoad($ciniki, $tnid, $args) {
    
    $strsql = "SELECT ciniki_herbalist_products.id, "
        . "ciniki_herbalist_products.uuid, "
        . "ciniki_herbalist_products.name, "
        . "ciniki_herbalist_products.permalink, "
        . "ciniki_herbalist_products.flags, "
        . "ciniki_herbalist_products.primary_image_id, "
        . "'' AS primary_image_caption, "
        . "ciniki_herbalist_products.synopsis, "
        . "ciniki_herbalist_products.description, "
        . "ciniki_herbalist_products.ingredients "
        . "FROM ciniki_herbalist_products "
        . "WHERE ciniki_herbalist_products.tnid = '" . ciniki_core_dbQuote($ciniki, $tnid) . "' "
        . "";
    if( isset($args['permalink']) && $args['permalink'] != '' ) {
        $strsql .= "AND ciniki_herbalist_products.permalink = '" . ciniki_core_dbQuote($ciniki, $args['permalink']) . "' ";
    } elseif( isset($args['id']) && $args['id'] > 0 ) {
        $strsql .= "AND ciniki_herbalist_products.id = '" . ciniki_core_dbQuote($ciniki, $args['id']) . "' ";
    } else {
        return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.herbalist.74', 'msg'=>'No product specified'));
    }
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbHashQuery');
    $rc = ciniki_core_dbHashQuery($ciniki, $strsql, 'ciniki.herbalist', 'product');
    if( $rc['stat'] != 'ok' ) {
        return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.herbalist.75', 'msg'=>'Product not found', 'err'=>$rc['err']));
    }
    if( !isset($rc['product']) ) {
        return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.herbalist.76', 'msg'=>'Unable to find Product'));
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
        . "WHERE ciniki_herbalist_product_versions.tnid = '" . ciniki_core_dbQuote($ciniki, $tnid) . "' "
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
            . "name AS title, "
            . "permalink, "
            . "flags, "
            . "image_id, "
            . "description "
            . "FROM ciniki_herbalist_product_images "
            . "WHERE product_id = '" . ciniki_core_dbQuote($ciniki, $product['id']) . "' "
            . "AND tnid = '" . ciniki_core_dbQuote($ciniki, $tnid) . "' "
            . "";
        $rc = ciniki_core_dbHashQueryArrayTree($ciniki, $strsql, 'ciniki.herbalist', array(
            array('container'=>'images', 'fname'=>'id', 'fields'=>array('id', 'title', 'permalink', 'flags', 'image_id', 'description')),
        ));
        if( $rc['stat'] != 'ok' ) {
            return $rc;
        }
        if( isset($rc['images']) ) {
            $product['images'] = $rc['images'];
        } else {
            $product['images'] = array();
        }
        if( $product['primary_image_id'] > 0 ) {
            $found = 'no';
            foreach($product['images'] as $image) {
                if( $image['image_id'] == $product['primary_image_id'] ) {
                    $found = 'yes';
                }
            }
            if( $found == 'no' ) {
                array_unshift($product['images'], array('title'=>'', 'flags'=>1, 'permalink'=>$product['uuid'], 'image_id'=>$product['primary_image_id'], 'description'=>''));
            }
        }
    }

    return array('stat'=>'ok', 'product'=>$product);
}
?>
