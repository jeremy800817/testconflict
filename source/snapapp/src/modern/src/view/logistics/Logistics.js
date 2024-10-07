Ext.define('snap.view.logistics.Logistics', {
    extend: 'Ext.panel.Panel',
    xtype: 'logistics',
    requires: [
        'Ext.Button',
        'Ext.Img'
    ],
    title: 'Logistics',
    layout: {
        align: 'stretch',
        type: 'hbox',
    },
    controller: 'logistics-logistics',
    viewModel: {
        type: 'logistics-logistics',
    },
    width: '100%',
    items: [{
        xtype: 'container',
        layout: {
            type: 'vbox',
            align: 'stretch',
        },
        width: '100%',
        items: [{
            xtype: 'toolbar',
            width: '100%',
            items: [               
                {
                    reference: 'updateLgsStatusButton', text: 'Update Logistic Status', itemId: 'statusLgs', iconCls: 'x-fa fa-list-alt',handler: 'updateDeliveryStatus', validSelection: 'single',
                },
            ]
        },
        {
            xtype: 'grid',
            width: '100%',
            layout: {
                type: 'hbox',
                align: 'fit',
            },
            selectable: {
                mode: 'single',
                checkbox: true
            },
            plugins: {
                pagingtoolbar: true
            },
            id: 'logisticsgrid',

            //pageSize:'2',
            store: [],
            // store: Ext.create('snap.store.Logistic'),
            permissionRoot: '/root/system/logistic',
            columns: [
                { text: 'ID', dataIndex: 'id', filter: { type: 'int' } },
                {
                    text: 'Status', dataIndex: 'status', filter: { type: 'string' },
                    renderer: function (value, rec) {
                        if (value == '0') return 'Pending';
                        else if (value == '1') return 'Processing';
                        else if (value == '2') return 'Packing';
                        else if (value == '3') return 'Packed';
                        else if (value == '4') return 'Collected';
                        else if (value == '5') return 'In Transit';
                        else if (value == '6') return 'Delivered';
                        else if (value == '7') return 'Completed';
                        else if (value == '8') return 'Failed';
                        //else if(value== 9) return 'Missing';
                        else return 'Missing';
                    },
                },
                { text: 'Type', dataIndex: 'type', filter: { type: 'string' } },
                { text: 'Vendor Name', dataIndex: 'vendorname', filter: { type: 'string' } },
                { text: 'Order ID', dataIndex: 'typeid', filter: { type: 'int' } },
                { text: 'Vendor', dataIndex: 'vendorname', name: 'vendorname', reference: 'vendorname', filter: { type: 'string' }},
                { text: 'Sender', dataIndex: 'sendername', filter: { type: 'string' } },
                { text: 'Contact name 1', dataIndex: 'contactname1', filter: { type: 'string' } },
                { text: 'Contact name 2', dataIndex: 'contactname2', filter: { type: 'string' } },
                { text: 'Contact number 1', dataIndex: 'contactno1', filter: { type: 'string' } },
                { text: 'Contact number 2', dataIndex: 'contactno2', filter: { type: 'string' } },
                { text: 'Address 1', dataIndex: 'address1', hidden: true, filter: { type: 'string' } },
                { text: 'Address 2', dataIndex: 'address2', hidden: true, filter: { type: 'string' } },
                { text: 'Address 3', dataIndex: 'address3', hidden: true, filter: { type: 'string' } },
                { text: 'City', dataIndex: 'city', filter: {type: 'string'}, hidden: true,},
                { text: 'Postcode', dataIndex: 'postcode', filter: {type: 'string'}, hidden: true,},
                { text: 'State', dataIndex: 'state', filter: {type: 'string'}, hidden: true,},
                { text: 'Country', dataIndex: 'country', filter: {type: 'string'}, hidden: true,},
                { text: 'Awb / DO No', dataIndex: 'awbno', filter: {type: 'string'}},                
                { text: 'Sent On',  dataIndex: 'senton',  xtype: 'datecolumn', format: 'd/m/Y', filter: {type: 'date'}},
                { text: 'Sent By',  dataIndex: 'sentbyname', filter: {type: 'string'}},
                { text: 'Delivered On',  dataIndex: 'deliveredon',  xtype: 'datecolumn', format: 'd/m/Y', filter: {type: 'date'}},
                { text: 'Delivered By',  dataIndex: 'deliveredbyname', filter: {type: 'string'}},
                { text: 'Delivery Date',  dataIndex: 'deliverydate',  xtype: 'datecolumn', format: 'd/m/Y', filter: {type: 'date'}},
                { text: 'Attempts',  dataIndex: 'attemps', filter: {type: 'int'}},

            ],
            height: '90%',
            width: '100%',
            listeners: {
                selectionchange: function (grid, re) {
                   
                },

            },
        }
        ]

    }],
});