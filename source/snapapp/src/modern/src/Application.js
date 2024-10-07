/**
 * The main application class. An instance of this class is created by app.js when it
 * calls Ext.application(). This is the ideal place to handle application launch and
 * initialization details.
 */
Ext.define('snap.Application', {
    extend: 'Ext.app.Application',

    name: 'snap',
	/*
    quickTips: false,
    platformConfig: {
        desktop: {
            quickTips: true
        }
    },
	*/
	profiles: [
        'Phone',
        'Tablet'
    ],

    init : function() {		
        this.username = _sii.a;
        this.permissions = _sii.b;
        this.states = _sii.c;
        this.usertype = _sii.e;
        this.info = _sii.f;
        this.direct = _sii.g;
		/*
        if(Ext.platformTags.classic) {
            //Initialise the state provider.
            var stateProvider = new snap.util.HttpStateProvider({
                app: this
            });
            Ext.state.Manager.setProvider(stateProvider);
        }
		*/
    },

    onAppUpdate: function () {
        Ext.Msg.confirm('Application Update', 'This application has an update, reload?',
            function (choice) {
                if (choice === 'yes') {
                    window.location.reload();
                }
            }
        );
    },
	stores: [
        'NavigationTree'
    ]
});
