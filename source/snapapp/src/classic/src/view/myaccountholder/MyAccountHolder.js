Ext.define('snap.view.myaccountholder.MyAccountHolder', {
    extend: 'snap.view.gridpanel.Base',
    xtype: 'myaccountholderview',

    requires: [
        'snap.store.MyAccountHolder',
        'snap.model.MyAccountHolder',
        'snap.view.myaccountholder.MyAccountHolderController',
        'snap.view.myaccountholder.MyAccountHolderModel',
    ],
    //permissionRoot: '/root/bmmb/profile',
    store: { type: 'MyAccountHolder' },
    controller: 'myaccountholder-myaccountholder',

    viewModel: {
        type: 'myaccountholder-myaccountholder'
    },
    checkActionPermission: function (view, record) {
        var selected = false;
        parCode = this.partnerCode;
        parCode = parCode.toLowerCase();
        Ext.Array.each(view.getSelectionModel().getSelection(), function (items) {
            if (items.getId() == record.getId()) {
                selected = true;
                return false;
            }
        });

        var btnApprovePep = Ext.ComponentQuery.query('#approvePep')[0];
        btnApprovePep.disable();
        var approvalPermission = snap.getApplication().hasPermission('/root/'+ parCode +'/approval/approve');
        if (approvalPermission == true && selected && record.data.ispep == 1) {
            btnApprovePep.enable();
        }

        var suspendBtn = Ext.ComponentQuery.query('#suspendBtn')[0];
        suspendBtn.disable();
        var suspendPermission = snap.getApplication().hasPermission('/root/'+ parCode +'/profile/suspend');

        if (suspendPermission == true && selected && record.data.status == 1) {
            suspendBtn.enable();
        }

        var unsuspendBtn = Ext.ComponentQuery.query('#unsuspendBtn')[0];
        unsuspendBtn.disable();
        var unsuspendPermission = snap.getApplication().hasPermission('/root/'+ parCode +'/profile/unsuspend');

        if (unsuspendPermission == true && selected && record.data.status == 4) {
            unsuspendBtn.enable();
        }

        var closeBtn = Ext.ComponentQuery.query('#closeBtn')[0];
        closeBtn.disable();
        var closePermission = snap.getApplication().hasPermission('/root/'+ parCode +'/accountclosure/close');

        if (closePermission == true && selected && record.data.status == 1) {
            closeBtn.enable();
        }

        var btnApproveEkyc = Ext.ComponentQuery.query('#approveEkyc')[0];
        btnApproveEkyc.disable();
        if (selected && record.data.kycstatus == 7) {
            btnApproveEkyc.enable();
        }

        var btnActivateDormant = Ext.ComponentQuery.query('#activateDormant')[0];
        btnActivateDormant.disable();
        if (selected && record.data.status == 5) {
            btnActivateDormant.enable();
        }

    },
    detailViewWindowHeight: 500,

    enableFilter: true,
    toolbarItems: [
        'detail', '|', 'filter', '|',
        { reference: 'profileBtn', handler: 'onViewAccountHolder', text: 'CIF', showToolbarItemText: true, itemId: 'profileBtn', tooltip: 'Account Holder Profile', viewType: 'myprofileview', iconCls: 'x-fa fa-user', validSelection: 'single' },
        { reference: 'approvePep', showToolbarItemText: true, itemId: 'approvePep', tooltip: 'Approve PEP', iconCls: 'x-fa fa-check-square', handler: 'approvePep', validSelection: 'single' },
        { reference: 'suspendBtn', showToolbarItemText: true, itemId: 'suspendBtn', tooltip: 'Suspend', iconCls: 'x-fa fa-lock', handler: 'onSuspendAccountHolder', validSelection: 'single' },
        { reference: 'unsuspendBtn', showToolbarItemText: true, itemId: 'unsuspendBtn', tooltip: 'Unsuspend', iconCls: 'x-fa fa-unlock', handler: 'onUnsuspendAccountHolder', validSelection: 'single' },
        { reference: 'closeBtn', showToolbarItemText: true, itemId: 'closeBtn', tooltip: 'Close', iconCls: 'x-fa fa-eraser', handler: 'onCloseAccountHolder', validSelection: 'single' },
        { reference: 'approveEkyc', showToolbarItemText: true, itemId: 'approveEkyc', tooltip: 'Approve EKYC', iconCls: 'x-fa fa-thumbs-up', handler: 'approveEkyc', validSelection: 'single' },
        { reference: 'activateDormantBtn', showToolbarItemText: true, itemId: 'activateDormant', tooltip: 'Activate Closed Accounts', iconCls: 'x-fa fa-power-off', handler: 'activateDormant', validSelection: 'single' },
        // Date functions
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
            iconCls: 'x-fa fa-times-circle', style : "width : 130px;",  text: 'Clear Date', tooltip: 'Clear Date', handler: 'clearDateRange', showToolbarItemText: true, labelWidth:'auto'
        },
        {
            style : "width : 130px;", text: 'Download', tooltip: 'Export Data', iconCls: 'x-fa fa-download', handler: 'getPrintReport',  showToolbarItemText: true, printType: 'xlsx', labelWidth:'auto'// printType: pending
        },
    ],
    listeners: {
        afterrender: function () {
            this.store.sorters.clear();
            this.store.sort([{
                property: 'id',
                direction: 'DESC'
            }]);

            // Init button
            approveEkycButton = this.lookupReference('approveEkyc');
            approveEkycButton.setHidden(true);

            if(this.partnerCode == 'BMMB'){
                // Check for type 
                // By right is only for trader
                if ("Operator" == snap.getApplication().usertype || "Sale" == snap.getApplication().usertype  || "Trader" == snap.getApplication().usertype ){
                    
                    approveEkycButton.setHidden(false);
                
                } 
            }
        },
        cellclick: function (view, cell, cellIndex, record, row, rowIndex, e) {   
            var me = this;
            me.checkActionPermission(view, record);
        },
        beforeitemkeyup: function (view, record, item, index, e) {            
            var me = this;
            me.checkActionPermission(view, record);
        },
    },

    columns: [
        { text: 'ID', dataIndex: 'id', filter: { type: 'string' }, hidden: true, minWidth: 100, flex: 1 },
        { text: 'Amount Balance', dataIndex: 'amountbalance',exportdecimal:2, filter: { type: 'string' }, hidden: true, minWidth: 130, flex: 1, renderer: Ext.util.Format.numberRenderer('0.00') },
        { text: 'Account Code', dataIndex: 'accountholdercode', filter: { type: 'string' }, minWidth: 130, flex: 1 },
        { text: 'Full Name', dataIndex: 'fullname', filter: { type: 'string' }, minWidth: 130, flex: 1 },
        { text: 'My Kad No', dataIndex: 'mykadno', filter: { type: 'string' }, minWidth: 130, flex: 1 },
        { text: 'Partner Customer ID', dataIndex: 'partnercusid', filter: { type: 'string' }, hidden: true, minWidth: 130, flex: 1 },
        { text: 'Email', dataIndex: 'email', filter: { type: 'string' }, hidden: true, minWidth: 130, flex: 1 },
        { text: 'Phone Number', dataIndex: 'phoneno', filter: { type: 'string' }, hidden: true, minWidth: 130, flex: 1 },
        { text: 'Preferred Lang', dataIndex: 'preferredlang', filter: { type: 'string' }, hidden: true, minWidth: 130, flex: 1 },
        { text: 'Occupation', dataIndex: 'occupation', filter: { type: 'string' }, hidden: true, minWidth: 130, flex: 1 },
        { text: 'Occupation Category ID', dataIndex: 'occupationcategoryid', filter: { type: 'string' }, hidden: true, minWidth: 130, flex: 1 },
        { text: 'Salesperson Code', dataIndex: 'referralsalespersoncode', filter: { type: 'string' }, hidden: true, minWidth: 130, flex: 1 },
        { text: 'Referral Branch Code', dataIndex: 'referralbranchcode', filter: { type: 'string' }, hidden: true, minWidth: 130, flex: 1 },
        { text: 'Pin Code', dataIndex: 'pincode', filter: { type: 'string' }, minWidth: 130, hidden: true, flex: 1 },
        { text: 'SAP Buy Code', dataIndex: 'sapacebuycode', filter: { type: 'string' }, minWidth: 130, hidden: true, flex: 1 },
        { text: 'SAP Sell Code', dataIndex: 'sapacesellcode', filter: { type: 'string' }, minWidth: 130, hidden: true, flex: 1 },
        { text: 'Bank Name', dataIndex: 'bankname', filter: { type: 'string' }, minWidth: 130, hidden: true, flex: 1 },
        { text: 'Bank Account Name', dataIndex: 'accountname', filter: { type: 'string' }, hidden: true, minWidth: 130, flex: 1 },
        { text: 'Bank Account Number', dataIndex: 'accountnumber', filter: { type: 'string' }, hidden: true, minWidth: 130, flex: 1 },
        { text: 'Nok Full Name', dataIndex: 'nokfullname', filter: { type: 'string' }, hidden: true, minWidth: 130, flex: 1 },
        { text: 'NoK Mykad No', dataIndex: 'nokmykadno', filter: { type: 'string' }, hidden: true, minWidth: 130, flex: 1 },
        { text: 'Nok Bank Name', dataIndex: 'nokbankname', filter: { type: 'string' }, hidden: true, minWidth: 130, flex: 1 },
        { text: 'Nok Account No', dataIndex: 'nokaccountnumber', filter: { type: 'string' }, hidden: true, minWidth: 130, flex: 1 },
        { text: 'Investment Made', dataIndex: 'investmentmade',   filter: {
            type: 'combo',
            store: [
                ['0', 'No'],
                ['1', 'Yes'],
            ],
        },
        renderer: function (value, rec) {
            if (value == '0') return 'No';
            else if (value == '1') return 'Yes';
            else return 'Unidentified';
        }, hidden: true, minWidth: 130, flex: 1, },
        { text: 'Xau Balance', dataIndex: 'xaubalance', exportdecimal:3, filter: { type: 'string' }, minWidth: 130, flex: 1, renderer: Ext.util.Format.numberRenderer('0.000') },
        // { 
        //     text: 'loan total', dataIndex: 'loantotal', exportdecimal:3, filter: { type: 'string' },  align: 'right', minWidth: 130, renderer: Ext.util.Format.numberRenderer('0,000.000'),
        //     editor: {    //field has been deprecated as of 4.0.5
        //         xtype: 'numberfield',
        //         decimalPrecision: 3
        //     } 
        // },
        // { 
        //     text: 'loan balance', dataIndex: 'loanbalance', exportdecimal:3, filter: { type: 'string' },  align: 'right', minWidth: 130, renderer: Ext.util.Format.numberRenderer('0,000.000'),
        //     editor: {    //field has been deprecated as of 4.0.5
        //         xtype: 'numberfield',
        //         decimalPrecision: 3
        //     } 
        // },
        // { text: 'Loan approved on', dataIndex: 'loanapprovedate', xtype: 'datecolumn', format: 'Y-m-d H:i:s', filter: { type: 'date' }, inputType: 'hidden', minWidth: 100 },
        // { text: 'Approved by', dataIndex: 'loanapproveby', filter: { type: 'string' }, minWidth: 130, flex: 1 },
        // {
        //     text: 'Loan Status', dataIndex: 'loanstatus', minWidth: 100,
        //     filter: {
        //         type: 'combo',
        //         store: [
        //             ['0', 'No'],
        //             ['1', 'Approved'],
        //             ['2', 'Settled'],
        //         ],
        //     },
        //     renderer: function (value, rec) {
        //         if (value == '0') return 'No';
        //         else if (value == '1') return 'Approved';
        //         else if (value == '2') return 'Settled';
        //         else return 'Unidentified';
        //     },
        // },
        // { text: 'Reference Number', dataIndex: 'loanreference', filter: { type: 'string' }, minWidth: 130, flex: 1 },
        {
            text: 'Is PEP', dataIndex: 'ispep', minWidth: 100,
            filter: {
                type: 'combo',
                store: [
                    ['0', 'No'],
                    ['1', 'Yes'],
                ],
            },
            renderer: function (value, rec) {
                if (value == '0') return 'No';
                else if (value == '1') return 'Yes';
                else return 'Unidentified';
            },
        },
        { text: 'Pep Declaration', dataIndex: 'pepdeclaration', filter: { type: 'string' }, hidden: true, minWidth: 130, flex: 1 },
        {
            text: 'Status', dataIndex: 'status', minWidth: 100,

            filter: {
                type: 'combo',
                store: [
                    ['0', 'Inactive'],
                    ['1', 'Active'],
                    ['2', 'Suspended'],
                    ['4', 'Blacklisted'],
                    ['5', 'Closed'],

                ],

            },
            renderer: function (value, rec) {
                if (value == '0') return '<span data-qtitle="Inactive" data-qwidth="200" '+
                'data-qtip="Account Pending Email Activation">'+
                 "Inactive" +'</span>';
                else if (value == '1') return '<span data-qtitle="Active" data-qwidth="200" '+
                'data-qtip="Active Accounts">'+
                 "Active" +'</span>';
                else if (value == '2') return '<span data-qtitle="Suspended" data-qwidth="200" '+
                'data-qtip="Accounts Pending Closure Approval">'+
                 "Suspended" +'</span>';
                else if (value == '4') return '<span data-qtitle="Blacklisted" data-qwidth="200" '+
                'data-qtip="Blacklisted Accounts">'+
                 "Blacklisted" +'</span>';
                else if (value == '5') return '<span data-qtitle="Closed" data-qwidth="200" '+
                'data-qtip="Accounts Successfully Closed">'+
                 "Closed" +'</span>';
                else return '<span data-qtitle="Unidentified" data-qwidth="200" '+
                'data-qtip="Unknown Status">'+
                 "Unidentified" +'</span>';
            },
            // renderer: function (value, rec) {
            //     if (value == '0') return 'Inactive';
            //     else if (value == '1') return 'Active';
            //     else if (value == '2') return 'Suspended';
            //     else if (value == '4') return 'Blacklisted';
            //     else if (value == '5') return 'Closed';
                    
            //     else return 'Unidentified';
            // },
        },
        {
            text: 'PEP Status', dataIndex: 'pepstatus', filter: { type: 'string' }, minWidth: 100, align: 'center',
            filter: {
                type: 'combo',
                store: [
                    ['0', 'Pending'],
                    ['1', 'Passed'],
                    ['2', 'Failed'],
                ],
            },
            renderer: function (val, m, record) {
                // If PEP
                if (record.data.ispep == 1) {
                    if (record.data.pepstatus == 0) {
                        // PEP Status Pending
                        return '<span class="fas fa-spinner fa-spin x-color-warning"></span>';
                    } else if (record.data.pepstatus == 1) {
                        // PEP Status Passed
                        return '<span class="fa fa-circle x-color-success"></span>';
                    } else if (record.data.pepstatus == 2) {
                        // PEP Status Failed
                        return '<span class="fa fa-circle x-color-danger"></span>';
                    } 
                } else {
                    // PEP Status Unidentified
                    return '<span class="fa fa-circle x-color-default"></span>';
                }
            }
        },
        {
            text: 'Is KYC Manually Approved', dataIndex: 'iskycmanualapproved', minWidth: 100,
            filter: {
                type: 'combo',
                store: [
                    ['0', 'No'],
                    ['1', 'Yes'],
                ],
            },
            renderer: function (value, rec) {
                if (value == '0') return 'No';
                else if (value == '1') return 'Yes';
                else return 'Unidentified';
            },
        },
        {
            text: 'KYC Status', dataIndex: 'kycstatus', filter: { type: 'string' }, minWidth: 100, align: 'center',
            filter: {
                type: 'combo',
                store: [
                    ['0', 'Incomplete'],
                    ['1', 'Passed'],
                    ['2', 'Pending'],
                    ['7', 'Failed'],
                ],
            },
            renderer: function (val, m, record) {

                if (record.data.kycstatus == 0) {
                    // eKYC Status Incomplete

                    if (record.data.kycpastday == false) {
                        return '<span class="fa fa-circle x-color-default"></span>';
                    } else {
                        return '<span class="fa fa-circle x-color-warning"></span>';
                    }
                } else if (record.data.kycstatus == 1) {
                    // eKYC Status Passed
                    return '<span class="fa fa-circle x-color-success"></span><span>';
                } else if (record.data.kycstatus == 2) {
                    // eKYC Status Pending
                    return '<span class="fas fa-spinner fa-spin x-color-warning"></span>';

                } else if (record.data.kycstatus == 7) {
                    // eKYC Status Failed
                    return '<span class="fa fa-circle x-color-danger"></span><span>';
                } else {
                    // eKYC Status Unidentified
                    return '<span class="fa fa-circle x-color-default"></span><span>';
                }
            }
        },

        //{ text: 'Amla Status',  dataIndex: 'amlastatus', filter: {type: 'string'} , minWidth:130, flex: 1 },
        {
            text: 'AMLA Status', dataIndex: 'amlastatus', filter: { type: 'string' }, minWidth: 100, align: 'center',
            filter: {
                type: 'combo',
                store: [
                    ['0', 'Pending'],
                    ['1', 'Passed'],
                    ['2', 'Failed'],
                ],
            },
            renderer: function (val, m, record) {
                // If KYC pass
                if (record.data.kycstatus == 1) {
                    if (record.data.amlastatus == 0) {
                        // AMLA Status Pending
                        return '<span class="fas fa-spinner fa-spin x-color-warning"></span>';
                    } else if (record.data.amlastatus == 1) {
                        // AMLA Status Passed
                        return '<span class="fa fa-circle x-color-success"></span><span>';
                    } else if (record.data.amlastatus == 2) {
                        // AMLA Status Failed
                        return '<span class="fa fa-circle x-color-danger"></span><span>';
                    } 
                } else {
                    // AMLA Status Unidentified
                    return '<span class="fa fa-circle x-color-default"></span><span>';
                }       
            }
        },
        { text: 'Status Remarks', dataIndex: 'statusremarks', filter: { type: 'string' }, hidden: true, minWidth: 130, flex: 1 },
        {
            text: 'Dormant', dataIndex: 'dormant', filter: { type: 'string' }, minWidth: 100, align: 'center',
            filter: {
                type: 'combo',
                store: [
                    ['1', 'Yes'],
                    ['0', 'No'],                    
                ],
            },
            renderer: function (val, m, record) {
                if (record.data.dormant) {
                    return '<span class="fa fa-circle x-color-danger"></span><span>';
                } else {                    
                    return '<span class="fa fa-circle x-color-success"></span><span>';
                }       
            }
        },
        { text: 'Campaign Code', dataIndex: 'campaigncode', filter: { type: 'string' }, minWidth: 130, flex: 1 },
        { text: 'Password Modified', dataIndex: 'passwordmodified', xtype: 'datecolumn', format: 'Y-m-d H:i:s', filter: { type: 'date' }, inputType: 'hidden', hidden: true, minWidth: 100 },
        { text: 'Last Login on', dataIndex: 'lastloginon', xtype: 'datecolumn', format: 'Y-m-d H:i:s', filter: { type: 'date' }, inputType: 'hidden', hidden: true, minWidth: 100 },
        { text: 'Last Login IP', dataIndex: 'lastloginip', filter: { type: 'string' }, hidden: true, minWidth: 130, flex: 1 },
        { text: 'Verified on', dataIndex: 'verifiedon', xtype: 'datecolumn', format: 'Y-m-d H:i:s', filter: { type: 'date' }, inputType: 'hidden', minWidth: 100, hidden: true, },

        { text: 'Created on', dataIndex: 'createdon', xtype: 'datecolumn', format: 'Y-m-d H:i:s', filter: { type: 'date' }, inputType: 'hidden', minWidth: 100 },
        { text: 'Modified on', dataIndex: 'modifiedon', xtype: 'datecolumn', format: 'Y-m-d H:i:s', filter: { type: 'date' }, inputType: 'hidden', minWidth: 100, hidden: true, },
        { text: 'Created by', dataIndex: 'createdbyname', filter: { type: 'string' }, inputType: 'hidden', hidden: true, minWidth: 100 },
        { text: 'Modified by', dataIndex: 'modifiedbyname', filter: { type: 'string' }, inputType: 'hidden', hidden: true, minWidth: 100 },

    ],

    //////////////////////////////////////////////////////////////
    /// View properties settings
    ///////////////////////////////////////////////////////////////
    formSuspendAccountHolder: {
        formDialogWidth: 950,
        controller: 'myaccountholder-myaccountholder',
        formDialogTitle: 'Suspend Account',

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
            {
                items: [
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
                                        xtype: 'textarea', fieldLabel: '', name: 'remarks', flex: 2, style: 'padding-left: 20px;', id: 'pepremarks'
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
                                        xtype: 'textarea', fieldLabel: '', name: 'remarks', flex: 2, style: 'padding-left: 20px;', id: 'pepremarks'
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
                                        xtype: 'textarea', fieldLabel: '', name: 'remarks', flex: 2, style: 'padding-left: 20px;', id: 'pepremarks'
                                    },
                                ]
                            },
                        ]
                    }
                ],
            },
        ],  
    },

    formPepApproval: {
        formDialogWidth: 950,
        controller: 'myaccountholder-myaccountholder',

        formDialogTitle: 'PEP Approval',

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
                                        xtype: 'displayfield', allowBlank: false, fieldLabel: 'Name', reference: 'accountholderpepname', name: 'accountholderpepname', flex: 5, style: 'padding-left: 20px;', labelWidth: '10%',
                                    },
                                    {
                                        xtype: 'displayfield', allowBlank: false, fieldLabel: 'IC', reference: 'accountholderpepic', name: 'accountholderpepic', flex: 5, style: 'padding-left: 20px;', labelWidth: '10%',
                                    },
                                ]
                            }
                        ]
                    },
                    {
                        xtype: 'form',
                        reference: 'searchresultsforpep-form',
                        border: false,
                        items: [
                            {
                                title: '',
                                flex: 13,
                                xtype: 'mypepmatchdataview',
                                reference: 'mypepematchdata',
                                enablePagination: false

                            },
                        ],
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
                                        xtype: 'textarea', fieldLabel: '', name: 'remarks', flex: 2, style: 'padding-left: 20px;', id: 'pepremarks'
                                    },
                                ]
                            },
                        ]
                    }
                ],
            },
        ],

       
    },

    formEkycApproval: {
        formDialogWidth: 950,
        controller: 'myaccountholder-myaccountholder',

        formDialogTitle: 'EKYC Approval',

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
                                        xtype: 'displayfield', allowBlank: false, fieldLabel: 'Name', reference: 'accountholderpepname', name: 'accountholderpepname', flex: 5, style: 'padding-left: 20px;', labelWidth: '10%',
                                    },
                                    {
                                        xtype: 'displayfield', allowBlank: false, fieldLabel: 'IC', reference: 'accountholderpepic', name: 'accountholderpepic', flex: 5, style: 'padding-left: 20px;', labelWidth: '10%',
                                    },
                                ]
                            }
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
                                        xtype: 'textarea', fieldLabel: '', name: 'remarks', flex: 2, style: 'padding-left: 20px;', id: 'pepremarks'
                                    },
                                ]
                            },
                        ]
                    }
                ],
            },
        ],

       
    },

    // Upload member list form 
    uploadmemberlistform: {
        controller: 'collection-poscollection',

        formDialogWidth: 700,
        formDialogHeight: 400,

        formDialogTitle: "Member List",

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
                reference: "grnposlist-form",
                items: [
                    {
                        xtype: 'container',
                        layout: 'hbox',
                        items: [
                            
                            {
                                flex:1,
                                xtype: 'displayfield',
                                value : "<p>&#9679; Please verify and get approval before upload </p>",
                                margin: '0 0 0 20',
                                forceSelection: true,
                                enforceMaxLength: true,
                                readOnly : true,
                            },{
                                flex:1,
                                xtype: 'displayfield',
                                value : "<p>&#9679; Minimum 1 member record is required.</p>",
                                margin: '0 0 0 20',
                                forceSelection: true,
                                enforceMaxLength: true,
                                readOnly : true,
                            },
                            { xtype: 'panel', flex : 1},
                        ]
                    },
                    {
                        xtype: 'container',
                        layout: 'hbox',
                        items: [
                            { xtype: 'filefield',fieldLabel: 'File (Required)', name: 'grnposlist', width: '90%', flex: 4, allowBlank: false, reference: 'grnposlist_field' },
                        ]
                    },
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
