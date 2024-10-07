Ext.define('snap.view.vaultitem.CommonVaultItemSummary', { // _CHANGE
    extend: 'Ext.panel.Panel',
    xtype: 'commonvaultitem-summary', // _CHANGE
    requires: [
        'Ext.layout.container.Border',
        'Ext.chart.*'
    ],
    profiles: {
        classic: {
            itemHeight: 100
        },
        neptune: {
            itemHeight: 100
        },
        graphite: {
            itemHeight: 120
        },
        'classic-material': {
            itemHeight: 120
        }
    },
    // type: 'common',
    name: 'Ace G4S',
    layout: 'border',
    width: 500,
    height: 250,
    //cls: [Ext.baseCSSPrefix + 'shadow', 'vaultranscontainerwrap'],

    bodyBorder: false,

    defaults: {
        collapsible: true,
        split: true,
        bodyPadding: 10
    },
    viewModel: {
        data: {
            withdoserialnumbers: [],
            withoutdoserialnumbers: [],
            transferringserialnumbers: [],
            permissions: [],
            acehqserialnumbers: [],
            aceg4sserialnumbers: [],
            mibg4sserialnumbers: [],
            totalserialnumbers: [],
            status: '',
            element: '',

        }
    },
    listeners: {
        afterrender: function () {
            elmnt = this;
            vmv = this.getViewModel();

            // Get the function type
            originType = this.type;
            partnerCode = this.name;

            logicalcount = originType + "logicalcount";
            reservedcount = originType + "reservedcount";
            g4scount = originType + "g4scount";
            totalcount = originType + "totalcount";
            overallcount = originType + "overallcount";

            pointer = originType + "pointer";
            // Set Windows

            windowforlogicalcount = originType + "windowforlogicalcount";


            var panel = this;

            //date = data.createdon.date;
            //filtereddate = Ext.Date.format(new Date(date), "Y-m-d");
            panel.removeAll();

            // Design all here
            // Start Design 
            // ****************************************************************************************** //
            panel.add(
                {
                    title: 'Kilobar Inventory By Warehouse Location',
                    header: {
                        style: 'background-color: #204A6D;border-color: #204A6D;',
                    },
                    region: 'center',
                    layout: 'vbox',
                    width: '100%',
                    items: [
                        {
                            layout: 'hbox',
                            width: '100%',
                            items: [
                                {
                                    layout: 'vbox',
                                    width: '40.2%',
                                    style: {
                                        'margin': '5px 5px 0px 0px',
                                    },
                                    items: [
                                        {
                                            html: '<div style="line-height: 10px;background:#204A6D;padding:5px;text-align:center"><span style="color:#ffffff;width:100%;">TAIPAN</span></div>',
                                            width: '100%',
                                        },

                                    ]
                                },
                                {
                                    layout: 'vbox',
                                    width: '19.8%',
                                    style: {
                                        'margin': '5px 5px 0px 0px',
                                    },
                                    items: [
                                        {
                                            html: '<div style="line-height: 10px;background:#204A6D;padding:5px;text-align:center"><span style="color:#ffffff;width:100%;">'+partnerCode+'</span></div>',
                                            width: '100%',
                                        },

                                    ]
                                },
                                {
                                    layout: 'vbox',
                                    width: '40.3%',
                                    style: {
                                        'margin': '5px 5px 0px 0px',
                                    },
                                    items: [
                                        {
                                            html: '<div style="line-height: 10px;background:#204A6D;padding:5px;text-align:center"><span style="color:#ffffff;width:100%;">TOTAL</span></div>',
                                            width: '100%',
                                        },

                                    ]
                                },
                            ]
                        },
                        {
                            layout: 'hbox',
                            width: '100%',
                            items: [
                                {
                                    layout: 'vbox',
                                    width: '20%',
                                    style: {
                                        'margin': '5px 5px 0px 0px',
                                    },
                                    items: [
                                        {
                                            html: '<div style="text-align: center;vertical-align: middle;line-height: 40px;background:#988c59"><span style="color:#ffffff;font-size:3em;font-weight:900;width:100%" id="'+logicalcount+'">-</span><div style="color:#ffffff;font-size:1.3em;">LOGICAL</div></div>',
                                            width: '100%',
                                        },

                                    ],
                                    listeners : {
                                        render: function(p) {
                                            var theElem = p.getEl();
                                            withoutserialnumber = 0;
                                            var theTip = Ext.create('Ext.tip.Tip', {
                                                html:  '<div>Click to view all Serial Numbers with <span span style="color:#ffffff;font-weight:900;">Delivery Order Number</span>&nbsp;</div>',
                                                style: {
        
                                                },
                                                margin: '520 0 0 520',
                                                shadow: false,
                                                maxHeight: 400,
                                            });
                                        },
                                        click: {
                                                element: 'el', //bind to the underlying el property on the panel
                                                fn: function(){ 
                                                    var windowforlogicalcount = new Ext.Window({
                                                        iconCls: 'x-fa fa-cube',
                                                        xtype: 'form',
                                                        header: {
                                                            // Custom style for Migasit
                                                            /*style: {
                                                                backgroundColor: '#204A6D',
                                                            },*/
                                                            style : 'background-color: #204A6D;border-color: #204A6D;',
                                                        },
                                                        scrollable: true,
                                                        title: 'Serial Numbers',
                                                        layout: 'fit',
                                                        width: 400,
                                                        height: 600,
                                                        maxHeight: 2000,
                                                        modal: true,
                                                        //closeAction: 'destroy',
                                                        plain: true,
                                                        buttonAlign: 'center',
                                                        items: [
                                                           {   
                                                                title: '<h1 style="font: 900 20px/30px Open Sans, Helvetica Neue, helvetica, arial, verdana, sans-serif;color:#404040;">Serial Numbers</h1>',
                                                                header: {
                                                                    style: {
                                                                        backgroundColor: 'white',
                                                                        display: 'inline-block',
                                                                        color: '#000000',
                                                                        
                                                                    }
                                                                },
                                                                style: 'opacity: 1.0;background: linear-gradient(269deg, #b08f26 0%, #d4af37 100%);color: #000000;box-sizing: border-box;font-weight: bold;text-transform: uppercase;letter-spacing: 1px;',
                                                                //title: 'Ask',
                                                                flex: 3,
                                                                scrollable: true,
                                                                margin: '0 10 0 0',
                                                                items: [
                                                                    {
                                                                        xtype: 'container',
                                                                        items: [{
                                                                            id: 'windowforlogicalcount',
                                                                        }]
                                                               
                                                                    }
                                                                ]
                                                            },
                                                        ],
                                                        buttons: [{
                                                            text: 'OK',
                                                            handler: function(btn) {
                                                                
                                                                owningWindow = btn.up('window');
                                                                //owningWindow.closeAction='destroy';
                                                                owningWindow.close();
                                                            } 
                                                        },],
                                                        closeAction: 'destroy',
                                                        //items: spotpanelbuytotalxauweight
                                                    });
                                                    
                                                    
                                                    if(vmv.get(logicalcount).length != 0){
                                                        windowforlogicalcount.show();
                                                    
                                                
                                                        element = vmv.get(pointer);
                                                        var panel = Ext.getCmp('windowforlogicalcount');
        
                                                        //date = data.createdon.date;
                                                        //filtereddate = Ext.Date.format(new Date(date), "Y-m-d");
                                                        panel.removeAll();
                                                        vmv.get(logicalcount).map((x) => {
                                    
                                                        panel.add(element.serialnoTemplateWithoutDO(x))
                                                        })
                                                    }else {
                                                        Ext.MessageBox.show({
                                                            title: 'Alert',
                                                            msg: 'No records are available',
                                                            buttons: Ext.MessageBox.OK,
                                                            icon: Ext.MessageBox.WARNING,
                                                        });
                                                        Ext.getCmp('windowforlogicalcount').destroy();
                                                    }
                                                 
                                                   
                                                }
                                            },
                                    }
                                },
                                {
                                    layout: 'vbox',
                                    width: '20%',
                                    style: {
                                        'margin': '5px 5px 0px 0px',
                                    },
                                    items: [
                                        {
                                            html: '<div style="text-align: center;vertical-align: middle;line-height: 40px;background:#0D47A1"><span style="color:#ffffff;font-size:3em;font-weight:900;width:100%" id="'+reservedcount+'">-</span><div style="color:#ffffff;font-size:1.3em;">RESERVED</div></div>',
                                            width: '100%',
                                        },

                                    ],
                                    listeners : {
                                        render: function(p) {
                                            var theElem = p.getEl();
                                            withoutserialnumber = 0;
                                            var theTip = Ext.create('Ext.tip.Tip', {
                                                html:  '<div>Click to view all Serial Numbers with <span span style="color:#ffffff;font-weight:900;">Delivery Order Number</span>&nbsp;</div>',
                                                style: {
        
                                                },
                                                margin: '520 0 0 520',
                                                shadow: false,
                                                maxHeight: 400,
                                            });
                                        },
                                        click: {
                                                element: 'el', //bind to the underlying el property on the panel
                                                fn: function(){ 
                                                    var windowforreservedcount = new Ext.Window({
                                                        iconCls: 'x-fa fa-cube',
                                                        xtype: 'form',
                                                        header: {
                                                            // Custom style for Migasit
                                                            /*style: {
                                                                backgroundColor: '#204A6D',
                                                            },*/
                                                            style : 'background-color: #204A6D;border-color: #204A6D;',
                                                        },
                                                        scrollable: true,
                                                        title: 'Serial Numbers',
                                                        layout: 'fit',
                                                        width: 400,
                                                        height: 600,
                                                        maxHeight: 2000,
                                                        modal: true,
                                                        //closeAction: 'destroy',
                                                        plain: true,
                                                        buttonAlign: 'center',
                                                        items: [
                                                           {   
                                                                title: '<h1 style="font: 900 20px/30px Open Sans, Helvetica Neue, helvetica, arial, verdana, sans-serif;color:#404040;">Serial Numbers</h1>',
                                                                header: {
                                                                    style: {
                                                                        backgroundColor: 'white',
                                                                        display: 'inline-block',
                                                                        color: '#000000',
                                                                        
                                                                    }
                                                                },
                                                                style: 'opacity: 1.0;background: linear-gradient(269deg, #b08f26 0%, #d4af37 100%);color: #000000;box-sizing: border-box;font-weight: bold;text-transform: uppercase;letter-spacing: 1px;',
                                                                //title: 'Ask',
                                                                flex: 3,
                                                                scrollable: true,
                                                                margin: '0 10 0 0',
                                                                items: [
                                                                    {
                                                                        xtype: 'container',
                                                                        items: [{
                                                                            id: 'windowforreservedcount',
                                                                        }]
                                                               
                                                                    }
                                                                ]
                                                            },
                                                        ],
                                                        buttons: [{
                                                            text: 'OK',
                                                            handler: function(btn) {
                                                                
                                                                owningWindow = btn.up('window');
                                                                //owningWindow.closeAction='destroy';
                                                                owningWindow.close();
                                                            } 
                                                        },],
                                                        closeAction: 'destroy',
                                                        //items: spotpanelbuytotalxauweight
                                                    });
                                                    
                                                    
                                                    if(vmv.get(reservedcount).length != 0){
                                                        windowforreservedcount.show();
                                                    
                                                
                                                        element = vmv.get(pointer);
                                                        var panel = Ext.getCmp('windowforreservedcount');
        
                                                        //date = data.createdon.date;
                                                        //filtereddate = Ext.Date.format(new Date(date), "Y-m-d");
                                                        panel.removeAll();
                                                        vmv.get(reservedcount).map((x) => {
                                    
                                                        panel.add(element.serialnoTemplateWithDO(x))
                                                        })
                                                    }else {
                                                        Ext.MessageBox.show({
                                                            title: 'Alert',
                                                            msg: 'No records are available',
                                                            buttons: Ext.MessageBox.OK,
                                                            icon: Ext.MessageBox.WARNING,
                                                        });
                                                        Ext.getCmp('windowforreservedcount').destroy();
                                                    }
                                                 
                                                   
                                                }
                                            },
                                    }
                                },
                                {
                                    layout: 'vbox',
                                    width: '20%',
                                    style: {
                                        'margin': '5px 5px 0px 0px',
                                    },
                                    items: [
                                        {
                                            html: '<div style="text-align: center;vertical-align: middle;line-height: 40px;background:#ffb91b"><span style="color:#ffffff;font-size:3em;font-weight:900;width:100%" id="'+g4scount+'">-</span><div style="color:#ffffff;font-size:1.3em;">'+partnerCode+'</div></div>',
                                            width: '100%',
                                        },

                                    ],
                                    listeners : {
                                        render: function(p) {
                                            var theElem = p.getEl();
                                            withoutserialnumber = 0;
                                            var theTip = Ext.create('Ext.tip.Tip', {
                                                html:  '<div>Click to view all Serial Numbers with <span span style="color:#ffffff;font-weight:900;">Delivery Order Number</span>&nbsp;</div>',
                                                style: {
        
                                                },
                                                margin: '520 0 0 520',
                                                shadow: false,
                                                maxHeight: 400,
                                            });
                                        },
                                        click: {
                                                element: 'el', //bind to the underlying el property on the panel
                                                fn: function(){ 
                                                    var windowforg4scount = new Ext.Window({
                                                        iconCls: 'x-fa fa-cube',
                                                        xtype: 'form',
                                                        header: {
                                                            // Custom style for Migasit
                                                            /*style: {
                                                                backgroundColor: '#204A6D',
                                                            },*/
                                                            style : 'background-color: #204A6D;border-color: #204A6D;',
                                                        },
                                                        scrollable: true,
                                                        title: 'Serial Numbers',
                                                        layout: 'fit',
                                                        width: 400,
                                                        height: 600,
                                                        maxHeight: 2000,
                                                        modal: true,
                                                        //closeAction: 'destroy',
                                                        plain: true,
                                                        buttonAlign: 'center',
                                                        items: [
                                                           {   
                                                                title: '<h1 style="font: 900 20px/30px Open Sans, Helvetica Neue, helvetica, arial, verdana, sans-serif;color:#404040;">Serial Numbers</h1>',
                                                                header: {
                                                                    style: {
                                                                        backgroundColor: 'white',
                                                                        display: 'inline-block',
                                                                        color: '#000000',
                                                                        
                                                                    }
                                                                },
                                                                style: 'opacity: 1.0;background: linear-gradient(269deg, #b08f26 0%, #d4af37 100%);color: #000000;box-sizing: border-box;font-weight: bold;text-transform: uppercase;letter-spacing: 1px;',
                                                                //title: 'Ask',
                                                                flex: 3,
                                                                scrollable: true,
                                                                margin: '0 10 0 0',
                                                                items: [
                                                                    {
                                                                        xtype: 'container',
                                                                        items: [{
                                                                            id: 'windowforg4scount',
                                                                        }]
                                                               
                                                                    }
                                                                ]
                                                            },
                                                        ],
                                                        buttons: [{
                                                            text: 'OK',
                                                            handler: function(btn) {
                                                                
                                                                owningWindow = btn.up('window');
                                                                //owningWindow.closeAction='destroy';
                                                                owningWindow.close();
                                                            } 
                                                        },],
                                                        closeAction: 'destroy',
                                                        //items: spotpanelbuytotalxauweight
                                                    });
                                                    
                                                    
                                                    if(vmv.get(g4scount).length != 0){
                                                        windowforg4scount.show();
                                                    
                                                
                                                        element = vmv.get(pointer);
                                                        var panel = Ext.getCmp('windowforg4scount');
        
                                                        //date = data.createdon.date;
                                                        //filtereddate = Ext.Date.format(new Date(date), "Y-m-d");
                                                        panel.removeAll();
                                                        vmv.get(g4scount).map((x) => {
                                    
                                                        panel.add(element.serialnoTemplateWithDO(x))
                                                        })
                                                    }else {
                                                        Ext.MessageBox.show({
                                                            title: 'Alert',
                                                            msg: 'No records are available',
                                                            buttons: Ext.MessageBox.OK,
                                                            icon: Ext.MessageBox.WARNING,
                                                        });
                                                        Ext.getCmp('windowforg4scount').destroy();
                                                    }
                                                 
                                                   
                                                }
                                            },
                                    }
                                },
                                {
                                    layout: 'vbox',
                                    width: '20%',
                                    style: {
                                        'margin': '5px 5px 0px 0px',
                                    },
                                    items: [
                                        {
                                            html: '<div style="text-align: center;vertical-align: middle;line-height: 40px;background:#B71C1C"><span style="color:#ffffff;font-size:3em;font-weight:900;width:100%" id="'+totalcount+'">-</span><div style="color:#ffffff;font-size:1.3em;">TOTAL</div></div>',
                                            width: '100%',
                                        },

                                    ],
                                    listeners : {
                                        render: function(p) {
                                            var theElem = p.getEl();
                                            withoutserialnumber = 0;
                                            var theTip = Ext.create('Ext.tip.Tip', {
                                                html:  '<div>Click to view all Serial Numbers with <span span style="color:#ffffff;font-weight:900;">Delivery Order Number</span>&nbsp;</div>',
                                                style: {
        
                                                },
                                                margin: '520 0 0 520',
                                                shadow: false,
                                                maxHeight: 400,
                                            });
                                        },
                                        click: {
                                                element: 'el', //bind to the underlying el property on the panel
                                                fn: function(){ 
                                                    var windowfortotalcount = new Ext.Window({
                                                        iconCls: 'x-fa fa-cube',
                                                        xtype: 'form',
                                                        header: {
                                                            // Custom style for Migasit
                                                            /*style: {
                                                                backgroundColor: '#204A6D',
                                                            },*/
                                                            style : 'background-color: #204A6D;border-color: #204A6D;',
                                                        },
                                                        scrollable: true,
                                                        title: 'Serial Numbers',
                                                        layout: 'fit',
                                                        width: 400,
                                                        height: 600,
                                                        maxHeight: 2000,
                                                        modal: true,
                                                        //closeAction: 'destroy',
                                                        plain: true,
                                                        buttonAlign: 'center',
                                                        items: [
                                                           {   
                                                                title: '<h1 style="font: 900 20px/30px Open Sans, Helvetica Neue, helvetica, arial, verdana, sans-serif;color:#404040;">Serial Numbers</h1>',
                                                                header: {
                                                                    style: {
                                                                        backgroundColor: 'white',
                                                                        display: 'inline-block',
                                                                        color: '#000000',
                                                                        
                                                                    }
                                                                },
                                                                style: 'opacity: 1.0;background: linear-gradient(269deg, #b08f26 0%, #d4af37 100%);color: #000000;box-sizing: border-box;font-weight: bold;text-transform: uppercase;letter-spacing: 1px;',
                                                                //title: 'Ask',
                                                                flex: 3,
                                                                scrollable: true,
                                                                margin: '0 10 0 0',
                                                                items: [
                                                                    {
                                                                        xtype: 'container',
                                                                        items: [{
                                                                            id: 'windowfortotalcount',
                                                                        }]
                                                               
                                                                    }
                                                                ]
                                                            },
                                                        ],
                                                        buttons: [{
                                                            text: 'OK',
                                                            handler: function(btn) {
                                                                
                                                                owningWindow = btn.up('window');
                                                                //owningWindow.closeAction='destroy';
                                                                owningWindow.close();
                                                            } 
                                                        },],
                                                        closeAction: 'destroy',
                                                        //items: spotpanelbuytotalxauweight
                                                    });
                                                    
                                                    
                                                    if(vmv.get(totalcount).length != 0){
                                                        windowfortotalcount.show();
                                                    
                                                
                                                        element = vmv.get(pointer);
                                                        var panel = Ext.getCmp('windowfortotalcount');
        
                                                        //date = data.createdon.date;
                                                        //filtereddate = Ext.Date.format(new Date(date), "Y-m-d");
                                                        panel.removeAll();
                                                        vmv.get(totalcount).map((x) => {
                                    
                                                        panel.add(element.serialnoTemplateWithDO(x))
                                                        })
                                                    }else {
                                                        Ext.MessageBox.show({
                                                            title: 'Alert',
                                                            msg: 'No records are available',
                                                            buttons: Ext.MessageBox.OK,
                                                            icon: Ext.MessageBox.WARNING,
                                                        });
                                                        Ext.getCmp('windowfortotalcount').destroy();
                                                    }
                                                 
                                                   
                                                }
                                            },
                                    }
                                },
                                ,
                                {
                                    layout: 'vbox',
                                    width: '20%',
                                    style: {
                                        'margin': '5px 5px 0px 0px',
                                    },
                                    items: [
                                        {
                                            html: '<div style="text-align: center;vertical-align: middle;line-height: 40px;background:#fc4e70"><span style="color:#ffffff;font-size:3em;font-weight:900;width:100%" id="'+overallcount+'">-</span><div style="color:#ffffff;font-size:1.3em;">OVERALL</div></div>',
                                            width: '100%',
                                        },

                                    ],
                                    listeners : {
                                        render: function(p) {
                                            var theElem = p.getEl();
                                            withoutserialnumber = 0;
                                            var theTip = Ext.create('Ext.tip.Tip', {
                                                html:  '<div>Click to view all Serial Numbers with <span span style="color:#ffffff;font-weight:900;">Delivery Order Number</span>&nbsp;</div>',
                                                style: {
        
                                                },
                                                margin: '520 0 0 520',
                                                shadow: false,
                                                maxHeight: 400,
                                            });
                                        },
                                        click: {
                                                element: 'el', //bind to the underlying el property on the panel
                                                fn: function(){ 
                                                    var windowforoverallcount = new Ext.Window({
                                                        iconCls: 'x-fa fa-cube',
                                                        xtype: 'form',
                                                        header: {
                                                            // Custom style for Migasit
                                                            /*style: {
                                                                backgroundColor: '#204A6D',
                                                            },*/
                                                            style : 'background-color: #204A6D;border-color: #204A6D;',
                                                        },
                                                        scrollable: true,
                                                        title: 'Serial Numbers',
                                                        layout: 'fit',
                                                        width: 400,
                                                        height: 600,
                                                        maxHeight: 2000,
                                                        modal: true,
                                                        //closeAction: 'destroy',
                                                        plain: true,
                                                        buttonAlign: 'center',
                                                        items: [
                                                           {   
                                                                title: '<h1 style="font: 900 20px/30px Open Sans, Helvetica Neue, helvetica, arial, verdana, sans-serif;color:#404040;">Serial Numbers</h1>',
                                                                header: {
                                                                    style: {
                                                                        backgroundColor: 'white',
                                                                        display: 'inline-block',
                                                                        color: '#000000',
                                                                        
                                                                    }
                                                                },
                                                                style: 'opacity: 1.0;background: linear-gradient(269deg, #b08f26 0%, #d4af37 100%);color: #000000;box-sizing: border-box;font-weight: bold;text-transform: uppercase;letter-spacing: 1px;',
                                                                //title: 'Ask',
                                                                flex: 3,
                                                                scrollable: true,
                                                                margin: '0 10 0 0',
                                                                items: [
                                                                    {
                                                                        xtype: 'container',
                                                                        items: [{
                                                                            id: 'windowforoverallcount',
                                                                        }]
                                                               
                                                                    }
                                                                ]
                                                            },
                                                        ],
                                                        buttons: [{
                                                            text: 'OK',
                                                            handler: function(btn) {
                                                                
                                                                owningWindow = btn.up('window');
                                                                //owningWindow.closeAction='destroy';
                                                                owningWindow.close();
                                                            } 
                                                        },],
                                                        closeAction: 'destroy',
                                                        //items: spotpanelbuytotalxauweight
                                                    });
                                                    
                                                    
                                                    if(vmv.get(overallcount).length != 0){
                                                        windowforoverallcount.show();
                                                    
                                                
                                                        element = vmv.get(pointer);
                                                        var panel = Ext.getCmp('windowforoverallcount');
        
                                                        //date = data.createdon.date;
                                                        //filtereddate = Ext.Date.format(new Date(date), "Y-m-d");
                                                        panel.removeAll();
                                                        vmv.get(overallcount).map((x) => {
                                    
                                                        panel.add(element.serialnoTemplateWithDO(x))
                                                        })
                                                    }else {
                                                        Ext.MessageBox.show({
                                                            title: 'Alert',
                                                            msg: 'No records are available',
                                                            buttons: Ext.MessageBox.OK,
                                                            icon: Ext.MessageBox.WARNING,
                                                        });
                                                        Ext.getCmp('windowforoverallcount').destroy();
                                                    }
                                                 
                                                   
                                                }
                                            },
                                    }
                                },
                            ]
                        }
                    ]
                },                
              
            )


            // ******************************************************************************************* //
            // End Design
            
            // Set View Settings

            // Get Summary
            /*
            if(this.items.items[1]){
                summary = this.items.items[1];
                summary.setHidden(true);
    
                // Check for type 
                if ("Operator" == snap.getApplication().usertype || "Sale" == snap.getApplication().usertype  || "Trader" == snap.getApplication().usertype ){
                    summary.setHidden(false);
                } 
            }
            
            // Get Total Customer Holding
            if(this.items.items[4]){
                totalCustomerHolding = this.items.items[4];
                totalCustomerHolding.setHidden(true);
    
                // Check for type 
                if ("Operator" == snap.getApplication().usertype || "Sale" == snap.getApplication().usertype  || "Trader" == snap.getApplication().usertype ){
                    totalCustomerHolding.setHidden(false);
                } 
            }*/
            
            snap.getApplication().sendRequest({
                hdl: 'myvaultitem', action: 'getStatusCount',
                partnercode : originType,
            }, 'Fetching data from server....').then(
                function (data) {
                    if (data.success) {
                        Ext.get(logicalcount).dom.innerHTML = data.logicalCount;
                        Ext.get(reservedcount).dom.innerHTML = data.hqCount;
                        Ext.get(g4scount).dom.innerHTML = data.bmmbG4Scount;
                        Ext.get(totalcount).dom.innerHTML = data.total;
                        Ext.get(overallcount).dom.innerHTML = data.overall;


                        vmv.set(logicalcount, data.logicalCountSerialNumbers);
                        vmv.set(reservedcount, data.hqCountSerialNumbers);
                        vmv.set(g4scount, data.bmmbG4ScountSerialNumbers);
                            
                        vmv.set(totalcount, data.totalSerialNumbers);
                        vmv.set(overallcount, data.overallSerialNumbers);
                    
 
                        vmv.set('userpartnerid', data.userpartnerid);

                        vmv.set(pointer, this);

                    }
                })
        }
    },

    
});


