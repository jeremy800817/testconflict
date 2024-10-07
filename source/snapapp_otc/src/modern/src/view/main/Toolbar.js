Ext.define('snap.view.main.Toolbar', {
    extend: 'Ext.Toolbar',
    xtype: 'maintoolbar',

    requires: [
        'Ext.Button',
		'Ext.MessageBox',		
        'Ext.Img',
        'Ext.SegmentedButton'
    ],

    items: [{
        // This component is moved to the floating nav container by the phone profile
        xtype: 'image',
        reference: 'logo',
        userCls: 'main-logo',
        
        src: '../src/resources/images/logo_normal.png'
    }, {
        ui: 'header',
        iconCls: 'x-fa fa-bars',
        margin: '0 0 0 10',
        listeners: {
            tap: 'onToggleNavigationSize'
        }
    }, '->', {
        xtype: 'segmentedbutton',
        reference: 'toolkitSwitch',
        margin: '0 16 0 0',
        platformConfig: {
            phone: {
                hidden: true
            }
        },
        items: [/* {
            width: 35,
            value: 'classic',
            iconCls: 'x-fa fa-desktop',
            handler: 'onSwitchToClassic'
        }, {
            value: 'modern',
            iconCls: 'x-fa fa-tablet',
            pressed: true
        } */]
    }, {
        ui: 'header',
        iconCls: 'x-fa fa-th-large',
        href: '#home',
        margin: '0 7 0 0',
        handler: 'toolbarButtonClick'
    }, {
        xtype: 'label',
		ui: 'header',
		bind: '{username}' ,                      
		cls: 'top-user-name'
    }, 
	{
		//iconCls:'x-fa fa-power-off',
        //ui: 'header-red',
        html:'<span class="x-fa fa-power-off" style="color:#e44959;font-size:1.7em"> </span>',
		handler: function(btn) {
			Ext.Msg.confirm('Confirm Logout', 'Are you sure you want to logout?', 
				function(buttonId , text) {
					if(buttonId  == "yes") {
						snap.getApplication().sendRequest({hdl: 'logout'}).then(function(data){
							//window.location.reload(true);
							window.location.href = window.location.origin;
						})
					}
				});
		},
		// href: 'index.php?hdl=logout',
		hrefTarget: '_self',
		tooltip: 'Logout'
	}
	]
});
