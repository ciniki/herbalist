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
function ciniki_herbalist_herbReindex(&$ciniki) {
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
    $rc = ciniki_herbalist_checkAccess($ciniki, $args['business_id'], 'ciniki.herbalist.herbUpdate');
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
        . "WHERE ciniki_herbalist_herbs.business_id = '" . ciniki_core_dbQuote($ciniki, $args['business_id']) . "' "
        . "";
    $rc = ciniki_core_dbHashQuery($ciniki, $strsql, 'ciniki.herbalist', 'herb');
    if( $rc['stat'] != 'ok' ) {
        return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.herbalist.88', 'msg'=>'herb not found', 'err'=>$rc['err']));
    }
    
    $herbs = $rc['rows'];
    foreach($herbs as $herb) {
        //
        // Setup the keywords_index field
        //
        $str = '';
        $str .= (isset($herb['dry']) ? ' ' . $herb['dry'] : '');
        $str .= (isset($herb['tincture']) ? ' ' . $herb['tincture'] : '');
        $str .= (isset($herb['latin_name']) ? ' ' . $herb['latin_name'] : '');
        $str .= (isset($herb['common_name']) ? ' ' . $herb['common_name'] : '');
        $str .= (isset($herb['dose']) ? ' ' . $herb['dose'] : '');
        $str .= (isset($herb['safety']) ? ' ' . $herb['safety'] : '');
        $str .= (isset($herb['actions']) ? ' ' . $herb['actions'] : '');
        $str .= (isset($herb['ailments']) ? ' ' . $herb['ailments'] : '');
        $str .= (isset($herb['energetics']) ? ' ' . $herb['energetics'] : '');

        ciniki_core_loadMethod($ciniki, 'ciniki', 'herbalist', 'private', 'herbKeywordsSetup');
        $rc = ciniki_herbalist_herbKeywordsSetup($ciniki, $str);
        if( $rc['stat'] != 'ok' ) {
            return $rc;
        }
        if( $rc['keywords'] != $herb['keywords_index'] ) {
            ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'objectUpdate');
            $rc = ciniki_core_objectUpdate($ciniki, $args['business_id'], 'ciniki.herbalist.herb', $herb['id'], array('keywords_index'=>$rc['keywords']), 0x07);
            if( $rc['stat'] != 'ok' ) {
                return $rc;
            }
        }
    }

    //
    // Update the last_change date in the business modules
    // Ignore the result, as we don't want to stop user updates if this fails.
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'businesses', 'private', 'updateModuleChangeDate');
    ciniki_businesses_updateModuleChangeDate($ciniki, $args['business_id'], 'ciniki', 'herbalist');

    return array('stat'=>'ok');
}
?>