serialnoTemplateWithDO = (data) => {
    var returnx = {

        xtype: 'container',
        height: '100%',
        //fieldStyle: 'background-color: #000000; background-image: none;',
        //scrollable: true,
        items: [{
            itemId: 'user_main_fieldset',
            xtype: 'fieldset',
            title: data.name,
            layout: 'hbox',
            defaultType: 'textfield',
            fieldDefaults: {
                anchor: '100%',
                msgTarget: 'side',
                margin: '0 0 5 0',
                width: '100%',
            },
            items: [{
                xtype: 'fieldcontainer',
                layout: 'vbox',
                flex: 2,
                items: [{
                        xtype: 'displayfield',
                        name: 'serialnumber',
                        value: data.name,
                        reference: 'serialno',
                        fieldLabel: 'Serial Number',
                        flex: 1,
                        style: 'padding-left: 20px;padding-right: 20px;',
                        fieldStyle: " background-color: #ffffff ",
                    },
                    {
                        xtype: 'displayfield',
                        name: 'donumber',
                        value: data.deliveryordernumber,
                        reference: 'deliveryorderno',
                        fieldLabel: 'Delivery Order Number',
                        flex: 1,
                        style: 'padding-left: 20px;padding-right: 20px;',
                        fieldStyle: " background-color: #ffffff ",
                    },
                    {
                        xtype: 'displayfield',
                        name: 'allocatedon',
                        value: data.allocatedon,
                        reference: 'allocatedon',
                        fieldLabel: 'Allocated On',
                        flex: 1,
                        style: 'padding-left: 20px;padding-right: 20px;',
                        fieldStyle: " background-color: #fff ",
                    },
                ]
            }, ]
        }, ],


    }

    return returnx
}

