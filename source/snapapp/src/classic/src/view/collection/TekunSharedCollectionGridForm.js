Ext.define('snap.view.collection.TekunSharedCollectionGridForm', {
    extend: 'snap.view.gridpanel.GridForm',
    alias: 'widget.TekunSharedCollectionGridForm',
    requires: [
        'Ext.panel.Panel',
        'Ext.window.Window',
        'Ext.form.*',
        'snap.view.gridpanel.BaseController',
        'snap.view.collection.SharedCollectionTreeController',
        'Ext.view.MultiSelector',
        'Ext.grid.*',
        'Ext.layout.container.Column',
    ],
    viewModel: {
        data: {
            theCompany: null,
            inputxauweight: 0,

            total_poweight: 0, // all po sum weight
            total_xauweight: 0,   // after * purity(all)
            total_balanceweight: 0, // remaining po weight
            total_purity: 0,    // purity(all)

        }
    },
    store:{
        selectedGRNStore: {},
    },
    controller: 'gridpanel-sharedcollectiontreecontroller',
    reference: 'formWindow',
    formDialogTitle: 'Collection',
    formDialogWidth: '95%',
    enableFormDialogClosable: false,
    formPanelDefaults: {
        msgTarget: 'side',
        margins: '0 0 10 0'
    },
    height: '100%',
    formPanelDefaults: {
        border: false,
        //scrollable: true,
    },
    enableFormPanelFrame: false,
    formPanelItems: [
        {
            xtype: 'fieldset',
            title: 'Sales Info',
            
            items: [{
                xtype: 'container',
                layout: {
                    type: 'vbox',
                    align: 'stretch'
                },
                items: [
                    // {
                    //     xtype: "container",
                    //     layout: {
                    //         type: "hbox",
                    //         align: "stretch",
                            
                    //     },
                    //     items: [
                    //         {
                    //             xtype: 'combobox',
                    //             fieldLabel: 'Company',
                    //             margin: '0 0 0 20',
                    //             forceSelection: true,
                    //             enforceMaxLength: true,
                                
                    //             store: [{
                    //                 id: '-',
                    //                 name: 'Select Company',
                    //             }],
                    //             autoLoad: true,
                    //             queryMode: 'local',
                    //             editable: false,
                    //             // disableKeyFilter: false,
                    //             valueField: 'id',
                    //             displayField: 'name',
                    //             reference: 'companycombo',
                    //             // handler: 'onclickcompany_handler',
                    //             listeners: {
                    //                 // select : 'onclickcompany',
                    //                 // beforeactivate: function(){
                    //                 //     Ext.MessageBox.confirm('Confirm', 'This will change your current test request data.', function(id) {
                    //                 //         if (id == 'yes') {

                    //                 //         }
                    //                 //     })
                    //                 // },
                    //             }
                    //         }
                    //     ]
                    // },
                    {
                        xtype: "container",
                        layout: {
                            type: "hbox",
                            align: "flex",
                            flex: 1
                        },
                        items: [{
                            xtype: 'textfield',
                            fieldLabel: 'Sales',
                            name: 'salespersonname',
                            reference: 'salespersoninput',
                            margin: '0 0 0 20',
                            forceSelection: true,
                            enforceMaxLength: true,
                            readOnly: true,
                        },]
                        // items: [
                        //     {
                        //         xtype: 'combobox',
                        //         fieldLabel: 'Sales',
                        //         margin: '0 0 0 20',
                        //         forceSelection: true,
                        //         enforceMaxLength: true,
                                
                        //         store: [{
                                    
                        //         }],
                        //         autoLoad: true,
                        //         queryMode: 'local',
                        //         editable: false,
                        //         // disableKeyFilter: false,
                        //         valueField: 'id',
                        //         displayField: 'name',
                        //         reference: 'salespersoncombo',
                        //         // handler: 'onclickcompany_handler',
                        //         listeners: {
                        //             // select : 'onclicksalesperson',
                        //             // beforeactivate: function(){
                        //             //     Ext.MessageBox.confirm('Confirm', 'This will change your current test request data.', function(id) {
                        //             //         if (id == 'yes') {

                        //             //         }
                        //             //     })
                        //             // },
                        //         }
                        //     }
                        // ]
                    },
                    {
                        xtype: "container",
                        layout: {
                            type: "hbox",
                            align: "flex",
                            flex: 1
                        },
                        items: [
                            {
                                fieldLabel: 'Customer <br><small><b>(SAP Vendor Name)</b></small>',
                                margin: '0 0 0 20',
                                xtype: 'combobox', 
                                name: 'customerid', 
                                minChars: 3, 
                                queryMode: 'remote', 
                                queryParam: 'query',
                                bind:{
                                    store: {
                                        type: 'CustomerStore',
                                        //fields: ['id', 'name', 'type'],
                                        autoLoad: true,
                                        remoteFilter: true,
                                        
                                        // sorters: 'name'
                                        // companyid: '{companyid_selected}'
                                        
                                        filters: [
                                            // { property: 'companyid', value: '{companycombo.selection.id}' },
                                            { property: 'customer ', value: 'tekun' }
                                        ],
                                    },
                                },
                                /*queryMode: 'local', remoteFilter: false, */
                                valueField: 'cardCode',
                                displayField: 'cardName',
                                reference: 'customercombox',
                                forceSelection: true,
                                editable: true,
                                allowBlank: false,
                                listeners: {
                                    select: 'customerComboSelected',
                                }
                            },
                            {
                                fieldLabel: 'Branch List',
                                width: '480px',
                                margin: '0 0 0 20',
                                xtype: 'combobox', 
                                name: 'branchid', 
                                minChars: 0, 
                                queryMode: 'local', 
                                queryParam: 'query',
                                bind:{
                                    store: {
                                        type: 'SharedBranchStore',
                                        proxy: {
                                            type: 'ajax',	       	
                                            url: 'index.php?hdl=collection&action=getSharedBranchList&partnercode=TEKUN',		
                                            reader: {
                                                type: 'json',
                                                rootProperty: 'results',
                                                idProperty: 'branch_list'            
                                            },	
                                        },
                                        //fields: ['id', 'name', 'type'],
                                        autoLoad: true,
                                        remoteFilter: true,
                                        
                                        // sorters: 'name'
                                        // companyid: '{companyid_selected}'
                                        
                                        filters: [
                                            { property: 'partnerid', value: '{customercombox.selection.cardCode}' }
                                        ],
                                    },
                                },
                                /*queryMode: 'local', remoteFilter: false, */
                                valueField: 'branchid',
                                displayField: 'branch_list',
                                reference: 'branchcombox',
                                forceSelection: true,
                                // editable: true,
                                allowBlank: true,
                                listeners: {
                                    select: 'branchComboSelected',
                                }
                            }
                        ]
                    }
                ],
            }],
            
        },
        {
            xtype: 'tabpanel',
            flex: 1,
            reference: 'collectiontab',
            items: [
                {
                    title: 'Collection Details',
                    layout: 'column',
                    margin: '15 0',
                    width: '100%',
                    // disabled: true,
                    items: [
                        {
                            xtype: 'container',
                            width: 320,
                            height: 300,
                            layout: 'fit',
                            items: [{
                           
                                    xtype: 'multiselector',
                                    title: 'Selected Open PO',

                                    columns: {
                                        items: [
                                            {
                                                text: "GTP NO.",
                                                dataIndex: "u_GTPREFNO"
                                            },{
                                                text: "Total Weight",
                                                dataIndex: "quantity"
                                            },{
                                                text: "Total",
                                                dataIndex: "docTotal"
                                            },{
                                                text: "Comments",
                                                dataIndex: "comments"
                                            },{
                                                text: "Date",
                                                dataIndex: "docDate"
                                            },
                                        ],
                                        defaults: {
                                            flex: 1
                                        }
                                    },
                                    
                                    fieldName: 
                                        '<tpl for=".">[ {cardCode} ] {cardCode}</tpl>'
                                    ,
                                    
                                    forceFit: true,
                                    // minHeight: 1000,
                                    
                                    viewConfig: {
                                        deferEmptyText: false,
                                        emptyText: 'No Open PO selected'
                                    },
                                    reference: 'pocontainer',

                                    search: {
                                        listeners: {
                                            add: function(a,b,c){
                                                code = this.lookupController().getView().lookupReference('customercombox').value
                                                url = 'index.php?hdl=collection&action=getPODetail' + '&query=' + code
                                                this.getSearchStore().getProxy().setUrl(url)
                                                if (this.getSearchStore().getProxy().url != url){
                                                    this.getView().lookupReference('pocontainer').getStore().removeAll()
                                                    this.getSearchStore().removeAll();
                                                    this.getSearchStore().load()
                                                }
                                                
                                            },
                                            
                                        },
                                        
                                        field: 'item_html_list',

                                        reference: 'searchpocontainer',
                                        // minWidth: 400,
                                        // minHeight: 300,
                                        width: 500,
                                        height: 300,
                                        store: {
                                            storeId: 'mcdd',
                                            alias: 'mcs',
                                            model: 'snap.model.Openpo',
                                            sorters: 'name',
                                            proxy: {
                                                type: 'ajax',
                                                limitParam: null,
                                                url: 'index.php?hdl=collection&action=getPODetail'
                                                // &query='+Ext.bind.getBind('customercombox.selection.cardCode')
                                            }
                                        },

                                        // renderTpl: 'asd',

                                        // viewConfig:{
                                        //     selectRecords: function (records) {
                                        //         console.log(records,'selectRecords')
                                        //         var view = this.items.getAt(0);
                                        //         return view.getSelectionModel().select(records);
                                        //     }
                                        // },
                                        // selectRecords: function (records) {
                                        //     var view = this.items.getAt(0);
                                        //     console.log(view.getSelectionModel().select(records),'selectRecords')
                                        //     return view.getSelectionModel().select(records);
                                        // },
                                        // makeItems: function (records) {
                                        //     // draftGRN
                                        // },

                                        // renderTpl: [
                                        //         '<ul><li role="option"',
                                        //         '<tpl for=".">',
                                        //         '<tpl if="draftGRN == true">',
                                        //             'class="x-disabled-item"',
                                        //         '<tpl else>',
                                        //             'class="x-custom-item"',
                                        //         '</tpl>',
                                        //         '>{#} - {quantity}</li></ul>'
                                            
                                        // ]
                                        // viewConfig: {
                                        //     getRowClass: function(record, rowIndex, rowParams, store)
                                        //     { 
                                        //       if (rowIndex == 0) // Or whatever it is you are using to determine read-only row or not
                                        //         return 'disabled-row';
                                        //       else
                                        //        return 'disabled-row';
                                        //     }
                                        // }
                                    }
                            }]
                        },
                        {
                            xtype: 'container',
                            margin: '0 0 0 50',
                            width: 400,
                            height: 300,
                            layout: 'column',
                            items: [{
                                    width: 400,
                                    height: 300,
                                    xtype: 'multiselector',
                                    title: 'Items',
                                    reference: 'itemsgird',

                                    fieldName: 'referenceno',
                                    columns: {
                                        items: [
                                            {
                                                text: "Item Ref",
                                                dataIndex: "referenceno",
                                                width: 150
                                            },
                                            // {
                                            //     text: "Purity",
                                            //     dataIndex: "purity"
                                            // },
                                            {
                                                text: 'Gross Weight',
                                                dataIndex: 'grossweight',
                                            },{
                                                text: 'XAU Weight',
                                                dataIndex: 'item_xauweight', // total rom rate card items XAU Weight
                                            }
                                            
                                        ],
                                        defaults: {
                                            flex: 1
                                        }
                                    },
                                    
                                    forceFit: true,
                                    // minHeight: 1000,
                                    bind: {
                                        selection: '{theCompany2_alpha}',
                                    },
    
                                    viewConfig: {
                                        deferEmptyText: false,
                                        emptyText: 'No item(s) selected'
                                    },
                                    // onSelectionChange: function(selModel, records) {
                                    //     console.log('a')
                                    // },
                                    // onChange: function(selModel, records) {
                                    //     console.log('b')
                                    // },
                                    listeners:{
                                        // onSelectionChange: function(selModel, records) {
                                        //     console.log('aa')
                                        // },
                                        // onChange: function(selModel, records) {
                                        //     console.log('bb')
                                        // },
                                        select: function(combo, records,c) {
                                            // console.log(combo, records,c,'dddcc')
                                            // console.log('readonly',combo.selected.items[0].data.u_purity_readonly)
                                            purity = 0;
                                            xauweight = 0;
                                            grossweight = 0;
                                            x = combo.view.lookupReferenceHolder().lookupReference('itemsgird').getStore()
                                            // console.log(x,'STORE')
                                            listing = x.data.items
                                            x = 0;
                                            listing.map((list)=>{
                                                list.data.details.map((detail)=>{
                                                    console.log(detail,'detail')
                                                    x++
                                                    purity += detail.u_purity
                                                    xauweight += detail.gtp_xauweight
                                                    grossweight += detail.gtp_inputweight
                                                })
                                            })
                                            list_length = x
                                            this.lookupController().cmdd(parseFloat(purity), parseFloat(xauweight), parseFloat(grossweight), list_length)

                                            // selections = combo.view.lookupReferenceHolder().lookupReference('itemsgird').getSelection();
                                            // if (selections){
                                            //     items = combo.view.lookupReferenceHolder().lookupReference('itemsgird').getSelection()[0].data.details;
                                            //     items.map(function(obj){
                                            //         combo.view.lookupReferenceHolder().lookupReference('ratecardgird').getStore().add(obj)
                                            //     })
                                            // }

                                            // purity_value = combo.view.lookupReferenceHolder().lookupReference('u_purity').value;
                                            // readonly = (purity_value.toString() != '0') ? true : false;
                                            // combo.view.lookupReferenceHolder().lookupReference('u_purity').setReadOnly(readonly)

                                        },
                                        // add: function(a,b,c){
                                        //     console.log(a,b,c, 'add')
                                        // },
                                        // selection: function(a,b,c){
                                        //     console.log(a,b,c, 'change')
                                        // },
                                        selectionchange: function(combo, records,c) {
                                            console.log(combo, records,c,'selectionchange')
                                        },
                                        // added: function(combo, records,c) {
                                        //     console.log(combo, records,c,'added')
                                        // },
                                        // afterrender: function(combo, records,c) {
                                        //     console.log(combo, records,c,'afterrender')
                                        // },
                                        // itemclick: function(combo, records,c) {
                                        //     console.log(combo, records,c,'itemclick')
                                        // },
                                    },
                                    search: {
                                        listeners: {
                                            add: function(a,b,c){
                                                code = this.lookupController().getView().lookupReference('customercombox').value
                                                // console.log(this.lookupController().getView().lookupReference('pocontainer'),"this.lookupController().getView().lookupReference('pocontainer')")
                                                // u_GTPREFNO = this.lookupController().getView().lookupReference('pocontainer').selection.u_GTPREFNO

                                                u_GTPREFNO_s = [];
                                                polists = this.lookupController().getView().lookupReference('pocontainer').getStore().data.items;
                                                polists.map(function(value, index){
                                                    u_GTPREFNO_s.push(value.data.u_GTPREFNO);
                                                })
                                                // console.log(u_GTPREFNO_s,'polists');

                                                url = 'index.php?hdl=collection&action=getPreDraftGrnItemList' + '&code=' + code + '&u_GTPREFNO_s=' + JSON.stringify(u_GTPREFNO_s);
                                                if (this.getSearchStore().getProxy().url != url){
                                                    // this.getView().lookupReference('searchitemscontainer').getStore().removeAll()
                                                    this.getSearchStore().removeAll();
                                                    this.getSearchStore().load()
                                                }
                                                this.getSearchStore().getProxy().setUrl(url)
                                            },
                                            statesave: function(a,b,c){
                                                console.log(a,b,c, 'statesave')
                                            },
                                            added: function(combo, records,c) {
                                                console.log(combo, records,c,'addedserach')
                                            },

                                            activate: function(a,b,c){
                                                // console.log(a,b,c,'activate');
                                                // reset itemgird->search
                                                code = this.lookupController().getView().lookupReference('customercombox').value
                                                u_GTPREFNO_s = [];
                                                polists = this.lookupController().getView().lookupReference('pocontainer').getStore().data.items;
                                                polists.map(function(value, index){
                                                    u_GTPREFNO_s.push(value.data.u_GTPREFNO);
                                                })
                                                // console.log(u_GTPREFNO_s,'polists');

                                                url = 'index.php?hdl=collection&action=getPreDraftGrnItemList' + '&code=' + code + '&u_GTPREFNO_s=' + JSON.stringify(u_GTPREFNO_s);
                                                if (this.getSearchStore().getProxy().url != url){
                                                    // this.getView().lookupReference('searchitemscontainer').getStore().removeAll()
                                                    this.getSearchStore().removeAll();
                                                    this.getSearchStore().load()
                                                }
                                                this.getSearchStore().getProxy().setUrl(url)
                                            },
                                        },

                                        field: 'item_html_list',

                                        reference: 'searchitemscontainer',
    
                                        // minWidth: 400,
                                        // minHeight: 300,
                                        width: 400,
                                        height: 300,
                                        store: {
                                            alias: 'store.itemstore',
                                            // type: 'ratecardstore',
                                            storeId: 'itemstore',
                                            reference: 'itemstore',
                                            model: 'snap.model.PreDraftGrn',
                                            sorters: 'name',
                                            proxy: {
                                                type: 'ajax',
                                                limitParam: null,
                                                url: 'index.php?hdl=collection&action=getPreDraftGrnItemList'
                                            }
                                        }
                                    }
                            }]
                        },
                        {
                            xtype: 'container',
                            margin: '0 0 0 50',
                            width: 600,
                            height: 300,
                            layout: 'column',
                            items: [{
                                    width: 300,
                                    height: 300,
                                    xtype: 'multiselector',
                                    title: 'Selected Rate Card Items',
                                    reference: 'ratecardgird',
                                    

                                    fieldName: 'u_itemcode',
                                    columns: {
                                        items: [
                                            {
                                                text: "Item Code",
                                                dataIndex: "u_itemcode"
                                            },{
                                                text: "Purity",
                                                dataIndex: "u_purity"
                                            },{
                                                text: 'XAU Weight',
                                                dataIndex: 'gtp_xauweight',
                                            }
                                            
                                        ],
                                        defaults: {
                                            flex: 1
                                        }
                                    },
                                    
                                    forceFit: true,
                                    // minHeight: 1000,
                                    bind: {
                                        store: '{theCompany2_alpha.details}',
                                        selection: '{theCompany2}',
                                    },
    
                                    viewConfig: {
                                        deferEmptyText: false,
                                        emptyText: 'No Rate Card selected'
                                    },
                                    // onSelectionChange: function(selModel, records) {
                                    //     console.log('a')
                                    // },
                                    // onChange: function(selModel, records) {
                                    //     console.log('b')
                                    // },
                                    listeners:{
                                        // onSelectionChange: function(selModel, records) {
                                        //     console.log('aa')
                                        // },
                                        // onChange: function(selModel, records) {
                                        //     console.log('bb')
                                        // },
                                        select: function(combo, records,c) {
                                            console.log(combo, records,c,'cc')
                                            console.log('readonly',combo.selected.items[0].data.u_purity_readonly)
                                            // purity_value = combo.view.lookupReferenceHolder().lookupReference('u_purity').value;
                                            // readonly = (purity_value.toString() != '0') ? true : false;
                                            // combo.view.lookupReferenceHolder().lookupReference('u_purity').setReadOnly(readonly)
                                        }
                                    },
                                    search: {
                                        listeners: {
                                            add: function(a,b,c){
                                                code = this.lookupController().getView().lookupReference('customercombox').value
                                                url = 'index.php?hdl=collection&action=getSapRateCardList' + '&code=' + code
                                                this.getSearchStore().getProxy().setUrl(url)

                                                if (this.getSearchStore().getProxy().url != url){
                                                    this.getView().lookupReference('pocontainer').getStore().removeAll()
                                                    this.getSearchStore().removeAll();
                                                    this.getSearchStore().load()
                                                }
                                            },
                                        },

                                        field: 'item_html_list',
    
                                        // minWidth: 400,
                                        // minHeight: 300,
                                        width: 400,
                                        height: 300,
                                        store: {
                                            alias: 'store.ratecardstore',
                                            // type: 'ratecardstore',
                                            storeId: 'ratecardstore',
                                            reference: 'ratecardstore',
                                            model: 'snap.model.Ratecard',
                                            sorters: 'name',
                                            proxy: {
                                                type: 'ajax',
                                                limitParam: null,
                                                url: 'index.php?hdl=collection&action=getSapRateCardList'
                                            }
                                        }
                                    }
                            },
                            {
                                // onSelectionChange: function(selModel, records) {
                                //     console.log(selModel, records, 'xxx')
                                //     var rec = records[0];
                                    
                                //     if (rec) {
                                //         this.getView().getForm().loadRecord(rec);
                                //     }
                                // },
                                xtype: 'fieldset',
                                title: 'Rate Card details',
                                minWidth: 200,
                                columnWidth: 0.35,
                                margin: '0 0 0 25',
                                layout: 'anchor',
                                defaultType: 'textfield',

                                items: [{
                                    fieldLabel: 'Weight',
                                    bind: '{theCompany2.gtp_inputweight}',
                                    reference: 'inputweight',
                                    // readOnly: true
                                    value: '',
                                    enableKeyEvents: true,
                                    listeners: {
                                        keyup: function(field, newVal, oldVal) {

                                            purity = this.lookupReferenceHolder().lookupReference('u_purity').value;

                                            newVal = parseFloat(field.value);
                                            newvalue = parseFloat(newVal) * parseFloat(purity) / 100
                                            this.lookupReferenceHolder().lookupReference('inputxauweight').setValue(newvalue);


                                            // total_xauweight,
                                            // total_balanceweight,
                                            // total_purity,
                                            this.lookupController().cmdc('1');
                                        }
                                    },
                                }, {
                                    fieldLabel: 'Purity',
                                    reference: 'u_purity',
                                    bind: {
                                        value: '{theCompany2.u_purity}',
                                        // readOnly: ('{theCompany2.u_purity}' != '0') ? true : false,
                                        readOnly: '{theCompany2.u_purity_readonly}',
                                    },
                                    
                                    enableKeyEvents: true,
                                    listeners: {
                                        keyup: function(field, newVal, oldVal) {

                                            purity = this.lookupReferenceHolder().lookupReference('u_purity').value;

                                            newVal = parseFloat(field.value);
                                            newvalue = parseFloat(newVal) * parseFloat(purity) / 100
                                            this.lookupReferenceHolder().lookupReference('inputxauweight').setValue(newvalue);


                                            // total_xauweight,
                                            // total_balanceweight,
                                            // total_purity,
                                            this.lookupController().cmdc('1');
                                        }
                                    },
                                },
                                //  {
                                //     fieldLabel: '% Change',
                                //     bind: '{theCompany.priceChangePct}'
                                // },
                                {
                                    labelAlign: 'top',
                                    // xtype: 'datefield',
                                    fieldLabel: 'Total XAU Weight',
                                    labelSeparator: '',
                                    reference: 'inputxauweight',
                                    // bind: '{inputxauweight}',
                                    bind: '{theCompany2.gtp_xauweight}',



                                    // This field is only set when the price changes
                                    // The Model rejects set changes.
                                    readOnly: true
                                },{
                                    xtype: 'button',
                                    text: 'Save Weight',
                                    handler: 'saveSingleDraftInput',
                                    align: 'right'
                                }]
                            }]
                        },{
                            xtype: 'fieldset',
                            
                            layout:{
                                type: 'hbox',
                                align: "flex",
                            },
                            margin: '0 0 0 20',
                            defaults: {
                                margin: '0 20 0 20',
                                width: 200,
                                align : 'fit'
                            },
                            // flex: 4,
                            items: [
                                {
                                    xtype: 'displayfield', 
                                    fieldLabel: 'Weight',
                                    reference: 'display_weight', // total po gross weight
                                    bind: '{total_poweight}'
                                },
                                {
                                    xtype: 'displayfield', 
                                    fieldLabel: 'Balance Weight',
                                    reference: 'display_balanceweight', // remaining po gross weight  
                                    bind: '{total_balanceweight}'
                                },
                                {
                                    xtype: 'displayfield', 
                                    fieldLabel: 'Purity',
                                    reference: 'display_purity', 
                                    bind: '{total_purity}'
                                },
                                {
                                    xtype: 'displayfield', 
                                    fieldLabel: 'XAU Weight',
                                    reference: 'display_xauweight',
                                    bind: '{total_xauweight}'
                                },
                            ]
                        },
                        
                    ]
                },
            ]
        },
    ]
});