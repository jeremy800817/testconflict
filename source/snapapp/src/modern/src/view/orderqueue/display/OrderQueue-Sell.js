Ext.define('snap.view.orderqueue.display.OrderQueue-Sell', {
	extend: 'Ext.Panel',
	xtype: 'orderqueuesellview',

	controller: {
		cancelOrders: function(btn, formAction) {
			var me = this,
				selectedRecord,
				myView = this.getView().down('grid');
			var selectedRecords = myView.getSelections();
			if (selectedRecords.length == 1) {
				for (var i = 0; i < selectedRecords.length; i++) {
					selectedID = selectedRecords[i].get('id');
					selectedRecord = selectedRecords[i];
					break;
				}
			} else if ('add' != formAction) {
				Ext.Msg.show({
					title: 'Warning',
					message: 'Select a record first'
				});
				return;
			}

			Ext.Msg.confirm('Confirm', 'Are you sure?', function(buttonId, value, opt) {
				if ('yes' == buttonId) {
					snap.getApplication().sendRequest({
							hdl: 'orderqueue',
							action: 'cancelFutureOrder',
							id: selectedRecord.data.id,
							partnerid: selectedRecord.data.partnerid,
							apiversion: selectedRecord.data.apiversion,
							refid: selectedRecord.data.partnerrefid,
							notifyurl: selectedRecord.data.notifyurl,
							reference: selectedRecord.data.remarks,
							timestamp: selectedRecord.data.createdon,
						})
						.then(function(data) {
							if (data.success) {
								Ext.Msg.show({
									title: 'Notification',
									message: 'Successfully cancelled future order'
								});
							}
							if (!data.success) {
								Ext.Msg.show({
									title: 'Warning',
									message: 'Unable to cancel order'
								});
							}
						})
				}
			});
		},
		boldText: function(value, record, dataIndex, cell) {
			cell.setStyle('font-weight:bold')
			return value
		}
	},
	title: 'Future Orders ACE Sell',
	userCls: 'panelhead-grid-modern',
	items: [{
		xtype: 'container',
		layout: {
			type: 'vbox',
			align: 'stretch',
		},
		width: '100%',
		items: [{
				xtype: 'toolbar',
				width: '100%',
				items: [{
					xtype: 'container',
					layout: {
						type: 'vbox',
						align: 'stretch',
					},
					width: '20%',
					style: 'text-align:center;font-size:0.75em',
					items: [{
							xtype: 'button',
							reference: '',
							text: '',
							itemId: 'statusLgs',
							iconCls: 'x-fa fa-times',
							handler: 'cancelOrders',
							validSelection: 'single'
						},
						{
							xtype: 'label',
							html: 'Cancel'
						}
					],
					listeners: {
						selectionchange: function(grid, re) {
							var selection = grid.getSelections(),
								data = null,
								vm = grid.up('panel').getViewModel();

							if (!Ext.isEmpty(selection)) {
								data = selection.map(rec => {
									return rec.get('name')
								}).join(',');
							}
							vm.set('selection', data);
						}
					}
				}]
			},
			{
				xtype: 'grid',
				width: '100%',
				minHeight: '600px',
				store: {
					type: 'MibOrderQueue',
					proxy: {
						type: 'ajax',
						url: 'index.php?hdl=orderqueue&action=list&type=CompanySell',
						reader: {
							type: 'json',
							rootProperty: 'records',
						}
					},
					remoteFilter: true,
					sorters: [{
						property: 'pricetarget',
						direction: 'DESC'
					}]
				},
				gridShowDeleteSuccessfulMessage: true,
				enableFilter: true,
				selectable: {
					mode: 'multi',
					checkbox: true
				},
				plugins: {
					pagingtoolbar: true
				},
				columns: [{
						text: 'Status',
						dataIndex: 'status',
						minWidth: 120,
						filter: {
							type: 'combo',
							store: [
								['0', 'Pending'],
								['1', 'Active'],
								['2', 'Fulfilled'],
								['3', 'Matched'],
								['4', 'Pending Cancel'],
								['5', 'Cancelled'],
								['6', 'Expired'],
							],
						},
						renderer: function(value, rec) {
							if (value == '0') return 'Pending';
							else if (value == '1') return 'Active';
							else if (value == '2') return 'Fulfilled';
							else if (value == '3') return 'Matched';
							else if (value == '4') return 'Pending Cancel';
							else if (value == '5') return 'Cancelled';
							else return 'Expired';
						},
						cell: {
							tools: {
								maximize: 'onRecord'
							}
						},
					},
					{
						text: 'ID',
						dataIndex: 'id',
						filter: {
							type: 'string'
						},
						hidden: true,
						flex: 1
					},
					{
						text: 'Spot Order No',
						dataIndex: 'orderid',
						filter: {
							type: 'string'
						},
						hidden: true,
						minWidth: 130
					},
					{
						text: 'Order Queue No',
						dataIndex: 'orderqueueno',
						filter: {
							type: 'string'
						},
						minWidth: 140,
						renderer: function(value, record, dataIndex, cell) {
							if (record.data.ordertype == 'CompanySell') {
								cell.setStyle('color: #209474;')
							}
							if (record.data.ordertype == 'CompanyBuy') {
								cell.setStyle('color: #d07b32;')
							}
							return value;
						},
					},
					{
						text: 'Partner Name',
						dataIndex: 'partnername',
						hidden: true,
						filter: {
							type: 'string'
						},
						flex: 1
					},
					{
						text: 'Partner Ref No.',
						dataIndex: 'partnerrefid',
						filter: {
							type: 'string'
						},
						hidden: true,
						flex: 1,
						renderer: 'boldText'
					},
					{
						text: 'Salesperson Name',
						dataIndex: 'salespersonname',
						filter: {
							type: 'string'
						},
						hidden: true,
						minWidth: 100
					},
					{
						text: 'Order Type',
						dataIndex: 'ordertype',
						minWidth: 100,
						filter: {
							type: 'combo',
							store: [
								['CompanySell', 'Buy'],
								['CompanyBuy', 'Sell'],
								['CompanyBuyBack', 'CompanyBuyBack'],
							],
						},
						renderer: function(value, rec) {
							if (value == 'CompanySell') return 'Buy';
							else if (value == 'CompanyBuy') return 'Sell';
							else return '--';
						},
					},
					{
						text: 'Price Target (RM/g)',
						dataIndex: 'pricetarget',
						filter: {
							type: 'string'
						},
						minWidth: 140,
						align: 'right',
						renderer: Ext.util.Format.numberRenderer('0,000.000'),
						editor: {
							xtype: 'numberfield',
							decimalPrecision: 3
						}
					},
					{
						text: 'Book By',
						dataIndex: 'byweight',
						minWidth: 100,
						filter: {
							type: 'combo',
							store: [
								['0', 'Amount'],
								['1', 'Weight'],
							],
						},
						renderer: function(value, record, dataIndex, cell) {
							if (value == '0') {
								cell.setStyle('color:#800080;');
								value = 'Amount';
							} else if (value == '1') {
								cell.setStyle('color:#d4af37;');
								value = 'Weight'
							} else {
								value = 'Unassigned';
							}
							return value;
						},
					},
					{
						text: 'Xau Weight (g)',
						dataIndex: 'xau',
						filter: {
							type: 'string'
						},
						minWidth: 130,
						align: 'right',
						minWidth: 130,
						renderer: Ext.util.Format.numberRenderer('0,000.000'),
						editor: {
							xtype: 'numberfield',
							decimalPrecision: 3
						}
					},
					{
						text: 'Amount (RM)',
						dataIndex: 'amount',
						filter: {
							type: 'string'
						},
						minWidth: 100,
						align: 'right',
						renderer: Ext.util.Format.numberRenderer('0,000.000'),
						editor: {
							xtype: 'numberfield',
							decimalPrecision: 3
						}
					},
					{
						text: 'Product',
						dataIndex: 'productname',
						filter: {
							type: 'string'
						},
						minWidth: 130
					},
					{
						text: 'Expire On',
						dataIndex: 'expireon',
						xtype: 'datecolumn',
						format: 'Y-m-d H:i:s',
						filter: {
							type: 'date'
						},
						minWidth: 100
					},
					{
						text: 'Matched On',
						dataIndex: 'matchon',
						xtype: 'datecolumn',
						format: 'Y-m-d H:i:s',
						filter: {
							type: 'date'
						},
						flex: 1
					},
					{
						text: 'Created on',
						dataIndex: 'createdon',
						xtype: 'datecolumn',
						format: 'Y-m-d H:i:s',
						filter: {
							type: 'date'
						},
						inputType: 'hidden',
						minWidth: 100
					},
					{
						text: 'Modified on',
						dataIndex: 'modifiedon',
						xtype: 'datecolumn',
						format: 'Y-m-d H:i:s',
						filter: {
							type: 'date'
						},
						inputType: 'hidden',
						minWidth: 100,
						hidden: true,
					},
					{
						text: 'Created by',
						dataIndex: 'createdbyname',
						filter: {
							type: 'string'
						},
						inputType: 'hidden',
						hidden: true,
						minWidth: 100
					},
					{
						text: 'Modified by',
						dataIndex: 'modifiedbyname',
						filter: {
							type: 'string'
						},
						inputType: 'hidden',
						hidden: true,
						minWidth: 100
					},
				],
			}
		]
	}],
});