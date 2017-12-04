<?php
//
// Description
// -----------
// This method will turn the herbalist settings for a tenant.
//
// Arguments
// ---------
// api_key:
// auth_token:
// tnid:        The ID of the tenant to get the ATDO settings for.
// 
// Returns
// -------
//
function ciniki_herbalist_settingsGet($ciniki) {
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
    // Make sure this module is activated, and
    // check permission to run this function for this tenant
    //  
    ciniki_core_loadMethod($ciniki, 'ciniki', 'herbalist', 'private', 'checkAccess');
    $rc = ciniki_herbalist_checkAccess($ciniki, $args['tnid'], 'ciniki.herbalist.settingsGet'); 
    if( $rc['stat'] != 'ok' ) { 
        return $rc;
    }   
    
    //
    // Grab the settings for the tenant from the database
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbDetailsQuery');
    $rc = ciniki_core_dbDetailsQuery($ciniki, 'ciniki_herbalist_settings', 'tnid', $args['tnid'], 'ciniki.herbalist', 'settings', '');
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }

    if( !isset($rc['settings']) ) {
        return array('stat'=>'ok', 'settings'=>array());
    }
    return array('stat'=>'ok', 'settings'=>$rc['settings']);
}
?>
