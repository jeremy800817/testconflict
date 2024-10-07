Ext.define('snap.view.tender.TekunTender',{
    extend: 'Ext.panel.Panel',
    xtype: 'tekuntenderview',

    requires: [

        'Ext.layout.container.Fit',
        'snap.view.tender.PosTenderController',

    ],
    formDialogWidth: 950,
    controller: 'postender-postender',
    // permissionRoot: '/root/trading/order',
    permissionRoot: '/root/tekun/tender',
    layout: 'fit',
    width: 500,
    height: 400,
    cls: Ext.baseCSSPrefix + 'shadow',

    bodyPadding: 25,
    partnercode: 'TEKUN',
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

    // initComponent: function(formView, form, record, asyncLoadCallback){
    //     elmnt = this;
    //     vmu = this.getViewModel();
    //     snap.getApplication().sendRequest({
    //         hdl: 'tenderhandler', 'action': 'fillunfulfilled',
    //         id: 1,
    //     }, 'Fetching data from server....').then(
    //     function(data) {
    //         if (data.success) {
    //             //alert(data.fees);
    //             vmu.set('usertype', data.usertype);
    //             //alert(data.usertype);
    //             Ext.getCmp('usertypefortransactionlisting').setValue(data.usertype);
    //             if( data.operatorconstant || data.saleconstant || data.traderconstant){
                   
    //                 Ext.getCmp('gtpcustomernamepo').setHidden(false);
    //                 Ext.getCmp('gtpcustomernametl').setHidden(false);
    //                 Ext.getCmp('fetchpolistbutton').setHidden(false);
    //                 Ext.getCmp('gtpcustomernamepo').getStore().loadData(data.partners);
    //                 Ext.getCmp('gtpcustomernametl').getStore().loadData(data.partners);

    //                 // Reset Grid Data for List

    //             }else {
    //                 Ext.getCmp('unfulfilledjlistpo').setHidden(false);
    //             }

    //         }
    //     });
    //     this.callParent(arguments);
    // },
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
            {
                
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
                      html: '<h1>Tekun Tender Upload</h1>',
                      flex: 10,
                  
                    },{
                    
                      flex: 1,
                    },{
                      
                        layout: {
                            type: 'hbox',
                            pack: 'start',
                            align: 'stretch'
                        },
                        flex: 6,
                                    
                        defaults: {
                            frame: false,
                        },

                    }]
    
           
                },]
            },
         
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
                            
                            { xtype: 'datefield', fieldLabel: 'Gold Price Date (Required)', name: 'fromdate', reference: 'fromdate', flex: 4, forceSelection: true,  allowBlank: false,},
                            { xtype: 'panel', flex: 1 },
                              { xtype: 'filefield',fieldLabel: 'File (Required)', name: 'tenderlist', width: '90%', flex: 4, allowBlank: false, },
                        ]
                    },
                     {
                        xtype: 'container',
                        layout: 'hbox',
                        items: [
                            
                              { xtype: 'textfield',fieldLabel: 'Gold Price Value (Required)', name: 'goldprice', width: '90%', flex: 4, allowBlank: false,minLength: '3', maskRe: /[0-9.]/ },
                        ]
                    },
                    { xtype: 'combobox', flex:1,  id: 'gtpcustomernametl', hidden: true, fieldLabel: 'GTP Customer Name', store: {type: 'array', fields: ['id', 'name']}, queryMode: 'local', remoteFilter: false, name: 'gtpcustomernametransactionlisting', valueField: 'id', displayField: 'name', reference: 'product', forceSelection: false, editable: true,
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
                        xtype: 'container',
                        //title: 'Spot Buy/Sell',
                        scrollable: false,
                        layout: 'hbox',
                        defaults: {
                            bodyPadding: '5',
                            // border: true
                        },
                        items: [
                              
                            ]
                    },
                    {
                        xtype: 'container',
                        layout: 'hbox',
                        items: [
                            
                            {
                                flex:1,
                                xtype: 'displayfield',
                                value : "<p>&#9679; Please verify and get approved before upload </p>",
                                margin: '0 0 0 20',
                                forceSelection: true,
                                enforceMaxLength: true,
                                readOnly : true,
                            },{
                                flex:1,
                                xtype: 'displayfield',
                                value : "<p>&#9679; Minimum 1 tender record is require.</p>",
                                margin: '0 0 0 20',
                                forceSelection: true,
                                enforceMaxLength: true,
                                readOnly : true,
                            },
                            { xtype: 'panel', flex : 1},
                        ]
                    },
                    {
                        xtype:
                            "fieldcontainer",
                        fieldLabel:
                            "Preview ",
                        reference:
                            "uploadtenderfilepreview",
                        flex: 1,
                        hidden: false,
                        defaultType:
                            "checkboxfield",
                        items: [
                            {
                                checked: true,
                                boxLabel:
                                    "",
                                name:
                                    "preview",
                                inputValue:
                                    "1",
                                reference:
                                    "uploadtenderfilepreviewbox",
                            },
                        ],
                    },
                    {
                        xtype: 'container',
                        layout: 'hbox',
                        items: [
                            
                            {
                                flex:1,
                                xtype: 'displayfield',
                                value : "<p>&#9679; Please uncheck preview to submit, check to preview total amount </p>",
                                margin: '0 0 0 20',
                                forceSelection: true,
                                enforceMaxLength: true,
                                readOnly : true,
                            },
                            { xtype: 'panel', flex : 2},
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
                                flex:4
                            },{
                                text: '<span style="font: 300 13px/22px Open Sans, Helvetica Neue, helvetica, arial, verdana, sans-serif;color:#ffffff;">Fetch</span>',
                                handler: '',
                                //style: 'border-radius: 20px;opacity: 1.0;background: linear-gradient(269deg, #b08f26 0%, #d4af37 100%);text-color: #000000;text-transform: uppercase;',
                                style: 'border-radius: 20px;opacity: 1.0;background: linear-gradient(269deg, #204A6D 0%, #204A6D 100%);color: #ffffff;display: inline-block;box-sizing: border-box;color: #ffffff; font-weight: bold;text-transform: uppercase;letter-spacing: 1px;',
                                labelStyle: 'font: 900 13px/22px Open Sans, Helvetica Neue, helvetica, arial, verdana, sans-serif',
                                flex: 2,
                                tooltip: 'Upload Tender List',
                                reference: 'tekuntender',
                                handler: 'summaryAction',
                                
                            },{
                                xtype:'panel',
                                flex:4
                            },],
                }],
            },
            // {
            //     xtype: 'form',
            //     title: 'Tender Listing Upload status',
            //     reference: 'unfulfilledpolisting-form',
            //     border: true,
            //     header: {
            //         // Custom style for Migasit
            //         /*style: {
            //             backgroundColor: '#204A6D',
            //         },*/
            //         style : 'background-color: #204A6D;border-color: #204A6D;',
            //     },
            //     items: [
            //         { xtype: 'combobox', flex:1, id: 'gtpcustomernamepo', hidden: true, fieldLabel: 'GTP Customer Name', store: {type: 'array', fields: ['id', 'name']}, queryMode: 'local', remoteFilter: false,  name: 'gtpcustomernamepurchaseorder', valueField: 'id', displayField: 'name', reference: 'product', forceSelection: false, editable: true,
            //             labelStyle: 'font: 900 13px/22px Open Sans, Helvetica Neue, helvetica, arial, verdana, sans-serif',
            //             tpl: [
            //                 '<ul class="x-list-plain">',
            //                 '<tpl for=".">',
            //                 '<li class="',
            //                 Ext.baseCSSPrefix, 'grid-group-hd ',
            //                 Ext.baseCSSPrefix, 'grid-group-title">{abbr}</li>',
            //                 '<li class="x-boundlist-item">',
            //                 '{name}',
            //                 '</li>',
            //                 '</tpl>',
            //                 '</ul>'
            //         ]},
            //         {
            //             title: '',
            //             flex: 13,
            //             xtype: 'unfulfillpoview',
            //             reference: 'unfulfillpo',
            //             id: 'unfulfilledjlistpo',
            //             hidden: true,
            //         },
            
            //     ],

            //     dockedItems: [{
            //         xtype: 'toolbar',
            //         dock: 'bottom',
            //         //ui: 'footer',
            //         style: 'opacity: 1.0;',
            //         // defaults: {
            //         //     // align: 'right',
            //         //     buttonAlign: 'right',
            //         //     alignTo: 'right',
            //         // },
            //         // // defaultAlign: 'right',
            //         // buttonAlign: 'right',
            //         // alignTo: 'right',
            //         layout: {
            //             pack: 'center',
            //             type: 'hbox',
            //             // align: 'right'
            //         },
            //         items: [{
            //                     xtype:'panel',
            //                     flex:4
            //                 },{
            //                     text: '<span style="font: 300 13px/22px Open Sans, Helvetica Neue, helvetica, arial, verdana, sans-serif;color:#ffffff;">Fetch</span>',
                                
            //                     id: 'uploadtenderlistbutton',
            //                     hidden: true,
            //                     //style: 'border-radius: 20px;opacity: 1.0;background: linear-gradient(269deg, #b08f26 0%, #d4af37 100%);text-color: #000000;text-transform: uppercase;',
            //                     style: 'border-radius: 20px;opacity: 1.0;background: linear-gradient(269deg, #204A6D 0%, #204A6D 100%);color: #ffffff;display: inline-block;box-sizing: border-box;color: #ffffff; font-weight: bold;text-transform: uppercase;letter-spacing: 1px;',
            //                     labelStyle: 'font: 900 13px/22px Open Sans, Helvetica Neue, helvetica, arial, verdana, sans-serif',
            //                     flex: 2,
            //                     tooltip: 'Upload Tender list',
            //                     reference: 'uploadtenderlistbutton',
            //                     handler: 'uploadtenderlist',

            //                 },{
            //                     xtype:'panel',
            //                     flex:4
            //                 },],
            //     }],
            // }
            
        ]
    }


});