serialnoTemplateWithoutDO = (data) => {
    var returnx = {

        xtype: 'container',
        height: 200,
        //fieldStyle: 'background-color: #000000; background-image: none;',
        //scrollable: true,
        items: [{
            itemId: 'user_main_fieldset',
            xtype: 'fieldset',
            title: data.name,
            layout: 'hbox',
            defaultType: 'textfield',
            fieldDefaults: {
                anchor: '100%',
                msgTarget: 'side',
                margin: '0 0 5 0',
                width: '100%',
            },
            items: [{
                xtype: 'fieldcontainer',
                layout: 'vbox',
                flex: 2,
                items: [{
                        xtype: 'displayfield',
                        name: 'serialnumber',
                        value: data.name,
                        reference: 'serialno',
                        fieldLabel: 'Serial Number',
                        flex: 1,
                        style: 'padding-left: 20px;padding-right: 20px;',
                        fieldStyle: " background-color: #ffffff ",
                    },
                    {
                        xtype: 'displayfield',
                        name: 'allocatedon',
                        value: data.allocatedon,
                        reference: 'allocatedon',
                        fieldLabel: 'Allocated On',
                        flex: 1,
                        style: 'padding-left: 20px;padding-right: 20px;',
                        fieldStyle: " background-color: #fff ",
                    },
                ]
            }, ]
        }, ],


    }

    return returnx
}

