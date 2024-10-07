Ext.define('snap.view.myregistration.MyRegistration', {
    extend: 'snap.view.gridpanel.Base',
    xtype: 'myregistrationview',
    requires: [
        'snap.store.MyRegistration',
        'snap.model.MyRegistration',
        'snap.view.myregistration.MyRegistrationController',
        'snap.view.myregistration.MyRegistrationModel',
    ],
    detailViewWindowHeight: 500,
    permissionRoot: '/root/bmmb/report/registration',
    store: { type: 'MyRegistration' },
    controller: 'myregistration-myregistration',
    viewModel: {
        type: 'myregistration-myregistration'
    },
    partnercode: '',
    enableFilter: true,
    status: 'ALL',
    toolbarItems: [
        'detail', '|', 'filter', '|',
        {
            xtype: 'datefield', fieldLabel: 'Start', reference: 'startDate', itemId: 'startDate', format: 'd/m/Y', menu: { items: [] }, name: 'startdateOn', labelWidth: 'auto'
        },
        {
            xtype: 'datefield', fieldLabel: 'End', reference: 'endDate', itemId: 'endDate', format: 'd/m/Y', menu: { items: [] }, name: 'enddateOn', labelWidth: 'auto'
        },
        {
            text: 'Print', tooltip: 'Print', iconCls: 'x-fa fa-print', reference: 'dailytransactionreport', handler: 'getPrintReport', showToolbarItemText: true, printType: 'xlsx', // printType: pending
        },
        {
            iconCls: 'x-fa fa-redo-alt', text: 'Filter Date', tooltip: 'Filter Date', handler: 'getDateRange', showToolbarItemText: true,
        },
        {
            iconCls: 'x-fa fa-times-circle', text: 'Clear Date', tooltip: 'Clear Date', handler: 'clearDateRange', showToolbarItemText: true,
        },
    ],
    listeners: {
        afterrender: function () {
            this.getReferences().startDate.setValue(new Date())
            this.getReferences().endDate.setValue(new Date())

            startDate = this.getReferences().startDate.getValue()
            endDate = this.getReferences().endDate.getValue()

            // this.store.addFilter(
            //     {
            //         property: "createdon", type: "date", operator: "BETWEEN", value: [startDate, endDate]
            //     },
            // )
            
            this.store.sorters.clear();
            this.store.sort([{
                property: 'id',
                direction: 'DESC'
            }]);
            var columns = this.query('gridcolumn');
            columns.find(obj => obj.text === 'ID').setVisible(false);
        }
    },

    viewConfig: {
        getRowClass: function (record) {
            record.data.price = parseFloat(record.data.price).toFixed(3);
            record.data.byweight = record.data.byweight == '1' ? 'Yes' : '';
            record.data.amount = parseFloat(record.data.amount).toFixed(3);
        },
    },

    columns: [
        { text: 'ID', dataIndex: 'id', filter: { type: 'string' }, hidden: true },
        { text: 'Partner Code', dataIndex: 'partnercode', filter: { type: 'string' }, minWidth: 130, flex: 1 },
        { text: 'Partner Name', dataIndex: 'partnername', filter: { type: 'string' }, minWidth: 130, flex: 1 },
        { text: 'Account Code', dataIndex: 'accountholdercode', filter: { type: 'string' }, minWidth: 130, flex: 1 },
        { text: 'Full Name', dataIndex: 'fullname', filter: { type: 'string' }, minWidth: 130, flex: 1 },
        { text: 'NRIC', dataIndex: 'mykadno', filter: { type: 'string' }, minWidth: 130, flex: 1 },
        { text: 'Email', dataIndex: 'email', filter: { type: 'string' }, hidden: true, minWidth: 130, flex: 1 },
        { text: 'Phone Number', dataIndex: 'phoneno', filter: { type: 'string' }, hidden: true, minWidth: 130, flex: 1 },
        { text: 'Occupation', dataIndex: 'occupationcategory', filter: { type: 'string' }, hidden: true, minWidth: 130, flex: 1 },
        { text: 'Occupation Subcategory', dataIndex: 'occupationsubcategory', filter: { type: 'string' }, hidden: true, minWidth: 130, flex: 1 },
        { text: 'Address Line 1', dataIndex: 'addressline1', filter: { type: 'string' }, minWidth: 130, flex: 1 },
        { text: 'Address Line 2', dataIndex: 'addressline2', filter: { type: 'string' }, minWidth: 130, flex: 1 },
        { text: 'Postcode', dataIndex: 'addresspostcode', filter: { type: 'string' }, minWidth: 130, flex: 1 },
        { text: 'City', dataIndex: 'addresscity', filter: { type: 'string' }, minWidth: 130, flex: 1 },
        { text: 'State', dataIndex: 'addressstate', filter: { type: 'string' }, minWidth: 130, flex: 1 },
        {
            text: 'PEP Status', dataIndex: 'pepstatus', filter: { type: 'string' }, minWidth: 100, align: 'center', renderer: function (val, m, record) {

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
                    return '-';
                }
            }
        },
        {
            text: 'KYC Status', dataIndex: 'kycstatus', filter: { type: 'string' }, minWidth: 100, align: 'center', renderer: function (val, m, record) {

                if (record.data.kycstatus == 0) {
                    // eKYC Status Incomplete
                    return '<span class="fa fa-circle x-color-warning"></span>';
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
        {
            text: 'AMLA Status', dataIndex: 'amlastatus', filter: { type: 'string' }, minWidth: 100, align: 'center', renderer: function (val, m, record) {

                if (record.data.amlastatus == 0) {
                    // AMLA Status Pending
                    return '<span class="fas fa-spinner fa-spin x-color-warning"></span>';
                } else if (record.data.amlastatus == 1) {
                    // AMLA Status Passed
                    return '<span class="fa fa-circle x-color-success"></span><span>';
                } else if (record.data.amlastatus == 2) {
                    // AMLA Status Failed
                    return '<span class="fa fa-circle x-color-danger"></span><span>';
                } else {
                    // AMLA Status Unidentified
                    return '<span class="fa fa-circle x-color-default"></span><span>';
                }       
            }
        },
        { text: 'PEP Remarks', dataIndex: 'statusremarks', filter: { type: 'string' }, hidden: true, minWidth: 130, flex: 1 },
        { text: 'AMLA Source', dataIndex: 'amlasourcetype', filter: { type: 'string' }, hidden: true, minWidth: 130, flex: 1 },
        { text: 'KYC Remarks', dataIndex: 'kycremarks', filter: { type: 'string' }, hidden: true, minWidth: 130, flex: 1 },
        { text: 'Created on', dataIndex: 'createdon', xtype: 'datecolumn', format: 'Y-m-d H:i:s', filter: { type: 'date' }, inputType: 'hidden', minWidth: 100 },
    ],
});
