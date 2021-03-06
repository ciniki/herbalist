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
function ciniki_herbalist_notesUpdateKeywords($ciniki, $tnid) {
    
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbHashQueryIDTree');
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbQueryList2');
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'objectUpdate');

    //
    // Get the list of notes
    //
    $strsql = "SELECT ciniki_herbalist_notes.id, "
        . "ciniki_herbalist_notes.content, "
        . "ciniki_herbalist_notes.keywords, "
        . "ciniki_herbalist_notes.keywords_index, "
        . "ciniki_herbalist_note_refs.object, "
        . "ciniki_herbalist_note_refs.object_id "
        . "FROM ciniki_herbalist_notes "
        . "LEFT JOIN ciniki_herbalist_note_refs ON ("
            . "ciniki_herbalist_notes.id = ciniki_herbalist_note_refs.note_id "
            . "AND ciniki_herbalist_note_refs.tnid = '" . ciniki_core_dbQuote($ciniki, $tnid) . "' "
            . ") "
        . "WHERE ciniki_herbalist_notes.tnid = '" . ciniki_core_dbQuote($ciniki, $tnid) . "' "
        . "ORDER BY ciniki_herbalist_notes.id, object, object_id "
        . "";
    $rc = ciniki_core_dbHashQueryIDTree($ciniki, $strsql, 'ciniki.herbalist', array(
        array('container'=>'notes', 'fname'=>'id', 'fields'=>array('id', 'content', 'keywords', 'keywords_index')),
        array('container'=>'objects', 'fname'=>'object', 'fields'=>array('object')),
        array('container'=>'refs', 'fname'=>'object_id', 'fields'=>array('object', 'object_id')),
        ));
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    if( !isset($rc['notes']) || count($rc['notes']) == 0 ) {
        return array('stat'=>'ok');
    }
    $notes = $rc['notes'];

    //
    // Load the tags
    //
    $strsql = "SELECT ref_id AS note_id, tag_name "
        . "FROM ciniki_herbalist_tags "
        . "WHERE tnid = '" . ciniki_core_dbQuote($ciniki, $tnid) . "' "
        . "AND tag_type = 60 "
        . "ORDER BY ref_id "
        . "";
    $rc = ciniki_core_dbHashQueryIDTree($ciniki, $strsql, 'ciniki.herbalist', array(
        array('container'=>'notes', 'fname'=>'note_id', 'fields'=>array()),
        array('container'=>'tags', 'fname'=>'tag_name', 'fields'=>array('tag_name')),
        ));
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    $tags = isset($rc['notes']) ? $rc['notes'] : array();

    //
    // Get the list of ingredients
    //
    $strsql = "SELECT id, name "
        . "FROM ciniki_herbalist_ingredients "
        . "WHERE tnid = '" . ciniki_core_dbQuote($ciniki, $tnid) . "' "
        . "ORDER BY id "
        . "";
    $rc = ciniki_core_dbQueryList2($ciniki, $strsql, 'ciniki.herbalist', 'ingredients');
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    $ingredients = isset($rc['ingredients']) ? $rc['ingredients'] : array();
   
    //
    // Get the list of actions
    //
    $strsql = "SELECT id, name "
        . "FROM ciniki_herbalist_actions "
        . "WHERE tnid = '" . ciniki_core_dbQuote($ciniki, $tnid) . "' "
        . "ORDER BY id "
        . "";
    $rc = ciniki_core_dbQueryList2($ciniki, $strsql, 'ciniki.herbalist', 'actions');
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    $actions = isset($rc['actions']) ? $rc['actions'] : array();
   
    //
    // Get the list of ailments
    //
    $strsql = "SELECT id, name "
        . "FROM ciniki_herbalist_ailments "
        . "WHERE tnid = '" . ciniki_core_dbQuote($ciniki, $tnid) . "' "
        . "ORDER BY id "
        . "";
    $rc = ciniki_core_dbQueryList2($ciniki, $strsql, 'ciniki.herbalist', 'ailments');
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    $ailments = isset($rc['ailments']) ? $rc['ailments'] : array();

    //
    // Get the list of recipes
    //
    $strsql = "SELECT id, name "
        . "FROM ciniki_herbalist_recipes "
        . "WHERE tnid = '" . ciniki_core_dbQuote($ciniki, $tnid) . "' "
        . "ORDER BY id "
        . "";
    $rc = ciniki_core_dbQueryList2($ciniki, $strsql, 'ciniki.herbalist', 'recipes');
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    $recipes = isset($rc['recipes']) ? $rc['recipes'] : array();

    //
    // Get the list of products
    //
    $strsql = "SELECT id, name "
        . "FROM ciniki_herbalist_products "
        . "WHERE tnid = '" . ciniki_core_dbQuote($ciniki, $tnid) . "' "
        . "ORDER BY id "
        . "";
    $rc = ciniki_core_dbQueryList2($ciniki, $strsql, 'ciniki.herbalist', 'products');
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    $products = isset($rc['products']) ? $rc['products'] : array();

    //
    // Build the keyword arrays
    //
    foreach($notes as $nid => $note) {
        $keywords = array();
        //
        // Add the keywords from ingredients, actions and ailments
        //
        if( isset($note['objects']) ) {
            foreach($note['objects'] as $object => $o) {
                foreach($o['refs'] as $ref_id => $ref) {
                    if( $object == 'ciniki.herbalist.ingredient' ) {
                        if( isset($ingredients[$ref_id]) ) {
                            $keywords[] = $ingredients[$ref_id];
                        }
                    } elseif( $object == 'ciniki.herbalist.action' ) {
                        if( isset($actions[$ref_id]) ) {
                            $keywords[] = $actions[$ref_id];
                        }
                    } elseif( $object == 'ciniki.herbalist.ailment' ) {
                        if( isset($ailments[$ref_id]) ) {
                            $keywords[] = $ailments[$ref_id];
                        }
                    } elseif( $object == 'ciniki.herbalist.recipe' ) {
                        if( isset($recipes[$ref_id]) ) {
                            $keywords[] = $recipes[$ref_id];
                        }
                    } elseif( $object == 'ciniki.herbalist.product' ) {
                        if( isset($products[$ref_id]) ) {
                            $keywords[] = $products[$ref_id];
                        }
                    }
                }
            }
        }

        if( isset($tags[$nid]['tags']) ) {
            foreach($tags[$nid]['tags'] as $tag_name => $tag) {
                $keywords[] = $tag_name;
            }
        }

        //
        // sort the keywords and remove duplicates, build the string used for display
        //
        sort($keywords);
        $keywords = array_unique($keywords);
        $keywords_string = implode(", ", $keywords);
        //
        // Build the string that will be used for searching
        //
        $keywords = explode(' ', preg_replace("/, /", " ", $keywords_string));
        sort($keywords);
        $keywords_index = implode(' ', array_unique($keywords));
        
        $update_args = array();
        if( $keywords_string != $note['keywords'] ) {
            $update_args['keywords'] = $keywords_string;
        }
        if( $keywords_index != $note['keywords_index'] ) {
            $update_args['keywords_index'] = $keywords_index;
        }
        if( count($update_args) > 0 ) {
            $rc = ciniki_core_objectUpdate($ciniki, $tnid, 'ciniki.herbalist.note', $note['id'], $update_args, 0x07);
            if( $rc['stat'] != 'ok' ) {
                return $rc;
            }
        }
    }
    
    return array('stat'=>'ok');
}
?>