transferringserialnumbers = (data) => {
    var returnx = {

        xtype: 'container',
        height: 200,
        //fieldStyle: 'background-color: #000000; background-image: none;',
        //scrollable: true,
        items: [{
            itemId: 'user_main_fieldset',
            xtype: 'fieldset',
            title: data.name,
            layout: 'hbox',
            defaultType: 'textfield',
            fieldDefaults: {
                anchor: '100%',
                msgTarget: 'side',
                margin: '0 0 5 0',
                width: '100%',
            },
            items: [{
                    xtype: 'fieldcontainer',
                    layout: 'vbox',
                    flex: 2,
                    items: [{
                            xtype: 'displayfield',
                            name: 'serialnumber',
                            value: data.name,
                            reference: 'serialno',
                            fieldLabel: 'Serial Number',
                            flex: 1,
                            style: 'padding-left: 20px;padding-right: 20px;',
                            fieldStyle: " background-color: #ffffff ",
                        },
                        {
                            xtype: 'displayfield',
                            name: 'allocatedon',
                            value: data.allocatedon,
                            reference: 'allocatedon',
                            fieldLabel: 'Allocated On',
                            flex: 1,
                            style: 'padding-left: 20px;padding-right: 20px;',
                            fieldStyle: " background-color: #fff ",
                        },
                    ]
                },
                {
                    xtype: 'fieldcontainer',
                    layout: 'vbox',
                    flex: 2,
                    items: [{
                            xtype: 'displayfield',
                            name: 'fromlocation',
                            value: data.from,
                            reference: 'from',
                            fieldLabel: 'From',
                            flex: 1,
                            style: 'padding-left: 20px;padding-right: 20px;',
                            fieldStyle: " background-color: #ffffff ",
                        },
                        {
                            xtype: 'displayfield',
                            name: 'tolocation',
                            value: data.to,
                            reference: 'to',
                            fieldLabel: 'To',
                            flex: 1,
                            style: 'padding-left: 20px;padding-right: 20px;',
                            fieldStyle: " background-color: #fff ",
                        },
                    ]
                },
            ]
        }, ],


    }

    return returnx
}

