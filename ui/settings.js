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
        'labelnames':{'label':'Label Names', 'fields':{
            'labels-avery5167-name':{'label':'1/2" x 1 3/4"', 'type':'text'},
            'labels-avery5160-name':{'label':'1" x 2 5/8"', 'type':'text'},
        }},
    };
    this.main.fieldValue = function(s, i, d) { 
        return this.data[i];
    };
    this.main.fieldHistoryArgs = function(s, i) {
        return {'method':'ciniki.herbalist.settingsHistory', 'args':{'tnid':M.curTenantID, 'setting':i}};
    };
    this.main.open = function(cb) {
        M.api.getJSONCb('ciniki.herbalist.settingsGet', {'tnid':M.curTenantID}, function(rsp) {
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
            M.api.postJSONCb('ciniki.herbalist.settingsUpdate', {'tnid':M.curTenantID}, c, function(rsp) {
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
            M.alert('App Error');
            return false;
        } 

        this.main.open(cb);
    }
}
