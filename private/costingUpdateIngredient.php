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
function ciniki_herbalist_costingUpdateIngredient(&$ciniki, $business_id, $ingredient_id, &$recipes, &$ingredients, $minute_wage) {

    if( !isset($ingredients[$ingredient_id]) ) {
        return array('stat'=>'fail', 'err'=>array('pkg'=>'ciniki', 'code'=>'3451', 'msg'=>'Ingredient does not exist'));
    }
    $ingredient = $ingredients[$ingredient_id];
    error_log('update ingredient ' . $ingredient_id);

    //
    // Check if ingredient is a recipe
    //
    if( $ingredient['recipe_id'] > 0 ) {
        $recipe_id = $ingredient['recipe_id'];
        if( !isset($recipes[$recipe_id]) ) {
            return array('stat'=>'fail', 'err'=>array('pkg'=>'ciniki', 'code'=>'3448', 'msg'=>'Ingredient recipe does not exist for ' . $ingredient['name']));
        }
        
        //
        // Update the recipe if not already updated
        //
        if( $recipes[$recipe_id]['verified'] != 'yes' ) {
            $rc = ciniki_herbalist_costingUpdateRecipe($ciniki, $business_id, $recipe_id, $recipes, $ingredients, $minute_wage);
            if( $rc['stat'] != 'ok' ) {
                return $rc;
            }
        }
       
        $materials_cost_per_unit = $recipes[$recipe_id]['materials_cost_per_unit'];
        $time_cost_per_unit = $recipes[$recipe_id]['time_cost_per_unit'];
        $total_cost_per_unit = $recipes[$recipe_id]['total_cost_per_unit'];
    } else {
        $materials_cost_per_unit = 0;
        $time_cost_per_unit = 0;
        $total_cost_per_unit = 0;
        if( $ingredient['costing_price'] > 0 && $ingredient['costing_quantity'] > 0 ) {
            $materials_cost_per_unit = bcdiv($ingredient['costing_price'], $ingredient['costing_quantity'], 4);
        }
        if( $ingredient['costing_time'] > 0 && $ingredient['costing_quantity'] > 0 && $minute_wage > 0 ) {
            $time_cost_per_unit = bcdiv(bcmul($ingredient['costing_time'], $minute_wage, 4), $ingredient['costing_quantity'], 4);
        }
        $total_cost_per_unit = bcadd($materials_cost_per_unit, $time_cost_per_unit, 4);
    }
    
    //
    // Check if anything needs updating
    //
    $update_args = array();
    if( $materials_cost_per_unit != $ingredient['materials_cost_per_unit'] ) {
        $update_args['materials_cost_per_unit'] = $materials_cost_per_unit;
        $ingredients[$ingredient_id]['materials_cost_per_unit'] = $materials_cost_per_unit;
    }
    if( $time_cost_per_unit != $ingredient['time_cost_per_unit'] ) {
        $update_args['time_cost_per_unit'] = $time_cost_per_unit;
        $ingredients[$ingredient_id]['time_cost_per_unit'] = $time_cost_per_unit;
    }
    if( $total_cost_per_unit != $ingredient['total_cost_per_unit'] ) {
        $update_args['total_cost_per_unit'] = $total_cost_per_unit;
        $ingredients[$ingredient_id]['total_cost_per_unit'] = $total_cost_per_unit;
    }
    if( count($update_args) > 0 ) {
        error_log('updating ingredient');
        $rc = ciniki_core_objectUpdate($ciniki, $business_id, 'ciniki.herbalist.ingredient', $ingredient['id'], $update_args);
        if( $rc['stat'] != 'ok' && $rc['err']['code'] != '1344' ) {
            return $rc;
        }
        $ingredients[$ingredient_id]['updated'] = 'yes';
    }
    $ingredients[$ingredient_id]['verified'] = 'yes';

    return array('stat'=>'ok');
}
?>