serialnoTemplateInventory = (data) => {
    var returnx = {

        xtype: 'container',
        height: 200,
        //fieldStyle: 'background-color: #000000; background-image: none;',
        //scrollable: true,
        items: [{
            itemId: 'user_main_fieldset',
            xtype: 'fieldset',
            title: data.name,
            layout: 'hbox',
            defaultType: 'textfield',
            fieldDefaults: {
                anchor: '100%',
                msgTarget: 'side',
                margin: '0 0 5 0',
                width: '100%',
            },
            items: [{
                xtype: 'fieldcontainer',
                layout: 'vbox',
                flex: 2,
                items: [{
                        xtype: 'displayfield',
                        name: 'serialnumber',
                        value: data.name,
                        reference: 'serialno',
                        fieldLabel: 'Serial Number',
                        flex: 1,
                        style: 'padding-left: 20px;padding-right: 20px;',
                        fieldStyle: " background-color: #ffffff ",
                    },
                    {
                        xtype: 'displayfield',
                        name: 'allocatedon',
                        value: data.allocatedon,
                        reference: 'allocatedon',
                        fieldLabel: 'Allocated On',
                        flex: 1,
                        style: 'padding-left: 20px;padding-right: 20px;',
                        fieldStyle: " background-color: #fff ",
                    },
                ]
            }, ]
        }, ],


    }

    return returnx
}


