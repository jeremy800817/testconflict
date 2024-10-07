Ext.define('snap.view.mygoldtransaction.MyGoldTransaction', {
    extend: 'snap.view.gridpanel.Base',
    xtype: 'mygoldtransactionview',

    requires: [

        'snap.store.MyGoldTransaction',
        'snap.model.MyGoldTransaction',
        'snap.view.mygoldtransaction.MyGoldTransactionController',
        'snap.view.mygoldtransaction.MyGoldTransactionModel',


    ],
    formDialogWidth: 950,
    permissionRoot: '/root/bmmb/goldtransaction',
    store: { type: 'MyGoldTransaction' },
    controller: 'mygoldtransaction-mygoldtransaction',
    viewModel: {
        type: 'mygoldtransaction-mygoldtransaction'
    },
    enableFilter: true,
    toolbarItems: [
        //'add', 'edit', 'detail', '|', 'delete', 'filter','|',
        'detail', 'filter',
      
        //{reference: 'summaryButton', text: 'Summary', itemId: 'summaryOrd', tooltip: 'Summary orders of same approval', iconCls: 'x-fa fa-list-alt', handler: 'summaryOrders', validSelection: 'single' }
    ],
    listeners: {
        afterrender: function () {
            this.store.sorters.clear();
            this.store.sort([{
                property: 'id',
                direction: 'DESC'
            }]);
            var columns=this.query('gridcolumn');             
            columns.find(obj => obj.text === 'ID').setVisible(false);
            if(this.lookupReference('sendButton')){
                button = this.lookupReference('sendButton');
                button.setHidden(true);
    
                // Check for type 
                if (snap.getApplication().usertype == "Operator" || "Sale" || "Trader" || "HQ" || "Regional" || "Branch"){
                    button.setHidden(false);
                } 
            }
            
            /*
            snap.getApplication().sendRequest({
                hdl: 'order', action: 'isHideSendToSap'
                }, 'Fetching data from server....').then(
                function (data) {
                    if (data.success) {
                        if(!data.hide){
       
                            button.setHidden(false);
                        }
                        
                        //Ext.get('allocatedcount').dom.innerHTML = data.allocatedcount;
                        //Ext.getCmp('allocatedcount').setValue(data.allocatedcount);
                        //Ext.get('availablecount').dom.innerHTML = data.availablecount;
                        //Ext.get('onrequestcount').dom.innerHTML = data.onrequestcount;
                        //Ext.get('returncount').dom.innerHTML = data.returncount;                      
                    }
            })*/
        }
    },

    columns: [
        { text: 'Completed On', dataIndex: 'completedon', xtype: 'datecolumn', format: 'Y-m-d H:i:s', filter: { type: 'date' }, minWidth: 100, },
        { text: 'Failed On', dataIndex: 'failedon', xtype: 'datecolumn', format: 'Y-m-d H:i:s', filter: { type: 'date' }, minWidth: 100, },
        { text: 'ID', dataIndex: 'id', filter: { type: 'int' }, inputType: 'hidden',hidden: true},
        { text: 'Partner', dataIndex: 'partnername', filter: { type: 'string' }, minWidth: 200, },
        //{ text: 'Buyer',  dataIndex: 'buyername', filter: {type: 'string'}, flex: 1, hidden: true },
        {
            text: 'Order Amount', dataIndex: 'ordamount', exportdecimal:2, filter: { type: 'string' }, align: 'right', minWidth: 80, renderer: Ext.util.Format.numberRenderer('0,000.000'),
            editor: {    //field has been deprecated as of 4.0.5
                xtype: 'numberfield',
                decimalPrecision: 3
            }
        },
        { text: 'Order Booking On', dataIndex: 'ordbookingon', xtype: 'datecolumn', format: 'Y-m-d H:i:s', filter: { type: 'date' }, minWidth: 100, },
        { text: 'Order Buyer Id', dataIndex: 'ordbuyerid', filter: { type: 'int' }, inputType: 'hidden',hidden: true},
        { text: 'Order Cancel On', dataIndex: 'ordcancelon', xtype: 'datecolumn', format: 'Y-m-d H:i:s', filter: { type: 'date' }, minWidth: 100, },
        { text: 'Order Confirm On', dataIndex: 'ordconfirmon', xtype: 'datecolumn', format: 'Y-m-d H:i:s', filter: { type: 'date' }, minWidth: 100, },
        { text: 'Order ID', dataIndex: 'orderid', filter: { type: 'int' }, inputType: 'hidden',hidden: true},
        {
            text: 'Order Fee', dataIndex: 'ordfee', exportdecimal:2, filter: { type: 'string' }, align: 'right', hidden : true, minWidth: 80, renderer: Ext.util.Format.numberRenderer('0,000.000'),
            editor: {    //field has been deprecated as of 4.0.5
                xtype: 'numberfield',
                decimalPrecision: 3
            }
        },
        { text: 'Order No', dataIndex: 'ordorderno', hidden: true, filter: { type: 'string' }, minWidth: 130 },
        { text: 'Order Partner ID', dataIndex: 'orderpartnerid', filter: { type: 'int' }, inputType: 'hidden',hidden: true},
        {
            text: 'Order Price', dataIndex: 'ordprice', exportdecimal:2, filter: { type: 'string' }, align: 'right', minWidth: 80, renderer: Ext.util.Format.numberRenderer('0,000.000'),
            editor: {    //field has been deprecated as of 4.0.5
                xtype: 'numberfield',
                decimalPrecision: 3
            }
        },
        { text: 'Order Remarks', dataIndex: 'ordremarks', filter: { type: 'string' }, minWidth: 130 },
        {
            text: 'Status', dataIndex: 'ordstatus', minWidth: 130,

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
        { text: 'Xau Weight (g)', dataIndex: 'ordxau', exportdecimal:3, filter: { type: 'string' },  align: 'right', minWidth: 130, renderer: Ext.util.Format.numberRenderer('0,000.000'),
            editor: {    //field has been deprecated as of 4.0.5
                xtype: 'numberfield',
                decimalPrecision: 3
            } 
        },
        {
            text: 'Original Amount (RM)', dataIndex: 'originalamount', exportdecimal:2, filter: { type: 'string' },  align: 'right', minWidth: 80, renderer: Ext.util.Format.numberRenderer('0,000.000'),
            editor: {    //field has been deprecated as of 4.0.5
                xtype: 'numberfield',
                decimalPrecision: 3
            }
        },
        {
            text: 'Payment Amount (RM)', dataIndex: 'pdtamount', exportdecimal:2, filter: { type: 'string' },  align: 'right', minWidth: 80, renderer: Ext.util.Format.numberRenderer('0,000.000'),
            editor: {    //field has been deprecated as of 4.0.5
                xtype: 'numberfield',
                decimalPrecision: 3
            }
        },
        {
            text: 'Customer Fee (RM)', dataIndex: 'pdtcustomerfee', exportdecimal:2, filter: { type: 'string' },  align: 'right', minWidth: 80, renderer: Ext.util.Format.numberRenderer('0,000.000'),
            editor: {    //field has been deprecated as of 4.0.5
                xtype: 'numberfield',
                decimalPrecision: 3
            }
        },
        { text: 'Payment Failed On', dataIndex: 'pdtfailedon', xtype: 'datecolumn', format: 'Y-m-d H:i:s', filter: { type: 'date' }, hidden: true, minWidth: 130 },
        {
            text: 'Payment Gateway Fee (RM)', dataIndex: 'pdtgatewayfee', exportdecimal:2, filter: { type: 'string' },  align: 'right', minWidth: 80, renderer: Ext.util.Format.numberRenderer('0,000.000'),
            editor: {    //field has been deprecated as of 4.0.5
                xtype: 'numberfield',
                decimalPrecision: 3
            }
        },
        { text: 'Order Remarks', dataIndex: 'pdtgatewayrefno', filter: { type: 'string' }, minWidth: 130 },
        { text: 'Order Remarks', dataIndex: 'pdtlocation', filter: { type: 'string' }, minWidth: 130 },
        { text: 'Order Remarks', dataIndex: 'pdtpaymentrefno', filter: { type: 'string' }, minWidth: 130 },
      

        { text: 'Payment Refunded On', dataIndex: 'pdtrefundedon', xtype: 'datecolumn', format: 'Y-m-d H:i:s', filter: { type: 'date' }, hidden: true, minWidth: 130 },
        { text: 'Payment Requested On', dataIndex: 'pdtrequestedon', xtype: 'datecolumn', format: 'Y-m-d H:i:s', filter: { type: 'date' }, hidden: true, minWidth: 130 },
      
        { text: 'Payment Signed Data', dataIndex: 'pdtsigneddata', filter: { type: 'string' }, minWidth: 130 },
        { text: 'Payment Source Ref No', dataIndex: 'pdtsourcerefno', filter: { type: 'string' }, minWidth: 130 },

        {
            text: 'Ace Buy/Sell', dataIndex: 'pdtstatus',
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

        { text: 'Payment Success On', dataIndex: 'pdtsuccesson', xtype: 'datecolumn', format: 'Y-m-d H:i:s', filter: { type: 'date' }, hidden: true, minWidth: 130 },
        { text: 'Payment Token', dataIndex: 'pdttoken', filter: { type: 'string' }, hidden: true, minWidth: 130 },
        { text: 'Payment Transaction Date', dataIndex: 'pdttransactiondate', xtype: 'datecolumn', format: 'Y-m-d H:i:s', filter: { type: 'date' }, minWidth: 130 },
        { text: 'Ref No', dataIndex: 'refno', filter: { type: 'string' }, minWidth: 130 },
        { text: 'Reversed On', dataIndex: 'reversedon', xtype: 'datecolumn', format: 'Y-m-d H:i:s', filter: { type: 'date' },  minWidth: 130 },
        { text: 'Sales Person Code', dataIndex: 'salespersoncode', filter: { type: 'string' }, minWidth: 130 },
        { text: 'Settlement Method', dataIndex: 'settlementmethod', filter: { type: 'string' }, minWidth: 130 },
        { text: 'Transaction Reference No', dataIndex: 'dbmtransactionrefno', filter: { type: 'string' }, minWidth: 130 },
        { text: 'Requested On', dataIndex: 'dbmrequestedon', xtype: 'datecolumn', format: 'Y-m-d H:i:s', filter: { type: 'date' },  minWidth: 130 },
        { text: 'Created On', dataIndex: 'createdon', xtype: 'datecolumn', format: 'Y-m-d H:i:s', filter: { type: 'date' }, hidden: true, minWidth: 130 },
        { text: 'Modified On', dataIndex: 'modifiedon', xtype: 'datecolumn', format: 'Y-m-d H:i:s', filter: { type: 'date' }, hidden: true, minWidth: 130 },
        { text: 'Created By', dataIndex: 'createdbyname', filter: { type: 'string' }, hidden: true, minWidth: 130 },
        { text: 'Modified By', dataIndex: 'modifiedbyname', filter: { type: 'string' }, hidden: true, minWidth: 130 },
    
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
        controller: 'mygoldtransaction-mygoldtransaction',
        formDialogTitle: 'Gold Transaction',

        // Form configuration

        formDialogWidth: 950,
        formDialogTitle: 'Gold Transaction',
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


        formPanelItems: []
    },


});
