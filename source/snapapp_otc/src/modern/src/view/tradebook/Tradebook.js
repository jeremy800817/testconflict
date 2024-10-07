Ext.define('snap.view.tradebook.Tradebook', {
    extend: 'Ext.panel.Panel',
    xtype: 'tradebook',
    requires: [        
        'Ext.Button',
        'Ext.Img'
    ],
    controller: 'tradebook-tradebook',
    viewModel: {
        type: 'tradebook-tradebook'
    },   
    layout: {
        align: 'stretch',
        type: 'hbox',
    },
    style : {
        borderColor : 'black',
        borderStyle : 'solid'
    },
    title: 'Tradebook',
    items: [{
        xtype: 'tabpanel',
        width: '100%',
        items: [
            {
                title: 'Spot Order',
                items: [{
                    xtype: 'container',
                    layout: {
                        type: 'hbox',
                        align: 'stretch',
                    },

                    items: [{
                        xtype: 'grid',
                        store: Ext.create('snap.store.Order'),
                        columns: [
                            { text: 'ID', dataIndex: 'id', hidden: true, filter: { type: 'int' } },
                            { text: 'Partner', dataIndex: 'partnername' },
                            { text: 'Buyer', dataIndex: 'buyername', hidden: true },
                            { text: 'Product Code', dataIndex: 'productcode', width: 150 },
                            { text: 'Partner Ref', dataIndex: 'partnerrefid', hidden: true },
                            { text: 'Order No', dataIndex: 'orderno', hidden: true },
                            { text: 'Price Stream ID', dataIndex: 'pricestreamid', hidden: true, },
                            { text: 'Salesperson Name', dataIndex: 'salespersonname', hidden: true },
                            { text: 'Api Version', dataIndex: 'apiversion' },
                            {
                                text: 'Type', dataIndex: 'type', renderer: function (value, rec) {
                                    if (value == 'CompanySell') return 'CompanySell';
                                    else if (value == 'CompanyBuy') return 'CompanyBuy';
                                    else return 'CompanyBuyBack';
                                },
                            },
                            { text: 'Product', dataIndex: 'productname' },
                            {
                                text: 'Is Spot', dataIndex: 'isspot',
                                renderer: function (value, rec) {
                                    if (value == '0') return 'False';
                                    else if (value == '1') return 'True';
                                    else return 'Unassigned';
                                },
                            },
                            { text: 'Price', dataIndex: 'price' },
                            {
                                text: 'By Weight', dataIndex: 'byweight',
                                renderer: function (value, rec) {
                                    if (value == '0') return 'Amount';
                                    else if (value == '1') return 'Weight';
                                    else return 'Unassigned';
                                },
                            },
                            { text: 'xau', dataIndex: 'xau', hidden: true },
                            { text: 'Amount', dataIndex: 'amount' },
                            { text: 'Fee', dataIndex: 'fee' },
                            { text: 'Remarks', dataIndex: 'remarks', hidden: true, hidden: true },
                            { text: 'Booking On', dataIndex: 'bookingon', xtype: 'datecolumn', format: 'd/m/Y', filter: { type: 'date' } },
                            { text: 'Booking Price', dataIndex: 'bookingprice' },
                            { text: 'Booking Price Stream ID', dataIndex: 'bookingpricestreamid', hidden: true, hidden: true },
                            { text: 'Confirm On', dataIndex: 'confirmon', xtype: 'datecolumn', format: 'd/m/Y', filter: { type: 'date' } },
                            { text: 'Confirm By', dataIndex: 'confirmbyname' },
                            { text: 'Confirm Price Stream ID', dataIndex: 'confirmpricestreamid', hidden: true },
                            { text: 'Confirm Price', dataIndex: 'confirmprice', },
                            { text: 'Confirm Reference', dataIndex: 'confirmreference', hidden: true },
                            { text: 'Cancel On', dataIndex: 'cancelon', xtype: 'datecolumn', format: 'd/m/Y', filter: { type: 'date' } },
                            { text: 'Cancel By', dataIndex: 'cancelbyname', },
                            { text: 'Cancel Price Stream ID', dataIndex: 'cancelpricestreamid' },
                            { text: 'Cancel Price', dataIndex: 'cancelprice', },
                            { text: 'Notify URL', dataIndex: 'notifyurl', hidden: true, hidden: true },
                            {
                                text: 'Reconciled', dataIndex: 'reconciled',
                                renderer: function (value, rec) {
                                    if (value == '0') return 'False';
                                    else if (value == '1') return 'True';
                                    else return 'Unassigned';
                                },
                            },
                            { text: 'Reconciled On', dataIndex: 'reconciledon', hidden: true, xtype: 'datecolumn', format: 'd/m/Y', filter: { type: 'date' } },
                            { text: 'Reconciled By', dataIndex: 'reconciledbyname', hidden: true },
                            { text: 'Created On', dataIndex: 'createdon', xtype: 'datecolumn', format: 'd/m/Y', filter: { type: 'date' } },
                            { text: 'Modified On', dataIndex: 'modifiedon', xtype: 'datecolumn', format: 'd/m/Y', filter: { type: 'date' }, hidden: true },
                            { text: 'Created By', dataIndex: 'createdbyname', hidden: true },
                            { text: 'Modified By', dataIndex: 'modifiedbyname', hidden: true },
                            {
                                text: 'Status', dataIndex: 'status',
                                renderer: function (value, rec) {
                                    if (value == '0') return 'Pending';
                                    else if (value == '1') return 'Confirmed';
                                    else if (value == '2') return 'PendingPayment';
                                    else if (value == '3') return 'PendingCancel';
                                    else if (value == '4') return 'Cancelled';
                                    else return 'Cancelled';
                                },
                            }
                            /* {
                                text: 'Date',
                                dataIndex: 'bookingon',
                            }, {
                                text: 'ID',
                                dataIndex: 'id'
                            }, {
                                text: 'Order',
                                dataIndex: '',
                            }, {
                                text: 'Price (RM/g)',
                                dataIndex: 'price',
                            }, {
                                text: 'Book By',
                                dataIndex: 'byweight',
                                renderer: function (value, p, record) {
                                    return (value == 1 ? 'Weight' : 'Amount');
                                }
                            }, {
                                text: 'Xau Weight(g)',
                                dataIndex: 'xau',
                            }, {
                                text: 'Amount(RM)',
                                dataIndex: 'amount',
                            }, {
                                text: 'Prod Type',
                                dataIndex: 'productname',
                            }, {
                                text: 'Ace Buy/Sell',
                                dataIndex: 'type',
                                renderer: function (value, p, record) {
                                    if (value == 'CompanyBuy') {
                                        return 'Buy';
                                    } else if (value == 'CompanySell') {
                                        return 'Sell';
                                    } else if (value == 'CompanyBuyBack') {
                                        return 'BuyBack';
                                    } else {
                                        return '';
                                    }
                                }
                            }, {
                                text: 'Status',
                                dataIndex: 'status',
                                renderer: function (value, p, record) {
                                    if (value == '0') return 'Pending';
                                    else if (value == '1') return 'Confirmed';
                                    else if (value == '2') return 'PendingPayment';
                                    else if (value == '3') return 'PendingCancel';
                                    else if (value == '4') return 'Cancelled';
                                    else return 'Cancelled';
                                },
                            } */
                        ],
                        height: 700,
                        width: '100%',
                    }]
                }]
            },
            {
                title: 'Future Order',                
                items: [{
                    xtype: 'container',
                    layout: {
                        type: 'hbox',
                        align: 'stretch',
                    },
                    items: [{
                        xtype: 'grid',
                        store: Ext.create('snap.store.OrderQueue'),
                        columns: [
                            { text: 'ID', dataIndex: 'id', },
                            { text: 'Spot Order No', dataIndex: 'orderno', },
                            { text: 'Partner Name', dataIndex: 'partnername', },
                            { text: 'Partner Code', dataIndex: 'partnercode', },
                            { text: 'Buyer Name', dataIndex: 'buyername', hidden: true, },
                            { text: 'Partner Refferal ID', dataIndex: 'partnerrefid', },
                            { text: 'Order Queue No', dataIndex: 'orderqueueno', },
                            { text: 'Salesperson Name', dataIndex: 'salespersonname', hidden: true, },
                            { text: 'API Version', dataIndex: 'apiversion', },
                            {
                                text: 'Order Type', dataIndex: 'ordertype',
                                renderer: function (value, rec) {
                                    if (value == 'CompanySell') return 'CompanySell';
                                    else if (value == 'CompanyBuy') return 'CompanyBuy';
                                    else return 'CompanyBuyBack';
                                },
                            },
                            { text: 'Queue Type', dataIndex: 'queuetype', hidden: true },
                            { text: 'Expire On', dataIndex: 'expireon', xtype: 'datecolumn', format: 'd/m/Y', filter: { type: 'date' }, },
                            { text: 'Product', dataIndex: 'productname', },
                            { text: 'Price Target', dataIndex: 'pricetarget', },
                            {
                                text: 'By Weight', dataIndex: 'byweight',
                                renderer: function (value, rec) {
                                    if (value == '0') return 'Amount';
                                    else if (value == '1') return 'Weight';
                                    else return 'Unassigned';
                                },
                            },
                            { text: 'XAU', dataIndex: 'xau', },
                            { text: 'Amount', dataIndex: 'amount', },
                            { text: 'Remarks', dataIndex: 'remarks', },
                            { text: 'Cancel On', dataIndex: 'cancelon', xtype: 'datecolumn', format: 'd/m/Y', filter: { type: 'date' }, },
                            { text: 'Cancel By', dataIndex: 'cancelbyname', inputType: 'hidden', hidden: true, },
                            { text: 'Match Price ID', dataIndex: 'matchpriceid', hidden: true, },
                            { text: 'Match On', dataIndex: 'matchon', xtype: 'datecolumn', format: 'd/m/Y', filter: { type: 'date' }, hidden: true, },
                            { text: 'Notify URL', dataIndex: 'notifyurl', },
                            { text: 'Notify Match URL', dataIndex: 'notifymatchurl', hidden: true, },
                            { text: 'Success Notify URL', dataIndex: 'successnotifyurl', hidden: true, },
                            { text: 'Reconciled On', dataIndex: 'reconciledon', xtype: 'datecolumn', format: 'd/m/Y', filter: { type: 'date' }, },
                            { text: 'Reconciled By', dataIndex: 'reconciledbyname', inputType: 'hidden', hidden: true, },
                            { text: 'Created on', dataIndex: 'createdon', xtype: 'datecolumn', format: 'd/m/Y', filter: { type: 'date' }, inputType: 'hidden', },
                            { text: 'Modified on', dataIndex: 'modifiedon', xtype: 'datecolumn', format: 'd/m/Y', filter: { type: 'date' }, inputType: 'hidden', hidden: true, },
                            { text: 'Created by', dataIndex: 'createdbyname', inputType: 'hidden', hidden: true, },
                            { text: 'Modified by', dataIndex: 'modifiedbyname', inputType: 'hidden', hidden: true, },
                            {
                                text: 'System Status', dataIndex: 'status', flex: 2,
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
                        height: 700,
                        width: '100%',
                    }]
                }]
            },
        ],
    }
    ]
});

