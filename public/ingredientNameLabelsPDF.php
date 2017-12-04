<?php
//
// Description
// -----------
// This method will return the list of Ingredients for a tenant.
//
// Arguments
// ---------
// api_key:
// auth_token:
// tnid:        The ID of the tenant to get Ingredient for.
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
        'tnid'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Tenant'),
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
    // Check access to tnid as owner, or sys admin.
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'herbalist', 'private', 'checkAccess');
    $rc = ciniki_herbalist_checkAccess($ciniki, $args['tnid'], 'ciniki.herbalist.ingredientNameLabelsPDF');
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }

    //
    // Load tenant settings
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'tenants', 'private', 'intlSettings');
    $rc = ciniki_tenants_intlSettings($ciniki, $args['tnid']);
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
        . "WHERE ciniki_herbalist_ingredients.tnid = '" . ciniki_core_dbQuote($ciniki, $args['tnid']) . "' "
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
    $rc = ciniki_herbalist_templates_labelsPDF($ciniki, $args['tnid'], $args);
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
