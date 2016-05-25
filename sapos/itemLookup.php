<?php
//
// Description
// ===========
// This function will lookup an object for adding to an invoice/cart.
//
// Arguments
// =========
// 
// Returns
// =======
//
function ciniki_herbalist_sapos_itemLookup($ciniki, $business_id, $args) {

	if( !isset($args['object']) || $args['object'] == '' || !isset($args['object_id']) || $args['object_id'] == '' ) {
		return array('stat'=>'fail', 'err'=>array('pkg'=>'ciniki', 'code'=>'3482', 'msg'=>'No product specified.'));
	}

	//
	// Lookup the requested product if specified along with a price_id
	//
	if( $args['object'] == 'ciniki.herbalist.productversion' && isset($args['object_id']) && $args['object_id'] > 0 ) {
        $strsql = "SELECT ciniki_herbalist_product_versions.id AS object_id, "
            . "CONCAT_WS(' - ', ciniki_herbalist_products.name, ciniki_herbalist_product_versions.name) AS description, "
            . "ciniki_herbalist_product_versions.retail_price AS unit_amount, "
            . "0 AS unit_discount_amount, "
            . "0 AS unit_discount_percentage, "
            . "0 AS taxtype_id, "
            . "1 AS quantity, "
            . "ciniki_herbalist_product_versions.inventory AS units_available "
            . "FROM ciniki_herbalist_products, ciniki_herbalist_product_versions "
            . "WHERE ciniki_herbalist_products.business_id = '" . ciniki_core_dbQuote($ciniki, $business_id) . "' "
            . "AND ciniki_herbalist_products.id = ciniki_herbalist_product_versions.product_id "
            . "AND ciniki_herbalist_product_versions.business_id = '" . ciniki_core_dbQuote($ciniki, $business_id) . "' "
            . "AND ciniki_herbalist_product_versions.id = '" . ciniki_core_dbQuote($ciniki, $args['object_id']) . "' "
            . "";
        $rc = ciniki_core_dbHashQuery($ciniki, $strsql, 'ciniki.herbalist', 'product');
		if( $rc['stat'] != 'ok' ) {
			return $rc;
		}
/*		ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbHashQueryIDTree');
		$rc = ciniki_core_dbHashQueryIDTree($ciniki, $strsql, 'ciniki.products', array(
			array('container'=>'products', 'fname'=>'id',
				'fields'=>array('id', 'price_id', 'parent_id', 'code', 'description'=>'name', 'product_flags',
					'pricepoint_id', 
					'unit_amount', 'unit_discount_amount', 'unit_discount_percentage',
					'inventory_flags', 'inventory_current_num', 
					'taxtype_id')),
			));
		if( !isset($rc['products']) || count($rc['products']) < 1 ) {
			return array('stat'=>'fail', 'err'=>array('pkg'=>'ciniki', 'code'=>'3483', 'msg'=>'No product found.'));		
		}
        */
        if( !isset($rc['product']) ) {
			return array('stat'=>'fail', 'err'=>array('pkg'=>'ciniki', 'code'=>'3483', 'msg'=>'No product found.'));		
        }
		$product = $rc['product'];
        $product['price_id'] = 0;
        $product['flags'] = 0x46;
        $product['limited_units'] = 'no';

		return array('stat'=>'ok', 'item'=>$product);
	}

	return array('stat'=>'fail', 'err'=>array('pkg'=>'ciniki', 'code'=>'3484', 'msg'=>'No product specified.'));		
}
?>
