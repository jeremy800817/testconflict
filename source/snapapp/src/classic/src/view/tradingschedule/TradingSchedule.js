Ext.define('snap.view.tradingschedule.TradingSchedule',{
    extend: 'snap.view.gridpanel.Base',
    xtype: 'tradingscheduleview',

    requires: [
        'snap.store.TradingSchedule',
        'snap.model.TradingSchedule',
        'snap.view.tradingschedule.TradingScheduleController',
        'snap.view.tradingschedule.TradingScheduleModel'
    ],
    //permissionRoot: '/root/developer/tradingschedule',
    permissionRoot: '/root/system/tradingschedule',

    store: { type: 'TradingSchedule' },

    controller: 'tradingschedule-tradingschedule',

    viewModel: {
        type: 'tradingschedule-tradingschedule'
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
        //{ text: 'Category ID',  dataIndex: 'categoryid', filter: {type: 'int'}, inputType: 'hidden', hidden: true, flex: 1 },
        { text: 'Category Name',  dataIndex: 'categoryname', filter: {type: 'string'} , flex: 1 },
        { text: 'Category Code',  dataIndex: 'categorycode', filter: {type: 'string'} , flex: 1 },
        //{ text: 'Type',  dataIndex: 'type', filter: {type: 'string'} , flex: 1 },
        { text: 'Type',  dataIndex: 'type', flex: 1 ,
            filter: {
                type: 'combo',
                store: [
                    ['DAILY', 'DAILY'],
                    ['WEEKENDS', 'WEEKENDS'],
                    ['WEEKDAYS', 'WEEKDAYS'],
                    ['STOP', 'STOP'],
                ],
                renderer: function(value, rec){
                    if(value=='DAILY') return 'DAILY';
                    else if(value=='WEEKENDS') return 'WEEKENDS';
                    else if(value=='WEEKDAYS') return 'WEEKDAYS';
                    else return 'STOP';
                },
            },
        },
        { text: 'Start At', dataIndex: 'startat', xtype: 'datecolumn', format: 'Y-m-d H:i:s', filter: {type: 'date'}, flex: 1 },
        { text: 'End At', dataIndex: 'endat', xtype: 'datecolumn', format: 'Y-m-d H:i:s', filter: {type: 'date'}, flex: 1 },
		{ text: 'Created on', dataIndex: 'createdon', xtype: 'datecolumn', format: 'Y-m-d H:i:s', filter: {type: 'date'}, inputType: 'hidden', hidden: true, flex: 1 },
		{ text: 'Modified on', dataIndex: 'modifiedon', xtype: 'datecolumn', format: 'Y-m-d H:i:s', filter: {type: 'date'  }, inputType: 'hidden', hidden: true, flex: 1 },
        { text: 'Created by', dataIndex: 'createdbyname', filter: {type: 'string'}, inputType: 'hidden',  hidden: true, flex: 1 },
        { text: 'Modified by', dataIndex: 'modifiedbyname', filter: {type: 'string'}, inputType: 'hidden',  hidden: true, flex: 1 },
        //{ text: 'System Status',  dataIndex: 'status_text', hidden:true, filter: {type: 'string'} }
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
        controller: 'tradingschedule-tradingschedule',
        formDialogTitle: 'Trading Schedule',
        formPanelItems: [
        {   inputType: 'hidden', hidden: true, name: 'id' },
        {   inputType: 'hidden', hidden: true, name: 'status', value: 1 },

        //Display all category from Tag table
       /* {   xtype: 'combobox', fieldLabel: 'Category', name: 'categoryid', displayField: 'code', width: '100%', valueField: 'id', reference: 'categoryid',  minChars: 0, typeAhead: true,
            store: { type: 'Tag', sorters: 'code', autoLoad: true },
			listConfig: {
                itemTpl: [
                    '<div data-qtip="{code}: {id}">{code}, {id}</div>'
                ]
            }
        },*/

        //Display only 'Trading Schedule' category from Tag table
        {   xtype: 'combobox', fieldLabel:'Category',  name: 'categoryid', valueField: 'id', displayField: 'code',  reference: 'categoryid', queryMode: 'local', forceSelection: true, editable: false,
            store: {
                type: 'Tag',
                autoLoad: true,
                filterOnLoad: true,
                remoteFilter: true,
                sorters: 'category',
                filters: [{ property: 'category', value: 'TradingSchedule', exactMatch: true,   caseSensitive: true }],
            },
        },
        {   xtype: 'combobox', fieldLabel:'Type', store: {type: 'array', fields: ['id', 'code']}, queryMode: 'local', remoteFilter: false, name: 'type', valueField: 'id', displayField: 'code', reference: 'type', forceSelection: true, editable: false },
        {   xtype: 'datefield', fieldLabel: 'Start At', name: 'startat',  format: 'Y-m-d H:i:s'},
        {   xtype: 'datefield', fieldLabel: 'End At', name: 'endat',  format: 'Y-m-d H:i:s'},
        ]
    },

});
