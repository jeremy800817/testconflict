Ext.define('snap.view.mymonthlysummary.MyMonthlySummaryController', {
  extend: 'snap.view.gridpanel.BaseController',
  alias: 'controller.mymonthlysummary-mymonthlysummary',

  getTransactionReport: function (btn) {

    var myView = this.getView();
    var selectedID;

    var sm = myView.getSelectionModel();
    var selectedRecords = sm.getSelection();
    if (selectedRecords.length == 1) {
      for (var i = 0; i < selectedRecords.length; i++) {
        selectedID = selectedRecords[i].get('id');
        break;
      }
    }


    monthEnd = this.getView().getReferences().monthEnd.getValue()

    if (monthEnd) {
      monthEnd = new Date(monthEnd);
      monthEnd = new Date(monthEnd.getFullYear(), monthEnd.getMonth() + 1, 0);
      monthEnd = Ext.Date.format(monthEnd, 'Y-m-d 23:59:59')
    } else {
      Ext.MessageBox.show({
        title: 'Filter Date',
        msg: 'Month end required.',
        buttons: Ext.MessageBox.OK,
        icon: Ext.MessageBox.ERROR
      });
      return
    }

    monthend = encodeURIComponent(JSON.stringify(monthEnd));

    url = '?hdl=mymonthlysummary&action=exportTransaction&monthend=' + monthEnd + '&accountholderid=' + selectedID;

    Ext.DomHelper.append(document.body, {
      tag: 'iframe',
      id: 'downloadIframe',
      frameBorder: 0,
      width: 0,
      height: 0,
      css: 'display:none;visibility:hidden;height: 0px;',
      src: url
    });
  },

  getPrintReport: function (btn) {

    // grid header data
    header = []
    btn.up('grid').getColumns().map(column => {
      if (column.isVisible() && column.dataIndex !== null) {
        _key = column.text
        _value = column.dataIndex
        columnlist = {
          // [_key]: _value
          text: _key,
          index: _value
        }
        if (column.exportdecimal != null) {
          _decimal = column.exportdecimal;
          columnlist.decimal = _decimal;
        } else {
          columnlist.string = 1;

        }
        header.push(columnlist);
      }
    });

    monthEnd = this.getView().getReferences().monthEnd.getValue()

    if (monthEnd) {
      monthEnd = new Date(monthEnd);
      monthEnd = new Date(monthEnd.getFullYear(), monthEnd.getMonth() + 1, 0);
      monthEnd = Ext.Date.format(monthEnd, 'Y-m-d 23:59:59')
    } else {
      Ext.MessageBox.show({
        title: 'Filter Date',
        msg: 'Month end required.',
        buttons: Ext.MessageBox.OK,
        icon: Ext.MessageBox.ERROR
      });
      return
    }

    monthend = encodeURIComponent(JSON.stringify(monthEnd));
    header = encodeURI(JSON.stringify(header));

    url = '?hdl=mymonthlysummary&action=exportExcel&partnercode=' + this.getView().partnercode + '&monthend=' + monthEnd + '&header=' + header;

    Ext.DomHelper.append(document.body, {
      tag: 'iframe',
      id: 'downloadIframe',
      frameBorder: 0,
      width: 0,
      height: 0,
      css: 'display:none;visibility:hidden;height: 0px;',
      src: url
    });
  },

  getDateRange: function () {

    // _this = this;
    vm = this.getViewModel();

    monthEnd = this.getView().getReferences().monthEnd.getValue()

    if (monthEnd) {
      monthEnd = new Date(monthEnd);
      monthEnd = new Date(monthEnd.getFullYear(), monthEnd.getMonth() + 1, 0);
      monthEnd = Ext.Date.format(monthEnd, 'Y-m-d 23:59:59')
    } else {


      Ext.MessageBox.show({
        title: 'Filter Date',
        msg: 'Month end required.',
        buttons: Ext.MessageBox.OK,
        icon: Ext.MessageBox.ERROR
      });
      return
    }
    store = this.getView().getStore();
    store.getProxy().setExtraParam('monthend', monthEnd);
    store.load();
  },

  clearDateRange: function () {
      monthEnd = this.getView().getReferences().monthEnd.setValue('')
      store = this.getView().getStore();
      delete store.getProxy().extraParams.includes
      store.load();
  },

  getPrintReportKtp: function (btn) {
    elmnt = this;
    // Set partnercode here
    myView = elmnt.getView();
    
    monthEnd = myView.getReferences().monthEnd.getValue();
    if (!monthEnd) {
      Ext.MessageBox.show({
        title: 'Filter Date',
        msg: 'Month end required.',
        buttons: Ext.MessageBox.OK,
        icon: Ext.MessageBox.ERROR
      });
      return
    } 
    else{
        _this = this;
        snap.getApplication().sendRequest({
          hdl: 'mymonthlysummary', action: 'getMerchantList', partnercode: myView.partnercode,
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
                      _this.GenerateReport(selected);
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

  getPrintReportKopetro: function (btn) {
    elmnt = this;
    // Set partnercode here
    myView = elmnt.getView();
    
    monthEnd = myView.getReferences().monthEnd.getValue();
    if (!monthEnd) {
      Ext.MessageBox.show({
        title: 'Filter Date',
        msg: 'Month end required.',
        buttons: Ext.MessageBox.OK,
        icon: Ext.MessageBox.ERROR
      });
      return
    } 
    else{
        _this = this;
        snap.getApplication().sendRequest({
          hdl: 'mymonthlysummary', action: 'getMerchantList', partnercode: myView.partnercode,
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
                    name: "KOPETRO",
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
                      _this.GenerateReport(selected);
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
  getPrintReportKopttr: function (btn) {
    elmnt = this;
    // Set partnercode here
    myView = elmnt.getView();
    
    monthEnd = myView.getReferences().monthEnd.getValue();
    if (!monthEnd) {
      Ext.MessageBox.show({
        title: 'Filter Date',
        msg: 'Month end required.',
        buttons: Ext.MessageBox.OK,
        icon: Ext.MessageBox.ERROR
      });
      return
    } 
    else{
        _this = this;
        snap.getApplication().sendRequest({
          hdl: 'mymonthlysummary', action: 'getMerchantList', partnercode: myView.partnercode,
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
                    name: "KOPTTR",
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
                      _this.GenerateReport(selected);
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

  getPrintReportBumira: function (btn) {
    elmnt = this;
    // Set partnercode here
    myView = elmnt.getView();
    
    monthEnd = myView.getReferences().monthEnd.getValue();
    if (!monthEnd) {
      Ext.MessageBox.show({
        title: 'Filter Date',
        msg: 'Month end required.',
        buttons: Ext.MessageBox.OK,
        icon: Ext.MessageBox.ERROR
      });
      return
    } 
    else{
        _this = this;
        snap.getApplication().sendRequest({
          hdl: 'mymonthlysummary', action: 'getMerchantList', partnercode: myView.partnercode,
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
                    name: "BUMIRA",
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
                      _this.GenerateReport(selected);
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
    
    monthEnd = myView.getReferences().monthEnd.getValue();
    if (!monthEnd) {
      Ext.MessageBox.show({
        title: 'Filter Date',
        msg: 'Month end required.',
        buttons: Ext.MessageBox.OK,
        icon: Ext.MessageBox.ERROR
      });
      return
    } 
    else{
        _this = this;
        snap.getApplication().sendRequest({
          hdl: 'mymonthlysummary', action: 'getMerchantList', partnercode: myView.partnercode,
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
                      _this.GenerateReport(selected);
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
  GenerateReport: function(selectedID){
    // grid header data
    header = []
    elmnt.getView('grid').getColumns().map(column => {
      if (column.isVisible() && column.dataIndex !== null) {
        _key = column.text
        _value = column.dataIndex
        columnlist = {
          // [_key]: _value
          text: _key,
          index: _value
        }
        if (column.exportdecimal != null) {
          _decimal = column.exportdecimal;
          columnlist.decimal = _decimal;
        } else {
          columnlist.string = 1;

        }
        header.push(columnlist);
      }
    });

    monthEnd = myView.getReferences().monthEnd.getValue()

    if (monthEnd) {
      monthEnd = new Date(monthEnd);
      monthEnd = new Date(monthEnd.getFullYear(), monthEnd.getMonth() + 1, 0);
      monthEnd = Ext.Date.format(monthEnd, 'Y-m-d 23:59:59')
    } else {
      Ext.MessageBox.show({
        title: 'Filter Date',
        msg: 'Month end required.',
        buttons: Ext.MessageBox.OK,
        icon: Ext.MessageBox.ERROR
      });
      return
    }

    monthend = encodeURIComponent(JSON.stringify(monthEnd));
    header = encodeURI(JSON.stringify(header));

    url = '?hdl=mymonthlysummary&action=exportExcel&partnercode=' + myView.partnercode + '&monthend=' + monthEnd +'&selected='+selectedID+ '&header=' + header;

    Ext.DomHelper.append(document.body, {
      tag: 'iframe',
      id: 'downloadIframe',
      frameBorder: 0,
      width: 0,
      height: 0,
      css: 'display:none;visibility:hidden;height: 0px;',
      src: url
    });
  }
});
