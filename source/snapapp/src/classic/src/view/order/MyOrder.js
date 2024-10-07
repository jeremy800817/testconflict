Ext.define('snap.view.order.MyOrder', {
    extend: 'snap.view.order.Order',
    xtype: 'myorderview',

    requires: [

        'snap.store.MyOrder',
        'snap.model.MyOrder',
        'snap.view.order.MyOrderController',
        'snap.view.order.OrderModel',


    ],
    formDialogWidth: 950,
    //permissionRoot: '/root/bmmb/goldtransaction',
    store: { type: 'MyOrder' },
    controller: 'myorder-myorder',
    viewModel: {
        type: 'order-order'
    },
    enableFilter: true,
    toolbarItems: [
        'detail', '|', 'filter', '|',
        {
            xtype: 'datefield', fieldLabel: 'Start', reference: 'startDate', itemId: 'startDate', format: 'd/m/Y', menu: { items: []}, name:'startdateOn', labelWidth:'auto'
        },
        {
            xtype: 'datefield', fieldLabel: 'End', reference: 'endDate', itemId: 'endDate', format: 'd/m/Y', menu: { items: []}, name:'enddateOn', labelWidth:'auto'
        },
        {
            text: 'Download',tooltip: 'Download Order',iconCls: 'x-fa fa-download', reference: 'dailytransactionreport', handler: 'getTransactionReport',  showToolbarItemText: true, printType: 'xlsx', // printType: pending
        },
        {
            text: 'Export Zip', tooltip: 'Export Zip To Email', iconCls: 'x-fa fa-envelope', handler: 'getPrintReportJob',  showToolbarItemText: true, printType: 'xlsx', // printType: pending
        },
        {
            iconCls: 'x-fa fa-redo-alt', text: 'Filter Date', tooltip: 'Filter Date', handler: 'getDateRange', showToolbarItemText: true,
        },
        {
            iconCls: 'x-fa fa-times-circle', text: 'Clear Date', tooltip: 'Clear Date', handler: 'clearDateRange', showToolbarItemText: true,
        },
        {
            iconCls: 'x-fa fa-edit', text: 'Single Update Ref. No.', itemId: 'editRefNoBmmb', tooltip: 'Edit Reference No. for Company Buy', handler: 'editReferenceNo', showToolbarItemText: true,
        },
        {
            reference: 'uploadbulkpaymentresponse',iconCls: 'x-fa fa-edit', text: 'Batch Update Ref. No.', itemId: 'updateRefNo', tooltip: 'Upload Maybank Response', handler: 'uploadbulkpaymentresponse', showToolbarItemText: true,
            listeners : {
                afterrender : function(srcCmp) {
                    Ext.create('Ext.tip.ToolTip', {
                        target : srcCmp.getEl(),
                        html : 'Upload Maybank Response'
                    });
                }
            }
        },
        {
            iconCls: 'x-fa fa-exclamation-circle', text: 'Update Refund Status', itemId: 'editPendingRefundStatus', tooltip: 'Update Pending Refund Status For Order', handler: 'editPendingRefundStatus', showToolbarItemText: true,
        },
        
        /*
        {
            text: 'Print DGV Sell',tooltip: 'Print Daily Transaction Report for Sell',iconCls: 'x-fa fa-print', reference: 'dailytransactionsell', handler: 'getTransactionReport',  showToolbarItemText: true, printType: 'xlsx', // printType: pending
        },
        {
            text: 'Print DGV Buy',tooltip: 'Print Daily Transaction Report for Buy',iconCls: 'x-fa fa-print', reference: 'dailytransactionbuy',  handler: 'getTransactionReport',  showToolbarItemText: true, printType: 'xlsx', // printType: pending
        }*/
    ], 
    viewConfig: {
        getRowClass: function (record) {           
            record.data.price = parseFloat(record.data.price).toFixed(3);
            record.data.byweight=record.data.byweight=='1'?'Yes':'';
            record.data.amount = parseFloat(record.data.amount).toFixed(3);
        },            
    },

    isIdentical(array) {
        for(var i = 0; i < array.length - 1; i++) {
            if(array[i] !== array[i+1]) {
                return false;
            }
        }
        return true;
    },    

    // Custom Columns
    columns: [
       
        { text: 'ID', dataIndex: 'id', filter: { type: 'int' }, inputType: 'hidden',hidden: true},
        { text: 'Booking On', dataIndex: 'ordbookingon', xtype: 'datecolumn', format: 'Y-m-d H:i:s', filter: { type: 'date' }, minWidth: 100, },
       
        { text: 'Customer Name', dataIndex: 'dbmpdtaccountholdername', filter: { type: 'string' }, minWidth: 130,
            renderer: function (value, rec, rowrec) {
                // console.log(rec,rowrec,'rec')
                
                rec.style = 'color:#00008B'
            
                return Ext.util.Format.htmlEncode(value)
            }, 
        },
        { text: 'Customer Code', dataIndex: 'dbmpdtaccountholdercode', filter: { type: 'string' }, minWidth: 130, hidden:true,
            renderer: function (value, rec, rowrec) {
                // console.log(rec,rowrec,'rec')
                
                rec.style = 'color:#800000'
            
                return Ext.util.Format.htmlEncode(value)
            }, 
        },
        {
            text: 'Ace Buy/Sell', dataIndex: 'ordtype',
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
        { 
            text: 'Xau Weight (g)', dataIndex: 'ordxau', exportdecimal:3, filter: { type: 'string' },  align: 'right', minWidth: 130, renderer: Ext.util.Format.numberRenderer('0,000.000'),
            editor: {    //field has been deprecated as of 4.0.5
                xtype: 'numberfield',
                decimalPrecision: 3
            } 
        },
        {
            text: 'FP', dataIndex: 'ordprice', exportdecimal:2, filter: { type: 'string' }, align: 'right', minWidth: 80, renderer: Ext.util.Format.numberRenderer('0,000.00'),
            editor: {    //field has been deprecated as of 4.0.5
                xtype: 'numberfield',
                decimalPrecision: 2
            }
        },
        // {
        //     text: 'P2 Price', dataIndex: 'ordbookingprice', exportdecimal:2, filter: { type: 'string' }, align: 'right', minWidth: 80, renderer: Ext.util.Format.numberRenderer('0,000.00'),
        //     editor: {
        //         xtype: 'numberfield',
        //         decimalPrecision: 2
        //     }
        // },
        // {
        //     text: 'FP', dataIndex: 'ordfpprice',  hidden: true, exportdecimal:2, filter: { type: 'string' }, align: 'right', minWidth: 80, renderer: Ext.util.Format.numberRenderer('0,000.00'),
        //     editor: {    //field has been deprecated as of 4.0.5
        //         xtype: 'numberfield',
        //         decimalPrecision: 2
        //     }
        // },
        
        {
            text: 'Total Amount (RM)', dataIndex: 'ordamount', exportdecimal:2, filter: { type: 'string' },  align: 'right', minWidth: 80, renderer: Ext.util.Format.numberRenderer('0,000.00'),
            editor: {    //field has been deprecated as of 4.0.5
                xtype: 'numberfield',
                decimalPrecision: 2
            }
        },
        {
            text: 'Discount', dataIndex: 'orddiscountprice', exportdecimal:2, filter: { type: 'string' },  align: 'right', minWidth: 80, renderer: Ext.util.Format.numberRenderer('0,000.00'),
            hidden: true,
            editor: {    //field has been deprecated as of 4.0.5
                xtype: 'numberfield',
                decimalPrecision: 2
            }
        },
        {
            text: 'Discount Info', dataIndex: 'orddiscountinfo', hidden: true, filter: { type: 'string' }, align: 'right', minWidth: 100,
        },
        {
            text: 'Incoming/ Outgoing Payment (RM)', dataIndex: 'dbmpdtverifiedamount', exportdecimal:2, filter: { type: 'string' },  align: 'right', minWidth: 80, renderer: Ext.util.Format.numberRenderer('0,000.00'),
            editor: {    //field has been deprecated as of 4.0.5
                xtype: 'numberfield',
                decimalPrecision: 2
            }
        },
        {
            text: 'Status', dataIndex: 'status', minWidth: 130,

            filter: {
                type: 'combo',
                store: [
                    ['0', 'Pending Payment'],
                    ['1', 'Confirmed'],
                    ['2', 'Paid'],
                    ['3', 'Failed'],
                    ['4', 'Reversed'],
                    ['5', 'Pending Refund'],
                    ['6','Refunded'],
                    ['7','Pending Approval']

                ],

            },
            renderer: function (value, rec) {
                if (value == '0') return 'Pending Payment';
                else if (value == '1') return 'Confirmed';
                else if (value == '2') return 'Paid';
                else if (value == '3') return 'Failed';
                else if (value == '4') return 'Reversed';
                else if (value == '5') return 'Pending Refund';
                else if (value == '6') return 'Refunded';
                else if (value == '7') return 'Pending Approval';
                else return 'Unspecified';
            },
        },
        { text: 'Settlement Method', dataIndex: 'settlementmethod', filter: { type: 'string' }, minWidth: 130,  },
        {
            text: 'Processing Fee (RM)', dataIndex: 'ordfee', exportdecimal:2, filter: { type: 'string' },  align: 'right', minWidth: 80, renderer: Ext.util.Format.numberRenderer('0,000.00'),
            editor: {    //field has been deprecated as of 4.0.5
                xtype: 'numberfield',
                decimalPrecision: 2
            }
        },
        { text: 'Referral Affiliate Code', dataIndex: 'referralbranchcode', filter: { type: 'string' }, hidden: true, minWidth: 130, flex: 1 },
        { text: 'Referral Affiliate Name', dataIndex: 'referralbranchname', filter: { type: 'string' }, hidden: true, minWidth: 130, flex: 1 },
        
        { text: 'Transaction Ref No', dataIndex: 'refno', filter: { type: 'string' }, minWidth: 130,  renderer: 'boldText'  },
        { text: 'Gateway Ref No', dataIndex: 'dbmpdtgatewayrefno', filter: { type: 'string' }, minWidth: 130 },
        { text: 'Disbursement Ref No', dataIndex: 'dbmpdtreferenceno', filter: { type: 'string' }, minWidth: 130 },
        { text: 'Partner', dataIndex: 'ordpartnername', hidden: true, filter: { type: 'string' }, minWidth: 200, },
        { text: 'Bank Name', dataIndex: 'dbmbankname', filter: { type: 'string' }, minWidth: 130 },
      
        
        //{ text: 'Buyer',  dataIndex: 'buyername', filter: {type: 'string'}, flex: 1, hidden: true },
       
        { text: 'Order Buyer Id', dataIndex: 'ordbuyerid', filter: { type: 'int' }, inputType: 'hidden', hidden: true},
        { text: 'Order Cancel On', dataIndex: 'ordcancelon', xtype: 'datecolumn', format: 'Y-m-d H:i:s', filter: { type: 'date' }, minWidth: 100, hidden: true },
        { text: 'Order Confirm On', dataIndex: 'ordconfirmon', xtype: 'datecolumn', format: 'Y-m-d H:i:s', filter: { type: 'date' }, minWidth: 100, hidden: true},
        // { text: 'Order ID', dataIndex: 'orderid', filter: { type: 'int' }, inputType: 'hidden', hidden: true},
        { text: 'Order No', dataIndex: 'ordorderno', filter: { type: 'string' }, minWidth: 130,
            renderer: function (value, rec, rowrec) {
                // console.log(rec,rowrec,'rec')
                if (rowrec.data.ordtype == 'CompanySell'){
                    rec.style = 'color:#209474'
                }
                if (rowrec.data.ordtype == 'CompanyBuy'){
                    rec.style = 'color:#d07b32'
                }
                return Ext.util.Format.htmlEncode(value)
            }, 
        },
      
        /*{
            text: 'Order Fee', dataIndex: 'ordfee', exportdecimal:2, filter: { type: 'string' }, align: 'right', hidden : true, minWidth: 80, renderer: Ext.util.Format.numberRenderer('0,000.000'),
            editor: {    //field has been deprecated as of 4.0.5
                xtype: 'numberfield',
                decimalPrecision: 3
            }
        },*/
        { text: 'Product', dataIndex: 'ordproductname', hidden: true,  filter: { type: 'string' }, minWidth: 130 },
       

        { text: 'Order Partner ID', dataIndex: 'ordpartnerid', filter: { type: 'int' }, inputType: 'hidden',hidden: true},
        
        { text: 'Order Remarks', dataIndex: 'ordremarks', filter: { type: 'string' }, minWidth: 130, hidden: true },
        
        
    
        /*
        {
            text: 'Original Amount (RM)', dataIndex: 'originalamount', exportdecimal:2, filter: { type: 'string' },  align: 'right', minWidth: 80, renderer: Ext.util.Format.numberRenderer('0,000.000'),
            editor: {    //field has been deprecated as of 4.0.5
                xtype: 'numberfield',
                decimalPrecision: 3
            }
        },*/
        {
            text: 'Payment Amount (RM)', hidden: true, dataIndex: 'pdtamount', exportdecimal:2, filter: { type: 'string' },  align: 'right', minWidth: 80, renderer: Ext.util.Format.numberRenderer('0,000.00'),
            editor: {    //field has been deprecated as of 4.0.5
                xtype: 'numberfield',
                decimalPrecision: 2
            }
        },
        /*
        {
            text: 'Customer Fee (RM)', hidden: true, dataIndex: 'pdtcustomerfee', exportdecimal:2, filter: { type: 'string' },  align: 'right', minWidth: 80, renderer: Ext.util.Format.numberRenderer('0,000.000'),
            editor: {    //field has been deprecated as of 4.0.5
                xtype: 'numberfield',
                decimalPrecision: 3
            }
        },*/
        { text: 'Payment Failed On', hidden: true, dataIndex: 'pdtfailedon', xtype: 'datecolumn', format: 'Y-m-d H:i:s', filter: { type: 'date' }, hidden: true, minWidth: 130 },
        /* {
            text: 'Payment Gateway Fee (RM)', dataIndex: 'pdtgatewayfee', exportdecimal:2, filter: { type: 'string' },  align: 'right', minWidth: 80, renderer: Ext.util.Format.numberRenderer('0,000.000'),
            editor: {    //field has been deprecated as of 4.0.5
                xtype: 'numberfield',
                decimalPrecision: 3
            }
        }, */
      
        { text: 'Transaction Date', dataIndex: 'dbmpdtrequestedon', xtype: 'datecolumn', format: 'Y-m-d H:i:s', filter: { type: 'date' }, minWidth: 130 },

        { text: 'Payment Location', hidden: true, dataIndex: 'pdtlocation', filter: { type: 'string' }, minWidth: 130 },
        //{ text: 'Payment Merchant Ref No', dataIndex: 'pdtpaymentrefno', filter: { type: 'string' }, minWidth: 130 },
      

        { text: 'Payment Refunded On', dataIndex: 'pdtrefundedon',  hidden: true, xtype: 'datecolumn', format: 'Y-m-d H:i:s', filter: { type: 'date' }, hidden: true, minWidth: 130 },
        //{ text: 'Payment Requested On', dataIndex: 'pdtrequestedon', xtype: 'datecolumn', format: 'Y-m-d H:i:s', filter: { type: 'date' }, minWidth: 130 },
      
        { text: 'Payment Signed Data', dataIndex: 'pdtsigneddata',  hidden: true, filter: { type: 'string' }, minWidth: 130 },
        { text: 'Payment Source Ref No', dataIndex: 'pdtsourcerefno', hidden: true, filter: { type: 'string' }, minWidth: 130 },
        /*
        {
            text: 'Payment Status', dataIndex: 'pdtstatus',
            filter: {
                type: 'combo',
                store: [
                    ['0', 'Pending'],
                    ['1', 'Success'],
                    ['2', 'Pending Payment'],
                    ['3', 'Cancelled'],
                    ['4', 'Failed'],
                    ['5', 'Refunded'],
                ],
                renderer: function (value, rec) {
                    if (value == '0') return 'Pending';
                    else if (value == '1') return 'Success';
                    else if (value == '2') return 'Pending Payment';
                    else if (value == '3') return 'Cancelled';
                    else if (value == '4') return 'Failed';
                    else if (value == '5') return 'Refunded';
                    else return 'Unidentified';
                },
            },

        },*/

        { text: 'Payment Success On', dataIndex: 'pdtsuccesson',  hidden: true, xtype: 'datecolumn', format: 'Y-m-d H:i:s', filter: { type: 'date' }, hidden: true, minWidth: 130 },
        { text: 'Payment Token', dataIndex: 'pdttoken', filter: { type: 'string' }, hidden: true, minWidth: 130 },
        { text: 'Payment Transaction Date', dataIndex: 'pdttransactiondate', hidden: true, xtype: 'datecolumn', format: 'Y-m-d H:i:s', filter: { type: 'date' }, minWidth: 130 },
        {
            text: 'Disbursement Amount (RM)', dataIndex: 'dbmamount', hidden: true, exportdecimal:2, filter: { type: 'string' },  align: 'right', minWidth: 80, renderer: Ext.util.Format.numberRenderer('0,000.00'),
            editor: {    //field has been deprecated as of 4.0.5
                xtype: 'numberfield',
                decimalPrecision: 2
            }
        },
       
      
        // { text: 'Bank ID', dataIndex: 'dbmbankid', filter: { type: 'int' }, inputType: 'hidden',hidden: true},
        { text: 'Bank Ref No', dataIndex: 'dbmbankrefno', filter: { type: 'string' }, minWidth: 130},
        { text: 'Campaign Code', dataIndex: 'campaigncode', filter: { type: 'string' }, minWidth: 130, flex: 1 },
        { text: 'Account Name', dataIndex: 'dbmaccountname',  hidden: true, filter: { type: 'string' }, minWidth: 130 },
        { text: 'Account No', dataIndex: 'dbmaccountnumber',  hidden: true, filter: { type: 'string' }, minWidth: 130 },
        { text: 'Ace Bank Code', dataIndex: 'dbmacebankcode', hidden: true, filter: { type: 'string' }, minWidth: 130 },
        /*{
            text: 'Disbursement Fee (RM)', dataIndex: 'dbmfee', exportdecimal:2, filter: { type: 'string' },  align: 'right', minWidth: 80, renderer: Ext.util.Format.numberRenderer('0,000.000'),
            editor: {    //field has been deprecated as of 4.0.5
                xtype: 'numberfield',
                decimalPrecision: 3
            }
        },*/
        // { text: 'Account Holder ID', dataIndex: 'dbmaccountholderid', filter: { type: 'int' }, inputType: 'hidden', hidden: true},
        // { text: 'Merchant Ref No', dataIndex: 'dbmrefno', filter: { type: 'string' }, minWidth: 130 },
        { text: 'Completed On', dataIndex: 'completedon', xtype: 'datecolumn', format: 'Y-m-d H:i:s', filter: { type: 'date' },  minWidth: 100, },
        { text: 'Failed On', dataIndex: 'failedon', xtype: 'datecolumn', format: 'Y-m-d H:i:s', filter: { type: 'date' }, hidden: true,  minWidth: 100, },
        { text: 'Reversed On', dataIndex: 'reversedon', xtype: 'datecolumn', format: 'Y-m-d H:i:s', filter: { type: 'date' }, hidden: true,  minWidth: 130 },
        //{ text: 'Sales Person Code', dataIndex: 'salespersoncode', filter: { type: 'string' }, minWidth: 130 },
        { text: 'Settlement Method', dataIndex: 'settlementmethod', filter: { type: 'string' }, minWidth: 130, hidden: true,  },
        //{ text: 'Transaction Reference No', dataIndex: 'dbmtransactionrefno', filter: { type: 'string' }, minWidth: 130 },
        //{ text: 'Requested On', dataIndex: 'dbmpdtrequestedon', xtype: 'datecolumn', format: 'Y-m-d H:i:s', filter: { type: 'date' },  minWidth: 130 },
        { text: 'Created On', dataIndex: 'createdon', xtype: 'datecolumn', format: 'Y-m-d H:i:s', filter: { type: 'date' }, hidden: true, minWidth: 130 },
        { text: 'Modified On', dataIndex: 'modifiedon', xtype: 'datecolumn', format: 'Y-m-d H:i:s', filter: { type: 'date' }, hidden: true, minWidth: 130 },
        { text: 'Created By', dataIndex: 'createdbyname', filter: { type: 'string' }, hidden: true, minWidth: 130 },
        { text: 'Modified By', dataIndex: 'modifiedbyname', filter: { type: 'string' }, hidden: true, minWidth: 130 },
    
    ],
    
    uploadbulkpaymentresponseform: {
        controller: 'myorder-myorder',
        formDialogWidth: 600,
        formDialogHeight: 200,
        formDialogTitle: "Bulk Payment Response file",
        // Settings
        enableFormDialogClosable: false,
        formPanelDefaults: {
            border: false,
            xtype: "panel",
            flex: 1,
            layout: "anchor",
            msgTarget: "side",
            margins: "0 0 10 10",
        },
        enableFormPanelFrame: false,
        formPanelLayout: "hbox",
        formViewModel: {},
        formPanelItems: [
            //1st hbox
            {
                xtype: "form",
                reference: "bpaymentlist-form",
                items: [
                    {
                        xtype: 'container',
                        layout: 'hbox',
                        items: [
                            {
                                flex:1,
                                xtype: 'displayfield',
                                value : "Upload bulk payment response file (.txt) format",
                                margin: '0 0 0 20',
                                forceSelection: true,
                                enforceMaxLength: true,
                                readOnly : true,
                            },
                            { xtype: 'panel', flex : 1},
                        ]
                    },
                    {
                        xtype: 'container',
                        layout: 'hbox',
                        items: [
                            { xtype: 'filefield',fieldLabel: 'File (Required)', name: 'bpaymentlist', width: '90%', flex: 4, allowBlank: false, reference: 'bpaymentlist_field' },
                        ]
                    },
                ],
                // Input listeners here if any
            },
            {
                xtype: "panel",
                flex: 0,
                width: 10,
                items: [],
            }, //padding hbox
            //2nd hbox
        ],
    },




});
