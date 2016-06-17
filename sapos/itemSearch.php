<?php
//
// Description
// ===========
// This function will search the herbalist for the ciniki.sapos module.
//
// Arguments
// =========
// 
// Returns
// =======
//
function ciniki_herbalist_sapos_itemSearch($ciniki, $business_id, $args) {

    if( !isset($args['start_needle']) || $args['start_needle'] == '' ) {
        return array('stat'=>'ok', 'items'=>array());
    }

    $args['start_needle'] = str_ireplace(' ', '%', $args['start_needle']);

    ciniki_core_loadMethod($ciniki, 'ciniki', 'users', 'private', 'dateFormat');
    $date_format = ciniki_users_dateFormat($ciniki);

    //
    // Load the status maps for the text description of each type
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'herbalist', 'private', 'maps');
    $rc = ciniki_herbalist_maps($ciniki);
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    $maps = $rc['maps'];

    //
    // Prepare the query
    //
    $strsql = "SELECT ciniki_herbalist_product_versions.id AS object_id, "
        . "CONCAT_WS(' - ', ciniki_herbalist_products.name, ciniki_herbalist_product_versions.name) AS description, "
        . "ciniki_herbalist_product_versions.retail_price AS unit_amount, "
        . "0 AS unit_discount_amount, "
        . "0 AS unit_discount_percentage, "
        . "0 AS taxtype_id, "
        . "1 AS quantity, "
        . "ciniki_herbalist_product_versions.inventory AS inventory_available "
        . "FROM ciniki_herbalist_products, ciniki_herbalist_product_versions "
        . "WHERE ciniki_herbalist_products.business_id = '" . ciniki_core_dbQuote($ciniki, $business_id) . "' "
        . "AND ciniki_herbalist_products.id = ciniki_herbalist_product_versions.product_id "
        . "AND ciniki_herbalist_product_versions.business_id = '" . ciniki_core_dbQuote($ciniki, $business_id) . "' "
        . "HAVING "
            . "description LIKE '" . ciniki_core_dbQuote($ciniki, $args['start_needle']) . "%' "
            . "OR description LIKE '% " . ciniki_core_dbQuote($ciniki, $args['start_needle']) . "%' "
        . "";
    if( isset($args['limit']) && $args['limit'] != '' && preg_match("/^[0-9]+$/", $args['limit']) ) {
        $strsql .= "LIMIT " . $args['limit'];
    }
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbHashQueryTree');
    $rc = ciniki_core_dbHashQueryTree($ciniki, $strsql, 'ciniki.herbalist', array(
        array('container'=>'items', 'fname'=>'object_id', 'name'=>'item',
            'fields'=>array('object_id', 'description', 'unit_amount', 'unit_discount_amount', 'unit_discount_percentage', 'taxtype_id', 'quantity', 'inventory_available')),
        ));
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    if( isset($rc['items']) ) {
        $items = $rc['items'];
        foreach($items as $iid => $item) {
            $items[$iid]['item']['quantity'] = 1;
            $items[$iid]['item']['object'] = 'ciniki.herbalist.productversion';
            $items[$iid]['item']['price_id'] = 0;
            $items[$iid]['item']['notes'] = '';
        }
    } else {
        return array('stat'=>'ok', 'items'=>array());
    }

    return array('stat'=>'ok', 'items'=>$items);        
}
?>
