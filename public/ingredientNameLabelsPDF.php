<?php
//
// Description
// -----------
// This method will return the list of Ingredients for a business.
//
// Arguments
// ---------
// api_key:
// auth_token:
// business_id:        The ID of the business to get Ingredient for.
//
// Returns
// -------
//
function ciniki_herbalist_ingredientNameLabelsPDF($ciniki) {
    //
    // Find all the required and optional arguments
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'prepareArgs');
    $rc = ciniki_core_prepareArgs($ciniki, 'no', array(
        'business_id'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Business'),
        'label'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Recipe Batch'),
        'start_col'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Start Column'),
        'start_row'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Start Row'),
        'ingredients'=>array('required'=>'yes', 'blank'=>'no', 'type'=>'idlist', 'name'=>'Ingredients'),
        ));
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    $args = $rc['args'];

    //
    // Check access to business_id as owner, or sys admin.
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'herbalist', 'private', 'checkAccess');
    $rc = ciniki_herbalist_checkAccess($ciniki, $args['business_id'], 'ciniki.herbalist.ingredientNameLabelsPDF');
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

    //
    // Load the maps
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'herbalist', 'private', 'maps');
    $rc = ciniki_herbalist_maps($ciniki);
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    $maps = $rc['maps'];

    //
    // Get the list of ingredients
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbQuoteIDs');
    $strsql = "SELECT ciniki_herbalist_ingredients.id, "
        . "ciniki_herbalist_ingredients.name, "
        . "ciniki_herbalist_ingredients.subname "
        . "FROM ciniki_herbalist_ingredients "
        . "WHERE ciniki_herbalist_ingredients.business_id = '" . ciniki_core_dbQuote($ciniki, $args['business_id']) . "' "
        . "AND ciniki_herbalist_ingredients.id IN (" . ciniki_core_dbQuoteIDs($ciniki, $args['ingredients']) . ") "
        . "ORDER BY name, subname "
        . "";
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbHashQueryArrayTree');
    $rc = ciniki_core_dbHashQueryArrayTree($ciniki, $strsql, 'ciniki.herbalist', array(
        array('container'=>'ingredients', 'fname'=>'id', 'fields'=>array('id', 'title'=>'name', 'content'=>'subname')),
        ));
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    $args['title'] = 'Ingredient Names';
    $args['labels'] = $rc['ingredients'];

    ciniki_core_loadMethod($ciniki, 'ciniki', 'herbalist', 'templates', 'labelsPDF');
    $rc = ciniki_herbalist_templates_labelsPDF($ciniki, $args['business_id'], $args);
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }

    if( isset($rc['pdf']) ) {
        $filename = preg_replace('/[^a-zA-Z0-9_]/', '', preg_replace('/ /', '_', 'ingredientnames_' . $args['label']));
        $rc['pdf']->Output($filename . '.pdf', 'D');
    }

    return array('stat'=>'exit');
}
?>
