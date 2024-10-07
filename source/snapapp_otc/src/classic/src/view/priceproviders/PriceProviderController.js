Ext.define('snap.view.priceproviders.PriceProviderController', {
    extend: 'snap.view.gridpanel.BaseController',
    alias: 'controller.priceprovider-priceprovider',

/*
    onPreLoadForm: function( formView, form, record, asyncLoadCallback) {
        var me = this;
        snap.getApplication().sendRequest({
            hdl: 'priceprovider', 'action': 'fillform', id: ((record && record.data) ? record.data.id : 0)
        }, 'Fetching data from server....').then(
        function(data) {
            if(data.success) {

                //formView.getController().lookupReference('type').getStore().loadData(data.type);
                formView.getController().lookupReference('pricesourcecode').setValue(data.pricesourcecode);
                /*
                formView.getController().lookupReference('leveltagid').getStore().loadData(data.pricecharges);
                formView.getController().lookupReference('nokrelationship').getStore().loadData(data.relationship);
                formView.getController().lookupReference('gender').getStore().loadData(data.gender);
                formView.getController().lookupReference('maritalstatus').getStore().loadData(data.marital);
                formView.getController().lookupReference('attachmentPicture').setValue(data.picture);
                formView.getController().lookupReference('ethnic').getStore().loadData(data.ethnic);
                formView.getController().lookupReference('nokgender').getStore().loadData(data.nokgender);
                //formView.getController().lookupReference('smoke').getStore().loadData(data.smoke);
                formView.getController().lookupReference('cardiodoc').getStore().loadData(data.cardiodoc);

                record.data.cardio = false;
                record.data.gp = false;

                if(record.data.type == data.patienttype[2].code) {
                    record.data.gp = true;
                    record.data.cardio = true;
                }
                else if(record.data.type == data.patienttype[0].code) {
                    record.data.gp = true;
                }
                else if(record.data.type == data.patienttype[1].code) {
                    record.data.cardio = true;
                }
            
            }
            
            if(Ext.isFunction(asyncLoadCallback)) asyncLoadCallback(record);
            else {
                record = Ext.apply(record, data.record);
                form.loadRecord(record);
            }
        });
        return false;
    },*/

    getPriceProviderStatus: function(record, column) {
      
        var myView = this.getView(),
            sm = this.getView().getSelectionModel(),
            selectedRecords = sm.getSelection(),
            priceproviderid = selectedRecords[0].data.id,
            name = selectedRecords[0].data.name;

        
   
            snap.getApplication().sendRequest({
                hdl: 'priceprovider', 'action': 'getPriceProviderStatus', id: priceproviderid, status: 1
            }, 'Processing....').then(
            function(data){
                if(data.success){
                    if(data.isrunning == 1){
                        Ext.MessageBox.show({
                            title: 'Alert', buttons: Ext.MessageBox.OK, icon: Ext.MessageBox.ALERT,
                            msg: 'Price Collector is running'});
                    }else{
                        Ext.MessageBox.show({
                            title: 'Alert', buttons: Ext.MessageBox.OK, icon: Ext.MessageBox.ALERT,
                            msg: 'Price Collector is not running'});
                    }
                    myView.getStore().reload();
                }
            });


        return false;
    },

    startPriceProvider: function(record, column) {

        var myView = this.getView(),
            sm = this.getView().getSelectionModel(),
            selectedRecords = sm.getSelection(),
            priceproviderid = selectedRecords[0].data.id,
            name = selectedRecords[0].data.name;

        Ext.MessageBox.confirm('Start Price Collector', 'Do you want to start Price Collection for '+name+'?', function(id) {
            if (id == 'yes') {
                snap.getApplication().sendRequest({
                    hdl: 'priceprovider', 'action': 'startPriceProvider', id: priceproviderid, status: 1
                }, 'Processing....').then(
                function(data){
                    if(data.success){
                        if(data.isrunning){
                            Ext.MessageBox.show({
                                title: 'Alert', buttons: Ext.MessageBox.OK, icon: Ext.MessageBox.ALERT,
                                msg: 'Price Collector is already running'});
                            myView.getStore().reload();
                        } else {
                            Ext.MessageBox.show({
                                title: 'Alert', buttons: Ext.MessageBox.OK, icon: Ext.MessageBox.ALERT,
                                msg: 'Price Collector is now running'});
                            myView.getStore().reload();
                        }
                        myView.getStore().reload();
                    }
                });
            }
        }, this);

        return false;
    },

    stopPriceProvider: function(record, column) {

        var myView = this.getView(),
            sm = this.getView().getSelectionModel(),
            selectedRecords = sm.getSelection(),
            priceproviderid = selectedRecords[0].data.id,
            name = selectedRecords[0].data.name;

        Ext.MessageBox.confirm('Stop Price Collector',  'Do you want to stop Price Collection for '+name+'?', function(id) {
            if (id == 'yes') {
                snap.getApplication().sendRequest({
                    hdl: 'priceprovider', 'action': 'stopPriceProvider', id: priceproviderid, status: 1
                }, 'Processing....').then(
                function(data){
                    if(data.success){
                        if(data.isstopped){
                            Ext.MessageBox.show({
                                title: 'Alert', buttons: Ext.MessageBox.OK, icon: Ext.MessageBox.ALERT,
                                msg: 'Price Collector is stopped'});
                            myView.getStore().reload();
                        } else {
                            Ext.MessageBox.show({
                                title: 'Alert', buttons: Ext.MessageBox.OK, icon: Ext.MessageBox.ALERT,
                                msg: 'Price Collector is not running'});
                            myView.getStore().reload();
                        }
                        myView.getStore().reload();
                    }
                });
            }
        }, this);

        return false;
    },

    toggleSpecificPriceProviderGroup: function(record, column) {

        let myView = this.getView(),
            value = record.value,
            sm = this.getView().getSelectionModel();
            // selectedRecords = sm.getSelection(),
            // priceproviderid = selectedRecords[0].data.id,
            // name = selectedRecords[0].data.name;

            _this = this;
            snap.getApplication().sendRequest({
              hdl: 'priceprovider', action: 'getPriceProviderGroup', value: value,
            }, 'Fetching data from server....').then(
              function (data) {
                if (data.success) {
                  //console.log(data.providerdata);
                  var var2 = new Ext.Window({
                    iconCls: 'x-fa fa-cube',
                    header: {
                        style : 'background-color: #204A6D;border-color: #204A6D;',
                        // style : 'border-color: #204A6D;',
                        titlePosition: 0,
                        items: [{
                            xtype: 'button',
                            text: 'Select All',
                            iconCls: 'x-fa fa-check',
                            reference: 'select-all',
                            // id: 'kyc-reminder-view-button',
                            //style: 'background-color: #B2C840'
                            style: 'border-radius: 20px;background-color: #606060;border-color: #204A6D',
                            listeners : {
                                render: function(p) {
                                
                                    this.getEl().dom.title = 'Select all price providers';
                
                                },
                                click: function(p) { 
                                    box = var2.lookupController().lookupReference('merchant-form').getForm();
                                    data = box.getFieldValues();
                                 
                                    // data.forEach(function(number, index){
                                    //     data[index] = true;
                                    // });
                                    if(p.selected){
                                        Object.keys(data).forEach(function (key){
                                            data[key] = false;
                                        })
                                        p.setText("Select All"); 
                                        p.selected = false;  
                                        p.setIconCls('x-fa fa-check')

                                    }else{
                                        Object.keys(data).forEach(function (key){
                                            data[key] = true;
                                        })
                                        p.setText("Deselect All");   
                                        p.selected = true;
                                        p.setIconCls('x-fa fa-times')

                                    }
                                  
                                    // Update combobox values;
                                    box.setValues(data);

                                }
                                
                            }
                        }]
                    },
                    scrollable: true,title: 'Start/Stop prices for ' + value,layout: 'fit',width: 400,height: 700,
                    maxHeight: 2000,modal: true,plain: true,buttonAlign: 'center',xtype: 'form', 
                    margin: '0 5 5 0',
                    defaults: { labelWidth: 190, width: '100%', layout: 'hbox', hideLabel: false },
                    viewModel: {
                      data: {
                        name: "KTP",
                        providerdata: data.providerdata
                      }
                    },
                    items: [{
                      html:'<p>Select Price Providers:</p>',margin: '10 50 50 20',xtype: 'form',reference: 'merchant-form',
                      scrollable: true,
                        items: [{
                            layout: 'column',
                            margin: '28 8 8 18',
                            width: '100%',
                            height: '100%',
                            reference: 'merchant-column-1',
                            scrollable: true,
                            items: []
                        },]
                    }],
                    buttons: [{
                      text: 'Start',
                      iconCls: 'x-fa fa-play',
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
                            let window = this.up().up();
                          _this.startSpecificPriceProviderGroup(value, selected, window);
                        }
                        //console.log("selected data: "+selected);
                      }
                    },{
                        text: 'Stop',
                        iconCls: 'x-fa fa-stop',
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
                            let window = this.up().up();
                            _this.stopSpecificPriceProviderGroup(value, selected, window);
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
                  for(i = 0; i < data.providerdata.length; i++){
                    // render color
                    if (data.providerdata[i].status == 0) {
                        //return 'Not Running';
                        label = '<span style="margin-right:5px;color:#c23f10" class="fa fa-square"></span><span>' + data.providerdata[i].name + '</span>';
                    } else if (data.providerdata[i].status == 1) {
                        //return 'Is Running';
                        label = '<span style="margin-right:5px;color:#0aad3b" class="fa fa-square"></span><span>' + data.providerdata[i].name + '</span>';
                    } else {
                        // Inactive    
                        label = '<span style="margin-right:5px;color:#bdb9b7" class="fa fa-square"></span><span>' + data.providerdata[i].name + '</span>';
                    }
                    panel.add({
                      columnWidth:0.5,
                      items: [{
                        xtype: 'checkbox', 
                        name: data.providerdata[i].id, 
                        inputValue: '1', 
                        uncheckedValue: '0', 
                        reference: data.providerdata[i].name, 
                        fieldLabel: label, 
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

        return false;
    },

    startSpecificPriceProviderGroup: function(value, selectedID, window) {

        let myView = this.getView();
        //     value = record.value,
        //     sm = this.getView().getSelectionModel();
            // selectedRecords = sm.getSelection(),
            // priceproviderid = selectedRecords[0].data.id,
            // name = selectedRecords[0].data.name;

        Ext.MessageBox.confirm('Start Price Collector',  'Do you want to start Price Collection for all '+value+'?', function(id) {
            if (id == 'yes') {
                snap.getApplication().sendRequest({
                    hdl: 'priceprovider', 'action': 'startSpecificPriceProviderGroup', value: value, id: selectedID, status: 1
                }, 'Processing....').then(
                function(data){
                    if(data.success){
                        if(data.isstopped){
                            Ext.MessageBox.show({
                                title: 'Alert', buttons: Ext.MessageBox.OK, icon: Ext.MessageBox.ALERT,
                                msg: 'Price Collector is already running'});
                            myView.getStore().reload();
                        } else {
                            Ext.MessageBox.show({
                                title: 'Alert', buttons: Ext.MessageBox.OK, icon: Ext.MessageBox.ALERT,
                                msg: 'Price Collector is now running'});
                            myView.getStore().reload();
                        }
                        myView.getStore().reload();
                        window.close();
                    }   
                });
            }
        }, this);

        return false;
    },

    stopSpecificPriceProviderGroup: function(value, selectedID, window) {

        let myView = this.getView();
        //     value = record.value,
        //     sm = this.getView().getSelectionModel();
            // selectedRecords = sm.getSelection(),
            // priceproviderid = selectedRecords[0].data.id,
            // name = selectedRecords[0].data.name;

        Ext.MessageBox.confirm('Stop Price Collector',  'Do you want to stop Price Collection for all '+value+'?', function(id) {
            if (id == 'yes') {
                snap.getApplication().sendRequest({
                    hdl: 'priceprovider', 'action': 'stopSpecificPriceProviderGroup', value: value, id: selectedID, status: 1
                }, 'Processing....').then(
                function(data){
                    if(data.success){
                        if(data.isstopped){
                            Ext.MessageBox.show({
                                title: 'Alert', buttons: Ext.MessageBox.OK, icon: Ext.MessageBox.ALERT,
                                msg: 'Price Collector is stopped'});
                            myView.getStore().reload();
                        } else {
                            Ext.MessageBox.show({
                                title: 'Alert', buttons: Ext.MessageBox.OK, icon: Ext.MessageBox.ALERT,
                                msg: 'Price Collector is not running'});
                            myView.getStore().reload();
                        }
                        myView.getStore().reload();
                        window.close();
                    }
                });
            }
        }, this);

        return false;
    },

});
