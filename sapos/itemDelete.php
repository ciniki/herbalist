<?php
//
// Description
// ===========
// This method will be called whenever a item is updated in an invoice.
//
// Arguments
// =========
// 
// Returns
// =======
//
function ciniki_herbalist_sapos_itemDelete($ciniki, $tnid, $invoice_id, $item) {

    //
    // Check the product exists and update the inventory
    //
    if( isset($item['object']) && $item['object'] == 'ciniki.herbalist.productversion' && isset($item['object_id']) ) {
        $strsql = "SELECT ciniki_herbalist_product_versions.id, "
            . "CONCAT_WS(' - ', ciniki_herbalist_products.name, ciniki_herbalist_product_versions.name) AS description, "
            . "ciniki_herbalist_product_versions.retail_price AS unit_amount, "
            . "ciniki_herbalist_product_versions.inventory AS units_available "
            . "FROM ciniki_herbalist_products, ciniki_herbalist_product_versions "
            . "WHERE ciniki_herbalist_products.tnid = '" . ciniki_core_dbQuote($ciniki, $tnid) . "' "
            . "AND ciniki_herbalist_products.id = ciniki_herbalist_product_versions.product_id "
            . "AND ciniki_herbalist_product_versions.tnid = '" . ciniki_core_dbQuote($ciniki, $tnid) . "' "
            . "AND ciniki_herbalist_product_versions.id = '" . ciniki_core_dbQuote($ciniki, $item['object_id']) . "' "
            . "";
        $rc = ciniki_core_dbHashQuery($ciniki, $strsql, 'ciniki.herbalist', 'product');
        if( $rc['stat'] != 'ok' ) {
            return $rc;
        }
        if( !isset($rc['product']) ) {
            return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.herbalist.63', 'msg'=>'No product found.'));        
        }
        $product = $rc['product'];

        $rsp = array('stat'=>'ok');

        //
        // Update the inventory
        //
        $new_quantity = $product['units_available'] + $item['quantity'];
        ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'objectUpdate');
        $rc = ciniki_core_objectUpdate($ciniki, $tnid, 'ciniki.herbalist.productversion', $item['object_id'], array('inventory'=>$new_quantity));
        if( $rc['stat'] != 'ok' ) {
            return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.herbalist.64', 'msg'=>'Unable to add product.', 'err'=>$rc['err']));        
        }

        return array('stat'=>'ok');
    }

    return array('stat'=>'ok');
}
?>
