Ext.define('snap.view.otcmanagementfee.OtcManagementFeeController', {
    extend: 'snap.view.gridpanel.BaseController',
    alias: 'controller.otcmanagementfee-otcmanagementfee',
	
	onPreAddEditSubmit: function(formAction, formView, formObject, btn) {
		Ext.MessageBox.show({
			title: 'Warning', buttons: Ext.MessageBox.OK, icon: Ext.MessageBox.WARNING,
			msg: 'Add/Edit Management fee is require checker approval'
		});
		
		return true;
    },
	
	onPostDeleteSubmit: function(formAction, view, btn) {
		Ext.MessageBox.show({
			title: 'Warning', buttons: Ext.MessageBox.OK, icon: Ext.MessageBox.WARNING,
			msg: 'Delete Management fee is require checker approval'
		});
		
		return true;
    },
	
	approvemanagementfee: function (btn, formAction, id) {
        var me = this, selectedRecord,
            myView = this.getView();

            var sm = myView.getSelectionModel();
            var selectedRecords = sm.getSelection();

            if (selectedRecords.length == 1) {
                for (var i = 0; i < selectedRecords.length; i++) {
                    selectedID = selectedRecords[i].get('id');
                    selectedRecord = selectedRecords[i];
                    break;
                }
                trxid = selectedRecords[0].data.id;
            } else if ('add' != formAction) {
                Ext.MessageBox.show({
                    title: 'Warning', buttons: Ext.MessageBox.OK, icon: Ext.MessageBox.WARNING,
                    msg: 'Select a record first'
                });
                return;
            }

        me._doApprovemanagementfee(myView, trxid);
    },

	_doApprovemanagementfee: function(me, transactionId) {
		var myView = me;
        
        trxid = transactionId;
        store = myView.getStore();
        var record = store.findRecord('id', trxid);
		var approveAction = '';
		var formName = '';
		
        if (record) {
            status = record.data.status;
            parentId = record.data.parentid;
            requestAction = record.data.requestaction;
			
			if (2 == status && 0 == parentId && 1 == requestAction) {
				approveAction = 'add';
				myView.formAddManagementFee.formDialogTitle = 'Add Management Fee Approval';
				formName = myView.formAddManagementFee;
			}
			if (2 == status && 0 < parentId && 2 == requestAction) {
				approveAction = 'edit';
				myView.formEditManagementFee.formDialogTitle = 'Edit Management Fee Approval';
				formName = myView.formEditManagementFee;
			}
			if ((1 == status || 2 == status) && 3 == requestAction) {
				approveAction = 'delete';
				myView.formAddManagementFee.formDialogTitle = 'Delete Management Fee Approval';
				formName = myView.formAddManagementFee;
			}
        }else{
            Ext.MessageBox.show({
                title: 'Warning', buttons: Ext.MessageBox.OK, icon: Ext.MessageBox.WARNING,
                msg: 'No Records Found'
            });
            return;
        }

        var gridFormView = Ext.create(myView.formClass, Ext.apply(formName ? formName : {}, {
            formDialogButtons: [{
                xtype: 'panel',
                flex: 1
            },
            {
                text: 'Approve',
                flex: 2.5,
                handler: function (modalBtn) {
                    var sm = myView.getSelectionModel();
                    var selectedRecords = sm.getSelection();
                    var remarks = Ext.getCmp('managementfeeapprovalremarks').getValue();
                    var approvalcode = Ext.getCmp('managementfeeapprovalcode').getValue();
					
                    Ext.MessageBox.confirm(
						'Confirm Approval', 'Are you sure you want to approve ?', function (btn) {
							if (btn === 'yes') {
								snap.getApplication().sendRequest({
									hdl: 'otcmanagementfee', 'action': 'approveManagementFee', id: selectedRecords[0].data.id, 'remarks': remarks, 'approvalcode': approvalcode , 'requestaction':requestAction, 'parentid' : parentId
								}, 'Sending request....').then(
									function (data) {
										if (data.success) {
											myView.getSelectionModel().deselectAll();
											myView.getStore().reload();
											
											owningWindow = modalBtn.up('window');
											owningWindow.close();
											me.gridFormView = null;
										} else {
											Ext.MessageBox.show({
												title: 'Error Message',
												msg: data.errorMessage,
												buttons: Ext.MessageBox.OK, icon: Ext.MessageBox.ERROR
											});
										}
									}
								);
							}
						}
					);
                }
            },
            {
                text: 'Reject',
                flex: 2.5,
                handler: function (modalBtn) {
                    var sm = myView.getSelectionModel();
                    var selectedRecords = sm.getSelection();
                    var remarks = Ext.getCmp('managementfeeapprovalremarks').getValue();
                    var approvalcode = Ext.getCmp('managementfeeapprovalcode').getValue();

                    Ext.MessageBox.confirm(
						'Confirm Rejection', 'Are you sure you want to reject?', function (btn) {
							if (btn === 'yes') {
								snap.getApplication().sendRequest({
									hdl: 'otcmanagementfee', 'action': 'rejectManagementFee', id: selectedRecords[0].data.id, 'remarks': remarks, 'approvalcode': approvalcode , 'requestaction':requestAction,
								}, 'Sending request....').then(
									function (data) {
										if (data.success) {
											myView.getSelectionModel().deselectAll();
											myView.getStore().reload();
											
											owningWindow = modalBtn.up('window');
											owningWindow.close();
											me.gridFormView = null;
										} else {
											Ext.MessageBox.show({
												title: 'Error Message',
												msg: data.errorMessage,
												buttons: Ext.MessageBox.OK, icon: Ext.MessageBox.ERROR
											});
										}
									}
								);
							}
						}
					);
                }
            },
            {
                xtype: 'panel',
                flex: 2,
            }, {
                text: 'Close',
                flex: 1,
                handler: function (btn) {
                    owningWindow = btn.up('window');
                    owningWindow.close();
                    me.gridFormView = null;
                }
            }]
        }));
		
		
		var minAmount = Ext.util.Format.number(record.data.avgdailygoldbalancegramfrom, '0.000000');
		var maxAmount = Ext.util.Format.number(record.data.avgdailygoldbalancegramto, '0.000000');
		var goldBalanceRange = minAmount + ' - ' + maxAmount;
		
		if ('add' == approveAction || 'delete' == approveAction) {
			gridFormView.controller.getView().lookupReference('goldbalancerange').setValue(goldBalanceRange);
			var feeAmount = Ext.util.Format.number(record.data.feeamount, '0.000000');
			gridFormView.controller.getView().lookupReference('feevalue').setValue(feeAmount);
			var startOn = new Date(record.data.starton);
			var formattedStartOn = Ext.Date.format(startOn, 'Y-m-d H:i:s');
			gridFormView.controller.getView().lookupReference('startdate').setValue(formattedStartOn);
			var endOn = new Date(record.data.endon);
			var formattedEndOn = Ext.Date.format(endOn, 'Y-m-d H:i:s');
			gridFormView.controller.getView().lookupReference('enddate').setValue(formattedEndOn);
		}
		
		if ('edit' == approveAction) {
			snap.getApplication().sendRequest({
				hdl: 'otcmanagementfee', 'action': 'getParentRecord', parentid: parentId
			}, 'Sending request....').then(
				function (data) {
					if (data.success) {
						var minAmountFrom = Ext.util.Format.number(data.record.avgdailygoldbalancegramfrom, '0.000000');
						var maxAmountFrom = Ext.util.Format.number(data.record.avgdailygoldbalancegramto, '0.000000');
						var goldBalanceRangeFrom = minAmountFrom + ' - ' + maxAmountFrom;
						
						gridFormView.controller.getView().lookupReference('goldbalancerangefrom').setValue(goldBalanceRangeFrom);
						var feeAmountFrom = Ext.util.Format.number(data.record.feeamount, '0.000000');
						gridFormView.controller.getView().lookupReference('feevaluefrom').setValue(feeAmountFrom);
						
						var startOn = new Date(data.record.starton.date);
						var formattedStartOnFrom = Ext.Date.format(startOn, 'Y-m-d H:i:s');
						gridFormView.controller.getView().lookupReference('startdatefrom').setValue(formattedStartOnFrom);
						var endOn = new Date(data.record.endon.date);
						var formattedEndOnFrom = Ext.Date.format(endOn, 'Y-m-d H:i:s');
						gridFormView.controller.getView().lookupReference('enddatefrom').setValue(formattedEndOnFrom);
						
					} else {
						Ext.MessageBox.show({
							title: 'Error Message',
							msg: data.errorMessage,
							buttons: Ext.MessageBox.OK, icon: Ext.MessageBox.ERROR
						});
					}
				}
			);
			
			
			gridFormView.controller.getView().lookupReference('goldbalancerangeto').setValue(goldBalanceRange);
			var feeAmountTo = Ext.util.Format.number(record.data.feeamount, '0.000000');
			gridFormView.controller.getView().lookupReference('feevalueto').setValue(feeAmountTo);
			var startOn = new Date(record.data.starton);
			var formattedStartOn = Ext.Date.format(startOn, 'Y-m-d H:i:s');
			gridFormView.controller.getView().lookupReference('startdateto').setValue(formattedStartOn);
			var endOn = new Date(record.data.endon);
			var formattedEndOn = Ext.Date.format(endOn, 'Y-m-d H:i:s');
			gridFormView.controller.getView().lookupReference('enddateto').setValue(formattedEndOn);
		}

        me.gridFormView = gridFormView;
        me.gridFormView.show();
	}
});
