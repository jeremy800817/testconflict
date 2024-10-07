//
Ext.define('snap.view.tag.Tag',{
    extend: 'snap.view.gridpanel.Base',
    xtype: 'tagview',

    requires: [
        'snap.store.Tag',
        'snap.model.Tag',
        'snap.view.tag.TagController',
        'snap.view.tag.TagModel'
    ],
    permissionRoot: '/root/developer/tag',
    store: { type: 'Tag' },

    controller: 'tag-tag',

    viewModel: {
        type: 'tag-tag'
    },

    detailViewWindowHeight: 400,  //Height of the view detail window

    enableFilter: true,
    // gridSelectionModel:'checkboxmodel',
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
        { text: 'ID',  dataIndex: 'id', hidden: true, filter: {type: 'int'}, flex: 1 },
        { text: 'Category',  dataIndex: 'category', flex: 1 ,
            filter: {
                type: 'combo',
                store: [
                    ['PriceSource', 'PriceSource'],
                    ['ProductCategory', 'ProductCategory'],
                    ['Currency', 'Currency'],
                    ['VaultOwner', 'VaultOwner'],
                    ['TradingSchedule', 'TradingSchedule'],
                    ['LogisticVendor', 'LogisticVendor'],
                ],
                renderer: function(value, rec){
                    if(value=='PriceSource') return 'PriceSource';
                    else if(value=='ProductCategory') return 'ProductCategory';
                    else if(value=='Currency') return 'Currency';
                    else if(value=='VaultOwner') return 'VaultOwner';
                    else if(value=='LogisticVendor') return 'LogisticVendor';
                    else return 'TradingSchedule';
                },
            },
        },
        { text: 'Code', dataIndex: 'code', filter: {type: 'string'}, flex: 1 },
        { text: 'Description', dataIndex: 'description', filter: {type: 'string'}, flex: 1 },
        { text: 'Value', dataIndex: 'value', filter: {type: 'string'}, flex: 1 },
		{ text: 'Created on', dataIndex: 'createdon', xtype: 'datecolumn', format: 'Y-m-d H:i:s', filter: {type: 'date'}, inputType: 'hidden', hidden: true, flex: 1 },
		{ text: 'Modified on', dataIndex: 'modifiedon', xtype: 'datecolumn', format: 'Y-m-d H:i:s', filter: {type: 'date'}, inputType: 'hidden', hidden: true, flex: 1 },
        { text: 'Created by', dataIndex: 'createdbyname', filter: {type: 'string'}, inputType: 'hidden', hidden: true,flex: 1 },
		{ text: 'Modified by', dataIndex: 'modifiedbyname', filter: {type: 'string'}, inputType: 'hidden', hidden: true, flex: 1 },
        //{ text: 'System Status',  dataIndex: 'status_text', filter: {type: 'string'} },
        { text: 'System Status',  dataIndex: 'status',  flex: 2,

               filter: {
                   type: 'combo',
                   store: [
                       ['0', 'Inactive'],
                       ['1', 'Active'],

                   ],

               },
               renderer: function(value, rec){
                   if(value=='0') return 'Inactive';
                   else if(value=='1') return 'Active';
                   else return 'Unassigned';
              },
        }
    ],

    //////////////////////////////////////////////////////////////
    /// View properties settings
    ///////////////////////////////////////////////////////////////
    enableDetailView: true,
    detailViewWindowHeight: 500,
	detailViewWindowWidth: 500,
	style: 'word-wrap: normal',
    detailViewSections: {default: 'Properties'},
    detailViewUseRawData: true,

    formConfig: {
        controller: 'tag-tag',
        formDialogTitle: 'Tag',
        formPanelItems: [
        {  inputType: 'hidden', hidden: true, name: 'id' },
        {  xtype: 'hiddenfield', inputType: 'hidden', hidden: true, name: 'status', value: '1' },
        {  xtype: 'combobox', fieldLabel:'Category', store: {type: 'array', fields: ['id', 'code']}, queryMode: 'local', remoteFilter: false, name: 'category', valueField: 'id', displayField: 'code', reference: 'tag_category', forceSelection: true, editable: false },
        {
            reference: 'tag_code',
            fieldLabel: 'Code',
            name: 'code'
        },{
            reference: 'tag_value',
            fieldLabel: 'Value',
            name: 'value'
        },{
            reference: 'tag_description',
            fieldLabel: 'Description',
            name: 'description'
        }]
    },
});
