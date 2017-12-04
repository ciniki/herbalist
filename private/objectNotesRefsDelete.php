<?php
//
// Description
// ===========
// This method will return all the information about an ingredient.
//
// Arguments
// ---------
// api_key:
// auth_token:
// tnid:         The ID of the tenant the ingredient is attached to.
// ingredient_id:          The ID of the ingredient to get the details for.
//
// Returns
// -------
//
function ciniki_herbalist_objectNotesRefsDelete($ciniki, $tnid, $object, $object_id) {
    //
    // Get the list of references
    //
    $strsql = "SELECT id, uuid "
        . "FROM ciniki_herbalist_note_refs "
        . "WHERE ciniki_herbalist_note_refs.object = '" . ciniki_core_dbQuote($ciniki, $object) . "' "
        . "AND ciniki_herbalist_note_refs.object_id = '" . ciniki_core_dbQuote($ciniki, $object_id) . "' "
        . "AND ciniki_herbalist_note_refs.tnid = '" . ciniki_core_dbQuote($ciniki, $tnid) . "' "
        . "";
    $rc = ciniki_core_dbHashQuery($ciniki, $strsql, 'ciniki.herbalist', 'item');
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    if( isset($rc['rows']) ) {
        foreach($rc['rows'] as $ref) {
            $rc = ciniki_core_objectDelete($ciniki, $args['tnid'], 'ciniki.herbalist.noteref', $ref['id'], $ref['uuid'], 0x04);
            if( $rc['stat'] != 'ok' ) {
                return $rc;
            }
        }
    }

    return array('stat'=>'ok');
}
?>
