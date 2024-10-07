Ext.define('snap.view.orderdashboard.UnfulfillPODashboard',{
    extend: 'Ext.panel.Panel',
    xtype: 'unfulfillpodashboardview',

    requires: [

        'Ext.layout.container.Fit',
        'snap.view.orderdashboard.UnfulfillPODashboardController',

    ],
    formDialogWidth: 950,
    controller: 'unfulfillpodashboard-unfulfillpodashboard',
    permissionRoot: '/root/trading/order',
    layout: 'fit',
    width: 500,
    height: 400,
    cls: Ext.baseCSSPrefix + 'shadow',


    /*
    listeners: {
        afterrender: function(){
            alert("!");
        },
    },*/

    viewModel: {
        data: {
            usertype: [],
        }
    },

    initComponent: function(formView, form, record, asyncLoadCallback){
        elmnt = this;
        vmu = this.getViewModel();
        snap.getApplication().sendRequest({
            hdl: 'orderdashboard', 'action': 'fillunfulfilled',
            id: 1,
        }, 'Fetching data from server....').then(
        function(data) {
            if (data.success) {
                //alert(data.fees);
                vmu.set('usertype', data.usertype);
                //alert(data.usertype);
                Ext.getCmp('usertypefortransactionlisting').setValue(data.usertype);
                if( data.operatorconstant || data.saleconstant || data.traderconstant){
                   
                    Ext.getCmp('gtpcustomernamepo').setHidden(false);
                    Ext.getCmp('gtpcustomernametl').setHidden(false);
                    Ext.getCmp('fetchpolistbutton').setHidden(false);
                    Ext.getCmp('gtpcustomernamepo').getStore().loadData(data.partners);
                    Ext.getCmp('gtpcustomernametl').getStore().loadData(data.partners);

                    // Reset Grid Data for List
                   
                    
                    
                }else {
                    Ext.getCmp('unfulfilledjlistpo').setHidden(false);
                    Ext.getCmp('hiddenspacingtransactionlisting').setHidden(false);
                }
                
                /* ****************************************** Old **********************************************************
                if(data.usertype == 'Operator' || data.usertype == 'Sale'){
                   
                    Ext.getCmp('gtpcustomernamepo').setHidden(false);
                    Ext.getCmp('gtpcustomernametl').setHidden(false);
                    Ext.getCmp('fetchpolistbutton').setHidden(false);
                    Ext.getCmp('gtpcustomernamepo').getStore().loadData(data.partners);
                    Ext.getCmp('gtpcustomernametl').getStore().loadData(data.partners);

                    // Reset Grid Data for List
                   
                    
                    
                }else {
                    Ext.getCmp('unfulfilledjlistpo').setHidden(false);
                   
                } ****************************************** Old ********************************************************** */
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
        width: 500,
        height: 400,
        cls: Ext.baseCSSPrefix + 'shadow',
    
        layout: {
            type: 'vbox',
            pack: 'start',
            align: 'stretch'
        },
        scrollable:true,
        bodyPadding: 10,
    
        defaults: {
            frame: true,
            bodyPadding: 10
        },
    
        items: [
            /*{
                
                 // Style for migasit default
                style: {
                    borderColor: '#204A6D',
                },
                height: 120,
                margin: '0 0 10 0',
                items: [{
                    xtype: 'container',
                    scrollable: false,
                    layout: 'hbox',
                    defaults: {
                        bodyPadding: '5',
                        // border: true
                    },
                    items: [{
                      html: '<h1>Unfulfill PO</h1>',
                      flex: 10,
                      //xtype: 'orderview',
                     //reference: 'spotorder',
                    },{
                      // spacing in between
                      flex: 1,
                    },{
                      
                        layout: {
                            type: 'hbox',
                            pack: 'start',
                            align: 'stretch'
                        },
                        flex: 6,
                    
                        //bodyPadding: 10,
                    
                        defaults: {
                            frame: false,
                        },

                    }]
    
                // id: 'medicalrecord',
                },]
            },*/
            /*{
                
                //height: 120,
                title: '<h4 style="background:transparent; color:#404040; ">Customer Transaction</h4>',
                header: {
                    style: {
                        backgroundColor: 'white',
                        
                    }
                },
                margin: '0 0 10 0',
                items: [{
                    xtype: 'tabpanel',
                    scrollable: false,
                    layout: 'hbox',
                    defaults: {
                        bodyPadding: '5',
                        // border: true
                    },
                    items:[
                        {
                            xtype: 'form',
                            title: 'Spot Buy/Sell',
                            scrollable: false,
                            layout: 'hbox',
                            defaults: {
                                bodyPadding: '5',
                                // border: true
                            },
                            signTpl: '<span style="' +
                                'color:{value:sign(\'"#cf4c35"\',\'"#73b51e"\')}"' +
                                '>{text}</span>',
                
                            items:[
                                { xtype: 'container',
                                    //fieldLabel: 'ACE BUY',
                                    //style="border-style:dotted;border-color:1px solid #E3EFF4"
                                    flex: 1,
                                    items: [
                                        { xtype: 'combobox', fieldLabel:'Product', style: 'margin-top:5%;', store: {type: 'array', fields: ['id', 'name']}, queryMode: 'local', remoteFilter: false, name: 'product', valueField: 'id', displayField: 'name', reference: 'product', forceSelection: false, editable: true,
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
                                        ]},
                                        { xtype: 'textfield', fieldLabel: 'Total Value (RM)', name: 'totalvalue', },
                                        {
                                            xtype : 'menuseparator',
                                            width : '99%',
                                            padding: '0 5 0 5',
                                            html: "dadadad",
                                        },
                                        { xtype: 'textfield', fieldLabel: 'Total Xau Weight (gram)', name: 'totalxauweight', },
                                    ]
                                },
                                //{ xtype: 'displayfield', flex: 1},
                                //{ xtype: 'displayfield', fieldLabel: 'Ask', name: 'acesell', dataIndex: 'priceChangePct', flex: 2 , renderer: 'renderPercent'},
                                {
                                    xtype: 'container',
                                    style: 'padding:2em 1em;box-sizing:border-box;',
                                    scrollable: false,
                                    layout: 'hbox',
                                    defaults: {
                                        bodyPadding: '5',
                                        // border: true
                                    },
                                    flex: 2,
                                    signTpl: '<span style="' +
                                        'color:{value:sign(\'"#cf4c35"\',\'"#73b51e"\')}"' +
                                        '>{text}</span>',
                        
                                    items:[
                                        { xtype: 'displayfield',
                                            //fieldLabel: 'ACE BUY',
                                            //style="border-style:dotted;border-color:1px solid #E3EFF4"
                                            flex: 11,
                                            name: 'acebuy',
                                            //tyle: 'text-align:center;border-style:solid;padding:2em 1em;box-sizing:border-box;',
                                            style: 'text-align:center;padding:2em 1em;box-sizing:border-box;',
                                            renderer: function(value, field) {
                                                this.rndTpl = this.rndTpl || new Ext.XTemplate('<div><p style="text-align:center;">ACE BUY</p></div>' +
                                                    '<br><div><div><h1 style="color:#7ED321;display:inline;text-align:center;">' + 
                                                    'RM 19<p style="font-size:130%;display:inline;font-style: italic;">{[values.decimals.replace(/\\n/g, "<li/>")]}</p></h1>' + 
                                                    '<div><p style="text-align:center">per gram</p></div>' +
                                                    '</div></div>');
                                                    
                                                return this.rndTpl.apply({
                                                    decimals: value
                                                });
                                            },
                                            listeners: {
                                                render: function(field, eOpts) {
                                                    field.setValue('5.111')
                                                }
                                        }},             
                                        { xtype: 'panel', flex: 1},
                                        //{ xtype: 'displayfield', fieldLabel: 'Ask', name: 'acesell', dataIndex: 'priceChangePct', flex: 2 , renderer: 'renderPercent'},
                                        { xtype: 'displayfield',
                                        //fieldLabel: 'Ask',
                                        flex: 11,
                                        name: 'acebuy',
                                        //tyle: 'text-align:center;border-style:solid;padding:2em 1em;box-sizing:border-box;',
                                        style: 'text-align:center;padding:2em 1em;box-sizing:border-box;',
                                        renderer: function(value, field) {
                                            this.rndTpl = this.rndTpl || new Ext.XTemplate('<p style="text-align:center">Ask</p>' +
                                                '<br><div><div><h1 style="color:#C3262E;display:inline;">' + 
                                                'RM 19<p style="font-size:130%;display:inline;font-style: italic;">{[values.decimals.replace(/\\n/g, "<li/>")]}</p></h1>' + 
                                                '<p style="text-align:center">per gram</p>' +
                                                '</div></div>');
                                
                                            return this.rndTpl.apply({
                                                decimals: value
                                            });
                                        },
                                        listeners: {
                                            render: function(field, eOpts) {
                                                field.setValue('5.001')
                                            }
                                        }},
                                        { xtype: 'displayfield', flex: 1},
                                    ]
                                },
                            ],
                            dockedItems: [{
                                xtype: 'toolbar',
                                dock: 'bottom',
                                //ui: 'footer',
                                style: 'opacity: 1.0;',
                                // defaults: {
                                //     // align: 'right',
                                //     buttonAlign: 'right',
                                //     alignTo: 'right',
                                // },
                                // // defaultAlign: 'right',
                                // buttonAlign: 'right',
                                // alignTo: 'right',
                                layout: {
                                    pack: 'center',
                                    type: 'hbox',
                                    // align: 'right'
                                },
                                items: [{
                                            xtype:'panel',
                                            flex: 11
                                        },{
                                            text: 'Sell',
                                            handler: '',
                                            style: 'opacity: 1.0;',
                                            flex: 8,
                                            tooltip: 'Sell',
                                            reference: 'printtestrequestbtn',
                                            
                                        },
                                        {
                                            xtype:'panel',
                                            flex:1
                                        },{
                                            text: 'Buy',
                                            handler: '',
                                            style: 'opacity: 1.0;',
                                            flex: 8,
                                            tooltip: 'Buy',
                                            reference: 'printtestrequestbtn',
                                            
                                        },{
                                            xtype:'panel',
                                            flex:1
                                        },],
                            }],
                        },
                        {
                            title: 'Order Buy/Sell',
                            html: 'This is order Buy/Sell.'
                        },
                        {
                            title: 'Tradebook',
                            html: 'This is tradebook.'
                        },
                        {
                            title: 'Daily Limit',
                            iconCls: 'x-fa fa-calendar',
                            xtype: 'form',
                            reference: 'userdailylimit',
                            store: { type: 'Partner' },
                            viewModel: {
                                type: 'partner-partner'
                            },
                            scrollable: true,
                            items: [
                                
                                    {
                                        itemId: 'user_main_fieldset',
                                        xtype: 'fieldset',
                                        title: 'Main Information',
                                        title: 'Daily Limit',
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
                                                  fieldLabel: 'Limits',
                                                  defaultType: 'textboxfield',
                                                  layout: 'hbox',
                                                  flex: 4,
                                                  items: [
                                                            {
                                                              xtype: 'fieldcontainer',
                                                              layout: 'vbox',
                                                              flex: 2,
                                                              items: [
                                                                {
                                                                  xtype: 'displayfield', name:'limitbuy', reference: 'limitbuy', fieldLabel: 'Buy limit (g)', flex: 1, style:'padding-left: 20px;padding-right: 20px;', fieldStyle:" background-color: #ffffff ",
                                                                },
                                                                {
                                                                  xtype: 'displayfield', name:'limitsell', reference: 'limitsell', fieldLabel: 'Sell Limit (g)', flex: 1, style:'padding-left: 20px;padding-right: 20px;', fieldStyle:" background-color: #fff ",
                                                                },
                                                              ]
                                                            },
                                                          
                                                        ]
                                                },
                                                {
                                                    xtype: 'panel',
                                                    flex: 1,
                                                    
                                                },
                                                {
                                                    xtype: 'fieldcontainer',
                                                    fieldLabel: 'Balance',
                                                    defaultType: 'textboxfield',
                                                    layout: 'hbox',
                                                    flex: 4,
                                                    items: [
          
                                                              // ALL CHECKBOX INPUT -- jsonConversion => to 'data[key] = value'
                                                              /*
                                                              {
                                                                xtype: 'displayfield', name:'vtweight', reference: 'vtweight', fieldLabel: 'Weight (kg)', name: 'weight', flex: 1, //style:'padding-left: 20px;'
                                                              },
                                                              {
                                                                xtype: 'displayfield', name:'vtheight', reference: 'vtheight', fieldLabel: 'Height (cm)', name: 'height', flex: 1, //style:'padding-left: 20px;'
                                                              },
                                                              {
                                                                xtype: 'displayfield', name:'vtbmi', reference: 'vtbmi', fieldLabel: 'BMI', name: 'bmi', flex: 1, style:'padding-left: 20px;',
                                                              },
                                                              {
                                                                xtype: 'fieldcontainer',
                                                                layout: 'vbox',
                                                                flex: 2,
                                                                items: [
                                                                  {
                                                                    xtype: 'displayfield', name:'balancebuy', reference: 'balancebuy', fieldLabel: 'Buy Balance (g)', flex: 1, style:'padding-left: 20px;padding-right: 20px;', fieldStyle:" background-color: #ffffff ",
                                                                  },
                                                                  {
                                                                    xtype: 'displayfield', name:'balancesell', reference: 'balancesell', fieldLabel: 'Sell Balance (g)', flex: 1, style:'padding-left: 20px;padding-right: 20px;', fieldStyle:" background-color: #fff ",
                                                                  },
                                                                ]
                                                              },
                                                            
                                                          ]
                                                },
                                                {
                                                    xtype: 'panel',
                                                    flex: 1,
                                                    
                                                },
                                              ]
                                    },
                                    {
                                        itemId: 'user_main_fieldset2',
                                        xtype: 'fieldset',
                                        title: 'Per Transaction',
                                        layout: 'anchor',
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
                                              fieldLabel: 'Per Transaction Minimum',
                                              defaultType: 'textboxfield',
                                              layout: 'hbox',
                                              flex: 4,
                                              items: [
                                                        {
                                                          xtype: 'fieldcontainer',
                                                          layout: 'vbox',
                                                          flex: 2,
                                                          items: [
                                                            {
                                                              xtype: 'displayfield', name:'pertransactionminbuy', reference: 'pertransactionminbuy', fieldLabel: 'Per Transaction Min Buy (g)', flex: 1, style:'padding-left: 20px;padding-right: 20px;', fieldStyle:" background-color: #ffffff ",
                                                            },
                                                            {
                                                              xtype: 'displayfield', name:'pertransactionminsell', reference: 'pertransactionminsell', fieldLabel: 'Per Transaction Min Sell (g)', flex: 1, style:'padding-left: 20px;padding-right: 20px;', fieldStyle:" background-color: #fff ",
                                                            },
                                                          ]
                                                        },
                                                      
                                                    ]
                                            },
                                            {
                                                xtype: 'panel',
                                                flex: 1,
                                                
                                            },
                                            {
                                                xtype: 'fieldcontainer',
                                                fieldLabel: 'Per Transaction Maximum',
                                                defaultType: 'textboxfield',
                                                layout: 'hbox',
                                                flex: 4,
                                                items: [
      
                                                          // ALL CHECKBOX INPUT -- jsonConversion => to 'data[key] = value'
                                                          /*
                                                          {
                                                            xtype: 'displayfield', name:'vtweight', reference: 'vtweight', fieldLabel: 'Weight (kg)', name: 'weight', flex: 1, //style:'padding-left: 20px;'
                                                          },
                                                          {
                                                            xtype: 'displayfield', name:'vtheight', reference: 'vtheight', fieldLabel: 'Height (cm)', name: 'height', flex: 1, //style:'padding-left: 20px;'
                                                          },
                                                          {
                                                            xtype: 'displayfield', name:'vtbmi', reference: 'vtbmi', fieldLabel: 'BMI', name: 'bmi', flex: 1, style:'padding-left: 20px;',
                                                          },
                                                          {
                                                            xtype: 'fieldcontainer',
                                                            layout: 'vbox',
                                                            flex: 2,
                                                            items: [
                                                              {
                                                                xtype: 'displayfield', name:'pertransactionmaxbuy', reference: 'pertransactionmaxbuy', fieldLabel: 'Per Transaction Max Buy (g)', flex: 1, style:'padding-left: 20px;padding-right: 20px;', fieldStyle:" background-color: #ffffff ",
                                                              },
                                                              {
                                                                xtype: 'displayfield', name:'pertransactionmaxsell', reference: 'pertransactionmaxsell', fieldLabel: 'Per Transaction Max Sell (g)', flex: 1, style:'padding-left: 20px;padding-right: 20px;', fieldStyle:" background-color: #fff ",
                                                              },
                                                            ]
                                                          },
                                                        
                                                      ]
                                            },
                                            {
                                                xtype: 'panel',
                                                flex: 1,
                                                
                                            },
                                          ]
                                    },
                                    /*{
                                        buttons: [{
                                            text: 'Cancel',
                                            handler: 'onFormReset'
                                        }, {
                                            text: 'Submit',
                                            handler: 'onRecordRequest'
                                        }, {
                                            text: 'Something else',
                                            width: 150,
                                            handler: 'onCompleteClick'
                                        }],
                                    }
                                
                            ],
        
                        },
        
                        {
                            title: 'Help',
                            html: 'This is tab 3 content.'
                        }
                    ]
    
                // id: 'medicalrecord',
                },]
            },*/
            {
                xtype: 'form',
                title: ' Transaction Listing',
                reference: 'transactionlisting-form',
                // Custom style
                header: {
                    // Custom style for Migasit
                    /*style: {
                        backgroundColor: '#204A6D',
                    },*/
                    style : 'background-color: #204A6D;border-color: #204A6D;',
                },
                style: "font-family:'Open Sans', 'Helvetica Neue', helvetica, arial, verdana, sans-serif;",
                border: true,
                margin: '0 0 10 0',
                items: [
                    {
                        xtype: 'hiddenfield', id: 'usertypefortransactionlisting', name:'usertypefortransactionlisting', reference: 'usertypefortransactionlisting', fieldLabel: 'usertypefortransactionlisting', flex: 1,
                    },
                    {
                        xtype: 'container',
                        layout: 'hbox',
                        items: [
                            
                            { xtype: 'datefield', labelStyle: 'font: 900 13px/22px Open Sans, Helvetica Neue, helvetica, arial, verdana, sans-serif', labelAlign: 'top', labelWidth: '39%', width: '99%', fieldLabel: 'From *', name: 'fromdate', reference: 'fromdate', flex: 2, forceSelection: true, allowBlank: false, },
                            {
                                xtype: 'panel', flex: 0.5
                            },
                            { xtype: 'datefield', labelStyle: 'font: 900 13px/22px Open Sans, Helvetica Neue, helvetica, arial, verdana, sans-serif', labelAlign: 'top', labelWidth: '39%', width: '99%', fieldLabel: 'To *', name: 'todate', reference: 'todate', flex: 2, forceSelection: true, allowBlank: false, },
                            {
                                xtype: 'panel', flex: 3.5
                            },
                        ]
                    },
                    { xtype: 'displayfield', id: 'hiddenspacingtransactionlisting', hidden: true, flex : 1, margin: '-20 0 0 0'},
                    { xtype: 'combobox',  labelWidth: '39%', width: '25%', labelAlign:'top', flex:3,  id: 'gtpcustomernametl', hidden: true, fieldLabel: 'Customer Name', store: {type: 'array', fields: ['id', 'name']}, queryMode: 'local', remoteFilter: false, name: 'gtpcustomernametransactionlisting', valueField: 'id', displayField: 'name', reference: 'product', forceSelection: false, editable: true,
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
                    { xtype: 'panel', flex : 1},
                    { xtype: 'panel', flex : 1},
                    
                    {
                        xtype: 'container',
                        //title: 'Spot Buy/Sell',
                        scrollable: false,
                        layout: 'hbox',
                        defaults: {
                            bodyPadding: '5',
                            // border: true
                        },
                        items: [
                            {
                                flex: 4,
                                reference: 'type', fieldLabel: '', xtype: 'radiogroup', allowBlank: false,
                                items: [
                                    { boxLabel: 'My Buys', flex: 6, name: 'type', inputValue: '0', checked: true, },
                                    { boxLabel: 'My Sells', flex: 6, name: 'type', inputValue: '1' },
                                ]
                            },
                            {
                                xtype: 'displayfield',
                                value: 'Date Range:',
                                flex: 2
                            },
                            {
                                xtype: 'container',
                                title: 'Quick',
                                scrollable: false,
                                flex: 6, 
                                items: [
                                    //{ xtype: 'panel', flex : 5},
                                  
                                    {
                                        xtype: 'button',
                                        flex: 1, 
                                        //text: 'Submit Form',
                                        text : '<span style="font: 400 12px/16px Open Sans, Helvetica Neue, helvetica, arial, verdana, sans-serif;color:#23a9df;">7</span>',
                                        style: 'opacity: 1.0;background: linear-gradient(269deg, #ffffff 0%, #ffffff 100%);color: #404040;border-color:transparent;box-sizing: border-box;font-weight: bold;text-transform: uppercase;',
                                        listeners: {
                                            click: function(elemnt){
                                                // Get form
                                                form = this.up().up();
        
                                                // Create Date object for current date
                                                date = new Date();
        
                                                // Set Dates
                                                // Set initial date to ToDate
                                                form.lookupController().lookupReference('todate').setValue(date);
        
                                                // Get Date difference
                                                date.setDate(date.getDate()-7);
        
                                                form.lookupController().lookupReference('fromdate').setValue(date);
                                            
                                                //form.lookupController().lookupReference('fromdate').getValue()
                                            }
                                        }
                                    
                                    },
                                    {
                                        xtype: 'button',
                                        flex: 1, 
                                        //text: 'Submit Form',
                                        text : '<span style="font: 400 12px/16px Open Sans, Helvetica Neue, helvetica, arial, verdana, sans-serif;color:#23a9df;">14</span>',
                                        style: 'opacity: 1.0;background: linear-gradient(269deg, #ffffff 0%, #ffffff 100%);color: #404040;border-color:transparent;box-sizing: border-box;font-weight: bold;text-transform: uppercase;letter-spacing: 1px;',
                                        listeners: {
                                            click: function(elemnt){
                                                // Get form
                                                form = this.up().up();
        
                                                // Create Date object for current date
                                                date = new Date();
        
                                                // Set Dates
                                                // Set initial date to ToDate
                                                form.lookupController().lookupReference('todate').setValue(date);
        
                                                // Get Date difference
                                                date.setDate(date.getDate()-14);
        
                                                form.lookupController().lookupReference('fromdate').setValue(date);
        
                                                
                                                //form.lookupController().lookupReference('fromdate').getValue()
                                            }
                                        }
                                    
                                    },
                                    {
                                        xtype: 'button',
                                        flex: 1, 
                                        //text: 'Submit Form',
                                        text : '<span style="font: 400 12px/16px Open Sans, Helvetica Neue, helvetica, arial, verdana, sans-serif;color:#23a9df;">30</span>',
                                        style: 'opacity: 1.0;background: linear-gradient(269deg, #ffffff 0%, #ffffff 100%);color: #404040;border-color:transparent;box-sizing: border-box;font-weight: bold;text-transform: uppercase;letter-spacing: 1px;',
                                        listeners: {
                                            click: function(elemnt){
                                                // Get form
                                                form = this.up().up();
        
                                                // Create Date object for current date
                                                date = new Date();
        
                                                // Set Dates
                                                // Set initial date to ToDate
                                                form.lookupController().lookupReference('todate').setValue(date);
        
                                                // Get Date difference
                                                date.setDate(date.getDate()-30);
        
                                                form.lookupController().lookupReference('fromdate').setValue(date);
        
                                                
                                                //form.lookupController().lookupReference('fromdate').getValue()
                                            }
                                        }
                                    
                                    },
                                    {
                                        xtype: 'button',
                                        flex: 1,
                                        //text: 'Submit Form',
                                        text : '<span style="font: 400 12px/16px Open Sans, Helvetica Neue, helvetica, arial, verdana, sans-serif;color:#23a9df;">60</span>',
                                        style: 'opacity: 1.0;background: linear-gradient(269deg, #ffffff 0%, #ffffff 100%);color: #404040;border-color:transparent;box-sizing: border-box;font-weight: bold;text-transform: uppercase;letter-spacing: 1px;',
                                        listeners: {
                                            click: function(elemnt){
                                                // Get form
                                                form = this.up().up();
        
                                                // Create Date object for current date
                                                date = new Date();
        
                                                // Set Dates
                                                // Set initial date to ToDate
                                                form.lookupController().lookupReference('todate').setValue(date);
        
                                                // Get Date difference
                                                date.setDate(date.getDate()-60);
        
                                                form.lookupController().lookupReference('fromdate').setValue(date);
        
                                                
                                                //form.lookupController().lookupReference('fromdate').getValue()
                                            }
                                        }
                                    
                                    },
                                    {
                                        xtype: 'button',
                                        flex: 1,
                                        //text: 'Submit Form',
                                        text : '<span style="font: 400 12px/16px Open Sans, Helvetica Neue, helvetica, arial, verdana, sans-serif;color:#23a9df;">90</span>',
                                        style: 'opacity: 1.0;background: linear-gradient(269deg, #ffffff 0%, #ffffff 100%);color: #404040;border-color:transparent;box-sizing: border-box;font-weight: bold;text-transform: uppercase;letter-spacing: 1px;',
                                        listeners: {
                                            click: function(elemnt){
                                                // Get form
                                                form = this.up().up();
        
                                                // Create Date object for current date
                                                date = new Date();
        
                                                // Set Dates
                                                // Set initial date to ToDate
                                                form.lookupController().lookupReference('todate').setValue(date);
        
                                                // Get Date difference
                                                date.setDate(date.getDate()-90);
        
                                                form.lookupController().lookupReference('fromdate').setValue(date);
        
                                                
                                                //form.lookupController().lookupReference('fromdate').getValue()
                                            }
                                        }
                                    
                                    },
                                    {
                                        xtype: 'button',
                                        flex: 1,
                                        //text: 'Submit Form',
                                        text : '<span style="font: 400 12px/16px Open Sans, Helvetica Neue, helvetica, arial, verdana, sans-serif;color:#23a9df;">180</span>',
                                        style: 'opacity: 1.0;background: linear-gradient(269deg, #ffffff 0%, #ffffff 100%);color: #404040;border-color:transparent;box-sizing: border-box;font-weight: bold;text-transform: uppercase;letter-spacing: 1px;',
                                        listeners: {
                                            click: function(elemnt){
                                                // Get form
                                                form = this.up().up();
        
                                                // Create Date object for current date
                                                date = new Date();
        
                                                // Set Dates
                                                // Set initial date to ToDate
                                                form.lookupController().lookupReference('todate').setValue(date);
        
                                                // Get Date difference
                                                date.setDate(date.getDate()-180);
        
                                                form.lookupController().lookupReference('fromdate').setValue(date);
        
                                                
                                                //form.lookupController().lookupReference('fromdate').getValue()
                                            }
                                        }
                                    
                                    },
                                    {
                                        xtype: 'button',
                                        text: '<span style="font: 300 13px/22px Open Sans, Helvetica Neue, helvetica, arial, verdana, sans-serif;color:#ffffff;">Fetch</span>',
                                        handler: '',
                                        padding: '8px 30px',
                                        width: '130px',
                                        //style: 'border-radius: 20px;opacity: 1.0;background: linear-gradient(269deg, #b08f26 0%, #d4af37 100%);text-color: #000000;text-transform: uppercase;',
                                        style: 'border-radius: 20px;opacity: 1.0;background: linear-gradient(269deg, #204A6D 0%, #204A6D 100%);color: #ffffff;display: inline-block;box-sizing: border-box;color: #ffffff; font-weight: bold;text-transform: uppercase;letter-spacing: 1px;',
                                        labelStyle: 'font: 900 13px/22px Open Sans, Helvetica Neue, helvetica, arial, verdana, sans-serif',
                                        flex: 2,
                                        tooltip: 'Fetch Transaction Listing from SAP',
                                        reference: 'fetchtransactionlisting',
                                        handler: 'summaryAction',
                                        
                                    },
        
                                ]
                            },
                            { xtype: 'panel', flex : 4},
                        ]
                    },
                   
                    {
                        xtype: 'container',
                        layout: 'vbox',
                        margin: "0 0 10 0", 
                        items: [
                            
                            {
                                flex:1,
                                xtype: 'displayfield',
                                value : "<p>&#9679; The listing will be created with your selected dates.</p>",
                                margin: '20 0 0 0',
                                forceSelection: true,
                                enforceMaxLength: true,
                                readOnly : true,
                            },{
                                flex:1,
                                xtype: 'displayfield',
                                value : "<p>&#9679; A maximum range of 180 days transactions per request.</p>",
                                margin: '-20 0 0 0',
                                forceSelection: true,
                                enforceMaxLength: true,
                                readOnly : true,
                            },
                            {
                                flex: 1,
                                xtype: 'displayfield',
                                value : "<p>&#9679; Rename the file of your choice ending with .pdf</p>",
                                margin: '-20 0 0 0',
                                forceSelection: true,
                                enforceMaxLength: true,
                                readOnly : true,
                            },{
                                flex: 1,
                                xtype: 'displayfield',
                                value : "<p>&#9679; GTP does not store transaction statements.</p>",
                                margin: '-20 0 0 0',
                                forceSelection: true,
                                enforceMaxLength: true,
                                readOnly : true,
                            },
                            { xtype: 'panel', flex : 1},
                        ]
                    },
                    /*{ xtype: 'combobox', fieldLabel:'Number of Records', store: {type: 'array', fields: ['id', 'name']}, queryMode: 'local', remoteFilter: false, name: 'product', valueField: 'id', displayField: 'name', reference: 'product', forceSelection: false, editable: true,
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
                    ]},*/
            
                ],

                dockedItems: [{
                    xtype: 'toolbar',
                    dock: 'bottom',
                    //ui: 'footer',
                    style: 'opacity: 1.0;',
                    // defaults: {
                    //     // align: 'right',
                    //     buttonAlign: 'right',
                    //     alignTo: 'right',
                    // },
                    // // defaultAlign: 'right',
                    // buttonAlign: 'right',
                    // alignTo: 'right',
                    layout: {
                        pack: 'center',
                        type: 'hbox',
                        // align: 'right'
                    },
                    items: [{
                                xtype:'panel',
                                flex:4
                            },{
                                xtype:'panel',
                                flex:4
                            },],
                }],
            },
            {
                xtype: 'form',
                title: 'Unfulfilled Purchase Orders',
                reference: 'unfulfilledpolisting-form',
                border: true,
                header: {
                    // Custom style for Migasit
                    /*style: {
                        backgroundColor: '#204A6D',
                    },*/
                    style : 'background-color: #204A6D;border-color: #204A6D;',
                },
                items: [
                    {
                        xtype: 'container',
                        layout: 'hbox',
                        items: [
                            
                             { xtype: 'combobox', labelAlign:'top',  labelWidth: '39%', width: '25%',  flex:2, id: 'gtpcustomernamepo', hidden: true, fieldLabel: 'GTP Customer Name', store: {type: 'array', fields: ['id', 'name']}, queryMode: 'local', remoteFilter: false,  name: 'gtpcustomernamepurchaseorder', valueField: 'id', displayField: 'name', reference: 'product', forceSelection: false, editable: true,
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
                            ]},
                            {
                                xtype: 'panel', flex: 0.5
                            },
                            {
                                xtype: 'button',
                                padding: '8px 30px',
                                width: '130px',
                                margin: '27 0 0 0',
                                text: '<span style="font: 300 13px/22px Open Sans, Helvetica Neue, helvetica, arial, verdana, sans-serif;color:#ffffff;">Fetch</span>',
                                
                                id: 'fetchpolistbutton',
                                hidden: true,
                                //style: 'border-radius: 20px;opacity: 1.0;background: linear-gradient(269deg, #b08f26 0%, #d4af37 100%);text-color: #000000;text-transform: uppercase;',
                                style: 'border-radius: 20px;opacity: 1.0;background: linear-gradient(269deg, #204A6D 0%, #204A6D 100%);color: #ffffff;display: inline-block;box-sizing: border-box;color: #ffffff; font-weight: bold;text-transform: uppercase;letter-spacing: 1px;',
                                labelStyle: 'font: 900 13px/22px Open Sans, Helvetica Neue, helvetica, arial, verdana, sans-serif',
                                flex: 0.842,
                                tooltip: 'Fetch PO list',
                                reference: 'fetchpolistbutton',
                                handler: 'fetchPOListForCustomer',

                            },
                            {
                                xtype: 'panel', flex: 4.658
                            },
                        ]
                    },
                    {
                        title: '',
                        flex: 13,
                        xtype: 'unfulfillpoview',
                        reference: 'unfulfillpo',
                        id: 'unfulfilledjlistpo',
                        hidden: true,
                    },
            
                ],

                dockedItems: [{
                    xtype: 'toolbar',
                    dock: 'bottom',
                    //ui: 'footer',
                    style: 'opacity: 1.0;',
                    // defaults: {
                    //     // align: 'right',
                    //     buttonAlign: 'right',
                    //     alignTo: 'right',
                    // },
                    // // defaultAlign: 'right',
                    // buttonAlign: 'right',
                    // alignTo: 'right',
                    layout: {
                        pack: 'center',
                        type: 'hbox',
                        // align: 'right'
                    },
                    items: [{
                                xtype:'panel',
                                flex:4
                            },{
                                xtype:'panel',
                                flex:4
                            },],
                }],
            }
            
        ]
    }


});
