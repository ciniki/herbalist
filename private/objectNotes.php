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
// business_id:         The ID of the business the ingredient is attached to.
// ingredient_id:          The ID of the ingredient to get the details for.
//
// Returns
// -------
//
function ciniki_herbalist_objectNotes($ciniki, $business_id, $object, $object_id) {

    //
    // Load business settings
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'businesses', 'private', 'intlSettings');
    $rc = ciniki_businesses_intlSettings($ciniki, $business_id);
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    $intl_timezone = $rc['settings']['intl-default-timezone'];
    $intl_currency_fmt = numfmt_create($rc['settings']['intl-default-locale'], NumberFormatter::CURRENCY);
    $intl_currency = $rc['settings']['intl-default-currency'];

    ciniki_core_loadMethod($ciniki, 'ciniki', 'users', 'private', 'dateFormat');
    $date_format = ciniki_users_dateFormat($ciniki, 'mysql');

    //
    // Get any notes for the object
    //
    $strsql = "SELECT ciniki_herbalist_notes.id, "
        . "IFNULL(DATE_FORMAT(ciniki_herbalist_notes.note_date, '" . ciniki_core_dbQuote($ciniki, $date_format) . "'), '') AS note_date, "
        . "ciniki_herbalist_notes.content, "
        . "ciniki_herbalist_notes.keywords "
        . "FROM ciniki_herbalist_note_refs "
        . "LEFT JOIN ciniki_herbalist_notes ON ("
            . "ciniki_herbalist_note_refs.note_id = ciniki_herbalist_notes.id "
            . "AND ciniki_herbalist_notes.business_id = '" . ciniki_core_dbQuote($ciniki, $business_id) . "' "
            . ") "
        . "WHERE ciniki_herbalist_note_refs.business_id = '" . ciniki_core_dbQuote($ciniki, $business_id) . "' "
        . "AND ciniki_herbalist_note_refs.object = '" . ciniki_core_dbQuote($ciniki, $object) . "' "
        . "AND ciniki_herbalist_note_refs.object_id = '" . ciniki_core_dbQuote($ciniki, $object_id) . "' "
        . "ORDER BY note_date, ciniki_herbalist_notes.date_added "
        . "";
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbHashQueryArrayTree');
    return ciniki_core_dbHashQueryArrayTree($ciniki, $strsql, 'ciniki.herbalist', array(
        array('container'=>'notes', 'fname'=>'id', 'fields'=>array('id', 'note_date', 'content', 'keywords')),
        ));
}
?>
