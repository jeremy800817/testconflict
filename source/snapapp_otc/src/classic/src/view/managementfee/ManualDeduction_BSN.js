Ext.define('snap.view.managementfee.ManualDeduction_BSN', {
    extend:'Ext.panel.Panel',
    xtype: 'manualdeduction_BSN',
    permissionRoot: '/root/' + PROJECTBASE.toLowerCase() + '/managementfee/manualdeduction',

    scrollable:true,
    items: {

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

        },
        cls: 'otc-main',
        bodyCls: 'otc-main-body',
        items: [

            {
                xtype: 'panel',
                title: 'Customer Outstanding Management Fee',
                layout: 'hbox',
                collapsible: true,
                
                margin: "10 0 0 0",
                scrollable:true,
                items: [

                        {
                            flex: 1,
                            xtype: 'mymonthlystoragefeeview',
                            enableFilter: true,
                            partnercode: PROJECTBASE,

                            requires: [
                                'snap.view.orderdashboard.OrderDashboardController',
                            ],
                            controller: 'orderdashboard-orderdashboard',

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
								iconCls: 'x-fa fa-times-circle', text: 'Clear Date', tooltip: 'Clear Date', handler: 'clearDateRange', showToolbarItemText: true,
							},
                              {
                                  text: 'Pay Fee', cls: '', tooltip: 'Force Sell',iconCls: 'x-fa fa-dollar-sign', reference: 'ManualDeduction', handler: 'ManualDeduction',  showToolbarItemText: true, printType: 'xlsx', 
                              },
                            ],
                            reference: 'myoutstandingmanagementfee',
                            store: {
                                type: 'MyMonthlyStorageFee', proxy: {
                                    type: 'ajax',
                                    url: 'index.php?hdl=myoutstandingmanagementfee&action=list&partnercode='+PROJECTBASE,
                                    reader: {
                                        type: 'json',
                                        rootProperty: 'records',
                                    }
                                },
                            },

                            columns: [

                                { text: 'ID', dataIndex: 'id', filter: { type: 'int' }, inputType: 'hidden',hidden: false},
                                { text: 'Account Holder ID', dataIndex: 'achaccountholderid', filter: { type: 'int' }, inputType: 'hidden',hidden: true},
								{ text: 'Gold Account No', dataIndex: 'achaccountholdercode', filter: { type: 'string' }, minwidth: 'auto',  },
                                { text: 'Full Name', dataIndex: 'achfullname', filter: { type: 'string' }, minWidth: 130, },
                                //{ text: 'Branch Name', dataIndex: 'partnername', filter: { type: 'string' }, minWidth: 130, },
                                //{ text: 'Branch Code', dataIndex: 'partnercode', filter: { type: 'string' }, minWidth: 130, },
                                //{ text: 'CIC', dataIndex: 'achpartnercusid', filter: { type: 'string' }, minWidth: 130,  },
                                //{ text: 'Casa Account No.', dataIndex: 'achaccountnumber', filter: { type: 'string' }, minwidth:130  },     
                                {
                                    text: 'Total Outstanding Management Fee Amount (RM)', dataIndex: 'amount', exportdecimal:6, filter: { type: 'string' },  align: 'right', minWidth: 80, renderer: Ext.util.Format.numberRenderer('0,000.000000'),
                                    editor: {    
                                        xtype: 'numberfield',
                                        decimalPrecision: 6
                                    }
                                },
                                /* {
                                    text: 'Status', dataIndex: 'status', minWidth: 130,

                                    filter: {
                                        type: 'combo',
                                        store: [
                                            ['0', 'Pending'],
                                            ['1', 'Success'],
                                            ['2', 'Failed'],
                                        ],

                                    },
                                    renderer: function (value, rec) {
                                        if (value == '0') return 'Pending';
                                        else if (value == '1') return 'Success';
                                        else if (value == '2') return 'Failed';
                                        else return 'Unspecified';
                                    },
                                }, */
                                { text: 'Created On', dataIndex: 'createdon', xtype: 'datecolumn', format: 'Y-m-d H:i:s', filter: { type: 'date' }, minWidth: 130 },
                            ],

                            formManualDeduction: {
                                formDialogWidth: 950,
                                controller: 'myorder-myorder',

                                formDialogTitle: 'Management Fee Payment Confirmation',

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
                                        items: [
                                            { xtype: 'hidden', hidden: true, name: 'id' },
                                            {
                                                itemId: 'user_main_fieldset',
                                                xtype: 'fieldset',
                                                title: 'Main Information',
                                                title: 'Account Holder Details',
                                                layout: 'vbox',
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
                                                                xtype: 'displayfield', allowBlank: false, fieldLabel: 'Customer Name', reference: 'achfullname', name: 'achfullname', flex: 5, style: 'padding-left: 20px;', labelWidth: '10%',
                                                            },
                                                            {
                                                                xtype: 'displayfield', allowBlank: false, fieldLabel: 'Branch Name', reference: 'partnername', name: 'partnername', flex: 5, style: 'padding-left: 20px;', labelWidth: '10%',
                                                            },
                                                        ]
                                                    },
                                                    {
                                                        xtype: 'fieldcontainer',
                                                        fieldLabel: '',
                                                        defaultType: 'textboxfield',
                                                        layout: 'hbox',
                                                        items: [
                                                            {
                                                                xtype: 'displayfield', allowBlank: false, fieldLabel: 'Gold Account No', reference: 'achaccountholdercode', name: 'achaccountholdercode', flex: 5, style: 'padding-left: 20px;', labelWidth: '10%',
                                                            },
                                                            {
                                                                xtype: 'displayfield', allowBlank: false, fieldLabel: 'Outstanding Management Fee', reference: 'amount', name: 'amount', flex: 5, style: 'padding-left: 20px;', labelWidth: '10%',
                                                            },
                                                        ]
                                                    }
                                                ]
                                            },
                                        ],
                                    },
                                ],

                            },
                        },

                  ]

            },

        ]
    },
});