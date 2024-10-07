Ext.define('snap.view.unfulfillpo.UnfulfillPODashboard', {
    extend: 'Ext.panel.Panel',
    xtype: 'unfulfillpodashboardview',
    requires: [
        'snap.view.unfulfillpo.UnfulfillPODashboardController',
    ],
    title: 'Unfulfilled PO',
    formDialogWidth: 950,
    controller: 'unfulfillpodashboard-unfulfillpodashboard',
    viewModel: 'unfulfillpodashboard-unfulfillpodashboard',
    permissionRoot: '/root/gtp/unfulfilledorder',
    layout: 'fit',
    width: '100%',
    //height: 400,
    // cls: Ext.baseCSSPrefix + 'shadow',
    bodyPadding: 25,
    userCls: 'transactionlisting-head',
    viewModel: {
        data: {
            usertype: [],
        }
    },
    initialize: function (formView, form, record, asyncLoadCallback) {            
        elmnt = this;
        vmu = this.getViewModel();

        // Do destruction 
        // if (Ext.getCmp('usertypefortransactionlisting') != null) {
        //     Ext.getCmp('usertypefortransactionlisting').destroy();
        // }
        // if (Ext.getCmp('gtpcustomernamepo') != null) {
        //     Ext.getCmp('gtpcustomernamepo').destroy();
        // }
        // if (Ext.getCmp('labelgtpcustomernamepo') != null) {
        //     Ext.getCmp('labelgtpcustomernamepo').destroy();
        // }
        // if (Ext.getCmp('gtpcustomernametl') != null) {
        //     Ext.getCmp('gtpcustomernametl').destroy();
        // }
        // if (Ext.getCmp('labelgtpcustomernametl') != null) {
        //     Ext.getCmp('labelgtpcustomernametl').destroy();
        // }
        // if (Ext.getCmp('fetchpolistbutton') != null) {
        //     Ext.getCmp('fetchpolistbutton').destroy();
        // }
        // if (Ext.getCmp('gtpcustomernamepo') != null) {
        //     Ext.getCmp('gtpcustomernamepo').destroy();
        // }
        // if (Ext.getCmp('gtpcustomernametl') != null) {
        //     Ext.getCmp('gtpcustomernametl').destroy();
        // }
        // if (Ext.getCmp('unfulfilledjlistpo') != null) {
        //     Ext.getCmp('unfulfilledjlistpo').destroy();
        // }
        snap.getApplication().sendRequest({
            hdl: 'orderdashboard', 'action': 'fillunfulfilled',
            id: 1,
        }, 'Fetching data from server....').then(
            function (data) {
                if (data.success) {
                    // console.log(data.usertype);
                    vmu.set('usertype', data.usertype);
                    // Ext.getCmp('usertypefortransactionlisting').setValue(data.usertype);
                    elmnt.lookupReference('usertypefortransactionlisting').setValue(data.usertype);
                    if (data.usertype == 'Operator' || data.usertype == 'Sale') {
                        // Ext.getCmp('gtpcustomernamepo').setHidden(false);
                        // Ext.getCmp('labelgtpcustomernamepo').setHidden(false);
                        
                        // Ext.getCmp('gtpcustomernametl').setHidden(false);
                        // Ext.getCmp('labelgtpcustomernametl').setHidden(false);
                        
                        // Ext.getCmp('fetchpolistbutton').setHidden(false);
                        // Ext.getCmp('gtpcustomernamepo').getStore().loadData(data.partners);
                        // Ext.getCmp('gtpcustomernametl').getStore().loadData(data.partners);
                        elmnt.lookupReference('gtpcustomernamepo').setHidden(false);
                        elmnt.lookupReference('labelgtpcustomernamepo').setHidden(false);

                        elmnt.lookupReference('gtpcustomernametl').setHidden(false);
                        elmnt.lookupReference('labelgtpcustomernametl').setHidden(false);

                        elmnt.lookupReference('fetchpolistbutton').setHidden(false);
                        elmnt.lookupReference('gtpcustomernamepo').getStore().loadData(data.partners);
                        elmnt.lookupReference('gtpcustomernametl').getStore().loadData(data.partners);
                    } else {
                        // Ext.getCmp('unfulfilledjlistpo').setHidden(false);
                        elmnt.lookupReference('unfulfilledjlistpo').setHidden(false);
                    }
                }
            });
        this.callParent(arguments);
    },
    items: {
        profiles: {
            classic: {
                panel1Flex: 1,
                panelHeight: 100,
                panel2Flex: 2
            },
            neptune: {
                panel1Flex: 1,
                panelHeight: 100,
                panel2Flex: 2
            },
            graphite: {
                panel1Flex: 2,
                panelHeight: 110,
                panel2Flex: 3
            },
            'classic-material': {
                panel1Flex: 2,
                panelHeight: 110,
                panel2Flex: 3
            }
        },
        width: '100%',
        //height: 400,
        //cls: Ext.baseCSSPrefix + 'shadow',
        layout: {
            type: 'vbox',
            pack: 'start',
            align: 'stretch'
        },
        scrollable: true,
        bodyPadding: 10,

        defaults: {
            frame: true,
            bodyPadding: 10
        },
        items: [
            // {
            //     // Style for migasit default
            //     style: {
            //         'border': '2px solid #204A6D',
            //         'padding':'5px'
            //     },
            //     //height: 120,
            //     margin: '0 0 10 0',
            //     items: [{
            //         xtype: 'container',
            //         scrollable: false,
            //         layout: 'vbox',
            //         defaults: {
            //             bodyPadding: '5',
            //             // border: true
            //         },
            //         items: [{
            //             html: '<h1>Unfulfilled PO</h1>',
            //         }]
            //     },]
            // },
            {
                xtype: 'formpanel',
                title: 'Transaction Listing',
                //reference: 'transactionlisting-form',                
                // Custom style
                header: {
                    style: 'color: #fff;',
                    // style: 'background-color: #204A6D;',
                },
                // style: "font-family:'Open Sans', 'Helvetica Neue', helvetica, arial, verdana, sans-serif;",
                border: true,
                margin: '0 0 10 0',
                userCls: 'transactionlisting-wrapper',
                items: [
                    {
                        xtype: 'hiddenfield', reference: 'usertypefortransactionlisting', name: 'usertypefortransactionlisting', reference: 'usertypefortransactionlisting', fieldLabel: 'usertypefortransactionlisting', flex: 1,
                    },
                    {
                        xtype: 'container',
                        layout: 'vbox',
                        items: [
                            { xtype: 'label', html: 'From (Required)' },
                            { xtype: 'datefield', name: 'fromdate', reference: 'fromdate', flex: 1, forceSelection: true, allowBlank: false, },
                            { xtype: 'label', html: 'To (Required)' },
                            { xtype: 'datefield', name: 'todate', reference: 'todate', flex: 1, forceSelection: true, allowBlank: false, },
                            { xtype: 'label', html: 'GTP Customer Name', hidden: true, reference:'labelgtpcustomernametl' },
                            {
                                xtype: 'combobox', flex: 1, hidden: true, store: { type: 'array', fields: ['id', 'name'] }, queryMode: 'local', remoteFilter: false, name: 'gtpcustomernametransactionlisting', valueField: 'id', displayField: 'name', reference: 'gtpcustomernametl', forceSelection: false, editable: true,
                                labelStyle: 'font: 900 13px/22px Open Sans, Helvetica Neue, helvetica, arial, verdana, sans-serif',
                                tpl: [
                                    '<ul class="x-list-plain">',
                                    '<tpl for=".">',
                                    '<li class="',
                                    Ext.baseCSSPrefix, 'grid-group-hd ',
                                    Ext.baseCSSPrefix, 'grid-group-title">{abbr}</li>',
                                    '<li class="x-boundlist-item">',
                                    '{name}',
                                    '</li>',
                                    '</tpl>',
                                    '</ul>'
                                ]
                            },
                            { xtype: 'label', html: 'Type' },
                            {
                                layout: 'hbox',
                                reference: 'type', 
                                fieldLabel: 'Type', 
                                xtype: 'radiogroup', 
                                xtype: 'formpanel', 
                                allowBlank: false,
                                scrollable: false,
                                items: [
                                    // { label: 'My Sells', name: 'type', value: '0', width: '50%' },
                                    // { label: 'My Buys', name: 'type', value: '1', width: '50%' },
                                    {
                                        xtype: 'radiofield',
                                        name : 'type',
                                        value: '0',
                                        label: 'My Buys',
                                        labelAlign: 'right',
                                        checked: true
                                    },
                                    {
                                        xtype: 'radiofield',
                                        name : 'type',
                                        value: '1',
                                        label: 'My Sells',
                                        labelAlign: 'right',
                                    },
                                ]
                                
                            },
                            {
                                layout: 'hbox',
                                items: [
                                    {
                                        xtype: 'button',
                                        // text: '<span style="font: 400 12px/16px Open Sans, Helvetica Neue, helvetica, arial, verdana, sans-serif;color:#23a9df;">7</span>',
                                        text: "7",
                                        flex:1,
                                        userCls: 'transactionlisting-date-buttons',
                                        // style: 'background-color: #4CAF50;border: none;color: white;padding: 15px 32px;text-align: center;text-decoration: none;display: inline-block;font-size: 16px;',
                                        //style: 'opacity: 1.0;background: linear-gradient(269deg, #ffffff 0%, #ffffff 100%);color: #404040;border-color:transparent;box-sizing: border-box;font-weight: bold;text-transform: uppercase;',
                                        handler: function() {                                           
                                            // Get form
                                            form = this.up().up();
                                            console.log(form);
                                            // Create Date object for current date
                                            date = new Date();
                                            // Set Dates
                                            // Set initial date to ToDate
                                            form.lookupController().lookupReference('todate').setValue(date);
                                            // Get Date difference
                                            date.setDate(date.getDate() - 7);
                                            form.lookupController().lookupReference('fromdate').setValue(date);
                                            //form.lookupController().lookupReference('fromdate').getValue()
                                        },
                                        

                                    },
                                    {
                                        xtype: 'button',
                                        //text: 'Submit Form',
                                        // text: '<span style="font: 400 12px/16px Open Sans, Helvetica Neue, helvetica, arial, verdana, sans-serif;color:#23a9df;">14</span>',
                                        text: "14",
                                        flex:1,
                                        userCls: 'transactionlisting-date-buttons',
                                        // style: 'background-color: #4CAF50;border: none;color: white;padding: 15px 32px;text-align: center;text-decoration: none;display: inline-block;font-size: 16px;',
                                        // style: 'opacity: 1.0;background: linear-gradient(269deg, #ffffff 0%, #ffffff 100%);color: #404040;border-color:transparent;box-sizing: border-box;font-weight: bold;text-transform: uppercase;letter-spacing: 1px;',
                                        handler:function(){
                                            // Get form
                                            form = this.up().up();

                                            // Create Date object for current date
                                            date = new Date();

                                            // Set Dates
                                            // Set initial date to ToDate
                                            form.lookupController().lookupReference('todate').setValue(date);

                                            // Get Date difference
                                            date.setDate(date.getDate() - 14);

                                            form.lookupController().lookupReference('fromdate').setValue(date);


                                            //form.lookupController().lookupReference('fromdate').getValue()
                                        }

                                    },
                                    {
                                        xtype: 'button',
                                        //text: 'Submit Form',
                                        // text: '<span style="font: 400 12px/16px Open Sans, Helvetica Neue, helvetica, arial, verdana, sans-serif;color:#23a9df;">30</span>',
                                        text: '30',
                                        flex:1,
                                        userCls: 'transactionlisting-date-buttons',
                                        // style: 'opacity: 1.0;background: linear-gradient(269deg, #ffffff 0%, #ffffff 100%);color: #404040;border-color:transparent;box-sizing: border-box;font-weight: bold;text-transform: uppercase;letter-spacing: 1px;',
                                        handler:function(){
                                            // Get form
                                            form = this.up().up();

                                            // Create Date object for current date
                                            date = new Date();

                                            // Set Dates
                                            // Set initial date to ToDate
                                            form.lookupController().lookupReference('todate').setValue(date);

                                            // Get Date difference
                                            date.setDate(date.getDate() - 30);

                                            form.lookupController().lookupReference('fromdate').setValue(date);


                                            //form.lookupController().lookupReference('fromdate').getValue()
                                        }

                                    },
                                    {
                                        xtype: 'button',
                                        //text: 'Submit Form',
                                        // text: '<span style="font: 400 12px/16px Open Sans, Helvetica Neue, helvetica, arial, verdana, sans-serif;color:#23a9df;">60</span>',
                                        text: '60',
                                        flex:1,
                                        userCls: 'transactionlisting-date-buttons',
                                        // style: 'opacity: 1.0;background: linear-gradient(269deg, #ffffff 0%, #ffffff 100%);color: #404040;border-color:transparent;box-sizing: border-box;font-weight: bold;text-transform: uppercase;letter-spacing: 1px;',
                                        handler:function(){
                                            // Get form
                                            form = this.up().up();

                                            // Create Date object for current date
                                            date = new Date();

                                            // Set Dates
                                            // Set initial date to ToDate
                                            form.lookupController().lookupReference('todate').setValue(date);

                                            // Get Date difference
                                            date.setDate(date.getDate() - 60);

                                            form.lookupController().lookupReference('fromdate').setValue(date);


                                            //form.lookupController().lookupReference('fromdate').getValue()
                                        }

                                    },
                                    {
                                        xtype: 'button',
                                        //text: 'Submit Form',
                                        // text: '<span style="font: 400 12px/16px Open Sans, Helvetica Neue, helvetica, arial, verdana, sans-serif;color:#23a9df;">90</span>',
                                        text: '90',
                                        flex:1,
                                        userCls: 'transactionlisting-date-buttons',
                                        // style: 'opacity: 1.0;background: linear-gradient(269deg, #ffffff 0%, #ffffff 100%);color: #404040;border-color:transparent;box-sizing: border-box;font-weight: bold;text-transform: uppercase;letter-spacing: 1px;',
                                        handler:function(){
                                            // Get form
                                            form = this.up().up();

                                            // Create Date object for current date
                                            date = new Date();

                                            // Set Dates
                                            // Set initial date to ToDate
                                            form.lookupController().lookupReference('todate').setValue(date);

                                            // Get Date difference
                                            date.setDate(date.getDate() - 90);

                                            form.lookupController().lookupReference('fromdate').setValue(date);


                                            //form.lookupController().lookupReference('fromdate').getValue()
                                        }

                                    },
                                    {
                                        xtype: 'button',
                                        //text: 'Submit Form',
                                        // text: '<span style="font: 400 12px/16px Open Sans, Helvetica Neue, helvetica, arial, verdana, sans-serif;color:#23a9df;">180</span>',
                                        text: '180',
                                        flex:1,
                                        userCls: 'transactionlisting-date-buttons',
                                        // style: 'opacity: 1.0;background: linear-gradient(269deg, #ffffff 0%, #ffffff 100%);color: #404040;border-color:transparent;box-sizing: border-box;font-weight: bold;text-transform: uppercase;letter-spacing: 1px;',
                                        handler:function(){
                                            // Get form
                                            form = this.up().up();

                                            // Create Date object for current date
                                            date = new Date();

                                            // Set Dates
                                            // Set initial date to ToDate
                                            form.lookupController().lookupReference('todate').setValue(date);

                                            // Get Date difference
                                            date.setDate(date.getDate() - 180);

                                            form.lookupController().lookupReference('fromdate').setValue(date);


                                            //form.lookupController().lookupReference('fromdate').getValue()
                                        }

                                    },
                                ]
                            },
                            {
                                xtype: 'displayfield',
                                value: "<p style='margin:0'><span style='font-size: 10px;'>&#9679;</span> The listing will be created with your selected dates.</p>",
                                forceSelection: true,
                                enforceMaxLength: true,
                                allowBlank: false,
                                margin: 0,
                                readOnly: true,
                                userCls: 'unfulfilledpo-display-bullet',
                                renderer: function (html) {
                                    this.setHtml(html)
                                }
                            }, {
                                xtype: 'displayfield',
                                value: "<p style='margin:0'><span style='font-size: 10px;'>&#9679;</span> A maximum range of 180 days transactions per request.</p>",
                                forceSelection: true,
                                enforceMaxLength: true,
                                allowBlank: false,
                                margin: '-10 0 0 0',
                                readOnly: true,
                                userCls: 'unfulfilledpo-display-bullet',
                                renderer: function (html) {
                                    this.setHtml(html)
                                }
                            },
                            {
                                xtype: 'displayfield',
                                value: "<p style='margin:0'><span style='font-size: 10px;'>&#9679;</span> Rename the file of your choice ending with .pdf</p>",
                                forceSelection: true,
                                enforceMaxLength: true,
                                allowBlank: false,
                                margin: '-10 0 0 0',
                                readOnly: true,
                                userCls: 'unfulfilledpo-display-bullet',
                                renderer: function (html) {
                                    this.setHtml(html)
                                }
                            }, {
                                xtype: 'displayfield',
                                value: "<p style='margin:0'><span style='font-size: 10px;'>&#9679;</span> GTP does not store transaction statements.</p>",
                                forceSelection: true,
                                enforceMaxLength: true,
                                allowBlank: false,
                                margin: '-10 0 0 0',
                                readOnly: true,
                                userCls: 'unfulfilledpo-display-bullet',
                                renderer: function (html) {
                                    this.setHtml(html)
                                }
                            },
                            {
                                xtype: 'button',
                                text: '<span style="font: 300 13px/22px Open Sans, Helvetica Neue, helvetica, arial, verdana, sans-serif;font-weight:bold;">Fetch</span>',
                                //style: 'border-radius: 20px;opacity: 1.0;background: linear-gradient(269deg, #b08f26 0%, #d4af37 100%);text-color: #000000;text-transform: uppercase;',
                                // style: 'border-radius: 20px;opacity: 1.0;background: linear-gradient(269deg, #204A6D 0%, #204A6D 100%);color: #ffffff;display: inline-block;box-sizing: border-box;color: #ffffff; font-weight: bold;text-transform: uppercase;letter-spacing: 1px;',
                                reference: 'fetchtransactionlisting',
                                handler: 'summaryAction',
                                userCls: 'transactionlisting-buttons',
                                renderer: function (html) {
                                    this.setHtml(html)
                                }

                            }
                        ]
                    },
                ],
            },
            {
                xtype: 'formpanel',
                title: 'Unfulfilled Purchase Orders',
                //reference: 'unfulfilledpolisting-form',
                border: true,
                layout: 'vbox',
                margin: '20 0 10 0',
                // header: {
                //     style: 'background-color: #204A6D;border-color: #204A6D;',
                // },
                style: "font-family:'Open Sans', 'Helvetica Neue', helvetica, arial, verdana, sans-serif;border: 1px solid #5fa2dd",
                userCls: 'transactionlisting-wrapper',
                items: [
                    {
                        xtype: 'label',
                        html: 'GTP Customer Name',
                        hidden: true,
                        reference: 'labelgtpcustomernamepo'
                    },
                    {
                        xtype: 'combobox', hidden: true, store: { type: 'array', fields: ['id', 'name'] }, queryMode: 'local', remoteFilter: false, name: 'gtpcustomernamepurchaseorder', valueField: 'id', displayField: 'name', reference: 'gtpcustomernamepo', forceSelection: false, editable: true,
                        labelStyle: 'font: 900 13px/22px Open Sans, Helvetica Neue, helvetica, arial, verdana, sans-serif',
                        tpl: [
                            '<ul class="x-list-plain">',
                            '<tpl for=".">',
                            '<li class="',
                            Ext.baseCSSPrefix, 'grid-group-hd ',
                            Ext.baseCSSPrefix, 'grid-group-title">{abbr}</li>',
                            '<li class="x-boundlist-item">',
                            '{name}',
                            '</li>',
                            '</tpl>',
                            '</ul>'
                        ]
                    },
                    {
                        xtype: 'button',
                        text: '<span style="font: 300 13px/22px Open Sans, Helvetica Neue, helvetica, arial, verdana, sans-serif;">Fetch</span>',
                        //style: 'border-radius: 20px;opacity: 1.0;background: linear-gradient(269deg, #b08f26 0%, #d4af37 100%);text-color: #000000;text-transform: uppercase;',
                        // style: 'margin-bottom:10px;border-radius: 20px;opacity: 1.0;background: linear-gradient(269deg, #204A6D 0%, #204A6D 100%);color: #ffffff;display: inline-block;box-sizing: border-box;color: #ffffff; font-weight: bold;text-transform: uppercase;letter-spacing: 1px;',
                        reference: 'fetchpolistbutton',
                        // id: 'fetchpolistbutton',
                        handler: 'fetchPOListForCustomer',
                        userCls: 'transactionlisting-buttons',
                        renderer: function (html) {
                            this.setHtml(html)
                        },
                        hidden: true,

                    },
                    {
                        xtype:'container',                        
                        xtype: 'unfulfillpo',                        
                        reference: 'unfulfilledjlistpo',
                        // id: 'unfulfilledjlistpo',                       
                        hidden: true,
                    },
                ],
            }
        ]
    }
});
