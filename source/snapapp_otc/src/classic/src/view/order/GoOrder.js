Ext.define('snap.view.order.GoOrder', {
    extend: 'snap.view.order.MyOrder',
    xtype: 'goorderview',
    partnercode: 'GO',
    permissionRoot: '/root/go/goldtransaction',
    controller: 'goorder-goorder',

    store: {
        type: 'MyOrder', proxy: {
            type: 'ajax',
            url: 'index.php?hdl=myorder&action=list&partnercode=GO',
            reader: {
                type: 'json',
                rootProperty: 'records',
            }
        },
    },

    // Custom Columns
    columns: [
       
        { text: 'ID', dataIndex: 'id', filter: { type: 'int' }, inputType: 'hidden',hidden: true},
        { text: 'Booking On', dataIndex: 'ordbookingon', xtype: 'datecolumn', format: 'Y-m-d H:i:s', filter: { type: 'date' }, minWidth: 100, },
       
        { text: 'Customer Name', dataIndex: 'achfullname', filter: { type: 'string' }, minWidth: 130,
            renderer: function (value, rec, rowrec) {
                // console.log(rec,rowrec,'rec')
                
                rec.style = 'color:#00008B'
            
                return Ext.util.Format.htmlEncode(value)
            },  
        },
        { text: 'Customer Code', dataIndex: 'achcode', filter: { type: 'string' }, minWidth: 130, hidden:true,
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
        //     text: 'FP', dataIndex: 'ordfpprice', exportdecimal:2, hidden: true, filter: { type: 'string' }, align: 'right', minWidth: 80, renderer: Ext.util.Format.numberRenderer('0,000.00'),
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
            text: 'Discount', dataIndex: 'orddiscountprice', hidden: true, exportdecimal:2, filter: { type: 'string' },  align: 'right', minWidth: 80, renderer: Ext.util.Format.numberRenderer('0,000.00'),
            editor: {    //field has been deprecated as of 4.0.5
                xtype: 'numberfield',
                decimalPrecision: 2
            }
        },
        {
            text: 'Discount Info', dataIndex: 'ordiscountinfo', hidden: true, filter: { type: 'string' }, align: 'right', minWidth: 100,
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
                    ['6','Refunded']

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
        { text: 'Transaction Ref No', dataIndex: 'refno', filter: { type: 'string' }, minWidth: 130, renderer: 'boldText' },
        { text: 'Gateway Ref No', dataIndex: 'dbmpdtgatewayrefno', filter: { type: 'string' }, minWidth: 130 },
        { text: 'Disbursement Ref No', dataIndex: 'dbmpdtreferenceno', filter: { type: 'string' }, minWidth: 130 },
        { text: 'Partner', dataIndex: 'ordpartnername', hidden: true, filter: { type: 'string' }, minWidth: 200, },
        { text: 'Bank Name', dataIndex: 'dbmbankname', filter: { type: 'string' }, minWidth: 130 },
       
       
        { text: 'Customer NRIC', dataIndex: 'achmykadno', filter: { type: 'string' }, minWidth: 130, renderer: 'boldText' },
        { text: 'Customer Phone', dataIndex: 'achphoneno', filter: { type: 'string' }, minWidth: 130 },
        { text: 'Customer Email', dataIndex: 'achemail', filter: { type: 'string' }, minWidth: 130 },
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
        { text: 'Product', dataIndex: 'ordproductname', hidden: true, filter: { type: 'string' }, minWidth: 130 },
       

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
        { text: 'Campaign Code', dataIndex: 'campaigncode', filter: { type: 'string' }, minWidth: 130, hidden: true,  },
        //{ text: 'Transaction Reference No', dataIndex: 'dbmtransactionrefno', filter: { type: 'string' }, minWidth: 130 },
        //{ text: 'Requested On', dataIndex: 'dbmpdtrequestedon', xtype: 'datecolumn', format: 'Y-m-d H:i:s', filter: { type: 'date' },  minWidth: 130 },
        { text: 'Created On', dataIndex: 'createdon', xtype: 'datecolumn', format: 'Y-m-d H:i:s', filter: { type: 'date' }, hidden: true, minWidth: 130 },
        { text: 'Modified On', dataIndex: 'modifiedon', xtype: 'datecolumn', format: 'Y-m-d H:i:s', filter: { type: 'date' }, hidden: true, minWidth: 130 },
        { text: 'Created By', dataIndex: 'createdbyname', filter: { type: 'string' }, hidden: true, minWidth: 130 },
        { text: 'Modified By', dataIndex: 'modifiedbyname', filter: { type: 'string' }, hidden: true, minWidth: 130 },
    
    ],
});
