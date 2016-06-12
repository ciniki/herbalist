<?php
//
// Description
// -----------
// This method will return the list of Notes for a business.
//
// Arguments
// ---------
// api_key:
// auth_token:
// business_id:        The ID of the business to get Note for.
//
// Returns
// -------
//
function ciniki_herbalist_noteList($ciniki) {
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
    // Check access to business_id as owner, or sys admin.
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'herbalist', 'private', 'checkAccess');
    $rc = ciniki_herbalist_checkAccess($ciniki, $args['business_id'], 'ciniki.herbalist.noteList');
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }

    //
    // Get the list of notes
    //
    $strsql = "SELECT ciniki_herbalist_notes.id, "
        . "ciniki_herbalist_notes.note_date, "
        . "ciniki_herbalist_notes.content "
        . "FROM ciniki_herbalist_notes "
        . "WHERE ciniki_herbalist_notes.business_id = '" . ciniki_core_dbQuote($ciniki, $args['business_id']) . "' "
        . "";
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbHashQueryArrayTree');
    $rc = ciniki_core_dbHashQueryArrayTree($ciniki, $strsql, 'ciniki.herbalist', array(
        array('container'=>'notes', 'fname'=>'id', 
            'fields'=>array('id', 'note_date', 'content')),
        ));
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    if( isset($rc['notes']) ) {
        $notes = $rc['notes'];
    } else {
        $notes = array();
    }

    return array('stat'=>'ok', 'notes'=>$notes);
}
?>
