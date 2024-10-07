Ext.define('snap.view.vaultitemtrans.vaultitemtransController', {
    extend: 'snap.view.gridpanel.BaseController',
    alias: 'controller.vaultitemtrans',
   
    printButton: function(btn)  {
        // Add print function here 
        var myView = this.getView(),
            me = this, record;
        var sm = myView.getSelectionModel();        
        var selectedRecords = sm.getSelection();     
        var address=selectedRecords[0].data.address1+' '+selectedRecords[0].data.address2+' '+selectedRecords[0].data.address3;
        
        var record = selectedRecords[0].data;
        

        var url = 'index.php?hdl=vaultitemtrans&action=getPrintDocuments&id='+record.id;
        Ext.Ajax.request({
            url: url,
            method: 'get',
            waitMsg: 'Processing',
            //params: { summaryfromdate: summaryfromdate, summarytodate: summarytodate, summarytype: summarytype },
            autoAbort: false,
            success: function (result) {
                var win = window.open('');
                    win.location = url;
                    win.focus();
            },
            failure: function () {
                
                Ext.MessageBox.show({
                    title: 'Error Message',
                    msg: 'Failed to retrieve data.',
                    buttons: Ext.MessageBox.OK,
                    icon: Ext.MessageBox.ERROR
                });
            }
        });

        // var url = 'index.php?hdl=logistic&action=getPrintDocuments';
        // Ext.DomHelper.append(document.body, {
        //     tag: 'iframe',
        //     id:'downloadIframe',
        //     frameBorder: 0,
        //     width: 0,
        //     height: 0,
        //     css: 'display:none;visibility:hidden;height: 0px;',
        //     src: url
        // });

        // MY CODE -- START
        var selectedRecords = sm.getSelection(); 
        // console.log(selectedRecords);return;
        var record = selectedRecords[0].data;

        snap.getApplication().sendRequest({
            hdl: 'vaultitemtrans', action: 'getPrintDocuments', id: record.id, recordType: record.type,
        }, 'Fetching data from server....').then(
        //Received data from server already
        function(data){
            if(data.success){
                
            }
        });
        return false;
        // MY CODE -- END
    },

    
    printGoldBarListButton: function (elemnt) {
        
        var url = 'index.php?hdl=myvaultitem&action=doGoldBarList';
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

    onPreAddEditSubmit: function(formAction, theGridFormPanel, theGridForm) {
        console.log(formAction, theGridFormPanel, theGridForm,'onPreAddEditSubmit');

        polists = theGridFormPanel.getController().lookupReference('pocontainer').getStore().data.items;
        scheduledate = theGridFormPanel.getController().lookupReference('scheduledate');
        
        
        trans_po = []
        // Update po with scheduled date
        polists.map(function(value, index){
            po = {
                "id": value.data.id,
            }
            trans_po.push(po);
            // total_weight += parseFloat(value.data.docTotalAmt)
        })

        customer = theGridFormPanel.getController().lookupReference('documentcombox').value
        req = {
            'po': trans_po,
            'type': customer,
        }
        // console.log(req);return;

        snap.getApplication().sendRequest({
            hdl: 'vaultitem', 
            action: 'createdocuments', 
            data: JSON.stringify(req),
            scheduledate:  scheduledate.getValue(),
        }, 'Fetching data from server....')
            .then(
                //Received data from server already
                function(data){
                    if(data.success){
                        Ext.MessageBox.show({
                            title: 'Create Document.',
                            msg: 'Successful',
                            buttons: Ext.MessageBox.OK,
                            icon: Ext.MessageBox.INFO
                        });
                        return false;
                        // snap.getApplication().sendRequest({
                        //     hdl: 'vaultitem', action: 'getPrintDocuments', id: data.id,
                        // }, 'Fetching data from server....').then(
                        // //Received data from server already
                        // function(data){
                        //     if(data.success){
                                
                        //     }
                        // });
                        // return false;
                    }
            });
    },


    editvaultitemtrans: function(btn){
        var myView = this.getView(),
            me = this, record;
        var sm = myView.getSelectionModel();
        var selectedRecords = sm.getSelection();
        
        var record = selectedRecords[0].data;

        myView = this.getView();
        me = this;
        var gridFormView = Ext.create(myView.formClass, Ext.apply(myView.editvaulttrans ? myView.editvaulttrans : {}, {
            formDialogButtons: [{
                xtype:'panel',
                flex:1
            },
            {
                text: 'Create Document',
                flex: 2.5,
                handler: function(btn) {
                    me._onSaveGridForm(btn);
                }
            },{
                text: 'Close',
                flex: 1,
                handler: function(btn) {
                    owningWindow = btn.up('window');
                    owningWindow.close();
                    me.gridFormView = null;
                }
            }]
        }));

        this.gridFormView = gridFormView;
        // this._formAction = "createDocuments";

        var addEditForm = this.gridFormView.down('form').getForm();

        snap.getApplication().sendRequest({
            hdl: 'vaultitemtrans', action: 'getTrans', id: record.id,
        }, 'Fetching data from server....').then(
        //Received data from server already
        function(data){
            if(data.success){
                addEditForm.setValues(data.data)
            }
        });

        gridFormView.title = 'Create ' + gridFormView.title + '...';
        
        this.gridFormView.show();
        
    },
    voidvaultitemtrans: function(){
        var myView = this.getView(),
            me = this, record;
        var sm = myView.getSelectionModel();
        var selectedRecords = sm.getSelection(); 
        var record = selectedRecords[0].data;

        Ext.MessageBox.confirm('Confirm', 'This will change cancel the current data.', function(id) {
            if (id == 'yes') {
                snap.getApplication().sendRequest({
                    hdl: 'vaultitemtrans', action: 'void', id: record.id,
                }, 'Fetching data from server....').then(
                //Received data from server already
                function(data){
                    if(data.success){
                        Ext.MessageBox.show({
                            title: 'Cancel Vault Transaction.',
                            msg: 'Successful',
                            buttons: Ext.MessageBox.OK,
                            icon: Ext.MessageBox.INFO
                        });
                        myView.getStore().reload()
                        snap.getApplication().getStore('snap.store.VaultItem').reload()
        
                        myView.lookupReferenceHolder().lookupReference('summarycontainer').doFireEvent('reloadsummary');
                    }else{
                        Ext.MessageBox.show({
                            title: 'Cancel Vault Transaction. Failed.',
                            msg: data.errorMessage,
                            buttons: Ext.MessageBox.OK,
                            icon: Ext.MessageBox.WARNING
                        });
                    }
                });
            }else{
                return false;
            }
        })
    },
    exportVaultListButton: function(btn){
        var myView = this.getView(),
            me = this, record;
        var sm = myView.getSelectionModel();
        var selectedRecords = sm.getSelection(); 

        //var record = selectedRecords[0].data;
        // snap.getApplication().sendRequest({
        //     hdl: 'vaultitemtrans', action: 'exportVaultList', partnercode: myView.partnerCode,
        // }, 'Fetching data from server....').then(
        // //Received data from server already
        // function(data){
        //     if(data.success){
        //         Ext.MessageBox.show({
        //             title: 'Print Document.',
        //             msg: 'Successful',
        //             buttons: Ext.MessageBox.OK,
        //             icon: Ext.MessageBox.INFO
        //         });
        //     }
        // });

        header = []
        var partnerCode = myView.partnerCode;  

        const reportingFields = [
            ['Date', ['createdon', 0]],
            ['Transaction Type', ['type', 0]], 
            ['Document No', ['documentno', 0]],
            ['Document Date On', ['documentdateon', 0]],
            ['From Vault Location', ['fromlocationname', 0]],
            ['To Vault Location', ['tolocationname', 0]],
            ['Cancel By', ['cancelbyname', 0]],
            ['Cancel On', ['cancelon', 0]],
            ['Status', ['status', 0]],
            
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
                columnlist.decimal = value[1];
            }
            header.push(columnlist);
        }

        startDate = '2000-01-01 00:00:00';
        endDate = '2100-01-01 23:59:59';
    
        daterange = {
            startDate: startDate,
            endDate: endDate,
        }

        header = encodeURI(JSON.stringify(header));
        daterange = encodeURI(JSON.stringify(daterange));

        url = '?hdl=vaultitemtrans&action=exportVaultList&header='+header+'&daterange='+daterange+'&partnercode='+partnerCode;
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


    approveButton: function(){
        var myView = this.getView(),
            me = this, record;
        var sm = myView.getSelectionModel();
        var selectedRecords = sm.getSelection(); 
        var record = selectedRecords[0].data;

        Ext.MessageBox.confirm('Confirm', 'This will approve the current data.', function(id) {
            if (id == 'yes') {
                snap.getApplication().sendRequest({
                    hdl: 'vaultitemtrans', action: 'approve', id: record.id,
                }, 'Fetching data from server....').then(
                //Received data from server already
                function(data){
                    if(data.success){
                        Ext.MessageBox.show({
                            title: 'Approve Vault Transaction.',
                            msg: 'Successful',
                            buttons: Ext.MessageBox.OK,
                            icon: Ext.MessageBox.INFO
                        });
                        myView.getStore().reload()
                        snap.getApplication().getStore('snap.store.VaultItem').reload()
        
                        myView.lookupReferenceHolder().lookupReference('summarycontainer').doFireEvent('reloadsummary');
                    }else{
                        Ext.MessageBox.show({
                            title: 'Approve Vault Transaction. Failed.',
                            msg: data.errorMessage,
                            buttons: Ext.MessageBox.OK,
                            icon: Ext.MessageBox.WARNING
                        });
                    }
                });
            }else{
                return false;
            }
        })
    },

    completeButton: function(){
        var myView = this.getView(),
            me = this, record;
        var sm = myView.getSelectionModel();
        var selectedRecords = sm.getSelection(); 
        var record = selectedRecords[0].data;

        Ext.MessageBox.confirm('Confirm', 'This will complete the current data.', function(id) {
            if (id == 'yes') {
                snap.getApplication().sendRequest({
                    hdl: 'vaultitemtrans', action: 'complete', id: record.id,
                }, 'Fetching data from server....').then(
                //Received data from server already
                function(data){
                    if(data.success){
                        Ext.MessageBox.show({
                            title: 'Complete Vault Transaction.',
                            msg: 'Successful',
                            buttons: Ext.MessageBox.OK,
                            icon: Ext.MessageBox.INFO
                        });
                        myView.getStore().reload()
                        snap.getApplication().getStore('snap.store.VaultItem').reload()
        
                        myView.lookupReferenceHolder().lookupReference('summarycontainer').doFireEvent('reloadsummary');
                    }else{
                        Ext.MessageBox.show({
                            title: 'Complete Vault Transaction. Failed.',
                            msg: data.errorMessage,
                            buttons: Ext.MessageBox.OK,
                            icon: Ext.MessageBox.WARNING
                        });
                    }
                });
            }else{
                return false;
            }
        })
    },
    
});


