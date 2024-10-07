Ext.define('snap.view.otcremarks.OtcRegisterRemarks', {
    extend: 'snap.view.gridpanel.Base',
    xtype: 'otcregisterremarks',

    requires: [
        'snap.store.RegisterRemarks',
        'snap.model.RegisterRemarks',
        'snap.view.otcremarks.OtcRegisterRemarksController',
        'snap.view.otcremarks.OtcRegisterRemarksModel',
    ],

    detailViewWindowHeight: 500,
    store: { type: 'RegisterRemarks' },
    controller: 'otcregisterremarks-otcregisterremarks',
    viewModel: {
        type: 'otcregisterremarks-otcregisterremarks'
    },
    partnercode: '',
    enableFilter: true,
    toolbarItems: [
        'detail', '|', 'filter', '|',
    ],

    columns: [
        // { text: 'ID', dataIndex: 'id', filter: { type: 'int' }, inputType: 'hidden', hidden: true },
        { text: 'ID', dataIndex: 'id', filter: { type: 'int' }, },
        {
            text: "Type",
            dataIndex: "type",
            flex: 2,

            filter: {
                type: "combo",
                store: [
                    ["Registration", "Registration"],
                    ["Buy", "Buy"],
                    ["Sell", "Sell"],
                    ["Conversion", "Conversion"],
                ],
            },
            renderer: function (value, rec) {
                if (value == "Registration") return "Registration";
                else if (value == "Buy") return "Buy";
                else if (value == "Sell") return "Sell";
                else return "Conversion";
            },
        },
        { text: 'Identity No.', dataIndex: 'mykadno', filter: { type: 'string' }, Width: 150,},
        { text: 'Remarks', dataIndex: 'remarks', filter: { type: 'string' }, Width: 400,},
        {
            text: 'Status', dataIndex: 'status', Width: 130,
            filter: {
                type: 'combo',
                store: [
                    ['0', 'Pending Approval'],                    
                    ['1', 'Approved'],
                    ['2', 'Rejected'],
                ],
            },
            renderer: function (value, rec) {
                if (value == '0') return 'Pending Approval';
                else if (value == '1') return 'Approved';
                else if (value == '2') return 'Rejected';
            },
        },
        { text: 'Requested by', dataIndex: 'createdbyname', filter: { type: 'int' } },
        { text: 'Requested On', dataIndex: 'createdon', xtype: "datecolumn", format: "Y-m-d H:i:s", filter: { type: "date"}, inputType: 'hidden', hidden: true }, 
        { text: 'Approved by', dataIndex: 'modifiedbyname', filter: { type: 'int' }, inputType: 'hidden'},
        { text: 'Approved On', dataIndex: 'modifiedon', xtype: "datecolumn", format: "Y-m-d H:i:s", filter: { type: "date"}, inputType: 'hidden', hidden: true },
    ],
});