Ext.define("snap.view.managementfee.ManagementFeeDeduction", {
    extend: "snap.view.gridpanel.Base",
    xtype: "managementfeededuction",
    permissionRoot: '/root/' + PROJECTBASE.toLowerCase() + '/managementfeereport',

    requires: [
        'snap.store.ManagementFeeDeduction',
        'snap.model.ManagementFeeDeduction',
        'snap.view.managementfee.ManagementFeeReportController',
        'snap.view.managementfee.ManagementFeeReportModel',
    ],

    store: { type: 'ManagementFeeDeduction' },
    controller: 'managementfeereport-managementfeereport',

    viewModel: {
        type: 'managementfeereport-managementfeereport'
    },
    detailViewWindowHeight: 500,

    enableFilter: true,
    toolbarItems: [
        'detail', '|' ,'filter', '|' ,
        {
            xtype: 'datefield', fieldLabel: 'Start', reference: 'startDate', itemId: 'startDate', format: 'd/m/Y', menu: { items: [] }, name: 'startdateOn', labelWidth: 'auto'
        },
        {
            xtype: 'datefield', fieldLabel: 'End', reference: 'endDate', itemId: 'endDate', format: 'd/m/Y', menu: { items: [] }, name: 'enddateOn', labelWidth: 'auto'
        },
        {
            iconCls: 'x-fa fa-redo-alt', text: 'Filter Date', tooltip: 'Filter Date', handler: 'getDateRangeManagementFee', showToolbarItemText: true,
        },
        {
            iconCls: 'x-fa fa-times-circle', text: 'Clear Date', tooltip: 'Clear Date', handler: 'clearDateRange', showToolbarItemText: true,
        },
        {
            style : "width : 130px;", text: 'Download', tooltip: 'Export Data', iconCls: 'x-fa fa-download', handler: 'getPrintReportManagementFeeStatus',  showToolbarItemText: true, printType: 'xlsx', labelWidth:'auto'// printType: pending
        },
    ],
    listeners: {
        afterrender: function () {
            this.store.sorters.clear();
            this.store.sort([{
                property: 'id',
                direction: 'ASC'
            }]);
        }
    },
    columns: [
        { text: 'ID', dataIndex: 'id', filter: { type: 'string' }, minWidth: 100 },
        { text: 'Full Name', dataIndex: 'achfullname', filter: { type: 'string' }, minWidth: 100, flex: 1 },
        { text: 'Gold Account No', dataIndex: 'achaccountholdercode', filter: { type: 'string' }, minWidth: 100, flex: 1 },
        //{ text: 'CIC No.', dataIndex: 'achpartnercusid', filter: { type: 'string' }, minWidth: 100, flex: 1 },
        //{ text: 'Casa Account No.', dataIndex: 'achaccountnumber', filter: { type: 'string' }, minWidth: 100, flex: 1 },
        //{ text: 'Fee (Xau)', dataIndex: 'xau', filter: { type: 'string' }, minWidth: 100, flex: 1 },
        { text: 'Management Fee Amount (RM)', dataIndex: 'amount', filter: { type: 'string' }, minWidth: 100, flex: 1 , renderer: Ext.util.Format.numberRenderer('0.00')},
        { text: 'Deduction Type', dataIndex: 'deducttype', minWidth: 100, flex: 1,
			filter: {
                type: 'combo',
                store: [
                    ['MONTHLY DEDUCT', 'MONTHLY DEDUCT'],
                    ['AUTO CRONJOB DEDUCT', 'AUTO CRONJOB DEDUCT'],
                    ['MANUAL DEDUCT', 'MANUAL DEDUCT'],
                ],
            },
            renderer: function (value, rec) {
                if (value == 'MONTHLY DEDUCT') return '<span data-qtitle="MONTHLY DEDUCT" data-qwidth="200" '+
                'data-qtip="MONTHLY DEDUCT">'+
                 "MONTHLY DEDUCT" +'</span>';
                else if (value == 'AUTO CRONJOB DEDUCT') return '<span data-qtitle="AUTO CRONJOB DEDUCT" data-qwidth="200" '+
                'data-qtip="AUTO CRONJOB DEDUCT">'+
                 "AUTO CRONJOB DEDUCT" +'</span>';
				 else if (value == 'MANUAL DEDUCT') return '<span data-qtitle="MANUAL DEDUCT" data-qwidth="200" '+
                'data-qtip="MANUAL DEDUCT">'+
                 "MANUAL DEDUCT" +'</span>';
                else return '<span data-qtitle="Unidentified" data-qwidth="200" '+
                'data-qtip="Unidentified">'+
                 "Unidentified" +'</span>';
            },
		},
		{ text: 'API Error Message', dataIndex: 'code', filter: { type: 'string' }, minWidth: 100, flex: 1 },
        { text: 'Deduction Date', dataIndex: 'createdon', xtype: 'datecolumn', format: 'Y-m-d H:i:s', filter: { type: 'date' }, inputType: 'hidden', flex: 1 },
        { text: 'Status', dataIndex: 'status', minWidth: 130,
            filter: {
                type: 'combo',
                store: [
                    ['1', 'Success'],
                    ['2', 'Failed'],
                ],
            },
            renderer: function (value, rec) {
                if (value == '1') return '<span data-qtitle="Success" data-qwidth="200" '+
                'data-qtip="Success">'+
                 "Success" +'</span>';
                else if (value == '2') return '<span data-qtitle="Failed" data-qwidth="200" '+
                'data-qtip="Failed">'+
                 "Failed" +'</span>';
                else return '<span data-qtitle="Unidentified" data-qwidth="200" '+
                'data-qtip="Unidentified">'+
                 "Unidentified" +'</span>';
            },
        }
    ],
});
