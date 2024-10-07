Ext.define('snap.view.iprestriction.IPrestriction',{
    extend: 'snap.view.gridpanel.Base',

    requires: [
        'snap.view.iprestriction.IPrestrictionController',
        'snap.view.iprestriction.IPrestrictionModel',
        'snap.model.IPrestriction',
        'snap.store.IPrestriction'
    ],
    xtype: 'iprestrictionview',
    controller: 'iprestriction-iprestriction',
    viewModel: {
        type: 'iprestriction-iprestriction'
    },
    store: {type: 'IPrestriction', autoLoad: true, otcuseractivitylog: true},
    permissionRoot: '/root/system/ip',

    listeners: {
        afterrender: function () {
            this.store.sorters.clear();
            this.store.sort([{
                property: 'id',
                direction: 'DESC'
            }]);
            var columns=this.query('gridcolumn');             
            columns.find(obj => obj.text === 'ID').setVisible(false);
        }
    },
    columns: [
        { text: 'ID', dataIndex: 'id', filter: { type: 'number'}, hidden: true },
        { text: 'Type', dataIndex: 'restricttype', filter: { type: 'string'} },
        { text: 'IP', dataIndex: 'ip', filter: { type: 'string'} },
        { text: 'Remarks', dataIndex: 'remark', filter: { type: 'string'}, flex: 1 },
        { text: 'status', dataIndex: 'status', filter: { type: 'string'} },
        { text: 'Created by', dataIndex: 'createdby', filter: { type: 'string'}, hidden: true },
        { text: 'created On', dataIndex: 'createdon', format: 'Y/m/d H:i:s', filter: { type: 'date'}, hidden: true },
        { text: 'Modified By', dataIndex: 'modifiedby', filter: { type: 'string'} },
        { text: 'Modified On', dataIndex: 'modifiedon', format: 'Y/m/d H:i:s',  filter: { type: 'date'} }
    ],

    formConfig: {
        formDialogTitle: 'IP Restriction',
        enableFormDialogClosable: false,
        formPanelDefaults: {
            labelWidth: 60,
            required: true
        },
        formPanelItems: [
            { inputType: 'hidden', hidden: true, name: 'id' },
            { inputType: 'hidden', hidden: true, name: 'restricttype', value: 'LOGIN' },
            { inputType: 'hidden', hidden: true, name: 'partnertype', value: 'HQ' },
            { inputType: 'hidden', hidden: true, name: 'status', value: '1' },
            { xtype: 'textfield', fieldLabel: 'IP', name: 'ip'},
            { xtype: 'textareafield', fieldLabel: 'Remarks', name: 'remark'}
        ]
    }
});
