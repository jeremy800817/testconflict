Ext.define('snap.view.main.Main', {
    extend: 'Ext.navigation.View',

    requires: [
        'Ext.Button',
        'Ext.list.Tree',
        'Ext.navigation.View',
        'snap.view.main.MainController',
        'snap.view.main.MainModel',
    ],

    controller: 'main',
    viewModel: 'main',
    navigationBar: false,
    userCls: 'main-container',

    platformConfig: {
        phone: {
            controller: 'phone-main'
        }
    },

    listeners: {
        render: 'onMainViewRender',
        afterrender: 'onAfterRender'
    },

    items: [{
        xtype: 'maintoolbar',
        docked: 'top',
        userCls: 'main-toolbar',
        style: {
            background: '#eff6fc'
        },
        shadow: true
    },/* {
        xtype: 'label',		
		bind:'<b>{webtrail}</b>',
        cls: 'top-user-name',
        style:{
            margin:'5px',            
        },
        docked:'top'
    }, */

    {
        xtype: 'container',
        docked: 'left',
        userCls: 'main-nav-container',
        reference: 'navigation',
        layout: 'fit',
        items: [
            /*{
                xtype: 'treelist',
                reference: 'navigationTree',
                scrollable: true,
                ui: 'nav',
                store: 'NavigationTree',
                expanderFirst: false,
                expanderOnly: false,
                listeners: {
                    itemclick: 'onNavigationItemClick',
                    selectionchange: 'onNavigationTreeSelectionChange'
                }
            }
            */
            {
                xtype: 'treelist',
                reference: 'navigationTree',
                itemId: 'navigationTree',
                ui: 'nav',
                //scrollable: 'y',
                //store: 'NavigationTree',,
                bind: '{navItems}',
                width: 250,
                expanderFirst: false,
                expanderOnly: false,
                singleExpand: true,
                listeners: {
                    itemclick: 'onNavigationItemClick',
                    selectionchange: 'onNavigationTreeSelectionChange'
                },
                style: { 'overflow-y': 'auto' }
            },
        ]
    }, {
        docked: 'bottom',
        ui: 'footer',
        aign: 'center',
        xtype: 'toolbar',
        cls: 'sencha-dash-dash-footerbar',
        // style: {'height': '15px', 'padding': '2px'}, 
        items: [
            '->', { xtype: 'label', style: { 'font-size': '0.5em' }, bind: { html: '{copyright}' } }, '->'
        ]
    }]
});
