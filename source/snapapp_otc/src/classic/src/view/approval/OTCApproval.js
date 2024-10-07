Ext.define('snap.view.approval.OTCApproval', {
    extend:'Ext.panel.Panel',
    xtype: 'approvalview',
    permissionRoot: '/root/' + PROJECTBASE.toLowerCase() + '/goldtransaction',
    
    scrollable:true,
    // initComponent: function(formView, form, record, asyncLoadCallback){
    //     elmnt = this;
    //     vm = this.getViewModel();

    //     // Ext.create('snap.store.OrderPriceStream');
    //     async function getList(){

    //         return true
    //     }
    //     getList().then(
    //         function(data){
    //             //elmnt.loadFormSeq(data.return)
    //         }
    //     )

    //     this.onHashTagId('INTLX.GTP_T1', PROJECTBASE + "_CHANNEL");

    //     this.callParent(arguments);
    // },
    data: {
        isAdmin: this,
    },
    initComponent: function(formView, form, record, asyncLoadCallback){
        elmnt = this;
        vm = this.getViewModel();

        // Ext.create('snap.store.OrderPriceStream');
        async function getList(){
            return true
        }
        getList().then(
            function(data){
                //elmnt.loadFormSeq(data.return)
            }
        )
        this.callParent(arguments);
        this.onHashTagId(elmnt.hashTagId);
    },
    onHashTagId: function(hashTagId) {
    //   alert('aaaaa');
      viewPointer = elmnt.down('myorderview');
      //   var trxid = 7083;
    
      var trxid = hashTagId;
      if(trxid){
            viewPointer.getStore().on('load', async function() {
                try {
                    const controller = await viewPointer.getController();
                    await controller._doApproveTransaction(viewPointer, trxid);
                } catch (err) {
                    console.error(err);
                }
            }, this, { single: true });
            
            viewPointer.getStore().reload();
      }
    },
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
                title: 'Orders To Be Approved',
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
                            xtype: 'myorderview',
                            enableFilter: true,
                            controller: 'myorder-myorder',
                            toolbarItems: [
                                'detail', '|', 'filter', '|',
                                {
                                    xtype: 'datefield', fieldLabel: 'Start', reference: 'startDate', itemId: 'startDate', format: 'd/m/Y', menu: { items: []}, name:'startdateOn', labelWidth:'auto'
                                },
                                {
                                    xtype: 'datefield', fieldLabel: 'End', reference: 'endDate', itemId: 'endDate', format: 'd/m/Y', menu: { items: []}, name:'enddateOn', labelWidth:'auto'
                                },
                                {
                                    text: 'Download', cls: '', tooltip: 'Download Order',iconCls: 'x-fa fa-download', reference: 'dailytransactionreport', handler: 'getTransactionReport',  showToolbarItemText: true, printType: 'xlsx', // printType: pending
                                },
                                {
                                    text: 'Approve Transaction', cls: '', tooltip: 'Approve Buy/Sell Order',iconCls: 'x-fa fa-check', reference: 'approvetransaction', handler: 'approveTransaction',  showToolbarItemText: true, 
                                },
                            ],

                            reference: 'myorder_'+PROJECTBASE,
                            store: {
                                type: 'MyOrder', 
                                proxy: {
                                    type: 'ajax',
                                    url: 'index.php?hdl=myorder&action=list&partnercode='+PROJECTBASE+'&filter=approval',
                                    reader: {
                                        type: 'json',
                                        rootProperty: 'records',
                                    }
                                },

                                filters: [
                                    // {
                                    //     property: 'ordamount',
                                    //     value: 10000,
                                    //     operator: '>'
                                    // },
                                    // {
                                    //     property: 'status',
                                    //     value: 7,
                                    //     operator: '='
                                    // }
                                
                                ],
                                //for testing when onload
                                // listeners:{
                                //     load: async function(store, records, successful,operation, options) {
                                //         viewPointer = elmnt.down('myorderview');
     
                                //         var trxid = 7038;
                                //         if(trxid){
                                //             try {
                                //                 controller = await viewPointer.getController();
                                //                 await viewPointer.getStore().reload();
                                //                 await controller._doApproveTransaction(viewPointer, trxid);
                                //             } catch (err) {
                                //                 console.error(err);
                                //             }
                                          
                                //         }
                                        
                                //         //this.itemId = this.up().up().partnercode + 'sendToLogistics';
                                //     }
                                // }
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
                                { text: 'Customer Code', dataIndex: 'achcode', filter: { type: 'string' }, minWidth: 130, hidden:true,
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
                                // { text: 'Gateway Ref No', dataIndex: 'dbmpdtgatewayrefno', filter: { type: 'string' }, minWidth: 130 },
                                { text: 'Disbursement Ref No', dataIndex: 'dbmpdtreferenceno', filter: { type: 'string' }, minWidth: 130 },
                                { text: 'Partner', dataIndex: 'ordpartnername', hidden: true, filter: { type: 'string' }, minWidth: 200, },
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
                                // { text: 'Account No', dataIndex: 'dbmaccountnumber',  hidden: true, filter: { type: 'string' }, minWidth: 130 },
                                // { text: 'Ace Bank Code', dataIndex: 'dbmacebankcode', hidden: true, filter: { type: 'string' }, minWidth: 130 },
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
                                { text: 'Checker', dataIndex: 'checker', filter: { type: 'string' }, minWidth: 130 },
                                { text: 'Remarks', dataIndex: 'remarks', filter: { type: 'string' }, minWidth: 130 },
                                { text: 'Action On', dataIndex: 'actionon', xtype: 'datecolumn', format: 'Y-m-d H:i:s', filter: { type: 'date' }, minWidth: 130 },
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
                                                xtype: 'fieldset', 
                                                title: 'Enter Approval Code', 
                                                collapsible: false,
                                                id: 'approvalfieldset',
                                                items: [
                                                    {
                                                        xtype: 'fieldcontainer',
                                                        layout: {
                                                            type: 'hbox',
                                                        },
                                                        items: [
                                                            {
                                                                xtype: 'textfield', fieldLabel: '', name: 'approvalcode', id:'approvalcode', flex: 2, style: 'padding-left: 20px;', maxLength :10, enforceMaxLength: true,
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
            {
                xtype: 'panel',
                title: 'Gold Transfer Transactions To Be Approved',
                layout: 'hbox',
                collapsible: true,
                margin: "10 0 0 0",
                scrollable:true,
                items: [
                        // ITEM 1
                        {
                            flex: 1,
                            xtype: 'mytransfergoldview_BSN',
                            enableFilter: true,
                            controller: 'mytransfergold-mytransfergold',
                            toolbarItems: [
                                'detail', '|', 'filter', '|',
                                {
                                    xtype: 'datefield', fieldLabel: 'Start', reference: 'startDate', itemId: 'startDate', format: 'd/m/Y', menu: { items: []}, name:'startdateOn', labelWidth:'auto'
                                },
                                {
                                    xtype: 'datefield', fieldLabel: 'End', reference: 'endDate', itemId: 'endDate', format: 'd/m/Y', menu: { items: []}, name:'enddateOn', labelWidth:'auto'
                                },
                                // {
                                //     text: 'Download', cls: '', tooltip: 'Download Order',iconCls: 'x-fa fa-download', reference: 'dailytransactionreport', handler: 'getTransactionReport',  showToolbarItemText: true, printType: 'xlsx', // printType: pending
                                // },
                                {
                                    text: 'Approve Transfer', cls: '', tooltip: 'Approve Transfer Gold',iconCls: 'x-fa fa-check', reference: 'approvetransfertransaction', handler: 'approvetransfertransaction',  showToolbarItemText: true, 
                                },
                            ],

                            reference: 'mytransfergold_'+PROJECTBASE,
                            store: {
                                type: 'MyTransferGold', 
                                proxy: {
                                    type: 'ajax',
                                    url: 'index.php?hdl=mytransfergold&action=list&partnercode='+PROJECTBASE+'&filter=approval',
                                    reader: {
                                        type: 'json',
                                        rootProperty: 'records',
                                    }
                                },
                                filters: [],
                            },
                            // Add form
                            formOtcTransferApproval: {
                                formDialogWidth: 950,
                                controller: 'mytransfergold-mytransfergold',
                        
                                formDialogTitle: 'Transfer Approval',
                        
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
                                            { xtype: 'hidden', hidden: true, name: 'transferid', reference:'transferid' },
                                            {
                                                itemId: 'user_main_fieldset',
                                                xtype: 'fieldset',
                                                title: 'Main Information',
                                                title: 'Transfer Gold Details',
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
                                                        layout: 'vbox',
                                                        items: [
                                                            {
                                                                xtype: 'fieldcontainer',
                                                                layout: 'hbox',
                                                                items: [
                                                                    {
                                                                        xtype: 'displayfield', allowBlank: false, fieldLabel: 'From', reference: 'fromFullname', name: 'fromFullname', flex: 5, style: 'padding-left: 20px;', labelWidth: '10%',
                                                                    },
                                                                    {
                                                                        xtype: 'displayfield', allowBlank: false, fieldLabel: 'To', reference: 'toFullname', name: 'toFullname', flex: 5, style: 'padding-left: 20px;', labelWidth: '10%',
                                                                    },
                                                                ]
                                                            },
                                                            {
                                                                xtype: 'fieldcontainer',
                                                                layout: 'hbox',
                                                                items: [
                                                                    {
                                                                        xtype: 'displayfield', allowBlank: false, fieldLabel: 'Transfer Reference No', reference: 'transferreferenceno', name: 'transferreferenceno', flex: 5, style: 'padding-left: 20px;', labelWidth: '10%',
                                                                    },
                                                                    {
                                                                        xtype: 'displayfield', allowBlank: false, fieldLabel: 'Total Amount (g)', reference: 'transferamount', name: 'transferamount', flex: 5, style: 'padding-left: 20px;', labelWidth: '10%',
                                                                    },
                                                                ]
                                                            },
                                                        ]
                                                    }
                                                ]
                                            },
                                            {
                                                xtype: 'fieldset', 
                                                title: 'Enter Approval Code', 
                                                collapsible: false,
                                                id: 'approvaltransferfieldset',
                                                items: [
                                                    {
                                                        xtype: 'fieldcontainer',
                                                        layout: {
                                                            type: 'hbox',
                                                        },
                                                        items: [
                                                            {
                                                                xtype: 'textfield', fieldLabel: '', name: 'transferapprovalcode', id:'transferapprovalcode', flex: 2, style: 'padding-left: 20px;', maxLength :10, enforceMaxLength: true,
                                                            },
                                                        ]
                                                    },
                                                ]
                                            },
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
                                                                xtype: 'textarea', fieldLabel: '', name: 'transferremarks', flex: 2, style: 'padding-left: 20px;', id: 'transferremarks'
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
            {
                xtype: 'panel',
                title: 'Request To Be Approved',
                layout: 'hbox',
                hidden: PROJECTBASE != 'ALRAJHI', // Hide the component if PROJECTBASE is not 'ALRAJHI'
                collapsible: true,
                margin: "10 0 0 0",
                scrollable: true,
                items: [

                    {
                        flex: 1,
                        xtype: 'otcregisterremarks',
                        enableFilter: true,
                        autoLoad: true,
                        controller: 'otcregisterremarks-otcregisterremarks',
                        toolbarItems: [
                           'detail', '|', 'filter',
                            {
                                xtype: 'datefield',
                                fieldLabel: 'Start',
                                reference: 'startDate',
                                itemId: 'startDate',
                                format: 'd/m/Y',
                                menu: {
                                    items: []
                                },
                                name: 'startdateOn',
                                labelWidth: 'auto'
                            },
                            {
                                xtype: 'datefield',
                                fieldLabel: 'End',
                                reference: 'endDate',
                                itemId: 'endDate',
                                format: 'd/m/Y',
                                menu: {
                                    items: []
                                },
                                name: 'enddateOn',
                                labelWidth: 'auto'
                            },
                            {
                                text: 'Approve Transfer',
                                cls: '',
                                tooltip: 'Approve Transfer Gold',
                                iconCls: 'x-fa fa-check',
                                reference: 'approveregisterfertransaction',
                                handler: 'approveregisterfertransaction',
                                showToolbarItemText: true,
                            },
                        ],

                        reference: 'otcregisterremarks_' + PROJECTBASE,
                        store: {
                            type: 'RegisterRemarks',
                            proxy: {
                                type: 'ajax',
                                url: 'index.php?hdl=otcregisterremarks&action=list&partnercode=' + PROJECTBASE + '&filter=approval',
                                reader: {
                                    type: 'json',
                                    rootProperty: 'records',
                                }
                            },
                            sorters: [
                                {
                                  property: 'createdon',
                                  direction: 'DESC'
                                }
                              ],
                            filters: [],
                        },

                        formOtcTransferApproval: {
                            formDialogWidth: 950,
                            controller: 'otcregisterremarks-otcregisterremarks',

                            formDialogTitle: 'Request for Approval',

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

                                {
                                    items: [{
                                            xtype: 'hidden',
                                            hidden: true,
                                            name: 'transferid',
                                            reference: 'transferid'
                                        },
                                        {
                                            itemId: 'user_main_fieldset',
                                            xtype: 'fieldset',
                                            title: 'Main Information',
                                            title: 'Approval Request Details',
                                            layout: 'hbox',
                                            defaultType: 'textfield',
                                            fieldDefaults: {
                                                anchor: '100%',
                                                msgTarget: 'side',
                                                margin: '0 0 5 0',
                                                width: '100%',
                                            },
                                            items: [{
                                                xtype: 'fieldcontainer',
                                                fieldLabel: '',
                                                defaultType: 'textboxfield',
                                                layout: 'vbox',
                                                items: [{
                                                        xtype: 'fieldcontainer',
                                                        layout: 'hbox',
                                                        items: [{
                                                                xtype: 'displayfield',
                                                                allowBlank: false,
                                                                fieldLabel: 'Identity No.',
                                                                reference: 'identityno',
                                                                name: 'IdentityNumber',
                                                                flex: 2,
                                                                style: 'padding-left: 20px;',
                                                                labelWidth: '10%',
                                                            },
                                                        ]
                                                    },
                                                    {
                                                        xtype: 'fieldcontainer',
                                                        layout: 'hbox',
                                                        items: [{
                                                                xtype: 'displayfield',
                                                                allowBlank: false,
                                                                fieldLabel: 'Reason Failed',
                                                                reference: 'registerremarks',
                                                                name: 'RegisterRemarksFailed',
                                                                flex: 2,
                                                                style: 'padding-left: 20px;',
                                                                labelWidth: '10%',
                                                            },
                                                        ]
                                                    },
                                                ]
                                            }]
                                        },
                                        {
                                            xtype: 'fieldset',
                                            title: 'Remarks',
                                            collapsible: false,
                                            items: [{
                                                xtype: 'fieldcontainer',
                                                layout: {
                                                    type: 'hbox',
                                                },
                                                items: [{
                                                    xtype: 'textarea',
                                                    fieldLabel: '',
                                                    name: 'approvalremarks',
                                                    flex: 2,
                                                    style: 'padding-left: 20px;',
                                                    id: 'approvalremarks'
                                                }, ]
                                            }, ]
                                        }
                                    ],
                                },
                            ],

                        },
                    },
                ]
            },
            // End test
            // Conversion container
			{
				xtype: 'panel',
				title: 'Management Fee To Be Approved',
				layout: 'hbox',
				collapsible: true,
				margin: "10 0 0 0",
				scrollable: true,
				defaultPageSize: 10,
				items: [{
					flex: 1,
					xtype: 'otcmanagementfeeview',
					store: {
						type: 'OtcManagementFee', 
						proxy: {
							type: 'ajax',
							url: 'index.php?hdl=otcmanagementfee&action=list&partnercode='+PROJECTBASE+'&filter=approval',
							reader: {
								type: 'json',
								rootProperty: 'records',
							}
						},

						filters: [
						],
					},
					formAddManagementFee: {
						formDialogWidth: 950,
						controller: 'otcmanagementfee-otcmanagementfee',
						formDialogTitle: 'Add Management Fee Approval',
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
							{
								items: [,
									{
										itemId: 'user_main_fieldset',
										xtype: 'fieldset',
										title: 'Main Information',
										title: 'Management Fee Details',
										layout: 'hbox',
										defaultType: 'textfield',
										fieldDefaults: {
											anchor: '100%',
											msgTarget: 'side',
											margin: '0 0 5 0',
											width: '100%',
										},
										items: [{
											xtype: 'fieldcontainer',
											fieldLabel: '',
											defaultType: 'textboxfield',
											layout: 'vbox',
											items: [{
													xtype: 'fieldcontainer',
													layout: 'hbox',
													items: [{
															xtype: 'displayfield',
															allowBlank: false,
															fieldLabel: 'Gold Balance Range',
															reference: 'goldbalancerange',
															name: 'avgdailygoldbalancegramfrom',
															flex: 2,
															style: 'padding-left: 20px;',
															labelWidth: '10%',
														},
													]
												},
												{
													xtype: 'fieldcontainer',
													layout: 'hbox',
													items: [{
															xtype: 'displayfield',
															allowBlank: false,
															fieldLabel: 'Fee Value',
															reference: 'feevalue',
															name: 'feeamount',
															flex: 2,
															style: 'padding-left: 20px;',
															labelWidth: '10%',
														},
													]
												},
												{
													xtype: 'fieldcontainer',
													layout: 'hbox',
													items: [{
															xtype: 'displayfield',
															allowBlank: false,
															fieldLabel: 'Start Date',
															reference: 'startdate',
															name: 'starton',
															flex: 2,
															style: 'padding-left: 20px;',
															labelWidth: '10%',
														},
													]
												},
												{
													xtype: 'fieldcontainer',
													layout: 'hbox',
													items: [{
															xtype: 'displayfield',
															allowBlank: false,
															fieldLabel: 'End Date',
															reference: 'enddate',
															name: 'endon',
															flex: 2,
															style: 'padding-left: 20px;',
															labelWidth: '10%',
														},
													]
												},
											]
										}]
									},
									{
										xtype: 'fieldset', 
										title: 'Enter Approval Code', 
										collapsible: false,
										id: 'approvalfieldset',
										items: [
											{
												xtype: 'fieldcontainer',
												layout: {
													type: 'hbox',
												},
												items: [
													{
														xtype: 'textfield', fieldLabel: '', name: 'approvalcode', id:'managementfeeapprovalcode', flex: 2, style: 'padding-left: 20px;', maxLength :10, enforceMaxLength: true,
													},
												]
											},
										]
									},
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
														xtype: 'textarea', fieldLabel: '', name: 'remarks', flex: 2, style: 'padding-left: 20px;', id: 'managementfeeapprovalremarks'
													},
												]
											},
										]
									}
								],
							},
						],
					},
					formEditManagementFee: {
						formDialogWidth: 950,
						controller: 'otcmanagementfee-otcmanagementfee',
						formDialogTitle: 'Edit Management Fee Approval',
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
							{
								items: [{
										itemId: 'user_main_fieldset',
										xtype: 'fieldset',
										title: 'Main Information',
										title: 'Management Fee Details',
										layout: 'hbox',
										defaultType: 'textfield',
										fieldDefaults: {
											anchor: '100%',
											msgTarget: 'side',
											margin: '0 0 5 0',
											width: '100%',
										},
										items: [{
											xtype: 'fieldcontainer',
											fieldLabel: '',
											defaultType: 'textboxfield',
											layout: 'vbox',
											items: [{
													xtype: 'fieldcontainer',
													layout: 'hbox',
													items: [
														{
															xtype: 'displayfield', allowBlank: false, fieldLabel: 'From', reference: 'fromFullname', name: 'fromFullname', flex: 5, style: 'padding-left: 20px;', labelWidth: '10%',
														},
														{
															xtype: 'displayfield', allowBlank: false, fieldLabel: 'To', reference: 'toFullname', name: 'toFullname', flex: 5, style: 'padding-left: 20px;', labelWidth: '10%',
														},
													]
												},{
													xtype: 'fieldcontainer',
													layout: 'hbox',
													items: [{
															xtype: 'displayfield',
															allowBlank: false,
															fieldLabel: 'Gold Balance Range',
															reference: 'goldbalancerangefrom',
															name: 'avgdailygoldbalancegramfrom',
															flex: 2,
															style: 'padding-left: 20px;',
															labelWidth: '10%',
														},{
															xtype: 'displayfield',
															allowBlank: false,
															fieldLabel: 'Gold Balance Range',
															reference: 'goldbalancerangeto',
															name: 'avgdailygoldbalancegramfrom',
															flex: 2,
															style: 'padding-left: 20px;',
															labelWidth: '10%',
														},
													]
												},
												{
													xtype: 'fieldcontainer',
													layout: 'hbox',
													items: [{
															xtype: 'displayfield',
															allowBlank: false,
															fieldLabel: 'Fee Value',
															reference: 'feevaluefrom',
															name: 'feeamount',
															flex: 2,
															style: 'padding-left: 20px;',
															labelWidth: '10%',
														},{
															xtype: 'displayfield',
															allowBlank: false,
															fieldLabel: 'Fee Value',
															reference: 'feevalueto',
															name: 'feeamount',
															flex: 2,
															style: 'padding-left: 20px;',
															labelWidth: '10%',
														},
													]
												},
												{
													xtype: 'fieldcontainer',
													layout: 'hbox',
													items: [{
															xtype: 'displayfield',
															allowBlank: false,
															fieldLabel: 'Start Date',
															reference: 'startdatefrom',
															name: 'starton',
															flex: 2,
															style: 'padding-left: 20px;',
															labelWidth: '10%',
														},
														{
															xtype: 'displayfield',
															allowBlank: false,
															fieldLabel: 'Start Date',
															reference: 'startdateto',
															name: 'starton',
															flex: 2,
															style: 'padding-left: 20px;',
															labelWidth: '10%',
														},
													]
												},
												{
													xtype: 'fieldcontainer',
													layout: 'hbox',
													items: [{
															xtype: 'displayfield',
															allowBlank: false,
															fieldLabel: 'End Date',
															reference: 'enddatefrom',
															name: 'endon',
															flex: 2,
															style: 'padding-left: 20px;',
															labelWidth: '10%',
														},{
															xtype: 'displayfield',
															allowBlank: false,
															fieldLabel: 'End Date',
															reference: 'enddateto',
															name: 'endon',
															flex: 2,
															style: 'padding-left: 20px;',
															labelWidth: '10%',
														},
													]
												},
											]
										}]
									},
									{
										xtype: 'fieldset', 
										title: 'Enter Approval Code', 
										collapsible: false,
										id: 'approvalfieldset',
										items: [
											{
												xtype: 'fieldcontainer',
												layout: {
													type: 'hbox',
												},
												items: [
													{
														xtype: 'textfield', fieldLabel: '', name: 'approvalcode', id:'managementfeeapprovalcode', flex: 2, style: 'padding-left: 20px;', maxLength :10, enforceMaxLength: true,
													},
												]
											},
										]
									},
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
														xtype: 'textarea', fieldLabel: '', name: 'remarks', flex: 2, style: 'padding-left: 20px;', id: 'managementfeeapprovalremarks'
													},
												]
											},
										]
									}
								],
							},
						],
					},
					toolbarItems: [
					   'detail', '|', 'filter',
						{
							text: 'Approve Request',
							cls: '',
							tooltip: 'Approve Management Fee',
							iconCls: 'x-fa fa-check',
							reference: 'approvemanagementfee',
							handler: 'approvemanagementfee',
							showToolbarItemText: true,
						},
					],
				}]
			}
        ]
    },
});
