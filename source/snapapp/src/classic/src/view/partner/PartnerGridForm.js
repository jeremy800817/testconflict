Ext.define('snap.view.partner.PartnerGridForm', {
    extend: 'snap.view.gridpanel.GridForm',
    alias: 'widget.PartnerGridForm',
    requires: [
        'Ext.panel.Panel',
        'Ext.window.Window',
        'Ext.form.*',
        'snap.view.gridpanel.BaseController',
        'snap.view.partner.PartnerTreeController'
    ],
    store: {
        teststore: Ext.create('snap.store.ProductItems'),
        pricesourceproviders: Ext.create('snap.store.PriceSourceProviders')
    },
    controller: 'gridpanel-partnertreecontroller',
    reference: 'formWindow',
    formDialogTitle: 'Partner',
    formDialogWidth: '80%',
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
    listeners: {
        'beforeedit': function (editor, e) {

        },
    },
    enableFormPanelFrame: false,
    formPanelItems: [
        {
            xtype: 'tabpanel',
            flex: 1,
            reference: 'partnertab',
            items: [
                {
                    title: 'Partner Details',
                    layout: 'column',
                    margin: '28 8 8 18',
                    width: 550,
                    //height: 300,
                    //default: { labelWidth: 70},
                    items: [
                        {
                            columnWidth: 0.33,
                            items: [
                                { xtype: 'hidden', hidden: true, name: 'id' },
                                { xtype: 'textfield', fieldLabel: 'Code', name: 'code' },
                                { xtype: 'textfield', fieldLabel: 'Name', name: 'name', },
                                { xtype: 'textarea', fieldLabel: 'Address', name: 'address', },
                                { xtype: 'textfield', fieldLabel: 'Post Code', name: 'postcode', },
                                {
                                    xtype: 'combobox', fieldLabel: 'State', store: Ext.create('snap.store.States'), queryMode: 'local', remoteFilter: false,
                                    name: 'state', valueField: 'code', displayField: 'name', reference: 'parstate',
                                    forceSelection: true, editable: false
                                },
                                {
                                    xtype: 'combobox', fieldLabel: 'Type', store: Ext.create('snap.store.PartnerTypes'), queryMode: 'local', remoteFilter: false,
                                    name: 'type', valueField: 'type', displayField: 'name'
                                },
                                { xtype: 'checkbox', fieldLabel: 'GTP Core Partner', name: 'corepartner', inputValue: '1', uncheckedValue: '0' },
                                {
                                    xtype: 'combobox', fieldLabel: 'Price Source ID',
                                    store: {
                                        autoLoad: true,
                                        type: 'PriceSourceProviders',
                                        sorters: 'value'
                                    },
                                    queryMode: 'local',
                                    remoteFilter: false,
                                    name: 'pricesourceid',
                                    valueField: 'id',
                                    displayField: 'value',
                                    forceSelection: true, editable: false,
                                    renderer: function (value, metaData, record, rowIndex, colIndex, store, view) {
                                        var productitems = Ext.getStore('pricesourceproviders').load();
                                        console.log(productitems);
                                        var catRecord = productitems.findRecord('id', value);
                                        return catRecord ? catRecord.get('value') : '';
                                    },
                                },
                                { xtype: 'combobox', fieldLabel:'Sales Person', 
                                        store: {
                                            autoLoad: true,
                                            type: 'SalesPersons',                   
                                            sorters: 'name'
                                        }, 
                                queryMode: 'local', remoteFilter: false, name: 'salespersonid', valueField: 'id', displayField: 'name', reference: 'salespersonid', forceSelection: false, editable: true,
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
                                /*
                                { 
                                    xtype: 'combobox', 
                                    fieldLabel: 'Sales Person', 
                                    name: 'salespersonid', 
                                    typeAhead: true,
                                    triggerAction: 'all',
                                    selectOnTab: true,
                                    store: {
                                        autoLoad: true,
                                        type: 'SalesPersons',
                                        sorters: 'name'
                                    },
                                    lazyRender: true,
                                    displayField: 'name',
                                    valueField: 'id',
                                    queryMode: 'remote',
                                    remoteFilter: false,
                                    listClass: 'x-combo-list-small',
                                    forceSelection: true,
                                },
                                */

                            ]
                        },
                        {
                            columnWidth: 0.33,
                            items: [
                                {
                                    xtype: 'combobox',
                                    fieldLabel: 'Trading Schedule ID',
                                    name: 'tradingscheduleid',
                                    typeAhead: true,
                                    triggerAction: 'all',
                                    selectOnTab: true,
                                    store: {
                                        autoLoad: true,
                                        type: 'TradingScheduleTag',
                                        sorters: 'name'
                                    },
                                    lazyRender: true,
                                    displayField: 'name',
                                    valueField: 'id',
                                    queryMode: 'remote',
                                    remoteFilter: false,
                                    listClass: 'x-combo-list-small',
                                    forceSelection: true,
                                },
                                /*{
                                    xtype: 'combobox', fieldLabel: 'Company Sell Code 1', store: Ext.create('snap.store.States'), queryMode: 'local', remoteFilter: false,
                                    name: 'sapcompanysellcode1', valueField: 'code', displayField: 'name', reference: 'parstate',
                                    forceSelection: true, editable: false
                                },*/
                                {
                                    xtype: 'combobox', fieldLabel: 'SAP Customer BP/Entity Code', store: { type: 'array', fields: ['id', 'name'] }, queryMode: 'local', remoteFilter: false, name: 'sapcompanysellcode1', valueField: 'id', displayField: 'id', reference: 'sapcompanysellcode1', forceSelection: false, editable: true,
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
                                    xtype: 'combobox', fieldLabel: 'SAP Vendor BP/Entity Code:', store: { type: 'array', fields: ['id', 'name'] }, queryMode: 'local', remoteFilter: false, name: 'sapcompanybuycode1', valueField: 'id', displayField: 'id', reference: 'sapcompanybuycode1', forceSelection: false, editable: true,
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
                                // {
                                //     xtype: 'combobox', fieldLabel: 'Company Sell Code 2', store: { type: 'array', fields: ['id', 'name'] }, queryMode: 'local', remoteFilter: false, name: 'sapcompanysellcode2', valueField: 'id', displayField: 'id', reference: 'sapcompanysellcode2', forceSelection: false, editable: true,
                                //     tpl: [
                                //         '<ul class="x-list-plain">',
                                //         '<tpl for=".">',
                                //         '<li class="',
                                //         Ext.baseCSSPrefix, 'grid-group-hd ',
                                //         Ext.baseCSSPrefix, 'grid-group-title">{abbr}</li>',
                                //         '<li class="x-boundlist-item">',
                                //         '{name}',
                                //         '</li>',
                                //         '</tpl>',
                                //         '</ul>'
                                //     ]
                                // },
                                // {
                                //     xtype: 'combobox', fieldLabel: 'Company Buy Code 2', store: { type: 'array', fields: ['id', 'name'] }, queryMode: 'local', remoteFilter: false, name: 'sapcompanybuycode2', valueField: 'id', displayField: 'id', reference: 'sapcompanybuycode2', forceSelection: false, editable: true,
                                //     tpl: [
                                //         '<ul class="x-list-plain">',
                                //         '<tpl for=".">',
                                //         '<li class="',
                                //         Ext.baseCSSPrefix, 'grid-group-hd ',
                                //         Ext.baseCSSPrefix, 'grid-group-title">{abbr}</li>',
                                //         '<li class="x-boundlist-item">',
                                //         '{name}',
                                //         '</li>',
                                //         '</tpl>',
                                //         '</ul>'
                                //     ]
                                // },
                                /*{
                                    xtype: 'combobox', fieldLabel: 'Company Buy Code 1', store: Ext.create('snap.store.States'), queryMode: 'local', remoteFilter: false,
                                    name: 'sapcompanybuycode1', valueField: 'code', displayField: 'name', reference: 'parstate',
                                    forceSelection: true, editable: false
                                },
                                {
                                    xtype: 'combobox', fieldLabel: 'Company Sell Code 2', store: Ext.create('snap.store.States'), queryMode: 'local', remoteFilter: false,
                                    name: 'sapcompanysellcode2', valueField: 'code', displayField: 'name', reference: 'parstate',
                                    forceSelection: true, editable: false
                                },
                                {
                                    xtype: 'combobox', fieldLabel: 'Company Buy Code 2', store: Ext.create('snap.store.States'), queryMode: 'local', remoteFilter: false,
                                    name: 'sapcompanybuycode2', valueField: 'code', displayField: 'name', reference: 'parstate',
                                    forceSelection: true, editable: false
                                },*/
                                //{ xtype: 'textfield', fieldLabel: 'Company Sell Code 1', name: 'sapcompanysellcode1', },
                                //{ xtype: 'textfield', fieldLabel: 'Company Buy Code 1', name: 'sapcompanybuycode1', },
                                //{ xtype: 'textfield', fieldLabel: 'Company Sell Code 2', name: 'sapcompanysellcode2', },
                                //{ xtype: 'textfield', fieldLabel: 'Company Buy Code 2', name: 'sapcompanybuycode2', },
                                { xtype: 'textfield', fieldLabel: 'Daily Buy Limit', name: 'dailybuylimitxau', },
                                { xtype: 'textfield', fieldLabel: 'Daily Sell Limit', name: 'dailyselllimitxau', },
                                { xtype: 'textfield', fieldLabel: 'Price Lapse Time Allowance', name: 'pricelapsetimeallowance', },
                                { xtype: 'checkbox', fieldLabel: 'Under KTP', name: 'isktp', reference: 'isktp', inputValue: '1', uncheckedValue: '0',
                                    listeners: {
                                        afterrender: function(value) {
                                        
                                            // Temporary Hide
                                            // Check if there is data for partner group
                                            
                                            // if(data == 1){
                                            //     // Do something if its toggled
                                              
                                            //     value.setValue(true);
                                            // }
                                        },
                                        // Do Copy
                                        change: function(checkbox, newValue, oldValue, eOpts) {
                                            // Initialize pointer
                                            // nonPeakContainer = checkbox.up().lookupReferenceHolder('nonpeak-container').view.items.items[0].items.items[0].items.items[0].items.items;
                                           
                                            // Do checking before initializing
                                            // 1) If non peak is checked, uncheck 
                                            // 2) reset nonpeak to default
                                            // 3) Change and update tick for peak
                                            //altCheckBox = checkbox.up().up().items.items[5].items.items[2];
                                            
                                            //formView.getController().lookupReference('partnerparentstatus').setHidden(true);
                                            // formView.getController().lookupReference('partnergroup').setHidden(true);
                                            // Initialize and check 
                                            formView = checkbox.up().up().up().up().up();

                                            if (newValue) {
                                        
                                                // Show panel

                                                // Set Allow Blank false 
                                                formView.getController().lookupReference('group').setHidden(false)
                                                formView.getController().lookupReference('parent').setHidden(false)
     

                                                // set hidden tier 2 container based on setting here
                                                // this.up().up().items.items[0].items.items[2].setValue(true);
                                            }else{
                                                
                                                // Set Allow Blank false 
                                                formView.getController().lookupReference('group').setHidden(true)
                                                formView.getController().lookupReference('parent').setHidden(true)
     
                                                // // Unset Hidden Tier 2 Content
                                                // this.up().up().items.items[0].items.items[2].setValue(false);
                                            }

                                        }
                                    } 
                                },
                                {
                                    // itemId: 'ktpaccountholder_partnerid',
                                    xtype: 'combobox',
                                    fieldLabel: 'Partner Group',
                                    allowBlank: true,
                                    reference: "group",
                                    name: 'group',
                                    hidden: true,
                                    store: {
                                        autoLoad: true,
                                        type: 'Partner', proxy: {
                                            type: 'ajax',
                                            url: 'index.php?hdl=partner&action=list&getKoperasiPartners=1',
                                            reader: {
                                                type: 'json',
                                                rootProperty: 'records',
                                            }
                                        },
                                        sorters: 'name',
                                        //Filter partners where group belong to KTP
                                        // filters: [{
                                        //     property: 'group',
                                        //     value: /PKB@UAT/,
                                        // }]
                                    },
                                    // disabled: 'true',
                                    listConfig: {
                                        getInnerTpl: function () {
                                            return '[ {code} ] {name}';
                                        }
                                    },
                                    displayTpl: Ext.create('Ext.XTemplate',
                                        '<tpl for=".">',
                                        '[ {code} ] {name}',
                                        '</tpl>'
                                    ),
                                    displayField: 'name',
                                    valueField: 'id',
                                    typeAhead: true,
                                    queryMode: 'local',
                                    listeners: {
                                        expand: function(combo){
                                            // debugger;
                                            combo.store.load({
                                                //page:2,
                                                start: 0,
                                                limit: 1500
                                            })
                                            // debugger;
                                            // combo.store.clearFilter();
                                            // combo.store.filter("group", myView.partnerId);
                                        }
                                    },
                                    // forceSelection: true,
                                    // allowBlank: false
                                },
                                // { xtype: 'textfield', fieldLabel: 'parent', name: 'parent', },
                                {
                                  
                                    xtype: "combobox",
                                    fieldLabel: "Partner Parent",
                                    store: {
                                        type: "array",
                                        fields: ["id", "code",],
                                    },
                                    displayTpl: Ext.create('Ext.XTemplate',
                                        // '<tpl style="color:{color} for=".">',
                                        '<tpl for=".">',
                                        '{code}',
                                        '</tpl>'
                                    ),
                                    listConfig: {
                                        getInnerTpl: function () {
                                            return '{code}';
                                        }
                                    },
                                    hidden: true,
                                    queryMode:"local",
                                    remoteFilter: false,
                                    name: "parent",
                                    valueField: "id",
                                    displayField: "code",
                                    reference: "parent",
                                    // forceSelection: true,
                                    allowBlank: true,
                                    flex: 1,
                                    // listeners:{
                                    //     scope: this,
                                    //     afterRender: function(me){
                                    //         me.setValue('1');   
                                    //     }
                                    // }
                                },
                            ]
                        },
                        {
                            columnWidth: 0.33,
                            items: [

                                {
                                    xtype: 'combobox', fieldLabel: 'Ordering Mode', store: Ext.create('snap.store.PartnerOrderingMode'), queryMode: 'local', remoteFilter: false, valueField: 'mode', displayField: 'name',
                                    name: 'orderingmode',
                                },
                                { xtype: 'checkbox', fieldLabel: 'Share DGV', name: 'sharedgv', inputValue: '1', uncheckedValue: '0',
                                    listeners: {
                                        afterrender: function(value) {
                                        
                                            // Temporary Hide
                                            // Check if use percentage
                                            // if(data.tier1.usepercent == 1){
                                            //     // Do something if its toggled
                                            //     this.up().up().items.items[0].items.items[1].setValue(true);
                                            //     this.up().up().items.items[0].items.items[2].setValue(true);
                                            // }else{
        
                                            // }
                                        },
                                        // Do Copy
                                        change: function(checkbox, newValue, oldValue, eOpts) {
                                            // Initialize pointer
                                            // nonPeakContainer = checkbox.up().lookupReferenceHolder('nonpeak-container').view.items.items[0].items.items[0].items.items[0].items.items;
                                            
                                            // Do checking before initializing
                                            // 1) If non peak is checked, uncheck 
                                            // 2) reset nonpeak to default
                                            // 3) Change and update tick for peak
                                            //altCheckBox = checkbox.up().up().items.items[5].items.items[2];
                                     
                                            // Initialize and check 
                                            if (newValue) {
                                        
                                                // Show panel

                                                // Set Allow Blank false 
                                                this.up().items.items[8].allowBlank = false;
                                                this.up().items.items[9].allowBlank = false;
                                                this.up().items.items[10].allowBlank = false;
                                                this.up().items.items[11].allowBlank = false;
                                        

                                                // set hidden tier 2 container based on setting here
                                                // this.up().up().items.items[0].items.items[2].setValue(true);
                                            }else{
                                                
                                                // Set Allow Blank false 
                                                this.up().items.items[8].allowBlank = true;
                                                this.up().items.items[9].allowBlank = true;
                                                this.up().items.items[10].allowBlank = true;
                                                this.up().items.items[11].allowBlank = true;

                                                // // Unset Hidden Tier 2 Content
                                                // this.up().up().items.items[0].items.items[2].setValue(false);
                                            }

                                        }
                                    } 
                                },
                                { xtype: 'checkbox', fieldLabel: 'Auto Submit Order', name: 'autosubmitorder', inputValue: '1', uncheckedValue: '0' },
                                { xtype: 'checkbox', fieldLabel: 'Auto Create Match Order', name: 'autocreatematchedorder', inputValue: '1', uncheckedValue: '0' },
                                { xtype: 'textfield', fieldLabel: 'Order Confirm Allowance', name: 'orderconfirmallowance', },
                                { xtype: 'textfield', fieldLabel: 'Order Cancel allowance', name: 'ordercancelallowance', },
                                { xtype: 'combobox', fieldLabel: 'Calculator Mode', store: Ext.create('snap.store.PartnerCalculatorMode'), queryMode: 'local', remoteFilter: false, valueField: 'mode', displayField: 'name', name: 'calculatormode', allowBlank: false,
                                    listeners: {
                                        afterrender: function(combo) {   
                               
                                            if(combo.store.isLoaded()){
                                                // var getState = combo.getState(), //get current combobox state
                                                // comboState = parseFloat(getState.value) - 1, 
                                                // comboStore = combo.store;
                                                
                                                // If combo not empty means there is data, then skip function below
                                                if(!combo.getState().value){
                                                    combo.select(combo.store.getAt(0));
                                                }
                                    
                                            }else {
                                                // store.on({
                                                //     load:function() {
                                                //         combo.select(combo.store.getAt(0))
                                                //     },
                                                //     single:true
                                                // });
                                            }
                                            // if(combo!== undefined) {
                                            //    var recordSelected = combo.getStore().getAt(0);                     
                                            //    if(recordSelected!== undefined) {
                                            //        combo.setValue(recordSelected.get('GTP'));
                                            //    }
                                            // }
                                          }
                                        
                                    }
                                },
                                //{ xtype: 'textfield', fieldLabel: 'Status', name: 'status', },
                                {
                                    reference: 'status', fieldLabel: 'Status', xtype: 'radiogroup',
                                    items: [
                                        { boxLabel: 'Inactive', name: 'status', inputValue: '0' },
                                        { boxLabel: 'Active', name: 'status', inputValue: '1' }
                                    ]
                                },
                                { xtype: 'textfield', fieldLabel: 'Project Base (for email)', name: 'projectbase', },
                                { xtype: 'textfield', fieldLabel: 'Sender Name (for email)', name: 'sendername', },
                                { xtype: 'textfield', fieldLabel: 'Sender Email (for email)', name: 'senderemail', },
                                { xtype: 'textfield', fieldLabel: 'Project Email (for email)', name: 'projectemail', },
                            ]
                        },
                    ]
                },
                {
                    title: 'Partner Service Details',
                    layout: 'fit',
                    autoScroll: true,
                    margin: '28 8 8 18',
                    //width: 450,
                    height: 400,
                    default: {
                        labelWidth: 200
                    },
                    items: [
                        {
                            xtype: 'textfield',
                            hidden: true,
                            name: 'serviceparams',
                            reference: 'serviceparams',
                            itemId: 'serviceparams',
                            store: { type: 'array' },
                        },
                        {
                            xtype: 'textfield',
                            hidden: true,
                            name: 'branchparams',
                            reference: 'branchparams',
                            itemId: 'branchparams',
                            store: { type: 'array' },
                        },
                        {
                            reference: 'partnerservice',
                            name: 'partnerservice',
                            itemId: 'partnerservice',
                            xtype: 'gridpanel',
                            layout: 'fit',
                            //autoScroll: true,
                            default: {
                                labelWidth: 200
                            },
                            //width: '100%',
                            //height: '100%',
                            title: '',
                            store: {
                                storeId: 'partnerserviceStore',
                                xclass: 'Ext.data.ArrayStore',
                                type: 'array',
                                fields: ['id', 'partnersapgroup', 'productid', 'refineryfee', 'premiumfee', { name: 'includefeeinprice', type: 'bool' }, { name: 'canbuy', type: 'bool' }, { name: 'cansell', type: 'bool' }, { name: 'canqueue', type: 'bool' }, { name: 'canredeem', type: 'bool' }, 'buyclickminxau', 'buyclickmaxxau','sellclickminxau', 'sellclickmaxxau', 'dailybuylimitxau', 'dailyselllimitxau', 'redemptionpremiumfee', 'redemptioncommission', 'redemptioninsurancefee', 'redemptionhandlingfee', 
                                'specialpricetype', 'specialpricecondition', 'specialpricecompanybuyoffset', 'specialpricecompanyselloffset'],
                            },
                            tbar: [
                                {
                                    itemId: 'addrec',
                                    text: 'Add',
                                    iconCls: 'fa fa-plus-circle',
                                    plain: true,
                                    handler: 'paramAddClick'
                                },
                                {
                                    itemId: 'removerec',
                                    text: 'Remove',
                                    iconCls: 'fa fa-minus-circle',
                                    plain: true,
                                    handler: 'paramDelClick',
                                    disabled: true
                                }
                            ],
                            listeners: { viewReady: 'partnerServiceViewReady', selectionchange: 'paramsSelectionChange' },
                            columns: [
                                { text: 'ID', dataIndex: 'id', inputType: 'hidden', hidden: true, editor: { name: 'id', allowBlank: true, selectOnFocus: true,   } },
                                { text: 'SAP Group', dataIndex: 'partnersapgroup', editor: { name: 'partnersapgroup', xtype: 'textfield', allowBlank: false, selectOnFocus: true,   } },
                                {

                                    header: 'Product',
                                    dataIndex: 'productid',
                                    renderer: function (value, metaData, record, rowIndex, colIndex, store, view) {
                                        var productitems = Ext.getStore('productitemsstore').load();
                                        var catRecord = productitems.findRecord('id', value);
                                        return catRecord ? catRecord.get('name') : '';
                                    },
                                    editor: {
                                        xtype: 'combobox',
                                        typeAhead: true,
                                        triggerAction: 'all',
                                        selectOnTab: true,
                                        store: Ext.getStore('productitemsstore').load(),
                                        lazyRender: true,
                                        displayField: 'name',
                                        valueField: 'id',
                                        queryMode: 'local',
                                        remoteFilter: false,
                                        listClass: 'x-combo-list-small',
                                        forceSelection: true
                                    }
                                },
                                { text: 'Refinery Fee', dataIndex: 'refineryfee', editor: { name: 'refineryfee', xtype: 'textfield', allowBlank: false, selectOnFocus: true,   } },
                                { text: 'Premium Fee', dataIndex: 'premiumfee', editor: { name: 'premiumfee', xtype: 'textfield', allowBlank: false, selectOnFocus: true,   } },
                                { text: 'Redemption Premium Fee', dataIndex: 'redemptionpremiumfee', editor: { name: 'redemptionpremiumfee', xtype: 'textfield', allowBlank: false, selectOnFocus: true,   } },
                                { text: 'Redemption Channel Marketing Fund', dataIndex: 'redemptioncommission', editor: { name: 'redemptioncommission', xtype: 'textfield', allowBlank: false, selectOnFocus: true,   } },
                                { text: 'Redemption Insurance Fee', dataIndex: 'redemptioninsurancefee', editor: { name: 'redemptioninsurancefee', xtype: 'textfield', allowBlank: false, selectOnFocus: true,   } },
                                { text: 'Redemption Handling Fee', dataIndex: 'redemptionhandlingfee', editor: { name: 'redemptionhandlingfee', xtype: 'textfield', allowBlank: false, selectOnFocus: true,   } },
                                { xtype: 'checkcolumn', text: 'Include Fee in Price', listeners: { checkchange: 'chkchange' }, dataIndex: 'includefeeinprice', editor: { name: 'includefeeinprice', xtype: 'checkbox', inputValue: '1', uncheckedValue: '0', id: 'checkincludefeeinprice', allowBlank: false } },
                                { xtype: 'checkcolumn', text: 'Partner Can Buy', listeners: { checkchange: 'chkchange' }, dataIndex: 'canbuy', editor: { name: 'canbuy', xtype: 'checkbox', inputValue: '1', uncheckedValue: '0', id: 'checkcanbuy', allowBlank: false } },
                                { xtype: 'checkcolumn', text: 'Partner Can Sell', listeners: { checkchange: 'chkchange' }, dataIndex: 'cansell', editor: { name: 'cansell', xtype: 'checkbox', inputValue: '1', uncheckedValue: '0', id: 'checkcansell', allowBlank: false } },
                                //{ xtype: 'checkcolumn', text: 'Can Queue', listeners: { checkchange: 'chkchange' }, dataIndex: 'canqueue', hidden: true, editor: { name: 'canqueue', xtype: 'checkbox', inputValue: '1', uncheckedValue: '0', id: 'checkcanqueue', allowBlank: false } },
                                { xtype: 'checkcolumn', text: 'Partner Can Redeem', listeners: { checkchange: 'chkchange' }, dataIndex: 'canredeem', editor: { name: 'canredeem', xtype: 'checkbox', inputValue: '1', uncheckedValue: '0', id: 'checkcanredeem', allowBlank: false } },                               
                                { text: 'Buy Min Xau', dataIndex: 'buyclickminxau', editor: { name: 'buyclickminxau', xtype: 'textfield', allowBlank: false, selectOnFocus: true,   } },
                                { text: 'Buy Max Xau', dataIndex: 'buyclickmaxxau', editor: { name: 'buyclickmaxxau', xtype: 'textfield', allowBlank: false, selectOnFocus: true,   } },
                                { text: 'Sell Min Xau', dataIndex: 'sellclickminxau', editor: { name: 'sellclickminxau', xtype: 'textfield', allowBlank: false, selectOnFocus: true,   } },
                                { text: 'Sell Max Xau', dataIndex: 'sellclickmaxxau', editor: { name: 'sellclickmaxxau', xtype: 'textfield', allowBlank: false, selectOnFocus: true,   } },
                                { text: 'Daily Buy Limit Xau', dataIndex: 'dailybuylimitxau', editor: { name: 'dailybuylimitxau', xtype: 'textfield', allowBlank: false, selectOnFocus: true,   } },
                                { text: 'Daily Sell Limit Xau', dataIndex: 'dailyselllimitxau', editor: { name: 'dailyselllimitxau', xtype: 'textfield', allowBlank: false, selectOnFocus: true,   } },
                                // {
                                //     xtype: 'combobox', fieldLabel: 'Special Price Type', store: Ext.create('snap.store.SpecialPriceType'), queryMode: 'local', remoteFilter: false, valueField: 'mode', displayField: 'name',
                                //     name: 'specialpricetype', 
                                // },
                                {

                                    header: 'Special Price Type',
                                    dataIndex: 'specialpricetype',
                                    // renderer: function (value, metaData, record, rowIndex, colIndex, store, view) {
                                    //     var productitems = Ext.getStore('productitemsstore').load();
                                    //     var catRecord = productitems.findRecord('id', value);
                                    //     return catRecord ? catRecord.get('name') : '';
                                    // },
                                    editor: {
                                        xtype: 'combobox',
                                        typeAhead: true,
                                        triggerAction: 'all',
                                        selectOnTab: true,
                                        store: Ext.create('snap.store.SpecialPriceType'),
                                        lazyRender: true,
                                        displayField: 'name',
                                        valueField: 'id',
                                        queryMode: 'local',
                                        remoteFilter: false,listClass: 'x-combo-list-small',
                                        forceSelection: true,
                                        allowBlank: false
                                    }
                                },
                                { text: 'Special Price Condition', dataIndex: 'specialpricecondition', editor: { name: 'specialpricecondition', xtype: 'textfield', allowBlank: false, selectOnFocus: true,   } },
                                { text: 'Special Price Company Buy Offset', dataIndex: 'specialpricecompanybuyoffset', editor: { name: 'specialpricecompanybuyoffset', xtype: 'textfield', allowBlank: false, selectOnFocus: true,   } },
                                { text: 'Special Price Company Sell Offset', dataIndex: 'specialpricecompanyselloffset', editor: { name: 'specialpricecompanyselloffset', xtype: 'textfield', allowBlank: false, selectOnFocus: true,   } },
                            ],
                            selType: 'rowmodel',
                            plugins: [
                                {
                                    xclass: 'Ext.grid.plugin.RowEditing',
                                    clicksToMoveEditor: 1,
                                    autoCancel: false,
                                    pluginId: 'editedRow1',
                                    id: 'editedRow1'
                                }
                            ]
                        }
                    ]
                },
                {
                    title: 'Partner Branch Details',
                    autoScroll: true,
                    margin: '28 8 8 18',
                    height: 400,
                    default: {
                        labelWidth: 200
                    },
                    items: [
                        {
                            reference: 'partnerbranch',
                            name: 'partnerbranch',
                            itemId: 'partnerbranch',
                            xtype: 'gridpanel',
                            layout: 'fit',
                            //width: 420,
                            height: 380,
                            title: '',
                            store: {
                                storeId: 'partnerbranchStore',
                                fields: ['id', 'partnerid', 'code', 'name', 'sapcode', 'address', 'postcode', 'city', 'contactno', { name: 'status', type: 'bool' }],
                            },
                            tbar: [
                                {
                                    itemId: 'addbranch',
                                    text: 'Add',
                                    iconCls: 'fa fa-plus-circle',
                                    plain: true,
                                    handler: 'paramAddBranchClick'
                                },
                                {
                                    itemId: 'removebranch',
                                    text: 'Remove',
                                    iconCls: 'fa fa-minus-circle',
                                    plain: true,
                                    handler: 'paramDelBranchClick',
                                    disabled: true
                                }
                            ],
                            listeners: { viewReady: 'partnerBranchviewReady', selectionchange: 'paramsBranchSelectionChange' },
                            columns: [
                                { text: 'ID', dataIndex: 'id', inputType: 'hidden', hidden: true, editor: { name: 'id', allowBlank: true } },
                                /* { text: 'Partner ID', dataIndex: 'partnerid', flex: 1, editor: { name: 'partnerid', xtype: 'textfield', allowBlank: false } }, */
                                { text: 'Code', dataIndex: 'code', flex: 1, editor: { name: 'code', xtype: 'textfield', allowBlank: false } },
                                { text: 'Branch Name', dataIndex: 'name', flex: 1, editor: { name: 'name', xtype: 'textfield', allowBlank: false } },
                                { text: 'SAP Code', dataIndex: 'sapcode', flex: 1, editor: { name: 'sapcode', xtype: 'textfield', allowBlank: false } },
                                { text: 'Address', dataIndex: 'address', flex: 1, editor: { name: 'address', xtype: 'textfield', allowBlank: false } },
                                { text: 'Post Code', dataIndex: 'postcode', flex: 1, editor: { name: 'postcode', xtype: 'textfield', allowBlank: false } },
                                { text: 'City', dataIndex: 'city', flex: 1, editor: { name: 'city', xtype: 'textfield', allowBlank: false } },
                                { text: 'Contact No', dataIndex: 'contactno', flex: 1, editor: { name: 'contactno', xtype: 'textfield', allowBlank: false } },
                                { xtype: 'checkcolumn', text: 'Status', listeners: { checkchange: 'chkbranchstatuschange' }, dataIndex: 'status', editor: { name: 'status', xtype: 'checkbox', inputValue: '1', uncheckedValue: '0', id: 'branchstatus', allowBlank: false } },

                            ],
                            selType: 'rowmodel',
                            plugins: [
                                {
                                    xclass: 'Ext.grid.plugin.RowEditing',
                                    clicksToMoveEditor: 1,
                                    autoCancel: false,
                                    pluginId: 'editedRow2',
                                    id: 'editedRow2'
                                }
                            ]
                        }
                    ]
                },
                {
                    title: 'Partner Setting',
                    layout: 'column',
                    margin: '28 8 8 18',
                    width: 550,
                    //height: 300,
                    //default: { labelWidth: 70},
                    items: [
                        {
                            columnWidth: 0.28,
                            items: [
                                { xtype: 'textfield', fieldLabel: '', name: 'partnersettingid', reference: 'partnersettingid', hidden: true},
                                { xtype: 'textfield', fieldLabel: 'SAP DG Code', name: 'sapdgcode', reference: 'sapdgcode'},
                                { xtype: 'textfield', fieldLabel: 'SAP Minted WHS', name: 'sapmintedwhs', reference: 'sapmintedwhs' },
                                { xtype: 'textfield', fieldLabel: 'SAP Kilobar WHS', name: 'sapkilobarwhs', reference: 'sapkilobarwhs' },
                                { xtype: 'textfield', fieldLabel: 'SAP Redemption Fee Code', name: 'sapitemcoderedeemfees', reference: 'sapitemcoderedeemfees' },
                                { xtype: 'textfield', fieldLabel: 'SAP Administration Fee Code', name: 'sapitemcodeannualfees', reference: 'sapitemcodeannualfees' },
                                { xtype: 'textfield', fieldLabel: 'SAP Storage Fee Code', name: 'sapitemcodestoragefees', reference: 'sapitemcodestoragefees' },
                                { xtype: 'textfield', fieldLabel: 'SAP Processing Fee Code', name: 'sapitemcodetransactionfees', reference: 'sapitemcodetransactionfees' },
                                { xtype: 'textfield', fieldLabel: 'Min Initial Buy Xau', name: 'mininitialxau', reference: 'mininitialxau' },
                                { xtype: 'textfield', fieldLabel: 'Min Account Balance Xau', name: 'minbalancexau', reference: 'minbalancexau' },
                                { xtype: 'textfield', fieldLabel: 'Min Disbursement Amount RM', name: 'mindisbursement', reference: 'mindisbursement' },                               
                            ]
                        },
                        {
                            columnWidth: 0.28,
                            items: [
                                {
                                    xtype: 'combobox', fieldLabel: 'EKYC Provider', store: Ext.create('snap.store.MyEkycProvider'), queryMode: 'local', remoteFilter: false,
                                    name: 'ekycprovider', reference: 'ekycprovider', valueField: 'code', displayField: 'name', reference: 'ekycprovider',
                                    forceSelection: true, editable: false
                                },
                                {
                                    xtype: 'combobox', fieldLabel: 'Wallet Payment Provider', store: Ext.create('snap.store.MyPartnerPaymentProvider'), queryMode: 'local', remoteFilter: false,
                                    name: 'partnerpaymentprovider', reference: 'partnerpaymentprovider', valueField: 'code', displayField: 'name', reference: 'partnerpaymentprovider',
                                    forceSelection: true, editable: false
                                },
                                {
                                    xtype: 'combobox', fieldLabel: 'FPX Payment Provider', store: Ext.create('snap.store.MyCompanyPaymentProvider'), queryMode: 'local', remoteFilter: false,
                                    name: 'companypaymentprovider', reference: 'companypaymentprovider', valueField: 'code', displayField: 'name', reference: 'companypaymentprovider',
                                    forceSelection: true, editable: false
                                },
                                {
                                    xtype: 'combobox', fieldLabel: 'Payout Provider', store: Ext.create('snap.store.MyPayoutProvider'), queryMode: 'local', remoteFilter: false,
                                    name: 'payoutprovider', reference: 'payoutprovider', valueField: 'code', displayField: 'name', reference: 'payoutprovider',
                                    forceSelection: true, editable: false
                                },

                                { xtype: 'textfield', fieldLabel: 'Processing Fee (RM)', name: 'transactionfee', reference: 'transactionfee' },
                                { xtype: 'textfield', fieldLabel: 'Payout Fee (RM)', name: 'payoutfee', reference: 'payoutfee' },
                                { xtype: 'textfield', fieldLabel: 'Wallet Fee (RM)', name: 'walletfee', reference: 'walletfee' },
                                { xtype: 'textfield', fieldLabel: 'Storage Fee p.a (%)', name: 'storagefeeperannum', reference: 'storagefeeperannum' },
                                { xtype: 'textfield', fieldLabel: 'Admin Fee p.a (%)', name: 'adminfeeperannum', reference: 'adminfeeperannum' },                                
                                { xtype: 'textfield', fieldLabel: 'Min Storage Charge (RM)', name: 'minstoragecharge', reference: 'minstoragecharge' },
                            ]
                        },
                        {
                            columnWidth: 0.28,
                            items: [
                                { xtype: 'textfield', fieldLabel: 'Min Storage Fee Threshold (Xau)', name: 'minstoragefeethreshold', reference: 'minstoragefeethreshold' },
                                { xtype: 'textfield', fieldLabel: 'Packing & Shipment Fee (RM)', name: 'courierfee', reference: 'courierfee' },
                                { xtype: 'numberfield', fieldLabel: 'Max XAU Per Delivery (g)', name: 'maxxauperdelivery', reference: 'maxxauperdelivery' },                                
                                { xtype: 'numberfield', fieldLabel: 'Max Pieces Per Delivery (Pcs)', name: 'maxpcsperdelivery', reference: 'maxpcsperdelivery' },
                                { xtype: 'textfield', fieldLabel: 'Price Alert Validity (days)', name: 'pricealertvaliddays', reference: 'pricealertvaliddays' },
                                { xtype: 'textfield', fieldLabel: 'Access Token Lifetime (minutes)', name: 'accesstokenlifetime', reference: 'accesstokenlifetime' },
                                { xtype: 'textfield', fieldLabel: 'Refresh Token Lifetime (minutes)', name: 'refreshtokenlifetime', reference: 'refreshtokenlifetime' },                                
                            ]
                        },
                        {
                            columnWidth: 0.16,
                            items: [
                                { xtype: 'checkbox', fieldLabel: 'Verify Email', name: 'verifyachemail', reference: 'verifyachemail', inputValue: '1', uncheckedValue: '0'},
                                { xtype: 'checkbox', fieldLabel: 'Verify Phone', name: 'verifyachphone', reference: 'verifyachphone', inputValue: '1', uncheckedValue: '0'},
                                { xtype: 'checkbox', fieldLabel: 'Verify Pin', name: 'verifyachpin', reference: 'verifyachpin', inputValue: '1', uncheckedValue: '0'},
                                { xtype: 'checkbox', fieldLabel: 'Allow Account Closures', name: 'achcancloseaccount', reference: 'achcancloseaccount', inputValue: '1', uncheckedValue: '0'},
                                { xtype: 'checkbox', fieldLabel: 'Skip EKYC', name: 'skipekyc', reference: 'skipekyc', inputValue: '1', uncheckedValue: '0'},
                                { xtype: 'checkbox', fieldLabel: 'Skip AMLA', name: 'skipamla', reference: 'skipamla', inputValue: '1', uncheckedValue: '0'},
                                { xtype: 'checkbox', fieldLabel: 'AMLA Immediate Blacklist', name: 'amlablacklistimmediately', reference: 'amlablacklistimmediately', inputValue: '1', uncheckedValue: '0'},                                
                                { xtype: 'checkbox',  fieldLabel: 'Strict Inventory Utilisation', name: 'strictinventoryutilisation', reference: 'strictinventoryutilisation', inputValue: '1', uncheckedValue: '0'},
                                { xtype: 'checkbox', fieldLabel: 'Enable Push Notification', name: 'enablepushnotification', reference: 'enablepushnotification', inputValue: '1', uncheckedValue: '0' },
                                { xtype: 'checkbox',  fieldLabel: 'Unique NRIC', name: 'uniquenric', reference: 'uniquenric', inputValue: '1', uncheckedValue: '0'},
                            ]
                        }
                    ]
                },

                {
                    title: 'Reporting Setting',
                    layout: 'column',
                    margin: '28 8 8 18',
                    width: 550,
                    items: [
                        {
                            columnWidth: 0.33,
                            items: [
                                { xtype: 'datefield', format: 'Y-m-d H:i:s', fieldLabel: 'Commission Peak Hour From', name: 'dgpeakhourfrom',  reference: 'dgpeakhourfrom'},
                                { xtype: 'datefield', format: 'Y-m-d H:i:s', fieldLabel: 'Commission Peak Hour To', name: 'dgpeakhourto',  reference: 'dgpeakhourto'},
                            ]
                        },
                        {
                            columnWidth: 0.33,
                            items: [
                                { xtype: 'textfield', fieldLabel: 'Peak Partner Sell Commission (RM)', name: 'dgpeakpartnersellcommission',  reference: 'dgpeakpartnersellcommission'},
                                { xtype: 'textfield', fieldLabel: 'Peak Partner Buy Commission (RM)', name: 'dgpeakpartnerbuycommission',  reference: 'dgpeakpartnerbuycommission'},
                                { xtype: 'textfield', fieldLabel: 'Partner Sell Commission (RM)', name: 'dgpartnersellcommission',  reference: 'dgpartnersellcommission'},
                                { xtype: 'textfield', fieldLabel: 'Partner Buy Commission (RM)', name: 'dgpartnerbuycommission', reference: 'dgpartnerbuycommission' },
                            ]
                        },
                        {
                            columnWidth: 0.33,
                            items: [
                                { xtype: 'textfield', fieldLabel: 'ACE Sell Commission (RM)', name: 'dgacesellcommission',  reference: 'dgacesellcommission'},
                                { xtype: 'textfield', fieldLabel: 'ACE Buy Commission (RM)', name: 'dgacebuycommission',  reference: 'dgacebuycommission'},                                
                            ]
                        },
                        {
                            columnWidth: 0.33,
                            items: [
                                { xtype: 'textfield', fieldLabel: 'Affiliate Sell Commission (RM)', name: 'dgaffiliatesellcommission',  reference: 'dgaffiliatesellcommission'},
                                { xtype: 'textfield', fieldLabel: 'Affiliate Buy Commission (RM)', name: 'dgaffiliatebuycommission', reference: 'dgaffiliatebuycommission' },
                            ]
                        },
                    ]
                },
                {
                    title: 'SAP Settings',
                    layout: 'fit',
                    autoScroll: true,
                    margin: '28 8 8 18',
                    //width: 450,
                    height: 400,
                    default: {
                        labelWidth: 200
                    },
                    items: [
                        {name: 'tradebp_v',    reference: 'tradebp_v', hidden: true, xtype: 'textfield'},
                        {name: 'tradebp_c',    reference: 'tradebp_c', hidden: true, xtype: 'textfield'},
                        {name: 'nontradebp_v', reference: 'nontradebp_v', hidden: true, xtype: 'textfield'},
                        {name: 'nontradebp_c', reference: 'nontradebp_c', hidden: true, xtype: 'textfield'},
                        {name: 'sapsettingsparams', reference: 'sapsettingsparams', hidden: true, xtype: 'textfield'},
                        {
                            reference: 'sapsettingsgrid',
                            xtype: 'gridpanel',
                            layout: 'fit',
                            referenceHolder: true,
                            //autoScroll: true,
                            default: {
                                labelWidth: 200
                            },
                            //width: '100%',
                            //height: '100%',
                            title: '',
                            store: {
                                storeId: 'sapsettingsStore',
                                fields: ['sapsettingid','transactiontype', 'itemcode', {name:'header_tradebp_v', type: 'boolean'}, {name: 'header_tradebp_c', type: 'boolean'}, {name:'header_nontradebp_v', type: 'boolean'}, {name:'header_nontradebp_c', type:'boolean'}, 'action', 'gtprefno']
                            },
                            // store: {
                            //     storeId: 'partnerserviceStore',
                            //     xclass: 'Ext.data.ArrayStore',
                            //     type: 'array',
                            //     fields: ['id', 'partnersapgroup', 'productid', 'refineryfee', 'premiumfee', { name: 'includefeeinprice', type: 'bool' }, { name: 'canbuy', type: 'bool' }, { name: 'cansell', type: 'bool' }, { name: 'canqueue', type: 'bool' }, { name: 'canredeem', type: 'bool' }, 'buyclickminxau', 'buyclickmaxxau','sellclickminxau', 'sellclickmaxxau', 'dailybuylimitxau', 'dailyselllimitxau', 'redemptionpremiumfee', 'redemptioncommission', 'redemptioninsurancefee', 'redemptionhandlingfee'],
                            // },
                            tbar: [
                                {
                                    text: 'Add',
                                    iconCls: 'fa fa-plus-circle',
                                    handler: function (btn, e) {
                                        let grid = btn.up('gridpanel');
                                        let plugin = grid.getPlugin('editedRow1');
                                        plugin.completeEdit();
                                        // grid.getStore().in
                                        let store = grid.getStore();
                                        store.insert(0, store.config.fields.reduce((acc, f) => ({...acc, [f]:''}), {}));
                                        plugin.startEdit(0, 0);
                                    }
                                },
                                {
                                    reference: 'removebtn',
                                    text: 'Remove',
                                    iconCls: 'fa fa-minus-circle',
                                    plain: true,
                                    handler: function(btn, e) {
                                        var grid = btn.up('gridpanel');
                                        let plugin = grid.getPlugin('editedRow1');
                                        plugin.cancelEdit();

                                        Ext.MessageBox.confirm('Confirm', 'Confirm Delete?', function (id) {
                                            if (id == 'yes') {
                                                let sm = grid.getSelectionModel();
                                                let store = sm.getStore();
                                                let controller = grid.up('form').up('panel').getController();
                                                store.remove(sm.getSelection());
                                                sm.select(0);
                                                controller.updateSapParamsData(controller);
                                            }
                                        }, this);
                                    },
                                    disabled: true
                                }
                            ],
                            listeners: {
                                viewReady: 'onPartnerSapViewReady', 
                                selectionchange: function (selection, records) {
                                    this.lookupReference('removebtn').setDisabled(records.length == 0);
                                }
                            },
                            // listeners: { viewReady: 'partnerServiceViewReady', selectionchange: 'paramsSelectionChange' },
                            columns: [
                                { text: 'ID', dataIndex: 'sapsettingid', inputType: 'hidden', hidden: true, editor: { name: 'sapsettingid', allowBlank: true } },
                                // { text: 'Transaction', dataIndex: 'partnersapgroup', editor: { name: 'partnersapgroup', xtype: 'textfield', allowBlank: false } },
                                {
                                    text: 'Transaction',
                                    dataIndex: 'transactiontype',
                                    // width: 200,
                                    flex: 1,
                                    renderer: function (value, metaData, record, rowIndex, colIndex, store, view) {
                                        let comboStore = this.getColumns()[colIndex].getEditor().getStore();
                                        let comboRecord = comboStore.findRecord('type', value);
                                        // return comboRecord?.get('name') ?? "";
                                        return comboRecord ? comboRecord.get('text') : "";
                                    },
                                    editor: {
                                        xtype: 'combobox',
                                        typeAhead: true,
                                        triggerAction: 'all',
                                        selectOnTab: true,
                                        allowBlank: false,
                                        store: {
                                            // fields: ['action'],
                                            // data: [{action: 'buy_invoice'}, {action:'sell_invoice'}]
                                            fields: ['type', 'text'],
                                            data: [
                                                {type: 'STORAGE_FEE', text: 'STORAGE FEES'},
                                                {type: 'PROCESSING_FEE', text: 'PROCESSING FEES'},
                                                {type: 'ADMIN_FEE', text: 'ADMINISTRATION FEES'},
                                                {type: 'CONVERSION_FEE', text: 'CONVERSION FEES'}
                                            ]
                                        },
                                        displayField: 'text',
                                        valueField: 'type',
                                        queryMode: 'local',
                                        remoteFilter: false,
                                        // listClass: 'x-combo-list-small',
                                        forceSelection: true
                                    }
                                },
                                {text: 'Item Code', dataIndex: 'itemcode', flex: 1, editor: {xtype: 'textfield', allowBlank: false} },
                                {
                                    text: 'Trade BP Code', minWidth: 200, menuDisabled: true, flex: 1.5,
                                    columns: [
                                    {
                                        text:'Vendor', sortable: false, locked: true, draggable: false,
                                        columns: [
                                            {text: '000', reference: 'header_tradebp_v', dataIndex: 'header_tradebp_v',xtype:'checkcolumn',  disabled: true, editor: {xtype: 'checkbox', name: 'header_tradebp_v'}, draggable: false, menuDisabled: true, sortable: false, disabledCls: ''}
                                        ]
                                    }, 
                                    {
                                        text: 'Customer', sortable: false, locked: true, draggable: false,
                                        columns: [
                                            {text: '000', reference: 'header_tradebp_c', dataIndex: 'header_tradebp_c', xtype: 'checkcolumn',  disabled: true, editor: {xtype: 'checkbox', name: 'header_tradebp_c'}, draggable: false, menuDisabled: true, sortable: false, disabledCls: ''}
                                        ]
                                    }]
                                },
                                {
                                    text: 'Admin & Storage BP Code', minWidth: 200, menuDisabled: true, flex: 1.5,
                                    columns: [
                                    {
                                        text:'Vendor', sortable: false, locked: true, draggable: false,
                                        columns: [
                                            {text: '000', reference: 'header_nontradebp_v', dataIndex: 'header_nontradebp_v', xtype: 'checkcolumn', disabled: true, editor: {xtype: 'checkbox', name: 'header_nontradebp_v'}, draggable: false, menuDisabled: true, sortable: false, disabledCls: ''}
                                        ]
                                    }, 
                                    {
                                        text: 'Customer', sortable: false, locked: true, draggable: false,
                                        columns: [
                                            {text: '000', reference: 'header_nontradebp_c', dataIndex: 'header_nontradebp_c', xtype: 'checkcolumn', disabled: true, editor: {xtype: 'checkbox', name: 'header_nontradebp_c'}, draggable: false, menuDisabled: true, sortable: false, disabledCls: ''}
                                        ]
                                    }]
                                },
                                {
                                    text: 'Action',
                                    dataIndex: 'action',
                                    // width: 200,
                                    flex: 1,
                                    editor: {
                                        xtype: 'combobox',
                                        typeAhead: true,
                                        triggerAction: 'all',
                                        allowBlank: false,
                                        selectOnTab: true,
                                        store: {
                                            // fields: ['action'],
                                            // data: [{action: 'buy_invoice'}, {action:'sell_invoice'}]
                                            fields: ['action'],
                                            data: [
                                                {action: 'buy_invoice'},
                                                {action: 'sell_invoice'}
                                            ]
                                        },
                                        valueField: 'action',
                                        displayField: 'action',
                                        queryMode: 'local',
                                        remoteFilter: false,
                                        // listClass: 'x-combo-list-small',
                                        forceSelection: true
                                    }
                                },
                                {text: 'GTP Ref. No', dataIndex: 'gtprefno', flex: 1, editor: {xtype: 'textfield'} }

                            ],
                            selType: 'rowmodel',
                            plugins: [
                                {
                                    xclass: 'Ext.grid.plugin.RowEditing',
                                    clicksToMoveEditor: 1,
                                    autoCancel: false,
                                    pluginId: 'editedRow1',
                                    id: 'editedRow1'
                                }
                            ]
                        }
                    ]
                }
            ]
        },
    ]
});
