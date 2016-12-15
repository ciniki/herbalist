<?php
//
// Description
// ===========
// This method will return all the information about an herb.
//
// Arguments
// ---------
// api_key:
// auth_token:
// business_id:         The ID of the business the herb is attached to.
// herb_id:          The ID of the herb to get the details for.
//
// Returns
// -------
//
function ciniki_herbalist_herbGet($ciniki) {
    //
    // Find all the required and optional arguments
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'prepareArgs');
    $rc = ciniki_core_prepareArgs($ciniki, 'no', array(
        'business_id'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Business'),
        'herb_id'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'herb'),
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
    $rc = ciniki_herbalist_checkAccess($ciniki, $args['business_id'], 'ciniki.herbalist.herbGet');
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

    ciniki_core_loadMethod($ciniki, 'ciniki', 'users', 'private', 'dateFormat');
    $date_format = ciniki_users_dateFormat($ciniki, 'php');

    //
    // Return default for new herb
    //
    if( $args['herb_id'] == 0 ) {
        $herb = array('id'=>0,
            'dry'=>'',
            'tincture'=>'',
            'latin_name'=>'',
            'common_name'=>'',
            'dose'=>'',
            'safety'=>'',
            'actions'=>'',
            'ailments'=>'',
            'energetics'=>'',
            'keywords_index'=>'',
        );
    }

    //
    // Get the details for an existing herb
    //
    else {
        $strsql = "SELECT ciniki_herbalist_herbs.id, "
            . "ciniki_herbalist_herbs.dry, "
            . "ciniki_herbalist_herbs.tincture, "
            . "ciniki_herbalist_herbs.latin_name, "
            . "ciniki_herbalist_herbs.common_name, "
            . "ciniki_herbalist_herbs.dose, "
            . "ciniki_herbalist_herbs.safety, "
            . "ciniki_herbalist_herbs.actions, "
            . "ciniki_herbalist_herbs.ailments, "
            . "ciniki_herbalist_herbs.energetics, "
            . "ciniki_herbalist_herbs.keywords_index "
            . "FROM ciniki_herbalist_herbs "
            . "WHERE ciniki_herbalist_herbs.business_id = '" . ciniki_core_dbQuote($ciniki, $args['business_id']) . "' "
            . "AND ciniki_herbalist_herbs.id = '" . ciniki_core_dbQuote($ciniki, $args['herb_id']) . "' "
            . "";
        ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbHashQueryArrayTree');
        $rc = ciniki_core_dbHashQueryArrayTree($ciniki, $strsql, 'ciniki.herbalist', array(
            array('container'=>'herbs', 'fname'=>'id', 
                'fields'=>array('dry', 'tincture', 'latin_name', 'common_name', 'dose', 'safety', 'actions', 'ailments', 'energetics', 'keywords_index'),
                ),
            ));
        if( $rc['stat'] != 'ok' ) {
            return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.herbalist.81', 'msg'=>'herb not found', 'err'=>$rc['err']));
        }
        if( !isset($rc['herbs'][0]) ) {
            return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.herbalist.82', 'msg'=>'Unable to find herb'));
        }
        $herb = $rc['herbs'][0];
    }

    return array('stat'=>'ok', 'herb'=>$herb);
}
?>
