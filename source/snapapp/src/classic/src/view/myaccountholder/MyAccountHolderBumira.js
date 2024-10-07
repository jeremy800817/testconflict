Ext.define('snap.view.myaccountholder.MyAccountHolderBumira', {
    extend: 'snap.view.myaccountholder.MyAccountHolderLoan',
    xtype: 'myaccountholderbumiraview',

    requires: [
        'snap.store.MyAccountHolder',
        'snap.model.MyAccountHolder',
        'snap.view.myaccountholder.MyAccountHolderController',
        'snap.view.myaccountholder.MyAccountHolderModel',
    ],
    toolbarItems: [
        'detail', '|', 'filter', '|',
        { reference: 'profileBtn', handler: 'onViewAccountHolder', text: 'CIF', showToolbarItemText: true, itemId: 'profileBtn', tooltip: 'Account Holder Profile', viewType: 'myprofileview', iconCls: 'x-fa fa-user', validSelection: 'single' },
        { reference: 'approvePep', showToolbarItemText: true, itemId: 'approvePep', tooltip: 'Approve PEP', iconCls: 'x-fa fa-check-square', handler: 'approvePep', validSelection: 'single' },
        { reference: 'suspendBtn', showToolbarItemText: true, itemId: 'suspendBtn', tooltip: 'Suspend', iconCls: 'x-fa fa-lock', handler: 'onSuspendAccountHolder', validSelection: 'single' },
        { reference: 'unsuspendBtn', showToolbarItemText: true, itemId: 'unsuspendBtn', tooltip: 'Unsuspend', iconCls: 'x-fa fa-unlock', handler: 'onUnsuspendAccountHolder', validSelection: 'single' },
        { reference: 'closeBtn', showToolbarItemText: true, itemId: 'closeBtn', tooltip: 'Close', iconCls: 'x-fa fa-eraser', handler: 'onCloseAccountHolder', validSelection: 'single' },
        { reference: 'updateLoanBtn', showToolbarItemText: true, itemId: 'updateLoanBtn', tooltip: 'Update Loan', iconCls: 'x-fa fa-list-alt', handler: 'onManualUpdateLoan', validSelection: 'single' },
        {
            reference: 'uploadLoanBtn', showToolbarItemText: true, text: 'Upload File', itemId: 'uploadLoanBtn', tooltip: 'Upload Loan', iconCls: 'x-fa fa-upload', handler: 'onFtpUploadLoan',
            listeners : {
                afterrender : function(srcCmp) {
                    Ext.create('Ext.tip.ToolTip', {
                        target : srcCmp.getEl(),
                        html : 'Upload Account Holder Loan'
                    });
                    // srcCmp.disable();
                }
            }
        },  
        {
            reference: 'uploadMemberList', showToolbarItemText: true, text: 'Upload Member List', itemId: 'uploadMemberBtn', tooltip: 'Upload Member', iconCls: 'x-fa fa-upload', handler: 'onFtpUploadMember',
            listeners : {
                afterrender : function(srcCmp) {
                    Ext.create('Ext.tip.ToolTip', {
                        target : srcCmp.getEl(),
                        html : 'Upload Member List'
                    });
                    // srcCmp.disable();
                }
            }
        },  
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
            style : "width : 130px;", text: 'Download', tooltip: 'Export Data', iconCls: 'x-fa fa-download', handler: 'getPrintReportKtp',  showToolbarItemText: true, printType: 'xlsx', labelWidth:'auto'// printType: pending
        },
    ],
    permissionRoot: '/root/bumira/profile',
    store: {
        type: 'MyAccountHolder',
        proxy: {
            type: 'ajax',
            url: 'index.php?hdl=myaccountholder&action=list&partnercode=BUMIRA',
            reader: {
                type: 'json',
                rootProperty: 'records',
            }
        },
    },
    partnerCode: 'BUMIRA',
    controller: 'myaccountholder-myaccountholder',

    viewModel: {
        type: 'myaccountholder-myaccountholder'
    },

});
