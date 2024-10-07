Ext.define('snap.view.buyback.BuybackGridForm', {
    extend: 'snap.view.gridpanel.GridForm',
    alias: 'widget.BuybackGridForm',
    requires: [
        'Ext.panel.Panel',
        'Ext.window.Window',
        'Ext.form.*',
        'snap.view.gridpanel.BaseController',
        'snap.view.buyback.BuybackTreeController'
    ],
    store:{
        teststore:Ext.create('snap.store.ProductItems'),
        pricesourceproviders:Ext.create('snap.store.PriceSourceProviders'),        
    },
    controller: 'gridpanel-buybacktreecontroller',
    reference: 'formWindow',
    formDialogTitle: 'Buyback',
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
                                                text: "Partner Name",
                                                dataIndex: "partnername"
                                            },{
                                                text: "Partner Reference No",
                                                dataIndex: "partnerrefno"
                                            },{
                                                text: "Serial No",
                                                dataIndex: "items",
                                                renderer: function (v, record) {
                                                    printHtml = '<table>';
                                                    // Parse JSON return from V 
                                                    // Sample JSON 
                                                    // [{"sapreturnid":20,"code":"GS-999-9-5g","serialnumber":"IGR3690683","weight":"5.000000","sapreverseno":"16678"}]
                                                    data = JSON.parse(v);
                                           
                                                    data.forEach((index) => {
                                                 
                                                        if(index.sapreturnid){
                                                            printHtml += `<tr>
                                                                <td style="text-align:center; width:200px">${index.serialnumber}</td>
                                                            </tr>`;
                                                        }else{
                                                            printHtml += `<tr>
                                                                <td style="text-align:center; width:200px">${index.serialno}</td>
                                                            </tr>`;
                                                        }
                                                       
                                                       
                                                    });
                                                    printHtml += '</table>';
                                                        return printHtml ? printHtml : null;
                                                }
                                            },{
                                                text: "Denomination",
                                                dataIndex: "items",
                                                renderer: function (v, record) {
                                                    printHtml = '<table>';
                                                    // Parse JSON return from V 
                                                    // Sample JSON 
                                                    // [{"sapreturnid":20,"code":"GS-999-9-5g","serialnumber":"IGR3690683","weight":"5.000000","sapreverseno":"16678"}]
                                                    data = JSON.parse(v);
                                           
                                                    data.forEach((index) => {
                                                 
                                                        if(index.sapreturnid){
                                                            printHtml += `<tr>
                                                                <td style="text-align:center; width:200px">${parseInt(index.weight)}</td>
                                                            </tr>`;
                                                        }else{
                                                            printHtml += `<tr>
                                                                <td style="text-align:center; width:200px">${parseInt(index.denomination)}</td>
                                                            </tr>`;
                                                        }
                                                       
                                                       
                                                    });
                                                    printHtml += '</table>';
                                                        return printHtml ? printHtml : null;
                                                }
                                            },{
                                                text: "Branch Name",
                                                dataIndex: "branchname"
                                            },{
                                                text: "Buyback No",
                                                dataIndex: "buybackno"
                                            },
                                            
                                        ],
                                        defaults: {
                                            flex: 1
                                        }
                                    },
                                    
                                    fieldName: 
                                        // '<tpl for=".">[ {serialno} ] {productname}</tpl>'
                                        'html_list'
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
                                                
                                                url = 'index.php?hdl=buyback&action=getItemsList'
                                                this.getSearchStore().getProxy().setUrl(url)

                                                if (this.getSearchStore().getProxy().url != url){
                                                    this.getView().lookupReference('pocontainer').getStore().removeAll()
                                                    this.getSearchStore().removeAll();
                                                    this.getSearchStore().load()
                                                }
                                                
                                                console.log(this,a,b,c)
                                            },
                                            
                                        },
                                        
                                        field: 'html_list',

                                        reference: 'searchpocontainer',
                                        // minWidth: 400,
                                        // minHeight: 300,
                                        width: 400,
                                        height: 300,
                                        store: {
                                            storeId: 'mcddx',
                                            alias: 'mcsx',
                                            model: 'snap.model.BuybackItems',
                                            sorters: 'name',
                                            proxy: {
                                                type: 'ajax',
                                                limitParam: null,
                                                url: 'index.php?hdl=buyback&action=getItemsList'
                                                // &query='+Ext.bind.getBind('customercombox.selection.cardCode')
                                            }
                                        }
                                    }
                            },]
                        },
                        {
                            items: [
                                //{ xtype: 'hiddenfield', inputType: 'hidden', hidden: true, name: 'id' , value:selectedRecords[0].id,allowBlank: false},	
                                { 
                                    xtype: 'combobox',
                                    fieldLabel: 'Sales Person',
                                    name:'salespersonid',
                                    typeAhead: true,
                                    triggerAction: 'all',
                                    flex: 1,
                                    selectOnTab: true,
                                    reference: 'salesperson',
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
                                { xtype: 'datefield', style:'padding-left: 20px;',  reference: 'deliverydate', flex: 1, fieldLabel: 'Date of Delivery', name: 'dateofdelivery', format: 'Y-m-d H:i:s', allowBlank: false },                      
                            ]
                        },		
                        
                    ]
                },
            ]
        },
    ]
});
