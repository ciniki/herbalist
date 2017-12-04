<?php
//
// Description
// -----------
// This method will return the list of Containers for a tenant.
//
// Arguments
// ---------
// api_key:
// auth_token:
// tnid:        The ID of the tenant to get Container for.
//
// Returns
// -------
//
function ciniki_herbalist_containerList($ciniki) {
    //
    // Find all the required and optional arguments
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'prepareArgs');
    $rc = ciniki_core_prepareArgs($ciniki, 'no', array(
        'tnid'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Tenant'),
        ));
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    $args = $rc['args'];

    //
    // Check access to tnid as owner, or sys admin.
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'herbalist', 'private', 'checkAccess');
    $rc = ciniki_herbalist_checkAccess($ciniki, $args['tnid'], 'ciniki.herbalist.containerList');
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
    // Get the list of containers
    //
    $strsql = "SELECT ciniki_herbalist_containers.id, "
        . "ciniki_herbalist_containers.name, "
        . "ciniki_herbalist_containers.top_quantity, "
        . "ciniki_herbalist_containers.top_price, "
        . "ciniki_herbalist_containers.bottom_quantity, "
        . "ciniki_herbalist_containers.bottom_price, "
        . "ciniki_herbalist_containers.cost_per_unit, "
        . "ciniki_herbalist_containers.notes "
        . "FROM ciniki_herbalist_containers "
        . "WHERE ciniki_herbalist_containers.tnid = '" . ciniki_core_dbQuote($ciniki, $args['tnid']) . "' "
        . "ORDER BY CAST(name AS UNSIGNED), name "
        . "";
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbHashQueryArrayTree');
    $rc = ciniki_core_dbHashQueryArrayTree($ciniki, $strsql, 'ciniki.herbalist', array(
        array('container'=>'containers', 'fname'=>'id', 
            'fields'=>array('id', 'name', 'top_quantity', 'top_price', 'bottom_quantity', 'bottom_price', 'cost_per_unit', 'notes')),
        ));
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    $container_ids = array();
    if( isset($rc['containers']) ) {
        $containers = $rc['containers'];
        foreach($containers as $cid => $container) {
            $container_ids[] = $container['id'];
            $containers[$cid]['cost_per_unit_display'] = numfmt_format_currency($intl_currency_fmt, $container['cost_per_unit'], $intl_currency);
        }
        usort($containers, function($a, $b) { return strnatcmp($a['name'], $b['name']); });
    } else {
        $containers = array();
    }

    return array('stat'=>'ok', 'containers'=>$containers, 'nextprevlist'=>$container_ids);
}
?>
