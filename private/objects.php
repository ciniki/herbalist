<?php
//
// Description
// -----------
//
// Arguments
// ---------
//
// Returns
// -------
//
function ciniki_herbalist_objects($ciniki) {
	
	$objects = array();
	$objects['ingredient'] = array(
		'name'=>'Ingredient',
        'o_name'=>'ingredient',
        'o_container'=>'ingredients',
		'sync'=>'yes',
		'table'=>'ciniki_herbalist_ingredients',
		'fields'=>array(
			'name'=>array('name'=>'Name'),
			'sorttype'=>array('name'=>'Type'),
            'plant_id'=>array('name'=>'Plant', 'ref'=>'ciniki.herbalist.plant', 'default'=>'0'),
            'recipe_id'=>array('name'=>'Recipe', 'ref'=>'ciniki.herbalist.recipe', 'default'=>'0'),
            'units'=>array('name'=>'Units'),
            'costing_quantity'=>array('name'=>'Purchase Quantity', 'default'=>'0'),
            'costing_price'=>array('name'=>'Purchase Price', 'default'=>'0'),
            'cost_per_unit'=>array('name'=>'Cost per Unit', 'default'=>'0'),
            'notes'=>array('name'=>'Notes', 'default'=>''),
			),
		'history_table'=>'ciniki_herbalist_history',
		);
	$objects['recipe'] = array(
		'name'=>'Recipe',
        'o_name'=>'recipe',
        'o_container'=>'recipes',
		'sync'=>'yes',
		'table'=>'ciniki_herbalist_recipes',
		'fields'=>array(
			'name'=>array('name'=>'Name'),
            'units'=>array('name'=>'Units'),
            'yield'=>array('name'=>'Yield', 'default'=>'0'),
            'cost_per_unit'=>array('name'=>'Cost per Unit', 'default'=>'0'),
            'notes'=>array('name'=>'Notes', 'default'=>''),
			),
		'history_table'=>'ciniki_herbalist_history',
		);
	$objects['recipeingredient'] = array(
		'name'=>'Recipe Ingredient',
        'o_name'=>'recipeingredient',
        'o_container'=>'recipeingredients',
		'sync'=>'yes',
		'table'=>'ciniki_herbalist_recipe_ingredients',
		'fields'=>array(
            'recipe_id'=>array('name'=>'Recipe', 'ref'=>'ciniki.herbalist.recipe'),
            'ingredient_id'=>array('name'=>'Ingredient', 'ref'=>'ciniki.herbalist.ingredient'),
            'quantity'=>array('name'=>'Quantity'),
			),
		'history_table'=>'ciniki_herbalist_history',
		);
	$objects['tag'] = array(
		'name'=>'Tag',
        'o_name'=>'tag',
        'o_container'=>'tags',
		'sync'=>'yes',
		'table'=>'ciniki_herbalist_tags',
		'fields'=>array(
            'object'=>array('name'=>'Object'),
            'object_id'=>array('name'=>'Object ID'),
            'tag_type'=>array('name'=>'Type'),
            'tag_name'=>array('name'=>'Name'),
            'permalink'=>array('name'=>'Permalink'),
			),
		'history_table'=>'ciniki_herbalist_history',
		);
	$objects['container'] = array(
		'name'=>'Container',
        'o_name'=>'container',
        'o_container'=>'containers',
		'sync'=>'yes',
		'table'=>'ciniki_herbalist_containers',
		'fields'=>array(
            'name'=>array('name'=>'Name'),
            'top_quantity'=>array('name'=>'Top Quantity', 'default'=>'0'),
            'top_price'=>array('name'=>'Top Price', 'default'=>'0'),
            'bottom_quantity'=>array('name'=>'Bottom Quantity', 'default'=>'0'),
            'bottom_price'=>array('name'=>'Bottom Price', 'default'=>'0'),
            'cost_per_unit'=>array('name'=>'Cost per Unit', 'default'=>'0'),
            'notes'=>array('name'=>'Notes', 'default'=>''),
			),
		'history_table'=>'ciniki_herbalist_history',
		);
	$objects['product'] = array(
		'name'=>'Product',
        'o_name'=>'product',
        'o_container'=>'products',
		'sync'=>'yes',
		'table'=>'ciniki_herbalist_products',
		'fields'=>array(
            'name'=>array('name'=>'Name'),
            'recipe_id'=>array('name'=>'Recipe', 'ref'=>'ciniki.herbalist.recipe', 'default'=>'0'),
            'recipe_quantity'=>array('name'=>'Recipe Quantity', 'default'=>'0'),
            'container_id'=>array('name'=>'Recipe Quantity', 'ref'=>'ciniki.herbalist.container', 'default'=>'0'),
            'cost_per_container'=>array('name'=>'Cost', 'default'=>'0'),
			),
		'history_table'=>'ciniki_herbalist_history',
		);
	$objects['setting'] = array(
		'type'=>'settings',
		'name'=>'Herbalist Settings',
		'table'=>'ciniki_herbalist_settings',
		'history_table'=>'ciniki_herbalist_history',
		);
	
	return array('stat'=>'ok', 'objects'=>$objects);
}
?>
