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
// <rsp stat='ok' />
//
function ciniki_herbalist_noteUpdate(&$ciniki) {
    //
    // Find all the required and optional arguments
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'prepareArgs');
    $rc = ciniki_core_prepareArgs($ciniki, 'no', array(
        'business_id'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Business'),
        'note_id'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Note'),
        'note_date'=>array('required'=>'no', 'blank'=>'no', 'type'=>'date', 'name'=>'Date'),
        'content'=>array('required'=>'no', 'blank'=>'no', 'name'=>'Content'),
        'ingredients'=>array('required'=>'no', 'blank'=>'yes', 'type'=>'idlist', 'name'=>'Ingredients'),
        'actions'=>array('required'=>'no', 'blank'=>'yes', 'type'=>'idlist', 'name'=>'Actions'),
        'ailments'=>array('required'=>'no', 'blank'=>'yes', 'type'=>'idlist', 'name'=>'Ailments'),
        'tags'=>array('required'=>'no', 'blank'=>'yes', 'type'=>'list', 'delimiter'=>'::', 'name'=>'Tags'),
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
    $rc = ciniki_herbalist_checkAccess($ciniki, $args['business_id'], 'ciniki.herbalist.noteUpdate');
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
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbHashQueryIDTree');
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'objectAdd');
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'objectDelete');
    $rc = ciniki_core_dbTransactionStart($ciniki, 'ciniki.herbalist');
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }

    //
    // Update the Note in the database
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'objectUpdate');
    $rc = ciniki_core_objectUpdate($ciniki, $args['business_id'], 'ciniki.herbalist.note', $args['note_id'], $args, 0x04);
    if( $rc['stat'] != 'ok' ) {
        ciniki_core_dbTransactionRollback($ciniki, 'ciniki.herbalist');
        return $rc;
    }

    // 
    // Load existing ingredients
    //
    $strsql = "SELECT id, uuid, object, object_id "
        . "FROM ciniki_herbalist_note_refs "
        . "WHERE ciniki_herbalist_note_refs.note_id = '" . ciniki_core_dbQuote($ciniki, $args['note_id']) . "' "
        . "AND ciniki_herbalist_note_refs.business_id = '" . ciniki_core_dbQuote($ciniki, $args['business_id']) . "' "
        . "ORDER BY object, object_id "
        . "";
    $rc = ciniki_core_dbHashQueryIDTree($ciniki, $strsql, 'ciniki.herbalist', array(
        array('container'=>'objects', 'fname'=>'object', 'fields'=>array('object')),
        array('container'=>'refs', 'fname'=>'object_id', 'fields'=>array('id', 'uuid', 'object_id')),
        ));
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    $objects = array(
        'ciniki.herbalist.ingredient'=>array('refs'=>array()),
        'ciniki.herbalist.action'=>array('refs'=>array()),
        'ciniki.herbalist.ailment'=>array('refs'=>array()),
    );
    if( isset($rc['objects']) ) {
        foreach($rc['objects'] as $obj => $refs) {
            $objects[$obj] = $refs;
        }
    }

    //
    // Update the ingredient refs
    //
    if( isset($args['ingredients']) ) {
        //
        // Check for additions
        //
        foreach($args['ingredients'] as $ingredient_id) {
            if( !isset($objects['ciniki.herbalist.ingredient']['refs'][$ingredient_id]) ) {
                $rc = ciniki_core_objectAdd($ciniki, $args['business_id'], 'ciniki.herbalist.noteref', array(
                    'note_id'=>$args['note_id'],
                    'object'=>'ciniki.herbalist.ingredient',
                    'object_id'=>$ingredient_id,
                    ), 0x04);
                if( $rc['stat'] != 'ok' ) {
                    ciniki_core_dbTransactionRollback($ciniki, 'ciniki.herbalist');
                    return $rc;
                }
            }
        }

        //
        // Check for deletions
        //
        foreach($objects['ciniki.herbalist.ingredient']['refs'] as $ref) {
            if( !in_array($ref['object_id'], $args['ingredients']) ) {
                $rc = ciniki_core_objectDelete($ciniki, $args['business_id'], 'ciniki.herbalist.noteref', $ref['id'], $ref['uuid'], 0x04);
                if( $rc['stat'] != 'ok' ) {
                    return $rc;
                }
            }
        }
    }

    //
    // Update the action refs
    //
    if( isset($args['actions']) ) {
        //
        // Check for additions
        //
        foreach($args['actions'] as $action_id) {
            if( !isset($objects['ciniki.herbalist.action']['refs'][$action_id]) ) {
                $rc = ciniki_core_objectAdd($ciniki, $args['business_id'], 'ciniki.herbalist.noteref', array(
                    'note_id'=>$args['note_id'],
                    'object'=>'ciniki.herbalist.action',
                    'object_id'=>$action_id,
                    ), 0x04);
                if( $rc['stat'] != 'ok' ) {
                    ciniki_core_dbTransactionRollback($ciniki, 'ciniki.herbalist');
                    return $rc;
                }
            }
        }

        //
        // Check for deletions
        //
        foreach($objects['ciniki.herbalist.action']['refs'] as $ref) {
            if( !in_array($ref['object_id'], $args['actions']) ) {
                $rc = ciniki_core_objectDelete($ciniki, $args['business_id'], 'ciniki.herbalist.noteref', $ref['id'], $ref['uuid'], 0x04);
                if( $rc['stat'] != 'ok' ) {
                    return $rc;
                }
            }
        }
    }

    //
    // Update the ailment refs
    //
    if( isset($args['ailments']) ) {
        //
        // Check for additions
        //
        foreach($args['ailments'] as $ailment_id) {
            if( !isset($objects['ciniki.herbalist.ailment']['refs'][$ailment_id]) ) {
                $rc = ciniki_core_objectAdd($ciniki, $args['business_id'], 'ciniki.herbalist.noteref', array(
                    'note_id'=>$args['note_id'],
                    'object'=>'ciniki.herbalist.ailment',
                    'object_id'=>$ailment_id,
                    ), 0x04);
                if( $rc['stat'] != 'ok' ) {
                    ciniki_core_dbTransailmentRollback($ciniki, 'ciniki.herbalist');
                    return $rc;
                }
            }
        }

        //
        // Check for deletions
        //
        foreach($objects['ciniki.herbalist.ailment']['refs'] as $ref) {
            if( !in_array($ref['object_id'], $args['ailments']) ) {
                $rc = ciniki_core_objectDelete($ciniki, $args['business_id'], 'ciniki.herbalist.noteref', $ref['id'], $ref['uuid'], 0x04);
                if( $rc['stat'] != 'ok' ) {
                    return $rc;
                }
            }
        }
    }

	//
	// Update the tags
	//
	if( isset($args['tags']) ) {
		ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'tagsUpdate');
		$rc = ciniki_core_tagsUpdate($ciniki, 'ciniki.herbalist', 'tag', $args['business_id'],
			'ciniki_herbalist_tags', 'ciniki_herbalist_history', 'ref_id', $args['note_id'], 60, $args['tags']);
		if( $rc['stat'] != 'ok' ) {
			ciniki_core_dbTransactionRollback($ciniki, 'ciniki.herbalist');
			return $rc;
		}
	}

    //
    // Commit the transaction
    //
    $rc = ciniki_core_dbTransactionCommit($ciniki, 'ciniki.herbalist');
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }

	//
	// Update the note keywords
	//
    ciniki_core_loadMethod($ciniki, 'ciniki', 'herbalist', 'private', 'notesUpdateKeywords');
    $rc = ciniki_herbalist_notesUpdateKeywords($ciniki, $args['business_id']); 
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
    ciniki_core_hookExec($ciniki, $args['business_id'], 'ciniki', 'web', 'indexObject', array('object'=>'ciniki.herbalist.note', 'object_id'=>$args['note_id']));

    return array('stat'=>'ok');
}
?>
