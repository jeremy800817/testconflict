Ext.define("snap.view.approval.OTCInventory_BSN", {
    extend: "Ext.grid.Panel",
    xtype: "inventoryview",
    partnercode: 'BSN',
    controller: "vaultitem",
    columns: [{
            text: "ID",
            dataIndex: "id",
            filter: {
                type: "int"
            },
            inputType: "hidden",
            hidden: true,
        },
        {
            text: "Vault Location",
            dataIndex: "vaultlocationname",
            filter: {
                type: "string"
            },
            align: "right",
            minWidth: 130,
        },

        {
            text: "Serial No",
            dataIndex: "serialno",
            filter: {
                type: "string"
            },
            align: "right",
            minWidth: 130,
        },

        {
            text: "ID",
            dataIndex: "partnerid",
            filter: {
                type: "string"
            },
            align: "right",
            minWidth: 130,
        },
        // {
        //     text: "Allocated",
        //     dataIndex: "allocated",
        //     filter: {
        //         type: "string"
        //     },
        //     align: "right",
        //     minWidth: 130,
        // },
        {
            text: 'Allocated', 
            dataIndex: 'allocated',
            minWidth: 130,
            align: "right", 
            filter: { type: 'string' }, 
            renderer: function (value, rec, records) {
                if (records.data.allocated == 0) return 'No';
                else if (records.data.allocated == 1) return 'Yes';
                else return '';
            }
        },
        {
            text: "Allocated On",
            dataIndex: "allocatedon",
            xtype: "datecolumn",
            format: "Y-m-d H:i:s",
            filter: {
                type: "date"
            },
            minWidth: 130,
        },
        {
            text: "Move to Vault Location",
            dataIndex: "movetovaultlocationname",
            filter: {
                type: "string"
            },
            align: "right",
            minWidth: 150,
        },

        {
            text: "Move Request On",
            dataIndex: "moverequestedon",
            xtype: "datecolumn",
            format: "Y-m-d H:i:s",
            filter: {
                type: "date"
            },
            minWidth: 130,
        },
        {
            text: "Move Completed On",
            dataIndex: "movecompletedon",
            xtype: "datecolumn",
            format: "Y-m-d H:i:s",
            filter: {
                type: "date"
            },
            minWidth: 130,
        },
        {
            text: "Returned On",
            dataIndex: "returnedon",
            xtype: "datecolumn",
            format: "Y-m-d H:i:s",
            filter: {
                type: "date"
            },
            minWidth: 130,
        },
        {
            text: "New Vault Location",
            dataIndex: "newvaultlocationname",
            filter: {
                type: "string"
            },
            align: "right",
            minWidth: 200,
        },

        {
            text: "Delivery Order No",
            dataIndex: "deliveryordernumber",
            filter: {
                type: "string"
            },
            align: "right",
            minWidth: 180,
        },

        {
            text: "Brand",
            dataIndex: "brand",
            filter: {
                type: "string"
            },
            align: "left",
            minWidth: 180,
        },
        {
            text: "Created On",
            dataIndex: "createdon",
            xtype: "datecolumn",
            format: "Y-m-d H:i:s",
            filter: {
                type: "date"
            },
            minWidth: 130,
        },
        {
            text: "Modified On",
            dataIndex: "modifiedon",
            xtype: "datecolumn",
            format: "Y-m-d H:i:s",
            filter: {
                type: "date"
            },
            minWidth: 130,
        },
        // {
        //     text: "Status",
        //     dataIndex: "status",
        //     filter: {
        //         type: "string"
        //     },
        //     align: "right",
        //     minWidth: 180,
        // },
        {
            text: 'Status', dataIndex: 'status',minWidth:180, align: "right", filter: {
                type: 'combo',
                store: [
                    ['0', 'Pending'],
                    ['1', 'Active'],
                    ['2', 'Transferring'],
                    ['3', 'Inactive'],
                    ['4', 'Removed'],
                ]
            }, renderer: function (value, rec, records) {
                if (value == 1 && records.data.allocated == 1) return 'Allocated Active';
                else if ((value == 3 || value == 4) && records.data.allocated == 1) return 'Allocated Inactive';
                else if (value == 2 && records.data.allocated == 1) return 'Allocated Transferring';
                else if (value == 1 && records.data.allocated == 0) return 'Unallocated Active';
                else if ((value == 3 || value == 4) && records.data.allocated == 0) return 'Unallocated Inactive';
                else if (value == 2 && records.data.allocated == 0) return 'Unallocated Transferring';
                else if (value == 5 && records.data.allocated == 0) return 'Unallocated Pending';
                else return '';
            },
        },
        {
            text: "Product Code",
            dataIndex: "productcode",
            filter: {
                type: "string"
            },
            align: "right",
            minWidth: 180,
        },
    ],
    dockedItems: [{
            xtype: "pagingtoolbar",
            dock: "bottom",
            displayInfo: true,
        },
        {
            xtype: "toolbar",
            dock: "top",
            items: [{
                    xtype: "datefield",
                    fieldLabel: "Start Date",
                    name: "start_date",
                    reference: "startDate",
                    format: "d/m/Y",
                },
                {
                    xtype: "datefield",
                    fieldLabel: "End Date",
                    name: "end_date",
                    reference: "endDate",
                    format: "d/m/Y",
                },
                {
                    iconCls: 'x-fa fa-redo-alt',
                    xtype: "button",
                    text: "Filter Date",
                    handler: "getdatefilter",
                }, 
                {
                    iconCls: 'x-fa fa-times-circle', 
                    xtype: "button",
                    text: "Clear Date",
                    handler: "cleardatefilter",
                },
                {
                    iconCls: 'x-fa fa-download',
                    xtype: "button",
                    text: "Download",
                    handler: "getinventoryreport",
                    // handler: function () {
                    //   // Handle download button click event here
                    // },
                },
            ],
        },
    ],

    store: {
        type: "MyOrder",
        pageSize: 25,
        proxy: {
            type: "ajax",
            url:
                // "index.php?hdl=vaultitemhandler&action=list&partnercode=" + PROJECTBASE,
                "index.php?hdl=vaultitemhandler&action=vaultdata&partnercode=" + PROJECTBASE,
            reader: {
                type: "json",
                rootProperty: "records",
            },
        },
    },
});