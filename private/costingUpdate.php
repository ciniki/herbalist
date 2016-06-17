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
function ciniki_herbalist_costingUpdate(&$ciniki, $business_id, $args) {

    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbHashQueryIDTree');
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'objectUpdate');
    ciniki_core_loadMethod($ciniki, 'ciniki', 'herbalist', 'private', 'costingUpdateIngredient');
    ciniki_core_loadMethod($ciniki, 'ciniki', 'herbalist', 'private', 'costingUpdateRecipe');
   
    //
    // Get the settings
    //
    $settings = array();
    $rc = ciniki_core_dbDetailsQueryDash($ciniki, 'ciniki_herbalist_settings', 'business_id', 
        $business_id, 'ciniki.herbalist', 'settings', '');
    if( $rc['stat'] == 'ok' && isset($rc['settings']) ) {
        $settings = $rc['settings'];
    }

    //
    // Load the production hourly wage
    //
    $hourly_wage = 0;
    if( isset($settings['production-hourly-wage']) && $settings['production-hourly-wage'] > 0 ) {
        $hourly_wage = $settings['production-hourly-wage'];
    }
    $minute_wage = 0;
    if( $hourly_wage > 0 ) {
        $minute_wage = bcdiv($hourly_wage, 60, 10);
    }

    //
    // Load all the recipes
    //
    $strsql = "SELECT ciniki_herbalist_recipes.id, "
        . "ciniki_herbalist_recipes.yield, "
        . "ciniki_herbalist_recipes.production_time, "
        . "ciniki_herbalist_recipes.materials_cost_per_unit, "
        . "ciniki_herbalist_recipes.time_cost_per_unit, "
        . "ciniki_herbalist_recipes.total_cost_per_unit, 'no' AS verified, "
        . "ciniki_herbalist_recipe_ingredients.ingredient_id, "
        . "ciniki_herbalist_recipe_ingredients.quantity "
        . "FROM ciniki_herbalist_recipes "
        . "LEFT JOIN ciniki_herbalist_recipe_ingredients ON ("
            . "ciniki_herbalist_recipes.id = ciniki_herbalist_recipe_ingredients.recipe_id "
            . "AND ciniki_herbalist_recipe_ingredients.business_id = '" . ciniki_core_dbQuote($ciniki, $business_id) . "' "
            . ") "
        . "WHERE ciniki_herbalist_recipes.business_id = '" . ciniki_core_dbQuote($ciniki, $business_id) . "' "
        . "";
    $rc = ciniki_core_dbHashQueryIDTree($ciniki, $strsql, 'ciniki.herbalist', array(
        array('container'=>'recipes', 'fname'=>'id', 'fields'=>array('id', 'yield', 'production_time', 
            'materials_cost_per_unit', 'time_cost_per_unit', 'total_cost_per_unit', 'verified')),
        array('container'=>'ingredients', 'fname'=>'ingredient_id', 'fields'=>array('ingredient_id', 'quantity')),
        ));
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    } 
    $recipes = (isset($rc['recipes']) ? $rc['recipes'] : array());

    //
    // Load all the ingredients
    //
    $strsql = "SELECT id, name, recipe_id, costing_quantity, costing_time, costing_price, materials_cost_per_unit, time_cost_per_unit, total_cost_per_unit, 'no' AS verified "
        . "FROM ciniki_herbalist_ingredients "
        . "WHERE business_id = '" . ciniki_core_dbQuote($ciniki, $business_id) . "' "
        . "";
    $rc = ciniki_core_dbHashQueryIDTree($ciniki, $strsql, 'ciniki.herbalist', array(
        array('container'=>'ingredients', 'fname'=>'id', 'fields'=>array('id', 'name', 'recipe_id', 'costing_quantity', 'costing_time', 'costing_price', 
            'materials_cost_per_unit', 'time_cost_per_unit', 'total_cost_per_unit', 'verified')),
        ));
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    } 
    $ingredients = (isset($rc['ingredients']) ? $rc['ingredients'] : array());

    //
    // Load all the containers
    //
    $strsql = "SELECT id, name, top_quantity, top_price, bottom_quantity, bottom_price, cost_per_unit, 'no' AS verified "
        . "FROM ciniki_herbalist_containers "
        . "WHERE business_id = '" . ciniki_core_dbQuote($ciniki, $business_id) . "' "
        . "";
    $rc = ciniki_core_dbHashQueryIDTree($ciniki, $strsql, 'ciniki.herbalist', array(
        array('container'=>'containers', 'fname'=>'id', 'fields'=>array('id', 'name', 'top_quantity', 'top_price', 'bottom_quantity', 'bottom_price', 'cost_per_unit', 'verified')),
        ));
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    } 
    $containers = (isset($rc['containers']) ? $rc['containers'] : array());

    //
    // Load all the products versions
    //
    $strsql = "SELECT id, name, recipe_id, recipe_quantity, container_id, materials_cost_per_container, time_cost_per_container, total_cost_per_container, 'no' AS verified "
        . "FROM ciniki_herbalist_product_versions "
        . "WHERE business_id = '" . ciniki_core_dbQuote($ciniki, $business_id) . "' "
        . "";
    $rc = ciniki_core_dbHashQueryIDTree($ciniki, $strsql, 'ciniki.herbalist', array(
        array('container'=>'products', 'fname'=>'id', 'fields'=>array('id', 'name', 'recipe_id', 'recipe_quantity', 'container_id',
            'materials_cost_per_container', 'time_cost_per_container', 'total_cost_per_container', 'verified')),
        ));
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    } 
    $products = (isset($rc['products']) ? $rc['products'] : array());

    //
    // Update cost_per_unit for each ingredient that doesn't come from a recipe
    //
    foreach($ingredients as $iid => $ingredient) {
        if( $ingredient['recipe_id'] == 0 && $ingredient['verified'] != 'yes' ) {
            $rc = ciniki_herbalist_costingUpdateIngredient($ciniki, $business_id, $iid, $recipes, $ingredients, $minute_wage);
            if( $rc['stat'] != 'ok' ) {
                return $rc;
            }
        }
    }

    //
    // Update each ingredient that is from another recipe
    //
    foreach($ingredients as $iid => $ingredient) {
        if( $ingredient['recipe_id'] > 0 && $ingredient['verified'] != 'yes' ) {
            $rc = ciniki_herbalist_costingUpdateIngredient($ciniki, $business_id, $iid, $recipes, $ingredients, $minute_wage);
            if( $rc['stat'] != 'ok' ) {
                return $rc;
            }
        }
    }

    //
    // Update the recipes
    //
    foreach($recipes as $rid => $recipe) {
        if( $recipe['verified'] == 'yes' ) {
            continue;
        }

        $rc = ciniki_herbalist_costingUpdateRecipe($ciniki, $business_id, $rid, $recipes, $ingredients, $minute_wage);
        if( $rc['stat'] != 'ok' ) {
            return $rc;
        }
    }

    //
    // Update the containers
    //
    foreach($containers as $cid => $container) {
        if( $container['verified'] == 'yes' ) {
            continue;
        }
        
        $top = 0;
        $bottom = 0;
        if( $container['top_price'] > 0 && $container['top_quantity'] > 0 ) {
            $top = bcdiv($container['top_price'], $container['top_quantity'], 10);
        }
        if( $container['bottom_price'] > 0 && $container['bottom_quantity'] > 0 ) {
            $bottom = bcdiv($container['bottom_price'], $container['bottom_quantity'], 10);
        }
        $cost_per_unit = bcadd($top, $bottom, 4);
        if( $cost_per_unit != $container['cost_per_unit'] ) {
            $rc = ciniki_core_objectUpdate($ciniki, $business_id, 'ciniki.herbalist.container', $container['id'], array('cost_per_unit'=>$cost_per_unit));
            if( $rc['stat'] != 'ok' && $rc['err']['code'] != '1344' ) {
                return $rc;
            }
            $containers[$cid]['updated'] = 'yes';
            $containers[$cid]['cost_per_unit'] = $cost_per_unit;
        }
        $containers[$cid]['verified'] = 'yes';
    }

    //
    // Update the products
    //
    foreach($products as $pid => $product) {
        if( $product['verified'] == 'yes' ) {
            continue;
        }

        $materials_cost_per_container = 0;
        $time_cost_per_container = 0;
        $total_cost_per_container = 0;
    
        $recipe_id = $product['recipe_id'];
        if( $recipe_id > 0 && isset($recipes[$recipe_id]) ) {
            $materials_cost_per_container = bcmul($product['recipe_quantity'], $recipes[$recipe_id]['materials_cost_per_unit'], 10);
            $time_cost_per_container = bcmul($product['recipe_quantity'], $recipes[$recipe_id]['time_cost_per_unit'], 10);
        }
        $container_id = $product['container_id'];
        if( $container_id > 0 && isset($containers[$container_id]) ) {
            $materials_cost_per_container = bcadd($materials_cost_per_container, $containers[$container_id]['cost_per_unit'], 10);
        }

        $total_cost_per_container = bcadd($materials_cost_per_container, $time_cost_per_container, 10);

        $update_args = array();
        if( $materials_cost_per_container != $product['materials_cost_per_container'] ) {
            $update_args['materials_cost_per_container'] = $materials_cost_per_container;
            $products[$pid]['materials_cost_per_container'] = $materials_cost_per_container;
        }
        if( $time_cost_per_container != $product['time_cost_per_container'] ) {
            $update_args['time_cost_per_container'] = $time_cost_per_container;
            $products[$pid]['time_cost_per_container'] = $time_cost_per_container;
        }
        if( $total_cost_per_container != $product['total_cost_per_container'] ) {
            $update_args['total_cost_per_container'] = $total_cost_per_container;
            $products[$pid]['total_cost_per_container'] = $total_cost_per_container;
        }
        if( count($update_args) > 0 ) {
            $rc = ciniki_core_objectUpdate($ciniki, $business_id, 'ciniki.herbalist.productversion', $product['id'], $update_args);
            if( $rc['stat'] != 'ok' && $rc['err']['code'] != '1344' ) {
                return $rc;
            }
            $products[$pid]['updated'] = 'yes';
        }
        $products[$pid]['verified'] = 'yes';
    }

    return array('stat'=>'ok');
}
?>
