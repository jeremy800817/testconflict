Ext.define('snap.view.order.BursaOrder', {
    extend: 'snap.view.order.MibOrder',
    xtype: 'bursaorderview',
    partnercode: 'BURSA',
    formDialogWidth: 950,
    permissionRoot: '/root/bursa/order',
    store: {
        type: 'MibOrder', proxy: {
            type: 'ajax',
            url: 'index.php?hdl=miborder&action=list&partnercode=BURSA',
            reader: {
                type: 'json',
                rootProperty: 'records',
            }
        },
    },
    columns: [
        { text: 'Booking On', dataIndex: 'bookingon', xtype: 'datecolumn', format: 'Y-m-d H:i:s', filter: { type: 'date' }, minWidth: 100, },
        { text: 'ID', dataIndex: 'id', filter: { type: 'int' }, inputType: 'hidden',hidden: true},
        { text: 'Order No.', dataIndex: 'orderno', filter: { type: 'string' }, minWidth: 110,
            renderer: 'ordernoColor'
        },
        { text: 'Product', dataIndex: 'productname', hidden: true, filter: { type: 'string' }, minWidth: 130 },
        
        {
            text: 'Ace Buy/Sell', dataIndex: 'type',
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
        { text: 'Xau Weight (g)', dataIndex: 'xau', exportdecimal:6, filter: { type: 'string' },  align: 'right', minWidth: 130, renderer: Ext.util.Format.numberRenderer('0,000.000000'),
            editor: {    //field has been deprecated as of 4.0.5
                xtype: 'numberfield',
                decimalPrecision: 6
            } 
        },
        {
            text: 'GP/P1 Price', dataIndex: 'price', exportdecimal:2, filter: { type: 'string' }, align: 'right', minWidth: 80, 
            xtype: 'numbercolumn', format: '0,000.00'
        },
        {
            text: 'P2 Price', dataIndex: 'bookingprice', exportdecimal:2, filter: { type: 'string' }, align: 'right', minWidth: 80, renderer: Ext.util.Format.numberRenderer('0,000.00'),
            editor: {
                xtype: 'numberfield',
                decimalPrecision: 2
            }
        },
        {
            text: 'Total Amount (RM)', dataIndex: 'amount', exportdecimal:2, filter: { type: 'string' },  align: 'right', minWidth: 80, renderer: Ext.util.Format.numberRenderer('0,000.00'),
            editor: {    //field has been deprecated as of 4.0.5
                xtype: 'numberfield',
                decimalPrecision: 2
            }
        },
        {
            text: 'Discount', dataIndex: 'discountprice', hidden: true, exportdecimal:2, filter: { type: 'string' },  align: 'right', minWidth: 80, renderer: Ext.util.Format.numberRenderer('0,000.00'),
            editor: {    //field has been deprecated as of 4.0.5
                xtype: 'numberfield',
                decimalPrecision: 2
            }
        },
        {
            text: 'Discount Info', dataIndex: 'discountinfo', hidden: true, filter: { type: 'string' }, align: 'right', minWidth: 100,
        },
        {
            text: 'Status', dataIndex: 'status', minWidth: 130,

            filter: {
                type: 'combo',
                store: [
                    ['0', 'Pending'],
                    ['1', 'Confirmed'],
                    ['2', 'PendingPayment'],
                    ['3', 'PendingCancel'],
                    ['4', 'Reversal'],
                    ['5', 'Completed'],
                    ['6', 'Cancelled'],

                ],

            },
            renderer: function (value, rec) {
                if (value == '0') return '<span data-qtitle="Pending" data-qwidth="200" '+
                'data-qtip="Order Received from Maybank">'+
                 "Pending" +'</span>';
                else if (value == '1') return '<span data-qtitle="Confirmed" data-qwidth="200" '+
                'data-qtip="After push to SAP">'+
                 "Confirmed" +'</span>';
                else if (value == '2') return '<span data-qtitle="Pending Payment" data-qwidth="200" '+
                'data-qtip="Temporary status not in use">'+
                 "Pending Payment" +'</span>';
                else if (value == '3') return '<span data-qtitle="Pending Cancel" data-qwidth="200" '+
                'data-qtip="Maybank request cancel before the end of the day">'+
                 "Pending Cancel" +'</span>';
                else if (value == '4') return '<span data-qtitle="Reversal" data-qwidth="200" '+
                'data-qtip="Direct cancellation by Maybank">'+
                 "Reversal" +'</span>';
                else if (value == '5') return '<span data-qtitle="Completed" data-qwidth="200" '+
                'data-qtip="Acknowledged by SAP">'+
                 "Completed" +'</span>';
                else return '<span data-qtitle="Cancelled" data-qwidth="200" '+
                'data-qtip="Cancelled either by ACE, GTP or SAP">'+
                 "Cancelled" +'</span>';
            },
        },
        { text: 'Partner', dataIndex: 'partnername', hidden: true, filter: { type: 'string' }, minWidth: 200, },
        //{ text: 'Buyer',  dataIndex: 'buyername', filter: {type: 'string'}, flex: 1, hidden: true },

        { 
            text: 'Partner Ref', dataIndex: 'partnerrefid', hidden: true, filter: { type: 'string' }, minWidth: 130,
            renderer: 'boldText' 
        },
        { text: 'Partner Code', dataIndex: 'partnercode', hidden: true, filter: { type: 'string' }, minWidth: 130 },
        { text: 'Partner Buy Code', dataIndex: 'partnerbuycode1', hidden: true, filter: { type: 'string' }, minWidth: 130 },
        { text: 'Partner Sell Code', dataIndex: 'partnersellcode1', hidden: true, filter: { type: 'string' }, minWidth: 130 },
       
        //{ text: 'Price Stream ID', dataIndex: 'pricestreamid', filter: {type: 'string'}, hidden: true, flex: 2,},
        { text: 'Salesperson Name', dataIndex: 'salespersonname', filter: { type: 'string' }, hidden: true, minWidth: 130 },
        //{ text: 'Api Version',  dataIndex: 'apiversion', filter: {type: 'string'}, flex: 1 },




        //{ text: 'Is Spot',  dataIndex: 'isspot', hidden:true, filter: {type: 'int'}, flex: 1 },
        /*{ text: 'Is Spot',  dataIndex: 'isspot',  flex: 1,

               filter: {
                   type: 'combo',
                   store: [
                       ['0', 'False'],
                       ['1', 'True'],

                   ],

               },
               renderer: function(value, rec){
                   if(value=='0') return 'False';
                   else if(value=='1') return 'True';
                   else return 'Unassigned';
              },
        },*/
      
        {
            text: 'Price Validation ID', dataIndex: 'uuid', hidden: true, filter: { type: 'string' }, align: 'right', minWidth: 100,
        },
        {
            text: 'Is Spot', dataIndex: 'isspot', hidden: true, filter: { type: 'string' }, minWidth: 100,
            filter: {
                type: 'combo',
                store: [
                    ['0', 'Future'],
                    ['1', 'Yes'],
                ],
            },
            renderer: function (value, rec) {
                if (value == true) return 'Yes';
                else return 'Future';
            },
        },
        //{ text: 'By Weight', dataIndex: 'byweight', filter: { type: 'string' }, hidden: true, minWidth: 80 },
        {
            text: 'Book By', dataIndex: 'byweight', minWidth: 80,

            filter: {
                type: 'combo',
                store: [
                    ['0', 'Amount'],
                    ['1', 'Weight'],

                ],

            },
            renderer: function (value, rec) {
                if (value == '0') return '<span style="color:#800080;">' + 'Amount' + '</span>';
                else if (value == '1') return '<span style="color:#d4af37;">' + 'Weight' + '</span>';
                else return 'Unassigned';
            },
        },
      
        { text: 'Product Code', dataIndex: 'productcode', hidden: true, filter: { type: 'string' }, minWidth: 130 },
        


        //{ text: 'Fee',  dataIndex: 'fee', filter: {type: 'string'}, flex: 1 },
        { text: 'Remarks', dataIndex: 'remarks', filter: {type: 'string'}, hidden: true },
        /*
        
       
        { text: 'Booking Price', dataIndex: 'bookingprice', filter: {type: 'string'}, flex: 1 },
        { text: 'Booking Price Stream ID' , dataIndex: 'bookingpricestreamid', hidden:true, filter: {type: 'string'}, hidden: true },

        { text: 'Confirm On', dataIndex: 'confirmon',  xtype: 'datecolumn', format: 'd/m/Y', filter: {type: 'date'}, flex: 1 },
        { text: 'Confirm By', dataIndex: 'confirmbyname', filter: {type: 'string'}, flex: 1 },
        { text: 'Confirm Price Stream ID', dataIndex: 'confirmpricestreamid', filter: {type: 'string'}, hidden: true },
        { text: 'Confirm Price', dataIndex: 'confirmprice',  filter: {type: 'string'}, flex: 1, },
        { text: 'Confirm Reference', dataIndex: 'confirmreference',  filter: {type: 'string'}, hidden: true },

        { text: 'Cancel On', dataIndex: 'cancelon', xtype: 'datecolumn', format: 'd/m/Y', filter: {type: 'date'}, flex: 1 },
        { text: 'Cancel By', dataIndex: 'cancelbyname', filter: {type: 'string'}, flex: 1, },
        { text: 'Cancel Price Stream ID', dataIndex: 'cancelpricestreamid', filter: {type: 'string'}, flex: 1 },
        { text: 'Cancel Price', dataIndex: 'cancelprice',  filter: {type: 'string'}, flex: 1, },
        { text: 'Notify URL', dataIndex: 'notifyurl',  hidden:true, filter: {type: 'string'}, hidden: true },
        
        //{ text: 'Reconciled', dataIndex: 'reconciled',  hidden:true, filter: {type: 'string'}, flex: 1, },
        { text: 'Reconciled',  dataIndex: 'reconciled',  flex: 1,

               filter: {
                   type: 'combo',
                   store: [
                       ['0', 'False'],
                       ['1', 'True'],

                   ],

               },
               renderer: function(value, rec){
                   if(value=='0') return 'False';
                   else if(value=='1') return 'True';
                   else return 'Unassigned';
              },
        },
        { text: 'Reconciled On', dataIndex: 'reconciledon', hidden:true,  xtype: 'datecolumn', format: 'd/m/Y', filter: {type: 'date'}, flex: 1 },
        { text: 'Reconciled By',  dataIndex: 'reconciledbyname', hidden:true, filter: {type: 'string'}, flex: 1 },
        */
        { text: 'Created On', dataIndex: 'createdon', xtype: 'datecolumn', format: 'Y-m-d H:i:s', filter: { type: 'date' }, hidden: true, minWidth: 130 },
        { text: 'Modified On', dataIndex: 'modifiedon', xtype: 'datecolumn', format: 'Y-m-d H:i:s', filter: { type: 'date' }, hidden: true, minWidth: 130 },
        { text: 'Created By', dataIndex: 'createdbyname', filter: { type: 'string' }, hidden: true, minWidth: 130 },
        { text: 'Modified By', dataIndex: 'modifiedbyname', filter: { type: 'string' }, hidden: true, minWidth: 130 },
       
    ],

});
