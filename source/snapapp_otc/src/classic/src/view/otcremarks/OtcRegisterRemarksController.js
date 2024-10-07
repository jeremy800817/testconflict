Ext.define('snap.view.otcremarks.OtcRegisterRemarksController', {
    extend: 'snap.view.gridpanel.BaseController',
    alias: 'controller.otcregisterremarks-otcregisterremarks',
    
    approveregisterfertransaction: function (btn, formAction, id) {
        var me = this, selectedRecord,
            myView = this.getView();
            //debugger;
            
            // grid header data
            header = [];
            partnerCode = myView.partnercode;
            
            var sm = myView.getSelectionModel();
            var selectedRecords = sm.getSelection();

            type = btn.reference;

 
            // ordamount = selectedRecords[0].get('ordamount');
            // fullname = selectedRecords[0].get('achfullname');
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

        me._doApproveTransaction(myView, trxid);
    },

    _doApproveTransaction: function(me, transactionId) {

        var myView = me;

        trxid = transactionId;
        store = myView.getStore();
        var record = store.findRecord('id', trxid);

        if (record) {

            refno = record.data.refno;
            ordamount = record.data.xau;
            fullname = record.data.fromfullname;
        }else{
            Ext.MessageBox.show({
                title: 'Warning', buttons: Ext.MessageBox.OK, icon: Ext.MessageBox.WARNING,
                msg: 'No Records Found'
            });
            return;
        }

        var gridFormView = Ext.create(myView.formClass, Ext.apply(myView.formOtcTransferApproval ? myView.formOtcTransferApproval : {}, {
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

                    var remarks = Ext.getCmp('approvalremarks').getValue();
                    var remarks_id = selectedRecords[0].data.id;
                    var user_id = selectedRecords[0].data.createdby;
                    var ic_no = selectedRecords[0].data.mykadno;
                    var reasonfailed = selectedRecords[0].data.remarks;

                    Ext.MessageBox.confirm(
                        'Confirm Approval', 'Are you sure you want to approve ?', function (btn) {
                            if (btn === 'yes') {
                                if(PROJECTBASE == 'BSN' ){
                                    snap.getApplication().sendRequest({
                                        hdl: 'otcregisterremarks', 'action': 'approveregister', id: remarks_id, 'user_id':user_id, 'ic_no':ic_no ,'checker_remarks': remarks, 'register_remarks': reasonfailed,'rot':1,
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
                                    });
                                }else{
                                    snap.getApplication().sendRequest({
                                        hdl: 'otcregisterremarks', 'action': 'approveregister', id: remarks_id, 'user_id':user_id, 'ic_no':ic_no ,'checker_remarks': remarks, 'register_remarks': reasonfailed,'rot':1,
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
                                    });
                                }

                            }
                        });
                }
            },
            {
                text: 'Reject',
                flex: 2.5,
                handler: function (modalBtn) {
                    var sm = myView.getSelectionModel();
                    var selectedRecords = sm.getSelection();

                    var remarks = Ext.getCmp('approvalremarks').getValue();
                    var remarks_id = selectedRecords[0].data.id;
                    var user_id = selectedRecords[0].data.createdby;
                    var ic_no = selectedRecords[0].data.mykadno;
                    var reasonfailed = selectedRecords[0].data.remarks;

                    Ext.MessageBox.confirm(
                        'Confirm Rejection', 'Are you sure you want to reject?', function (btn) {
                            if (btn === 'yes') {
                                snap.getApplication().sendRequest({
                                    hdl: 'otcregisterremarks', 'action': 'rejectregister', id: remarks_id, 'user_id':user_id, 'ic_no':ic_no ,'checker_remarks': remarks, 'register_remarks': reasonfailed,'rot':1,
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
                                    });
                            }

                        });
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

        gridFormView.controller.getView().lookupReference('transferid').setValue(trxid);
        gridFormView.controller.getView().lookupReference('identityno').setValue(record.data.mykadno);
        gridFormView.controller.getView().lookupReference('registerremarks').setValue(record.data.remarks);
        // gridFormView.controller.getView().lookupReference('transferreferenceno').setValue(refno);
        // gridFormView.controller.getView().lookupReference('transferamount').setValue(ordamount);

        me.gridFormView = gridFormView;

        me.gridFormView.show();

    },

});
