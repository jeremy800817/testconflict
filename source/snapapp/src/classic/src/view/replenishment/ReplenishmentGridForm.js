Ext.define('snap.view.replenishment.ReplenishmentGridForm', {
    extend: 'snap.view.gridpanel.GridForm',
    alias: 'widget.ReplenishmentGridForm',
    requires: [
        'Ext.panel.Panel',
        'Ext.window.Window',
        'Ext.form.*',
        'snap.view.gridpanel.BaseController',
        'snap.view.replenishment.ReplenishmentTreeController',
        'Ext.view.MultiSelector',
        'Ext.grid.*',
        'Ext.layout.container.Column',
    ],
    viewModel: {
        data: {
            theCompany: null,
            inputgrossweight: 0,

            total_poweight: 100, // all po sum weight
            total_grossweight: 0,   // after * purity(all)
            total_balanceweight: 0, // remaining po weight
            total_purity: 0,    // purity(all)

        }
    },
    store:{
        selectedGRNStore: {},
    },
    controller: 'gridpanel-replenishmenttreecontroller',
    reference: 'formWindow',
    formDialogTitle: 'Replenishment Logistic',
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
    dockedItems: [{
        xtype: 'toolbar',
        dock: 'bottom',
        ui: 'footer',
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
        items: [, {
            text: 'Clear Form',
            cls: 'windowclearbutton',
            handler: 'onClearWindow'
        },{
            text: 'Cancel',
            handler: 'onWindowCancel'
        }, {
            text: 'Confirm',
            handler: 'onRequestWindowSaveButton'
        }],
    }],
    formPanelItems: [
        {
            xtype: 'tabpanel',
            flex: 1,
            reference: 'collectiontab',
            items: [
                {
                    title: 'Logistic Bundle Details',
                    layout: 'column',
                    margin: '28 8 8 18',
                    width: 800,
                    // disabled: true,
                    items: [
                        {
                            xtype: 'container',
                            width: 700,
                            height: 400,
                            layout: 'fit',
                            items: [{
                           
                                    xtype: 'multiselector',
                                    title: 'Select Items',

                                    columns: {
                                        items: [
                                            {
                                                text: "Serial Number",
                                                dataIndex: "serialno"
                                            },{
                                                text: "Product",
                                                dataIndex: "productname"
                                            },{
                                                text: "Branch Name",
                                                dataIndex: "branchname"
                                            },{
                                                text: "Replenishment No",
                                                dataIndex: "replenishmentno"
                                            },
                                            
                                        ],
                                        defaults: {
                                            flex: 1
                                        }
                                    },
                                    
                                    fieldName: 
                                        // '<tpl for=".">[ {serialno} ] {productname}</tpl>'
                                        'serialno'
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
                                            // active: function(){
                                            //     console.log('active')
                                            // },
                                            // search: function(){
                                            //     console.log('active1')
                                            // },
                                            add: function(a,b,c){
                                                
                                                url = 'index.php?hdl=replenishment&action=getItemsList'
                                                this.getSearchStore().getProxy().setUrl(url)

                                                if (this.getSearchStore().getProxy().url != url){
                                                    this.getView().lookupReference('pocontainer').getStore().removeAll()
                                                    this.getSearchStore().removeAll();
                                                    this.getSearchStore().load()
                                                }
                                                
                                                console.log(this,a,b,c)
                                            },
                                            
                                        },
                                        
                                        field: 'serialno',

                                        reference: 'searchpocontainer',
                                        // minWidth: 400,
                                        // minHeight: 300,
                                        width: 400,
                                        height: 300,
                                        store: {
                                            storeId: 'mcddx',
                                            alias: 'mcsx',
                                            model: 'snap.model.Replenishmentitems',
                                            sorters: 'name',
                                            proxy: {
                                                type: 'ajax',
                                                limitParam: null,
                                                url: 'index.php?hdl=replenishment&action=getItemsList'
                                                // &query='+Ext.bind.getBind('customercombox.selection.cardCode')
                                            }
                                        }
                                    }
                            }]
                        },
                        {
                            items: [
                                //{ xtype: 'hiddenfield', inputType: 'hidden', hidden: true, name: 'id' , value:selectedRecords[0].id,allowBlank: false},	
                                { 
                                    reference: 'senderid',
                                    xtype: 'combobox',
                                    fieldLabel: 'Sales Person',
                                    name:'salespersonid',
                                    typeAhead: true,
                                    triggerAction: 'all',
                                    flex: 1,
                                    selectOnTab: true,
                                    store: {
                                        autoLoad: true,
                                        type: 'SalesPersons',                   
                                        sorters: 'name'
                                    },            
                                    style:'padding-left: 20px;',
                                    lazyRender: true,
                                    displayField: 'name',
                                    valueField: 'id',
                                    queryMode: 'remote',
                                    remoteFilter: false,
                                    listClass: 'x-combo-list-small',
                                    forceSelection: true,
                                    allowBlank: false
                                },     
                            ]
                        },
                        {
                            items:[
                                { reference: 'deliverydate', xtype: 'datefield', style:'padding-left: 20px;', flex: 1, fieldLabel: 'Date of Delivery', name: 'dateofdelivery', format: 'Y-m-d H:i:s', allowBlank: false },                      
                            ]
                        },		
                        {

                            items:[
                                {
                                    xtype : 'displayfield',
                                    width : '99%',
                                    padding: '0 1 0 1',
                                    value: "<h5 style=' width:100%;line-height: normal;overflow: inherit; margin:0px 0 30px; font-size: 16px;color:#757575;'><span style='background:#fff;position: relative;top: 10px;'>Note: The SLA for Replenishment deliveries is by 15th and 30th of each month. </span></h5>",
                                    //style: "content: 'OR';display: inline-block;padding: 3px 5px 3px 0;padding-top: 3px;padding-right: 5px;padding-bottom: 3px;padding-left: 0px;background-color: #ffffff;font-size: 12px;font-weight: bold;text-transform: uppercase;color: #BBC3CE;letter-spacing: 1px; position: absolute;top: -0.6em;left: 0;",
                                    
                                },
                            ]
                        },	
                        
                    ]
                },
            ]
        },
    ]
});
