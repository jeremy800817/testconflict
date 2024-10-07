// Init searchfield
Ext.define('snap.view.otcregister.OTCRegister_POSARRAHNU',{
    extend: 'Ext.panel.Panel',
    xtype: 'otcregisterview_POSARRAHNU',

    requires: [

        // 'Ext.layout.container.Fit',
        'snap.view.otcregister.OTCRegisterController',
        // 'snap.view.orderdashboard.OrderDashboardModel',
        // 'snap.store.OrderPriceStream',


    ],
    store: {
        occupationcategory: Ext.create('snap.store.OccupationCategory'),
        occupationsubcategorychecker: Ext.create('snap.store.OccupationSubCategory'),
        occupationsubcategory: Ext.create('snap.store.OccupationSubCategory'),
        bankaccounts: Ext.create('snap.store.BankAccounts')
    },
    controller: 'otcregister-otcregister',
    viewModel: {
        data: {
            name: "Register",
            fees: [],
            permissions : [],
            status: '',

        }
    },

    initComponent: function(formView, form, record, asyncLoadCallback){
        elmnt = this;
        vm = this.getViewModel();

        // Ext.create('snap.store.OrderPriceStream');
        async function getList(){
            // const item_list = await snap.getApplication().sendRequest({
            //     hdl: 'orderdashboard', 'action': 'fillform',
            //     id: 1,
            // }, 'Fetching data from server....').then(
            // function(data) {
            //     if (data.success) {
            //         //alert(data.fees);
            //         vm.set('fees', data.fees);
            //         // Set product permissions 
            //         vm.set('permissions', data.permissions);

            //         // Set Status
            //         vm.set('status', data.status);

            //         // If no products
            //         /*
            //         if (data.items){

            //             Ext.getCmp('orderdashboardspotorderform').setHidden(false);
            //             Ext.getCmp('orderdashboardfutureorderform').setHidden(false);
                       
            //         }else {
            //             Ext.getCmp('orderdashboardspotorderformblank').setHidden(false);
            //             Ext.getCmp('orderdashboardfutureorderformblank').setHidden(false);
            //         }*/

            //         /* ****************************************** Old **********************************************************
            //         if(data.usertype == 'Operator' || data.usertype == 'Sale'){
                   
            //            // Ext.getCmp('orderdashboardspotorderform').setHidden(false);
            //            // Ext.getCmp('orderdashboardfutureorderform').setHidden(false);

    
            //             Ext.getCmp('orderdashboardspotorderformblank').setHidden(false);
            //             Ext.getCmp('orderdashboardfutureorderformblank').setHidden(false);
            //             // Reset Grid Data for List
                       
                        
                        
            //         }else if (data.usertype == 'Customer' ){

            //             Ext.getCmp('orderdashboardspotorderform').setHidden(false);
            //             Ext.getCmp('orderdashboardfutureorderform').setHidden(false);
                       
            //         }else {
            //             Ext.getCmp('orderdashboardspotorderformblank').setHidden(false);
            //             Ext.getCmp('orderdashboardfutureorderformblank').setHidden(false);
            //         }
            //         ****************************************** End Old ********************************************************** */

            //         if(data.status == 'offline'){
                        
            //             // Ext.getCmp('spotorderonlinestatus').setText('Offline');
            //             // Ext.getCmp('spotorderonlinestatus').setStyle('background-color', '#C0282E');
            //         }else if(data.status == 'online'){
            //             // Ext.getCmp('spotorderonlinestatus').setText('Online');
            //             // Ext.getCmp('spotorderonlinestatus').setStyle('background-color', '#4CAF50');
            //         }else{
            //             // Ext.getCmp('spotorderonlinestatus').setText('No Response');
            //             // Ext.getCmp('spotorderonlinestatus').setStyle('background-color', '#C0282E');
            //         }

            //         // Set PartnerService permissions
            //         //vm.set('fees', data.fees);

            //         //Ext.getCmp('userrefineryfee').getStore().loadData(data.items);

            //         //Bid Price
            //         //Ext.getCmp('bidpricedashboard').getStore().loadData(data.items);
            //         //Ext.getCmp('askpricedashboard').getStore().loadData(data.items);
                    
            //         //alert(data.items);
            //         //console.log('data_success')
            //         //return data
            //     }
            // });
            return true
        }
        getList().then(
            function(data){
                //elmnt.loadFormSeq(data.return)
            }
        )

        this.callParent(arguments);
    },
    permissionRoot: '/root/gtp/cust',
    //store: { type: 'Order' },
    store: 'orderPriceStream',	
    // formDialogWidth: 950,
    layout: 'fit',
    // width: 500,
    // height: 400,
    cls: Ext.baseCSSPrefix + 'shadow',

    //bodyPadding: 25,

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
        //width: 500,
        //height: 400,
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
            //bodyPadding: 10
        },
        cls: 'otc-main',
        bodyCls: 'otc-main-body',
        items: [
            // Hidden for future use
            // {
            //     xtype: 'panel',
            //     title: getText('nricupload'),
            //     layout: 'hbox',
            //     collapsible: true,
            //     cls: 'otcpanel',
            //     defaults: {
            //       layout: 'vbox',
            //       flex: 1,
            //       bodyPadding: 10
            //     },
            //     margin: "10 0 0 0",
            //     items: [
            //         {
            //             xtype: 'panel',
            //             defaults: {
            //               labelStyle: 'font-weight:bold',
            //             },
            //             layout: {
            //                 type: 'vbox',
            //                 align: 'center',
            //                 pack: 'center'
            //             },
            //             flex:1, 
            //             items: [
            //                     {
            //                     xtype:'image',
            //                     src: 'src/resources/images/nric-front.png',
            //                     // src: 'https://fiddle.sencha.com/classic/resources/images/sencha-logo.png',
            //                     region: 'south',
            //                     style: {
            //                         'display': 'block',
            //                         'margin': 'auto'
            //                     },
                           
            //                     width: 282,
            //                     height: 166,
            //                 },
            //                 { xtype: 'filefield',fieldLabel: getText('uploadnricfront')+' <span style="color:red;">*</span>', name: 'uploadnricfront', margin: "10 0 0 0", width: '90%', flex: 4, allowBlank: false, reference: 'uploadnricfront',
            //                     listeners:{
            //                         afterrender:function(cmp){
            //                         cmp.fileInputEl.set({
            //                             accept:'image/*' // or w/e type
            //                         });
            //                         }
            //                     }
            //                 },
            //             ]
            //         },
               
            //       {
            //         xtype: 'panel',
            //         defaults: {
            //           labelStyle: 'font-weight:bold',
            //         },
            //         flex:1, 
            //         layout: {
            //             type: 'vbox',
            //             align: 'center',
            //             pack: 'center'
            //         },
            //         items: [
            //             {
            //                 xtype:'image',
            //                 src: 'src/resources/images/nric-back.png',
            //                 // src: 'https://fiddle.sencha.com/classic/resources/images/sencha-logo.png',
            //                 region: 'south',
            //                 style: {
            //                     'display': 'block',
            //                     'margin': 'auto'
            //                 },
                       
            //                 width: 282,
            //                 height: 166,
            //             },
            //             { xtype: 'filefield',fieldLabel: getText('uploadnricback')+' <span style="color:red;">*</span>', name: 'uploadnricback', margin: "10 0 0 0", width: '90%', flex: 4, allowBlank: false, reference: 'uploadnricback',
            //                 listeners:{
            //                     afterrender:function(cmp){
            //                     cmp.fileInputEl.set({
            //                         accept:'image/*' // or w/e type
            //                     });
            //                     }
            //                 }
            //             },
            //         ]
            //       }
            //     ]
      
            // },
            // Register
            {
                xtype: 'form',
                reference: 'otcregisterform',
                title: getText('register'),
                // layout: 'hbox',
                collapsible: true,
                // cls: 'otc-panel',
                defaults: {
                  layout: 'vbox',
                  flex: 1,
                  bodyPadding: 10
                },
                margin: "10 0 0 0",
                items:[{
                    layout: {
                        type: 'table',
                        columns: 3,
                        tableAttrs: {
                            style: {
                                width: '100%',
                                height: '100%',
                                top: '10px',
                            },
                        },
                        tdAttrs: {
                            valign: 'top',
                            height: '100%',
                            'background-color': 'grey',
                        }
                    },
                    xtype: 'form',
                    scrollable: false,
                    defaults: {
                        bodyPadding: '5',
                    },
                    reference: "register-form-personal",
                    title: getText('personalinformation'),
                    items: [
                        {
                           
                            items: [
                                { xtype: 'hidden', hidden: true, name: 'id' },
                                { xtype: 'textfield', fieldLabel: getText('fullname')+' <span style="color:red;">*</span>', name: 'fullname', width: '90%', labelWidth: 150, allowBlank: false},
                                { xtype: 'textfield', reference: 'email', fieldLabel: getText('email')+' <span style="color:red;">*</span>', name: 'email', width: '90%', labelWidth: 150, allowBlank: false, vtype: 'email'},
                                // { xtype: 'displayfield', reference: "email_error", hidden: true, cls: 'mini_error_text', labelWidth: 150, flex: 0.5, value: getText('email_error'), name: 'email_error', width: '90%', margin: '0 10 0 0', },
                                { xtype: 'textfield', fieldLabel: getText('nric')+' <span style="color:red;">*</span>', name: 'nric', width: '90%', labelWidth: 150, allowBlank: false,
                                    minLength     : 12,
                                    maxLength     : 12,
                                    enforceMinLength : true,
                                    enforceMaxLength : true,
                                    maskRe: /[0-9]/,
                                    validator: function(v) {
                                        return /^-?[0-9]*(\.[0-9]{1,2})?$/.test(v)? true : 'Only positive/negative float (x.yy)/int formats allowed!';
                                    },

                                },
                                { xtype: 'textfield', fieldLabel: getText('mobile')+' <span style="color:red;">*</span>', name: 'mobile', width: '90%', labelWidth: 150, allowBlank: false,
                                    minLength     : 10,
                                    maxLength     : 15,
                                    enforceMinLength : true,
                                    enforceMaxLength : true,
                                    maskRe: /[0-9-]/,
                                    validator: function(v) {
                                        return /^-?[0-9]*(\.[0-9]{1,2})?$/.test(v)? true : 'Only positive/negative float (x.yy)/int formats allowed!';
                                    },

                                },
                                { xtype: 'textarea', fieldLabel: getText('address')+' <span style="color:red;">*</span>', name: 'address', width: '90%', labelWidth: 150, allowBlank: false},
                               
                                //{ xtype: 'combobox', fieldLabel:'Announcement Type', store: {type: 'array', fields: ['id', 'code']}, queryMode: 'local', remoteFilter: false, name: 'type', valueField: 'id', displayField: 'code', reference: 'type', forceSelection: true, editable: false },
                                // { xtype: 'datefield', fieldLabel: 'Start On', name: 'displaystarton', width: '90%', format: 'Y-m-d H:i:s' },
                                // { xtype: 'datefield', fieldLabel: 'End On', name: 'displayendon', width: '90%', format: 'Y-m-d H:i:s' },
                                //{ xtype: 'textfield', fieldLabel: 'Timer', name: 'timer', width: '90%' },
                                // { xtype: 'radiogroup', fieldLabel: 'Status', width: '90%',
                                //     items: [{
                                //         boxLabel  : 'Inactive',
                                //         name      : 'status',
                                //         inputValue: '0'
                                //     },{
                                //         boxLabel  : 'Active',
                                //         name      : 'status',
                                //         inputValue: '1'
                                //     },]
                                // },
                                // { xtype: 'radiogroup', fieldLabel: 'Is Mobile', width: '90%',
                                //     items: [{
                                //         boxLabel  : 'No',
                                //         name      : 'ismobile',
                                //         inputValue: '0'
                                //     },{
                                //         boxLabel  : 'Yes',
                                //         name      : 'ismobile',
                                //         inputValue: '1'
                                //     },]
                                // },
                                {
                                    xtype: 'panel',
                                    border: false,
                                    layout: 'hbox',
                                    width: '90%',
                                    items: [
                                        { xtype: 'textfield', flex: 0.3, fieldLabel: getText('city')+' <span style="color:red;">*</span>', name: 'city', width: '90%', labelWidth: 150, margin: '0 10 0 0', allowBlank: false},
                                        { xtype: 'textfield', flex: 0.3, fieldLabel: getText('postcode')+' <span style="color:red;">*</span>', name: 'postcode', width: '90%', labelWidth: 150, margin: '0 10 0 0', allowBlank: false,
                                            minLength     : 5,
                                            maxLength     : 5,
                                            enforceMinLength : true,
                                            enforceMaxLength : true,
                                        },
                                        {
                                            xtype: 'combobox', flex: 0.3, fieldLabel: getText('state')+' <span style="color:red;">*</span>', store: Ext.create('snap.store.States'), queryMode: 'local', remoteFilter: false,
                                            name: 'state', valueField: 'code', displayField: 'name', reference: 'parstate',
                                            forceSelection: true, editable: false, allowBlank: false
                                        },
                                        { xtype: 'panel', flex: 0.1, width: '90%', labelWidth: 150, margin: '0 10 0 0', allowBlank: false},
                                    ]
                                },
                            ]
                        },
                        // {
                        //     items: [
                        //         { xtype: 'fieldset', title: 'Picture', collapsible: false,
                        //             default: { labelWidth: 90, layout: 'hbox'},
                        //             items: [
                        //                 { xtype: 'filefield', name: 'picture', width: '90%' },
                        //                 { xtype: 'displayfield', reference: 'attachmentPicture', fieldStyle: 'color:#5fa2dd;margin:0!important;min-height:200px; min-width:200px', height: 292, },
                        //             ]
                        //         },
                        //     ]
                        // }
                    ]
                },
                {
                    layout: {
                        type: 'table',
                        columns: 3,
                        tableAttrs: {
                            style: {
                                width: '100%',
                                height: '100%',
                                top: '10px',
                            },
                        },
                        tdAttrs: {
                            valign: 'top',
                            height: '100%',
                            'background-color': 'grey',
                        }
                    },
                    xtype: 'form',
                    scrollable: false,
                    defaults: {
                        bodyPadding: '5',
                    },
                    reference: "register-form-nok",
                    title: getText('nokinformation'),
                    items: [
                        {
                           
                            items: [
                                { xtype: 'hidden', hidden: true, name: 'id' },
                                { xtype: 'textfield', fieldLabel: getText('fullname')+' <span style="color:red;">*</span>', name: 'nokfullname', width: '90%', labelWidth: 150, allowBlank: false},
                                { xtype: 'textfield', fieldLabel: getText('email')+' <span style="color:red;">*</span>', name: 'nokemail', width: '90%', labelWidth: 150, allowBlank: false},
                                { xtype: 'textfield', fieldLabel: getText('nric')+' <span style="color:red;">*</span>', name: 'noknric', width: '90%', labelWidth: 150, allowBlank: false,
                                    minLength     : 12,
                                    maxLength     : 12,
                                    enforceMinLength : true,
                                    enforceMaxLength : true,
                                    maskRe: /[0-9]/,
                                    validator: function(v) {
                                        return /^-?[0-9]*(\.[0-9]{1,2})?$/.test(v)? true : 'Only positive/negative float (x.yy)/int formats allowed!';
                                    },

                                },
                                { xtype: 'textfield', fieldLabel: getText('mobile')+' <span style="color:red;">*</span>', name: 'nokmobile', width: '90%', labelWidth: 150, allowBlank: false,
                                    minLength     : 10,
                                    maxLength     : 15,
                                    enforceMinLength : true,
                                    enforceMaxLength : true,
                                    maskRe: /[0-9.-]/,
                                    validator: function(v) {
                                        return /^-?[0-9]*(\.[0-9]{1,2})?$/.test(v)? true : 'Only positive/negative float (x.yy)/int formats allowed!';
                                    },

                                },
                                { xtype: 'textarea', fieldLabel: getText('address')+' <span style="color:red;">*</span>', name: 'nokaddress', width: '90%', labelWidth: 150, allowBlank: false},
                                { xtype: 'textfield', fieldLabel: getText('relationship')+' <span style="color:red;">*</span>', name: 'nokrelationship', width: '90%', labelWidth: 150, allowBlank: false},
                                // {
                                //     xtype: 'combobox', fieldLabel: getText('relationship')+' <span style="color:red;">*</span>', store: Ext.create('snap.store.States'), queryMode: 'local', remoteFilter: false,
                                //     name: 'nokrelationship', valueField: 'code', displayField: 'name', reference: 'parstate',
                                //     width: '90%', labelWidth: 150, ZforceSelection: true, editable: false, allowBlank: false
                                // },
                            ]
                        },
                        // {
                        //     items: [
                        //         { xtype: 'fieldset', title: 'Picture', collapsible: false,
                        //             default: { labelWidth: 90, layout: 'hbox'},
                        //             items: [
                        //                 { xtype: 'filefield', name: 'picture', width: '90%' },
                        //                 { xtype: 'displayfield', reference: 'attachmentPicture', fieldStyle: 'color:#5fa2dd;margin:0!important;min-height:200px; min-width:200px', height: 292, },
                        //             ]
                        //         },
                        //     ]
                        // }
                    ]
                },
                // item 2
                {
                    layout: {
                        type: 'table',
                        columns: 3,
                        tableAttrs: {
                            style: {
                                width: '100%',
                                height: '100%',
                                top: '10px',
                            },
                        },
                        tdAttrs: {
                            valign: 'top',
                            height: '100%',
                            'background-color': 'grey',
                        }
                    },
                    xtype: 'form',
                    scrollable: false,
                    defaults: {
                        bodyPadding: '5',
                    },
                    reference: "register-form-bankaccountinfo",
                    title: getText('bankaccountinformation'),
                    items: [
                        {
                           
                            items: [
                                // { xtype: 'textfield', fieldLabel: getText('bankaccount'), name: 'bankaccount', width: '90%', labelWidth: 150, allowBlank: true},
                                {
                                    xtype: 'combobox', 
                                    width: '90%', labelWidth: 150,
                                    fieldLabel: getText('bankaccount')+' <span style="color:red;">*</span>',
                                    store: Ext.getStore('bankaccounts').load(),
                                    queryMode: 'local',
                                    remoteFilter: false,
                                    name: 'bankaccounts',
                                    valueField: 'id',
                                    displayField: 'value',
                                    forceSelection: true, editable: false,
                                    allowBlank: false,
                                    renderer: function (value, metaData, record, rowIndex, colIndex, store, view) {
               
                                        var productitems = Ext.getStore('bankaccounts').load();
                                        console.log(productitems);
                                        var catRecord = productitems.findRecord('id', value);
                                        return catRecord ? catRecord.get('value') : '';
                                    },
                                },
                                { xtype: 'textfield', fieldLabel: 'Bank Account Number' +' <span style="color:red;">*</span>', name: 'bankaccountnumber', width: '90%', labelWidth: 150, allowBlank: false},
                                // { xtype: 'textfield', fieldLabel: getText('occupationcategory'), name: 'occupationcategory', width: '90%', labelWidth: 150, },
                                // {
                                //     xtype: 'combobox', width: '90%', labelWidth: 150, fieldLabel: getText('occupationcategory'), store: Ext.create('snap.store.OccupationCategory'), queryMode: 'local', remoteFilter: false,
                                //     name: 'occupationcategory', valueField: 'id', displayField: 'value', reference: 'occupationcategory',
                                //     forceSelection: true, editable: false, allowBlank: false
                                // },
                                {
                                    xtype: 'combobox', 
                                    width: '90%', labelWidth: 150,
                                    fieldLabel: getText('occupationcategory')+' <span style="color:red;">*</span>',
                                    store: Ext.getStore('occupationcategory').load(),
                                    queryMode: 'local',
                                    remoteFilter: false,
                                    name: 'occupationcategory',
                                    reference: 'occupationcategory',
                                    valueField: 'id',
                                    displayField: 'value',
                                    forceSelection: true, editable: false,
                                    allowBlank: false,
                                    renderer: function (value, metaData, record, rowIndex, colIndex, store, view) {
               
                                        var productitems = Ext.getStore('occupationcategory').load();
                                        console.log(productitems);
                                        var catRecord = productitems.findRecord('id', value);
                                        return catRecord ? catRecord.get('value') : '';
                                    },
                                    listeners: {
                                        change: function(field, newVal, oldVal) {
             
                                            // do checking, if newval is found in occupationsubcategory, show field.
                                            // else hide field if not found in subcategory
                                            sub = Ext.getStore('occupationsubcategory').load();
                                            // check if subcategory exists
                                            subcategory = sub.findRecord('occ_id', newVal);

                                            // if return, check if exact match
                                            match = false;
                                            if(subcategory){
                                          
                                                if (subcategory.data.occ_id == newVal){
                                                    match = true;
                                                }else{
                                                    match = false;
                                                }
                                            }
                                            
                                            // clear filter
                                            this.lookupController().lookupReference('occupationsubcategory').store.clearFilter()

                                            if(match){
                                                this.lookupController().lookupReference('occupationsubcategory').show();
                                            }else{
                                                this.lookupController().lookupReference('occupationsubcategory').setValue(null);
                                                this.lookupController().lookupReference('occupationsubcategory').hide();
                                            }
                                            
                                        }
                                    }
     
                                },
                                // {
                                //     xtype: 'combobox', width: '90%', labelWidth: 150, fieldLabel: getText('occupationsubcategory'), store: Ext.create('snap.store.OccupationSubCategory'), queryMode: 'local', remoteFilter: false,
                                //     name: 'occupationsubcategory', valueField: 'id', displayField: 'value', reference: 'occupationsubcategory',
                                //     forceSelection: true, editable: false, allowBlank: false
                                // },
                                {
                                    xtype: 'combobox', 
                                    width: '90%', labelWidth: 150,
                                    fieldLabel: getText('occupationsubcategory')+' <span style="color:red;">*</span>',
                                    store: Ext.getStore('occupationsubcategory').load(),
                                    queryMode: 'local',
                                    remoteFilter: false,
                                    name: 'occupationsubcategory',
                                    reference: 'occupationsubcategory',
                                    valueField: 'id',
                                    displayField: 'value',
                                    forceSelection: true, editable: false,
                                    allowBlank: true,
                                    hidden: true,
                                    // store: {
                                    //     filters: [{
                                    //         property: 'id',
                                    //         value: 4,
                                    //     }]
                                    // },
                                    listeners: {
                                        // filter fields by selected occupationid
                                        expand: function(combo){
                                            // combo.store.load({
                                            //     //page:2,
                                            //     start: 0,
                                            //     limit: 1500
                                            // })
                                            combo.store.clearFilter();
                                            combo.store.filter("occ_id", this.lookupController().lookupReference('occupationcategory').value);
                                          
                                            // combo.store.filter("group", myView.partnerId);
                                        }
                                    },
                                    renderer: function (value, metaData, record, rowIndex, colIndex, store, view) {
                                        var productitems = Ext.getStore('occupationsubcategory').load();
                                        console.log(productitems);
                                        var catRecord = productitems.findRecord('id', value);
                                    
                                        return catRecord ? catRecord.get('value') : '';
                                    },
                                },
                                // { xtype: 'textarea', fieldLabel: 'Description', name: 'description', width: '90%' },
                                //{ xtype: 'textfield', fieldLabel: 'Content', name: 'content', width: '90%' },
                                //{ xtype: 'textfield', fieldLabel: 'Content Repo', name: 'contentrepo', width: '90%' },
                                { xtype: 'textfield', fieldLabel: getText('campaigncode'), name: 'campaigncode', width: '90%', labelWidth: 150, },
                                //{ xtype: 'combobox', fieldLabel:'Announcement Type', store: {type: 'array', fields: ['id', 'code']}, queryMode: 'local', remoteFilter: false, name: 'type', valueField: 'id', displayField: 'code', reference: 'type', forceSelection: true, editable: false },
                            ]
                        },
                        // {
                        //     items: [
                        //         { xtype: 'fieldset', title: 'Picture', collapsible: false,
                        //             default: { labelWidth: 90, layout: 'hbox'},
                        //             items: [
                        //                 { xtype: 'filefield', name: 'picture', width: '90%' },
                        //                 { xtype: 'displayfield', reference: 'attachmentPicture', fieldStyle: 'color:#5fa2dd;margin:0!important;min-height:200px; min-width:200px', height: 292, },
                        //             ]
                        //         },
                        //     ]
                        // }
                    ]
                },
                // Item 3
                // {
                //     layout: {
                //         type: 'table',
                //         columns: 3,
                //         tableAttrs: {
                //             style: {
                //                 width: '100%',
                //                 height: '100%',
                //                 top: '10px',
                //             },
                //         },
                //         tdAttrs: {
                //             valign: 'top',
                //             height: '100%',
                //             'background-color': 'grey',
                //         }
                //     },
                //     xtype: 'form',
                //     scrollable: false,
                //     defaults: {
                //         bodyPadding: '5',
                //     },
                //     reference: "register-form-password",
                //     title: getText('setpassword'),
                //     items: [
                //         {
                //             xtype: 'panel',
                //             border: false,
                //             layout: 'hbox',
                //             width: '90%',
                        
                //             items: [
                //                 {
                //                     xtype: 'panel',
                //                     // defaults: {
                //                     //   labelStyle: 'font-weight:bold',
                //                     // },
                //                     // layout: {
                //                     //     type: 'vbox',
                //                     //     align: 'center',
                //                     //     pack: 'center'
                //                     // },
                //                     flex:1, 
                //                     items: [
                //                         { xtype: 'textfield', inputType: 'password', labelWidth: 150, flex: 0.5, fieldLabel: getText('enterpassword')+' <span style="color:red;">*</span>', name: 'enterpassword', width: '90%', margin: '0 10 0 0', 
                //                             minLength     : 8,
                //                             maxLength     : 30,
                //                             enforceMinLength : true,
                //                             enforceMaxLength : true,
                //                         },
                //                         { xtype: 'displayfield', reference: "password_error_1", hidden: true, cls: 'mini_error_text', labelWidth: 150, flex: 0.5, value: getText('confirmpassword'), name: 'enterpassword', width: '90%', margin: '0 10 0 0', },
                                      
                //                     ]
                //                 },
                //                 {
                //                     xtype: 'panel',
                //                     // defaults: {
                //                     //   labelStyle: 'font-weight:bold',
                //                     // },
                //                     // layout: {
                //                     //     type: 'vbox',
                //                     //     align: 'center',
                //                     //     pack: 'center'
                //                     // },
                //                     flex:1, 
                //                     items: [
                //                         { xtype: 'textfield', inputType: 'password', labelWidth: 150, flex: 0.5, fieldLabel: getText('confirmpassword')+' <span style="color:red;">*</span>', name: 'confirmpassword', width: '90%', margin: '0 10 0 0', 
                //                             minLength     : 8,
                //                             maxLength     : 30,
                //                             enforceMinLength : true,
                //                             enforceMaxLength : true,
                //                         },
                //                         { xtype: 'displayfield', reference: "password_error_2", hidden: true, cls: 'mini_error_text', labelWidth: 150, flex: 0.5, value: getText('confirmpassword'), name: 'confirmpassword', width: '90%', margin: '0 10 0 0', },
                //                     ]
                //                 },
                               
                                
                    
                //                 { xtype: 'panel', flex: 0.1, width: '90%', margin: '0 10 0 0', },
                //             ]
                //         },
                       
                //     ]
                // },
                
                // Final item
                // {
                //     layout: {
                //         type: 'table',
                //         columns: 3,
                //         tableAttrs: {
                //             style: {
                //                 width: '100%',
                //                 height: '100%',
                //                 top: '10px',
                //             },
                //         },
                //         tdAttrs: {
                //             valign: 'top',
                //             height: '100%',
                //             'background-color': 'grey',
                //         }
                //     },
                //     xtype: 'form',
                //     scrollable: false,
                //     defaults: {
                //         bodyPadding: '5',
                //     },
                //     title: getText('securitypin'),
                //     reference: "register-form-pin",
                //     items: [
                //         {
                //             layout: 'vbox',
                //             items: [
                //                 {
                //                     xtype: 'panel',
                //                     border: false,
                //                     layout: 'hbox',
                //                     width: '90%',
                //                     flex: 1,
                //                     items: [
                //                         {
                //                             xtype: 'displayfield',
                //                             name: 'pincode',
                //                             fieldLabel: getText('pincode')+' <span style="color:red;">*</span>',
                //                             flex: 0.3,
                //                             labelWidth: 150,
                //                         },
                //                         { xtype: 'textfield', name: "init_pin_1", width: '5%',
                //                             maskRe: /[0-9-]/,
                //                             maxLength: 1,
                //                             enforceMaxLength: true,
                //                             reference: 'init_pin_1',
                //                             inputType: 'password',
                //                             flex: 0.12,
                //                             listeners: {
                //                                 change: function(field, newVal, oldVal) {
                //                                     // debugger;
                //                                     this.lookupController().lookupReference('init_pin_2').focus();
                //                                 }
                //                                 // 'keyup':function(field, event){
                                    
                //                                 //     if(event.getKey() >= 65 && event.getKey() <= 90) {
                //                                 //        //the key was A-Z
                //                                 //     }
                //                                 //     if(event.getKey() >= 97 && event.getKey() <= 122) {
                //                                 //        //the key was a-z
                //                                 //     }
                //                                 // }
                //                             }
                //                         },
                //                         { xtype: 'textfield', name: "init_pin_2", width: '5%',
                //                             maskRe: /[0-9-]/,
                //                             maxLength: 1,
                //                             enforceMaxLength: true,
                //                             flex: 0.12,
                //                             reference: 'init_pin_2',
                //                             inputType: 'password',
                //                             listeners: {
                //                                 change: function(field, newVal, oldVal) {
                                                
                //                                     this.lookupController().lookupReference('init_pin_3').focus();
                //                                 }
                //                             }
                //                         },
                //                         { xtype: 'textfield', name: "init_pin_3", width: '5%',
                //                             maskRe: /[0-9-]/,
                //                             maxLength: 1,
                //                             enforceMaxLength: true,
                //                             flex: 0.12,
                //                             reference: 'init_pin_3',
                //                             inputType: 'password',
                //                             listeners: {
                //                                 change: function(field, newVal, oldVal) {
                                                
                //                                     this.lookupController().lookupReference('init_pin_4').focus();
                //                                 }
                //                             }
                //                         },
                //                         { xtype: 'textfield', name: "init_pin_4", width: '5%',
                //                             maskRe: /[0-9-]/,
                //                             maxLength: 1,
                //                             enforceMaxLength: true,
                //                             flex: 0.12,
                //                             reference: 'init_pin_4',
                //                             inputType: 'password',
                //                             listeners: {
                //                                 change: function(field, newVal, oldVal) {
                                                
                //                                     this.lookupController().lookupReference('init_pin_5').focus();
                //                                 }
                //                             }
                //                         },
                //                         { xtype: 'textfield', name: "init_pin_5", width: '5%',
                //                             maskRe: /[0-9-]/,
                //                             maxLength: 1,
                //                             enforceMaxLength: true,
                //                             flex: 0.12,
                //                             reference: 'init_pin_5',
                //                             inputType: 'password',
                //                             listeners: {
                //                                 change: function(field, newVal, oldVal) {
                                                
                //                                     this.lookupController().lookupReference('init_pin_6').focus();
                //                                 }
                //                             }
                //                         },
                //                         { xtype: 'textfield', name: "init_pin_6", width: '5%',
                //                             maskRe: /[0-9-]/,
                //                             maxLength: 1,
                //                             enforceMaxLength: true,
                //                             flex: 0.12,
                //                             reference: 'init_pin_6',
                //                             inputType: 'password',
                //                             listeners: {
                //                                 change: function(field, newVal, oldVal) {
                                                
                //                                     this.callParent(arguments);
                //                                 }
                //                             }
                //                         },
                //                         { xtype: 'panel', flex: 0.1, width: '90%', margin: '0 10 0 0', },
                //                     ]
                //                 },
                //                 { xtype: 'displayfield', reference: "pin_error_1", hidden: true, cls: 'mini_error_text', labelWidth: 150, flex: 0.5, value: '', name: 'pin_error_1', width: '90%', margin: '0 10 0 0', },
                //             ]
                //         },
                //         {
                //             layout: 'vbox',
                //             items: [
                //                 {
                //                     xtype: 'panel',
                //                     border: false,
                //                     layout: 'hbox',
                //                     width: '90%',
                //                     reference: "register-panel-confirmpin",
                //                     flex: 1,
                //                     items: [
                //                         {
                //                             xtype: 'displayfield',
                //                             name: 'confirmpincode',
                //                             fieldLabel: getText('confirmpincode')+' <span style="color:red;">*</span>',
                //                             flex: 0.3,
                //                             labelWidth: 150,
                //                         },
                //                         { xtype: 'textfield', name: "confirm_pin_1", width: '5%',
                //                             maskRe: /[0-9-]/,
                //                             maxLength: 1,
                //                             enforceMaxLength: true,
                //                             flex: 0.12,
                //                             inputType: 'password',
                //                             reference: 'confirm_pin_1',
                //                             listeners: {
                //                                 change: function(field, newVal, oldVal) {
                                                
                //                                     this.lookupController().lookupReference('confirm_pin_2').focus();
                //                                 }
                //                             }
                //                         },
                //                         { xtype: 'textfield', name: "confirm_pin_2", width: '5%',
                //                             maskRe: /[0-9-]/,
                //                             maxLength: 1,
                //                             enforceMaxLength: true,
                //                             flex: 0.12,
                //                             inputType: 'password',
                //                             reference: 'confirm_pin_2',
                //                             listeners: {
                //                                 change: function(field, newVal, oldVal) {
                                                
                //                                     this.lookupController().lookupReference('confirm_pin_3').focus();
                //                                 }
                //                             }
                //                         },
                //                         { xtype: 'textfield', name: "confirm_pin_3", width: '5%',
                //                             maskRe: /[0-9-]/,
                //                             maxLength: 1,
                //                             enforceMaxLength: true,
                //                             flex: 0.12,
                //                             inputType: 'password',
                //                             reference: 'confirm_pin_3',
                //                             listeners: {
                //                                 change: function(field, newVal, oldVal) {
                                                
                //                                     this.lookupController().lookupReference('confirm_pin_4').focus();
                //                                 }
                //                             }
                //                         },
                //                         { xtype: 'textfield', name: "confirm_pin_4", width: '5%',
                //                             maskRe: /[0-9-]/,
                //                             maxLength: 1,
                //                             enforceMaxLength: true,
                //                             flex: 0.12,
                //                             inputType: 'password',
                //                             reference: 'confirm_pin_4',
                //                             listeners: {
                //                                 change: function(field, newVal, oldVal) {
                                                
                //                                     this.lookupController().lookupReference('confirm_pin_5').focus();
                //                                 }
                //                             }
                //                         },
                //                         { xtype: 'textfield', name: "confirm_pin_5", width: '5%',
                //                             maskRe: /[0-9-]/,
                //                             maxLength: 1,
                //                             enforceMaxLength: true,
                //                             flex: 0.12,
                //                             inputType: 'password',
                //                             reference: 'confirm_pin_5',
                //                             listeners: {
                //                                 change: function(field, newVal, oldVal) {
                                                
                //                                     this.lookupController().lookupReference('confirm_pin_6').focus();
                //                                 }
                //                             }
                //                         },
                //                         { xtype: 'textfield', name: "confirm_pin_6", width: '5%',
                //                             maskRe: /[0-9-]/,
                //                             maxLength: 1,
                //                             enforceMaxLength: true,
                //                             flex: 0.12,
                //                             inputType: 'password',
                //                             reference: 'confirm_pin_6',
                //                             listeners: {
                //                                 onChange: function(newVal, oldVal) {
                                                
                //                                     this.callParent(arguments);
                //                                 }
                //                             }
                //                         },
                //                         { xtype: 'panel', flex: 0.1, width: '90%', margin: '0 10 0 0', },
                //                     ]
                //                 },
                //                 { xtype: 'displayfield', reference: "pin_error_2", hidden: true, cls: 'mini_error_text', labelWidth: 150, flex: 0.5, value: '', name: 'pin_error_2', width: '90%', margin: '0 10 0 0', },
                //             ]
                //         }
                //     ]
                // },
                
                ],
                // docked item button
                 dockedItems: [{
                    xtype: 'toolbar',
                    dock: 'bottom',
                    // style: 'opacity: 1.0;background: #ffffff;color: #ffffff; border-color: #ffffff; display: inline-block;box-sizing: border-box;color: #ffffff; font-weight: bold;text-transform: uppercase;letter-spacing: 1px;',      
                    //ui: 'footer',
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
                    cls: 'otc-main-form-button-bottom',
                    items: [{
                        text: getText('submit'),
                        handler: '',
                        //style: 'border-radius: 20px;opacity: 1.0;background: linear-gradient(269deg, #b08f26 0%, #d4af37 100%);text-color: #000000;text-transform: uppercase;',
                        // style: 'border-radius: 20px;opacity: 1.0;background: linear-gradient(269deg, #204A6D 0%, #204A6D 100%);color: #ffffff;display: inline-block;box-sizing: border-box;color: #ffffff; font-weight: bold;text-transform: uppercase;letter-spacing: 1px;',
                        // labelStyle: 'font: 900 13px/22px Open Sans, Helvetica Neue, helvetica, arial, verdana, sans-serif',
                        flex: 4,
                        tooltip: getText('submit'),
                        reference: 'next',
                        handler: 'nextButton'
                        
                    }],
                }]
            },
            // End test
           
        ]
    }


});
