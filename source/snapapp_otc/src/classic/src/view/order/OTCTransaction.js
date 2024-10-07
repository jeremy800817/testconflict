Ext.define('snap.view.approval.OTCTransaction', {
    extend:'Ext.panel.Panel',
    xtype: 'transactionview',
    permissionRoot: '/root/' + PROJECTBASE.toLowerCase() + '/order',
    
    scrollable:true,
    items: {
        
        
        //width: 500,
        //height: 400,
        cls: Ext.baseCSSPrefix + 'shadow',
    
        layout: {
            type: 'vbox',
            pack: 'start',
            align: 'stretch'
        },
        scrollable:true,
        bodyPadding: 10,
    
        defaults: {
            frame: true,
            //bodyPadding: 10
        },
        cls: 'otc-main',
        bodyCls: 'otc-main-body',
        items: [
          
            {
                xtype: 'panel',
                title: 'Transaction',
                layout: 'hbox',
                collapsible: true,
                // cls: 'otc-panel',
                // defaults: {
                //   layout: 'vbox',
                //   flex: 1,
                //   bodyPadding: 10
                // },
                margin: "10 0 0 0",
                scrollable:true,
                items: [
                        // ITEM 1
                        {
                            flex: 1,
                            xtype: 'otcorderview',
                            enableFilter: true,
                            partnercode: PROJECTBASE,
                            toolbarItems: [
                              'detail', '|', 'filter', '|',
                              {
                                  xtype: 'datefield', fieldLabel: 'Start', reference: 'startDate', itemId: 'startDate', format: 'd/m/Y', menu: { items: []}, name:'startdateOn', labelWidth:'auto'
                              },
                              {
                                  xtype: 'datefield', fieldLabel: 'End', reference: 'endDate', itemId: 'endDate', format: 'd/m/Y', menu: { items: []}, name:'enddateOn', labelWidth:'auto'
                              },
                              {
                                iconCls: 'x-fa fa-redo-alt', style : "width : 130px;",  text: 'Filter Date', tooltip: 'Filter Date', handler: 'getDateRange', showToolbarItemText: true, labelWidth:'auto'
                              },
                              {
                                  text: 'Download', cls: '', tooltip: 'Download Order',iconCls: 'x-fa fa-download', reference: 'dailytransactionreport', handler: 'getTransactionReport',  showToolbarItemText: true, printType: 'xlsx', // printType: pending
                              },
                              {
                                text: 'Print Receipt', cls: '', tooltip: 'Print Order Receipt', validSelection: 'single', iconCls: 'x-fa fa-print', reference: 'printspotorderotc', handler: 'printSpotOrderOtc',  showToolbarItemText: true, printType: 'xlsx', // printType: pending
                              },
                              {
                                  text: 'Print Order Confirmation', cls: '', tooltip: 'Print Order Confirmation', validSelection: 'single', iconCls: 'x-fa fa-print', reference: 'printAqad', handler: 'printSpotOrderOtc',  showToolbarItemText: true, printType: 'xlsx', // printType: pending
                              },
                            //   {
                            //     text: 'Approve Transaction', cls: '', tooltip: 'Approve Buy/Sell Order',iconCls: 'x-fa fa-check', reference: 'approvetransaction', handler: 'approveTransaction',  showToolbarItemText: true, 
                            //   },
                            ],
                            reference: 'myorder',
                            store: {
                                  type: 'MyOrder', proxy: {
                                      type: 'ajax',
                                      url: 'index.php?hdl=myorder&action=list&partnercode='+PROJECTBASE,
                                      reader: {
                                          type: 'json',
                                          rootProperty: 'records',
                                      }
                                  },
                            },

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
                                { text: 'My Kad / Passport No', dataIndex: 'achmykadno', filter: { type: 'string' }, minWidth: 130,
                                    renderer: function (value, rec, rowrec) {
                                        // console.log(rec,rowrec,'rec')
                                        
                                        rec.style = 'color:#800000'
                                    
                                        return Ext.util.Format.htmlEncode(value)
                                    }, 
                                },
                                { text: 'Gold Account No', dataIndex: 'achcode', filter: { type: 'string' }, minWidth: 130, hidden:true,
                                    renderer: function (value, rec, rowrec) {
                                        // console.log(rec,rowrec,'rec')
                                        
                                        rec.style = 'color:#800000'
                                    
                                        return Ext.util.Format.htmlEncode(value)
                                    }, 
                                },
                                {
                                    text: 'Bank Buy/Sell', dataIndex: 'ordtype',
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
                                    text: 'Ace Price', dataIndex: 'aceprice', exportdecimal:2, filter: { type: 'string' }, align: 'right', minWidth: 80, renderer: Ext.util.Format.numberRenderer('0,000.00'),
                                    editor: {    //field has been deprecated as of 4.0.5
                                        xtype: 'numberfield',
                                        decimalPrecision: 2
                                    }
                                },
                                {
                                    text: 'Original Price', dataIndex: 'ordprice', exportdecimal:2, filter: { type: 'string' }, align: 'right', minWidth: 80, renderer: Ext.util.Format.numberRenderer('0,000.00'),
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
                                    text: 'Final Price', dataIndex: 'originalprice', exportdecimal:2, filter: { type: 'string' },  align: 'right', minWidth: 80, renderer: Ext.util.Format.numberRenderer('0,000.00'),
                                    editor: {    //field has been deprecated as of 4.0.5
                                        xtype: 'numberfield',
                                        decimalPrecision: 2
                                    }
                                },
                                {
                                    text: 'Total Amount (RM)', dataIndex: 'ordamount', exportdecimal:2, filter: { type: 'string' },  align: 'right', minWidth: 80, renderer: Ext.util.Format.numberRenderer('0,000.00'),
                                    editor: {    //field has been deprecated as of 4.0.5
                                        xtype: 'numberfield',
                                        decimalPrecision: 2
                                    }
                                },
                                {
                                    text: 'Commission Amount', dataIndex: 'commision', exportdecimal:2, filter: { type: 'string' },  align: 'right', minWidth: 80, renderer: Ext.util.Format.numberRenderer('0,000.00'),
                                    editor: {    //field has been deprecated as of 4.0.5
                                        xtype: 'numberfield',
                                        decimalPrecision: 2
                                    }
                                },
                                {
                                    text: 'Discount', dataIndex: 'pricedifference', exportdecimal:2, filter: { type: 'string' },  align: 'right', minWidth: 80, renderer: Ext.util.Format.numberRenderer('0,000.00'),
                                    hidden: true,
                                    editor: {    //field has been deprecated as of 4.0.5
                                        xtype: 'numberfield',
                                        decimalPrecision: 2
                                    }
                                },
                                {
                                    text: 'Discount Info', dataIndex: 'discountAmount', hidden: true, filter: { type: 'string' }, align: 'right', minWidth: 100,
                                },
                                {
                                    text: 'Incoming/ Outgoing Payment (RM)', dataIndex: 'dbmpdtverifiedamount', hidden: true, exportdecimal:2, filter: { type: 'string' },  align: 'right', minWidth: 80, renderer: Ext.util.Format.numberRenderer('0,000.00'),
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
                                            ['7','Pending Approval'],
                                            ['8','Rejected']
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
                                        else if (value == '8') return 'Rejected';
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
                                { text: 'Transaction Ref No', dataIndex: 'refno', filter: { type: 'string' }, minWidth: 130,  renderer: 'boldText'  },
                                { text: 'Gateway Ref No', dataIndex: 'dbmpdtgatewayrefno', filter: { type: 'string' }, minWidth: 130 },
                                { text: 'Disbursement Ref No', dataIndex: 'dbmpdtreferenceno', filter: { type: 'string' }, minWidth: 130 },
                                { text: 'Partner', dataIndex: 'ordpartnername', hidden: true, filter: { type: 'string' }, minWidth: 200, },
                                { text: 'Partner Code', dataIndex: 'ordpartnercode', hidden: true, filter: { type: 'string' }, minWidth: 200, },
                                { text: 'Bank Name', dataIndex: 'dbmbankname', hidden: true, filter: { type: 'string' }, minWidth: 130 },
                              
                                
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
                              
                                { text: 'Transaction Date', dataIndex: 'dbmpdtrequestedon', hidden: true, xtype: 'datecolumn', format: 'Y-m-d H:i:s', filter: { type: 'date' }, minWidth: 130 },
                        
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
                                { text: 'Bank Ref No', dataIndex: 'dbmbankrefno', hidden: true, filter: { type: 'string' }, minWidth: 130},
                                { text: 'Campaign Code', dataIndex: 'campaigncode', filter: { type: 'string' }, minWidth: 130, flex: 1 },
                                // { text: 'Account Name', dataIndex: 'dbmaccountname',  hidden: true, filter: { type: 'string' }, minWidth: 130 },
                                // { text: 'Account No', dataIndex: 'dbmaccountnumber',  hidden: true, filter: { type: 'string' }, minWidth: 130,
                                // listeners : {
                                //     afterrender : function(srcCmp) {
                                        
                                //         srcCmp.setText("GIRO/ GIRO i Account No");
                                //     }
                                // }},
                                // { text: 'Bank Code', dataIndex: 'dbmacebankcode', hidden: true, filter: { type: 'string' }, minWidth: 130 },
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
                                { text: 'Sales Person Code', dataIndex: 'salespersoncode', filter: { type: 'string' }, minWidth: 130 },
                                { text: 'Introducer Code', dataIndex: 'extradata', filter: { type: 'string' }, minWidth: 130 },
                                { text: 'Settlement Method', dataIndex: 'settlementmethod', filter: { type: 'string' }, minWidth: 130, hidden: true,  },
                                //{ text: 'Transaction Reference No', dataIndex: 'dbmtransactionrefno', filter: { type: 'string' }, minWidth: 130 },
                                //{ text: 'Requested On', dataIndex: 'dbmpdtrequestedon', xtype: 'datecolumn', format: 'Y-m-d H:i:s', filter: { type: 'date' },  minWidth: 130 },
                                { text: 'Created On', dataIndex: 'createdon', xtype: 'datecolumn', format: 'Y-m-d H:i:s', filter: { type: 'date' }, hidden: true, minWidth: 130 },
                                { text: 'Modified On', dataIndex: 'modifiedon', xtype: 'datecolumn', format: 'Y-m-d H:i:s', filter: { type: 'date' }, hidden: true, minWidth: 130 },
                                { text: 'Created By', dataIndex: 'createdbyname', filter: { type: 'string' }, hidden: true, minWidth: 130 },
                                { text: 'Modified By', dataIndex: 'modifiedbyname', filter: { type: 'string' }, hidden: true, minWidth: 130 },
                                { text: 'Checker', dataIndex: 'checker', filter: { type: 'string' }, minWidth: 130 },
                                { text: 'Remarks', dataIndex: 'remarks', filter: { type: 'string' }, minWidth: 130 },
                                { text: 'Action On', dataIndex: 'actionon', xtype: 'datecolumn', format: 'Y-m-d H:i:s', filter: { type: 'date' }, minWidth: 130 },
								{ text: 'GTP reference', dataIndex: 'gtpreference', filter: { type: 'string' }, minWidth: 150 },
                        
                            
                            ],
                            
                            // Add form
                            formOtcApproval: {
                                formDialogWidth: 950,
                                controller: 'myorder-myorder',
                        
                                formDialogTitle: 'Transaction Approval',
                        
                                // Settings
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
                                formViewModel: {
                        
                                },
                        
                                formPanelItems: [
                                    //1st hbox
                                    {
                                        items: [
                                            { xtype: 'hidden', hidden: true, name: 'id' },
                                            {
                                                itemId: 'user_main_fieldset',
                                                xtype: 'fieldset',
                                                title: 'Main Information',
                                                title: 'Account Holder Details',
                                                layout: 'hbox',
                                                defaultType: 'textfield',
                                                fieldDefaults: {
                                                    anchor: '100%',
                                                    msgTarget: 'side',
                                                    margin: '0 0 5 0',
                                                    width: '100%',
                                                },
                                                items: [
                                                    {
                                                        xtype: 'fieldcontainer',
                                                        fieldLabel: '',
                                                        defaultType: 'textboxfield',
                                                        layout: 'hbox',
                                                        items: [
                                                            {
                                                                xtype: 'displayfield', allowBlank: false, fieldLabel: 'Order No', reference: 'ordorderno', name: 'ordorderno', flex: 5, style: 'padding-left: 20px;', labelWidth: '10%',
                                                            },
                                                            {
                                                                xtype: 'displayfield', allowBlank: false, fieldLabel: 'Total Amount', reference: 'ordamount', name: 'ordamount', flex: 5, style: 'padding-left: 20px;', labelWidth: '10%',
                                                            },
                                                        ]
                                                    }
                                                ]
                                            },
                                            {
                                                xtype: 'fieldset', title: 'Enter Approval Code', collapsible: false,
                                                items: [
                                                    {
                                                        xtype: 'fieldcontainer',
                                                        layout: {
                                                            type: 'hbox',
                                                        },
                                                        items: [
                                                            {
                                                                xtype: 'textfield', fieldLabel: '', name: 'approvalcode', flex: 2, style: 'padding-left: 20px;', id: 'approvalcode'
                                                            },
                                                        ]
                                                    },
                                                ]
                                            },
                                            // {
                                            //     xtype: 'form',
                                            //     reference: 'searchresultsforpep-form',
                                            //     border: false,
                                            //     items: [
                                            //         {
                                            //             title: '',
                                            //             flex: 13,
                                            //             xtype: 'mypepmatchdataview',
                                            //             reference: 'mypepematchdata',
                                            //             enablePagination: false
                        
                                            //         },
                                            //     ],
                                            // },
                                            {
                                                xtype: 'fieldset', title: 'Remarks', collapsible: false,
                                                items: [
                                                    {
                                                        xtype: 'fieldcontainer',
                                                        layout: {
                                                            type: 'hbox',
                                                        },
                                                        items: [
                                                            {
                                                                xtype: 'textarea', fieldLabel: '', name: 'remarks', flex: 2, style: 'padding-left: 20px;', id: 'approvalremarks'
                                                            },
                                                        ]
                                                    },
                                                ]
                                            }
                                        ],
                                    },
                                ],
                        
                               
                            },
                            // Form for approval
                        },
                        // END ITEM 1
                  ]
      
            },
            // End test
            // Conversion container
           
        ]
    },
});
