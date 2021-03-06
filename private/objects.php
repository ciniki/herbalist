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
    $objects['action'] = array(
        'name'=>'Action',
        'o_name'=>'action',
        'o_container'=>'actions',
        'sync'=>'yes',
        'table'=>'ciniki_herbalist_actions',
        'fields'=>array(
            'name'=>array('name'=>'Name'),
            'description'=>array('name'=>'Description', 'default'=>''),
            ),
        'history_table'=>'ciniki_herbalist_history',
        );
    $objects['ailment'] = array(
        'name'=>'Ailment',
        'o_name'=>'ailment',
        'o_container'=>'ailments',
        'sync'=>'yes',
        'table'=>'ciniki_herbalist_ailments',
        'fields'=>array(
            'name'=>array('name'=>'Name'),
            'description'=>array('name'=>'Description', 'default'=>''),
            ),
        'history_table'=>'ciniki_herbalist_history',
        );
    $objects['ingredient'] = array(
        'name'=>'Ingredient',
        'o_name'=>'ingredient',
        'o_container'=>'ingredients',
        'sync'=>'yes',
        'table'=>'ciniki_herbalist_ingredients',
        'fields'=>array(
            'name'=>array('name'=>'Name'),
            'subname'=>array('name'=>'Sub Name'),
            'sorttype'=>array('name'=>'Type'),
            'plant_id'=>array('name'=>'Plant', 'ref'=>'ciniki.herbalist.plant', 'default'=>'0'),
            'recipe_id'=>array('name'=>'Recipe', 'ref'=>'ciniki.herbalist.recipe', 'default'=>'0'),
            'units'=>array('name'=>'Units'),
            'costing_quantity'=>array('name'=>'Purchase Quantity', 'default'=>'0'),
            'costing_price'=>array('name'=>'Purchase Price', 'default'=>'0'),
            'materials_cost_per_unit'=>array('name'=>'Materials Cost per Unit', 'default'=>'0'),
            'time_cost_per_unit'=>array('name'=>'Time Cost per Unit', 'default'=>'0'),
            'total_cost_per_unit'=>array('name'=>'Total Cost per Unit', 'default'=>'0'),
            'total_time_per_unit'=>array('name'=>'Total Time per Unit', 'default'=>'0'),
            'warnings'=>array('name'=>'Warnings', 'default'=>''),
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
            'recipe_type'=>array('name'=>'Type', 'default'=>'0'),
            'flags'=>array('name'=>'Options'),
            'units'=>array('name'=>'Units'),
            'yield'=>array('name'=>'Yield', 'default'=>'0'),
            'production_time'=>array('name'=>'Production Time', 'default'=>'0'),
            'materials_cost_per_unit'=>array('name'=>'Materials Cost per Unit', 'default'=>'0'),
            'time_cost_per_unit'=>array('name'=>'Time Cost per Unit', 'default'=>'0'),
            'total_cost_per_unit'=>array('name'=>'Total Cost per Unit', 'default'=>'0'),
            'total_time_per_unit'=>array('name'=>'Total Time per Unit', 'default'=>'0'),
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
    $objects['recipebatch'] = array(
        'name'=>'Recipe Batch',
        'o_name'=>'recipebatch',
        'o_container'=>'recipebatches',
        'sync'=>'yes',
        'table'=>'ciniki_herbalist_recipe_batches',
        'fields'=>array(
            'recipe_id'=>array('name'=>'Recipe', 'ref'=>'ciniki.herbalist.recipe'),
            'production_date'=>array('name'=>'Production Date'),
            'pressing_date'=>array('name'=>'Pressing Date'),
            'status'=>array('name'=>'Status', 'default'=>'60'),
            'size'=>array('name'=>'Size'),
            'yield'=>array('name'=>'Yield'),
            'production_time'=>array('name'=>'Production Time'),
            'materials_cost_per_unit'=>array('name'=>'Materials Cost per Unit', 'default'=>'0'),
            'time_cost_per_unit'=>array('name'=>'Time Cost per Unit', 'default'=>'0'),
            'total_cost_per_unit'=>array('name'=>'Total Cost per Unit', 'default'=>'0'),
            'total_time_per_unit'=>array('name'=>'Total Time per Unit', 'default'=>'0'),
            'notes'=>array('name'=>'Notes', 'default'=>''),
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
            'ref_id'=>array('name'=>'Reference ID'),
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
            'permalink'=>array('name'=>'Permalink'),
            'flags'=>array('name'=>'Options', 'default'=>'0'),
            'category'=>array('name'=>'Category', 'default'=>''),
            'primary_image_id'=>array('name'=>'Image', 'default'=>'0'),
            'synopsis'=>array('name'=>'Synopsis', 'default'=>''),
            'description'=>array('name'=>'Description', 'default'=>''),
            'ingredients'=>array('name'=>'Ingredients', 'default'=>''),
            ),
        'history_table'=>'ciniki_herbalist_history',
        );
    $objects['productversion'] = array(
        'name'=>'Product Version',
        'o_name'=>'productversion',
        'o_container'=>'productversions',
        'sync'=>'yes',
        'table'=>'ciniki_herbalist_product_versions',
        'fields'=>array(
            'product_id'=>array('name'=>'Product', 'ref'=>'ciniki.herbalist.product'),
            'name'=>array('name'=>'Name'),
            'permalink'=>array('name'=>'Permalink'),
            'flags'=>array('name'=>'Options', 'default'=>'0'),
            'sequence'=>array('name'=>'Order', 'default'=>'1'),
            'recipe_id'=>array('name'=>'Recipe', 'ref'=>'ciniki.herbalist.recipe', 'default'=>'0'),
            'recipe_quantity'=>array('name'=>'Recipe Quantity', 'default'=>'0'),
            'container_id'=>array('name'=>'Container', 'ref'=>'ciniki.herbalist.container', 'default'=>'0'),
            'materials_cost_per_container'=>array('name'=>'Materials Cost', 'default'=>'0'),
            'time_cost_per_container'=>array('name'=>'Time Cost', 'default'=>'0'),
            'total_cost_per_container'=>array('name'=>'Total Cost', 'default'=>'0'),
            'total_time_per_container'=>array('name'=>'Total Time', 'default'=>'0'),
            'inventory'=>array('name'=>'Inventory', 'default'=>'0'),
            'wholesale_price'=>array('name'=>'Wholesale', 'default'=>'0'),
            'retail_price'=>array('name'=>'Retail', 'default'=>'0'),
            ),
        'history_table'=>'ciniki_herbalist_history',
        );
    $objects['productimage'] = array(
        'name'=>'Product Image',
        'o_name'=>'productimage',
        'o_container'=>'productimages',
        'sync'=>'yes',
        'table'=>'ciniki_herbalist_product_images',
        'fields'=>array(
            'product_id'=>array('name'=>'Product', 'ref'=>'ciniki.herbalist.product'),
            'name'=>array('name'=>'Name'),
            'permalink'=>array('name'=>'Permalink'),
            'flags'=>array('name'=>'Options', 'default'=>0x01),
            'image_id'=>array('name'=>'Image', 'ref'=>'ciniki.images.image'),
            'description'=>array('name'=>'Description', 'default'=>''),
            ),
        'history_table'=>'ciniki_herbalist_history',
        );
    $objects['note'] = array(
        'name'=>'Note',
        'o_name'=>'note',
        'o_container'=>'notes',
        'sync'=>'yes',
        'table'=>'ciniki_herbalist_notes',
        'fields'=>array(
            'note_date'=>array('name'=>'Date'),
            'content'=>array('name'=>'Content'),
            'keywords'=>array('name'=>'Keywords', 'default'=>''),
            'keywords_index'=>array('name'=>'Keywords Index', 'default'=>''),
            ),
        'history_table'=>'ciniki_herbalist_history',
        );
    $objects['noteref'] = array(
        'name'=>'Note Ref',
        'o_name'=>'noteref',
        'o_container'=>'noterefs',
        'sync'=>'yes',
        'table'=>'ciniki_herbalist_note_refs',
        'fields'=>array(
            'note_id'=>array('name'=>'Note', 'ref'=>'ciniki.herbalist.note'),
            'object'=>array('name'=>'Object'),
            'object_id'=>array('name'=>'Object ID'),
            ),
        'history_table'=>'ciniki_herbalist_history',
        );
    $objects['herb'] = array(
        'name'=>'herb',
        'o_name'=>'herb',
        'o_container'=>'herbs',
        'sync'=>'yes',
        'table'=>'ciniki_herbalist_herbs',
        'fields'=>array(
            'dry'=>array('name'=>'Dry', 'default'=>''),
            'tincture'=>array('name'=>'Tincture', 'default'=>''),
            'latin_name'=>array('name'=>'Latin Name', 'default'=>''),
            'common_name'=>array('name'=>'Common Name', 'default'=>''),
            'dose'=>array('name'=>'Dose', 'default'=>''),
            'safety'=>array('name'=>'Safety', 'default'=>''),
            'actions'=>array('name'=>'Actions', 'default'=>''),
            'ailments'=>array('name'=>'Ailments', 'default'=>''),
            'energetics'=>array('name'=>'Energetics', 'default'=>''),
            'keywords_index'=>array('name'=>'Keywords', 'default'=>''),
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
