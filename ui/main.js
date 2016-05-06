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
			'mc', 'medium', 'sectioned', 'ciniki.herbalist.main.menu');
		this.menu.sections = {
            '_tabs':{'label':'', 'type':'paneltabs', 'selected':'containers', 'tabs':{
                'products':{'label':'Products', 'fn':'M.ciniki_herbalist_main.menuShow(null,"products");'},
                'recipes':{'label':'Recipes', 'fn':'M.ciniki_herbalist_main.menuShow(null,"recipes");'},
                'ingredients':{'label':'Ingredients', 'fn':'M.ciniki_herbalist_main.menuShow(null,"ingredients");'},
                'containers':{'label':'Containers', 'fn':'M.ciniki_herbalist_main.menuShow(null,"containers");'},
                }},
            'products':{'label':'Products', 'type':'simplegrid', 'num_cols':1, 
                'visible':function() {return M.ciniki_herbalist_main.menu.sections._tabs.selected=='products'?'yes':'no';},
                'headerValues':['Name'],
                'cellClasses':[''],
                'noData':'No Products',
                'addTxt':'Add Product',
                'addFn':'',
                },
            'recipes':{'label':'Recipes', 'type':'simplegrid', 'num_cols':1, 
                'visible':function() {return M.ciniki_herbalist_main.menu.sections._tabs.selected=='recipes'?'yes':'no';},
                'headerValues':['Name'],
                'cellClasses':[''],
                'noData':'No Recipes',
                'addTxt':'Add Recipe',
                'addFn':'',
                },
            'ingredients':{'label':'Ingredients', 'type':'simplegrid', 'num_cols':1, 
                'visible':function() {return M.ciniki_herbalist_main.menu.sections._tabs.selected=='ingredients'?'yes':'no';},
                'headerValues':['Name'],
                'cellClasses':[''],
                'noData':'No Ingredients',
                'addTxt':'Add Ingredients',
                'addFn':'',
                },
            'containers':{'label':'Containers', 'type':'simplegrid', 'num_cols':2, 
                'visible':function() {return M.ciniki_herbalist_main.menu.sections._tabs.selected=='containers'?'yes':'no';},
                'headerValues':['Name', '$/Unit'],
                'cellClasses':['', ''],
                'noData':'No Containers',
                'addTxt':'Add Container',
                'addFn':'M.ciniki_herbalist_main.container.edit(0);',
                },
		};
		this.menu.sectionData = function(s) {
			return this.data[s];
		};
		this.menu.noData = function(s) { return this.sections[s].noData; }
		this.menu.cellValue = function(s, i, j, d) {
            if( s == 'products' ) {
                switch (j) {
                    case 0: return d.name;
                }
            } else if( s == 'recipes' ) {
                switch (j) {
                    case 0: return d.name;
                }
            } else if( s == 'ingredients' ) {
                switch (j) {
                    case 0: return d.name;
                }
            } else if( s == 'containers' ) {
                switch (j) {
                    case 0: return d.name;
                    case 1: return d.cost_per_unit_display;
                }
            }
		};
		this.menu.rowFn = function(s, i, d) {
            if( s == 'products' ) {
                return 'M.ciniki_herbalist_main.productShow(\'M.ciniki_herbalist_main.menuShow();\',\'' + d.id + '\');';
            } else if( s == 'recipes' ) {
                return 'M.ciniki_herbalist_main.recipeShow(\'M.ciniki_herbalist_main.menuShow();\',\'' + d.id + '\');';
            } else if( s == 'ingredients' ) {
                return 'M.ciniki_herbalist_main.ingredientShow(\'M.ciniki_herbalist_main.menuShow();\',\'' + d.id + '\');';
            } else if( s == 'containers' ) {
                return 'M.ciniki_herbalist_main.container.edit(\'M.ciniki_herbalist_main.menuShow();\',\'' + d.id + '\');';
            }
		};
		this.menu.addClose('Back');

		//
		// The profile panel 
		//
		this.artist = new M.panel('Artist Profile',
			'ciniki_herbalist_main', 'artist',
			'mc', 'medium mediumaside', 'sectioned', 'ciniki.herbalist.main.artist');
		this.artist.data = {};
		this.artist.artist_id = 0;
		this.artist.sections = {
            '_image':{'label':'', 'aside':'yes', 'type':'imageform', 'fields':{
                'primary_image_id':{'label':'', 'type':'image_id', 'hidelabel':'yes', 'history':'no'},
            }},
			'_caption':{'label':'', 'aside':'yes', 'visible':function() {return M.ciniki_herbalist_main.artist.data.primary_image_caption!=''?'yes':'no';}, 'list':{
				'primary_image_caption':{'label':'Caption', 'type':'text'},
				}},
			'info':{'label':'Service', 'aside':'yes', 'list':{
				'name':{'label':'Name'},
				'subname':{'label':'Sub Name'},
				'status_text':{'label':'Status'},
				'flags_text':{'label':'Options'},
                'categories':{'label':'Categories'},
            }},
//            '_tabs':{'label':'', 'type':'paneltabs', 'selected':'recent', 'tabs':{
//                'bio':{'label':'Overview', 'fn':'M.ciniki_herbalist_main.artistShow(null,null,"recent");'},
//                'setup':{'label':'Trades', 'fn':'M.ciniki_herbalist_main.artistShow(null,null,"trades");'},
//            }},
            'synopsis':{'label':'Synopsis', 'type':'htmlcontent'},
            'description':{'label':'Bio', 'type':'htmlcontent'},
			'images':{'label':'Gallery', 'type':'simplethumbs'},
			'_images':{'label':'', 'type':'simplegrid', 'num_cols':1,
				'addTxt':'Add Image',
				'addFn':'M.startApp(\'ciniki.herbalist.images\',null,\'M.ciniki_herbalist_main.artistShow();\',\'mc\',{\'artist_id\':M.ciniki_herbalist_main.artist.artist_id,\'add\':\'yes\'});',
				},
            'videos':{'label':'Videos', 'type':'simplegrid', 'num_cols':1,
                'cellClasses':['multiline'],
                'addTxt':'Add Video',
				'addFn':'M.startApp(\'ciniki.herbalist.links\',null,\'M.ciniki_herbalist_main.artistShow();\',\'mc\',{\'artist_id\':M.ciniki_herbalist_main.artist.artist_id,\'add\':\'yes\'});',
                },
//            'audio':
            'links':{'label':'Links', 'type':'simplegrid', 'num_cols':1,
                'cellClasses':['multiline'],
                'addTxt':'Add Link',
				'addFn':'M.startApp(\'ciniki.herbalist.links\',null,\'M.ciniki_herbalist_main.artistShow();\',\'mc\',{\'artist_id\':M.ciniki_herbalist_main.artist.artist_id,\'add\':\'yes\'});',
                },
            '_buttons':{'label':'', 'buttons':{
                
                }},
		};
		this.artist.sectionData = function(s) {
            if( s == 'info' || s == '_caption' ) { return this.sections[s].list; }
            if( s == 'synopsis' || s == 'description' ) { return this.data[s].replace(/\n/g, '<br/>'); }
			return this.data[s];
		};
        this.artist.noData = function(s) {
            if( this.sections[s].noData != null ) { return this.sections[s].noData; }
            return null;
        }
        this.artist.listLabel = function(s, i, d) {
            return d.label;
        };
		this.artist.listValue = function(s, i, d) {
            return this.data[i];
		};
        this.artist.fieldValue = function(s, i, d) {
            return this.data[i];
        }
        this.artist.cellValue = function(s, i, j, d) {
            if( s == 'videos' || s == 'links' ) {
                return '<span class="maintext">' + d.name + '</span><span class="subtext">' + d.url + '</span>';
            }
        };
        this.artist.rowFn = function(s, i, d) {
            if( s == 'videos' || s == 'links' ) {
                return 'M.startApp(\'ciniki.herbalist.links\',null,\'M.ciniki_herbalist_main.artistShow();\',\'mc\',{\'link_id\':\'' + d.id + '\'});';
            }
            return '';
        };
		this.artist.thumbFn = function(s, i, d) {
			return 'M.startApp(\'ciniki.herbalist.images\',null,\'M.ciniki_herbalist_main.artistShow();\',\'mc\',{\'artist_image_id\':\'' + d.id + '\'});';
		};
        this.artist.addButton('edit', 'Edit', 'M.ciniki_herbalist_main.artistEdit(\'M.ciniki_herbalist_main.artistShow();\',M.ciniki_herbalist_main.artist.artist_id);');
		this.artist.addClose('Back');

		//
		// The panel for containering an artist
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
                'delete':{'label':'Delete', 'visible':'no', 'fn':'M.ciniki_herbalist_main.container.delete();'},
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
            tp = parseFloat(tp.replace(/[^\d\.]/,''));
            bp = parseFloat(bp.replace(/[^\d\.]/,''));
            var v = 0;
            if( tq != '' && tq > 0 && tp != '' && tp > 0 ) {
                v += (tp/tq);
            }
            if( bq != '' && bq > 0 && bp != '' && bp > 0 ) {
                v += (bp/bq);
            }
            M.gE(this.panelUID + '_cost_per_unit').value = '$' + v.toFixed(2);
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
                if( M.gE(this.panelUID + '_cost_per_unit').value != this.data.cost_per_unit ) {
//                    c += 'cost_per_unit=' + M.gE(this.panelUID + '_cost_per_unit').value + '&';
                }
                console.log(c);
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
                var c = this.serializeForm('no');
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

	this.menuShow = function(cb, tab) {
		this.menu.data = {};
        if( tab != null ) { this.menu.sections._tabs.selected = tab; }
        args = {'business_id':M.curBusinessID};
        method = '';
        switch( this.menu.sections._tabs.selected ) {
            case 'products': method = 'ciniki.herbalist.productList'; break;
            case 'recipes': method = 'ciniki.herbalist.recipeList'; break;
            case 'ingredients': method = 'ciniki.herbalist.ingredientList'; break;
            case 'containers': method = 'ciniki.herbalist.containerList'; break;
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

	this.artistShow = function(cb, sid) {
		if( sid != null ) { this.artist.artist_id = sid; }
        var args = {'business_id':M.curBusinessID, 'artist_id':this.artist.artist_id, 'images':'yes', 'audio':'yes', 'links':'yes', 'videos':'yes'};
		M.api.getJSONCb('ciniki.herbalist.artistGet', args, function(rsp) {
            if( rsp.stat != 'ok' ) {
                M.api.err(rsp);
                return false;
            }
            var p = M.ciniki_herbalist_main.artist;
            p.data = rsp.artist;
            p.refresh();
            p.show(cb);
        });
	};

	this.artistEdit = function(cb, aid) {
		this.edit.reset();
		if( aid != null ) { this.edit.artist_id = aid; }
		this.edit.sections._buttons.buttons.delete.visible = (this.edit.artist_id>0?'yes':'no');
        M.api.getJSONCb('ciniki.herbalist.artistGet', {'business_id':M.curBusinessID, 'artist_id':this.edit.artist_id, 'categories':'yes'}, function(rsp) {
            if( rsp.stat != 'ok' ) {
                M.api.err(rsp);
                return false;
            }
            var p = M.ciniki_herbalist_main.edit;
            p.data = rsp.artist;
            p.sections._categories.fields.categories.tags = [];
            if( rsp.categories != null ) {
                for(i in rsp.categories) {
                    p.sections._categories.fields.categories.tags.push(rsp.categories[i].tag.name);
                }
            }
            p.refresh();
            p.show(cb);
        });
	};

	this.artistSave = function() {
		if( this.edit.artist_id > 0 ) {
			var c = this.edit.serializeForm('no');
			if( c != '' ) {
				M.api.postJSONCb('ciniki.herbalist.artistUpdate', {'business_id':M.curBusinessID, 'artist_id':M.ciniki_herbalist_main.edit.artist_id}, c,
					function(rsp) {
						if( rsp.stat != 'ok' ) {
							M.api.err(rsp);
							return false;
						} 
					M.ciniki_herbalist_main.edit.close();
					});
			} else {
				this.edit.close();
			}
		} else {
			var c = this.edit.serializeForm('yes');
            M.api.postJSONCb('ciniki.herbalist.artistAdd', {'business_id':M.curBusinessID}, c, function(rsp) {
                if( rsp.stat != 'ok' ) {
                    M.api.err(rsp);
                    return false;
                } 
                if( rsp.id > 0 ) {
                    var cb = M.ciniki_herbalist_main.edit.cb;
                    M.ciniki_herbalist_main.edit.close();
                    M.ciniki_herbalist_main.artistShow(cb,rsp.id);
                } else {
                    M.ciniki_herbalist_main.edit.close();
                }
            });
		}
	};

	this.artistDelete = function() {
		if( confirm("Are you sure you want to remove '" + this.edit.data.name + "'?") ) {
			M.api.getJSONCb('ciniki.herbalist.artistDelete', 
				{'business_id':M.curBusinessID, 'artist_id':M.ciniki_herbalist_main.edit.artist_id}, function(rsp) {
					if( rsp.stat != 'ok' ) {
						M.api.err(rsp);
						return false;
					}
					M.ciniki_herbalist_main.artist.close();
				});
		}
	};
};
