Ext.define('snap.view.mydailystoragefee.MyDailyStorageFeeController', {
    extend: 'snap.view.gridpanel.BaseController',
    alias: 'controller.mydailystoragefee-mydailystoragefee',


	getDateRange: function () {

		// _this = this;
		vm = this.getViewModel();

		startDate = this.getView().getReferences().startDate.getValue()
		endDate = this.getView().getReferences().endDate.getValue()

    if (startDate && endDate) {
      
      if (this.checkDateRangeExceedLimit(startDate, endDate)) {
        Ext.MessageBox.show({
          title: 'Filter Date',
          msg: 'Please select date range within 2 months',
          buttons: Ext.MessageBox.OK,
          icon: Ext.MessageBox.ERROR
        });

        return;
      }

			startDate = Ext.Date.format(startDate, 'Y-m-d 00:00:00');
			endDate = Ext.Date.format(endDate, 'Y-m-d 23:59:59');
		} else {
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
				property: "calculatedon", type: "date", operator: "BETWEEN", value: [startDate, endDate]
			},
		)
	},

	clearDateRange: function () {
		startDate = this.getView().getReferences().startDate.setValue('')
		endDate = this.getView().getReferences().endDate.setValue('')
		filter = this.getView().getStore().getFilters().items[0];
		if (filter) {
			this.getView().getStore().removeFilter(filter)
		} else {
			Ext.MessageBox.show({
				title: 'Clear Filter',
				msg: 'No Filter.',
				buttons: Ext.MessageBox.OK,
				icon: Ext.MessageBox.ERROR
			});
			return
		}
	},

	getPrintReport: function (btn) {

    var accountHolderId = this.getView().accountHolderId;
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


    startDate = this.getView().getReferences().startDate.getValue()
    endDate = this.getView().getReferences().endDate.getValue()

    if (startDate && endDate) {

      // if (this.checkDateRangeExceedLimit(startDate, endDate)) {
      //   Ext.MessageBox.show({
      //     title: 'Filter Date',
      //     msg: 'Please select date range within 2 months',
      //     buttons: Ext.MessageBox.OK,
      //     icon: Ext.MessageBox.ERROR
      //   });

      //   return;
      // }
      
      startDate = Ext.Date.format(startDate, 'Y-m-d 00:00:00');
      endDate = Ext.Date.format(endDate, 'Y-m-d 23:59:59');
      daterange = {
        startDate: startDate,
        endDate: endDate,
      }
    } else {
      Ext.MessageBox.show({
        title: 'Filter Date',
        msg: 'Start date and End date required.',
        buttons: Ext.MessageBox.OK,
        icon: Ext.MessageBox.ERROR
      });
      return
    }

    header = encodeURIComponent(JSON.stringify(header));
    daterange = encodeURIComponent(JSON.stringify(daterange));

    url = '?hdl=mydailystoragefee&action=exportExcel&accountholderid=' + accountHolderId + '&header=' + header + '&daterange=' + daterange + '&partnercode=' + this.getView().partnercode;
    // url = Ext.urlEncode(url);

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

  checkDateRangeExceedLimit: function (startDate, endDate) {

    // Do a daterange checker
    // If date exceeds 2 months, reject
    // Init date values
    var msecPerMinute = 1000 * 60;
    var msecPerHour = msecPerMinute * 60;
    var msecPerDay = msecPerHour * 24;

    // Calculate date interval 
    var interval = endDate - startDate;
    var intervalDays = Math.floor(interval / msecPerDay);


    // Get 2 months range limit for filter
    rangeLimit = 61;
    return intervalDays > rangeLimit;

  },

  getPrintReportKtp: function (){
    elmnt = this;
    // Set partnercode here
    myView = elmnt.getView();
    
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
            hdl: 'mydailystoragefee', action: 'getMerchantList', partnercode: myView.partnercode,
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
    });
}
},

getPrintReportKopetro: function (){
  elmnt = this;
  // Set partnercode here
  myView = elmnt.getView();
  
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
          hdl: 'mydailystoragefee', action: 'getMerchantList', partnercode: myView.partnercode,
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
  });
}
},
getPrintReportKopttr: function (){
  elmnt = this;
  // Set partnercode here
  myView = elmnt.getView();
  
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
          hdl: 'mydailystoragefee', action: 'getMerchantList', partnercode: myView.partnercode,
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
  });
}
},

getPrintReportBumira: function (){
    elmnt = this;
    // Set partnercode here
    myView = elmnt.getView();
    
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
            hdl: 'mydailystoragefee', action: 'getMerchantList', partnercode: myView.partnercode,
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
                            name: myView.partnercode,
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
    });
  }
  },

getPrintReportPkbAffi: function (){
    elmnt = this;
    // Set partnercode here
    myView = elmnt.getView();
    
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
            hdl: 'mydailystoragefee', action: 'getMerchantList', partnercode: myView.partnercode,
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
    });
  }
  },

GenerateReport: function(selectedID){
  var accountHolderId = myView.accountHolderId;
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


  startDate = myView.getReferences().startDate.getValue()
  endDate = myView.getReferences().endDate.getValue()

  if (startDate && endDate) {

    // if (this.checkDateRangeExceedLimit(startDate, endDate)) {
    //   Ext.MessageBox.show({
    //     title: 'Filter Date',
    //     msg: 'Please select date range within 2 months',
    //     buttons: Ext.MessageBox.OK,
    //     icon: Ext.MessageBox.ERROR
    //   });

    //   return;
    // }
    
    startDate = Ext.Date.format(startDate, 'Y-m-d 00:00:00');
    endDate = Ext.Date.format(endDate, 'Y-m-d 23:59:59');
    daterange = {
      startDate: startDate,
      endDate: endDate,
    }
  } else {
    Ext.MessageBox.show({
      title: 'Filter Date',
      msg: 'Start date and End date required.',
      buttons: Ext.MessageBox.OK,
      icon: Ext.MessageBox.ERROR
    });
    return
  }

  header = encodeURIComponent(JSON.stringify(header));
  daterange = encodeURIComponent(JSON.stringify(daterange));

  url = '?hdl=mydailystoragefee&action=exportExcel&accountholderid=' + accountHolderId + '&header=' + header + '&daterange=' + daterange + '&partnercode=' + myView.partnercode+'&selected='+selectedID;
  // url = Ext.urlEncode(url);

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
