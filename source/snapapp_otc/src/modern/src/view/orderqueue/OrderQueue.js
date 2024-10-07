Ext.define('snap.view.orderqueue.OrderQueue', {
    extend: 'Ext.grid.Grid',
    xtype: 'orderqueueview',
    requires: [
        'snap.store.OrderQueue',
        'snap.model.OrderQueue',
        'snap.view.orderqueue.OrderQueueController',
        'snap.view.orderqueue.OrderQueueModel'
    ],
    permissionRoot: '/root/gtp/ftrorder',
    store: { type: 'OrderQueue' },
    controller: 'orderqueue-orderqueue',
    viewModel: {
        type: 'orderqueue-orderqueue'
    },
    detailViewWindowHeight: 400,
    enableFilter: true,
    toolbarItems: [
        'detail', 'filter',
    ],
    plugins: {
        pagingtoolbar: true,
        gridfilters: true
    },
    title:'Future Orders',
    columns: [
        { text: 'ID', dataIndex: 'id', filter: { type: 'string' }},
        { text: 'Spot Order No', dataIndex: 'orderno', filter: { type: 'string' }},
        { text: 'Partner Code', dataIndex: 'partnercode', filter: { type: 'string' }},
        { text: 'Order Queue No', dataIndex: 'orderqueueno', filter: { type: 'string' }},
        { text: 'Salesperson Name', dataIndex: 'salespersonname', filter: { type: 'string' }, hidden: true,},
        {
            text: 'Order Type', dataIndex: 'ordertype',
            filter: {
                type: 'combo',
                store: [
                    ['CompanySell', 'CompanySell'],
                    ['CompanyBuy', 'CompanyBuy'],
                    ['CompanyBuyBack', 'CompanyBuyBack'],
                ],
                renderer: function (value, rec) {
                    if (value == 'CompanySell') return 'CompanySell';
                    else if (value == 'CompanyBuy') return 'CompanyBuy';
                    else return 'CompanyBuyBack';
                },
            },
        },
        { text: 'Price Target (RM/g)', dataIndex: 'pricetarget', filter: { type: 'string' }},
        {
            text: 'Book By', dataIndex: 'byweight',
            filter: {
                type: 'combo',
                store: [
                    ['0', 'Amount'],
                    ['1', 'Weight'],

                ],

            },
            renderer: function (value, rec) {
                if (value == '0') return 'Amount';
                else if (value == '1') return 'Weight';
                else return 'Unassigned';
            },
        },
        { text: 'Xau Weight (g)', dataIndex: 'xau', filter: { type: 'string' }},
        { text: 'Amount (RM)', dataIndex: 'amount', filter: { type: 'string' }},
        { text: 'Product', dataIndex: 'productname', filter: { type: 'string' }},
        { text: 'Created on', dataIndex: 'createdon', xtype: 'datecolumn', format: 'd/m/Y', filter: { type: 'date' }, inputType: 'hidden'},
        { text: 'Modified on', dataIndex: 'modifiedon', xtype: 'datecolumn', format: 'd/m/Y', filter: { type: 'date' }, inputType: 'hidden', hidden: true},
        { text: 'Created by', dataIndex: 'createdbyname', filter: { type: 'string' }, inputType: 'hidden', hidden: true},
        { text: 'Modified by', dataIndex: 'modifiedbyname', filter: { type: 'string' }, inputType: 'hidden', hidden: true},
        {
            text: 'Status', dataIndex: 'status',

            filter: {
                type: 'combo',
                store: [
                    ['0', 'Pending'],
                    ['1', 'Active'],
                    ['2', 'Fulfilled'],
                    ['3', 'Matched'],
                    ['4', 'Pending Cancel'],
                    ['5', 'Cancelled'],
                    ['6', 'Expired'],

                ],

            },
            renderer: function (value, rec) {
                if (value == '0') return 'Pending';
                else if (value == '1') return 'Active';
                else if (value == '2') return 'Fulfilled';
                else if (value == '3') return 'Matched';
                else if (value == '4') return 'Pending Cancel';
                else if (value == '5') return 'Cancelled';
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