serialnoTemplateWithDO = (data) =>
{
  var returnx = {

      xtype: 'container',
      height: '100%',
      //fieldStyle: 'background-color: #000000; background-image: none;',
      //scrollable: true,
      items: [{
        itemId: 'user_main_fieldset',
        xtype: 'fieldset',
        title: data.name,
        layout: 'hbox',
        defaultType: 'textfield',
        fieldDefaults: {
            anchor: '100%',
            msgTarget: 'side',
            margin: '0 0 5 0',
            width: '100%',
        },
        items: [
                  {
                    xtype: 'fieldcontainer',
                    layout: 'vbox',
                    flex: 2,
                    items: [
                      {
                        xtype: 'displayfield', name:'serialnumber', value: data.name, reference: 'serialno', fieldLabel: 'Serial Number', flex: 1, style:'padding-left: 20px;padding-right: 20px;', fieldStyle:" background-color: #ffffff ",
                      },
                      {
                        xtype: 'displayfield', name:'donumber', value: data.deliveryordernumber, reference: 'deliveryorderno', fieldLabel: 'Delivery Order Number', flex: 1, style:'padding-left: 20px;padding-right: 20px;', fieldStyle:" background-color: #ffffff ",
                      },
                      {
                        xtype: 'displayfield', name:'allocatedon', value: data.allocatedon, reference: 'allocatedon', fieldLabel: 'Allocated On', flex: 1, style:'padding-left: 20px;padding-right: 20px;', fieldStyle:" background-color: #fff ",
                      },
                    ]
                },
              ]
    },],


  }

  return returnx
}

