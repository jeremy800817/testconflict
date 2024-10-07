Ext.define('snap.view.mypricealert.MyPriceAlert', {
    extend: 'snap.view.gridpanel.Base',
    xtype: 'mypricealertview',
    requires: [
        'snap.store.MyPriceAlert',
        'snap.model.MyPriceAlert',
        'snap.view.mypricealert.MyPriceAlertController',
        'snap.view.mypricealert.MyPriceAlertModel'
    ],
    //permissionRoot: '/root/bmmb/pricealert',
    store: { type: 'MyPriceAlert' },
    controller: 'mypricealert-mypricealert',
    viewModel: {
        type: 'mypricealert-mypricealert'
    },
    detailViewWindowHeight: 400,
    enableFilter: true,
    toolbarItems: [
        'detail', '|', 'filter',
    ],
    listeners: {
        afterrender: function () {
            this.store.sorters.clear();
            this.store.sort([{
                property: 'id',
                direction: 'DESC'
            }]);
        }
    },
    columns: [
        { xtype: 'rownumberer', text: 'No.' },
        { text: 'ID', dataIndex: 'id', filter: { type: 'string' }, hidden: true, minWidth: 100, flex: 1 },
        { text: 'Account Holder ID', dataIndex: 'accountholderid', filter: { type: 'int' }, hidden: true, flex: 1 },
        { text: 'Account Holder Name', dataIndex: 'accountholderfullname', filter: { type: 'string' }, minWidth: 130, flex: 1,
            renderer: function (value, rec, rowrec) {
                // console.log(rec,rowrec,'rec')
                
                rec.style = 'color:#00008B'
            
                return Ext.util.Format.htmlEncode(value)
            }, 
         },
        { text: 'Account Holder Code', dataIndex: 'accountholdercode', filter: { type: 'string' }, minWidth: 130, flex: 1,
            renderer: function (value, rec, rowrec) {
                // console.log(rec,rowrec,'rec')
                
                rec.style = 'color:#800000'
            
                return Ext.util.Format.htmlEncode(value)
            }, 
        },
        { text: 'Account Holder NRIC', dataIndex: 'accountholdermykadno', filter: { type: 'string' }, minWidth: 130, flex: 1, renderer: 'boldText' },
        { text: 'Price Provider ID', dataIndex: 'priceproviderid', filter: { type: 'int' }, hidden: true, flex: 1 },
        { text: 'Price Provider Name', dataIndex: 'priceprovidername', filter: { type: 'string' }, minWidth: 130, flex: 1 },
        { text: 'Price Provider Code', dataIndex: 'priceprovidercode', filter: { type: 'string' }, minWidth: 130, flex: 1 },
        {
            text: 'Type', dataIndex: 'type', minWidth: 100,
            filter: {
                type: 'combo',
                store: [['ACESELL', 'ACESELL'], ['ACEBUY', 'ACEBUY'],],
                renderer: function (value, rec) {
                    if (value == 'ACESELL') return 'ACESELL';
                    else if (value == 'ACEBUY') return 'ACEBUY';
                    else return 'Undefined';
                },
            },
        },
        {
            text: 'Amount (RM)', dataIndex: 'amount', filter: { type: 'string' }, minWidth: 100, renderer: Ext.util.Format.numberRenderer('0,000.000'),
            editor: {    //field has been deprecated as of 4.0.5
                xtype: 'numberfield',
                decimalPrecision: 2
            }
        },
        { text: 'Remarks', dataIndex: 'remarks', filter: { type: 'string' }, minWidth: 130, flex: 1 },
        { text: 'Last Triggered On', dataIndex: 'lasttriggeredon', xtype: 'datecolumn', format: 'Y-m-d H:i:s', filter: { type: 'date' }, minWidth: 100 },
        { text: 'Sent On', dataIndex: 'senton', xtype: 'datecolumn', format: 'Y-m-d H:i:s', filter: { type: 'date' }, minWidth: 100 },
        { text: 'Created on', dataIndex: 'createdon', xtype: 'datecolumn', format: 'Y-m-d H:i:s', filter: { type: 'date' }, inputType: 'hidden', minWidth: 100 },
        { text: 'Modified on', dataIndex: 'modifiedon', xtype: 'datecolumn', format: 'Y-m-d H:i:s', filter: { type: 'date' }, inputType: 'hidden', minWidth: 100, hidden: true, },
    ],

});
