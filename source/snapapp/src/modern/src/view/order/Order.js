Ext.define('snap.view.order.Order', {
    extend: 'Ext.grid.Grid',
    xtype: 'orderview',
    requires: [
        'snap.store.Order',
        'snap.model.Order',
        'snap.view.order.OrderController',
        'snap.view.order.OrderModel',
        'Ext.state.Provider',
        'snap.util.HttpStateProvider',
    ],
    stateful: true,
    initComponent: function () {
        this.stateId = this.stateId || Ext.getClass(this).getName();
        
        _this = this;
        vm = this.getViewModel();

        this.callParent(arguments);
    },
    formDialogWidth: 950,
    permissionRoot: '/root/gtp/order',
    store: { type: 'Order' },
    controller: 'order-order',
    viewModel: {
        type: 'order-order'
    },
    enableFilter: true,
    toolbarItems: [
        'detail', 'filter',
    ],
    plugins: {
        pagingtoolbar: true,
        gridfilters: true,
        // rowoperations: {
        //     operation: {
        //         text: 'Archive',
        //         handler: 'onRowOperation',
        //         ui: 'alt'
        //     }
        // }
        // pullrefresh: {
        //     pullText: 'Pull down for reload'
        // },
    },
    // floated: false,
    // axisLock: true,
    // draggable: false,
    // scrollable: false,
    // sm: new Ext.grid.RowSelectionModel({singleSelect:true}),
    // listeners: {
        //     rowclick: function(searchgrid, rowIndex, e) {
            //         var record = storeResults.getAt(rowIndex);
    //         alert(record.data.id);
    //     }
    // },
    title:'Spot Orders',
    userCls: 'panelhead-grid-modern',
    columns: [
        { text: 'Booking On', dataIndex: 'bookingon', xtype: 'datecolumn', format: 'Y-m-d H:i:s', filter: { type: 'date' }, minWidth: 212, 
            cell: {
                tools: {
                    // Tools go to the left of the cell by default.
                    // When the value is just a string, it is the handler.
                    maximize: 'onRecord'
                }
            },
        },
        { text: 'Order #', dataIndex: 'orderno', filter: { type: 'string' }, width: '180px',
            renderer: 'ordernoColor'
            
        },
        {
            text: 'Type', dataIndex: 'type', width: '68px',
            filter: {
                type: 'combo',
                store: [
                    ['CompanySell', 'Buy'],
                    ['CompanyBuy', 'Sell'],
                    ['CompanyBuyBack', 'CompanyBuyBack'],
                ],
            },
            renderer: function (value, rec) {
                if (value == 'CompanySell') return 'Buy';
                else if (value == 'CompanyBuy') return 'Sell';
                else return '--';
            },
        },
        { text: 'Xau Weight (g)', dataIndex: 'xau', filter: { type: 'string' },
            align: 'right', minWidth: 80, renderer: Ext.util.Format.numberRenderer('0,000.000'),
        },
        { text: 'Amount (RM)', dataIndex: 'amount', filter: { type: 'string' },
            align: 'right', minWidth: 120, renderer: Ext.util.Format.numberRenderer('0,000.000'),
        },
        // { text: 'Price', dataIndex: 'price', filter: { type: 'string' } },
        { text: 'Book By', dataIndex: 'byweight', filter: { type: 'string' },
            renderer: function (value, record, dataIndex, cell) {
                if (value == '0'){
                    cell.setStyle('color:#800080;');
                    value = 'Amount';
                }
                else if (value == '1'){
                    cell.setStyle('color:#d4af37;');
                    value = 'Weight'
                }else{
                    value = 'Unassigned';
                }
                return value;
            },
            
        },
        { text: 'Partner', dataIndex: 'partnername', filter: { type: 'string' }, minWidth: 130, },
        // { text: 'Partner Ref', dataIndex: 'partnerrefid', hidden: true, filter: { type: 'string' } },
        // { text: 'ID', dataIndex: 'id', filter: { type: 'string' }, hidden: true, },
        // {
        //     text: 'Book By', dataIndex: 'byweight',
        //     filter: {
        //         type: 'combo',
        //         store: [
        //             ['0', 'Amount'],
        //             ['1', 'Weight'],

        //         ],
        //     },
        //     renderer: function (value, rec) {
        //         if (value == '0') return 'Weight';
        //         else if (value == '1') return 'Amount';
        //         else return 'Unassigned';
        //     },
        // },
        // { text: 'Product Code', dataIndex: 'productcode', filter: { type: 'string' }, width: 150 },
        { text: 'Product', dataIndex: 'productname', filter: { type: 'string' }, minWidth: 100, },
        // { text: 'Created On', dataIndex: 'createdon', xtype: 'datecolumn', format: 'd/m/Y', filter: { type: 'date' }, hidden: true },
        // { text: 'Modified On', dataIndex: 'modifiedon', xtype: 'datecolumn', format: 'd/m/Y', filter: { type: 'date' }, hidden: true },
        // { text: 'Created By', dataIndex: 'createdbyname', filter: { type: 'string' }, hidden: true },
        // { text: 'Modified By', dataIndex: 'modifiedbyname', filter: { type: 'string' }, hidden: true },
        {
            text: 'Status', dataIndex: 'status', minWidth: 130,
            filter: {
                type: 'combo',
                store: [
                    ['0', 'Pending'],
                    ['1', 'Confirmed'],
                    ['2', 'PendingPayment'],
                    ['3', 'PendingCancel'],
                    ['4', 'Cancelled'],
                    ['5', 'Completed'],
                    ['6', 'Expired'],
                ],
            },
            renderer: function (value, rec) {
                if (value == '0') return 'Pending';
                else if (value == '1') return 'Confirmed';
                else if (value == '2') return 'PendingPayment';
                else if (value == '3') return 'PendingCancel';
                else if (value == '4') return 'Cancelled';
                else if (value == '5') return 'Completed';
                else return 'Expired';
            },
        }
    ],
    //////////////////////////////////////////////////////////////
    /// View properties settings
    ///////////////////////////////////////////////////////////////
    enableDetailView: true,
    detailViewWindowHeight: 500,
    detailViewWindowWidth: 500,
    style: 'word-wrap: normal',
    detailViewSections: { default: 'Properties' },
    detailViewUseRawData: true,

    formConfig: {
        controller: 'orderqueue-orderqueue',
        formDialogTitle: 'Order Queue',

        // Form configuration

        formDialogWidth: 950,
        formDialogTitle: 'Order',
        enableFormDialogClosable: false,
        formPanelDefaults: {
            border: false,
            xtype: 'panel',
            flex: 1,
            layout: 'anchor',
            msgTarget: 'side',
            margins: '0 0 10 10'
        },
        enableFormPanelFrame: false,
        formPanelLayout: 'hbox',


        formPanelItems: [
            { inputType: 'hidden', hidden: true, name: 'id' },
            { xtype: 'hiddenfield', inputType: 'hidden', hidden: true, name: 'status', value: '1' },

            {
                items: [
                    { xtype: 'hidden', hidden: true, name: 'id' },
                    { xtype: 'hidden', hidden: true, name: 'orderlist', bind: '{orderlist}' },
                    { xtype: 'hidden', hidden: true, name: 'orderdeliverydata', reference: 'orderdeliverydata' },
                    {
                        xtype: 'fieldset', title: 'Spot Order', collapsible: false,
                        default: { labelWidth: 90, layout: 'hbox' },
                        items: [
                            {
                                xtype: 'combobox',
                                fieldLabel: 'Product',
                                name: 'productid',
                                displayField: 'name',
                                valueField: 'id',
                                itemId: 'inventoryIdOrder',
                                reference: 'inventorycatid',
                                minChars: 0,
                                store: {
                                    type: 'Order',
                                    //type: 'array',
                                    autoLoad: true,
                                    remoteFilter: true,
                                    sorters: 'name',
                                },
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
                                typeAhead: true,
                                queryMode: 'local',
                                forceSelection: true,
                            },
                            { xtype: 'displayfield', fieldLabel: 'Order On', itemId: 'orderon', value: new Date(), renderer: Ext.util.Format.dateRenderer('d-m-Y') },
                            { xtype: 'textfield', fieldLabel: 'Total Value (RM)', name: 'ponums', itemId: 'ponums' },
                            { xtype: 'textfield', fieldLabel: 'Total Xau Weight (gram)', name: 'ponum', itemId: 'ponum' },
                        ]
                    }
                ],
                listeners: {
                    afterRender: function () {
                        var me = this;
                        var permission = snap.getApplication().hasPermission('/root/branch/order/approve');
                        var fieldBranch = Ext.ComponentQuery.query('#orderFromBranch');
                        var fieldPO = Ext.ComponentQuery.query('#ponum');
                        if (permission !== true) {
                            fieldBranch[0].disable();
                            fieldPO[0].disable();
                        }
                    }
                }
            },
        ]
    },
});
