<?php
//
// Description
// -----------
// This method will return the list of Actions for a tenant.
//
// Arguments
// ---------
// api_key:
// auth_token:
// tnid:        The ID of the tenant to get Action for.
//
// Returns
// -------
//
function ciniki_herbalist_actionList($ciniki) {
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
    $rc = ciniki_herbalist_checkAccess($ciniki, $args['tnid'], 'ciniki.herbalist.actionList');
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }

    //
    // Get the list of actions
    //
    $strsql = "SELECT ciniki_herbalist_actions.id, "
        . "ciniki_herbalist_actions.name, "
        . "ciniki_herbalist_actions.description "
        . "FROM ciniki_herbalist_actions "
        . "WHERE ciniki_herbalist_actions.tnid = '" . ciniki_core_dbQuote($ciniki, $args['tnid']) . "' "
        . "";
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbHashQueryArrayTree');
    $rc = ciniki_core_dbHashQueryArrayTree($ciniki, $strsql, 'ciniki.herbalist', array(
        array('container'=>'actions', 'fname'=>'id', 
            'fields'=>array('id', 'name', 'description')),
        ));
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    $action_ids = array();
    if( isset($rc['actions']) ) {
        $actions = $rc['actions'];
        foreach($actions as $action) {
            $action_ids[] = $action['id'];
        }
    } else {
        $actions = array();
    }

    return array('stat'=>'ok', 'actions'=>$actions, 'nextprevlist'=>$action_ids);
}
?>
