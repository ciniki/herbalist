//
// This app will handle the listing, additions and deletions of herbalist.  These are associated business.
//
function ciniki_herbalist_main() {
	//
	// Panels
	//
	this.init = function() {
		//
		// herbalist panel
		//
		this.menu = new M.panel('Herbalist',
			'ciniki_herbalist_main', 'menu',
			'mc', 'medium narrowaside', 'sectioned', 'ciniki.herbalist.main.menu');
        this.menu.category = '';
		this.menu.sections = {
            '_tabs':{'label':'', 'type':'paneltabs', 'selected':'ingredients', 'tabs':{
                'inventory':{'label':'Inventory', 'fn':'M.ciniki_herbalist_main.menuShow(null,"inventory");'},
                'products':{'label':'Products', 'fn':'M.ciniki_herbalist_main.menuShow(null,"products");'},
                'recipes':{'label':'Recipes', 'fn':'M.ciniki_herbalist_main.menuShow(null,"recipes");'},
                'ingredients':{'label':'Ingredients', 'fn':'M.ciniki_herbalist_main.menuShow(null,"ingredients");'},
                'containers':{'label':'Containers', 'fn':'M.ciniki_herbalist_main.menuShow(null,"containers");'},
                }},
            'categories':{'label':'Categories', 'aside':'yes', 'type':'simplegrid', 'num_cols':1,
                'visible':function() {
                    return (M.ciniki_herbalist_main.menu.sections._tabs.selected == 'products' || M.ciniki_herbalist_main.menu.sections._tabs.selected == 'inventory') ? 'yes':'no';
                    },
                },
            'products':{'label':'Products', 'type':'simplegrid', 'num_cols':2, 'sortable':'yes',
                'visible':function() {return M.ciniki_herbalist_main.menu.sections._tabs.selected=='products'?'yes':'no';},
                'headerValues':['Category', 'Name'],
                'cellClasses':['', ''],
                'sortTypes':['text', 'text'],
                'noData':'No Products',
                'addTxt':'Add Product',
                'addFn':'M.ciniki_herbalist_main.product.edit(\'M.ciniki_herbalist_main.menuShow();\',0);',
                },
            'productversions':{'label':'Inventory', 'type':'simplegrid', 'num_cols':4, 'sortable':'yes',
                'visible':function() {return M.ciniki_herbalist_main.menu.sections._tabs.selected=='inventory'?'yes':'no';},
                'headerValues':['Category', 'Product', 'Option', 'Inventory'],
                'cellClasses':['', '', '', ''],
                'sortTypes':['text', 'text', 'text', 'number'],
                'noData':'No Products',
                },
            'recipes':{'label':'Recipes', 'type':'simplegrid', 'num_cols':1, 
                'visible':function() {return M.ciniki_herbalist_main.menu.sections._tabs.selected=='recipes'?'yes':'no';},
                'headerValues':['Name'],
                'cellClasses':[''],
                'noData':'No Recipes',
                'addTxt':'Add Recipe',
                'addFn':'M.ciniki_herbalist_main.recipe.edit(\'M.ciniki_herbalist_main.menuShow();\',0);',
                },
            '_ingredient_tabs':{'label':'', 'type':'paneltabs', 'selected':'0', 
                'visible':function() { return (M.ciniki_herbalist_main.menu.sections._tabs.selected=='ingredients'?'yes':'no'); },
                'tabs':{
                    '0':{'label':'All', 'fn':'M.ciniki_herbalist_main.menuShow(null,null,0);'},
                    '30':{'label':'Herbs', 'fn':'M.ciniki_herbalist_main.menuShow(null,null,30);'},
                    '60':{'label':'Liquids', 'fn':'M.ciniki_herbalist_main.menuShow(null,null,60);'},
                    '90':{'label':'Misc', 'fn':'M.ciniki_herbalist_main.menuShow(null,null,90);'},
                }},
            'ingredients':{'label':'Ingredients', 'type':'simplegrid', 'num_cols':2, 
                'visible':function() {return M.ciniki_herbalist_main.menu.sections._tabs.selected=='ingredients'?'yes':'no';},
                'headerValues':['Name', 'Cost'],
                'cellClasses':['', ''],
                'noData':'No Ingredients',
                'addTxt':'Add Ingredient',
                'addFn':'M.ciniki_herbalist_main.ingredient.edit(\'M.ciniki_herbalist_main.menuShow();\',0);',
                },
            'containers':{'label':'Containers', 'type':'simplegrid', 'num_cols':2, 
                'visible':function() {return M.ciniki_herbalist_main.menu.sections._tabs.selected=='containers'?'yes':'no';},
                'headerValues':['Name', '$/Unit'],
                'cellClasses':['', ''],
                'noData':'No Containers',
                'addTxt':'Add Container',
                'addFn':'M.ciniki_herbalist_main.container.edit(\'M.ciniki_herbalist_main.menuShow();\',0);',
                },
		};
		this.menu.sectionData = function(s) {
			return this.data[s];
		};
		this.menu.noData = function(s) { return this.sections[s].noData; }
		this.menu.cellValue = function(s, i, j, d) {
            if( s == 'categories' ) {
                switch (j) {
                    case 0: return d.name;
                }
            } else if( s == 'products' ) {
                switch (j) {
                    case 0: return d.category;
                    case 1: return d.name;
                }
            } else if( s == 'productversions' ) {
                switch (j) {
                    case 0: return d.category;
                    case 1: return d.name;
                    case 2: return d.version_name;
                    case 3: return d.inventory;
                }
            } else if( s == 'recipes' ) {
                switch (j) {
                    case 0: return d.name;
                }
            } else if( s == 'ingredients' ) {
                switch (j) {
                    case 0: return d.name;
                    case 1: return d.total_cost_per_unit_display;
                }
            } else if( s == 'containers' ) {
                switch (j) {
                    case 0: return d.name;
                    case 1: return d.cost_per_unit_display;
                }
            }
		};
		this.menu.rowFn = function(s, i, d) {
            if( s == 'categories' ) {
                return 'M.ciniki_herbalist_main.menuShow(\'M.ciniki_herbalist_main.menuShow();\',null,\'' + d.name + '\');';
            } else if( s == 'products' ) {
                return 'M.ciniki_herbalist_main.product.edit(\'M.ciniki_herbalist_main.menuShow();\',\'' + d.id + '\');';
            } else if( s == 'productversions' ) {
                return 'M.ciniki_herbalist_main.productversion.edit(\'M.ciniki_herbalist_main.menuShow();\',\'' + d.id + '\');';
            } else if( s == 'recipes' ) {
                return 'M.ciniki_herbalist_main.recipe.edit(\'M.ciniki_herbalist_main.menuShow();\',\'' + d.id + '\');';
            } else if( s == 'ingredients' ) {
                return 'M.ciniki_herbalist_main.ingredient.edit(\'M.ciniki_herbalist_main.menuShow();\',\'' + d.id + '\');';
            } else if( s == 'containers' ) {
                return 'M.ciniki_herbalist_main.container.edit(\'M.ciniki_herbalist_main.menuShow();\',\'' + d.id + '\');';
            }
		};
		this.menu.addClose('Back');

		//
		// The panel for editing a product
		//
		this.product = new M.panel('Product',
			'ciniki_herbalist_main', 'product',
			'mc', 'large narrowaside', 'sectioned', 'ciniki.herbalist.main.product');
		this.product.data = {};
		this.product.product_id = 0;
        this.product.sections = { 
			'_image':{'label':'Image', 'type':'imageform', 'aside':'yes', 'fields':{
                'primary_image_id':{'label':'', 'type':'image_id', 'hidelabel':'yes', 'controls':'all', 'history':'no',
                    'addDropImage':function(iid) {
                        M.ciniki_herbalist_main.product.setFieldValue('primary_image_id', iid, null, null);
                        return true;
                        },
                    'addDropImageRefresh':'',
                    'deleteImage':function(fid) {
                            M.ciniki_herbalist_main.product.setFieldValue(fid, 0, null, null);
                            return true;
                        },
                    },
				}},
            'general':{'label':'Product', 'aside':'yes', 'fields':{
                'name':{'label':'Name', 'type':'text'},
                'category':{'label':'Category', 'type':'text', 'livesearch':'yes', 'livesearchempty':'yes'},
                'flags':{'label':'Options', 'type':'flags', 'flags':{'1':{'name':'Visible'}}},
                }}, 
			'_categories':{'label':'Web Categories', 'aside':'yes', 
                'visible':function() { return M.modFlagSet('ciniki.herbalist', 0x20);},
                'fields':{
                    'categories':{'label':'', 'hidelabel':'yes', 'type':'tags', 'tags':[], 'hint':'Enter a new category: '},
                }},
            '_tabs':{'label':'', 'type':'paneltabs', 'selected':'options', 'tabs':{
                'options':{'label':'Options', 'fn':'M.ciniki_herbalist_main.product.selectTab("options");'},
                'web':{'label':'Description', 'fn':'M.ciniki_herbalist_main.product.selectTab("web");'},
                'images':{'label':'Images', 'fn':'M.ciniki_herbalist_main.product.selectTab("images");'},
                'notes':{'label':'Notes', 'fn':'M.ciniki_herbalist_main.product.selectTab("notes");'},
                }},
            'versions':{'label':'Purchase Options', 'type':'simplegrid', 'num_cols':5,
                'visible':function() { return (M.ciniki_herbalist_main.product.sections._tabs.selected == 'options' ? 'yes':'hidden');},
                'headerValues':['Name', 'Cost', 'Wholesale', 'Retail', 'Inventory'],
                'headerClasses':['', 'alignright', 'alignright', 'alignright', 'alignright'],
                'cellClasses':['', 'alignright', 'alignright', 'alignright', 'alignright'],
                'addTxt':'Add Option',
                'addFn':'M.ciniki_herbalist_main.product.saveFirst("M.ciniki_herbalist_main.productversion.edit(\'M.ciniki_herbalist_main.product.refreshVersions();\',0,M.ciniki_herbalist_main.product.product_id);");',
                },
			'_synopsis':{'label':'Synopsis', 
                'visible':function() { return (M.ciniki_herbalist_main.product.sections._tabs.selected == 'web' ? 'yes':'hidden');},
                'fields':{
                    'synopsis':{'label':'', 'hidelabel':'yes', 'hint':'', 'size':'small', 'type':'textarea'},
                }},
			'_description':{'label':'Description', 
                'visible':function() { return (M.ciniki_herbalist_main.product.sections._tabs.selected == 'web' ? 'yes':'hidden');},
                'fields':{
                    'description':{'label':'', 'hidelabel':'yes', 'hint':'', 'size':'large', 'type':'textarea'},
                }},
			'_ingredients':{'label':'Ingredients', 
                'visible':function() { return (M.ciniki_herbalist_main.product.sections._tabs.selected == 'web' ? 'yes':'hidden');},
                'fields':{
                    'ingredients':{'label':'', 'hidelabel':'yes', 'hint':'', 'size':'medium', 'type':'textarea'},
                }},
			'images':{'label':'Gallery', 'type':'simplethumbs',
                'visible':function() { return (M.ciniki_herbalist_main.product.sections._tabs.selected == 'images' ? 'yes':'hidden');},
                },
			'_images':{'label':'', 'type':'simplegrid', 'num_cols':1,
                'visible':function() { return (M.ciniki_herbalist_main.product.sections._tabs.selected == 'images' ? 'yes':'hidden');},
				'addTxt':'Add Additional Image',
                'addFn':'M.ciniki_herbalist_main.product.saveFirst("M.ciniki_herbalist_main.productimage.edit(\'M.ciniki_herbalist_main.product.refreshImages();\',0,M.ciniki_herbalist_main.product.product_id);");',
				},
			'_notes':{'label':'Notes', 
                'visible':function() { return (M.ciniki_herbalist_main.product.sections._tabs.selected == 'notes' ? 'yes':'hidden');},
                'fields':{
                    'notes':{'label':'', 'hidelabel':'yes', 'hint':'', 'size':'large', 'type':'textarea'},
                }},
			'_buttons':{'label':'', 'buttons':{
                'save':{'label':'Save', 'fn':'M.ciniki_herbalist_main.product.save();'},
                'delete':{'label':'Delete', 'visible':function() {return M.ciniki_herbalist_main.product.product_id>0?'yes':'no';}, 'fn':'M.ciniki_herbalist_main.product.remove();'},
                }},
            };  
        this.product.sectionData = function(s) { 
            return this.data[s];
        }
		this.product.fieldValue = function(s, i, d) { return this.data[i]; }
		this.product.liveSearchCb = function(s, i, value) {
			if( i == 'category' ) {
				var rsp = M.api.getJSONBgCb('ciniki.herbalist.productSearchField', {'business_id':M.curBusinessID, 'field':i, 'start_needle':value, 'limit':15},
					function(rsp) {
						M.ciniki_herbalist_main.product.liveSearchShow(s, i, M.gE(M.ciniki_herbalist_main.product.panelUID + '_' + i), rsp.results);
					});
			}
		};
		this.product.liveSearchResultValue = function(s, f, i, j, d) {
			if( f == 'category' && d != null ) { return d.name; }
			return '';
		};
		this.product.liveSearchResultRowFn = function(s, f, i, j, d) { 
			if( f == 'category' && d != null ) {
				return 'M.ciniki_herbalist_main.product.updateField(\'' + s + '\',\'' + f + '\',\'' + escape(d.name) + '\');';
			}
		};
		this.product.updateField = function(s, fid, result) {
			M.gE(this.panelUID + '_' + fid).value = unescape(result);
			this.removeLiveSearch(s, fid);
		};
		this.product.thumbFn = function(s, i, d) {
            return 'M.ciniki_herbalist_main.productimage.edit(\'M.ciniki_herbalist_main.product.refreshImages();\',\'' + d.id + '\');';
		};
        this.product.refreshImages = function() {
			if( M.ciniki_herbalist_main.product.product_id > 0 ) {
				var rsp = M.api.getJSONCb('ciniki.herbalist.productGet', {'business_id':M.curBusinessID, 
					'product_id':this.product_id, 'images':'yes'}, function(rsp) {
						if( rsp.stat != 'ok' ) {
							M.api.err(rsp);
							return false;
						}
                        var p = M.ciniki_herbalist_main.product;
						p.data.images = rsp.product.images;
						p.refreshSection('images');
                        p.show();
					});
			}
        }
		this.product.fieldHistoryArgs = function(s, i) {
			return {'method':'ciniki.herbalist.productHistory', 'args':{'business_id':M.curBusinessID, 
				'product_id':this.product_id, 'field':i}};
		}
        this.product.cellValue = function(s, i, j, d) {
            switch(j) {
                case 0: return d.name;
                case 1: return d.total_cost_per_container_display;
                case 2: return d.wholesale_price_display;
                case 3: return d.retail_price_display;
                case 4: return d.inventory;
            }
        }
        this.product.saveFirst = function(nc) {
            if( this.product_id == 0 ) {
                var c = this.serializeForm('yes');
                M.api.postJSONCb('ciniki.herbalist.productAdd', {'business_id':M.curBusinessID, 'product_id':this.product_id}, c,
                    function(rsp) {
                        if( rsp.stat != 'ok' ) {
                            M.api.err(rsp);
                            return false;
                        } 
                        M.ciniki_herbalist_main.product.product_id = rsp.id;
                        eval(nc);
                    });
            } else {
                eval(nc);
            }
        }
		this.product.addDropImage = function(iid) {
            if( this.product_id == 0 ) {
                var c = this.serializeForm('yes');
                M.api.postJSONCb('ciniki.herbalist.productAdd', {'business_id':M.curBusinessID, 'product_id':this.product_id, 'image_id':iid}, c,
                    function(rsp) {
                        if( rsp.stat != 'ok' ) {
                            M.api.err(rsp);
                            return false;
                        } 
                        M.ciniki_herbalist_main.product.product_id = rsp.id;
                        M.ciniki_herbalist_main.product.refreshImages();
                    });
            } else {
				M.api.getJSONCb('ciniki.herbalist.productImageAdd', {'business_id':M.curBusinessID, 'image_id':iid, 'name':'', 'product_id':this.product_id}, function(rsp) {
                    if( rsp.stat != 'ok' ) {
                        M.api.err(rsp);
                        return false;
                    }
                    M.ciniki_herbalist_main.product.refreshImages();
                });
            }
            return true;
		};
        this.product.rowFn = function(s, i, d) {
            return 'M.ciniki_herbalist_main.productversion.edit(\'M.ciniki_herbalist_main.product.refreshVersions();\',' + d.id + ');';
        }
        this.product.refreshVersions = function() {
            M.api.getJSONCb('ciniki.herbalist.productGet', {'business_id':M.curBusinessID, 'product_id':this.product_id}, function(rsp) {
                if( rsp.stat != 'ok' ) {
                    M.api.err(rsp);
                    return false;
                }
                var p = M.ciniki_herbalist_main.product;
                p.data.versions = rsp.product.versions;
                p.refreshSection('versions');
                p.show();
            });
        };
        this.product.selectTab = function(tab) {
            var p = M.ciniki_herbalist_main.product;
            p.sections._tabs.selected = tab;
            p.refreshSection('_tabs');
            p.showHideSection('versions');
            p.showHideSection('_synopsis');
            p.showHideSection('_description');
            p.showHideSection('_ingredients');
            p.showHideSection('images');
            p.showHideSection('_images');
            p.showHideSection('_notes');
        };
        this.product.edit = function(cb, id, tab) {
            this.reset();
            if( id != null ) { this.product_id = id; }
            if( tab != null ) { this.product.sections._tabs.selected = tab; }
            M.api.getJSONCb('ciniki.herbalist.productGet', {'business_id':M.curBusinessID, 'product_id':this.product_id, 'images':'yes'}, function(rsp) {
                if( rsp.stat != 'ok' ) {
                    M.api.err(rsp);
                    return false;
                }
                var p = M.ciniki_herbalist_main.product;
                p.data = rsp.product;
                p.sections._categories.fields.categories.tags = rsp.categories;
                p.refresh();
                p.show(cb);
            });
        }
        this.product.save = function() {
            if( this.product_id > 0 ) {
                var c = this.serializeForm('no');
                if( c != '' ) {
                    M.api.postJSONCb('ciniki.herbalist.productUpdate', {'business_id':M.curBusinessID, 'product_id':this.product_id}, c,
                        function(rsp) {
                            if( rsp.stat != 'ok' ) {
                                M.api.err(rsp);
                                return false;
                            } 
                        M.ciniki_herbalist_main.product.close();
                        });
                } else {
                    this.close();
                }
            } else {
                var c = this.serializeForm('yes');
                M.api.postJSONCb('ciniki.herbalist.productAdd', {'business_id':M.curBusinessID, 'product_id':this.product_id}, c,
                    function(rsp) {
                        if( rsp.stat != 'ok' ) {
                            M.api.err(rsp);
                            return false;
                        } 
                    M.ciniki_herbalist_main.product.close();
                    });
            }
        };
        this.product.remove = function() {
            if( confirm('Are you sure you want to remove this product?') ) {
                M.api.getJSONCb('ciniki.herbalist.productDelete', {'business_id':M.curBusinessID, 'product_id':this.product_id}, function(rsp) {
                    if( rsp.stat != 'ok' ) {
                        M.api.err(rsp);
                        return false;
                    } 
                    M.ciniki_herbalist_main.product.close();
                });
            }
        };
		this.product.addButton('save', 'Save', 'M.ciniki_herbalist_main.product.save();');
		this.product.addClose('Cancel');

		//
		// The panel to display the edit form
		//
		this.productversion = new M.panel('Product Option',
			'ciniki_herbalist_main', 'productversion',
			'mc', 'medium mediumaside', 'sectioned', 'ciniki.herbalist.main.productversion');
		this.productversion.data = {};
		this.productversion.productversion_id = 0;
		this.productversion.product_id = 0;
		this.productversion.sections = {
			'info':{'label':'Information', 'aside':'yes', 'type':'simpleform', 'fields':{
                'name':{'label':'Name', 'type':'text'},
                'recipe_id':{'label':'Recipe', 'type':'select', 'options':{'0':'None'}, 'complex_options':{'name':'name', 'value':'id'}, 
                    'onchangeFn':'M.ciniki_herbalist_main.productversion.updateCosts'},
                'recipe_quantity':{'label':'Quantity', 'type':'text', 'size':'small', 'onkeyupFn':'M.ciniki_herbalist_main.productversion.updateCosts'},
                'container_id':{'label':'Container', 'type':'select', 'options':{'0':'None'}, 'complex_options':{'name':'name', 'value':'id'}, 
                    'onchangeFn':'M.ciniki_herbalist_main.productversion.updateCosts'},
                'flags':{'label':'Options', 'type':'flags', 'flags':{'1':{'name':'Visible'}}},
                'sequence':{'label':'Order', 'type':'text', 'size':'small'},
                'inventory':{'label':'Inventory', 'type':'text', 'size':'small'},
				}},
            '_costs':{'label':'Cost/Container', 'fields':{
                'materials_cost_per_container':{'label':'Materials', 'type':'text', 'editable':'no', 'history':'no'},
                'time_cost_per_container':{'label':'Time', 'type':'text', 'editable':'no', 'history':'no'},
                'total_cost_per_container':{'label':'Total', 'type':'text', 'editable':'no', 'history':'no'},
                }}, 
            '_prices':{'label':'Prices', 'fields':{
                'wholesale_price':{'label':'Wholesale', 'type':'text', 'size':'small'},
                'retail_price':{'label':'Retail', 'type':'text', 'size':'small'},
				}},
            '_buttons':{'label':'', 'buttons':{
                'save':{'label':'Save', 'fn':'M.ciniki_herbalist_main.productversion.save();'},
                'delete':{'label':'Delete', 'visible':'no', 'fn':'M.ciniki_herbalist_main.productversion.remove();'},
				}},
		};
		this.productversion.fieldValue = function(s, i, d) { 
			if( this.data[i] != null ) {
				return this.data[i]; 
			} 
			return ''; 
		};
		this.productversion.fieldHistoryArgs = function(s, i) {
			return {'method':'ciniki.herbalist.productVersionHistory', 'args':{'business_id':M.curBusinessID, 
				'productversion_id':this.productversion_id, 'field':i}};
		};
        this.productversion.updateCosts = function() {
            var mc = 0;
            var tc = 0;
            var q = M.gE(this.panelUID + '_recipe_quantity').value;
            if( q > 0 && this.formValue('recipe_id') > 0 ) {
                var rid = this.formValue('recipe_id');
                for(var i in this.data.recipes) {
                    if( this.data.recipes[i].id == rid ) {
                        mc += (parseFloat(this.data.recipes[i].materials_cost_per_unit) * q);
                        tc += (parseFloat(this.data.recipes[i].time_cost_per_unit) * q);
                    }
                }
            }
            if( q > 0 && this.formValue('container_id') > 0 ) {
                var cid = this.formValue('container_id');
                for(var i in this.data.containers) {
                    if( this.data.containers[i].id == cid ) {
                        mc += parseFloat(this.data.containers[i].cost_per_unit);
                    }
                }
            }
            var c = mc + tc;
            M.gE(this.panelUID + '_materials_cost_per_container').value = '$' + mc.toFixed((mc>0&&mc<0.001)?4:(mc>0&&mc<0.01?3:2));
            M.gE(this.panelUID + '_time_cost_per_container').value = '$' + tc.toFixed((tc>0&&tc<0.001)?4:(tc>0&&tc<0.01?3:2));
            M.gE(this.panelUID + '_total_cost_per_container').value = '$' + c.toFixed((c>0&&c<0.001)?4:(c>0&&c<0.01?3:2));
        };
        this.productversion.edit = function(cb, iid, pid) {
            if( iid != null ) { this.productversion_id = iid; }
            if( pid != null ) { this.product_id = pid; }
            this.reset();
            this.sections._buttons.buttons.delete.visible = 'yes';
            M.api.getJSONCb('ciniki.herbalist.productVersionGet', {'business_id':M.curBusinessID, 'productversion_id':this.productversion_id}, function(rsp) {
                if( rsp.stat != 'ok' ) {
                    M.api.err(rsp);
                    return false;
                }
                var p = M.ciniki_herbalist_main.productversion;
                p.data = rsp.productversion;
                p.data.recipes = rsp.recipes;
                p.data.containers = rsp.containers;
                p.sections.info.fields.recipe_id.options = rsp.recipes;
                p.sections.info.fields.container_id.options = rsp.containers;
                p.refresh();
                p.show(cb);
            });
        };
        this.productversion.save = function() {
            if( this.productversion_id > 0 ) {
                var c = this.serializeForm('no');
                if( c != '' ) {
                    M.api.postJSONFormData('ciniki.herbalist.productVersionUpdate', {'business_id':M.curBusinessID, 
                        'productversion_id':this.productversion_id}, c, function(rsp) {
                            if( rsp.stat != 'ok' ) {
                                M.api.err(rsp);
                                return false;
                            } else {
                                M.ciniki_herbalist_main.productversion.close();
                            }
                        });
                } else {
                    this.close();
                }
            } else {
                var c = this.serializeForm('yes');
                M.api.postJSONFormData('ciniki.herbalist.productVersionAdd', {'business_id':M.curBusinessID, 'product_id':this.product_id}, c, function(rsp) {
                    if( rsp.stat != 'ok' ) {
                        M.api.err(rsp);
                        return false;
                    } 
                    M.ciniki_herbalist_main.productversion.close();
                });
            }
        };
        this.productversion.remove = function() {
            if( confirm('Are you sure you want to delete this purchase option?') ) {
                M.api.getJSONCb('ciniki.herbalist.productVersionDelete', {'business_id':M.curBusinessID, 'productversion_id':this.productversion_id}, function(rsp) {
                    if( rsp.stat != 'ok' ) {
                        M.api.err(rsp);
                        return false;
                    }
                    M.ciniki_herbalist_main.productversion.close();
                });
            }
        };
		this.productversion.addButton('save', 'Save', 'M.ciniki_herbalist_main.productversion.save();');
		this.productversion.addClose('Cancel');

		//
		// The panel to display the edit form
		//
		this.productimage = new M.panel('Edit Image',
			'ciniki_herbalist_main', 'productimage',
			'mc', 'medium', 'sectioned', 'ciniki.herbalist.main.productimage');
		this.productimage.data = {};
		this.productimage.productimage_id = 0;
		this.productimage.product_id = 0;
		this.productimage.sections = {
			'_image':{'label':'Image', 'type':'imageform', 'fields':{
                'image_id':{'label':'', 'type':'image_id', 'hidelabel':'yes', 'controls':'all', 'history':'no'},
				}},
			'info':{'label':'Information', 'type':'simpleform', 'fields':{
                'name':{'label':'Title', 'type':'text'},
                'flags':{'label':'Website', 'type':'flags', 'join':'yes', 'flags':{'1':{'name':'Visible'}}},
				}},
			'_description':{'label':'Description', 'type':'simpleform', 'fields':{
                'description':{'label':'', 'type':'textarea', 'size':'medium', 'hidelabel':'yes'},
				}},
            '_buttons':{'label':'', 'buttons':{
                'save':{'label':'Save', 'fn':'M.ciniki_herbalist_main.productimage.save();'},
                'delete':{'label':'Delete', 'visible':'no', 'fn':'M.ciniki_herbalist_main.productimage.remove();'},
				}},
		};
		this.productimage.fieldValue = function(s, i, d) { 
			if( this.data[i] != null ) {
				return this.data[i]; 
			} 
			return ''; 
		};
		this.productimage.fieldHistoryArgs = function(s, i) {
			return {'method':'ciniki.herbalist.productImageHistory', 'args':{'business_id':M.curBusinessID, 
				'productimage_id':this.productimage_id, 'field':i}};
		};
		this.productimage.addDropImage = function(iid) {
			M.ciniki_herbalist_main.productimage.setFieldValue('image_id', iid, null, null);
			return true;
		};
        this.productimage.edit = function(cb, iid, pid) {
            if( iid != null ) { this.productimage_id = iid; }
            if( pid != null ) { this.product_id = pid; }
            if( this.productimage_id > 0 ) {
                this.reset();
                this.sections._buttons.buttons.delete.visible = 'yes';
                M.api.getJSONCb('ciniki.herbalist.productImageGet', {'business_id':M.curBusinessID, 'productimage_id':this.productimage_id}, function(rsp) {
                    if( rsp.stat != 'ok' ) {
                        M.api.err(rsp);
                        return false;
                    }
                    var p = M.ciniki_herbalist_main.productimage;
                    p.data = rsp.productimage;
                    p.refresh();
                    p.show(cb);
                });
            } else {
                this.reset();
                this.sections._buttons.buttons.delete.visible = 'no';
                this.data = {'flags':1};
                this.refresh();
                this.show(cb);
            }
        };
        this.productimage.save = function() {
            if( this.productimage_id > 0 ) {
                var c = this.serializeFormData('no');
                if( c != '' ) {
                    M.api.postJSONFormData('ciniki.herbalist.productImageUpdate', {'business_id':M.curBusinessID, 
                        'productimage_id':this.productimage_id}, c, function(rsp) {
                            if( rsp.stat != 'ok' ) {
                                M.api.err(rsp);
                                return false;
                            } else {
                                M.ciniki_herbalist_main.productimage.close();
                            }
                        });
                } else {
                    this.close();
                }
            } else {
                var c = this.serializeFormData('yes');
                M.api.postJSONFormData('ciniki.herbalist.productImageAdd', {'business_id':M.curBusinessID, 'product_id':this.product_id}, c, function(rsp) {
                    if( rsp.stat != 'ok' ) {
                        M.api.err(rsp);
                        return false;
                    } 
                    M.ciniki_herbalist_main.productimage.close();
                });
            }
        };

        this.productimage.remove = function() {
            if( confirm('Are you sure you want to delete this image?') ) {
                M.api.getJSONCb('ciniki.herbalist.productImageDelete', {'business_id':M.curBusinessID, 
                    'productimage_id':this.productimage_id}, function(rsp) {
                        if( rsp.stat != 'ok' ) {
                            M.api.err(rsp);
                            return false;
                        }
                        M.ciniki_herbalist_main.productimage.close();
                    });
            }
        };
		this.productimage.addButton('save', 'Save', 'M.ciniki_herbalist_main.productimage.save();');
		this.productimage.addClose('Cancel');

		//
		// The panel for editing a recipe
		//
		this.recipe = new M.panel('Recipe',
			'ciniki_herbalist_main', 'recipe',
			'mc', 'large narrowaside', 'sectioned', 'ciniki.herbalist.main.recipe');
		this.recipe.data = {};
		this.recipe.recipe_id = 0;
        this.recipe.sections = { 
            '_name':{'label':'Recipe Name', 'aside':'yes', 'fields':{
                'name':{'label':'', 'hidelabel':'yes', 'type':'text'},
                }},
            '_yield':{'label':'Expected Yield', 'aside':'yes',
                'fields':{
                    'yield':{'label':'Yield', 'type':'text', 'size':'small', 'onkeyupFn':'M.ciniki_herbalist_main.recipe.updateCPU'},
                    'units':{'label':'Units', 'type':'toggle', 'toggles':{'10':'gm', '60':'ml'}},
                    'production_time':{'label':'Time (minutes)', 'type':'text', 'size':'small', 'onkeyupFn':'M.ciniki_herbalist_main.recipe.updateCPU'},
                }},
            '_costs':{'label':'Expected Cost/Unit', 'aside':'yes', 'fields':{
                'materials_cost_per_unit':{'label':'Materials', 'type':'text', 'editable':'no', 'history':'no'},
                'time_cost_per_unit':{'label':'Time', 'type':'text', 'editable':'no', 'history':'no'},
                'total_cost_per_unit':{'label':'Total', 'type':'text', 'editable':'no', 'history':'no'},
                }}, 
            '_tabs':{'label':'', 'type':'paneltabs', 'selected':'ingredients', 'tabs':{
                'ingredients':{'label':'Ingredients', 'fn':'M.ciniki_herbalist_main.recipe.selectTab("ingredients");'},
                'batches':{'label':'Batches', 'fn':'M.ciniki_herbalist_main.recipe.selectTab("batches");'},
                'notes':{'label':'Notes', 'fn':'M.ciniki_herbalist_main.recipe.selectTab("notes");'},
                }},
            'ingredients_30':{'label':'Herbs', 'type':'simplegrid', 'num_cols':3,
                'visible':function() { 
                    return (M.ciniki_herbalist_main.recipe.data.ingredient_types[30] != null && M.ciniki_herbalist_main.recipe.sections._tabs.selected == 'ingredients' ) ? 'yes': 'hidden'; 
                },
                'headerValues':['Ingredient', 'Quantity', 'Cost'],
                'headerClasses':['', 'alignright', 'alignright'],
                'cellClasses':['', 'alignright', 'alignright'],
                },
            'ingredients_60':{'label':'Liquids', 'type':'simplegrid', 'num_cols':3,
                'visible':function() { 
                    return (M.ciniki_herbalist_main.recipe.data.ingredient_types[60] != null && M.ciniki_herbalist_main.recipe.sections._tabs.selected == 'ingredients' ) ? 'yes': 'hidden'; 
                },
                'headerValues':['Ingredient', 'Quantity', 'Cost'],
                'headerClasses':['', 'alignright', 'alignright'],
                'cellClasses':['', 'alignright', 'alignright'],
                },
            'ingredients_90':{'label':'Misc', 'type':'simplegrid', 'num_cols':3,
                'visible':function() { 
                    return (M.ciniki_herbalist_main.recipe.data.ingredient_types[90] != null && M.ciniki_herbalist_main.recipe.sections._tabs.selected == 'ingredients' ) ? 'yes': 'hidden'; 
                },
                'headerValues':['Ingredient', 'Quantity', 'Cost'],
                'headerClasses':['', 'alignright', 'alignright'],
                'cellClasses':['', 'alignright', 'alignright'],
                },
            'ingredients':{'label':'', 'type':'simplegrid', 'num_cols':1,
                'visible':function() { return M.ciniki_herbalist_main.recipe.sections._tabs.selected == 'ingredients' ? 'yes': 'hidden'; },
                'addTxt':'Add Ingredient',
                'addFn':'M.ciniki_herbalist_main.recipe.addIngredient();',
                },
            'batches':{'label':'', 'type':'simplegrid', 'num_cols':7,
                'visible':function() { return M.ciniki_herbalist_main.recipe.sections._tabs.selected == 'batches' ? 'yes': 'hidden'; },
                'headerValues':['Date', 'Size', 'Yield', 'Time', 'Materials', 'Time', 'Total'],
                'addTxt':'Add Batch',
                'addFn':'M.ciniki_herbalist_main.recipe.addBatch();',
                },
			'_notes':{'label':'Notes', 
                'visible':function() { return M.ciniki_herbalist_main.recipe.sections._tabs.selected == 'notes' ? 'yes': 'hidden'; },
                'fields':{
                    'notes':{'label':'', 'hidelabel':'yes', 'hint':'', 'size':'large', 'type':'textarea'},
                }},
			'_buttons':{'label':'', 'buttons':{
                'save':{'label':'Save', 'fn':'M.ciniki_herbalist_main.recipe.save();'},
                'delete':{'label':'Delete', 'visible':function() {return M.ciniki_herbalist_main.recipe.recipe_id>0?'yes':'no';}, 'fn':'M.ciniki_herbalist_main.recipe.remove();'},
                }},
            };  
        this.recipe.sectionData = function(s) { 
            switch (s) {
                case 'ingredients_30': return this.data['ingredient_types'][30] != null ? this.data['ingredient_types'][30]['ingredients'] : null;
                case 'ingredients_60': return this.data['ingredient_types'][60] != null ? this.data['ingredient_types'][60]['ingredients'] : null;
                case 'ingredients_90': return this.data['ingredient_types'][90] != null ? this.data['ingredient_types'][90]['ingredients'] : null;
            }
            return this.data[s];
        }
		this.recipe.fieldValue = function(s, i, d) { return this.data[i]; }
		this.recipe.fieldHistoryArgs = function(s, i) {
			return {'method':'ciniki.herbalist.recipeHistory', 'args':{'business_id':M.curBusinessID, 
				'recipe_id':this.recipe_id, 'field':i}};
		}
        this.recipe.cellValue = function(s, i, j, d) {
            if( s == 'batches' ) {
                switch(j) {
                    case 0: return d.production_date;
                    case 1: return d.size;
                    case 2: return d.yield;
                    case 3: return d.production_time;
                    case 4: return d.materials_cost_per_unit_display;
                    case 5: return d.time_cost_per_unit_display;
                    case 6: return d.total_cost_per_unit_display;
                }
            } else {
                switch(j) {
                    case 0: return d.name;
                    case 1: return d.quantity_display;
                    case 2: return d.total_cost_per_unit_display;
                }
            }
        }
        this.recipe.rowFn = function(s, i, d) {
            if( s == 'batches' ) {
                return 'M.ciniki_herbalist_main.recipebatch.edit(\'M.ciniki_herbalist_main.recipe.updateBatches();\',' + d.id + ');';
            } else {
                return 'M.ciniki_herbalist_main.recipeingredient.edit(\'M.ciniki_herbalist_main.recipe.updateIngredients();\',' + d.id + ');';
            }
        }
        this.recipe.selectTab = function(tab) {
            var p = M.ciniki_herbalist_main.recipe;
            p.sections._tabs.selected = tab;
            p.refreshSection('_tabs');
            p.showHideSection('ingredients_30');
            p.showHideSection('ingredients_60');
            p.showHideSection('ingredients_90');
            p.showHideSection('ingredients');
            p.showHideSection('batches');
            p.showHideSection('_yield');
            p.showHideSection('_notes');
        };
        this.recipe.addIngredient = function() {
            if( this.recipe_id == 0 ) {
                var c = this.serializeForm('yes');
                M.api.postJSONCb('ciniki.herbalist.recipeAdd', {'business_id':M.curBusinessID, 'recipe_id':this.recipe_id}, c,
                    function(rsp) {
                        if( rsp.stat != 'ok' ) {
                            M.api.err(rsp);
                            return false;
                        } 
                        M.ciniki_herbalist_main.recipe.recipe_id = rsp.id;
                        M.ciniki_herbalist_main.recipeingredient.edit('M.ciniki_herbalist_main.recipe.updateIngredients();',0,rsp.id);
                    });
            } else {
                M.ciniki_herbalist_main.recipeingredient.edit('M.ciniki_herbalist_main.recipe.updateIngredients();',0,this.recipe_id);
            }
        }
        this.recipe.updateIngredients = function() {
            M.api.getJSONCb('ciniki.herbalist.recipeGet', {'business_id':M.curBusinessID, 'recipe_id':this.recipe_id}, function(rsp) {
                if( rsp.stat != 'ok' ) {
                    M.api.err(rsp);
                    return false;
                }
                var p = M.ciniki_herbalist_main.recipe;
                p.data.ingredient_types = rsp.recipe.ingredient_types;
                p.refreshSection('ingredients_30');
                p.refreshSection('ingredients_60');
                p.refreshSection('ingredients_90');
                p.updateCPU();
                p.show();
            });
        };
        this.recipe.addBatch = function() {
            if( this.recipe_id == 0 ) {
                var c = this.serializeForm('yes');
                M.api.postJSONCb('ciniki.herbalist.recipeAdd', {'business_id':M.curBusinessID, 'recipe_id':this.recipe_id}, c,
                    function(rsp) {
                        if( rsp.stat != 'ok' ) {
                            M.api.err(rsp);
                            return false;
                        } 
                        M.ciniki_herbalist_main.recipe.recipe_id = rsp.id;
                        M.ciniki_herbalist_main.recipebatch.edit('M.ciniki_herbalist_main.recipe.updateBatches();',0,rsp.id);
                    });
            } else {
                M.ciniki_herbalist_main.recipebatch.edit('M.ciniki_herbalist_main.recipe.updateBatches();',0,this.recipe_id);
            }
        }
        this.recipe.updateBatches = function() {
            M.api.getJSONCb('ciniki.herbalist.recipeGet', {'business_id':M.curBusinessID, 'recipe_id':this.recipe_id}, function(rsp) {
                if( rsp.stat != 'ok' ) {
                    M.api.err(rsp);
                    return false;
                }
                var p = M.ciniki_herbalist_main.recipe;
                p.data.batches = rsp.recipe.batches;
                p.refreshSection('batches');
                p.show();
            });
        };
        this.recipe.updateCPU = function() {
            var y = M.gE(this.panelUID + '_yield').value;
            var t = M.gE(this.panelUID + '_production_time').value;
            var mc = 0; // materials cost
            var tc = 0; // materials cost
            var c = 0;  // total cost
            for(var i in this.data.ingredient_types) {
                for(var j in this.data.ingredient_types[i].ingredients) {
                    mc += (this.data.ingredient_types[i].ingredients[j].quantity * this.data.ingredient_types[i].ingredients[j].materials_cost_per_unit);
                    tc += (this.data.ingredient_types[i].ingredients[j].quantity * this.data.ingredient_types[i].ingredients[j].time_cost_per_unit);
                }
            }
            var mv = (mc/y);
            M.gE(this.panelUID + '_materials_cost_per_unit').value = '$' + mv.toFixed((mv>0&&mv<0.001)?4:(mv>0&&mv<0.01?3:2));
            var tv = (tc/y);
            if( M.curBusiness.modules['ciniki.herbalist'].settings != null 
                && M.curBusiness.modules['ciniki.herbalist'].settings['production-hourly-wage'] != null 
                && M.curBusiness.modules['ciniki.herbalist'].settings['production-hourly-wage'] > 0 ) {
                // hourly wage per unit of recipe
                tv += (((t/60)*M.curBusiness.modules['ciniki.herbalist'].settings['production-hourly-wage'])/y);
            }
            M.gE(this.panelUID + '_time_cost_per_unit').value = '$' + tv.toFixed((tv>0&&tv<0.001)?4:(tv>0&&tv<0.01?3:2));
            c = mv + tv;
            M.gE(this.panelUID + '_total_cost_per_unit').value = '$' + c.toFixed((c>0&&c<0.001)?4:(c>0&&c<0.01?3:2));
        }
        this.recipe.edit = function(cb, id) {
            this.reset();
            if( id != null ) { this.recipe_id = id; }
            M.api.getJSONCb('ciniki.herbalist.recipeGet', {'business_id':M.curBusinessID, 'recipe_id':this.recipe_id}, function(rsp) {
                if( rsp.stat != 'ok' ) {
                    M.api.err(rsp);
                    return false;
                }
                var p = M.ciniki_herbalist_main.recipe;
                p.data = rsp.recipe;
                p.refresh();
                p.show(cb);
            });
        }
        this.recipe.downloadPDF = function() {
            M.api.openPDF('ciniki.herbalist.recipePDF', {'business_id':M.curBusinessID, 'recipe_id':this.recipe_id});
        }
        this.recipe.save = function() {
            if( this.recipe_id > 0 ) {
                var c = this.serializeForm('no');
                if( c != '' ) {
                    M.api.postJSONCb('ciniki.herbalist.recipeUpdate', {'business_id':M.curBusinessID, 'recipe_id':this.recipe_id}, c,
                        function(rsp) {
                            if( rsp.stat != 'ok' ) {
                                M.api.err(rsp);
                                return false;
                            } 
                        M.ciniki_herbalist_main.recipe.close();
                        });
                } else {
                    this.close();
                }
            } else {
                var c = this.serializeForm('yes');
                M.api.postJSONCb('ciniki.herbalist.recipeAdd', {'business_id':M.curBusinessID, 'recipe_id':this.recipe_id}, c,
                    function(rsp) {
                        if( rsp.stat != 'ok' ) {
                            M.api.err(rsp);
                            return false;
                        } 
                    M.ciniki_herbalist_main.recipe.close();
                    });
            }
        };
        this.recipe.remove = function() {
            if( confirm('Are you sure you want to remove this recipe?') ) {
                M.api.getJSONCb('ciniki.herbalist.recipeDelete', {'business_id':M.curBusinessID, 'recipe_id':this.recipe_id}, function(rsp) {
                    if( rsp.stat != 'ok' ) {
                        M.api.err(rsp);
                        return false;
                    } 
                    M.ciniki_herbalist_main.recipe.close();
                });
            }
        };
		this.recipe.addButton('save', 'Save', 'M.ciniki_herbalist_main.recipe.save();');
		this.recipe.addButton('print', 'Print', 'M.ciniki_herbalist_main.recipe.downloadPDF();');
		this.recipe.addClose('Cancel');

		//
		// The panel for editing a recipe ingredient
		//
		this.recipeingredient = new M.panel('Recipe Ingredient',
			'ciniki_herbalist_main', 'recipeingredient',
			'mc', 'medium', 'sectioned', 'ciniki.herbalist.main.recipeingredient');
		this.recipeingredient.data = {};
		this.recipeingredient.recipe_id = 0;
        this.recipeingredient.recipeingredient_id = 0;
        this.recipeingredient.sections = { 
            'general':{'label':'Ingredient', 'fields':{
                'ingredient_id':{'label':'Ingredient', 'type':'select', 'options':{}, 'complex_options':{'value':'id', 'name':'name'}},
                'quantity':{'label':'Quantity', 'type':'text', 'size':'small'},
                }}, 
			'_buttons':{'label':'', 'buttons':{
                'save':{'label':'Save', 'fn':'M.ciniki_herbalist_main.recipeingredient.save();'},
                'delete':{'label':'Delete', 'visible':function() {return M.ciniki_herbalist_main.recipeingredient.recipeingredient_id>0?'yes':'no';}, 'fn':'M.ciniki_herbalist_main.recipeingredient.remove();'},
                }},
            };  
		this.recipeingredient.fieldValue = function(s, i, d) { return this.data[i]; }
		this.recipeingredient.fieldHistoryArgs = function(s, i) {
			return {'method':'ciniki.herbalist.recipeIngredientHistory', 'args':{'business_id':M.curBusinessID, 
				'recipeingredient_id':this.recipeingredient_id, 'field':i}};
		}
        this.recipeingredient.edit = function(cb, riid, rid) {
            this.reset();
            if( riid != null ) { this.recipeingredient_id = riid; }
            if( rid != null ) { this.recipe_id = rid; }
            M.api.getJSONCb('ciniki.herbalist.recipeIngredientGet', {'business_id':M.curBusinessID, 'recipe_id':this.recipe_id, 'recipeingredient_id':this.recipeingredient_id}, function(rsp) {
                if( rsp.stat != 'ok' ) {
                    M.api.err(rsp);
                    return false;
                }
                var p = M.ciniki_herbalist_main.recipeingredient;
                p.data = rsp.recipeingredient;
                p.sections.general.fields.ingredient_id.options = rsp.ingredients;
                p.refresh();
                p.show(cb);
            });
        }
        this.recipeingredient.save = function() {
            if( this.recipeingredient_id > 0 ) {
                var c = this.serializeForm('no');
                if( c != '' ) {
                    M.api.postJSONCb('ciniki.herbalist.recipeIngredientUpdate', {'business_id':M.curBusinessID, 'recipeingredient_id':this.recipeingredient_id}, c,
                        function(rsp) {
                            if( rsp.stat != 'ok' ) {
                                M.api.err(rsp);
                                return false;
                            } 
                        M.ciniki_herbalist_main.recipeingredient.close();
                        });
                } else {
                    this.close();
                }
            } else {
                var c = this.serializeForm('yes');
                M.api.postJSONCb('ciniki.herbalist.recipeIngredientAdd', {'business_id':M.curBusinessID, 'recipe_id':this.recipe_id}, c,
                    function(rsp) {
                        if( rsp.stat != 'ok' ) {
                            M.api.err(rsp);
                            return false;
                        } 
                    M.ciniki_herbalist_main.recipeingredient.close();
                    });
            }
        };
        this.recipeingredient.remove = function() {
            if( confirm('Are you sure you want to remove this recipe?') ) {
                M.api.getJSONCb('ciniki.herbalist.recipeIngredientDelete', {'business_id':M.curBusinessID, 'recipeingredient_id':this.recipeingredient_id}, function(rsp) {
                    if( rsp.stat != 'ok' ) {
                        M.api.err(rsp);
                        return false;
                    } 
                    M.ciniki_herbalist_main.recipeingredient.close();
                });
            }
        };
		this.recipeingredient.addButton('save', 'Save', 'M.ciniki_herbalist_main.recipeingredient.save();');
		this.recipeingredient.addClose('Cancel');

		//
		// The panel for editing a recipe batch
		//
		this.recipebatch = new M.panel('Recipe Batch',
			'ciniki_herbalist_main', 'recipebatch',
			'mc', 'medium narrowaside', 'sectioned', 'ciniki.herbalist.main.recipebatch');
		this.recipebatch.data = {};
		this.recipebatch.recipe_id = 0;
        this.recipebatch.batch_id = 0;
        this.recipebatch.sections = { 
            'general':{'label':'Batch', 'aside':'yes', 'fields':{
                'production_date':{'label':'Date', 'type':'date', 'size':'small'},
                'size':{'label':'Size', 'type':'text', 'size':'small', 'onkeyupFn':'M.ciniki_herbalist_main.recipebatch.updateCPU'},
                'yield':{'label':'Yield', 'type':'text', 'size':'small', 'onkeyupFn':'M.ciniki_herbalist_main.recipebatch.updateCPU'},
                'production_time':{'label':'Time', 'type':'text', 'size':'small', 'onkeyupFn':'M.ciniki_herbalist_main.recipebatch.updateCPU'},
                }}, 
            '_costs':{'label':'Cost/Unit', 'aside':'yes', 'fields':{
                'materials_cost_per_unit':{'label':'Materials', 'type':'text', 'editable':'no', 'history':'no'},
                'time_cost_per_unit':{'label':'Time', 'type':'text', 'editable':'no', 'history':'no'},
                'total_cost_per_unit':{'label':'Total', 'type':'text', 'editable':'no', 'history':'no'},
                }}, 
            'productversions':{'label':'Options', 'aside':'yes', 'type':'simplegrid', 'num_cols':2,
                'cellClasses':['label', ''],
                }, 
            'ingredients_30':{'label':'Herbs', 'type':'simplegrid', 'num_cols':3,
                'visible':function() { return M.ciniki_herbalist_main.recipe.data.ingredient_types[30] != null ? 'yes': 'hidden'; },
                'headerValues':['Ingredient', 'Quantity', 'Cost'],
                'headerClasses':['', 'alignright', 'alignright'],
                'cellClasses':['', 'alignright', 'alignright'],
                },
            'ingredients_60':{'label':'Liquids', 'type':'simplegrid', 'num_cols':3,
                'visible':function() { return M.ciniki_herbalist_main.recipe.data.ingredient_types[60] != null ? 'yes': 'hidden'; },
                'headerValues':['Ingredient', 'Quantity', 'Cost'],
                'headerClasses':['', 'alignright', 'alignright'],
                'cellClasses':['', 'alignright', 'alignright'],
                },
            'ingredients_90':{'label':'Misc', 'type':'simplegrid', 'num_cols':3,
                'visible':function() { return M.ciniki_herbalist_main.recipe.data.ingredient_types[90] != null ? 'yes': 'hidden'; },
                'headerValues':['Ingredient', 'Quantity', 'Cost'],
                'headerClasses':['', 'alignright', 'alignright'],
                'cellClasses':['', 'alignright', 'alignright'],
                },
			'_notes':{'label':'Notes', 'fields':{
                'notes':{'label':'', 'hidelabel':'yes', 'hint':'', 'size':'medium', 'type':'textarea'},
                }},
			'_buttons':{'label':'', 'buttons':{
                'save':{'label':'Save', 'fn':'M.ciniki_herbalist_main.recipebatch.save();'},
                'delete':{'label':'Delete', 'visible':function() {return M.ciniki_herbalist_main.recipebatch.batch_id>0?'yes':'no';}, 'fn':'M.ciniki_herbalist_main.recipebatch.remove();'},
                }},
            };  
		this.recipebatch.fieldValue = function(s, i, d) { return this.data[i]; }
		this.recipebatch.fieldHistoryArgs = function(s, i) {
			return {'method':'ciniki.herbalist.recipeBatchHistory', 'args':{'business_id':M.curBusinessID, 
				'batch_id':this.batch_id, 'field':i}};
		}
        this.recipebatch.sectionData = function(s) { 
            switch (s) {
                case 'ingredients_30': return this.data['ingredient_types'][30] != null ? this.data['ingredient_types'][30]['ingredients'] : null;
                case 'ingredients_60': return this.data['ingredient_types'][60] != null ? this.data['ingredient_types'][60]['ingredients'] : null;
                case 'ingredients_90': return this.data['ingredient_types'][90] != null ? this.data['ingredient_types'][90]['ingredients'] : null;
            }
            return this.data[s];
        }
        this.recipebatch.cellValue = function(s, i, j, d) {
            if( s == 'productversions' ) {
                switch(j) {
                    case 0: return d.name;
                    case 1: return d.total_cost_display;
                }
            } else {
                switch(j) {
                    case 0: return d.name;
                    case 1: return d.quantity_display;
                    case 2: return d.total_cost_per_unit_display;
                }
            }
        }
        this.recipebatch.updateCPU = function() {
            var s = M.gE(this.panelUID + '_size').value;
            var y = M.gE(this.panelUID + '_yield').value;
            var t = M.gE(this.panelUID + '_production_time').value;
            var mc = 0; // materials cost
            var tc = 0; // materials cost
            var c = 0;  // total cost
            for(var i in this.data.ingredient_types) {
                for(var j in this.data.ingredient_types[i].ingredients) {
                    var umc = (this.data.ingredient_types[i].ingredients[j].quantity * s * this.data.ingredient_types[i].ingredients[j].materials_cost_per_unit);
                    var utc = (this.data.ingredient_types[i].ingredients[j].quantity * s * this.data.ingredient_types[i].ingredients[j].time_cost_per_unit);
                    var uc = umc + utc;
                    mc += umc;
                    tc += utc;
                    this.data.ingredient_types[i].ingredients[j].quantity_display = (this.data.ingredient_types[i].ingredients[j].quantity * s) + ' ' + this.data.ingredient_types[i].ingredients[j].units;
                    this.data.ingredient_types[i].ingredients[j].total_cost_per_unit_display = '$' + uc.toFixed((uc>0&&uc<0.001)?4:(uc>0&&uc<0.01?3:2));
                }
            }
            var mv = (mc/y);
            M.gE(this.panelUID + '_materials_cost_per_unit').value = '$' + mv.toFixed((mv>0&&mv<0.001)?4:(mv>0&&mv<0.01?3:2));
            var tv = (tc/y);
            if( M.curBusiness.modules['ciniki.herbalist'].settings != null 
                && M.curBusiness.modules['ciniki.herbalist'].settings['production-hourly-wage'] != null 
                && M.curBusiness.modules['ciniki.herbalist'].settings['production-hourly-wage'] > 0 ) {
                // hourly wage per unit of recipe
                tv += (((t/60)*M.curBusiness.modules['ciniki.herbalist'].settings['production-hourly-wage'])/y);
            }
            M.gE(this.panelUID + '_time_cost_per_unit').value = '$' + tv.toFixed((tv>0&&tv<0.001)?4:(tv>0&&tv<0.01?3:2));
            c = mv + tv;
            M.gE(this.panelUID + '_total_cost_per_unit').value = '$' + c.toFixed((c>0&&c<0.001)?4:(c>0&&c<0.01?3:2));
            if( this.data.productversions ) {
                for(i in this.data.productversions) {
                    this.data.productversions[i].total_cost = (parseFloat(this.data.productversions[i].recipe_quantity) * c) + parseFloat(this.data.productversions[i].container_cost);
                    this.data.productversions[i].total_cost_display = '$' + this.data.productversions[i].total_cost.toFixed(2);
                }
            }
            this.refreshSection('ingredients_30');
            this.refreshSection('ingredients_60');
            this.refreshSection('ingredients_90');
            this.refreshSection('productversions');
        }
        this.recipebatch.edit = function(cb, riid, rid) {
            this.reset();
            if( riid != null ) { this.batch_id = riid; }
            if( rid != null ) { this.recipe_id = rid; }
            M.api.getJSONCb('ciniki.herbalist.recipeBatchGet', {'business_id':M.curBusinessID, 'recipe_id':this.recipe_id, 'batch_id':this.batch_id}, function(rsp) {
                if( rsp.stat != 'ok' ) {
                    M.api.err(rsp);
                    return false;
                }
                var p = M.ciniki_herbalist_main.recipebatch;
                p.data = rsp.batch;
                p.recipe_id = rsp.batch.recipe_id;
                p.refresh();
                p.show(cb);
            });
        }
        this.recipebatch.downloadPDF = function() {
            var size = M.gE(this.panelUID + '_size').value;
            M.api.openPDF('ciniki.herbalist.recipePDF', {'business_id':M.curBusinessID, 'recipe_id':this.recipe_id, 'size':size});
        }
        this.recipebatch.save = function() {
            if( this.batch_id > 0 ) {
                var c = this.serializeForm('no');
                if( c != '' ) {
                    M.api.postJSONCb('ciniki.herbalist.recipeBatchUpdate', {'business_id':M.curBusinessID, 'batch_id':this.batch_id}, c,
                        function(rsp) {
                            if( rsp.stat != 'ok' ) {
                                M.api.err(rsp);
                                return false;
                            } 
                        M.ciniki_herbalist_main.recipebatch.close();
                        });
                } else {
                    this.close();
                }
            } else {
                var c = this.serializeForm('yes');
                M.api.postJSONCb('ciniki.herbalist.recipeBatchAdd', {'business_id':M.curBusinessID, 'recipe_id':this.recipe_id}, c,
                    function(rsp) {
                        if( rsp.stat != 'ok' ) {
                            M.api.err(rsp);
                            return false;
                        } 
                    M.ciniki_herbalist_main.recipebatch.close();
                    });
            }
        };
        this.recipebatch.remove = function() {
            if( confirm('Are you sure you want to remove this recipe?') ) {
                M.api.getJSONCb('ciniki.herbalist.recipeBatchDelete', {'business_id':M.curBusinessID, 'batch_id':this.batch_id}, function(rsp) {
                    if( rsp.stat != 'ok' ) {
                        M.api.err(rsp);
                        return false;
                    } 
                    M.ciniki_herbalist_main.recipebatch.close();
                });
            }
        };
		this.recipebatch.addButton('save', 'Save', 'M.ciniki_herbalist_main.recipebatch.save();');
		this.recipebatch.addButton('print', 'Print', 'M.ciniki_herbalist_main.recipebatch.downloadPDF();');
		this.recipebatch.addClose('Cancel');

		//
		// The panel for containering an ingredient
		//
		this.ingredient = new M.panel('Ingredient',
			'ciniki_herbalist_main', 'ingredient',
			'mc', 'medium', 'sectioned', 'ciniki.herbalist.main.ingredient');
		this.ingredient.data = {};
		this.ingredient.ingredient_id = 0;
        this.ingredient.sections = { 
            'general':{'label':'Ingredient', 'fields':{
                'name':{'label':'Name', 'type':'text'},
                'sorttype':{'label':'Type', 'type':'multitoggle', 'toggles':{'30':'Herb', '60':'Liquid', '90':'Misc'}},
                'recipe_id':{'label':'Recipe', 'type':'select', 'options':{'0':'None'}, 'onchangeFn':'M.ciniki_herbalist_main.ingredient.updateForm'},
                'units':{'label':'Units', 'type':'toggle', 'toggles':{'10':'gm', '60':'ml'} },
                }},
            'costing':{'label':'', 'visible':'hidden', 'fields':{
                'costing_quantity':{'label':'Quantity', 'type':'text', 'visible':'hidden', 'size':'small', 'onkeyupFn':'M.ciniki_herbalist_main.ingredient.updateCPU'},
//                'costing_time':{'label':'Time', 'type':'text', 'size':'small', 'onkeyupFn':'M.ciniki_herbalist_main.ingredient.updateCPU'},
                'costing_price':{'label':'Price', 'type':'text', 'size':'small', 'visible':'hidden', 'onkeyupFn':'M.ciniki_herbalist_main.ingredient.updateCPU'},
//                'materials_cost_per_unit':{'label':'Materials Cost/Unit', 'type':'text', 'visible':'hidden', 'editable':'no'},
//                'time_cost_per_unit':{'label':'Time Cost/Unit', 'type':'text', 'editable':'no'},
                'total_cost_per_unit':{'label':'Total Cost/Unit', 'type':'text', 'visible':'hidden', 'editable':'no'},
                }}, 
			'_notes':{'label':'Notes', 'fields':{
                'notes':{'label':'', 'hidelabel':'yes', 'hint':'', 'size':'large', 'type':'textarea'},
                }},
			'_buttons':{'label':'', 'buttons':{
                'save':{'label':'Save', 'fn':'M.ciniki_herbalist_main.ingredient.save();'},
                'delete':{'label':'Delete', 'visible':function() {return M.ciniki_herbalist_main.ingredient.ingredient_id>0?'yes':'no';}, 'fn':'M.ciniki_herbalist_main.ingredient.remove();'},
                }},
            };
		this.ingredient.fieldValue = function(s, i, d) { return this.data[i]; }
		this.ingredient.fieldHistoryArgs = function(s, i) {
			return {'method':'ciniki.herbalist.ingredientHistory', 'args':{'business_id':M.curBusinessID, 
				'ingredient_id':this.ingredient_id, 'field':i}};
		}
        this.ingredient.updateForm = function() {
            if( this.formValue('recipe_id') > 0 ) {
                M.gE(this.panelUID + '_section_costing').style.display = 'none';
            } else {
                M.gE(this.panelUID + '_section_costing').style.display = '';
            }
        }
        this.ingredient.updateCPU = function() {
            var cq = M.gE(this.panelUID + '_costing_quantity').value;
//            var ct = M.gE(this.panelUID + '_costing_time').value;
            var cp = M.gE(this.panelUID + '_costing_price').value;
            cp = parseFloat(cp.replace(/[^\d\.]/g,''));
            var mc = 0;
            if( cq != '' && cq > 0 && cp != '' && cp > 0 ) {
                mc += (cp/cq);
            }
//            M.gE(this.panelUID + '_materials_cost_per_unit').value = '$' + mc.toFixed(2);
            var mt = 0;
//            if( cq != '' && cq > 0 && ct != '' && ct > 0 ) {
//                mt += (cp/cq);
//            }
//            M.gE(this.panelUID + '_time_cost_per_unit').value = '$' + mt.toFixed(2);
            var c = mc + mt;
            M.gE(this.panelUID + '_total_cost_per_unit').value = '$' + c.toFixed((c>0&&c<0.001)?4:(c>0&&c<0.01?3:2));
        }
        this.ingredient.edit = function(cb, id) {
            this.reset();
            if( id != null ) { this.ingredient_id = id; }
            M.api.getJSONCb('ciniki.herbalist.ingredientGet', {'business_id':M.curBusinessID, 'ingredient_id':this.ingredient_id}, function(rsp) {
                if( rsp.stat != 'ok' ) {
                    M.api.err(rsp);
                    return false;
                }
                var p = M.ciniki_herbalist_main.ingredient;
                p.data = rsp.ingredient;
                p.sections.general.fields.recipe_id.options = rsp.recipes;
                p.sections.general.fields.recipe_id.options[0] = 'None';
                if( rsp.ingredient.recipe_id > 0 ) {
                    p.sections.costing.visible = 'hidden';
                } else {
                    p.sections.costing.visible = 'yes';
                }
                p.refresh();
                p.show(cb);
            });
        }
        this.ingredient.save = function() {
            if( this.ingredient_id > 0 ) {
                var c = this.serializeForm('no');
                if( c != '' ) {
                    M.api.postJSONCb('ciniki.herbalist.ingredientUpdate', {'business_id':M.curBusinessID, 'ingredient_id':this.ingredient_id}, c,
                        function(rsp) {
                            if( rsp.stat != 'ok' ) {
                                M.api.err(rsp);
                                return false;
                            } 
                        M.ciniki_herbalist_main.ingredient.close();
                        });
                } else {
                    this.close();
                }
            } else {
                var c = this.serializeForm('yes');
                M.api.postJSONCb('ciniki.herbalist.ingredientAdd', {'business_id':M.curBusinessID, 'ingredient_id':this.ingredient_id}, c,
                    function(rsp) {
                        if( rsp.stat != 'ok' ) {
                            M.api.err(rsp);
                            return false;
                        } 
                    M.ciniki_herbalist_main.ingredient.close();
                    });
            }
        };
        this.ingredient.remove = function() {
            if( confirm('Are you sure you want to remove this ingredient?') ) {
                M.api.getJSONCb('ciniki.herbalist.ingredientDelete', {'business_id':M.curBusinessID, 'ingredient_id':this.ingredient_id}, function(rsp) {
                    if( rsp.stat != 'ok' ) {
                        M.api.err(rsp);
                        return false;
                    } 
                    M.ciniki_herbalist_main.ingredient.close();
                });
            }
        };
		this.ingredient.addButton('save', 'Save', 'M.ciniki_herbalist_main.ingredient.save();');
		this.ingredient.addClose('Cancel');

		//
		// The panel for editing containers
		//
		this.container = new M.panel('Container',
			'ciniki_herbalist_main', 'container',
			'mc', 'medium', 'sectioned', 'ciniki.herbalist.main.container');
		this.container.data = {};
		this.container.container_id = 0;
        this.container.sections = { 
            'general':{'label':'Container', 'fields':{
                'name':{'label':'Name', 'type':'text'},
                'top_quantity':{'label':'Top Quantity', 'type':'text', 'size':'small', 'onkeyupFn':'M.ciniki_herbalist_main.container.updateCPU'},
                'top_price':{'label':'Top Price', 'type':'text', 'size':'small', 'onkeyupFn':'M.ciniki_herbalist_main.container.updateCPU'},
                'bottom_quantity':{'label':'Bottom Quantity', 'type':'text', 'size':'small', 'onkeyupFn':'M.ciniki_herbalist_main.container.updateCPU'},
                'bottom_price':{'label':'Bottom Price', 'type':'text', 'size':'small', 'onkeyupFn':'M.ciniki_herbalist_main.container.updateCPU'},
                'cost_per_unit':{'label':'Cost/Unit', 'type':'text', 'editable':'no'},
                }}, 
			'_notes':{'label':'Notes', 'fields':{
                'notes':{'label':'', 'hidelabel':'yes', 'hint':'', 'size':'large', 'type':'textarea'},
                }},
			'_buttons':{'label':'', 'buttons':{
                'save':{'label':'Save', 'fn':'M.ciniki_herbalist_main.container.save();'},
                'delete':{'label':'Delete', 'visible':function() {return M.ciniki_herbalist_main.container.container_id>0?'yes':'no';}, 'fn':'M.ciniki_herbalist_main.container.remove();'},
                }},
            };  
		this.container.fieldValue = function(s, i, d) { return this.data[i]; }
		this.container.fieldHistoryArgs = function(s, i) {
			return {'method':'ciniki.herbalist.containerHistory', 'args':{'business_id':M.curBusinessID, 
				'container_id':this.container_id, 'field':i}};
		}
        this.container.updateCPU = function() {
            var tq = M.gE(this.panelUID + '_top_quantity').value;
            var tp = M.gE(this.panelUID + '_top_price').value;
            var bq = M.gE(this.panelUID + '_bottom_quantity').value;
            var bp = M.gE(this.panelUID + '_bottom_price').value;
            tp = parseFloat(tp.replace(/[^\d\.]/g,''));
            bp = parseFloat(bp.replace(/[^\d\.]/g,''));
            var v = 0;
            if( tq != '' && tq > 0 && tp != '' && tp > 0 ) {
                v += (tp/tq);
            }
            if( bq != '' && bq > 0 && bp != '' && bp > 0 ) {
                v += (bp/bq);
            }
            M.gE(this.panelUID + '_cost_per_unit').value = '$' + v.toFixed((v<0.001)?4:(v<0.01?3:2));
        }
        this.container.edit = function(cb, id) {
            this.reset();
            if( id != null ) { this.container_id = id; }
            M.api.getJSONCb('ciniki.herbalist.containerGet', {'business_id':M.curBusinessID, 'container_id':this.container_id}, function(rsp) {
                if( rsp.stat != 'ok' ) {
                    M.api.err(rsp);
                    return false;
                }
                var p = M.ciniki_herbalist_main.container;
                p.data = rsp.container;
                p.refresh();
                p.show(cb);
            });
        }
        this.container.save = function() {
            if( this.container_id > 0 ) {
                var c = this.serializeForm('no');
                if( c != '' ) {
                    M.api.postJSONCb('ciniki.herbalist.containerUpdate', {'business_id':M.curBusinessID, 'container_id':this.container_id}, c,
                        function(rsp) {
                            if( rsp.stat != 'ok' ) {
                                M.api.err(rsp);
                                return false;
                            } 
                        M.ciniki_herbalist_main.container.close();
                        });
                } else {
                    this.close();
                }
            } else {
                var c = this.serializeForm('yes');
                M.api.postJSONCb('ciniki.herbalist.containerAdd', {'business_id':M.curBusinessID, 'container_id':this.container_id}, c,
                    function(rsp) {
                        if( rsp.stat != 'ok' ) {
                            M.api.err(rsp);
                            return false;
                        } 
                    M.ciniki_herbalist_main.container.close();
                    });
            }
        };
        this.container.remove = function() {
            if( confirm('Are you sure you want to remove this container?') ) {
                M.api.getJSONCb('ciniki.herbalist.containerDelete', {'business_id':M.curBusinessID, 'container_id':this.container_id}, function(rsp) {
                    if( rsp.stat != 'ok' ) {
                        M.api.err(rsp);
                        return false;
                    } 
                    M.ciniki_herbalist_main.container.close();
                });
            }
        };
		this.container.addButton('save', 'Save', 'M.ciniki_herbalist_main.container.save();');
		this.container.addClose('Cancel');
	}

	//
	// Arguments:
	// aG - The arguments to be parsed into args
	//
	this.start = function(cb, appPrefix, aG) {
		args = {};
		if( aG != null ) { args = eval(aG); }

		//
		// Create the app container if it doesn't exist, and clear it out
		// if it does exist.
		//
		var appContainer = M.createContainer(appPrefix, 'ciniki_herbalist_main', 'yes');
		if( appContainer == null ) {
			alert('App Error');
			return false;
		} 

        this.menuShow(cb);
	}

	this.menuShow = function(cb, tab, itab) {
		this.menu.data = {};
        if( tab != null ) { this.menu.sections._tabs.selected = tab; }
        if( itab != null && this.menu.sections._tabs.selected == 'ingredients' ) { this.menu.sections._ingredient_tabs.selected = itab; }
        if( itab != null && this.menu.sections._tabs.selected == 'products' ) { this.menu.category = itab; }
        if( itab != null && this.menu.sections._tabs.selected == 'inventory' ) { this.menu.category = itab; }
        if( this.menu.sections._tabs.selected == 'inventory' ) {
            this.menu.size = 'large narrowaside';
        } else if( this.menu.sections._tabs.selected == 'products' ) {
            this.menu.size = 'medium narrowaside';
        } else {
            this.menu.size = 'medium';
        }
        args = {'business_id':M.curBusinessID};
        method = '';
        switch( this.menu.sections._tabs.selected ) {
            case 'inventory': method = 'ciniki.herbalist.productVersionList'; break;
            case 'products': method = 'ciniki.herbalist.productList'; break;
            case 'recipes': method = 'ciniki.herbalist.recipeList'; break;
            case 'ingredients': method = 'ciniki.herbalist.ingredientList'; break;
            case 'containers': method = 'ciniki.herbalist.containerList'; break;
        }
        if( this.menu.sections._tabs.selected == 'products' || this.menu.sections._tabs.selected == 'inventory' ) {
            args['category'] = this.menu.category;
        }
        if( this.menu.sections._tabs.selected == 'ingredients' ) {
            args['sorttype'] = this.menu.sections._ingredient_tabs.selected;
        }
        M.api.getJSONCb(method, args, function(rsp) {
            if( rsp.stat != 'ok' ) {
                M.api.err(rsp);
                return false;
            }
            var p = M.ciniki_herbalist_main.menu;
            p.data = rsp;
            p.refresh();
            p.show(cb);
        });
	};
};
