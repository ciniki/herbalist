<?php
//
// Description
// -----------
// This method will turn the herbalist settings for a business.
//
// Arguments
// ---------
// api_key:
// auth_token:
// business_id:		The ID of the business to get the ATDO settings for.
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
        'business_id'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Business'), 
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
    $rc = ciniki_herbalist_checkAccess($ciniki, $args['business_id'], 'ciniki.herbalist.settingsGet'); 
    if( $rc['stat'] != 'ok' ) { 
        return $rc;
    }   
	
	//
	// Grab the settings for the business from the database
	//
	ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbDetailsQuery');
	$rc = ciniki_core_dbDetailsQuery($ciniki, 'ciniki_herbalist_settings', 'business_id', $args['business_id'], 'ciniki.herbalist', 'settings', '');
	if( $rc['stat'] != 'ok' ) {
		return $rc;
	}

	if( !isset($rc['settings']) ) {
		return array('stat'=>'ok', 'settings'=>array());
	}
	return array('stat'=>'ok', 'settings'=>$rc['settings']);
}
?>
