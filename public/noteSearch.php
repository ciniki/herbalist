<?php
//
// Description
// -----------
// This method will return the list of Notes for a tenant.
//
// Arguments
// ---------
// api_key:
// auth_token:
// tnid:        The ID of the tenant to get Note for.
//
// Returns
// -------
//
function ciniki_herbalist_noteSearch($ciniki) {
    //
    // Find all the required and optional arguments
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'prepareArgs');
    $rc = ciniki_core_prepareArgs($ciniki, 'no', array(
        'tnid'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Tenant'),
        'search_str'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Search String'),
        'limit'=>array('required'=>'no', 'blank'=>'yes', 'default'=>'15', 'name'=>'Limit'),
        ));
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    $args = $rc['args'];

    //
    // Check access to tnid as owner, or sys admin.
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'herbalist', 'private', 'checkAccess');
    $rc = ciniki_herbalist_checkAccess($ciniki, $args['tnid'], 'ciniki.herbalist.noteSearch');
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }

    ciniki_core_loadMethod($ciniki, 'ciniki', 'users', 'private', 'dateFormat');
    $mysql_date_format = ciniki_users_dateFormat($ciniki, 'mysql');

    $keywords = explode(' ', trim($args['search_str']));
    sort($keywords);
    $keywords = implode('% ', $keywords);

    //
    // Get the list of notes
    //
    $strsql = "SELECT ciniki_herbalist_notes.id, "
        . "DATE_FORMAT(ciniki_herbalist_notes.note_date, '" . ciniki_core_dbQuote($ciniki, $mysql_date_format) . "') AS note_date, "
        . "ciniki_herbalist_notes.content, "
        . "ciniki_herbalist_notes.keywords "
        . "FROM ciniki_herbalist_notes "
        . "WHERE ciniki_herbalist_notes.tnid = '" . ciniki_core_dbQuote($ciniki, $args['tnid']) . "' "
        . "AND ("
            . "keywords_index LIKE '" . ciniki_core_dbQuote($ciniki, $keywords) . "%' "
            . "OR keywords_index LIKE '% " . ciniki_core_dbQuote($ciniki, $keywords) . "%' "
            . ") "
        . "";
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbHashQueryArrayTree');
    $rc = ciniki_core_dbHashQueryArrayTree($ciniki, $strsql, 'ciniki.herbalist', array(
        array('container'=>'notes', 'fname'=>'id', 
            'fields'=>array('id', 'note_date', 'content', 'keywords')),
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
