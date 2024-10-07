//
Ext.define('snap.view.orderdashboard.UnfulfillPO',{
    extend: 'snap.view.gridpanel.Base',
    xtype: 'unfulfillpoview',

    requires: [
        'snap.store.UnfulfilledPO',
        'snap.model.UnfulfilledPO',
        'snap.view.orderdashboard.UnfulfillPODashboardController',
        //'snap.view.orderdashboard.UnfulfillPODashboardModel',
    ],
    permissionRoot: '/root/gtp/unfulfilledorder',
    store: { type: 'UnfulfilledPO' },
    //store: Ext.create('snap.store.UnfulfilledPO'),
    controller: 'unfulfillpodashboard-unfulfillpodashboard',
    toolbarItems: [
        //'add', 'edit''detail', '|', 'delete', 'filter','|',
        'detail', '|', 'filter',
      
       
    ],
    detailViewWindowHeight: 400,  //Height of the view detail window

    enableFilter: true,
    // gridSelectionModel:'checkboxmodel',
    columns: [
        { text: 'PO Number', dataIndex: 'docNum', filter: { type: 'int' },  renderer: 'boldText'  },
        { text: 'PO Date', dataIndex: 'docDate', xtype: 'datecolumn', format: 'Y-m-d', filter: { type: 'date' } },
        { text: 'Card Code', dataIndex: 'cardCode',filter: { type: 'string' }, },
        { text: 'Doc Entry', dataIndex: 'docEntry',filter: { type: 'string' } },
        { text: 'Line Number', dataIndex: 'lineNum',filter: { type: 'int' },  align: 'right',  },
        { text: 'Item Code', dataIndex: 'itemCode',filter: { type: 'string' }  },
        { text: 'Description', dataIndex: 'dscription',filter: { type: 'string' }  },
        { text: 'Quantity', dataIndex: 'quantity', filter: { type: 'float' }, align: 'right', renderer:  Ext.util.Format.numberRenderer('0,000.000'),
        editor: {    //field has been deprecated as of 4.0.5
            xtype: 'numberfield',
            decimalPrecision: 3
        }},           
        { text: 'Open Quantity', dataIndex: 'openQty', filter: { type: 'float' }, align: 'right', renderer:  Ext.util.Format.numberRenderer('0,000.000'),
        editor: {    //field has been deprecated as of 4.0.5
            xtype: 'numberfield',
            decimalPrecision: 3
        }},
        { text: 'Draft Quantity', dataIndex: 'draftQty', filter: { type: 'float' }, align: 'right', renderer:  Ext.util.Format.numberRenderer('0,000.000'),
        editor: {    //field has been deprecated as of 4.0.5
            xtype: 'numberfield',
            decimalPrecision: 3
        }},
        { text: 'Open Draft', dataIndex: 'opndraft', filter: { type: 'float' }, align: 'right', renderer:  Ext.util.Format.numberRenderer('0,000.000'),
        editor: {    //field has been deprecated as of 4.0.5
            xtype: 'numberfield',
            decimalPrecision: 3
        }},
        { text: 'Price', dataIndex: 'price',filter: { type: 'float' }, align: 'right', renderer:  Ext.util.Format.numberRenderer('0,000.000'),
        editor: {    //field has been deprecated as of 4.0.5
            xtype: 'numberfield',
            decimalPrecision: 3
        }},
        { text: 'Doc Total', dataIndex: 'docTotal',filter: { type: 'float' }, align: 'right', renderer:  Ext.util.Format.numberRenderer('0,000.000'),
        editor: {    //field has been deprecated as of 4.0.5
            xtype: 'numberfield',
            decimalPrecision: 3
        }},
        { text: 'VAT Sum', dataIndex: 'vatSum',filter: { type: 'float' },  align: 'right', },
        { text: 'Doc Total Amount', dataIndex: 'docTotalAmt',filter: { type: 'float' }, align: 'right', renderer:  Ext.util.Format.numberRenderer('0,000.000'),
        editor: {    //field has been deprecated as of 4.0.5
            xtype: 'numberfield',
            decimalPrecision: 3
        }},
        { text: 'Draft GRN', dataIndex: 'draftGRN',filter: { type: 'string' }},
        { text: 'GTP REF No', dataIndex: 'u_GTPREFNO',filter: { type: 'string' }, renderer: 'boldText',  align: 'right', },
        { text: 'Comments', dataIndex: 'comments',filter: { type: 'string' } },                   
    ],

    //////////////////////////////////////////////////////////////
    /// View properties settings
    ///////////////////////////////////////////////////////////////
    enableDetailView: true,
    detailViewWindowHeight: 500,
	detailViewWindowWidth: 500,
	style: 'word-wrap: normal',
    detailViewSections: {default: 'Properties'},
    detailViewUseRawData: true,

});
