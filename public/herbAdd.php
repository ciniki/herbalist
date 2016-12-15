<?php
//
// Description
// -----------
// This method will add a new herb for the business.
//
// Arguments
// ---------
// api_key:
// auth_token:
// business_id:        The ID of the business to add the herb to.
//
// Returns
// -------
//
function ciniki_herbalist_herbAdd(&$ciniki) {
    //
    // Find all the required and optional arguments
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'prepareArgs');
    $rc = ciniki_core_prepareArgs($ciniki, 'no', array(
        'business_id'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Business'),
        'dry'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Dry'),
        'tincture'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Tincture'),
        'latin_name'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Latin Name'),
        'common_name'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Common Name'),
        'dose'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Dose'),
        'safety'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Safety'),
        'actions'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Actions'),
        'ailments'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Ailments'),
        'energetics'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Energetics'),
        ));
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    $args = $rc['args'];

    //
    // Check access to business_id as owner
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'herbalist', 'private', 'checkAccess');
    $rc = ciniki_herbalist_checkAccess($ciniki, $args['business_id'], 'ciniki.herbalist.herbAdd');
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }

    //
    // Start transaction
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbTransactionStart');
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbTransactionRollback');
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbTransactionCommit');
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbAddModuleHistory');
    $rc = ciniki_core_dbTransactionStart($ciniki, 'ciniki.herbalist');
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }

    //
    // Setup the keywords_index field
    //
    $str = '';
    $str .= (isset($args['latin_name']) ? ' ' . $args['latin_name'] : '');
    $str .= (isset($args['common_name']) ? ' ' . $args['common_name'] : '');
    $str .= (isset($args['dose']) ? ' ' . $args['dose'] : '');
    $str .= (isset($args['safety']) ? ' ' . $args['safety'] : '');
    $str .= (isset($args['actions']) ? ' ' . $args['actions'] : '');
    $str .= (isset($args['ailments']) ? ' ' . $args['ailments'] : '');
    $str .= (isset($args['energetics']) ? ' ' . $args['energetics'] : '');

    ciniki_core_loadMethod($ciniki, 'ciniki', 'herbalist', 'private', 'herbKeywordsSetup');
    $rc = ciniki_herbalist_herbKeywordsSetup($ciniki, $str);
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    $args['keywords_index'] = $rc['keywords'];

    //
    // Add the herb to the database
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'objectAdd');
    $rc = ciniki_core_objectAdd($ciniki, $args['business_id'], 'ciniki.herbalist.herb', $args, 0x04);
    if( $rc['stat'] != 'ok' ) {
        ciniki_core_dbTransactionRollback($ciniki, 'ciniki.herbalist');
        return $rc;
    }
    $herb_id = $rc['id'];

    //
    // Commit the transaction
    //
    $rc = ciniki_core_dbTransactionCommit($ciniki, 'ciniki.herbalist');
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }

    //
    // Update the last_change date in the business modules
    // Ignore the result, as we don't want to stop user updates if this fails.
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'businesses', 'private', 'updateModuleChangeDate');
    ciniki_businesses_updateModuleChangeDate($ciniki, $args['business_id'], 'ciniki', 'herbalist');

    //
    // Update the web index if enabled
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'hookExec');
    ciniki_core_hookExec($ciniki, $args['business_id'], 'ciniki', 'web', 'indexObject', array('object'=>'ciniki.herbalist.herb', 'object_id'=>$herb_id));

    return array('stat'=>'ok', 'id'=>$herb_id);
}
?>
