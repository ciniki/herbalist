<?php
//
// Description
// ===========
// This method will return all the information about an container.
//
// Arguments
// ---------
// api_key:
// auth_token:
// tnid:         The ID of the tenant the container is attached to.
// container_id:          The ID of the container to get the details for.
//
// Returns
// -------
//
function ciniki_herbalist_containerGet($ciniki) {
    //
    // Find all the required and optional arguments
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'prepareArgs');
    $rc = ciniki_core_prepareArgs($ciniki, 'no', array(
        'tnid'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Tenant'),
        'container_id'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Container'),
        ));
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    $args = $rc['args'];

    //
    // Make sure this module is activated, and
    // check permission to run this function for this tenant
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'herbalist', 'private', 'checkAccess');
    $rc = ciniki_herbalist_checkAccess($ciniki, $args['tnid'], 'ciniki.herbalist.containerGet');
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

    ciniki_core_loadMethod($ciniki, 'ciniki', 'users', 'private', 'datetimeFormat');
    $datetime_format = ciniki_users_datetimeFormat($ciniki, 'php');

    //
    // Return default for new Container
    //
    if( $args['container_id'] == 0 ) {
        $container = array('id'=>0,
            'name'=>'',
            'top_quantity'=>'',
            'top_price'=>'',
            'bottom_quantity'=>'',
            'bottom_price'=>'',
            'cost_per_unit'=>'',
            'notes'=>'',
        );
    }

    //
    // Get the details for an existing Container
    //
    else {
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
            . "AND ciniki_herbalist_containers.id = '" . ciniki_core_dbQuote($ciniki, $args['container_id']) . "' "
            . "";
        ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbHashQuery');
        $rc = ciniki_core_dbHashQuery($ciniki, $strsql, 'ciniki.herbalist', 'container');
        if( $rc['stat'] != 'ok' ) {
            return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.herbalist.16', 'msg'=>'Container not found', 'err'=>$rc['err']));
        }
        if( !isset($rc['container']) ) {
            return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.herbalist.17', 'msg'=>'Unable to find Container'));
        }
        $container = $rc['container'];
        $container['top_price'] = numfmt_format_currency($intl_currency_fmt, $container['top_price'], $intl_currency);
        $container['bottom_price'] = numfmt_format_currency($intl_currency_fmt, $container['bottom_price'], $intl_currency);
        $container['cost_per_unit'] = numfmt_format_currency($intl_currency_fmt, $container['cost_per_unit'], $intl_currency);
    }

    return array('stat'=>'ok', 'container'=>$container);
}
?>
