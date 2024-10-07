Ext.define('snap.view.unfulfillpo.UnfulfillPO',{
    extend: 'Ext.grid.Grid',
    xtype: 'unfulfillpo',
    requires: [
        'snap.store.UnfulfilledPO',
        'snap.model.UnfulfilledPO',
        //'snap.view.unfulfillpo.UnfulfillPODashboardController',
        //'snap.view.orderdashboard.UnfulfillPODashboardModel',
    ],
    //title: 'J List',
    header: {    
        style: 'background-color: #204A6D;border-color: #204A6D;',
    },
    permissionRoot: '/root/gtp/unfulfilledorder',
    store: { type: 'UnfulfilledPO' },    
    controller: 'unfulfillpodashboard-unfulfillpodashboard',
	width: '100%',
	layout: {
		type: 'hbox',
		align: 'fit',
	},
    plugins: {
		pagingtoolbar: true
	},
    detailViewWindowHeight: 400,  //Height of the view detail window
    height: 300,
    enableFilter: true,
    columns: [
        { text: 'PO Number', dataIndex: 'docNum', align: 'right', filter: { type: 'int' } },
        { text: 'PO Date', dataIndex: 'docDate', align: 'right', formatter: 'date("Y-m-d")', filter: { type: 'date' } },
        { text: 'Card Code', dataIndex: 'cardCode',align: 'right', filter: { type: 'string' }},
        { text: 'Doc Entry', dataIndex: 'docEntry', align: 'right', filter: { type: 'string' } },
        { text: 'Line Number', dataIndex: 'lineNum', align: 'right', filter: { type: 'int' }  },
        { text: 'Item Code', dataIndex: 'itemCode', align: 'right', filter: { type: 'string' }  },
        { text: 'Description', dataIndex: 'dscription', align: 'right', filter: { type: 'string' }  },
        { text: 'Quantity', dataIndex: 'quantity', align: 'right', filter: { type: 'string' }, renderer: Ext.util.Format.numberRenderer('0,000.000'),
            editor: {    //field has been deprecated as of 4.0.5
                xtype: 'numberfield',
                decimalPrecision: 3
            } 
        },                  
        { text: 'Open Quanity', dataIndex: 'openQty', align: 'right', filter: { type: 'float' }, renderer: Ext.util.Format.numberRenderer('0,000.000'),
            editor: {    //field has been deprecated as of 4.0.5
                xtype: 'numberfield',
                decimalPrecision: 3
            } 
        },  
        { text: 'Draft Quantity', dataIndex: 'draftQty', align: 'right', filter: { type: 'float' }, renderer: Ext.util.Format.numberRenderer('0,000.000'),
            editor: {    //field has been deprecated as of 4.0.5
                xtype: 'numberfield',
                decimalPrecision: 3
            } 
        },  
        { text: 'Open Draft', dataIndex: 'opndraft', align: 'right', filter: { type: 'float' }, renderer: Ext.util.Format.numberRenderer('0,000.000'),
            editor: {    //field has been deprecated as of 4.0.5
                xtype: 'numberfield',
                decimalPrecision: 3
            } 
        },  
        { text: 'Price', dataIndex: 'price', align: 'right', filter: { type: 'float' }, renderer: Ext.util.Format.numberRenderer('0,000.000'),
            editor: {    //field has been deprecated as of 4.0.5
                xtype: 'numberfield',
                decimalPrecision: 3
            } 
        },  
        { text: 'Doc Total', dataIndex: 'docTotal', align: 'right', filter: { type: 'float' }, renderer: Ext.util.Format.numberRenderer('0,000.000'),
            editor: {    //field has been deprecated as of 4.0.5
                xtype: 'numberfield',
                decimalPrecision: 3
            } 
        },  
        { text: 'VAT Sum', dataIndex: 'vatSum', align: 'right', filter: { type: 'float' }, renderer: Ext.util.Format.numberRenderer('0,000.000'),
            editor: {    //field has been deprecated as of 4.0.5
                xtype: 'numberfield',
                decimalPrecision: 3
            } 
        },  
        { text: 'Doc Totl Amount', dataIndex: 'docTotalAmt', align: 'right', filter: { type: 'float' }, renderer: Ext.util.Format.numberRenderer('0,000.000'),
            editor: {    //field has been deprecated as of 4.0.5
                xtype: 'numberfield',
                decimalPrecision: 3
            } 
        },  
        { text: 'Draft GRN', dataIndex: 'draftGRN', align: 'right', filter: { type: 'string' }},
        { text: 'GTP REF No', dataIndex: 'u_GTPREFNO', align: 'right', filter: { type: 'string' } },
        { text: 'Comments', dataIndex: 'comments', align: 'right', filter: { type: 'string' } },                   
    ],  
});


