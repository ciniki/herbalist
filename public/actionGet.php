<?php
//
// Description
// ===========
// This method will return all the information about an action.
//
// Arguments
// ---------
// api_key:
// auth_token:
// tnid:         The ID of the tenant the action is attached to.
// action_id:          The ID of the action to get the details for.
//
// Returns
// -------
//
function ciniki_herbalist_actionGet($ciniki) {
    //
    // Find all the required and optional arguments
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'prepareArgs');
    $rc = ciniki_core_prepareArgs($ciniki, 'no', array(
        'tnid'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Tenant'),
        'action_id'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Action'),
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
    $rc = ciniki_herbalist_checkAccess($ciniki, $args['tnid'], 'ciniki.herbalist.actionGet');
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
    // Return default for new Action
    //
    if( $args['action_id'] == 0 ) {
        $action = array('id'=>0,
            'name'=>'',
            'description'=>'',
            'notes'=>array(),
        );
    }

    //
    // Get the details for an existing Action
    //
    else {
        $strsql = "SELECT ciniki_herbalist_actions.id, "
            . "ciniki_herbalist_actions.name, "
            . "ciniki_herbalist_actions.description "
            . "FROM ciniki_herbalist_actions "
            . "WHERE ciniki_herbalist_actions.tnid = '" . ciniki_core_dbQuote($ciniki, $args['tnid']) . "' "
            . "AND ciniki_herbalist_actions.id = '" . ciniki_core_dbQuote($ciniki, $args['action_id']) . "' "
            . "";
        ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbHashQuery');
        $rc = ciniki_core_dbHashQuery($ciniki, $strsql, 'ciniki.herbalist', 'action');
        if( $rc['stat'] != 'ok' ) {
            return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.herbalist.9', 'msg'=>'Action not found', 'err'=>$rc['err']));
        }
        if( !isset($rc['action']) ) {
            return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.herbalist.10', 'msg'=>'Unable to find Action'));
        }
        $action = $rc['action'];

        //
        // Get any notes for this action
        //
        ciniki_core_loadMethod($ciniki, 'ciniki', 'herbalist', 'private', 'objectNotes');
        $rc = ciniki_herbalist_objectNotes($ciniki, $args['tnid'], 'ciniki.herbalist.action', $args['action_id']);
        if( $rc['stat'] != 'ok' ) {
            return $rc;
        }
        if( isset($rc['notes']) ) {
            $action['notes'] = $rc['notes'];
        } else {
            $action['notes'] = array();
        }
    }

    return array('stat'=>'ok', 'action'=>$action);
}
?>
