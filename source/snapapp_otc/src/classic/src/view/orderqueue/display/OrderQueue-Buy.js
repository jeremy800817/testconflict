Ext.define('snap.view.orderqueue.display.OrderQueue-Buy',{
    extend: 'snap.view.orderqueue.OrderQueue',
    xtype: 'orderqueuebuyview',

    //permissionRoot: '/root/mbb/ftrorder',
    store: { type: 'MibOrderQueue', proxy: {
            type: 'ajax',
            url: 'index.php?hdl=orderqueue&action=list&type=CompanyBuy',
            reader: {
                type: 'json',
                rootProperty: 'records',
            }
        },
        remoteFilter: true,
        filters: [
            { property: 'ordertype',value: 'CompanyBuy'},
            { property: 'status',value: '1'},
        ],
        sorters: [{
            property: 'pricetarget',
            direction: 'ASC'
        }]
    },
    listeners: {
        afterrender: function () {
            this.store.sorters.clear();
            this.store.sort([{
                property: 'pricetarget',
                direction: 'ASC'
            }]);          
        }
    },
    columns: [
        { text: 'Status',  dataIndex: 'status', minWidth:100,

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
                renderer: function(value, rec){
                if(value=='0') return 'Pending';
                else if(value=='1') return 'Active';
                else if(value=='2') return 'Fulfilled';
                else if(value=='3') return 'Matched';
                else if(value=='4') return 'Pending Cancel';
                else if(value=='5') return 'Cancelled';
                else return 'Expired';
            },
        },
        { text: 'ID',  dataIndex: 'id', filter: {type: 'string'}, hidden: true, flex: 1 },
        //{ text: 'Order ID',  dataIndex: 'orderid', filter: {type: 'string'} , hidden: true, flex: 1 },
        { text: 'Spot Order No',  dataIndex: 'orderid', filter: {type: 'string'} ,hidden: true,minWidth:130},
        { text: 'Order Queue No',  dataIndex: 'orderqueueno', filter: {type: 'string'} ,hidden: true,minWidth:130, 
            renderer: function (value, rec, rowrec) {
                // console.log(rec,rowrec,'rec')
                if (rowrec.data.ordertype == 'CompanySell'){
                    rec.style = 'color:#209474'
                }
                if (rowrec.data.ordertype == 'CompanyBuy'){
                    rec.style = 'color:#d07b32'
                }
                return Ext.util.Format.htmlEncode(value)
            },
        },
        { text: 'Partner Name',  dataIndex: 'partnername', hidden: true, filter: {type: 'string'}, flex: 1 },
       
        //{ text: 'Buyer Name',  dataIndex: 'buyername', filter: {type: 'string'} , hidden: true, flex: 1 },
        { text: 'Partner Ref No.',  dataIndex: 'partnerrefid', filter: {type: 'string'} ,hidden: true, flex: 1 , renderer: 'boldText'},
        { text: 'Salesperson Name',  dataIndex: 'salespersonname', filter: {type: 'string'} , hidden: true,minWidth:100},
        //{ text: 'API Version',  dataIndex: 'apiversion', filter: {type: 'string'} ,  flex: 1d },
        { text: 'Order Type',  dataIndex: 'ordertype', minWidth:100,
            renderer: function (value, rec, rowrec) {
                // console.log(rec,rowrec,'rec')
                if (rowrec.data.ordertype == 'CompanySell'){
                    rec.style = 'color:#209474'
                }
                if (rowrec.data.ordertype == 'CompanyBuy'){
                    rec.style = 'color:#d07b32'
                }
                return Ext.util.Format.htmlEncode(value)
            }, 
        },
        //{ text: 'Queue Type',  dataIndex: 'queuetype', filter: {type: 'string'  } , flex: 1, hidden: true },
        //{ text: 'Expire On', dataIndex: 'expireon', xtype: 'datecolumn', format: 'Y-m-d H:i:s', filter: {type: 'date'}, flex: 1 },
       
        { text: 'Price Target (RM/g)',  dataIndex: 'pricetarget', filter: {type: 'string'} ,minWidth:140, align: 'right', renderer: Ext.util.Format.numberRenderer('0,000.000'),
        editor: {    //field has been deprecated as of 4.0.5
            xtype: 'numberfield',
            decimalPrecision: 3
        } },
        //{ text: 'By Weight',  dataIndex: 'byweight', filter: {type: 'string'} , flex: 1 },
        { text: 'Book By',  dataIndex: 'byweight',  minWidth:100,

               filter: {
                   type: 'combo',
                   store: [
                       ['0', 'Amount'],
                       ['1', 'Weight'],

                   ],

               },
               renderer: function(value, rec){
                   if(value=='0') return '<span style="color:#800080;">' + 'Amount' + '</span>';
                   else if(value=='1') return '<span style="color:#d4af37;">' + 'Weight' + '</span>';
                   else return 'Unassigned';
              },
        },
        { text: 'Xau Weight (g)',  dataIndex: 'xau', filter: {type: 'string'} ,minWidth:130, align: 'right', minWidth: 130, renderer: Ext.util.Format.numberRenderer('0,000.000'),
            editor: {    //field has been deprecated as of 4.0.5
                xtype: 'numberfield',
                decimalPrecision: 3
            }  
        },
        { text: 'Amount (RM)',  dataIndex: 'amount', filter: {type: 'string'} ,minWidth:100, align: 'right', renderer: Ext.util.Format.numberRenderer('0,000.000'),
        editor: {    //field has been deprecated as of 4.0.5
            xtype: 'numberfield',
            decimalPrecision: 3
        } },
        { text: 'Product',  dataIndex: 'productname', filter: {type: 'string'} ,minWidth:130  },
        { text: 'Expire On', dataIndex: 'expireon', xtype: 'datecolumn', format: 'Y-m-d H:i:s', filter: {type: 'date'}, minWidth:100 },
        //{ text: 'Effective On', dataIndex: 'effectiveon', xtype: 'datecolumn', format: 'Y-m-d H:i:s', filter: {type: 'date'}, minWidth:100 },
        
        // { text: 'Remarks',  dataIndex: 'remarks', filter: {type: 'string'} , flex: 1 },
        // { text: 'Cancel On', dataIndex: 'cancelon', xtype: 'datecolumn', format: 'd/m/Y', filter: {type: 'date'}, flex: 1 },
        // { text: 'Cancel By', dataIndex: 'cancelbyname', filter: {type: 'string'  }, inputType: 'hidden',  hidden: true, flex: 1 },
        // { text: 'Match Price ID',  dataIndex: 'matchpriceid', filter: {type: 'string'} , flex: 1 },
        { text: 'Matched Price (RM)',  dataIndex: 'companybuyppg', filter: {type: 'string'} ,minWidth:100, align: 'right', renderer: Ext.util.Format.numberRenderer('0,000.000'),
        editor: {    //field has been deprecated as of 4.0.5
            xtype: 'numberfield',
            decimalPrecision: 3
        } }, 
        { text: 'Matched On', dataIndex: 'matchon', xtype: 'datecolumn', format: 'Y-m-d H:i:s', filter: {type: 'date'},  flex: 1 },
        // { text: 'Notify URL',  dataIndex: 'notifyurl', filter: {type: 'string'} , flex: 1 },
        // { text: 'Notify Match URL',  dataIndex: 'notifymatchurl', filter: {type: 'string'} , hidden: true, flex: 1 },
        // { text: 'Success Notify URL',  dataIndex: 'successnotifyurl', filter: {type: 'string'} , hidden: true, flex: 1 },
        // { text: 'Reconciled',  dataIndex: 'reconciled', filter: {type: 'int'} , flex: 1 },
        // { text: 'Reconciled On', dataIndex: 'reconciledon', xtype: 'datecolumn', format: 'd/m/Y', filter: {type: 'date'}, flex: 1 },
        // { text: 'Reconciled By', dataIndex: 'reconciledbyname', filter: {type: 'string'  }, inputType: 'hidden',  hidden: true, flex: 1 },
        


		{ text: 'Created on', dataIndex: 'createdon', xtype: 'datecolumn', format: 'Y-m-d H:i:s', filter: {type: 'date'  }, inputType: 'hidden',minWidth:100},
		{ text: 'Modified on', dataIndex: 'modifiedon', xtype: 'datecolumn', format: 'Y-m-d H:i:s', filter: {type: 'date'  }, inputType: 'hidden',minWidth:100, hidden: true,  },
        { text: 'Created by', dataIndex: 'createdbyname', filter: {type: 'string'  }, inputType: 'hidden',  hidden: true,minWidth:100  },
        { text: 'Modified by', dataIndex: 'modifiedbyname', filter: {type: 'string'  }, inputType: 'hidden',  hidden: true, minWidth:100},
       
    ],

});
