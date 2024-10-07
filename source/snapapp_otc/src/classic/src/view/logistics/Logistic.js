Ext.define("snap.view.logistics.Logistic", {
    extend: "snap.view.gridpanel.Base",
    xtype: "logisticview",

    requires: [
        "snap.store.Logistic",
        "snap.model.Logistic",
        "snap.view.logistics.LogisticController",
        "snap.view.logistics.LogisticModel",
        "Ext.ProgressBarWidget",
    ],
    formDialogWidth: 950,
    //permissionRoot: '/root/gtp/logistic;/root/mbb/logistic;',
    store: {
        type: "Logistic",
    },
    controller: "logistic-logistic",
    viewModel: {
        type: "logistic-logistic",
    },
    enableFilter: true,
    gridSelectionMode: "SINGLE",
    /*
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
    {reference: 'minimizeLgsButton', text: 'Minimize Listing', itemId: 'minimizeLgs', tooltip: 'Minimize Logistic Listing', iconCls: 'x-fa fa-arrow-left', handler: 'minimizeGridColumn', validSelection: 'ignore' },
    {reference: 'expandLgsButton', text: 'Expand Listing', itemId: 'expandLgs', tooltip: 'Expand Logistic Listing', iconCls: 'x-fa fa-arrow-right', handler: 'expandGridColumn', validSelection: 'ignore' },
    //{reference: 'rejectButton', text: 'Reject', itemId: 'rejectOrd', tooltip: 'Reject orders', iconCls: 'x-fa fa-thumbs-o-down', handler: 'ona', validSelection: 'single' },
],*/

    listeners: {
        cellclick: function (view, cell, cellIndex, record, row, rowIndex, e) {
            var me = this;
            me.checkActionPermission(view, record);
        },
        beforeitemkeyup: function (view, record, item, index, e) {
            var me = this;
            me.checkActionPermission(view, record);
        },
        afterrender: function () {
            this.store.sorters.clear();
            this.store.sort([
                {
                    property: "id",
                    direction: "DESC",
                },
            ]);
            var columns = this.query("gridcolumn");
            columns
                .find((obj) => obj.text === "Logistics ID")
                .setVisible(false);
        },
    },
    checkActionPermission: function (view, record) {
        var selected = false;
        Ext.Array.each(
            view.getSelectionModel().getSelection(),
            function (items) {
                if (items.getId() == record.getId()) {
                    selected = true;
                    return false;
                }
            }
        );

        var btnassignAceSalesman = Ext.ComponentQuery.query(
            "#assignAceSalesman"
        )[0];
        btnassignAceSalesman.disable();

        var vendorvalue = view.getSelectionModel().getSelection()[0].data
            .vendorvalue;
        var usertype = view.getSelectionModel().getSelection()[0].data.usertype;

        if (vendorvalue == "CourAce" && usertype == "Operator" && selected) {
            btnassignAceSalesman.enable();
        }
    },

    //id: 'logisticgrid',
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
            // renderer: "setTextColor",
            renderer: function (v, record) {
                if(Array.isArray(v)){
                    printHtml = '<table>';
                    // Parse JSON return from V 
                    // Sample JSON 
                    // [{"sapreturnid":20,"code":"GS-999-9-5g","serialnumber":"IGR3690683","weight":"5.000000","sapreverseno":"16678"}]
                
                        v.forEach((index) => {
                            printHtml += `<tr>
                                <td style="text-align:center; width:200px">${index}</td>
                            </tr>`;                       
                        });
                
                
                    printHtml += '</table>';
                }
                return printHtml ? printHtml : v;
            }
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

        /*{

      text     : 'Progress',
      xtype    : 'widgetcolumn',
      width    : 120,
      dataIndex: 'statusbar',
      flex: 3,
      widget: {
          xtype: 'progress',
          textTpl: [
              '{percent:number("0")}% done'
          ]
      }
  },*/

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
        //{ text: 'User Type',  dataIndex: 'usertype', filter: {type: 'string'},  hidden: true, flex: 2 },
        //{ text: 'Status Text',  dataIndex: 'status_text', filter: {type: 'string'},  hidden: true, flex: 2, renderer: 'setTextColor' },
        /*    { text: 'Status',  dataIndex: 'status',  flex: 3,

             filter: {
                 type: 'combo',
                 store: [
                     ['0', 'Delivered'],
                     ['1', 'Order Received'],
                     ['2', 'Processed'],
                     ['3', 'In Transit'],
                     ['4', 'Completed'],
                     ['5', 'Undelivered'],
                     ['6', 'Missing'],
                     ['7', 'Rejected'],

                 ],

             },
             renderer: function(value, rec){
                if(value=='0') return 'Delivered';
                else if(value=='1') return 'Order Received';
                else if(value=='2') return 'Processen';
                else if(value=='3') return 'In Transit';
                else if(value=='4') return 'Completed';
                else if(value=='5') return 'Undelivered';
                else if(value=='6') return 'Missing';
                else return 'Rejected';
            },
      }*/
    ],

    //////////////////////////////////////////////////////////////
    /// View properties settings
    ///////////////////////////////////////////////////////////////
    enableDetailView: true,
    detailViewWindowHeight: 650,
    //detailViewWindowWidth: 500,
    style: "word-wrap: normal",
    detailViewSections: {
        default: "Properties",
    },
    detailViewUseRawData: true,

    formConfig: {
        controller: "logistic-logistic",

        formDialogWidth: 950,

        formDialogTitle: "Logistics",

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
                reference: "logisticredemptionpickup-form",
                items: [
                    {
                        xtype: "hidden",
                        hidden: true,
                        name: "id",
                    },
                    {
                        xtype: "hidden",
                        hidden: true,
                        name: "orderlist",
                        bind: "{orderlist}",
                    },
                    {
                        xtype: "hidden",
                        hidden: true,
                        name: "orderdeliverydata",
                        reference: "orderdeliverydata",
                    },
                    {
                        items: [
                            {
                                layout: {
                                    type: "table",
                                    columns: 2,
                                    tableAttrs: {
                                        style: {
                                            width: "100%",
                                            height: "100%",
                                            top: "10px",
                                        },
                                    },
                                    tdAttrs: {
                                        valign: "top",
                                        height: "100%",
                                        "background-color": "grey",
                                    },
                                },
                                xtype: "container",
                                scrollable: false,
                                defaults: {
                                    bodyPadding: "5",
                                },
                                items: [
                                    {
                                        width: 910,
                                        height: 380,
                                        //layout: 'fit',
                                        flex: 5,
                                        items: [
                                            {
                                                itemId: "user_main_fieldset",
                                                xtype: "fieldset",
                                                title: "Main Information",
                                                title: "Order Details",
                                                layout: "hbox",
                                                defaultType: "textfield",
                                                fieldDefaults: {
                                                    anchor: "100%",
                                                    msgTarget: "side",
                                                    margin: "0 0 5 0",
                                                    width: "100%",
                                                },
                                                items: [
                                                    {
                                                        xtype: "fieldcontainer",
                                                        fieldLabel: "",
                                                        defaultType:
                                                            "textboxfield",
                                                        layout: "hbox",
                                                        items: [
                                                            // ALL CHECKBOX INPUT -- jsonConversion => to 'data[key] = value'
                                                            {
                                                                xtype:
                                                                    "displayfield",
                                                                allowBlank: false,
                                                                fieldLabel:
                                                                    "Order No",
                                                                reference:
                                                                    "logisticorderno",
                                                                name: "orderno",
                                                                flex: 3,
                                                                style:
                                                                    "padding-left: 20px;",
                                                                labelWidth:
                                                                    "20%",
                                                            },
                                                            {
                                                                xtype:
                                                                    "displayfield",
                                                                allowBlank: false,
                                                                fieldLabel:
                                                                    "Order Type",
                                                                reference:
                                                                    "logisticordertype",
                                                                name:
                                                                    "ordertype",
                                                                flex: 4,
                                                                style:
                                                                    "padding-left: 20px;",
                                                                labelWidth:
                                                                    "20%",
                                                            },
                                                            {
                                                                xtype:
                                                                    "displayfield",
                                                                allowBlank: false,
                                                                fieldLabel:
                                                                    "Delivery Mode",
                                                                reference:
                                                                    "logisticdeliverymode",
                                                                name:
                                                                    "deliverymode",
                                                                flex: 4,
                                                                style:
                                                                    "padding-left: 20px;",
                                                                labelWidth:
                                                                    "20%",
                                                            },
                                                            {
                                                                xtype:
                                                                    "displayfield",
                                                                allowBlank: false,
                                                                fieldLabel:
                                                                    "Attempts",
                                                                reference:
                                                                    "logisticattempts",
                                                                name: "attemps",
                                                                flex: 3,
                                                                style:
                                                                    "padding-left: 20px;",
                                                                labelWidth:
                                                                    "20%",
                                                            },
                                                            {
                                                                xtype:
                                                                    "displayfield",
                                                                fieldLabel: "",
                                                                name: "",
                                                                flex: 1,
                                                                style:
                                                                    "padding-left: 20px;",
                                                            },
                                                        ],
                                                    },
                                                ],
                                            },
                                            {
                                                xtype: "fieldset",
                                                title: "Select Logistics",
                                                collapsible: false,
                                                default: {
                                                    labelWidth: 90,
                                                    layout: "hbox",
                                                },
                                                items: [
                                                    /*      {
                                xtype: 'textfield', fieldLabel: 'Blood Pressure', name: ''
                            },
                            {
                                xtype: 'textarea', fieldLabel: 'Personal History', name: ''
                            }, */
                                                    {
                                                        xtype: "fieldcontainer",
                                                        //fieldLabel: '     ',
                                                        // defaultType: 'textboxfield',
                                                        layout: {
                                                            type: "hbox",
                                                        },
                                                        fieldDefaults: {
                                                            anchor: "100%",
                                                            msgTarget: "side",
                                                            margin: "0 0 5 0",
                                                            width: "100%",
                                                        },
                                                        items: [
                                                            /*
                                                                    {
                                                                      xtype: 'textfield', allowBlank: false, fieldLabel: 'AWB Number', name: 'awbno', flex: 1, style:'padding-left: 20px;padding-right: 20px;', labelWidth : '20%',
                                                                    },
                                                                    {
                                                                      xtype: 'datefield', allowBlank: false, fieldLabel: 'Scheduled Date', name: 'deliverydate' , flex: 1, style:'padding-left: 20px;padding-right: 20px', format: 'Y-m-d H:i:s', labelWidth: '20%',
                                                                    },
                                                                    */
                                                            {
                                                                xtype:
                                                                    "textfield",
                                                                allowBlank: false,
                                                                fieldLabel:
                                                                    "AWB Number",
                                                                name: "awbno",
                                                                reference:
                                                                    "logisticdoawbnumber",
                                                                flex: 1,
                                                                style:
                                                                    "padding-left: 20px;padding-right: 20px;",
                                                                labelWidth:
                                                                    "20%",
                                                            },
                                                            {
                                                                xtype:
                                                                    "datefield",
                                                                allowBlank: false,
                                                                fieldLabel:
                                                                    "Scheduled Delivery Date",
                                                                name:
                                                                    "deliverydate",
                                                                reference:
                                                                    "deliverydate",
                                                                flex: 1,
                                                                style:
                                                                    "padding-left: 20px;padding-right: 20px",
                                                                format:
                                                                    "Y-m-d H:i:s",
                                                                labelWidth:
                                                                    "20%",
                                                            },
                                                        ],
                                                    },
                                                    {
                                                        xtype: "fieldcontainer",
                                                        //fieldLabel: 'Clinical Data',
                                                        //defaultType: 'checkboxfield',
                                                        /*
                        items: [
                            {
                                boxLabel  : 'Hypertension',
                                name      : 'data[something]',
                            }, {
                                boxLabel  : 'Diabetes Mellitus',
                                name      : 'data[something]',
                            }, {
                                boxLabel  : 'Hyperlipidemia',
                                name      : 'data[something]',
                            }, {
                                boxLabel  : 'Smoking',
                                name      : 'data[something]',
                            }
                        ]
                        */
                                                        layout: {
                                                            type: "hbox",
                                                        },
                                                        items: [
                                                            {
                                                                xtype:
                                                                    "combobox",
                                                                fieldLabel:
                                                                    "Status",
                                                                store: {
                                                                    type:
                                                                        "array",
                                                                    fields: [
                                                                        "id",
                                                                        "code",
                                                                    ],
                                                                },
                                                                queryMode:
                                                                    "local",
                                                                remoteFilter: false,
                                                                name: "status",
                                                                valueField:
                                                                    "id",
                                                                displayField:
                                                                    "code",
                                                                reference:
                                                                    "status",
                                                                forceSelection: true,
                                                                flex: 1,
                                                                style:
                                                                    "padding-left: 20px;padding-right: 20px;",
                                                                editable: false,
                                                            },
                                                            {
                                                                xtype:
                                                                    "displayfield",
                                                                allowBlank: false,
                                                                fieldLabel:
                                                                    "Current Status",
                                                                name:
                                                                    "status_text",
                                                                flex: 1,
                                                                style:
                                                                     "padding-left: 20px;padding-right: 20px;",
                                                                labelWidth:
                                                                    "20%",
                                                            },
                                                            {
                                                                xtype:
                                                                    "datefield",
                                                                allowBlank: false,
                                                                fieldLabel:
                                                                    "Status Date",
                                                                name:
                                                                    "statusdate",
                                                                flex: 1,
                                                                style:
                                                                    "padding-left: 20px;padding-right: 20px;",
                                                                format:
                                                                    "Y-m-d H:i:s",
                                                                labelWidth:
                                                                    "20%",
                                                            },
                                                        ],
                                                    },
                                                    {
                                                        xtype: "fieldcontainer",
                                                        layout: {
                                                            type: "hbox",
                                                        },
                                                        items: [
                                                            {
                                                                xtype:
                                                                    "textarea",
                                                                fieldLabel:
                                                                    "Remarks",
                                                                name: "remarks",
                                                                flex: 3,
                                                                style:
                                                                    "padding-left: 20px;",
                                                            },
                                                            {
                                                                xtype:
                                                                    "displayfield",
                                                                reference:
                                                                    "logisticpickupbuttonpadding",
                                                                fieldLabel: "",
                                                                name: "",
                                                                flex: 2,
                                                                style:
                                                                    "padding-left: 20px;",
                                                            },
                                                            {
                                                                xtype:
                                                                    "fieldcontainer",
                                                                fieldLabel:
                                                                    "Confirm Pickup",
                                                                reference:
                                                                    "logisticpickupbutton",
                                                                flex: 1,
                                                                hidden: true,
                                                                defaultType:
                                                                    "checkboxfield",
                                                                items: [
                                                                    {
                                                                        boxLabel:
                                                                            "",
                                                                        name:
                                                                            "isPickup",
                                                                        inputValue:
                                                                            "1",
                                                                        reference:
                                                                            "isPickupLogistic",
                                                                    },
                                                                ],
                                                            },
                                                            /*  {
                              xtype: 'displayfield', fieldLabel: '', name: '' , flex: 1, style:'padding-left: 20px;padding-right: 20px', labelWidth: '20%', maxLength: 3,
                            },*/

                                                            /*
                                                    {
                                                      xtype: 'textfield', fieldLabel: 'Respiration', name: 'respiration' , flex: 1, style:'padding-left: 20px;', width: '50%'
                                                    },
                                                    {
                                                      xtype: 'textfield', fieldLabel: 'Visual Fall Screening', name: 'visualfallscreening', flex: 1, style:'padding-left: 20px;',
                                                    }
                                                  */
                                                        ],
                                                    },
                                                ],
                                            },
                                        ],
                                    },
                                    {
                                        flex: 1,
                                        width: 380,
                                        height: 380,
                                        items: [
                                            {
                                                xtype: "fieldset",
                                                title: "Delivery Status",
                                                collapsible: false,
                                                default: {
                                                    labelWidth: 30,
                                                    layout: "hbox",
                                                },
                                                items: [
                                                    {
                                                        xtype: "container",
                                                        height: 300,
                                                        scrollable: true,
                                                        reference:
                                                            "deliverystatusdisplayfield",
                                                    },
                                                ],
                                            },
                                        ],
                                    },
                                ],
                            },
                        ],
                    },
                    //
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

    //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    //
    //  Redemption
    //
    /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

    /*--------------------------------------------------------- Redemption Form  -----------------------------------------------------------------------*/

    formRedemption: {
        controller: "logistic-logistic",

        formDialogWidth: 1350,

        formDialogTitle: "Logistics",

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
                reference: "logisticredemptionpickup-form",
                items: [
                    {
                        xtype: "hidden",
                        hidden: true,
                        name: "id",
                    },
                    {
                        xtype: "hidden",
                        hidden: true,
                        name: "orderlist",
                        bind: "{orderlist}",
                    },
                    {
                        xtype: "hidden",
                        hidden: true,
                        name: "orderdeliverydata",
                        reference: "orderdeliverydata",
                    },
                    {
                        items: [
                            {
                                layout: {
                                    type: "table",
                                    columns: 2,
                                    tableAttrs: {
                                        style: {
                                            width: "100%",
                                            height: "100%",
                                            top: "10px",
                                        },
                                    },
                                    tdAttrs: {
                                        valign: "top",
                                        height: "100%",
                                        "background-color": "grey",
                                    },
                                },
                                xtype: "container",
                                scrollable: false,
                                defaults: {
                                    bodyPadding: "5",
                                },
                                items: [
                                    {
                                        width: 910,
                                        height: 380,
                                        //layout: 'fit',
                                        flex: 5,
                                        items: [
                                            {
                                                itemId: "user_main_fieldset",
                                                xtype: "fieldset",
                                                title: "Main Information",
                                                title: "Order Details",
                                                layout: "hbox",
                                                defaultType: "textfield",
                                                fieldDefaults: {
                                                    anchor: "100%",
                                                    msgTarget: "side",
                                                    margin: "0 0 5 0",
                                                    width: "100%",
                                                },
                                                items: [
                                                    {
                                                        xtype: "fieldcontainer",
                                                        fieldLabel: "",
                                                        defaultType:
                                                            "textboxfield",
                                                        layout: "hbox",
                                                        items: [
                                                            // ALL CHECKBOX INPUT -- jsonConversion => to 'data[key] = value'
                                                            {
                                                                xtype:
                                                                    "displayfield",
                                                                allowBlank: false,
                                                                fieldLabel:
                                                                    "Order No",
                                                                reference:
                                                                    "logisticorderno",
                                                                name: "orderno",
                                                                flex: 3,
                                                                style:
                                                                    "padding-left: 20px;",
                                                                labelWidth:
                                                                    "18%",
                                                            },
                                                            {
                                                                xtype:
                                                                    "displayfield",
                                                                allowBlank: false,
                                                                fieldLabel:
                                                                    "Order Type",
                                                                reference:
                                                                    "logisticordertype",
                                                                name:
                                                                    "ordertype",
                                                                flex: 4,
                                                                style:
                                                                    "padding-left: 20px;",
                                                                labelWidth:
                                                                    "18%",
                                                            },
                                                            {
                                                                xtype:
                                                                    "displayfield",
                                                                allowBlank: false,
                                                                fieldLabel:
                                                                    "Delivery Mode",
                                                                reference:
                                                                    "logisticdeliverymode",
                                                                name:
                                                                    "deliverymode",
                                                                flex: 4,
                                                                style:
                                                                    "padding-left: 20px;",
                                                                labelWidth:
                                                                    "18%",
                                                            },
                                                            {
                                                                xtype:
                                                                    "displayfield",
                                                                allowBlank: false,
                                                                fieldLabel:
                                                                    "Attempts",
                                                                reference:
                                                                    "logisticattempts",
                                                                name: "attemps",
                                                                flex: 3,
                                                                hidden: true,
                                                                style:
                                                                    "padding-left: 20px;",
                                                                labelWidth:
                                                                    "18%",
                                                            },
                                                            { 
                                                                xtype: 'combobox',
                                                                fieldLabel:
                                                                    "Sent By",
                                                                name: "senderid",
                                                                typeAhead: true,
                                                                triggerAction: 'all',
                                                                selectOnTab: true,
                                                                store: {
                                                                    autoLoad: true,
                                                                    type: 'SalesPersons',                   
                                                                    sorters: 'name'
                                                                },               
                                                                flex: 5,
                                                                lazyRender: true,
                                                                reference:
                                                                 "salespersonace",
                                                                displayField: 'name',
                                                                valueField: 'id',
                                                                queryMode: 'remote',
                                                                remoteFilter: false,
                                                                listClass: 'x-combo-list-small',
                                                                forceSelection: true,
                                                            }, 
                                                            {
                                                                xtype:
                                                                    "displayfield",
                                                                fieldLabel: "",
                                                                name: "",
                                                                flex: 0.5,
                                                                style:
                                                                    "padding-left: 20px;",
                                                            },
                                                        ],
                                                    },
                                                ],
                                            },
                                            {
                                                xtype: "fieldset",
                                                title: "Select Logistics",
                                                collapsible: false,
                                                default: {
                                                    labelWidth: 90,
                                                    layout: "hbox",
                                                },
                                                items: [
                                                    /*      {
                                xtype: 'textfield', fieldLabel: 'Blood Pressure', name: ''
                            },
                            {
                                xtype: 'textarea', fieldLabel: 'Personal History', name: ''
                            }, */
                                                    {
                                                        xtype: "fieldcontainer",
                                                        //fieldLabel: '     ',
                                                        // defaultType: 'textboxfield',
                                                        layout: {
                                                            type: "hbox",
                                                        },
                                                        fieldDefaults: {
                                                            anchor: "100%",
                                                            msgTarget: "side",
                                                            margin: "0 0 5 0",
                                                            width: "100%",
                                                        },
                                                        items: [
                                                            /*
                                                                    {
                                                                      xtype: 'textfield', allowBlank: false, fieldLabel: 'AWB Number', name: 'awbno', flex: 1, style:'padding-left: 20px;padding-right: 20px;', labelWidth : '20%',
                                                                    },
                                                                    {
                                                                      xtype: 'datefield', allowBlank: false, fieldLabel: 'Scheduled Date', name: 'deliverydate' , flex: 1, style:'padding-left: 20px;padding-right: 20px', format: 'Y-m-d H:i:s', labelWidth: '20%',
                                                                    },
                                                                    */
                                                            {
                                                                xtype:
                                                                    "textfield",
                                                                allowBlank: false,
                                                                fieldLabel:
                                                                    "AWB Number",
                                                                name: "awbno",
                                                                reference:
                                                                    "logisticdoawbnumber",
                                                                flex: 1,
                                                                style:
                                                                    "padding-left: 20px;padding-right: 20px;",
                                                                labelWidth:
                                                                    "20%",
                                                            },
                                                            {
                                                                xtype:
                                                                    "datefield",
                                                                allowBlank: false,
                                                                fieldLabel:
                                                                    "Scheduled Delivery Date",
                                                                name:
                                                                    "deliverydate",
                                                                flex: 1,
                                                                style:
                                                                    "padding-left: 20px;padding-right: 20px",
                                                                format:
                                                                    "Y-m-d H:i:s",
                                                                labelWidth:
                                                                    "20%",
                                                            },
                                                        ],
                                                    },
                                                    {
                                                        xtype: "fieldcontainer",
                                                        //fieldLabel: 'Clinical Data',
                                                        //defaultType: 'checkboxfield',
                                                        /*
                        items: [
                            {
                                boxLabel  : 'Hypertension',
                                name      : 'data[something]',
                            }, {
                                boxLabel  : 'Diabetes Mellitus',
                                name      : 'data[something]',
                            }, {
                                boxLabel  : 'Hyperlipidemia',
                                name      : 'data[something]',
                            }, {
                                boxLabel  : 'Smoking',
                                name      : 'data[something]',
                            }
                        ]
                        */
                                                        layout: {
                                                            type: "hbox",
                                                        },
                                                        items: [
                                                            {
                                                                xtype:
                                                                    "combobox",
                                                                fieldLabel:
                                                                    "Status",
                                                                store: {
                                                                    type:
                                                                        "array",
                                                                    fields: [
                                                                        "id",
                                                                        "code",
                                                                    ],
                                                                },
                                                                queryMode:
                                                                    "local",
                                                                remoteFilter: false,
                                                                name: "status",
                                                                valueField:
                                                                    "id",
                                                                displayField:
                                                                    "code",
                                                                reference:
                                                                    "status",
                                                                forceSelection: true,
                                                                flex: 1,
                                                                style:
                                                                    "padding-left: 20px;padding-right: 20px;",
                                                                editable: false,
                                                            },
                                                            {
                                                                xtype:
                                                                    "displayfield",
                                                                allowBlank: false,
                                                                fieldLabel:
                                                                    "Current Status",
                                                                name:
                                                                    "status_text",
                                                                flex: 1,
                                                                style:
                                                                    "padding-left: 20px;padding-right: 20px;",
                                                                labelWidth:
                                                                    "20%",
                                                            },
                                                            {
                                                                xtype:
                                                                    "datefield",
                                                                allowBlank: false,
                                                                fieldLabel:
                                                                    "Status Date",
                                                                name:
                                                                    "statusdate",
                                                                flex: 1,
                                                                style:
                                                                    "padding-left: 20px;padding-right: 20px;",
                                                                format:
                                                                    "Y-m-d H:i:s",
                                                                labelWidth:
                                                                    "20%",
                                                            },
                                                        ],
                                                    },
                                                    {
                                                        xtype: "fieldcontainer",
                                                        layout: {
                                                            type: "hbox",
                                                        },
                                                        items: [
                                                            {
                                                                xtype:
                                                                    "textarea",
                                                                fieldLabel:
                                                                    "Remarks",
                                                                name: "remarks",
                                                                flex: 3,
                                                                style:
                                                                    "padding-left: 20px;",
                                                            },
                                                            {
                                                                xtype:
                                                                    "displayfield",
                                                                reference:
                                                                    "logisticpickupbuttonpadding",
                                                                fieldLabel: "",
                                                                name: "",
                                                                flex: 2,
                                                                style:
                                                                    "padding-left: 20px;",
                                                            },
                                                            {
                                                                xtype:
                                                                    "fieldcontainer",
                                                                fieldLabel:
                                                                    "Confirm Pickup",
                                                                reference:
                                                                    "logisticpickupbutton",
                                                                flex: 1,
                                                                hidden: true,
                                                                defaultType:
                                                                    "checkboxfield",
                                                                items: [
                                                                    {
                                                                        boxLabel:
                                                                            "",
                                                                        name:
                                                                            "isPickup",
                                                                        inputValue:
                                                                            "1",
                                                                        reference:
                                                                            "isPickupLogistic",
                                                                    },
                                                                ],
                                                            },
                                                            /*  {
                              xtype: 'displayfield', fieldLabel: '', name: '' , flex: 1, style:'padding-left: 20px;padding-right: 20px', labelWidth: '20%', maxLength: 3,
                            },*/

                                                            /*
                                                    {
                                                      xtype: 'textfield', fieldLabel: 'Respiration', name: 'respiration' , flex: 1, style:'padding-left: 20px;', width: '50%'
                                                    },
                                                    {
                                                      xtype: 'textfield', fieldLabel: 'Visual Fall Screening', name: 'visualfallscreening', flex: 1, style:'padding-left: 20px;',
                                                    }
                                                  */
                                                        ],
                                                    },
                                                ],
                                            },
                                        ],
                                    },
                                    {
                                        flex: 1,
                                        width: 380,
                                        height: 380,
                                        items: [
                                            {
                                                xtype: "fieldset",
                                                title: "Delivery Status",
                                                collapsible: false,
                                                default: {
                                                    labelWidth: 30,
                                                    layout: "hbox",
                                                },
                                                items: [
                                                    {
                                                        xtype: "container",
                                                        height: 300,
                                                        scrollable: true,
                                                        reference:
                                                            "deliverystatusdisplayfield",
                                                    },
                                                ],
                                            },
                                        ],
                                    },
                                ],
                            },
                        ],
                    },
                    //
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
