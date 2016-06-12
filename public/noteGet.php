<?php
//
// Description
// ===========
// This method will return all the information about an note.
//
// Arguments
// ---------
// api_key:
// auth_token:
// business_id:         The ID of the business the note is attached to.
// note_id:          The ID of the note to get the details for.
//
// Returns
// -------
//
function ciniki_herbalist_noteGet($ciniki) {
    //
    // Find all the required and optional arguments
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'prepareArgs');
    $rc = ciniki_core_prepareArgs($ciniki, 'no', array(
        'business_id'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Business'),
        'note_id'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Note'),
        'reflists'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Reference Lists'),
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
    $rc = ciniki_herbalist_checkAccess($ciniki, $args['business_id'], 'ciniki.herbalist.noteGet');
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }

    //
    // Load business settings
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'businesses', 'private', 'intlSettings');
    $rc = ciniki_businesses_intlSettings($ciniki, $args['business_id']);
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    $intl_timezone = $rc['settings']['intl-default-timezone'];
    $intl_currency_fmt = numfmt_create($rc['settings']['intl-default-locale'], NumberFormatter::CURRENCY);
    $intl_currency = $rc['settings']['intl-default-currency'];

    ciniki_core_loadMethod($ciniki, 'ciniki', 'users', 'private', 'datetimeFormat');
    $datetime_format = ciniki_users_datetimeFormat($ciniki, 'php');

    //
    // Return default for new Note
    //
    if( $args['note_id'] == 0 ) {
        $note = array('id'=>0,
            'note_date'=>'',
            'content'=>'',
        );
    }

    //
    // Get the details for an existing Note
    //
    else {
        $strsql = "SELECT ciniki_herbalist_notes.id, "
            . "ciniki_herbalist_notes.note_date, "
            . "ciniki_herbalist_notes.content "
            . "FROM ciniki_herbalist_notes "
            . "WHERE ciniki_herbalist_notes.business_id = '" . ciniki_core_dbQuote($ciniki, $args['business_id']) . "' "
            . "AND ciniki_herbalist_notes.id = '" . ciniki_core_dbQuote($ciniki, $args['note_id']) . "' "
            . "";
        ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbHashQuery');
        $rc = ciniki_core_dbHashQuery($ciniki, $strsql, 'ciniki.herbalist', 'note');
        if( $rc['stat'] != 'ok' ) {
            return array('stat'=>'fail', 'err'=>array('pkg'=>'ciniki', 'code'=>'3516', 'msg'=>'Note not found', 'err'=>$rc['err']));
        }
        if( !isset($rc['note']) ) {
            return array('stat'=>'fail', 'err'=>array('pkg'=>'ciniki', 'code'=>'3517', 'msg'=>'Unable to find Note'));
        }
        $note = $rc['note'];

        //
        // Get the references to the note
        //
        $strsql = "SELECT object, object_id AS refs "
            . "FROM ciniki_herbalist_note_refs "
            . "WHERE ciniki_herbalist_note_refs.note_id = '" . ciniki_core_dbQuote($ciniki, $args['note_id']) . "' "
            . "AND ciniki_herbalist_note_refs.business_id = '" . ciniki_core_dbQuote($ciniki, $args['business_id']) . "' "
            . "";
        ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbHashQueryIDTree');
        $rc = ciniki_core_dbHashQueryIDTree($ciniki, $strsql, 'ciniki.herbalist', array(
            array('container'=>'objects', 'fname'=>'object', 'fields'=>array('refs'), 'dlists'=>array('refs'=>',')),
            ));
        if( $rc['stat'] != 'ok' ) {
            return $rc;
        }
        $note['ingredients'] = '';
        $note['actions'] = '';
        $note['ailments'] = '';
        if( isset($rc['objects']) ) {
            if( isset($rc['objects']['ciniki.herbalist.ingredient']['refs']) ) {
                $note['ingredients'] = $rc['objects']['ciniki.herbalist.ingredient']['refs'];
            } elseif( isset($rc['objects']['ciniki.herbalist.action']['refs']) ) {
                $note['actions'] = $rc['objects']['ciniki.herbalist.action']['refs'];
            } elseif( isset($rc['objects']['ciniki.herbalist.ailment']['refs']) ) {
                $note['ailments'] = $rc['objects']['ciniki.herbalist.ailment']['refs'];
            }
        }

        //
        // Get the tags
        //
        $note['tags'] = array();
        $strsql = "SELECT tag_type, tag_name AS lists "
            . "FROM ciniki_herbalist_tags "
            . "WHERE ref_id = '" . ciniki_core_dbQuote($ciniki, $args['note_id']) . "' "
            . "AND business_id = '" . ciniki_core_dbQuote($ciniki, $args['business_id']) . "' "
            . "AND tag_type = 60 "
            . "ORDER BY tag_type, tag_name "
            . "";
        ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbHashQueryTree');
        $rc = ciniki_core_dbHashQueryTree($ciniki, $strsql, 'ciniki.herbalist', array(
            array('container'=>'tags', 'fname'=>'tag_type', 'name'=>'tags',
                'fields'=>array('tag_type', 'lists'), 'dlists'=>array('lists'=>'::')),
            ));
        if( $rc['stat'] != 'ok' ) {
            return $rc;
        }
        if( isset($rc['tags']) ) {
            foreach($rc['tags'] as $tags) {
                if( $tags['tags']['tag_type'] == 60 ) {
                    $note['tags'] = $tags['tags']['lists'];
                }
            }
        }
    }

    $rsp = array('stat'=>'ok', 'note'=>$note);

    if( isset($args['reflists']) && $args['reflists'] == 'yes' ) {
        //
        // Get the list of ingredients
        //
		$strsql = "SELECT ciniki_herbalist_ingredients.id, "
			. "ciniki_herbalist_ingredients.name "
			. "FROM ciniki_herbalist_ingredients "
			. "WHERE ciniki_herbalist_ingredients.business_id = '" . ciniki_core_dbQuote($ciniki, $args['business_id']) . "' "
			. "ORDER BY ciniki_herbalist_ingredients.name "
			. "";
        ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbHashQueryArrayTree');
		$rc = ciniki_core_dbHashQueryArrayTree($ciniki, $strsql, 'ciniki.herbalist', array(
			array('container'=>'ingredients', 'fname'=>'id', 'fields'=>array('id', 'name')),
		));
		if( $rc['stat'] != 'ok' ) {
			return $rc;
		}
		if( isset($rc['ingredients']) ) {
			$rsp['ingredients'] = $rc['ingredients'];
		} else {
            $rsp['ingredients'] = array();
        }
/*
        //
        // Get the list of actions
        //
		$strsql = "SELECT ciniki_herbalist_actions.id, "
			. "ciniki_herbalist_actions.name "
			. "FROM ciniki_herbalist_actions "
			. "WHERE ciniki_herbalist_actions.business_id = '" . ciniki_core_dbQuote($ciniki, $args['business_id']) . "' "
			. "ORDER BY ciniki_herbalist_actions.name "
			. "";
		$rc = ciniki_core_dbHashQueryArrayTree($ciniki, $strsql, 'ciniki.herbalist', array(
			array('container'=>'actions', 'fname'=>'id', 'fields'=>array('id', 'name')),
		));
		if( $rc['stat'] != 'ok' ) {
			return $rc;
		}
		if( isset($rc['actions']) ) {
			$rsp['actions'] = $rc['actions'];
		} else {
            $rsp['actions'] = array();
        }

        //
        // Get the list of ailments
        //
		$strsql = "SELECT ciniki_herbalist_ailments.id, "
			. "ciniki_herbalist_ailments.name "
			. "FROM ciniki_herbalist_ailments "
			. "WHERE ciniki_herbalist_ailments.business_id = '" . ciniki_core_dbQuote($ciniki, $args['business_id']) . "' "
			. "ORDER BY ciniki_herbalist_ailments.name "
			. "";
		$rc = ciniki_core_dbHashQueryArrayTree($ciniki, $strsql, 'ciniki.herbalist', array(
			array('container'=>'ailments', 'fname'=>'id', 'fields'=>array('id', 'name')),
		));
		if( $rc['stat'] != 'ok' ) {
			return $rc;
		}
		if( isset($rc['ailments']) ) {
			$rsp['ailments'] = $rc['ailments'];
		} else {
            $rsp['ailments'] = array();
        }
*/
        //
        // Get the categories
        //
        ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbQueryList');
        $strsql = "SELECT DISTINCT tag_name FROM ciniki_herbalist_tags WHERE tag_type = 60 AND business_id = '" . ciniki_core_dbQuote($ciniki, $args['business_id']) . "' ";
        $rc = ciniki_core_dbQueryList($ciniki, $strsql, 'ciniki.herbalist', 'tags', 'tag_name');
        if( $rc['stat'] != 'ok' ) {
            return array('stat'=>'fail', 'err'=>array('pkg'=>'ciniki', 'code'=>'3471', 'msg'=>'Unable to get list of categories', 'err'=>$rc['err']));
        }
        if( isset($rc['tags']) ) {
            $rsp['tags'] = $rc['tags'];
        } else {
            $rsp['tags'] = array();
        }
    }

    return $rsp;
}
?>
