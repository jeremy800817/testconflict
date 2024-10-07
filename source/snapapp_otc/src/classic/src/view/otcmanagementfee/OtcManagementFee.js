Ext.define('snap.view.otcmanagementfee.OtcManagementFee',{
    extend: 'snap.view.gridpanel.Base',

    requires: [
        'snap.view.otcmanagementfee.OtcManagementFeeController',
        'snap.view.otcmanagementfee.OtcManagementFeeModel',
        'snap.model.OtcManagementFee',
        'snap.store.OtcManagementFee'
    ],
    xtype: 'otcmanagementfeeview',
    controller: 'otcmanagementfee-otcmanagementfee',
    viewModel: {
        type: 'otcmanagementfee-otcmanagementfee'
    },
    store: {type: 'OtcManagementFee', autoLoad: true, otcuseractivitylog: true},
	enableFilter: true,
    permissionRoot: '/root/bsn/managementfee',

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
        { text: 'ID', dataIndex: 'id', filter: { type: 'int'}, hidden: true },
        { text: 'Gold Balance(g) Range', dataIndex: 'avgdailygoldbalancegramfrom', 
			filter: { type: 'string'}, width: 200,
			renderer: function(value, metaData, record) {
				const minAmount = Ext.util.Format.number(record.get('avgdailygoldbalancegramfrom'), '0.000000');
				const maxAmount = Ext.util.Format.number(record.get('avgdailygoldbalancegramto'), '0.000000');
				return `${minAmount} - ${maxAmount}`;
			}
		},
        { text: 'Fee per gram(RM)', dataIndex: 'feeamount', 
			filter: { type: 'string'}, width: 200,
			renderer: Ext.util.Format.numberRenderer('0,000.000')
		},
        { text: 'Start Date', dataIndex: 'starton', xtype: 'datecolumn', format: 'Y-m-d H:i:s', filter: { type: 'string'}, width: 200 },
        { text: 'End Date', dataIndex: 'endon', xtype: 'datecolumn', format: 'Y-m-d H:i:s', filter: { type: 'string'}, width: 200 },
        { text: 'status', dataIndex: 'status', flex: 1,
			filter: {
				type: 'combo',
				store: [
					['1', 'Active'],
					['2', 'Pending Approval']
				],
			},
			renderer: function(value, rec){
				if (value == '1') return 'Active';
				if (value == '2') return 'Pending Approval';
				else return '';
			}
		},
        { text: 'Created by', dataIndex: 'createdby', filter: { type: 'string'}, hidden: true },
        { text: 'created On', dataIndex: 'createdon', format: 'Y-m-d H:i:s', filter: { type: 'date'}, hidden: true },
        { text: 'Modified By', dataIndex: 'modifiedby', filter: { type: 'string'}, hidden: true },
        { text: 'Modified On', dataIndex: 'modifiedon', format: 'Y-m-d H:i:s',  filter: { type: 'date'}, hidden: true }
    ],

    formConfig: {
        formDialogTitle: 'Management Fee',
        enableFormDialogClosable: false,
        formPanelDefaults: {
            labelWidth: 150,
            required: true
        },
        formPanelItems: [
            { inputType: 'hidden', hidden: true, name: 'id' },
            { inputType: 'hidden', hidden: true, name: 'status', value: '2' },
            { inputType: 'hidden', hidden: true, name: 'requestaction', value: '1' },
            { xtype: 'numberfield', minValue: 0, fieldLabel: 'Min Gold Balance(g)', name: 'avgdailygoldbalancegramfrom', 
				listeners: {
					change: function (field, newValue, oldValue, eOpts) {
						var nameField = field.up().down('textfield[name=name]');
						var maxValue = field.up().down('textfield[name=avgdailygoldbalancegramto]').getValue();
						
						var nameValue = newValue;
						if (maxValue) nameValue = nameValue + ' - ' + maxValue;
						
						nameField.setValue(nameValue);
					}
				}
			},
            { xtype: 'numberfield', minValue: 0, fieldLabel: 'Max Gold Balance(g)', name: 'avgdailygoldbalancegramto',
				listeners: {
					change: function (field, newValue, oldValue, eOpts) {
						var nameField = field.up().down('textfield[name=name]');
						var minValue = field.up().down('textfield[name=avgdailygoldbalancegramfrom]').getValue();
						
						var nameValue = newValue;
						if (minValue) nameValue = minValue + ' - ' + nameValue;
						
						nameField.setValue(nameValue);
					}
				}
			},
            { xtype: 'textfield', hidden: true, fieldLabel: 'Name', name: 'name'},
            { xtype: 'datefield', format: 'Y-m-d', fieldLabel: 'Start Date', name: 'starton'},
            { xtype: 'datefield', format: 'Y-m-d', fieldLabel: 'End Date', name: 'endon'},
            { xtype: 'numberfield', minValue: 0, fieldLabel: 'Fee per gram(RM)', name: 'feeamount'}
        ]
    }
});
