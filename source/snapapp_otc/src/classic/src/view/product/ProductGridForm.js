Ext.define('snap.view.product.ProductGridForm', {
    extend: 'snap.view.gridpanel.GridForm',
    alias: 'widget.ProductGridForm',
    requires: [
        'Ext.panel.Panel',
        'Ext.window.Window',
        'Ext.form.*',
        'snap.view.gridpanel.BaseController',
        'snap.view.product.ProductTreeController'
    ],
    controller: 'gridpanel-producttreecontroller',
    store:{       
        productcategories:Ext.create('snap.store.ProductCategories'),        
    },
    reference: 'formWindow',
    formDialogTitle: 'Product',
    formDialogWidth: '80%',
    enableFormDialogClosable: false,
    formPanelDefaults: {
        msgTarget: 'side',
        margins: '0 0 10 0'
    },
    height: '70%',
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
            xtype: 'fieldset', title: 'Product Details', collapsible: false, margin: '0 5 5 0',
            defaults: { labelWidth: 190, width: '100%', layout: 'hbox', hideLabel: false },
            items: [
                {
                    layout: 'column',
                    margin: '28 8 8 18',
                    width: '60%',
                    height: '100%',
                    items: [
                        {
                            columnWidth: 0.5,
                            items: [
                                { xtype: 'hidden', hidden: true, name: 'id' },
                                //{ xtype: 'textfield', fieldLabel: 'Category ID', name: 'categoryid' },
                                {
                                    xtype: 'combobox', fieldLabel: 'Category ID', store: Ext.getStore('productcategories').load(), queryMode: 'local', remoteFilter: false,
                                    name: 'categoryid', valueField: 'id', displayField: 'value',
                                    forceSelection: true, editable: false,
                                    renderer: function (value, metaData, record, rowIndex, colIndex, store, view) {     
                                        var productitems =Ext.getStore('productcategories').load();                                                                              
                                        var catRecord = productitems.findRecord('id', value);                                       
                                        return catRecord ? catRecord.get('value') : ''; 
                                    }, 
                                }, 
                                { xtype: 'textfield', fieldLabel: 'Code', name: 'code' },
                                { xtype: 'textfield', fieldLabel: 'Name', name: 'name' },
                                { xtype: 'checkbox', fieldLabel: 'Company can sell', name: 'companycansell', inputValue: '1', uncheckedValue: '0' },
                                { xtype: 'checkbox', fieldLabel: 'Company can buy', name: 'companycanbuy', inputValue: '1', uncheckedValue: '0' },
                            ]
                        },
                        {
                            columnWidth: 0.5,
                            items: [
                                { xtype: 'checkbox', fieldLabel: 'Trx by weight', name: 'trxbyweight', inputValue: '1', uncheckedValue: '0' },
                                { xtype: 'checkbox', fieldLabel: 'Trx by currency', name: 'trxbycurrency', inputValue: '1', uncheckedValue: '0' },
                                { xtype: 'checkbox', fieldLabel: 'Deliverable', name: 'deliverable', inputValue: '1', uncheckedValue: '0' },
                                { xtype: 'checkbox', fieldLabel: 'Status', name: 'status', inputValue: '1', uncheckedValue: '0' },
                                { xtype: 'textfield', fieldLabel: 'SAP Item code', name: 'sapitemcode' },
                            ]
                        },
                    ]
                },
            ]
        }
    ]
});
