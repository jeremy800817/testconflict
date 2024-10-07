Ext.define('snap.view.myledger.MyLedgerController', {
    extend: 'snap.view.gridpanel.BaseController',
    alias: 'controller.myledger-myledger',
    
    
    onPreLoadViewDetail: function(record, displayCallback) {
    	snap.getApplication().sendRequest({ hdl: 'myledger', action: 'detailview', id: record.data.id})
    	.then(function(data){
    		if(data.success) {
    			displayCallback(data.record);
    		}
    	})
        return false;
	},

    // Do print
    getPrintReport: function(btn){

        var myView = this.getView(),
        // grid header data
        header = [];
        partnerCode = myView.partnerCode;

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

        url = '?hdl=myledger&action=exportExcel&header='+header+'&daterange='+daterange+'&partnercode='+partnerCode;
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
              hdl: 'myledger', action: 'getMerchantList', partnercode: myView.partnerCode,
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
              hdl: 'myledger', action: 'getMerchantList', partnercode: myView.partnercode,
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

        url = '?hdl=myledger&action=exportExcel&header='+header+'&daterange='+daterange+'&partnercode='+partnerCode+'&selected='+selectedID;
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
});
