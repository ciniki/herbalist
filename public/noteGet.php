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
// tnid:         The ID of the tenant the note is attached to.
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
        'tnid'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Tenant'),
        'note_id'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Note'),
        'reflists'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Reference Lists'),
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
    $rc = ciniki_herbalist_checkAccess($ciniki, $args['tnid'], 'ciniki.herbalist.noteGet');
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }

    //
    // Load tenant settings
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'tenants', 'private', 'intlSettings');
    $rc = ciniki_tenants_intlSettings($ciniki, $args['tnid']);
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    $intl_timezone = $rc['settings']['intl-default-timezone'];
    $intl_currency_fmt = numfmt_create($rc['settings']['intl-default-locale'], NumberFormatter::CURRENCY);
    $intl_currency = $rc['settings']['intl-default-currency'];

    ciniki_core_loadMethod($ciniki, 'ciniki', 'users', 'private', 'dateFormat');
    $php_date_format = ciniki_users_dateFormat($ciniki, 'php');
    $mysql_date_format = ciniki_users_dateFormat($ciniki, 'mysql');

    //
    // Return default for new Note
    //
    if( $args['note_id'] == 0 ) {
        $dt = new DateTime('now', new DateTimeZone($intl_timezone));
        $note = array('id'=>0,
            'note_date'=>$dt->format($php_date_format),
            'content'=>'',
        );
    }

    //
    // Get the details for an existing Note
    //
    else {
        $strsql = "SELECT ciniki_herbalist_notes.id, "
            . "DATE_FORMAT(ciniki_herbalist_notes.note_date, '" . ciniki_core_dbQuote($ciniki, $mysql_date_format) . "') AS note_date, "
            . "ciniki_herbalist_notes.content "
            . "FROM ciniki_herbalist_notes "
            . "WHERE ciniki_herbalist_notes.tnid = '" . ciniki_core_dbQuote($ciniki, $args['tnid']) . "' "
            . "AND ciniki_herbalist_notes.id = '" . ciniki_core_dbQuote($ciniki, $args['note_id']) . "' "
            . "";
        ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbHashQuery');
        $rc = ciniki_core_dbHashQuery($ciniki, $strsql, 'ciniki.herbalist', 'note');
        if( $rc['stat'] != 'ok' ) {
            return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.herbalist.23', 'msg'=>'Note not found', 'err'=>$rc['err']));
        }
        if( !isset($rc['note']) ) {
            return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.herbalist.24', 'msg'=>'Unable to find Note'));
        }
        $note = $rc['note'];

        //
        // Get the references to the note
        //
        $strsql = "SELECT object, object_id AS refs "
            . "FROM ciniki_herbalist_note_refs "
            . "WHERE ciniki_herbalist_note_refs.note_id = '" . ciniki_core_dbQuote($ciniki, $args['note_id']) . "' "
            . "AND ciniki_herbalist_note_refs.tnid = '" . ciniki_core_dbQuote($ciniki, $args['tnid']) . "' "
            . "ORDER BY object, object_id "
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
            } 
            if( isset($rc['objects']['ciniki.herbalist.action']['refs']) ) {
                $note['actions'] = $rc['objects']['ciniki.herbalist.action']['refs'];
            } 
            if( isset($rc['objects']['ciniki.herbalist.ailment']['refs']) ) {
                $note['ailments'] = $rc['objects']['ciniki.herbalist.ailment']['refs'];
            }
            if( isset($rc['objects']['ciniki.herbalist.recipe']['refs']) ) {
                $note['recipes'] = $rc['objects']['ciniki.herbalist.recipe']['refs'];
            }
            if( isset($rc['objects']['ciniki.herbalist.product']['refs']) ) {
                $note['products'] = $rc['objects']['ciniki.herbalist.product']['refs'];
            }
        }

        //
        // Get the tags
        //
        $note['tags'] = array();
        $strsql = "SELECT tag_type, tag_name AS lists "
            . "FROM ciniki_herbalist_tags "
            . "WHERE ref_id = '" . ciniki_core_dbQuote($ciniki, $args['note_id']) . "' "
            . "AND tnid = '" . ciniki_core_dbQuote($ciniki, $args['tnid']) . "' "
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
            . "WHERE ciniki_herbalist_ingredients.tnid = '" . ciniki_core_dbQuote($ciniki, $args['tnid']) . "' "
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

        //
        // Get the list of actions
        //
        $strsql = "SELECT ciniki_herbalist_actions.id, "
            . "ciniki_herbalist_actions.name "
            . "FROM ciniki_herbalist_actions "
            . "WHERE ciniki_herbalist_actions.tnid = '" . ciniki_core_dbQuote($ciniki, $args['tnid']) . "' "
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
            . "WHERE ciniki_herbalist_ailments.tnid = '" . ciniki_core_dbQuote($ciniki, $args['tnid']) . "' "
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

        //
        // Get the list of recipes
        //
        $strsql = "SELECT ciniki_herbalist_recipes.id, "
            . "ciniki_herbalist_recipes.name "
            . "FROM ciniki_herbalist_recipes "
            . "WHERE ciniki_herbalist_recipes.tnid = '" . ciniki_core_dbQuote($ciniki, $args['tnid']) . "' "
            . "ORDER BY ciniki_herbalist_recipes.name "
            . "";
        $rc = ciniki_core_dbHashQueryArrayTree($ciniki, $strsql, 'ciniki.herbalist', array(
            array('container'=>'recipes', 'fname'=>'id', 'fields'=>array('id', 'name')),
        ));
        if( $rc['stat'] != 'ok' ) {
            return $rc;
        }
        if( isset($rc['recipes']) ) {
            $rsp['recipes'] = $rc['recipes'];
        } else {
            $rsp['recipes'] = array();
        }

        //
        // Get the list of products
        //
        $strsql = "SELECT ciniki_herbalist_products.id, "
            . "ciniki_herbalist_products.name "
            . "FROM ciniki_herbalist_products "
            . "WHERE ciniki_herbalist_products.tnid = '" . ciniki_core_dbQuote($ciniki, $args['tnid']) . "' "
            . "ORDER BY ciniki_herbalist_products.name "
            . "";
        $rc = ciniki_core_dbHashQueryArrayTree($ciniki, $strsql, 'ciniki.herbalist', array(
            array('container'=>'products', 'fname'=>'id', 'fields'=>array('id', 'name')),
        ));
        if( $rc['stat'] != 'ok' ) {
            return $rc;
        }
        if( isset($rc['products']) ) {
            $rsp['products'] = $rc['products'];
        } else {
            $rsp['products'] = array();
        }

        //
        // Get the categories
        //
        ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbQueryList');
        $strsql = "SELECT DISTINCT tag_name FROM ciniki_herbalist_tags WHERE tag_type = 60 AND tnid = '" . ciniki_core_dbQuote($ciniki, $args['tnid']) . "' ";
        $rc = ciniki_core_dbQueryList($ciniki, $strsql, 'ciniki.herbalist', 'tags', 'tag_name');
        if( $rc['stat'] != 'ok' ) {
            return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.herbalist.25', 'msg'=>'Unable to get list of categories', 'err'=>$rc['err']));
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
