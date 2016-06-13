<?php
//
// Description
// -----------
// This function will redo the costing numbers for each recipe and product.
//
// Arguments
// ---------
// ciniki:
// business_id:                 The business ID to check the session user against.
// method:                      The requested method.
//
// Returns
// -------
// <rsp stat='ok' />
//
function ciniki_herbalist_costingUpdateRecipe(&$ciniki, $business_id, $recipe_id, &$recipes, &$ingredients, $minute_wage) {

    if( !isset($recipes[$recipe_id]) ) {
        return array('stat'=>'fail', 'err'=>array('pkg'=>'ciniki', 'code'=>'3449', 'msg'=>'Recipe does not exist'));
    }
    $recipe = $recipes[$recipe_id];

    //
    // Go through the list of ingredients and update the costs
    //
    if( isset($recipe['ingredients']) ) {
        $materials_cost = 0;
        $time_cost = 0;
        foreach($recipe['ingredients'] as $iid => $ingredient) {
            if( !isset($ingredients[$iid]) ) {
                return array('stat'=>'fail', 'err'=>array('pkg'=>'ciniki', 'code'=>'3468', 'msg'=>'Recipe ingredient does not exist'));
            }
            $ingredient = $ingredients[$iid];
            //
            // If the ingredient is not verified, then it needs to be updated
            //
            if( $ingredient['verified'] != 'yes' ) {
                $rc = ciniki_herbalist_costingUpdateIngredient($ciniki, $business_id, $iid, $recipes, $ingredients, $minute_wage);
                if( $rc['stat'] != 'ok' ) {
                    return $rc;
                }
            }
            
            $materials_cost = bcadd($materials_cost, bcmul($ingredients[$iid]['materials_cost_per_unit'], $recipe['ingredients'][$iid]['quantity'], 10), 10);
            $time_cost = bcadd($time_cost, bcmul($ingredients[$iid]['time_cost_per_unit'], $recipe['ingredients'][$iid]['quantity'], 10), 10);
        }

        //
        // Add the time taken to produce the recipe
        //
        if( $recipe['production_time'] > 0 ) {
            $time_cost = bcadd($time_cost, bcmul($recipe['production_time'], $minute_wage, 10), 10);
        }
    
        if( $materials_cost > 0 && $recipe['yield'] > 0 ) {
            $materials_cost_per_unit = bcdiv($materials_cost, $recipe['yield'], 10);
        } else {
            $materials_cost_per_unit = 0;
        }
        if( $time_cost > 0 && $recipe['yield'] > 0 ) {
            $time_cost_per_unit = bcdiv($time_cost, $recipe['yield'], 10);
        } else {
            $time_cost_per_unit = 0;
        }
        $total_cost_per_unit = bcadd($materials_cost_per_unit, $time_cost_per_unit, 10);
        $update_args = array();
        if( $materials_cost_per_unit != $recipe['materials_cost_per_unit'] ) {
            $update_args['materials_cost_per_unit'] = $materials_cost_per_unit;
        }
        if( $time_cost_per_unit != $recipe['time_cost_per_unit'] ) {
            $update_args['time_cost_per_unit'] = $time_cost_per_unit;
        }
        if( $total_cost_per_unit != $recipe['total_cost_per_unit'] ) {
            $update_args['total_cost_per_unit'] = $total_cost_per_unit;
        }
        if( count($update_args) > 0 ) {
            $rc = ciniki_core_objectUpdate($ciniki, $business_id, 'ciniki.herbalist.recipe', $recipe_id, $update_args);
            if( $rc['stat'] != 'ok' && $rc['err']['code'] != '1344' ) {
                return $rc;
            }
            $recipes[$recipe_id]['updated'] = 'yes';
        }
        $recipes[$recipe_id]['verified'] = 'yes';
    }

    return array('stat'=>'ok');
}
?>
