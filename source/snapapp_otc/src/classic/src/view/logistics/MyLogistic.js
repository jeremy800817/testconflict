Ext.define('snap.view.logistics.MyLogistic',{
    extend: 'snap.view.logistics.Logistic',
    xtype: 'mylogisticview',

    //formDialogWidth: 950,
    //permissionRoot: '/root/bmmb/logistic',
    store: { type: 'MyLogistic' },
    //controller: 'logistic-logistic',
    //viewModel: {
    //    type: 'logistic-logistic',
    //},
    //enableFilter: true,
    //gridSelectionMode: 'SINGLE',
    columns: [
        {
            text: "Logistics ID",
            dataIndex: "id",
            hidden: true,
            filter: {
                type: "string",
            },
            flex: 1,
        },
        {
            text: "Type",
            dataIndex: "type",
            flex: 2,

            filter: {
                type: "combo",
                store: [
                    ["Redemption", "Redemption"],
                    ["Buyback", "Buyback"],
                    ["Replenishment", "Replenishment"],
                ],
            },
            renderer: function (value, rec) {
                if (value == "Redemption") return "Redemption";
                else if (value == "Buyback") return "Buyback";
                else return "Replenishment";
            },
        },
        {
            text: "Partner Id",
            dataIndex: "partnerid",
            hidden: true,
            filter: {
                type: "string",
            },
            flex: 1,
        },
        {
            text: "Status",
            dataIndex: "status",
            flex: 2,
            renderer: "setTextColor",

            filter: {
                type: "combo",
                store: [
                    ["0", "Pending"],
                    ["1", "Processing"],
                    ["2", "Packing"],
                    ["3", "Packed"],
                    ["4", "Collected"],
                    ["5", "In Transit"],
                    ["6", "Delivered"],
                    ["7", "Completed"],
                    ["8", "Failed"],
                    ["9", "Missing"],

                    //['Collecting', 10],
                ],
            },
            renderer: function (value, rec) {
                if (value == "0") return  '<span data-qtitle="Pending" data-qwidth="200" '+
                'data-qtip="Logistic is pending for the next action">'+
                 "Pending" +'</span>';
                else if (value == "1") return  '<span data-qtitle="Processing" data-qwidth="200" '+
                'data-qtip="Logistic request is being processed for collection">'+
                 "Processing" +'</span>';
                else if (value == "2") return  '<span data-qtitle="Packing" data-qwidth="200" '+
                'data-qtip="Logistic package is being packed">'+
                 "Packing" +'</span>';
                else if (value == "3") return  '<span data-qtitle="Packed" data-qwidth="200" '+
                'data-qtip="Logistic package is awaiting collection">'+
                 "Packed" +'</span>';
                else if (value == "4") return  '<span data-qtitle="Collected" data-qwidth="200" '+
                'data-qtip="Logistic package is successfully collected">'+
                 "Collected" +'</span>';
                else if (value == "5") return  '<span data-qtitle="In Transit" data-qwidth="200" '+
                'data-qtip="Package is being delivered">'+
                 "In  Transit" +'</span>';
                else if (value == "6") return  '<span data-qtitle="Delivered" data-qwidth="200" '+
                'data-qtip="Package is successfully delivered">'+
                 "Delivered" +'</span>';
                else if (value == "7") return  '<span data-qtitle="Completed" data-qwidth="200" '+
                'data-qtip="Successful delivery is verified by admin">'+
                 "Completed" +'</span>';
                else if (value == "8") return  '<span data-qtitle="Failed" data-qwidth="200" '+
                'data-qtip="Failed delivery after 3 attempts during In Transit">'+
                 "Failed" +'</span>';
                //else if(value== 9) return 'Missing';
                else return  '<span data-qtitle="Missing" data-qwidth="200" '+
                'data-qtip="Logistic package went missing">'+
                 "Missing" +'</span>';
                
          
            },
            
        },
        {
            text: "Type ID",
            dataIndex: "typeid",
            filter: {
                type: "int",
            },
            flex: 2,
            renderer: "setTextColor",
        },
        {
            text: "Type No",
            dataIndex: "typeno",
            filter: {
                type: "string",
            },
            flex: 2,
        },
        //{ text: 'Vendor Name',  dataIndex: 'vendorid', filter: {type: 'int'}, flex: 1 },
        /*{ text: 'Vendor',  dataIndex: 'vendorid',  flex: 2,

         filter: {
             type: 'combo',
             store: [
                 ['1', 'Ace Logistic'],
                 ['2', 'GDEX'],

             ],

         },
         renderer: function(value, rec){
            if(value=='1') return 'Ace Logistic';
            if(value=='2') return 'GDEX';
            else return 'Unidentified';
        },
  },*/
        {
            text: "Vendor",
            dataIndex: "vendorname",
            name: "vendorname",
            reference: "vendorname",
            filter: {
                type: "string",
            },
            flex: 1,
            renderer: "setTextColor",
        },

        {
            text: "Sender",
            dataIndex: "sendername",
            filter: {
                type: "string",
            },
            flex: 1,
            renderer: "setTextColor",
        },

        {
            text: "Contact name 1",
            dataIndex: "contactname1",
            filter: {
                type: "string",
            },
            flex: 2,
            renderer: "setTextColor",
        },
        {
            text: "Contact name 2",
            dataIndex: "contactname2",
            filter: {
                type: "string",
            },
            flex: 2,
            renderer: "setTextColor",
        },
        {
            text: "Contact number 1",
            dataIndex: "contactno1",
            filter: {
                type: "string",
            },
            flex: 2,
            renderer: "setTextColor",
        },
        {
            text: "Contact number 2",
            dataIndex: "contactno2",
            filter: {
                type: "string",
            },
            flex: 2,
            renderer: "setTextColor",
        },

        {
            text: "Address 1",
            dataIndex: "address1",
            hidden: true,
            filter: {
                type: "string",
            },
            flex: 3,
            renderer: "setTextColor",
        },
        {
            text: "Address 2",
            dataIndex: "address2",
            hidden: true,
            filter: {
                type: "string",
            },
            flex: 3,
            renderer: "setTextColor",
        },
        {
            text: "Address 3",
            dataIndex: "address3",
            hidden: true,
            filter: {
                type: "string",
            },
            flex: 3,
            renderer: "setTextColor",
        },

        {
            text: "City",
            dataIndex: "city",
            filter: {
                type: "string",
            },
            hidden: true,
            flex: 1,
            renderer: "setTextColor",
        },
        {
            text: "Postcode",
            dataIndex: "postcode",
            filter: {
                type: "string",
            },
            hidden: true,
            flex: 1,
            renderer: "setTextColor",
        },
        {
            text: "State",
            dataIndex: "state",
            filter: {
                type: "string",
            },
            hidden: true,
            flex: 1,
            renderer: "setTextColor",
        },
        {
            text: "Country",
            dataIndex: "country",
            filter: {
                type: "string",
            },
            hidden: true,
            flex: 1,
            renderer: "setTextColor",
        },

        {
            text: "Awb / DO No",
            dataIndex: "awbno",
            filter: {
                type: "string",
            },
            flex: 2,
            renderer: "setTextColor",
        },
        //{ text: 'From Branch',  dataIndex: 'frombranchname', filter: {type: 'string'}, flex: 1 },
        //{ text: 'To Branch',  dataIndex: 'tobranchname', filter: {type: 'string'}, flex: 1 },
        {
            text: "Sent On",
            dataIndex: "senton",
            xtype: "datecolumn",
            format: "Y-m-d H:i:s",
            filter: {
                type: "date",
            },
            flex: 2,
        },
        {
            text: "Sent By",
            dataIndex: "sendername",
            filter: {
                type: "string",
            },
            flex: 1,
            renderer: "setTextColor",
        },
        //{ text: 'Received Person',  dataIndex: 'receivedperson', filter: {type: 'string'}, flex: 1 },
        {
            text: "Delivered On",
            dataIndex: "deliveredon",
            xtype: "datecolumn",
            format: "Y-m-d H:i:s",
            filter: {
                type: "date",
            },
            flex: 2,
        },
        {
            text: "Delivered By",
            dataIndex: "deliveredbyname",
            filter: {
                type: "string",
            },
            flex: 2,
            renderer: "setTextColor",
        },
        {
            text: "Delivery Date",
            dataIndex: "deliverydate",
            xtype: "datecolumn",
            format: "Y-m-d H:i:s",
            filter: {
                type: "date",
            },
            flex: 2,
        },
        {
            text: "Attempts",
            dataIndex: "attemps",
            filter: {
                type: "int",
            },
            flex: 1,
            renderer: "setTextColor",
        },

        {
            text: "Created On",
            dataIndex: "createdon",
            xtype: "datecolumn",
            format: "Y-m-d H:i:s",
            filter: {
                type: "date",
            },
            hidden: true,
            // renderer: "setTextColor",
        },
        {
            text: "Created By",
            dataIndex: "createdbyname",
            filter: {
                type: "string",
            },
            hidden: true,
            renderer: "setTextColor",
        },
        {
            text: "Modified On",
            dataIndex: "modifiedon",
            xtype: "datecolumn",
            format: "Y-m-d H:i:s",
            filter: {
                type: "date",
            },
            hidden: true,
            // renderer: "setTextColor",
        },
        {
            text: "Modified By",
            dataIndex: "modifiedbyname",
            filter: {
                type: "string",
            },
            hidden: true,
            renderer: "setTextColor",
        },
      
    ],
    toolbarItems: [
        //'add', 'edit''detail', '|', 'delete', 'filter','|',
        'detail', '|', 'filter',
        //{reference: 'approveButton', text: 'Approve', itemId: 'approveOrd', tooltip: 'Approve orders', iconCls: 'x-fa fa-thumbs-o-up', handler: 'approveOrders', validSelection: 'multiple'},
        //{reference: 'rejectButton', text: 'Reject', itemId: 'rejectOrd', tooltip: 'Reject orders', iconCls: 'x-fa fa-thumbs-o-down', handler: 'rejectOrders', validSelection: 'single' },
        //{reference: 'deliveredButton', text: 'Received', itemId: 'deliveredOrd', tooltip: 'Received orders', iconCls: 'x-fa fa-truck', handler: 'deliveredOrders', validSelection: 'single' },
        '|',
        { reference: 'assignAceSalesmanButton', text: 'Assign Ace Salesman', itemId: 'assignAceSalesman', tooltip: 'Assign Salesman for delivery', iconCls: 'x-fa fa-paper-plane', handler: 'assignAceSalesman', validSelection: 'single',
            listeners : {
                afterrender : function(srcCmp) {
                    Ext.create('Ext.tip.ToolTip', {
                        target : srcCmp.getEl(),
                        html : 'Assign Salesman for delivery'
                    });
                }
            }
        },
        {reference: 'updateLgsStatusButton', text: 'Logistic Status', itemId: 'statusLgs', tooltip: 'Update Logistic Status', iconCls: 'x-fa fa-list-alt', handler: 'updateDeliveryStatus', validSelection: 'single',
            listeners : {
                afterrender : function(srcCmp) {
                    Ext.create('Ext.tip.ToolTip', {
                        target : srcCmp.getEl(),
                        html : 'Update Logistic Status'
                    });
                }
            }
        },
        {reference: 'minimizeLgsButton', text: 'Minimize Listing', itemId: 'minimizeLgs', tooltip: 'Minimize Logistic Listing', iconCls: 'x-fa fa-arrow-left', handler: 'minimizeGridColumnGTP', validSelection: 'ignore' },
        {reference: 'expandLgsButton', text: 'Expand Listing', itemId: 'expandLgs', tooltip: 'Expand Logistic Listing', iconCls: 'x-fa fa-arrow-right', handler: 'expandGridColumnGTP', validSelection: 'ignore' },
        {reference: 'printButton', text: 'Print Document', itemId: 'printButton', tooltip: 'Print Documents', iconCls: 'x-fa fa-print', handler: 'printButton', validSelection: 'single' },
        //{reference: 'rejectButton', text: 'Reject', itemId: 'rejectOrd', tooltip: 'Reject orders', iconCls: 'x-fa fa-thumbs-o-down', handler: 'ona', validSelection: 'single' },
        {
            reference: 'getlatestcourierstatus',
            text: 'Upate Courier Status',
            itemId: 'getlatestcourierstatus',
            tooltip: 'Upate Courier Status',
            iconCls: 'x-fa fa-truck',
            handler: 'getlatestcourierstatus',
            validSelection: 'ignore',
            showToolbarItemText: true, 
        },
    ],

});
