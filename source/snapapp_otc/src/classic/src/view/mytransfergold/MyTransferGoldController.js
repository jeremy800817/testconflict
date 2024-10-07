Ext.define('snap.view.mytransfergold.MyTransferGoldController', {
    extend: 'snap.view.gridpanel.BaseController',
    alias: 'controller.mytransfergold-mytransfergold',
    
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

        url = '?hdl=mytransfergold&action=exportExcel&header='+header+'&daterange='+daterange+'&partnercode='+partnerCode;
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
        elmntTransfer = this;
        // Set partnercode here
        myView = elmntTransfer.getView();
        
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
              hdl: 'mytransfergold', action: 'getMerchantList', partnercode: myView.partnerCode,
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
        elmntTransfer = this;
        // Set partnercode here
        myView = elmntTransfer.getView();
        
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
              hdl: 'mytransfergold', action: 'getMerchantList', partnercode: myView.partnercode,
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

        url = '?hdl=mytransfergold&action=exportExcelAccounts&header='+header+'&daterange='+daterange+'&partnercode='+partnerCode+'&selected='+selectedID;
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

    searchaccountholder: function(){
        var elmntTransfer = this.getView().getController();
        var sendersearchgrid = this.getView().getController().lookupReference('sendersearchresults');
        var receiversearchgrid = this.getView().getController().lookupReference('receiversearchresults');
        var senderic = Ext.ComponentQuery.query('textfield[id=searchsenderic]')[0].getValue();
        var receiveric = Ext.ComponentQuery.query('textfield[id=searchreceiveric]')[0].getValue();
        var type = Ext.ComponentQuery.query('combobox[id=searchtype]')[0].getValue();

        var validate = this._validatesearch(senderic,receiveric);
        var proceed = validate.proceed;
        var msg = validate.msg;
        

        if(proceed){
            elmntTransfer.lookupReference('sendername').setText('-');
            elmntTransfer.lookupReference('senderic').setText('-');
            elmntTransfer.lookupReference('senderemail').setText('-');
            elmntTransfer.lookupReference('sendermygoldaccountno').setText('-');
            elmntTransfer.lookupReference('senderstatus').setText('-');
            elmntTransfer.lookupReference('senderxau').setText('-');

            elmntTransfer.lookupReference('receivername').setText('-');
            elmntTransfer.lookupReference('receiveric').setText('-');
            elmntTransfer.lookupReference('receivermygoldaccountno').setText('-');
            elmntTransfer.lookupReference('receiveremail').setText('-');
            elmntTransfer.lookupReference('receiverstatus').setText('-');
            elmntTransfer.lookupReference('receiverxau').setText('-');

            vm.set('senderid','');
            vm.set('senderxau',0);
            vm.set('receiverid','');

            sendersearchgrid.getStore().proxy.url = 'index.php?hdl=myaccountholder&action=getOtcAccountHolders&searchparams='+senderic+'&partner='+PROJECTBASE+'&option='+type;
            sendersearchgrid.getStore().reload();

            receiversearchgrid.getStore().proxy.url = 'index.php?hdl=myaccountholder&action=getOtcAccountHolders&searchparams='+receiveric+'&partner='+PROJECTBASE+'&option='+type;
            receiversearchgrid.getStore().reload();
        }
        else{
            Ext.MessageBox.show({
                title: 'Warning', buttons: Ext.MessageBox.OK, icon: Ext.MessageBox.WARNING,
                msg: msg
            });
        }
        
        // searchpanel.setHidden(false);
        // Ext.ComponentQuery.query('label[id=senderic]')[0].setText(senderic);
        // Ext.ComponentQuery.query('label[id=receiveric]')[0].setText(receiveric);
        // console.log(senderic+" "+receiveric);
    },

    proceedconfirmtransfer: function(elmntTransfer){
        var senderid = vm.get('senderid');
        var senderxau = vm.get('senderxau');
        var receiverid = vm.get('receiverid');
        var amounttransfer = Ext.ComponentQuery.query('textfield[id=amounttransfer]')[0].getValue();
        var tellerremarks = Ext.ComponentQuery.query('textfield[id=tellerremarks]')[0].getValue();

        var validate = this._validateproceed();
        if(validate.proceed){
            // myView = this.getView();
            // me = this;

            // var confirm_view = Ext.create(myView.formClass, Ext.apply(myView.confirmationpopup, {
            //     classView: me,
            //     parentView: myView,
            // }));
            // confirm_view.setViewModel(vm);
            var me = this, selectedRecord,
            myView = this.getView();
            // var sm = myView.getSelectionModel();
            
            var confirm_view = Ext.create(myView.formClass, Ext.apply(myView.confirmationpopup ? myView.confirmationpopup : {}, {
                classView: me,
                parentView: myView,
            }));
            
            // Set view model
            confirm_view.setViewModel(vm);
            this.confirm_view = confirm_view;

            var elmntTransfer = this.getView().getController();
            
            confirm_view.controller.getView().lookupReference('confirmsendername').setValue(elmntTransfer.lookupReference('sendername').text);
            confirm_view.controller.getView().lookupReference('confirmsenderic').setValue(elmntTransfer.lookupReference('senderic').text);
            confirm_view.controller.getView().lookupReference('confirmsenderemail').setValue(elmntTransfer.lookupReference('senderemail').text);
            confirm_view.controller.getView().lookupReference('confirmsenderxau').setValue(elmntTransfer.lookupReference('senderxau').text);

            confirm_view.controller.getView().lookupReference('confirmreceivername').setValue(elmntTransfer.lookupReference('receivername').text);
            confirm_view.controller.getView().lookupReference('confirmreceiveric').setValue(elmntTransfer.lookupReference('receiveric').text);
            confirm_view.controller.getView().lookupReference('confirmreceiveremail').setValue(elmntTransfer.lookupReference('receiveremail').text);
            confirm_view.controller.getView().lookupReference('confirmreceiverxau').setValue(elmntTransfer.lookupReference('receiverxau').text);

            var remaining = this._transformthreedecimalplaces((senderxau - amounttransfer));
            confirm_view.controller.getView().lookupReference('confirmamounttransfer').setValue(parseFloat(amounttransfer).toFixed(3)+'g');
            confirm_view.controller.getView().lookupReference('confirmsenderbalanceaftertransfer').setValue(remaining+'g');
            confirm_view.controller.getView().lookupReference('confirmtellerremarks').setValue(tellerremarks);

            confirm_view.controller.getView().lookupReference('btnconfirmtransfer').show();
            confirm_view.controller.getView().lookupReference('btnprinttransferconfirmation').show();
            confirm_view.controller.getView().lookupReference('btntransferreceipt').hide();
            confirm_view.controller.getView().lookupReference('btnokclose').hide();

            this.confirm_view.show();
        }
        else{
            Ext.MessageBox.show({
                title: 'Warning', buttons: Ext.MessageBox.OK, icon: Ext.MessageBox.WARNING,
                msg: validate.msg
            });
        }
    },

    confirmtransfer: function(){
        vm.set('transferid', '');
        if(vm.get('isprinttransferconfirmation') == true){
            var element = this.getView().getController();
            // debugger;
            var senderid = vm.get('senderid');
            var receiverid = vm.get('receiverid');
            var amounttransfer = Ext.ComponentQuery.query('textfield[id=amounttransfer]')[0].getValue();
            var tellerremarks = Ext.ComponentQuery.query('textfield[id=tellerremarks]')[0].getValue();
            console.log('teller remarks value: '+tellerremarks);
            var pin1 = element.lookupReference('init_pin_1').getValue();
            var pin2 = element.lookupReference('init_pin_2').getValue();
            var pin3 = element.lookupReference('init_pin_3').getValue();
            var pin4 = element.lookupReference('init_pin_4').getValue();
            var pin5 = element.lookupReference('init_pin_5').getValue();
            var pin6 = element.lookupReference('init_pin_6').getValue();

            var pin = String(pin1)+String(pin2)+String(pin3)+String(pin4)+String(pin5)+String(pin6);

            //doTransfer
            snap.getApplication().sendRequest({ hdl: 'mytransfergold', action: 'doTransfer', 
                pin: pin,
                senderid:senderid,
                receiverid:receiverid,
                amounttransfer:amounttransfer,
                partner:PROJECTBASE,
                tellerremarks:tellerremarks,
                'rot':1
            }, 
            'Processing....').then(function(data){
                
                if(data.isawait){
                    vm.set('transferid', data.id);
                    // to close the confirmation popup
                    // element.getView().close();
                    element.getView().lookupReference('btnconfirmtransfer').hide();
                    element.getView().lookupReference('btnprinttransferconfirmation').hide();
                    

                    Ext.MessageBox.wait('Waiting For Approval...', 'Please wait', {
                        icon: 'my-loading-icon'
                    });
                    
                    const url = 'index.php?hdl=mytransfergold&action=checkApprovalStatus&id='+data.id+'&approve=yes';
                    const intervalId = setInterval(async () => {
                        try {
                            const response = await Ext.Ajax.request({
                                url: url,
                                method: 'GET'
                            });
                            const data = Ext.JSON.decode(response.responseText);

                            // if pending = false, trx is approved
                            if (!data.ispendingapproval) {
                                clearInterval(intervalId);
                                console.log('Approval process complete');
                                if(data.istransfersuccessful){
                                    Ext.MessageBox.show({
                                        title: 'Transfer Process Complete', 
                                        buttons: Ext.MessageBox.OK,
                                        iconCls: 'x-fa fa-check-circle',
                                        msg: 'Transfer '+ data.statusstring,
                                        // callback:function() { 
                                        //     this.elmntTransfer.getController().transferreceipt();
                                        // }
                                    });
                                    element.getView().lookupReference('btntransferreceipt').show();
                                    element.getView().lookupReference('btnokclose').show();

                                    return
                                }
                                else{
                                    
                                    Ext.MessageBox.show({
                                        title: 'Transfer Gold', 
                                        buttons: Ext.MessageBox.OK,
                                        icon: Ext.MessageBox.WARNING,
                                        msg: data.statusstring,
                                        // callback:function() { 
                                        //     this.elmntTransfer.getController().transferreceipt();
                                        // }
                                    });
                                    element.getView().close();
                                    this.elmntTransfer.getController().clearform();

                                    return
                                    // console.log(data);
                                }
                                // Do something with the data
                            }
                        } 
                        catch (error) {
                            console.log('Request failed');
                        }
                    }, 10000); // interval set to 10 seconds
                }
                else if(data.success) {
                    vm.set('transferid', data.id);
                    // to close the confirmation popup
                    // element.getView().close();
                    element.getView().lookupReference('btnconfirmtransfer').hide();
                    element.getView().lookupReference('btnprinttransferconfirmation').hide();
                    element.getView().lookupReference('btntransferreceipt').show();
                    element.getView().lookupReference('btnokclose').show();

                    vm.set('isprinttransferconfirmation', false);
                    // debugger;
                    Ext.MessageBox.show({
                        title: 'Success', 
                        buttons: Ext.MessageBox.OK, 
                        // icon: Ext.MessageBox.WARNING,
                        iconCls: 'x-fa fa-check-circle',
                        msg: "Proceed print receipt",
                        // callback:function() { 
                        //     this.elmntTransfer.getController().transferreceipt();
                        // }
                    });    
                }
            });
        }
        else{
            Ext.MessageBox.show({
                title: 'Warning', 
                buttons: Ext.MessageBox.OK, 
                icon: Ext.MessageBox.WARNING,
                msg: "Please print the Confirmation Document before proceed"
            });
        }
        
    },

    printconfirmtransfer: function(){
        var me = this;
        vm.set('isprinttransferconfirmation', true);
        
        var senderid = vm.get('senderid');
        var receiverid = vm.get('receiverid');
        var partnercode = vm.get('partnercode');
        var xau = Ext.ComponentQuery.query('textfield[id=amounttransfer]')[0].getValue();
        


        var url = 'index.php?hdl=mytransfergold&action=printAqad&senderid='+senderid+'&receiverid='+receiverid+'&xau='+xau+'&partnercode='+partnercode;
        Ext.Ajax.request({
            url: url,
            method: 'get',
            waitMsg: 'Processing',
            autoAbort: false,
            success: function (result) {
                //window.location.href = result.responseText;
                var win = window.open('');
                            win.location = result.responseText;
                           win.focus();
	    },
            failure: function () {
                Ext.MessageBox.show({
                    title: 'Error Message',
                    msg: 'Failed to retrieve data',
                    buttons: Ext.MessageBox.OK,
                    icon: Ext.MessageBox.ERROR
                });
            }
        });
    },

    _validatesearch: function(sender,receiver){
        var result = {"proceed":false,"msg":""};
        var proceed = true;
        var msg = "";
        if(sender == ''){
            proceed = false;
            msg = "Please fill in Sender Search Information";
        }
        else if (receiver == ''){
            proceed = false;
            msg = "Please fill in Receiver Search Information";
        }
        else if(receiver == sender){
            proceed = false;
            msg = "sender and receiver cannot be the same";
        }

        result.proceed = proceed;
        result.msg = msg;

        return result;
    },

    _validateproceed: function(){
        var senderid = vm.get('senderid');
        var senderxau = parseFloat(vm.get('senderxau').replace(/,/g ,''));
        var receiverid = vm.get('receiverid');
        var amounttransfer = Ext.ComponentQuery.query('textfield[id=amounttransfer]')[0].getValue();
        var tellerremarks = Ext.ComponentQuery.query('textfield[id=tellerremarks]')[0].getValue();

        var result = {"proceed":false,"msg":""};
        var proceed = true;
        var msg = "";

        if(senderid == ''){
            proceed = false;
            msg = 'Please select a sender record';
        }
        else if(receiverid == ''){
            proceed = false;
            msg = 'Please select a receiver record';
        }
        else if(amounttransfer == ''){
            proceed = false;
            //msg = 'Amount transfer must be more than or equal to 1g';
            msg = 'Amount transfer cannot be empty';
	}
        else if(parseFloat(senderxau) < parseFloat(amounttransfer)){
            proceed = false;
            msg = 'Sender Balance insufficient';
        }
        else if(tellerremarks == ''){
            proceed = false;
            msg = 'Teller remarks cannot be empty';
        }

        result.proceed = proceed;
        result.msg = msg;

        return result;
    },

    _transformthreedecimalplaces:function(n){
        return n.toFixed(3);
    },

    approvetransfertransaction: function (btn, formAction, id) {
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

    // Pass params to perform approve trx
    // param 1 : pass this pointer (object)
    // param 2 : trx id (int)
    _doApproveTransaction: function(me, transactionId) {

        var myView = me;
        
        trxid = transactionId;
        store = myView.getStore();
        var record = store.findRecord('id', trxid);

        if (record) {
            // Do something with the record...
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
                    // debugger;
                    var remarks = Ext.getCmp('transferremarks').getValue();
                    var approvalcode = Ext.getCmp('transferapprovalcode').getValue();
                    var originalamount = selectedRecords[0].data.xau;

                    Ext.MessageBox.confirm(
                        'Confirm Approval', 'Are you sure you want to approve ?', function (btn) {
                            if (btn === 'yes') {
                                if(PROJECTBASE == 'BSN' ){
                                    snap.getApplication().sendRequest({
                                        hdl: 'mytransfergold', 'action': 'approveTransfer', id: selectedRecords[0].data.id, 'remarks': remarks, 'approvalcode': approvalcode , 'rot':1,
                                    }, 'Sending request....').then(
                                        function (data) {
                                            if (data.success) {
                                                //debugger;
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
                                    // if(approvalcode != '' && approvalcode.length == 10){
                                        
                                    // }else{
                                    //     if(approvalcode.length == ''){
                                    //         Ext.Msg.show({
                                    //             title: 'Warning',
                                    //             message: 'Please input the approval code.',
                                    //             icon: Ext.MessageBox.WARNING,
                                    //             buttons: Ext.Msg.OK,
                                    //         });
                                    //     }else{
                                    //         Ext.Msg.show({
                                    //             title: 'Warning',
                                    //             message: 'The approval code cannot be less than 10 characters.',
                                    //             icon: Ext.MessageBox.WARNING,
                                    //             buttons: Ext.Msg.OK,
                                    //         });
                                    //     }
                                        
                                    // }
                                }else{
                                    snap.getApplication().sendRequest({
                                        hdl: 'mytransfergold', 'action': 'approveTransfer', id: selectedRecords[0].data.id, 'remarks': remarks, 'approvalcode': approvalcode , 'rot':1,
                                    }, 'Sending request....').then(
                                        function (data) {
                                            if (data.success) {
                                                //debugger;
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
                    // debugger;
                    var remarks = Ext.getCmp('transferremarks').getValue();
                    var approvalcode = Ext.getCmp('transferapprovalcode').getValue();
                    var originalamount = selectedRecords[0].data.xau;

                    Ext.MessageBox.confirm(
                        'Confirm Rejection', 'Are you sure you want to reject?', function (btn) {
                            if (btn === 'yes') {
                                snap.getApplication().sendRequest({
                                    hdl: 'mytransfergold', 'action': 'rejectTransfer', id: selectedRecords[0].data.id, 'remarks': remarks, 'approvalcode': approvalcode , 'rot':1,
                                }, 'Sending request....').then(
                                    function (data) {
                                        if (data.success) {
                                            // if success means order is rejected
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

        // Get Full Name
        //fullname = selectedRecords[i].get('fullname');
        gridFormView.controller.getView().lookupReference('transferid').setValue(trxid);
        gridFormView.controller.getView().lookupReference('fromFullname').setValue(record.data.fromfullname);
        gridFormView.controller.getView().lookupReference('toFullname').setValue(record.data.tofullname);
        gridFormView.controller.getView().lookupReference('transferreferenceno').setValue(refno);
        gridFormView.controller.getView().lookupReference('transferamount').setValue(ordamount);
        
        var fieldset = Ext.getCmp('approvaltransferfieldset');
        if(fieldset){
            // debugger;
            if(parseFloat(ordamount) < 0.1){
                fieldset.hide();
            }else{
                fieldset.show();
            }
        }else {
            console.error("Component with id 'approvalfieldset' not found.");
        }

        me.gridFormView = gridFormView;

        me.gridFormView.show();

        // Ext.Ajax.request({
        //     url: 'index.php?hdl=myorder&action=getInterval&transactionId='+trxid,
        //     method: 'get',
        //     autoAbort: false,
        //     success: function (result) {
        //         var data = JSON.parse(result.responseText);
        //         console.log(result);
        //             if(data.success){
        //                 me.gridFormView.show();
        //             }
        //             else{
        //                 Ext.MessageBox.show({
        //                     title: "Error",
        //                     msg: "Transactions Expired",
        //                     buttons: Ext.MessageBox.OK,
        //                     icon: Ext.MessageBox.WARNING
        //                 });
        //             } 
        //     },
        //     failure: function () {
              
        //       Ext.MessageBox.show({
        //         title: 'Error Message',
        //         msg: 'Failed to retrieve data',
        //         buttons: Ext.MessageBox.OK,
        //         icon: Ext.MessageBox.ERROR
        //       });
        //     }
        // });
    },

    transferreceipt: function(){
        var me = this;
        // debugger;
        var partnercode = vm.get('partnercode');
        var id = vm.get('transferid');
        vm.set('isprinttransferreceipt', true);

        this.getView().getController().clearform();
        var url = 'index.php?hdl=mytransfergold&action=printReceipt&id='+id+'&partnercode='+partnercode;
        Ext.Ajax.request({
            url: url,
            method: 'get',
            waitMsg: 'Processing',
            autoAbort: false,
            success: function (result) {
                //window.location.href = result.responseText;
                var win = window.open('');
                            win.location = result.responseText;
                           win.focus();
            },
            failure: function () {
                Ext.MessageBox.show({
                    title: 'Error Message',
                    msg: 'Failed to retrieve data',
                    buttons: Ext.MessageBox.OK,
                    icon: Ext.MessageBox.ERROR
                });
            }
        });


        
    },

    clearform: function(){
        //var elmntTransfer = this.getView().getController();
        // debugger;
        var sendersearchgrid = elmntTransfer.getController().lookupReference('sendersearchresults');
        var receiversearchgrid = elmntTransfer.getController().lookupReference('receiversearchresults');
        
        var type = Ext.ComponentQuery.query('combobox[id=searchtype]')[0].getValue();

        Ext.ComponentQuery.query('textfield[id=searchsenderic]')[0].setValue('');
        Ext.ComponentQuery.query('textfield[id=searchreceiveric]')[0].setValue('');
        Ext.ComponentQuery.query('textfield[id=amounttransfer]')[0].setValue('');
        Ext.ComponentQuery.query('textfield[id=tellerremarks]')[0].setValue('');

        sendersearchgrid.getStore().removeAll();
        receiversearchgrid.getStore().removeAll();

        elmntTransfer.lookupReference('sendername').setText('-');
        elmntTransfer.lookupReference('senderic').setText('-');
        elmntTransfer.lookupReference('sendermygoldaccountno').setText('-');
        elmntTransfer.lookupReference('senderemail').setText('-');
        elmntTransfer.lookupReference('senderstatus').setText('-');
        elmntTransfer.lookupReference('senderxau').setText('-');
        elmntTransfer.lookupReference('receivername').setText('-');
        elmntTransfer.lookupReference('receiveric').setText('-');
        elmntTransfer.lookupReference('receivermygoldaccountno').setText('-');
        elmntTransfer.lookupReference('receiveremail').setText('-');
        elmntTransfer.lookupReference('receiverstatus').setText('-');
        elmntTransfer.lookupReference('receiverxau').setText('-');

        vm.set('senderid','');
        vm.set('senderxau',0);
        vm.set('receiverid','');
        vm.set('transferid','');
        
    },

    okclose: function(){
        // debugger;
        if(vm.get('isprinttransferreceipt') == true){
            this.getView().getController().clearform();
            vm.set('isprinttransferreceipt', false);
            var element = this.getView().getController();
            element.getView().close();
        }
        else{
            Ext.MessageBox.show({
                title: 'Warning', 
                buttons: Ext.MessageBox.OK, 
                icon: Ext.MessageBox.WARNING,
                msg: "Please print the Receipt before proceed"
            });
        }
    },

    transferreceiptfromlist: function(){
        let me = this;
        let selection = me.getView().getSelectionModel().getSelection()[0];
        var partnercode = me.getView().partnerCode;
        var id = selection.get('id');
        var transactiondate = selection.get('createdon');
        var teller = selection.get('createdby');    
        
        var url = 'index.php?hdl=mytransfergold&action=printReceipt&id='+id+'&partnercode='+partnercode+'&teller='+teller+'&transactiondate='+transactiondate;
        Ext.Ajax.request({
            url: url,
            method: 'get',
            waitMsg: 'Processing',
            autoAbort: false,
            success: function (result) {
                var win = window.open('');
                win.location = result.responseText;
                win.focus();
            },
            failure: function () {
                Ext.MessageBox.show({
                    title: 'Error Message',
                    msg: 'Failed to retrieve data',
                    buttons: Ext.MessageBox.OK,
                    icon: Ext.MessageBox.ERROR
                });
            }
        });        
    },

    printconfirmtransferfromlist: function(){
        let me = this;
        let selection = me.getView().getSelectionModel().getSelection()[0];

        var senderid = selection.get('fromaccountholderid');
        var receiverid = selection.get('toaccountholderid');
        var partnercode = me.getView().partnerCode;
        var xau = selection.get('xau');
        var transactiondate = selection.get('createdon');
        var teller = selection.get('createdby');    

        var url = 'index.php?hdl=mytransfergold&action=printAqad&senderid='+senderid+'&receiverid='+receiverid+'&xau='+xau+'&partnercode='+partnercode+'&teller='+teller+'&transactiondate='+transactiondate;
        Ext.Ajax.request({
            url: url,
            method: 'get',
            waitMsg: 'Processing',
            autoAbort: false,
            success: function (result) {
                var win = window.open('');
                win.location = result.responseText;
                win.focus();
            },
            failure: function () {
                Ext.MessageBox.show({
                    title: 'Error Message',
                    msg: 'Failed to retrieve data',
                    buttons: Ext.MessageBox.OK,
                    icon: Ext.MessageBox.ERROR
                });
            }
        });
    },
});
