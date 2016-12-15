<?php
//
// Description
// -----------
// This method searchs for a herbs for a business.
//
// Arguments
// ---------
// api_key:
// auth_token:
// business_id:        The ID of the business to get herb for.
// start_needle:       The search string to search for.
// limit:              The maximum number of entries to return.
//
// Returns
// -------
//
function ciniki_herbalist_herbSearch($ciniki) {
    //
    // Find all the required and optional arguments
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'prepareArgs');
    $rc = ciniki_core_prepareArgs($ciniki, 'no', array(
        'business_id'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Business'),
        'start_needle'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Search String'),
        'limit'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Limit'),
        ));
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    $args = $rc['args'];

    //
    // Check access to business_id as owner, or sys admin.
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'herbalist', 'private', 'checkAccess');
    $rc = ciniki_herbalist_checkAccess($ciniki, $args['business_id'], 'ciniki.herbalist.herbSearch');
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }

    $words = explode(' ', $args['start_needle']);
    $search_sql = '';
    foreach($words as $word) {  
        if( trim($word) == '' ) {
            continue;
        }
        $search_sql .= "AND ("
            . "keywords_index LIKE '" . ciniki_core_dbQuote($ciniki, $word) . "%' "
            . "OR keywords_index LIKE '% " . ciniki_core_dbQuote($ciniki, $word) . "%'"
            . ") ";
    }
    if( $search_sql == '' ) {
        return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.herbalist.78', 'msg'=>'Invalid search string'));
    }
    
    //
    // Get the list of herbs
    //
    $strsql = "SELECT ciniki_herbalist_herbs.id, "
        . "ciniki_herbalist_herbs.dry, "
        . "ciniki_herbalist_herbs.tincture, "
        . "ciniki_herbalist_herbs.latin_name, "
        . "ciniki_herbalist_herbs.common_name, "
        . "ciniki_herbalist_herbs.dose, "
        . "ciniki_herbalist_herbs.safety, "
        . "ciniki_herbalist_herbs.actions, "
        . "ciniki_herbalist_herbs.ailments, "
        . "ciniki_herbalist_herbs.energetics "
        . "FROM ciniki_herbalist_herbs "
        . "WHERE ciniki_herbalist_herbs.business_id = '" . ciniki_core_dbQuote($ciniki, $args['business_id']) . "' "
        . $search_sql
        . "";
    if( isset($args['limit']) && is_numeric($args['limit']) && $args['limit'] > 0 ) {
        $strsql .= "LIMIT " . ciniki_core_dbQuote($ciniki, $args['limit']) . " ";
    } else {
        $strsql .= "LIMIT 25 ";
    }
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbHashQueryArrayTree');
    $rc = ciniki_core_dbHashQueryArrayTree($ciniki, $strsql, 'ciniki.herbalist', array(
        array('container'=>'herbs', 'fname'=>'id', 
            'fields'=>array('id', 'dry', 'tincture', 'latin_name', 'common_name', 'dose', 'safety', 'actions', 'ailments', 'energetics')),
        ));
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    if( isset($rc['herbs']) ) {
        $herbs = $rc['herbs'];
        $herb_ids = array();
        foreach($herbs as $iid => $herb) {
            foreach($words as $word) {
                if( $word == '' ) { continue; }
                foreach($herb as $field => $value) {
                    $herbs[$iid][$field] = preg_replace("/([^a-zA-Z0-9])($word)/i", "$1<span class='highlight'>$2</span>", $herbs[$iid][$field]);
                    $herbs[$iid][$field] = preg_replace("/^($word)/i", "<span class='highlight'>$1</span>", $herbs[$iid][$field]);
                }
            }
            $herb_ids[] = $herb['id'];
        }
    } else {
        $herbs = array();
        $herb_ids = array();
    }

    return array('stat'=>'ok', 'herbs'=>$herbs, 'nplist'=>$herb_ids);
}
?>
