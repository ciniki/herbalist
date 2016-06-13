<?php
//
// Description
// ===========
// This method will return all the information about an ailment.
//
// Arguments
// ---------
// api_key:
// auth_token:
// business_id:         The ID of the business the ailment is attached to.
// ailment_id:          The ID of the ailment to get the details for.
//
// Returns
// -------
//
function ciniki_herbalist_ailmentGet($ciniki) {
    //
    // Find all the required and optional arguments
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'prepareArgs');
    $rc = ciniki_core_prepareArgs($ciniki, 'no', array(
        'business_id'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Business'),
        'ailment_id'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Ailment'),
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
    $rc = ciniki_herbalist_checkAccess($ciniki, $args['business_id'], 'ciniki.herbalist.ailmentGet');
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

    ciniki_core_loadMethod($ciniki, 'ciniki', 'users', 'private', 'datetimeFormat');
    $datetime_format = ciniki_users_datetimeFormat($ciniki, 'php');

    //
    // Return default for new Ailment
    //
    if( $args['ailment_id'] == 0 ) {
        $ailment = array('id'=>0,
            'name'=>'',
            'description'=>'',
            'notes'=>array(),
        );
    }

    //
    // Get the details for an existing Ailment
    //
    else {
        $strsql = "SELECT ciniki_herbalist_ailments.id, "
            . "ciniki_herbalist_ailments.name, "
            . "ciniki_herbalist_ailments.description "
            . "FROM ciniki_herbalist_ailments "
            . "WHERE ciniki_herbalist_ailments.business_id = '" . ciniki_core_dbQuote($ciniki, $args['business_id']) . "' "
            . "AND ciniki_herbalist_ailments.id = '" . ciniki_core_dbQuote($ciniki, $args['ailment_id']) . "' "
            . "";
        ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbHashQuery');
        $rc = ciniki_core_dbHashQuery($ciniki, $strsql, 'ciniki.herbalist', 'ailment');
        if( $rc['stat'] != 'ok' ) {
            return array('stat'=>'fail', 'err'=>array('pkg'=>'ciniki', 'code'=>'3523', 'msg'=>'Ailment not found', 'err'=>$rc['err']));
        }
        if( !isset($rc['ailment']) ) {
            return array('stat'=>'fail', 'err'=>array('pkg'=>'ciniki', 'code'=>'3524', 'msg'=>'Unable to find Ailment'));
        }
        $ailment = $rc['ailment'];

        //
        // Get any notes for this ailment
        //
        ciniki_core_loadMethod($ciniki, 'ciniki', 'herbalist', 'private', 'objectNotes');
        $rc = ciniki_herbalist_objectNotes($ciniki, $args['business_id'], 'ciniki.herbalist.ailment', $args['ailment_id']);
        if( $rc['stat'] != 'ok' ) {
            return $rc;
        }
        if( isset($rc['notes']) ) {
            $ailment['notes'] = $rc['notes'];
        } else {
            $ailment['notes'] = array();
        }
    }

    return array('stat'=>'ok', 'ailment'=>$ailment);
}
?>
