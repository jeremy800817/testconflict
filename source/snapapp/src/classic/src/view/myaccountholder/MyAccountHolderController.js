Ext.define('snap.view.myaccountholder.MyAccountHolderController', {
    extend: 'snap.view.gridpanel.BaseController',
    alias: 'controller.myaccountholder-myaccountholder',

    // getPrintReport: function (btn) {

    //     var myView = this.getView()

    //     // grid header data
    //     header = []
    //     btn.up('grid').getColumns().map(column => {
    //         if (column.isVisible() && column.dataIndex !== null) {
    //             _key = column.text
    //             _value = column.dataIndex
    //             columnlist = {
    //                 // [_key]: _value
    //                 text: _key,
    //                 index: _value
    //             }
    //             if (column.exportdecimal != null) {
    //                 _decimal = column.exportdecimal;
    //                 columnlist.decimal = _decimal;
    //             } else {
    //                 columnlist.string = 1;

    //             }
    //             header.push(columnlist);
    //         }
    //     });
    
       
    
    //     header = encodeURI(JSON.stringify(header));
    
    //     url = '?hdl=myaccountholder&action=exportExcel&partnercode=' + myView.partnerCode + '&header=' + header;
    
    //     Ext.DomHelper.append(document.body, {
    //         tag: 'iframe',
    //         id: 'downloadIframe',
    //         frameBorder: 0,
    //         width: 0,
    //         height: 0,
    //         css: 'display:none;visibility:hidden;height: 0px;',
    //         src: url
    //     });
    // },
    
    onViewAccountHolder: function () {
        var myView = this.getView(),
            me = this, record;
        var sm = myView.getSelectionModel();
        var selectedRecords = sm.getSelection();

        // set path
        path = 'my' + myView.partnerCode.toLowerCase() + 'cifview';
        if (selectedRecords.length == 1) {
            for (var i = 0; i < selectedRecords.length; i++) {
                selectedID = selectedRecords[i].get('id');
                record = selectedRecords[i];
                me.redirectTo(path + '/accountholder/' + selectedID);
                break;
            }
        } else {
            Ext.MessageBox.show({
                title: 'Warning', buttons: Ext.MessageBox.OK, icon: Ext.MessageBox.WARNING,
                msg: 'Select a record first'
            });
            return;
        }
    },
    approvePep: function (btn, formAction) {
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
        } else if ('add' != formAction) {
            Ext.MessageBox.show({
                title: 'Warning', buttons: Ext.MessageBox.OK, icon: Ext.MessageBox.WARNING,
                msg: 'Select a record first'
            });
            return;
        }

        if (!selectedRecord.get('ispep')) {
            Ext.MessageBox.show({
                title: 'Warning', buttons: Ext.MessageBox.OK, icon: Ext.MessageBox.WARNING,
                msg: 'Account holder is not PEP'
            });
            return;
        }

        var partnerId = selectedRecords[i].data['partnerid'];
        var accountHolderId = selectedRecords[i].data['id'];

        var gridFormView = Ext.create(myView.formClass, Ext.apply(myView.formPepApproval ? myView.formPepApproval : {}, {
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
                    var remarks = Ext.getCmp('pepremarks').getValue();
                    Ext.MessageBox.confirm(
                        'Confirm', 'Are you sure you want to approve ?', function (btn) {
                            if (btn === 'yes') {
                                snap.getApplication().sendRequest({
                                    hdl: 'mypepsearchresult', 'action': 'approveAccountHolder', id: selectedRecords[0].data.id, 'remarks': remarks
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
                text: 'Reject',
                flex: 2.5,
                handler: function (modalBtn) {
                    var sm = myView.getSelectionModel();
                    var selectedRecords = sm.getSelection();
                    var remarks = Ext.getCmp('pepremarks').getValue();
                    Ext.MessageBox.confirm(
                        'Confirm', 'Are you sure you want to reject ?', function (btn) {
                            if (btn === 'yes') {
                                snap.getApplication().sendRequest({
                                    hdl: 'mypepsearchresult', 'action': 'rejectAccountHolder', id: selectedRecords[0].data.id, 'remarks': remarks
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
            }, {
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

        // Get Full Name
        fullname = selectedRecords[i].get('fullname');
        gridFormView.controller.getView().lookupReference('accountholderpepname').setValue(fullname);
        gridFormView.controller.getView().lookupReference('accountholderpepic').setValue(selectedRecords[i].get('mykadno'));
        gridFormView.accountHolderId = selectedRecords[i].get('id')

        // Populate Form
        if (partnerId != null) {

            // If form not present, enable form
            if (gridFormView.down('mypepmatchdataview').isHidden() == true) {
                // Clear init form
                gridFormView.down('mypepmatchdataview').getStore().removeAll();
                gridFormView.down('mypepmatchdataview').setHidden(false);

            }

            // Replace proxy URL with selection
            gridFormView.down('mypepmatchdataview').getStore().proxy.url = 'index.php?hdl=mypepsearchresult&action=getPepMatchData&partnerid=' + partnerId + '&accountholderid=' + accountHolderId;
            gridFormView.down('mypepmatchdataview').getStore().reload();

        } else {
            Ext.MessageBox.show({
                title: "Error",
                msg: "Please select a record",
                buttons: Ext.MessageBox.OK,
                icon: Ext.MessageBox.WARNING
            });
        }

        this.gridFormView = gridFormView;
        this._formAction = "edit";

        var addEditForm = this.gridFormView.down('form').getForm();

        gridFormView.title = 'Update ' + gridFormView.title + '...';
        if (Ext.isFunction(me['onPreLoadForm'])) {
            if (!this.onPreLoadForm(gridFormView, addEditForm, selectedRecord, function (updatedRecord) {
                addEditForm.loadRecord(updatedRecord);
                if (Ext.isFunction(me['onPostLoadForm'])) this.onPostLoadForm(gridFormView, addEditForm, updatedRecord);
                me.gridFormView.show();
            })) return;
        }
        addEditForm.loadRecord(selectedRecord);
        if (Ext.isFunction(me['onPostLoadForm'])) this.onPostLoadForm(gridFormView, addEditForm, selectedRecord);

        this.gridFormView.show();
    },

    approveEkyc: function (btn, formAction) {
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
        } else if ('add' != formAction) {
            Ext.MessageBox.show({
                title: 'Warning', buttons: Ext.MessageBox.OK, icon: Ext.MessageBox.WARNING,
                msg: 'Select a record first'
            });
            return;
        }

        // Check record on ekyc status
        // Only allows KYC_Failed
        if (selectedRecord.get('kycstatus') == 0) {
            Ext.MessageBox.show({
                title: 'Warning', buttons: Ext.MessageBox.OK, icon: Ext.MessageBox.WARNING,
                msg: 'EKYC is still incomplete'
            });
            return;
        }

        if (selectedRecord.get('kycstatus') == 1) {
            Ext.MessageBox.show({
                title: 'Warning', buttons: Ext.MessageBox.OK, icon: Ext.MessageBox.WARNING,
                msg: 'EKYC has been passed'
            });
            return;
        }

        if (selectedRecord.get('kycstatus') == 2) {
            Ext.MessageBox.show({
                title: 'Warning', buttons: Ext.MessageBox.OK, icon: Ext.MessageBox.WARNING,
                msg: 'EKYC is still pending'
            });
            return;
        }

        var partnerId = selectedRecords[i].data['partnerid'];
        var accountHolderId = selectedRecords[i].data['id'];

        var gridFormView = Ext.create(myView.formClass, Ext.apply(myView.formEkycApproval ? myView.formEkycApproval : {}, {
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
                    var remarks = Ext.getCmp('pepremarks').getValue();
                    Ext.MessageBox.confirm(
                        'Confirm', 'Are you sure you want to approve ?', function (btn) {
                            if (btn === 'yes') {
                                snap.getApplication().sendRequest({
                                    hdl: 'myaccountholder', 'action': 'approveAccountHolderEKYC', id: selectedRecords[0].data.id, 'remarks': remarks
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
            // {
            //     text: 'Reject',
            //     flex: 2.5,
            //     handler: function (modalBtn) {
            //         var sm = myView.getSelectionModel();
            //         var selectedRecords = sm.getSelection();
            //         var remarks = Ext.getCmp('pepremarks').getValue();
            //         Ext.MessageBox.confirm(
            //             'Confirm', 'Are you sure you want to reject ?', function (btn) {
            //                 if (btn === 'yes') {
            //                     snap.getApplication().sendRequest({
            //                         hdl: 'mypepsearchresult', 'action': 'rejectAccountHolder', id: selectedRecords[0].data.id, 'remarks': remarks
            //                     }, 'Sending request....').then(
            //                         function (data) {
            //                             if (data.success) {
            //                                 myView.getSelectionModel().deselectAll();
            //                                 myView.getStore().reload();

            //                                 owningWindow = modalBtn.up('window');
            //                                 owningWindow.close();
            //                                 me.gridFormView = null;

            //                             } else {
            //                                 Ext.MessageBox.show({
            //                                     title: 'Error Message',
            //                                     msg: data.errorMessage,
            //                                     buttons: Ext.MessageBox.OK, icon: Ext.MessageBox.ERROR
            //                                 });
            //                             }
            //                         });
            //                 }
            //             });
            //     }
            // },
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

        // Get Full Name
        fullname = selectedRecords[i].get('fullname');
        gridFormView.controller.getView().lookupReference('accountholderpepname').setValue(fullname);
        gridFormView.controller.getView().lookupReference('accountholderpepic').setValue(selectedRecords[i].get('mykadno'));
        gridFormView.accountHolderId = selectedRecords[i].get('id')

        // Populate Form
        // if (partnerId != null) {

        //     // If form not present, enable form
        //     if (gridFormView.down('mypepmatchdataview').isHidden() == true) {
        //         // Clear init form
        //         gridFormView.down('mypepmatchdataview').getStore().removeAll();
        //         gridFormView.down('mypepmatchdataview').setHidden(false);

        //     }

        //     // Replace proxy URL with selection
        //     gridFormView.down('mypepmatchdataview').getStore().proxy.url = 'index.php?hdl=mypepsearchresult&action=getPepMatchData&partnerid=' + partnerId + '&accountholderid=' + accountHolderId;
        //     gridFormView.down('mypepmatchdataview').getStore().reload();

        // } else {
        //     Ext.MessageBox.show({
        //         title: "Error",
        //         msg: "Please select a record",
        //         buttons: Ext.MessageBox.OK,
        //         icon: Ext.MessageBox.WARNING
        //     });
        // }

        this.gridFormView = gridFormView;
        this._formAction = "edit";

        var addEditForm = this.gridFormView.down('form').getForm();

        gridFormView.title = 'Update ' + gridFormView.title + '...';
        if (Ext.isFunction(me['onPreLoadForm'])) {
            if (!this.onPreLoadForm(gridFormView, addEditForm, selectedRecord, function (updatedRecord) {
                addEditForm.loadRecord(updatedRecord);
                if (Ext.isFunction(me['onPostLoadForm'])) this.onPostLoadForm(gridFormView, addEditForm, updatedRecord);
                me.gridFormView.show();
            })) return;
        }
        addEditForm.loadRecord(selectedRecord);
        if (Ext.isFunction(me['onPostLoadForm'])) this.onPostLoadForm(gridFormView, addEditForm, selectedRecord);

        this.gridFormView.show();
    },

    onSuspendAccountHolder: function (btn) {
        let me = this;
        let selection = me.getView().getSelectionModel().getSelection()[0];
        if (! selection) {
            Ext.MessageBox.show({
                title: 'Warning', buttons: Ext.MessageBox.OK, icon: Ext.MessageBox.WARNING,
                msg: 'Select a record first'
            });
        }

        let win = new Ext.Window ({
            title:'Account Suspension Confirmation',
            layout:'form',
            width:530,
            closeAction:'close',
            
            items: [{
                xtype : 'textfield',
                fieldLabel: 'Account Code',
                readOnly: true,
                value: selection.get('accountholdercode'),                
            },{
                xtype: 'textfield',
                readOnly: true,
                fieldLabel: 'Fullname',
               value: selection.get('fullname')
            },{
               xtype : 'textfield',
                fieldLabel: 'NRIC',
                readOnly: true,
                value: selection.get('mykadno')
            },{
                xtype : 'textarea',
                fieldLabel: 'Remarks',
                value: selection.get('statusremarks'),
                itemId: 'suspensionremarks'
            },{
                boxLabel  : 'Note: This will blacklist the account. Tick to confirm',
                xtype: 'checkboxfield',
                itemId: 'suspensionconfirmation'
             }],
            
            buttons: [{
                text: 'Suspend',
                handler: function () {
                    let remarks = win.items.get('suspensionremarks').getValue();
                    let confirmation = win.items.get('suspensionconfirmation').getValue();

                    if ('' == remarks) {
                        Ext.MessageBox.show({
                            title: 'Error',
                            msg: 'Remarks is required',
                            buttons: Ext.MessageBox.OK, icon: Ext.MessageBox.ERROR
                        });
                        return;
                    }

                    if (!confirmation) {
                        Ext.MessageBox.show({
                            title: 'Error',
                            msg: 'Please tick the checkbox to continue',
                            buttons: Ext.MessageBox.OK, icon: Ext.MessageBox.ERROR
                        });
                        return;
                    }
                    
                    win.close();
                    snap.getApplication().sendRequest({
                        hdl: 'myaccountholder', 'action': 'suspendAccountHolder', id: selection.get('id'), 'remarks': remarks
                    }, 'Sending request....').then(
                        function (data) {
                            if (data.success) {
                                me.getView().getSelectionModel().deselectAll();
                                me.getView().getStore().reload();

                                owningWindow = modalBtn.up('window');
                                owningWindow.close();

                            } else {
                                Ext.MessageBox.show({
                                    title: 'Error',
                                    msg: data.errorMessage,
                                    buttons: Ext.MessageBox.OK, icon: Ext.MessageBox.ERROR
                                });
                            }
                        });
                }
            },{
               text: 'Cancel',
               handler: function() {
                   win.close();                   
               }
            }],
            buttonAlign: 'center',
        });

        win.show();
    },

    onUnsuspendAccountHolder: function (btn) {
        let me = this;
        let selection = me.getView().getSelectionModel().getSelection()[0];
        if (! selection) {
            Ext.MessageBox.show({
                title: 'Warning', buttons: Ext.MessageBox.OK, icon: Ext.MessageBox.WARNING,
                msg: 'Select a record first'
            });
        }

        let win = new Ext.Window ({
            title:'Account Unsuspend Confirmation',
            layout:'form',
            width:530,
            closeAction:'close',
            
            items: [{
                xtype : 'textfield',
                fieldLabel: 'Account Code',
                readOnly: true,
                value: selection.get('accountholdercode'),                
            },{
                xtype: 'textfield',
                readOnly: true,
                fieldLabel: 'Fullname',
               value: selection.get('fullname')
            },{
               xtype : 'textfield',
                fieldLabel: 'NRIC',
                readOnly: true,
                value: selection.get('mykadno')
            },{
                xtype : 'textarea',
                fieldLabel: 'Remarks',
                value: selection.get('statusremarks'),
                itemId: 'suspensionremarks'
            },{
                boxLabel  : 'Note: This will activate the account. Tick to confirm',
                xtype: 'checkboxfield',
                itemId: 'suspensionconfirmation'
             }],
            
            buttons: [{
                text: 'Unsuspend',
                handler: function () {
                    let remarks = win.items.get('suspensionremarks').getValue();
                    let confirmation = win.items.get('suspensionconfirmation').getValue();

                    if ('' == remarks) {
                        Ext.MessageBox.show({
                            title: 'Error',
                            msg: 'Remarks is required',
                            buttons: Ext.MessageBox.OK, icon: Ext.MessageBox.ERROR
                        });
                        return;
                    }

                    if (!confirmation) {
                        Ext.MessageBox.show({
                            title: 'Error',
                            msg: 'Please tick the checkbox to continue',
                            buttons: Ext.MessageBox.OK, icon: Ext.MessageBox.ERROR
                        });
                        return;
                    }
                    
                    win.close();
                    snap.getApplication().sendRequest({
                        hdl: 'myaccountholder', 'action': 'unsuspendAccountHolder', id: selection.get('id'), 'remarks': remarks
                    }, 'Sending request....').then(
                        function (data) {
                            if (data.success) {
                                me.getView().getSelectionModel().deselectAll();
                                me.getView().getStore().reload();

                                owningWindow = modalBtn.up('window');
                                owningWindow.close();

                            } else {
                                Ext.MessageBox.show({
                                    title: 'Error',
                                    msg: data.errorMessage,
                                    buttons: Ext.MessageBox.OK, icon: Ext.MessageBox.ERROR
                                });
                            }
                        });
                }
            },{
               text: 'Cancel',
               handler: function() {
                   win.close();                   
               }
            }],
            buttonAlign: 'center',
        });

        win.show();
    },

    onCloseAccountHolder: function (btn) {
        let me = this;
        let selection = me.getView().getSelectionModel().getSelection()[0];
        if (! selection) {
            Ext.MessageBox.show({
                title: 'Warning', buttons: Ext.MessageBox.OK, icon: Ext.MessageBox.WARNING,
                msg: 'Select a record first'
            });
        }

        let win = new Ext.Window ({
            title:'Account Closure Confirmation',
            layout:'form',
            width:530,
            closeAction:'close',
            
            items: [{
                xtype : 'textfield',
                fieldLabel: 'Account Code',
                readOnly: true,
                value: selection.get('accountholdercode'),                
            },{
                xtype: 'textfield',
                readOnly: true,
                fieldLabel: 'Fullname',
               value: selection.get('fullname')
            },{
               xtype : 'textfield',
                fieldLabel: 'NRIC',
                readOnly: true,
                value: selection.get('mykadno')
            },{
                xtype : 'textarea',
                fieldLabel: 'Remarks',
                value: selection.get('statusremarks'),
                itemId: 'closeremarks'
            },{
                boxLabel  : 'Note: This will close the account. Tick to confirm',
                xtype: 'checkboxfield',
                itemId: 'closeconfirmation',
                listeners: {
                    change: function (checkcolumn, rowIndex, unchecked, record, eOpts) {
                        if (unchecked) {
                            win.getDockedItems('toolbar[dock="bottom"]')[0].getComponent('submitbutton').disable();
                        } else {
                            win.getDockedItems('toolbar[dock="bottom"]')[0].getComponent('submitbutton').enable(true);
                        }
                    },                    
                }
             }],
            
            buttons: [{
                itemId:'submitbutton',
                disabled:true,
                text: 'Confirm',
                handler: function () {
                    let remarks = win.items.get('closeremarks').getValue();
                    let confirmation = win.items.get('closeconfirmation').getValue();

                    if ('' == remarks) {
                        Ext.MessageBox.show({
                            title: 'Error',
                            msg: 'Remarks is required',
                            buttons: Ext.MessageBox.OK, icon: Ext.MessageBox.ERROR
                        });
                        return;
                    }

                    if (!confirmation) {
                        Ext.MessageBox.show({
                            title: 'Error',
                            msg: 'Please tick the checkbox to continue',
                            buttons: Ext.MessageBox.OK, icon: Ext.MessageBox.ERROR
                        });
                        return;
                    }
                    
                    win.close();
                    snap.getApplication().sendRequest({
                        hdl: 'myaccountclosure', 'action': 'closeAccountHolder', id: selection.get('id'), 'remarks': remarks
                    }, 'Sending request....').then(
                        function (data) {
                            if (data.success) {
                                me.getView().getSelectionModel().deselectAll();
                                me.getView().getStore().reload();

                                owningWindow = modalBtn.up('window');
                                owningWindow.close();

                            } else {
                                Ext.MessageBox.show({
                                    title: 'Error',
                                    msg: data.errorMessage,
                                    buttons: Ext.MessageBox.OK, icon: Ext.MessageBox.ERROR
                                });
                            }
                        });
                }
            },{
               text: 'Cancel',
               handler: function() {
                   win.close();                   
               }
            }],
            buttonAlign: 'center',
        });

        win.show();
    },

    /* Edit Loan Information  */
    onManualUpdateLoan: function (btn) {
        let me = this;
        let selection = me.getView().getSelectionModel().getSelection()[0];
        if (! selection) {
            Ext.MessageBox.show({
                title: 'Warning', buttons: Ext.MessageBox.OK, icon: Ext.MessageBox.WARNING,
                msg: 'Select a record first'
            });
        }

        let win = new Ext.Window ({
            title:'Approve Loan',
            layout:'form',
            width:530,
            closeAction:'close',
            
            items: [{
                xtype : 'textfield',
                 fieldLabel: 'NRIC',
                 readOnly: true,
                 value: selection.get('mykadno'),
             },{
                xtype : 'textfield',
                 fieldLabel: 'Account Code',
                 readOnly: true,
                 value: selection.get('accountholdercode'),
             },{
                xtype : 'numberfield',
                fieldLabel: 'Loan Total',
                forcePrecision: true,     
                decimalPrecision: 2,    
                // readOnly: true,
                itemId: 'loantotal',
                value: selection.get('loantotal'),                
            },{
                xtype : 'textfield',
                fieldLabel: 'Reference Number',
                // readOnly: true,
                itemId: 'loanreference',
                value: selection.get('loanreference'),                
            },{
                xtype: 'datefield',
                // readOnly: true,
                fieldLabel: 'Loan Approve Date',
                anchor: '100%',
                emptyText: 'mm/dd/yyyy',
                maskRe: /[0-9\/]/,
                itemId: 'loanapprovedate',
                value: new Date(),    // Defaults to today
            },{
                boxLabel  : 'Note: This will approve the loan. Tick to confirm',
                xtype: 'checkboxfield',
                itemId: 'updateloanconfirmation',
                listeners: {
                    change: function (checkcolumn, rowIndex, unchecked, record, eOpts) {
                        if (unchecked) {
                            win.getDockedItems('toolbar[dock="bottom"]')[0].getComponent('submitbutton').disable();
                        } else {
                            win.getDockedItems('toolbar[dock="bottom"]')[0].getComponent('submitbutton').enable(true);
                        }
                    },                    
                }
             }],
            
            buttons: [{
                itemId:'submitbutton',
                disabled:true,
                text: 'Confirm',
                handler: function () {
                    let loantotal = win.items.get('loantotal').getValue();
                    let loanreference = win.items.get('loanreference').getValue();
                    let loanapprovedate = win.items.get('loanapprovedate').getValue();
                    // let remarks = win.items.get('closeremarks').getValue();
                    let confirmation = win.items.get('updateloanconfirmation').getValue();

                    if ('' == loantotal) {
                        Ext.MessageBox.show({
                            title: 'Error',
                            msg: 'Loan Total is required',
                            buttons: Ext.MessageBox.OK, icon: Ext.MessageBox.ERROR
                        });
                        return;
                    }

                    if ('' == loanreference) {
                        Ext.MessageBox.show({
                            title: 'Error',
                            msg: 'Loan Reference is required',
                            buttons: Ext.MessageBox.OK, icon: Ext.MessageBox.ERROR
                        });
                        return;
                    }

                    if (!confirmation) {
                        Ext.MessageBox.show({
                            title: 'Error',
                            msg: 'Please tick the checkbox to continue',
                            buttons: Ext.MessageBox.OK, icon: Ext.MessageBox.ERROR
                        });
                        return;
                    }
                    
                    Ext.MessageBox.confirm(
                        'Confirm', 'Are you sure you want to approve this loan?', function (btn) {
                            if (btn === 'yes') {
                                win.close();
                                snap.getApplication().sendRequest({
                                    hdl: 'myaccountholder', 'action': 'updateAccountHolderLoan', id: selection.get('id'), 
                                    // 'remarks': remarks,
                                    'loantotal': loantotal,
                                    'loanreference': loanreference,
                                    'loanapprovedate': loanapprovedate,
                                }, 'Sending request....').then(
                                    function (data) {
                                        if (data.success) {
                                            me.getView().getSelectionModel().deselectAll();
                                            me.getView().getStore().reload();
            
                                            owningWindow = modalBtn.up('window');
                                            owningWindow.close();
            
                                            snap.getApplication().getStore('snap.store.MyAccountHolder').reload()
                                        } else {
                                            Ext.MessageBox.show({
                                                title: 'Error',
                                                msg: data.errorMessage,
                                                buttons: Ext.MessageBox.OK, icon: Ext.MessageBox.ERROR
                                            });
                                        }
                                    });               
                            }                
                        });
                }
            },{
               text: 'Cancel',
               handler: function() {
                   win.close();                   
               }
            }],
            buttonAlign: 'center',
        });

        win.show();
    },

    /**
     * Method to get the detail item for the specific column.  Override here to provide additional or
     * special implementation for a particular column info.
     */
    onGetItemDetail: function( record, column) {
        var me = this;
        if(column.text == undefined || column.text.match(/&nbsp/)) return null;
        var value = Ext.isFunction(record['get']) ? record.get(column.dataIndex) : record[column.dataIndex];
        if (column.dataIndex === 'status') {
            return me.getStatusString(value);
        } else if(column.dataIndex === 'investmentmade') {
            if (value == '0') return 'No';
            else if (value == '1') return 'Yes';
        } else if(column.dataIndex === 'ispep') {
            if (value == '0') return 'No';
            else if (value == '1') return 'Yes';
        } else if(column.dataIndex === 'pepstatus') {
            if (value == '0') return 'Pending';
            else if (value == '1') return 'Passed';
            else if (value == '2') return 'Failed';
        } else if(column.dataIndex === 'kycstatus') {
            if (value == '0') return 'Incomplete';
            else if (value == '1') return 'Passed';
            else if (value == '2') return 'Pending';
            else if (value == '7') return 'Failed';
        } else if(column.dataIndex === 'amlastatus') {
            if (value == '0') return 'Pending';
            else if (value == '1') return 'Passed';
            else if (value == '2') return 'Failed';
        } else if(Ext.isDate(value)) value = Ext.Date.format(value, 'D H:i:s F d, Y (O)');
        if (Ext.isFunction(me['onCustomGetItemDetail'])) {
            var customValue = this.onCustomGetItemDetail(record, column);
            if (customValue != '' && customValue != null && customValue !== undefined) value = customValue;
        }
        return value;
    },

    getStatusString: function (value) {
        if (value == '0') value = 'Inactive';
        else if (value == '1') value = 'Active';
        else if (value == '2') value = 'Suspended';
        else if (value == '4') value = 'Blacklisted';
        else if (value == '5') value = 'Closed';
        else value = 'Unidentified';

        return value;
    },

    // Do print
    getPrintReport: function(btn){

        var myView = this.getView(),
        // grid header data
        header = [];
        partnerCode = myView.partnerCode;

        // Bmmb custom export
        // if(partnerCode == 'BMMB'){
        //     reportingFields = [
        //         ['Date', ['createdon', 0]],
        //         ['Account Code', ['accountholdercode', 0]], 
        //         ['Full Name', ['fullname', 0]],
        //         ['My Kad No', ['mykadno', 'string']],
        //         ['Phone Number', ['phoneno', 0]],
        //         ['Email', ['email', 0]],
        //         ['Referral Branch Code', ['referralbranchcode', 0]],
        //         ['Referral Salesperson Code', ['referralsalespersoncode', 0]],
        //         ['Is PEP', ['ispep', 0]],
        //         ['Xau Balance (g)', ['xaubalance', 3]], 
        //         ['Status', ['status', 0]],
        //         ['PEP Status', ['pepstatus', 0]],
        //         ['KYC Status', ['kycstatus', 0]],
        //         ['AMLA Status', ['amlastatus', 0]],
        //         // ['Dormant', ['dormant', 0]],
                
        //     ];
        // }else{
        //     reportingFields = [
        //         ['Date', ['createdon', 0]],
        //         ['Account Code', ['accountholdercode', 0]], 
        //         ['Full Name', ['fullname', 0]],
        //         ['My Kad No', ['mykadno', 'string']],
        //         ['Phone Number', ['phoneno', 0]],
        //         ['Email', ['email', 0]],
        //         // ['Referral Branch Code', ['referralbranchcode', 0]],
        //         // ['Referral Salesperson Code', ['referralsalespersoncode', 0]],
        //         // ['Is PEP', ['ispep', 0]],
        //         ['Xau Balance (g)', ['xaubalance', 3]], 
        //         ['Status', ['status', 0]],
        //         // ['PEP Status', ['pepstatus', 0]],
        //         // ['KYC Status', ['kycstatus', 0]],
        //         // ['AMLA Status', ['amlastatus', 0]],
        //         // ['Dormant', ['dormant', 0]],
                
        //     ];
        // }
        const reportingFields = [
            ['Date', ['createdon', 0]], 
            
        ];
        //{ key1 : [val1, val2, val3] } 
        
        for (let [key, value] of reportingFields) {
            //alert(key + " = " + value);
            columnlist = {
                // [_key]: _value
                text: key,
                index: value[0]
            }
            
            if (value[0] !== 0){
                
                // Do check to convert string
                if (value[1] === 'string'){
                    columnlist.convert = value[1];
                    columnlist.decimal = 0;
                }else{
                    columnlist.decimal = value[1];
                }
            }

            header.push(columnlist);
        }

        btn.up('grid').getColumns().map(column => {
            if (column.isVisible() && column.dataIndex !== null){
                    _key = column.text
                    _value = column.dataIndex
                    columnlist = {
                        // [_key]: _value
                        text: _key,
                        index: _value
                    }
                    if (column.exportdecimal !== null){
                        _decimal = column.exportdecimal;
                        columnlist.decimal = _decimal;
                    }
                    if('dormant' == column.dataIndex || 'ordstatus' == column.dataIndex || 'createdon' == column.dataIndex){
                        // dont push header if its status
                    }else {
                        header.push(columnlist);
                    }
                  
                }
            });

        startDate = this.getView().getReferences().startDate.getValue()
        endDate = this.getView().getReferences().endDate.getValue()

        if (startDate && endDate){
            startDate = Ext.Date.format(startDate,'Y-m-d 00:00:00');
            endDate = Ext.Date.format(endDate,'Y-m-d 23:59:59');
            daterange = {
                startDate: startDate,
                endDate: endDate,
            }
        }else{
            Ext.MessageBox.show({
                title: 'Filter Date',
                msg: 'Start date and End date required.',
                buttons: Ext.MessageBox.OK,
                icon: Ext.MessageBox.ERROR
            });
            return
        }

        header = encodeURI(JSON.stringify(header));
        daterange = encodeURI(JSON.stringify(daterange));

        url = '?hdl=myaccountholderhandler&action=exportExcelAccounts&header='+header+'&daterange='+daterange+'&partnercode='+partnerCode;
        // url = Ext.urlEncode(url);

        Ext.DomHelper.append(document.body, {
            tag: 'iframe',
            id:'downloadIframe',
            frameBorder: 0,
            width: 0,
            height: 0,
            css: 'display:none;visibility:hidden;height: 0px;',
            src: url
          });
    },
    
    getDateRange: function(){ 

        // _this = this;
        vm = this.getViewModel();

        startDate = this.getView().getReferences().startDate.getValue()
        endDate = this.getView().getReferences().endDate.getValue()

        if (startDate && endDate){
            startDate = Ext.Date.format(startDate,'Y-m-d 00:00:00');
            endDate = Ext.Date.format(endDate,'Y-m-d 23:59:59');
        }else{
            Ext.MessageBox.show({
                title: 'Filter Date',
                msg: 'Start date and End date required.',
                buttons: Ext.MessageBox.OK,
                icon: Ext.MessageBox.ERROR
            });
            return
        }
        this.getView().getStore().addFilter(
            {
                property: "createdon", type: "date", operator: "BETWEEN", value: [startDate, endDate]
            },
        )
    },

    clearDateRange: function(){
        startDate = this.getView().getReferences().startDate.setValue('')
        endDate = this.getView().getReferences().endDate.setValue('')
        filter = this.getView().getStore().getFilters().items[0];
        if (filter){
            this.getView().getStore().removeFilter(filter)
        }else{
            Ext.MessageBox.show({
                title: 'Clear Filter',
                msg: 'No Filter.',
                buttons: Ext.MessageBox.OK,
                icon: Ext.MessageBox.ERROR
            });
            return
        }
    },

    getPrintReportKtp: function (btn) {
        elmnt = this;
        // Set partnercode here
        myView = elmnt.getView();
        
        startDate = myView.getReferences().startDate.getValue();
        endDate = myView.getReferences().endDate.getValue();
        
        // get button path 
        buttonpath = btn.up('grid');

        if(!startDate || !endDate){
            Ext.MessageBox.show({
                title: 'Filter Date',
                msg: 'Please select date range within 2 months',
                buttons: Ext.MessageBox.OK,
                icon: Ext.MessageBox.ERROR
            });
        }
        else{
            _this = this;
            snap.getApplication().sendRequest({
              hdl: 'myaccountholderhandler', action: 'getMerchantList', partnercode: myView.partnerCode,
            }, 'Fetching data from server....').then(
              function (data) {
                if (data.success) {
                  //console.log(data.merchantdata);
                  var var2 = new Ext.Window({
                    iconCls: 'x-fa fa-cube',
                    header: {
                      style : 'background-color: #204A6D;border-color: #204A6D;',
                    },
                    scrollable: true,title: 'Print',layout: 'fit',width: 400,height: 500,
                    maxHeight: 2000,modal: true,plain: true,buttonAlign: 'center',xtype: 'form', 
                    margin: '0 5 5 0',
                    defaults: { labelWidth: 190, width: '100%', layout: 'hbox', hideLabel: false },
                    viewModel: {
                      data: {
                        name: "KTP",
                        merchantdata: data.merchantdata
                      }
                    },
                    items: [{
                      html:'<p>Select Merchant:</p>',margin: '10 50 50 20',xtype: 'form',reference: 'merchant-form',
                      items: [{
                        layout: 'column',
                        margin: '28 8 8 18',
                        width: '100%',
                        height: '100%',
                        reference: 'merchant-column-1',
                        items: []
                      }]
                    }],
                    buttons: [{
                      text: 'OK',
                      handler: function(){
                        // Point to Form with reference point
                        box = var2.lookupController().lookupReference('merchant-form').getForm();
                        // assign variable to form fields
                        form = box.getFieldValues();
                        var selected = "";
                        Object.entries(form).forEach((entry) => {
                          const [key, value] = entry;
                          if (value == true){
                            if(selected == ""){
                              selected += key;
                            }
                            else{
                              selected += ","+key;
                            }
                          }
                        });
                        if(selected == ""){
                          Ext.MessageBox.show({
                            title: 'Select Checkbox',
                            msg: 'Please select at least one option',
                            buttons: Ext.MessageBox.OK,
                            icon: Ext.MessageBox.WARNING
                          });
                        }
                        else{
                          _this.GenerateReport(selected, buttonpath);
                        }
                        //console.log("selected data: "+selected);
                      }
                    }],
                    closeAction: 'destroy',
                  });
                  // End create pop window
                    
                  // * Start adding checkbox based on the data
                  // Add to transferpanel
                  var panel =  var2.lookupController().lookupReference('merchant-column-1');
                  // panel.removeAll();
                  for(i = 0; i < data.merchantdata.length; i++){
                    panel.add({
                      columnWidth:0.5,
                      items: [{
                        xtype: 'checkbox', 
                        name: data.merchantdata[i].id, 
                        inputValue: '1', 
                        uncheckedValue: '0', 
                        reference: data.merchantdata[i].name, 
                        fieldLabel: data.merchantdata[i].name, 
                      }]
                    });
                  }
                  var2.show();
                }
                else{
                  Ext.MessageBox.show({
                    title: 'Alert',
                    msg: 'No data received',
                    height: 150,
                    buttons: Ext.MessageBox.OK,
                    icon: Ext.MessageBox.WARNING,
                  });
                }
                //console.log(data);
              }
            );
          }
      },

      getPrintReportPkbAffi: function (btn) {
        elmnt = this;
        // Set partnercode here
        myView = elmnt.getView();
        
        // get button path
        buttonpath = btn.up('grid');

        startDate = myView.getReferences().startDate.getValue();
        endDate = myView.getReferences().endDate.getValue();
        
        if(!startDate || !endDate){
            Ext.MessageBox.show({
                title: 'Filter Date',
                msg: 'Please select date range within 2 months',
                buttons: Ext.MessageBox.OK,
                icon: Ext.MessageBox.ERROR
            });
        }
        else{
            _this = this;
            snap.getApplication().sendRequest({
              hdl: 'myaccountholderhandler', action: 'getMerchantList', partnercode: myView.partnercode,
            }, 'Fetching data from server....').then(
              function (data) {
                if (data.success) {
                  //console.log(data.merchantdata);
                  var var2 = new Ext.Window({
                    iconCls: 'x-fa fa-cube',
                    header: {
                      style : 'background-color: #204A6D;border-color: #204A6D;',
                    },
                    scrollable: true,title: 'Print',layout: 'fit',width: 400,height: 500,
                    maxHeight: 2000,modal: true,plain: true,buttonAlign: 'center',xtype: 'form', 
                    margin: '0 5 5 0',
                    defaults: { labelWidth: 190, width: '100%', layout: 'hbox', hideLabel: false },
                    viewModel: {
                      data: {
                        name: "PKB AFFILIATE",
                        merchantdata: data.merchantdata
                      }
                    },
                    items: [{
                      html:'<p>Select Merchant:</p>',margin: '10 50 50 20',xtype: 'form',reference: 'merchant-form',
                      items: [{
                        layout: 'column',
                        margin: '28 8 8 18',
                        width: '100%',
                        height: '100%',
                        reference: 'merchant-column-1',
                        items: []
                      }]
                    }],
                    buttons: [{
                      text: 'OK',
                      handler: function(){
                        // Point to Form with reference point
                        box = var2.lookupController().lookupReference('merchant-form').getForm();
                        // assign variable to form fields
                        form = box.getFieldValues();
                        var selected = "";
                        Object.entries(form).forEach((entry) => {
                          const [key, value] = entry;
                          if (value == true){
                            if(selected == ""){
                              selected += key;
                            }
                            else{
                              selected += ","+key;
                            }
                          }
                        });
                        if(selected == ""){
                          Ext.MessageBox.show({
                            title: 'Select Checkbox',
                            msg: 'Please select at least one option',
                            buttons: Ext.MessageBox.OK,
                            icon: Ext.MessageBox.WARNING
                          });
                        }
                        else{
                          _this.GenerateReport(selected, buttonpath);
                        }
                        //console.log("selected data: "+selected);
                      }
                    }],
                    closeAction: 'destroy',
                  });
                  // End create pop window
                    
                  // * Start adding checkbox based on the data
                  // Add to transferpanel
                  var panel =  var2.lookupController().lookupReference('merchant-column-1');
                  // panel.removeAll();
                  for(i = 0; i < data.merchantdata.length; i++){
                    panel.add({
                      columnWidth:0.5,
                      items: [{
                        xtype: 'checkbox', 
                        name: data.merchantdata[i].id, 
                        inputValue: '1', 
                        uncheckedValue: '0', 
                        reference: data.merchantdata[i].name, 
                        fieldLabel: data.merchantdata[i].name, 
                      }]
                    });
                  }

                  var2.show();
                }
                else{
                  Ext.MessageBox.show({
                    title: 'Alert',
                    msg: 'No data received',
                    height: 150,
                    buttons: Ext.MessageBox.OK,
                    icon: Ext.MessageBox.WARNING,
                  });
                }
                //console.log(data);
              }
            );
          }
      },
    
      GenerateReport: function(selectedID, buttonpath){
        // grid header data
        header = [];
        partnerCode = myView.partnerCode;

        const reportingFields = [
            ['Date', ['createdon', 0]],
            // ['Account Code', ['accountholdercode', 0]], 
            // ['Full Name', ['fullname', 0]],
            // ['My Kad No', ['mykadno', 0]],
            // ['Is PEP', ['ispep', 0]],
            // ['Xau Balance (g)', ['xaubalance', 3]], 
            // ['Status', ['status', 0]],
            // ['PEP Status', ['pepstatus', 0]],
            // ['KYC Status', ['kycstatus', 0]],
            // ['AMLA Status', ['amlastatus', 0]],
            //'Dormant', ['dormant', 0]],
            
        ];
        //{ key1 : [val1, val2, val3] } 
        
        for (let [key, value] of reportingFields) {
            //alert(key + " = " + value);
            columnlist = {
                // [_key]: _value
                text: key,
                index: value[0]
            }
            
            if (value[0] !== 0){
                
                // Do check to convert string
                if (value[1] === 'string'){
                    columnlist.convert = value[1];
                    columnlist.decimal = 0;
                }else{
                    columnlist.decimal = value[1];
                }
            }
            header.push(columnlist);
        }

        buttonpath.getColumns().map(column => {
            if (column.isVisible() && column.dataIndex !== null){
                    _key = column.text
                    _value = column.dataIndex
                    columnlist = {
                        // [_key]: _value
                        text: _key,
                        index: _value
                    }
                    if (column.exportdecimal !== null){
                        _decimal = column.exportdecimal;
                        columnlist.decimal = _decimal;
                    }
                    if('dormant' == column.dataIndex || 'ordstatus' == column.dataIndex || 'createdon' == column.dataIndex){
                        // dont push header if its status
                    }else {
                        header.push(columnlist);
                    }
                  
                }
            });

        startDate = myView.getReferences().startDate.getValue()
        endDate = myView.getReferences().endDate.getValue()

        if (startDate && endDate){
            startDate = Ext.Date.format(startDate,'Y-m-d 00:00:00');
            endDate = Ext.Date.format(endDate,'Y-m-d 23:59:59');
            daterange = {
                startDate: startDate,
                endDate: endDate,
            }
        }else{
            Ext.MessageBox.show({
                title: 'Filter Date',
                msg: 'Start date and End date required.',
                buttons: Ext.MessageBox.OK,
                icon: Ext.MessageBox.ERROR
            });
            return
        }

        header = encodeURI(JSON.stringify(header));
        daterange = encodeURI(JSON.stringify(daterange));

        url = '?hdl=myaccountholderhandler&action=exportExcelAccounts&header='+header+'&daterange='+daterange+'&partnercode='+partnerCode+'&selected='+selectedID;
        // url = Ext.urlEncode(url);

        Ext.DomHelper.append(document.body, {
            tag: 'iframe',
            id:'downloadIframe',
            frameBorder: 0,
            width: 0,
            height: 0,
            css: 'display:none;visibility:hidden;height: 0px;',
            src: url
          });
      },

      onFtpUploadLoan: function(btn, formAction) {
        var me = this, selectedRecord,
            myView = this.getView();
        var sm = myView.getSelectionModel();
        

        var gridFormView = Ext.create(myView.formClass, Ext.apply(myView.uploadcollectionposform ? myView.uploadcollectionposform : {}, {
            formDialogButtons: [{
                xtype:'panel',
                flex:3
            },
            {
                text: 'Upload File',
                flex: 2,
                handler: function(btn) {
                    me._uploadFile(btn);
                }
            },{
                text: 'Close',
                flex: 1,
                handler: function(btn) {
                    owningWindow = btn.up('window');
                    owningWindow.close();
                    me.gridFormView = null;
                }
            },{
                xtype:'panel',
                flex: 2,
            }]
        }));


        this.gridFormView = gridFormView;
        this._formAction = "edit";

        var addEditForm = this.gridFormView.down('form').getForm();

        gridFormView.title = 'Update ' + gridFormView.title + '...';
        // var sm = this.getView().getSelectionModel();
        // var selectedRecords = sm.getSelection();
        // var selectedRecord = selectedRecords[0];
        // if(Ext.isFunction(me['onPreLoadForm'])) {
        //     if(! this.onPreLoadForm( gridFormView, addEditForm, selectedRecord, function(updatedRecord){
        //         addEditForm.loadRecord(updatedRecord);
        //         if(Ext.isFunction(me['onPostLoadForm'])) this.onPostLoadForm( gridFormView, addEditForm, updatedRecord);
        //         me.gridFormView.show();
        //       })) return;
        // }
        // addEditForm.loadRecord(selectedRecord);
        // if(Ext.isFunction(me['onPostLoadForm'])) this.onPostLoadForm( gridFormView, addEditForm, selectedRecord);

        this.gridFormView.show();
    },
    _uploadFile: function(btn) {
        // var owningWindow = btn.up('window');
        // var gridFormPanel = owningWindow.down('form');
        var me = this,
        myView = this.getView();
        // console.log('form',btn);return
        form = btn.lookupController().lookupReference('grnposlist-form').getForm();
        transactionlisting = form.getFieldValues();
        if (form.isValid()) {
            if ( transactionlisting.grnposlist != null) {
                form.submit({
                  
                    
                    url: 'index.php?hdl=myaccountholder&action=updateAccountHolderLoanFTP&partnerid='+form.getFieldValues().partnerid,
                    // url: 'index.php?hdl=tender&action=uploadTenderFile',
                    dataType: "json",
                    // waitMsg: 'Uploading your Account Holder Loan list...',
                    success: function(fp, o) {
                        if (o.result.error){
                            Ext.Msg.alert('Exception', o.result.message);
                            return;
                        }
                        if (o.result.success){
                            Ext.Msg.alert('Success', 'Your excel list has converted to loan approval.');
                            
                            me.getView().getSelectionModel().deselectAll();
                            me.getView().getStore().reload();

                            owningWindow = modalBtn.up('window');
                            owningWindow.close();

                            snap.getApplication().getStore('snap.store.MyAccountHolder').reload()
                            return;
                        }
                    },
                    // failure: function(fp, o) {
                    //     if (o.result.error){
                    //         Ext.Msg.alert('Exception', o.result.message);
                    //         return;
                    //     }
                    //     if (o.result.success){
                    //         Ext.Msg.alert('Success', 'Your excel list has converted to loan approval.');
                            
                    //         me.getView().getSelectionModel().deselectAll();
                    //         me.getView().getStore().reload();

                    //         owningWindow = modalBtn.up('window');
                    //         owningWindow.close();

                    //         snap.getApplication().getStore('snap.store.MyAccountHolder').reload()
                    //         return;
                    //     }
                    // },
                    
                });
                // snap.getApplication().sendRequest({
                //     hdl: 'myaccountholder', 'action': 'updateAccountHolderLoanFTP', partnerid: form.getFieldValues().partnerid,
                // }, 'Uploading your Account Holder Loan list...').then(
                //     function (data) {
                //         if (data.success) {
                //             Ext.Msg.alert('Success', 'Your excel list has converted to loan approval.');
                        
                //             me.getView().getSelectionModel().deselectAll();
                //             me.getView().getStore().reload();

                //             owningWindow = modalBtn.up('window');
                //             owningWindow.close();

                //             snap.getApplication().getStore('snap.store.MyAccountHolder').reload();
                //         } else {
                //             Ext.MessageBox.show({
                //                 title: 'Error Message',
                //                 msg: data.errorMessage,
                //                 buttons: Ext.MessageBox.OK, icon: Ext.MessageBox.ERROR
                //             });
                //         }
                // });
            } else {
                    Ext.MessageBox.show({
                    title: "ERROR-A1001",
                    msg: "Choose correct .xlsx file",
                    buttons: Ext.MessageBox.OK,
                    icon: Ext.MessageBox.WARNING
                });
            }
        } else {
                Ext.MessageBox.show({
                title: "ERROR-A1001",
                msg: "Please fill the required fields correctly.",
                buttons: Ext.MessageBox.OK,
                icon: Ext.MessageBox.WARNING
            });
        }
    },

    // Upload member list 
    onFtpUploadMember: function(btn, formAction) {
        var me = this, selectedRecord,
            myView = this.getView();
        var sm = myView.getSelectionModel();
        

        var gridFormView = Ext.create(myView.formClass, Ext.apply(myView.uploadmemberlistform ? myView.uploadmemberlistform : {}, {
            formDialogButtons: [{
                xtype:'panel',
                flex:3
            },
            {
                text: 'Upload File',
                flex: 2,
                handler: function(btn) {
                    me._uploadMemberFile(btn);
                }
            },{
                text: 'Close',
                flex: 1,
                handler: function(btn) {
                    owningWindow = btn.up('window');
                    owningWindow.close();
                    me.gridFormView = null;
                }
            },{
                xtype:'panel',
                flex: 2,
            }]
        }));


        this.gridFormView = gridFormView;
        this._formAction = "edit";

        var addEditForm = this.gridFormView.down('form').getForm();

        gridFormView.title = 'Upload ' + gridFormView.title + '...';
        // var sm = this.getView().getSelectionModel();
        // var selectedRecords = sm.getSelection();
        // var selectedRecord = selectedRecords[0];
        // if(Ext.isFunction(me['onPreLoadForm'])) {
        //     if(! this.onPreLoadForm( gridFormView, addEditForm, selectedRecord, function(updatedRecord){
        //         addEditForm.loadRecord(updatedRecord);
        //         if(Ext.isFunction(me['onPostLoadForm'])) this.onPostLoadForm( gridFormView, addEditForm, updatedRecord);
        //         me.gridFormView.show();
        //       })) return;
        // }
        // addEditForm.loadRecord(selectedRecord);
        // if(Ext.isFunction(me['onPostLoadForm'])) this.onPostLoadForm( gridFormView, addEditForm, selectedRecord);

        this.gridFormView.show();
    },
    _uploadMemberFile: function(btn) {
        // var owningWindow = btn.up('window');
        // var gridFormPanel = owningWindow.down('form');
        var me = this,
        myView = this.getView();
        // console.log('form',btn);return
        form = btn.lookupController().lookupReference('grnposlist-form').getForm();
        transactionlisting = form.getFieldValues();
        if (form.isValid()) {
            if ( transactionlisting.grnposlist != null) {
                form.submit({
                  
                    
                    url: 'index.php?hdl=myaccountholder&action=updateAccountHolderMemberFTP&partnercode='+myView.partnerCode,
                    // url: 'index.php?hdl=tender&action=uploadTenderFile',
                    dataType: "json",
                    // waitMsg: 'Uploading your Account Holder Loan list...',
                    success: function(fp, o) {
                        if (o.result.error){
                            Ext.Msg.alert('Exception', o.result.message);
                            return;
                        }
                        if (o.result.success){
                            Ext.Msg.alert('Success', 'Your excel list has converted to loan approval.');
                            
                            me.getView().getSelectionModel().deselectAll();
                            me.getView().getStore().reload();

                            owningWindow = modalBtn.up('window');
                            owningWindow.close();

                            snap.getApplication().getStore('snap.store.MyAccountHolder').reload()
                            return;
                        }
                    },
                    // failure: function(fp, o) {
                    //     if (o.result.error){
                    //         Ext.Msg.alert('Exception', o.result.message);
                    //         return;
                    //     }
                    //     if (o.result.success){
                    //         Ext.Msg.alert('Success', 'Your excel list has converted to loan approval.');
                            
                    //         me.getView().getSelectionModel().deselectAll();
                    //         me.getView().getStore().reload();

                    //         owningWindow = modalBtn.up('window');
                    //         owningWindow.close();

                    //         snap.getApplication().getStore('snap.store.MyAccountHolder').reload()
                    //         return;
                    //     }
                    // },
                    
                });
                // snap.getApplication().sendRequest({
                //     hdl: 'myaccountholder', 'action': 'updateAccountHolderLoanFTP', partnerid: form.getFieldValues().partnerid,
                // }, 'Uploading your Account Holder Loan list...').then(
                //     function (data) {
                //         if (data.success) {
                //             Ext.Msg.alert('Success', 'Your excel list has converted to loan approval.');
                        
                //             me.getView().getSelectionModel().deselectAll();
                //             me.getView().getStore().reload();

                //             owningWindow = modalBtn.up('window');
                //             owningWindow.close();

                //             snap.getApplication().getStore('snap.store.MyAccountHolder').reload();
                //         } else {
                //             Ext.MessageBox.show({
                //                 title: 'Error Message',
                //                 msg: data.errorMessage,
                //                 buttons: Ext.MessageBox.OK, icon: Ext.MessageBox.ERROR
                //             });
                //         }
                // });
            } else {
                    Ext.MessageBox.show({
                    title: "ERROR-A1001",
                    msg: "Choose correct .xlsx file",
                    buttons: Ext.MessageBox.OK,
                    icon: Ext.MessageBox.WARNING
                });
            }
        } else {
                Ext.MessageBox.show({
                title: "ERROR-A1001",
                msg: "Please fill the required fields correctly.",
                buttons: Ext.MessageBox.OK,
                icon: Ext.MessageBox.WARNING
            });
        }
    },

    //Display Identity Photo
    displayIdentityPhoto: function(btn){
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
        }
        else{
            Ext.MessageBox.show({
                title: 'Warning', buttons: Ext.MessageBox.OK, icon: Ext.MessageBox.WARNING,
                msg: 'Select a record first'
            });
            return;
        }

        snap.getApplication().sendRequest({
            hdl: 'myaccountholder', 'action': 'getIdentityPhoto', id: selectedRecords[0].data.id, partnercode:'NUBEX'
        }, 'Sending request....').then(
            function (data) {
                console.log(data.data.mykadno);

                // init form
                let win = new Ext.Window ({
                    title:'Display Identity Image',
                    layout:'form',
                    closeAction:'close',
                    items: [
                        {
                            xtype: 'fieldcontainer',
                            layout: 'hbox',
                            width:800,
                            items: [
                                {
                                    xtype: 'fieldcontainer',
                                    layout: 'vbox',
                                    items: [
                                        {
                                            xtype : 'textfield',
                                            fieldLabel: 'Name',
                                            readOnly: true,
                                            value: data.data.name,                
                                        },
                                        {
                                            xtype: 'textfield',
                                            readOnly: true,
                                            fieldLabel: 'Identity No',
                                            value: data.data.mykadno
                                        },
                                    ]
                                },        
                            ]
                        },
                        {
                            xtype: 'fieldcontainer',
                            layout: 'hbox',
                            width:800,
                            items:[
                                {
                                    layout:'form',
                                    flex:1,
                                    style: 'text-align:center;',
                                    items: [
                                        {
                                            width:350,
                                            html: data.data.front_image,
                                        },
                                        {
                                            xtype:'label',
                                            text: 'Front Image'  
                                        }
                                      ]
                                },
                                {
                                    layout:'form',
                                    flex:1,
                                    style: 'text-align:center;',
                                    items: [
                                        {
                                            width:350,
                                            html: data.data.back_image,
                                            style: 'text-align:center;',
                                        },
                                        {
                                            xtype:'label',
                                            text: 'Back Image'
                                        }
                                    ]
                                }
                            ]
                        },
                    ],
                    buttons: [{
                       text: 'Close',
                       handler: function() {
                           win.close();                   
                       }
                    }],
                    buttonAlign: 'center',
                });

                win.show();
                
            }
        );

    },
    
    activateDormant: function (btn) {
        let me = this;
        let selection = me.getView().getSelectionModel().getSelection()[0];
        if (! selection) {
            Ext.MessageBox.show({
                title: 'Warning', buttons: Ext.MessageBox.OK, icon: Ext.MessageBox.WARNING,
                msg: 'Select a record first'
            });
        }

        let win = new Ext.Window ({
            title:'Account Activation Confirmation',
            layout:'form',
            width:530,
            closeAction:'close',
            
            items: [{
                xtype : 'textfield',
                fieldLabel: 'Account Code',
                readOnly: true,
                value: selection.get('accountholdercode'),                
            },{
                xtype: 'textfield',
                readOnly: true,
                fieldLabel: 'Fullname',
               value: selection.get('fullname')
            },{
               xtype : 'textfield',
                fieldLabel: 'NRIC',
                readOnly: true,
                value: selection.get('mykadno')
            },{
                xtype : 'textarea',
                fieldLabel: 'Remarks',
                value: selection.get('statusremarks'),
                itemId: 'suspensionremarks'
            },{
                boxLabel  : 'Note: This will reactivate the account. Tick to confirm',
                xtype: 'checkboxfield',
                itemId: 'suspensionconfirmation'
             }],
            
            buttons: [{
                text: 'Activate',
                handler: function () {
                    let remarks = win.items.get('suspensionremarks').getValue();
                    let confirmation = win.items.get('suspensionconfirmation').getValue();

                    if ('' == remarks) {
                        Ext.MessageBox.show({
                            title: 'Error',
                            msg: 'Remarks is required',
                            buttons: Ext.MessageBox.OK, icon: Ext.MessageBox.ERROR
                        });
                        return;
                    }

                    if (!confirmation) {
                        Ext.MessageBox.show({
                            title: 'Error',
                            msg: 'Please tick the checkbox to continue',
                            buttons: Ext.MessageBox.OK, icon: Ext.MessageBox.ERROR
                        });
                        return;
                    }
                    
                    win.close();
                    snap.getApplication().sendRequest({
                        hdl: 'myaccountholder', 'action': 'activateDormant', id: selection.get('id'), 'remarks': remarks
                    }, 'Sending request....').then(
                        function (data) {
                            if (data.success) {
                                me.getView().getSelectionModel().deselectAll();
                                me.getView().getStore().reload();

                                owningWindow = modalBtn.up('window');
                                owningWindow.close();

                            } else {
                                Ext.MessageBox.show({
                                    title: 'Error',
                                    msg: data.errorMessage,
                                    buttons: Ext.MessageBox.OK, icon: Ext.MessageBox.ERROR
                                });
                            }
                        });
                }
            },{
               text: 'Cancel',
               handler: function() {
                   win.close();                   
               }
            }],
            buttonAlign: 'center',
        });

        win.show();
    },
});