serialnoTemplateWithoutDO = (data) =>
{
  var returnx = {

      xtype: 'container',
      height: 200,
      //fieldStyle: 'background-color: #000000; background-image: none;',
      //scrollable: true,
      items: [{
        itemId: 'user_main_fieldset',
        xtype: 'fieldset',
        title: data.name,
        layout: 'hbox',
        defaultType: 'textfield',
        fieldDefaults: {
            anchor: '100%',
            msgTarget: 'side',
            margin: '0 0 5 0',
            width: '100%',
        },
        items: [
                  {
                    xtype: 'fieldcontainer',
                    layout: 'vbox',
                    flex: 2,
                    items: [
                      {
                        xtype: 'displayfield', name:'serialnumber', value: data.name, reference: 'serialno', fieldLabel: 'Serial Number', flex: 1, style:'padding-left: 20px;padding-right: 20px;', fieldStyle:" background-color: #ffffff ",
                      },
                      {
                        xtype: 'displayfield', name:'allocatedon', value: data.allocatedon, reference: 'allocatedon', fieldLabel: 'Allocated On', flex: 1, style:'padding-left: 20px;padding-right: 20px;', fieldStyle:" background-color: #fff ",
                      },
                    ]
                },
              ]
    },],


  }

  return returnx
}

transferringserialnumbers = (data) =>
{
  var returnx = {

      xtype: 'container',
      height: 200,
      //fieldStyle: 'background-color: #000000; background-image: none;',
      //scrollable: true,
      items: [{
        itemId: 'user_main_fieldset',
        xtype: 'fieldset',
        title: data.name,
        layout: 'hbox',
        defaultType: 'textfield',
        fieldDefaults: {
            anchor: '100%',
            msgTarget: 'side',
            margin: '0 0 5 0',
            width: '100%',
        },
        items: [
                  {
                    xtype: 'fieldcontainer',
                    layout: 'vbox',
                    flex: 2,
                    items: [
                      {
                        xtype: 'displayfield', name:'serialnumber', value: data.name, reference: 'serialno', fieldLabel: 'Serial Number', flex: 1, style:'padding-left: 20px;padding-right: 20px;', fieldStyle:" background-color: #ffffff ",
                      },
                      {
                        xtype: 'displayfield', name:'allocatedon', value: data.allocatedon, reference: 'allocatedon', fieldLabel: 'Allocated On', flex: 1, style:'padding-left: 20px;padding-right: 20px;', fieldStyle:" background-color: #fff ",
                      },
                    ]
                },
                {
                    xtype: 'fieldcontainer',
                    layout: 'vbox',
                    flex: 2,
                    items: [
                        {
                            xtype: 'displayfield', name:'fromlocation', value: data.from, reference: 'from', fieldLabel: 'From', flex: 1, style:'padding-left: 20px;padding-right: 20px;', fieldStyle:" background-color: #ffffff ",
                        },
                        {
                        xtype: 'displayfield', name:'tolocation', value: data.to, reference: 'to', fieldLabel: 'To', flex: 1, style:'padding-left: 20px;padding-right: 20px;', fieldStyle:" background-color: #fff ",
                        },
                    ]
                },
              ]
    },],


  }

  return returnx
}

serialnoTemplateInventory = (data) =>
{
  var returnx = {

      xtype: 'container',
      height: 200,
      //fieldStyle: 'background-color: #000000; background-image: none;',
      //scrollable: true,
      items: [{
        itemId: 'user_main_fieldset',
        xtype: 'fieldset',
        title: data.name,
        layout: 'hbox',
        defaultType: 'textfield',
        fieldDefaults: {
            anchor: '100%',
            msgTarget: 'side',
            margin: '0 0 5 0',
            width: '100%',
        },
        items: [
                  {
                    xtype: 'fieldcontainer',
                    layout: 'vbox',
                    flex: 2,
                    items: [
                      {
                        xtype: 'displayfield', name:'serialnumber', value: data.name, reference: 'serialno', fieldLabel: 'Serial Number', flex: 1, style:'padding-left: 20px;padding-right: 20px;', fieldStyle:" background-color: #ffffff ",
                      },
                      {
                        xtype: 'displayfield', name:'allocatedon', value: data.allocatedon, reference: 'allocatedon', fieldLabel: 'Allocated On', flex: 1, style:'padding-left: 20px;padding-right: 20px;', fieldStyle:" background-color: #fff ",
                      },
                    ]
                },
              ]
    },],


  }

  return returnx
}