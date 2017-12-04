<?php
//
// Description
// ===========
//
// Arguments
// ---------
//
// Returns
// -------
//
function ciniki_herbalist_herbUpdate(&$ciniki) {
    //
    // Find all the required and optional arguments
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'prepareArgs');
    $rc = ciniki_core_prepareArgs($ciniki, 'no', array(
        'tnid'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Tenant'),
        'herb_id'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'herb'),
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
    // Make sure this module is activated, and
    // check permission to run this function for this tenant
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'herbalist', 'private', 'checkAccess');
    $rc = ciniki_herbalist_checkAccess($ciniki, $args['tnid'], 'ciniki.herbalist.herbUpdate');
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }

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
        . "WHERE ciniki_herbalist_herbs.tnid = '" . ciniki_core_dbQuote($ciniki, $args['tnid']) . "' "
        . "AND ciniki_herbalist_herbs.id = '" . ciniki_core_dbQuote($ciniki, $args['herb_id']) . "' "
        . "";
    $rc = ciniki_core_dbHashQuery($ciniki, $strsql, 'ciniki.herbalist', 'herb');
    if( $rc['stat'] != 'ok' ) {
        return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.herbalist.83', 'msg'=>'herb not found', 'err'=>$rc['err']));
    }
    if( !isset($rc['herb']) ) {
        return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.herbalist.84', 'msg'=>'Unable to find herb'));
    }
    $herb = $rc['herb'];

    //
    // Setup the keywords_index field
    //
    $str = '';
    $str .= (isset($args['dry']) ? ' ' . $args['dry'] : ' ' . $herb['dry']);
    $str .= (isset($args['tincture']) ? ' ' . $args['tincture'] : ' ' . $herb['tincture']);
    $str .= (isset($args['latin_name']) ? ' ' . $args['latin_name'] : ' ' . $herb['latin_name']);
    $str .= (isset($args['common_name']) ? ' ' . $args['common_name'] : ' ' . $herb['common_name']);
    $str .= (isset($args['dose']) ? ' ' . $args['dose'] : ' ' . $herb['dose']);
    $str .= (isset($args['safety']) ? ' ' . $args['safety'] : ' ' . $herb['safety']);
    $str .= (isset($args['actions']) ? ' ' . $args['actions'] : ' ' . $herb['actions']);
    $str .= (isset($args['ailments']) ? ' ' . $args['ailments'] : ' ' . $herb['ailments']);
    $str .= (isset($args['energetics']) ? ' ' . $args['energetics'] : ' ' . $herb['energetics']);

    ciniki_core_loadMethod($ciniki, 'ciniki', 'herbalist', 'private', 'herbKeywordsSetup');
    $rc = ciniki_herbalist_herbKeywordsSetup($ciniki, $str);
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    if( $rc['keywords'] != $herb['keywords_index'] ) {
        $args['keywords_index'] = $rc['keywords'];
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
    // Update the herb in the database
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'objectUpdate');
    $rc = ciniki_core_objectUpdate($ciniki, $args['tnid'], 'ciniki.herbalist.herb', $args['herb_id'], $args, 0x04);
    if( $rc['stat'] != 'ok' ) {
        ciniki_core_dbTransactionRollback($ciniki, 'ciniki.herbalist');
        return $rc;
    }

    //
    // Commit the transaction
    //
    $rc = ciniki_core_dbTransactionCommit($ciniki, 'ciniki.herbalist');
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }

    //
    // Update the last_change date in the tenant modules
    // Ignore the result, as we don't want to stop user updates if this fails.
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'tenants', 'private', 'updateModuleChangeDate');
    ciniki_tenants_updateModuleChangeDate($ciniki, $args['tnid'], 'ciniki', 'herbalist');

    //
    // Update the web index if enabled
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'hookExec');
    ciniki_core_hookExec($ciniki, $args['tnid'], 'ciniki', 'web', 'indexObject', array('object'=>'ciniki.herbalist.herb', 'object_id'=>$args['herb_id']));

    return array('stat'=>'ok');
}
?>
