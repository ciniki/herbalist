<?php
//
// Description
// -----------
// This function will return a list of user interface settings for the module.
//
// Arguments
// ---------
// ciniki:
// tnid:        The ID of the tenant to get events for.
//
// Returns
// -------
//
function ciniki_herbalist_hooks_uiSettings($ciniki, $tnid, $args) {

    //
    // Set the default response
    //
    $rsp = array('stat'=>'ok', 'settings'=>array(), 'menu_items'=>array(), 'settings_menu_items'=>array());    

    //
    // Get the settings
    //
    $rc = ciniki_core_dbDetailsQueryDash($ciniki, 'ciniki_herbalist_settings', 'tnid', 
        $tnid, 'ciniki.herbalist', 'settings', '');
    if( $rc['stat'] == 'ok' && isset($rc['settings']) ) {
        $rsp['settings'] = $rc['settings'];
    }

    //
    // Check permissions for what menu items should be available
    //
    if( isset($ciniki['tenant']['modules']['ciniki.herbalist'])
        && (isset($args['permissions']['owners'])
            || isset($args['permissions']['employees'])
            || isset($args['permissions']['resellers'])
            || ($ciniki['session']['user']['perms']&0x01) == 0x01
            )
        ) {
        $menu_item = array(
            'priority'=>6500,
            'label'=>'Products', 
            'edit'=>array('app'=>'ciniki.herbalist.main'),
            );
        $rsp['menu_items'][] = $menu_item;
       
        if( ($ciniki['tenant']['modules']['ciniki.herbalist']['flags']&0x1000) == 0x1000 ) {
            $menu_item = array(
                'priority'=>6501,
                'label'=>'Herbs', 
                'edit'=>array('app'=>'ciniki.herbalist.main', 'args'=>array('menu'=>'\'"herbs"\'')),
                );
            $rsp['menu_items'][] = $menu_item;
        }
    } 

    if( isset($ciniki['tenant']['modules']['ciniki.herbalist'])
        && (isset($args['permissions']['owners'])
            || isset($args['permissions']['resellers'])
            || ($ciniki['session']['user']['perms']&0x01) == 0x01
            )
        ) {
        $rsp['settings_menu_items'][] = array('priority'=>6500, 'label'=>'Herbalist', 'edit'=>array('app'=>'ciniki.herbalist.settings'));
    }

    return $rsp;
}
?>
