<?php
//
// Description
// -----------
// This method will add a new note for the tenant.
//
// Arguments
// ---------
// api_key:
// auth_token:
// tnid:        The ID of the tenant to add the Note to.
//
// Returns
// -------
// <rsp stat="ok" id="42">
//
function ciniki_herbalist_noteAdd(&$ciniki) {
    //
    // Find all the required and optional arguments
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'prepareArgs');
    $rc = ciniki_core_prepareArgs($ciniki, 'no', array(
        'tnid'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Tenant'),
        'note_date'=>array('required'=>'yes', 'blank'=>'no', 'type'=>'date', 'name'=>'Date'),
        'content'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Content'),
        'ingredients'=>array('required'=>'no', 'blank'=>'yes', 'type'=>'idlist', 'name'=>'Ingredients'),
        'actions'=>array('required'=>'no', 'blank'=>'yes', 'type'=>'idlist', 'name'=>'Actions'),
        'ailments'=>array('required'=>'no', 'blank'=>'yes', 'type'=>'idlist', 'name'=>'Ailments'),
        'recipes'=>array('required'=>'no', 'blank'=>'yes', 'type'=>'idlist', 'name'=>'Recipes'),
        'products'=>array('required'=>'no', 'blank'=>'yes', 'type'=>'idlist', 'name'=>'Products'),
        'tags'=>array('required'=>'no', 'blank'=>'yes', 'type'=>'list', 'delimiter'=>'::', 'name'=>'Tags'),
        ));
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    $args = $rc['args'];

    //
    // Check access to tnid as owner
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'herbalist', 'private', 'checkAccess');
    $rc = ciniki_herbalist_checkAccess($ciniki, $args['tnid'], 'ciniki.herbalist.noteAdd');
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
    // Add the note to the database
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'objectAdd');
    $rc = ciniki_core_objectAdd($ciniki, $args['tnid'], 'ciniki.herbalist.note', $args, 0x04);
    if( $rc['stat'] != 'ok' ) {
        ciniki_core_dbTransactionRollback($ciniki, 'ciniki.herbalist');
        return $rc;
    }
    $note_id = $rc['id'];
    
    //
    // Add any ingredient references
    //
    if( isset($args['ingredients']) ) {
        foreach($args['ingredients'] as $ingredient_id) {
            $rc = ciniki_core_objectAdd($ciniki, $args['tnid'], 'ciniki.herbalist.noteref', array(
                'note_id'=>$note_id,
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
    // Add any action references
    //
    if( isset($args['actions']) ) {
        foreach($args['actions'] as $action_id) {
            $rc = ciniki_core_objectAdd($ciniki, $args['tnid'], 'ciniki.herbalist.noteref', array(
                'note_id'=>$note_id,
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
    // Add any ailment references
    //
    if( isset($args['ailments']) ) {
        foreach($args['ailments'] as $ailment_id) {
            $rc = ciniki_core_objectAdd($ciniki, $args['tnid'], 'ciniki.herbalist.noteref', array(
                'note_id'=>$note_id,
                'object'=>'ciniki.herbalist.ailment',
                'object_id'=>$ailment_id,
                ), 0x04);
            if( $rc['stat'] != 'ok' ) {
                ciniki_core_dbTransactionRollback($ciniki, 'ciniki.herbalist');
                return $rc;
            }
        }
    }

    //
    // Add any recipe references
    //
    if( isset($args['recipes']) ) {
        foreach($args['recipes'] as $recipe_id) {
            $rc = ciniki_core_objectAdd($ciniki, $args['tnid'], 'ciniki.herbalist.noteref', array(
                'note_id'=>$note_id,
                'object'=>'ciniki.herbalist.recipe',
                'object_id'=>$recipe_id,
                ), 0x04);
            if( $rc['stat'] != 'ok' ) {
                ciniki_core_dbTransactionRollback($ciniki, 'ciniki.herbalist');
                return $rc;
            }
        }
    }

    //
    // Add any product references
    //
    if( isset($args['products']) ) {
        foreach($args['products'] as $product_id) {
            $rc = ciniki_core_objectAdd($ciniki, $args['tnid'], 'ciniki.herbalist.noteref', array(
                'note_id'=>$note_id,
                'object'=>'ciniki.herbalist.product',
                'object_id'=>$product_id,
                ), 0x04);
            if( $rc['stat'] != 'ok' ) {
                ciniki_core_dbTransactionRollback($ciniki, 'ciniki.herbalist');
                return $rc;
            }
        }
    }

    //
    // Update the categories
    //
    if( isset($args['tags']) ) {
        ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'tagsUpdate');
        $rc = ciniki_core_tagsUpdate($ciniki, 'ciniki.herbalist', 'tag', $args['tnid'],
            'ciniki_herbalist_tags', 'ciniki_herbalist_history', 'ref_id', $note_id, 60, $args['tags']);
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
    $rc = ciniki_herbalist_notesUpdateKeywords($ciniki, $args['tnid']); 
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
    ciniki_core_hookExec($ciniki, $args['tnid'], 'ciniki', 'web', 'indexObject', array('object'=>'ciniki.herbalist.note', 'object_id'=>$note_id));

    return array('stat'=>'ok', 'id'=>$note_id);
}
?>
