//
function ciniki_herbalist_settings() {
    //
    // The main panel, which lists the options for production
    //
    this.main = new M.panel('Settings',
        'ciniki_herbalist_settings', 'main',
        'mc', 'medium', 'sectioned', 'ciniki.herbalist.settings.main');
    this.main.sections = {
        'herbalist':{'label':'Herbalist', 'fields':{
            'production-hourly-wage':{'label':'Hourly Wage', 'type':'text', 'size':'small'},
        }},
    };
    this.main.fieldValue = function(s, i, d) { 
        return this.data[i];
    };
    this.main.fieldHistoryArgs = function(s, i) {
        return {'method':'ciniki.herbalist.settingsHistory', 'args':{'business_id':M.curBusinessID, 'setting':i}};
    };
	this.main.open = function(cb) {
		M.api.getJSONCb('ciniki.herbalist.settingsGet', {'business_id':M.curBusinessID}, function(rsp) {
            if( rsp.stat != 'ok' ) {
                M.api.err(rsp);
                return false;
            }
            var p = M.ciniki_herbalist_settings.main;
            p.data = rsp.settings;
            p.refresh();
            p.show(cb);
        });
	}
	this.main.save = function() {
		var c = this.serializeForm('no');
		if( c != '' ) {
			M.api.postJSONCb('ciniki.herbalist.settingsUpdate', {'business_id':M.curBusinessID}, c, function(rsp) {
                if( rsp.stat != 'ok' ) {
                    M.api.err(rsp);
                    return false;
                } 
                M.ciniki_herbalist_settings.main.close();
            });
		} else {
            M.ciniki_herbalist_settings.main.close();
        }
	}
    this.main.addButton('save', 'Save', 'M.ciniki_herbalist_settings.main.save();');
    this.main.addClose('Cancel');

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
		var appContainer = M.createContainer(appPrefix, 'ciniki_herbalist_settings', 'yes');
		if( appContainer == null ) {
			alert('App Error');
			return false;
		} 

		this.main.open(cb);
	}
}
