Ext.define('snap.view.redemption.MyConversionController', {
    extend: 'snap.view.redemption.RedemptionController',
    alias: 'controller.myconversion-myconversion',
    listeners: {
        render: function(store) {
            store.on('load', function(records) {               
            });
        }
    }, 
    
    onPreLoadViewDetail: function(record, displayCallback) {
        snap.getApplication().sendRequest({ hdl: 'myconversion', action: 'detailview', id: record.data.id})
        .then(function(data){
            if(data.success) {
                displayCallback(data.record);
            }
        })
        return false;
    },

    getConversionReport: function(btn){
        var myView = this.getView(),
        // grid header data
        header = [];
        partnerCode = myView.partnercode;
        //debugger;
        // Check if buy or sell based on button reference
        /*
        if('dailytransactionsell' == btn.reference){
            // filter by companysell
            
        }else if('dailytransactionbuy' == btn.reference){
            // filter by companybuy
        }
        */
       
        /*
        columnleft = {
            // [_key]: _value
                text: 'Date',
                index: 'createdon'
        }
        header.push(columnleft);
            
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
                if('rdmitems' == column.dataIndex){
                    // dont push header if its status
                }else {
                    header.push(columnlist);
                }
              
            }
        });
        */
        
        // Initialize Mapping for Excel Headers
        // change the value based on conversion db
        // [Key, [val1, val2]]
        // [Name, [dataIndex, decimal point]]
        
        // Check if BMMB, contain comissionm fee
        const reportingFields = [
            ['Conversion Status', ['rdmstatus', 0]],
            ['Payment Status', ['status', 0]],
            ['Date', ['createdon', 0]],

            ['Customer Code', ['accountholdercode', 0]],
            ['Customer Name', ['accountholdername', 0]],
            ['Customer NRIC', ['accountholdermykadno', 0]],
            ['Customer Eemail', ['accountholderemail', 0]],
            ['Customer Phone', ['accountholderphoneno', 0]],

            ['Conversion No', ['rdmredemptionno', 0]],
            ['Xau Weight (g)', ['rdmtotalweight', 3]],

            // end new format
            // ['Conversion Fee', ['rdmredemptionfee', 3]],
            ['Premium Fee', ['premiumfee', 3]],
            [partnerCode +' Commission Fee', ['commissionfee', 3]],
            ['Insurance Fee', ['rdminsurancefee', 3]],
            ['Handling Fee', ['rdmhandlingfee', 3]],
            // ['Delivery Fee', ['rdmspecialdeliveryfee', 3]],
            ['Total Amount', ['rdmtotalfee', 3]],

            ['Partner Ref No', ['rdmpartnerrefno', 0]], 
            ['Conversion Type', ['rdmtype', 0]],
            ['Item Code', ['code', 0]],
            ['Serial Number', ['serialnumber', 0]],

            ['Delivery Contact Name 1', ['rdmdeliverycontactname1', 0]],
            ['Delivery Contact No 1', ['rdmdeliverycontactno1', 0]],
            ['Delivery Contact Name 2', ['rdmdeliverycontactname2', 0]],
            ['Delivery Contact No 2', ['rdmdeliverycontactno2', 0]],
            ['Delivery Address', ['rdmdeliveryaddress', 0]],
            ['Delivery Postcode', ['rdmdeliverypostcode', 0]],
            ['Delivery State', ['rdmdeliverystate', 0]],
            ['Delivery Country', ['rdmdeliverycountry', 0]],

            // ['Branch', ['rdmbranchid', 0]],
            // ['Branch Name', ['rdmbranchname', 0]],
      
            // ['Total Items', ['rdmtotalquantity', 0]],
            // ['Customer Code', ['accountholdercode', 0]], 
            // ['Customer Name', ['accountholdername', 0]],
            // Custom fields here 
           
          
            // End custom
           

           
           
          
         
            // ['Delivery Country', ['rdmdeliverycountry', 0]],
          
         
      
            
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
        
        // btn.up('grid').getColumns().map(column => {
        //     if (column.isVisible() && column.dataIndex !== null){
        //             _key = column.text
        //             _value = column.dataIndex
        //             columnlist = {
        //                 // [_key]: _value
        //                 text: _key,
        //                 index: _value
        //             }
        //             if (column.exportdecimal !== null){
        //                 _decimal = column.exportdecimal;
        //                 columnlist.decimal = _decimal;
        //             }
        //             if('createdon' == column.dataIndex){
        //                 // dont push header if its status
        //             }else {
        //                 header.push(columnlist);
        //             }
                  
        //         }
        //     });
        // Fixed printing model
        

        // Add a transaction header 
      
        startDate = this.getView().getReferences().startDate.getValue();
        endDate = this.getView().getReferences().endDate.getValue();

        // Do a daterange checker
        // If date exceeds 2 months, reject
        // Init date values
        var msecPerMinute = 1000 * 60;
        var msecPerHour = msecPerMinute * 60;
        var msecPerDay = msecPerHour * 24;

        // Calculate date interval 
        var interval = endDate - startDate;

        var intervalDays = Math.floor(interval / msecPerDay );

        if(!startDate || !endDate){
            Ext.MessageBox.show({
                title: 'Filter Date',
                msg: 'Start date and End date required.',
                buttons: Ext.MessageBox.OK,
                icon: Ext.MessageBox.ERROR
            });
        }
        
        // Get days of months
        // Startdate
        startMonth = new Date(startDate.getYear(), startDate.getMonth(), 0).getDate();

        endMonth = new Date(endDate.getYear(), endDate.getMonth(), 0).getDate();

        // Get 2 months range limit for filter
        rangeLimit = startMonth + endMonth;

        if (startDate && endDate){
           // Check if day exceeds 63 days 
            // if (rangeLimit >= intervalDays){
            //     // Check if day exceeds 63 days 
                
            //     startDate = Ext.Date.format(startDate,'Y-m-d 00:00:00');
            //     endDate = Ext.Date.format(endDate,'Y-m-d 23:59:59');
            //     daterange = {
            //         startDate: startDate,
            //         endDate: endDate,
            //     }
            // }else{
            //     Ext.MessageBox.show({
            //         title: 'Filter Date',
            //         msg: 'Please select date range within 2 months',
            //         buttons: Ext.MessageBox.OK,
            //         icon: Ext.MessageBox.ERROR
            //     });
            //     return
            // }
             // Check if day exceeds 63 days 
                
             startDate = Ext.Date.format(startDate,'Y-m-d 00:00:00');
             endDate = Ext.Date.format(endDate,'Y-m-d 23:59:59');
             daterange = {
                 startDate: startDate,
                 endDate: endDate,
             }
            // End check
            
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

        
        url = '?hdl=myconversion&action=exportExcel&header='+header+'&daterange='+daterange+'&partnercode='+partnerCode;
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

    canScheduleDate: function(records) {
        var status = records[0].get('status');
        var rdmstatus = records[0].get('rdmstatus');

        return records.length == 1 && status == 1 && rdmstatus == 1;
    },

    getConversionReportKtp: function(btn){
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
                hdl: 'myconversion', action: 'getMerchantList', partnercode: myView.partnercode,
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
                            items: [
                                {
                                    html:'<p>Select Merchant:</p>',margin: '10 50 50 20',xtype: 'form',reference: 'merchant-form',
                                    items: [
                                        {
                                            layout: 'column',
                                            margin: '28 8 8 18',
                                            width: '100%',
                                            height: '100%',
                                            reference: 'merchant-column-1',
                                            items: []
                                        },
                                    ]
                                },
                            ],
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
                                        var status = _this.GenerateReport(selected);

                                        if (!status){
                                            Ext.MessageBox.show({
                                                title: 'Generate File',
                                                msg: 'Generating file failed',
                                                buttons: Ext.MessageBox.OK,
                                                icon: Ext.MessageBox.ERROR
                                            });
                                        }
                                    }
                                    return false;
                                    //console.log("selected data: "+selected);
                                }
                            },],
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
                                },
                                ]
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
                }
            );
        }
    },

    getConversionReportPkbAffi: function(btn){
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
                hdl: 'myconversion', action: 'getMerchantList', partnercode: myView.partnercode,
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
                            items: [
                                {
                                    html:'<p>Select Merchant:</p>',margin: '10 50 50 20',xtype: 'form',reference: 'merchant-form',
                                    items: [
                                        {
                                            layout: 'column',
                                            margin: '28 8 8 18',
                                            width: '100%',
                                            height: '100%',
                                            reference: 'merchant-column-1',
                                            items: []
                                        },
                                    ]
                                },
                            ],
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
                                        var status = _this.GenerateReport(selected);

                                        if (!status){
                                            Ext.MessageBox.show({
                                                title: 'Generate File',
                                                msg: 'Generating file failed',
                                                buttons: Ext.MessageBox.OK,
                                                icon: Ext.MessageBox.ERROR
                                            });
                                        }
                                    }
                                    return false;
                                    //console.log("selected data: "+selected);
                                }
                            },],
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
                                },
                                ]
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
                }
            );
        }
    },

    GenerateReport: function(selectedID){
        //var myView = this.getView(),
        // grid header data
        header = [];
        partnerCode = myView.partnercode;
        //debugger;
        // Check if buy or sell based on button reference
        /*
        if('dailytransactionsell' == btn.reference){
            // filter by companysell
            
        }else if('dailytransactionbuy' == btn.reference){
            // filter by companybuy
        }
        */
       
        /*
        columnleft = {
            // [_key]: _value
                text: 'Date',
                index: 'createdon'
        }
        header.push(columnleft);
            
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
                if('rdmitems' == column.dataIndex){
                    // dont push header if its status
                }else {
                    header.push(columnlist);
                }
              
            }
        });
        */
        
        // Initialize Mapping for Excel Headers
        // change the value based on conversion db
        // [Key, [val1, val2]]
        // [Name, [dataIndex, decimal point]]
        
        // Check if BMMB, contain comissionm fee
        // const reportingFields = [
        //     ['Date', ['createdon', 0]],
        //     ['Customer Code', ['accountholdercode', 0]], 
        //     ['Customer Name', ['accountholdername', 0]],
        //     ['Conversion Type', ['rdmtype', 0]],
        //     ['Total Weight', ['rdmtotalweight', 3]],
        //     ['Total Quantity', ['rdmtotalquantity', 3]],
        //     ['Conversion Fee', ['rdmredemptionfee', 3]],
        //     [partnerCode +' Commission Fee', ['commissionfee', 3]],
        //     ['Insurance Fee', ['rdminsurancefee', 3]],
        //     ['Handling Fee', ['rdmhandlingfee', 3]],
        //     ['Delivery Fee', ['rdmspecialdeliveryfee', 3]],
        //     ['Total Amount', ['rdmspecialdeliveryfee', 3]],
        //     ['Delivery Address', ['rdmdeliveryaddress', 0]],
        //     ['Delivery Postcode', ['rdmdeliverypostcode', 0]],
        //     ['Delivery State', ['rdmdeliverystate', 0]],
        //     ['Delivery Country', ['rdmdeliverycountry', 0]],

        //     ['Delivery Contact Name', ['rdmdeliverycontactname1', 0]],
        //     ['Delivery Contact No', ['rdmdeliverycontactno1', 0]],
        //     ['Delivery Country', ['rdmdeliverycountry', 0]],
        //     ['Delivery Country', ['rdmdeliverycountry', 0]],
        //     ['Payment Status', ['status', 0]],
        //     ['Conversion Status', ['rdmstatus', 0]],
            
        // ];
        const reportingFields = [
            ['Readable Status', ['rdmstatus', 0]],
            ['Type', ['rdmtype', 0]],
            ['Partner Ref No', ['rdmpartnerrefno', 0]], 
            ['Redemption No', ['rdmredemptionno', 0]],
            ['Branch', ['rdmbranchid', 0]],
            ['Branch Name', ['rdmbranchname', 0]],
            ['Total Weight', ['rdmtotalweight', 3]],
            ['Total Items', ['rdmtotalquantity', 0]],
            // ['Customer Code', ['accountholdercode', 0]], 
            // ['Customer Name', ['accountholdername', 0]],
            // Custom fields here 
            ['Item Code', ['code', 0]],
            ['Serial Number', ['serialnumber', 0]],
            // End custom
            ['Delivery Address', ['rdmdeliveryaddress', 0]],
            ['Delivery Postcode', ['rdmdeliverypostcode', 0]],
            ['Delivery State', ['rdmdeliverystate', 0]],
            // ['Delivery Country', ['rdmdeliverycountry', 0]],
            ['Delivery Contact Name 1', ['rdmdeliverycontactname1', 0]],
            ['Delivery Contact No 1', ['rdmdeliverycontactno1', 0]],
            ['Delivery Contact Name 2', ['rdmdeliverycontactname2', 0]],
            ['Delivery Contact No 2', ['rdmdeliverycontactno2', 0]],

            // end new format
            ['Conversion Fee', ['rdmredemptionfee', 3]],
            [partnerCode +' Commission Fee', ['commissionfee', 3]],
            ['Insurance Fee', ['rdminsurancefee', 3]],
            ['Handling Fee', ['rdmhandlingfee', 3]],
            ['Delivery Fee', ['rdmspecialdeliveryfee', 3]],
            ['Total Amount', ['rdmspecialdeliveryfee', 3]],
          
         
            // ['Delivery Country', ['rdmdeliverycountry', 0]],
            ['Payment Status', ['status', 0]],
         
            ['Created On', ['createdon', 0]],
            
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
        // end fixed model
        
        // Fixed printing model
        // btn.up('grid').getColumns().map(column => {
        //     if (column.isVisible() && column.dataIndex !== null){
        //         _key = column.text
        //         _value = column.dataIndex
        //         columnlist = {
        //             // [_key]: _value
        //             text: _key,
        //             index: _value
        //         }
        //         if (column.exportdecimal !== null){
        //             _decimal = column.exportdecimal;
        //             columnlist.decimal = _decimal;
        //         }
        //         if('createdon' == column.dataIndex){
        //             // dont push header if its status
        //         }else {
        //             header.push(columnlist);
        //         }
        //     }
        // });
        // Add a transaction header 
      
        // startDate = this.getView().getReferences().startDate.getValue();
        // endDate = this.getView().getReferences().endDate.getValue();
        startDate = myView.getReferences().startDate.getValue();
        endDate = myView.getReferences().endDate.getValue();

        // Do a daterange checker
        // If date exceeds 2 months, reject
        // Init date values
        var msecPerMinute = 1000 * 60;
        var msecPerHour = msecPerMinute * 60;
        var msecPerDay = msecPerHour * 24;

        // Calculate date interval 
        var interval = endDate - startDate;

        var intervalDays = Math.floor(interval / msecPerDay );

        if(!startDate || !endDate){
            Ext.MessageBox.show({
                title: 'Filter Date',
                msg: 'Start date and End date required',
                buttons: Ext.MessageBox.OK,
                icon: Ext.MessageBox.ERROR
            });
        }
        
        // Get days of months
        // Startdate
        startMonth = new Date(startDate.getYear(), startDate.getMonth(), 0).getDate();

        endMonth = new Date(endDate.getYear(), endDate.getMonth(), 0).getDate();

        // Get 2 months range limit for filter
        rangeLimit = startMonth + endMonth;

        if (startDate && endDate){
           // Check if day exceeds 63 days 
            // if (rangeLimit >= intervalDays){
            //     // Check if day exceeds 63 days 
                
            //     startDate = Ext.Date.format(startDate,'Y-m-d 00:00:00');
            //     endDate = Ext.Date.format(endDate,'Y-m-d 23:59:59');
            //     daterange = {
            //         startDate: startDate,
            //         endDate: endDate,
            //     }
            // }else{
            //     Ext.MessageBox.show({
            //         title: 'Filter Date',
            //         msg: 'Please select date range within 2 months',
            //         buttons: Ext.MessageBox.OK,
            //         icon: Ext.MessageBox.ERROR
            //     });
            //     return
            // }
            // End check
             // Check if day exceeds 63 days 
                
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
        // remove 2 month limit

        header = encodeURI(JSON.stringify(header));
        
        daterange = encodeURI(JSON.stringify(daterange));

        
        url = '?hdl=myconversion&action=exportExcel&header='+header+'&daterange='+daterange+'&partnercode='+partnerCode+'&selected='+selectedID;
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

        return true;
    },

    /*
    *
    * Add custom functions for no courier partners
    * 
    */
    updateStatus : function(selectedID){
        var myView = this.getView(),
        // grid header data
        header = [];
        selected_ids = [];
        string_template = [];
        serialNumberLine = "";
        selectedSerialNumbers = "";
        length = 0;
        count = 0;
        // me = this, record;
        var sm = myView.getSelectionModel();        
        var selectedRecords = sm.getSelection(); 
      
        partnerCode = myView.partnercode;


        // check if confirmed status, if not end
        if(selectedRecords[0].data.rdmstatus == 1){
            var transferpanel = new Ext.form.Panel({			
                frame: true,
                layout: 'vbox',
                defaults: {
                    columnWidth: .5,                
                },         
                border: 0,
                bodyBorder: false,
                bodyPadding: 10,
                width: 580,
                items: [
                    // {
                    //     layout: 'column',
                    //     items:[
                    //         {
                    //             items: [ 
                    //                 //{ xtype: 'hiddenfield', inputType: 'hidden', hidden: true, name: 'serialno' , value:selectedRecords[0].data.serialno,allowBlank: false},	                      
                    //                 /*{ 
                    //                     xtype: 'combobox',
                    //                     fieldLabel: 'From',
                    //                     name:'vaultfrom',
                    //                     typeAhead: true,
                    //                     triggerAction: 'all',
                    //                     selectOnTab: true,
                    //                     store: option1data,             
                    //                     lazyRender: true,
                    //                     displayField: 'name',
                    //                     valueField: 'id',
                    //                     queryMode: 'remote',
                    //                     remoteFilter: false,
                    //                     listClass: 'x-combo-list-small',
                    //                     forceSelection: true,
                    //                     allowBlank: false
                    //                 },*/ 
                    //                 { 
                    //                     xtype: 'combobox',
                    //                     fieldLabel: 'From',
                    //                     name:'vaultfrom',
                    //                     typeAhead: true,
                    //                     triggerAction: 'all',
                    //                     selectOnTab: true,
                    //                     store: fromlocation,               
                    //                     lazyRender: true,
                    //                     displayField: 'name',
                    //                     valueField: 'id',
                    //                     queryMode: 'remote',
                    //                     remoteFilter: false,
                    //                     valueField: 'value',
                    //                     value: defaultvalue,
                    //                     listClass: 'x-combo-list-small',
                    //                     forceSelection: true,
                    //                     allowBlank: false,
                    //                     /*listeners:{
                    //                         load:function (store, recs) {
                    //                             store.add({id:'1', name:'paprika'});  //adding empty record to enable deselection of assignment
                    //                         }
                    //                     }*/
                    //                 },   
                    //             ]
                    //         },
                    //         {
                    //             items:[
                    //                 { 
                    //                     xtype: 'combobox',
                    //                     fieldLabel: 'To',
                    //                     name:'vaultto',
                    //                     typeAhead: true,
                    //                     triggerAction: 'all',
                    //                     selectOnTab: true,
                    //                     store: tolocation,               
                    //                     lazyRender: true,
                    //                     displayField: 'name',
                    //                     valueField: 'id',
                    //                     queryMode: 'remote',
                    //                     remoteFilter: false,
                    //                     listClass: 'x-combo-list-small',
                    //                     forceSelection: true,
                    //                     allowBlank: false,
                    //                 },   
                                    
                    //             ]
                    //         },	
                    //     ]
                    // },
                    // {
                    //     // xtype: 'datefield',
                    //     flex: 1,
                    //     // align: 'center',
                    //     xtype: 'datefield', 
                    //     format: 'Y-m-d H:i:s', 
                    //     fieldLabel: 'Document Date On', 
                    //     name: 'documentdateon',  
                    //     reference: 'documentdateon',
                    // },
                    {
                        xtype:'panel',
                        flex: 10,
                        width: 580,
                        height: 230,
                        layout: {
                            type: 'hbox',
                            align: 'center',
                            pack: 'center'
                        }, 
                        items: [
                            {
                                xtype: "fieldset",
                                title: "Selected Record",
                                collapsible: false,
                                default: {
                                    labelWidth: 30,
                                },
                                items: [
                                    {
                                        xtype: "container",
                                        height: 150,
                                        width: 300,
                                        scrollable: true,
                                        id: 'confirmconversionrecords',
                                        reference:
                                            "deliverystatusdisplayfield",
                                    },
                                ],
                            },
                        ],
                    },
                ],						
            });

            // Add to transferpanel
            var panel = Ext.getCmp('confirmconversionrecords');
            panel.removeAll();
            for(i = 0; i < selectedRecords.length; i++){
    
                // get serial numbers and code
                if (selectedRecords[0].data.rdmstatus == 1){
                    items = Ext.JSON.decode(selectedRecords[0].data.rdmitems);
                    xreturn = '';
                    items.map((item) => {
                        xreturn += '<span><b>Serial:</b> '+item.serialnumber+', <b>Deno:</b> '+item.weight+', <br> <b>Gold:</b> '+item.code+'</span><br>';
                    })
                    // return xreturn;
                }
        
                selected_ids[i] = selectedRecords[i].data.id;
                string_template[i] = selectedRecords[i].data.rdmredemptionno + '<br>' + xreturn;
                //records[i] = selectedRecords[i].data.movetovaultlocationid;

                // Set a length limit counter to populate for horizontal display
                // Add length counter 
                length++;
                count++;

                

                //date = data.createdon.date;
                //filtereddate = Ext.Date.format(new Date(date), "Y-m-d");

                // Color based on status
                // If confirmed = orange
                // If completed = green
                // The rest = purple
                if(selectedRecords[i].data.rdmstatus == 0){
                    color = "color: blue ";
                    rdmstatus = "Pending";
                } else if(selectedRecords[i].data.rdmstatus == 1){
                    color = "color: orange ";
                    rdmstatus = "Confirmed";
                } else if(selectedRecords[i].data.rdmstatus == 2){
                    color = "color: green ";
                    rdmstatus = "Completed";
                } else if(selectedRecords[i].data.rdmstatus == 3){
                    color = "color: red ";
                    rdmstatus = "Failed";
                }else{
                    color = "color: orange ";
                    rdmstatus = "Process Delivery";
    
                }
                label = count.toString();
                value = '<span data-qtitle="'+string_template[i]+'" data-qwidth="200" '+
                'data-qtip="Status: '+ rdmstatus +' ">'+
                string_template[i] +'</span>';

                panel.add({
                    xtype: 'container',
                    height: 30,
                    //fieldStyle: 'background-color: #000000; background-image: none;',
                    //scrollable: true,
                    items: [{
                        xtype: 'displayfield', name:'serialnumber', value: value, reference: 'serialnumbers', fieldLabel: label, flex: 1, style:'padding-left: 20px;padding-right: 20px;', fieldStyle: color,
                    },
                    ]
                },);
            
                // If length is 5 or less, populate horizontally
                if(length <= 1){
                    serialNumberLine += "&nbsp;" + count + ".&nbsp;" + selectedRecords[i].data.rdmredemptionno + "&nbsp;".repeat(25);

                }else {
                    // Reset length
                    length = 0;

                    // Add last entry in line
                    serialNumberLine += "&nbsp;" + count + ".&nbsp;" + selectedRecords[i].data.rdmredemptionno + "&nbsp;".repeat(25);
                    // Add length to a newline
                    selectedSerialNumbers += '<p>' + serialNumberLine+ '</p>';
                    // Reset Serial Number Line
                    serialNumberLine = "";
                }
                
                // Marks end of loop
                if(selectedRecords.length - count == 0){
                    selectedSerialNumbers += '<p>' + serialNumberLine+ '</p>';
                }
                
            
                //count++;
            }
            
            layout = "<div style='height:100px;width:350px;border:1px solid #4e4e4e;overflow:auto;'>"+ selectedSerialNumbers  +"</div>";

            var transferwindow = new Ext.Window({
                title: 'Update Conversion Status',
                layout: 'fit',
                width: 600,
                maxHeight: 700,
                modal: true,
                plain: true,
                buttonAlign: 'center',
                buttons: [{
                    text: 'Update Status',
                    handler: function(btn) {
                        if (transferpanel.getForm().isValid()) {
                            btn.disable();
            
    
                            transferpanel.getForm().submit({
                                submitEmptyText: false,
                                url: 'index.php',
                                method: 'POST',
                                dataType: "json",
                                params: { hdl: 'myconversion', 'action': 'updateStatus', 
                                'id[]': selected_ids,

                                },
                                waitMsg: 'Processing',
                                success: function(frm, action) { //success                                   
                                    Ext.MessageBox.show({
                                        title: 'Update Success',
                                        msg: 'Conversion Status Updated Successfully',
                                        buttons: Ext.MessageBox.OK,
                                        icon: Ext.MessageBox.INFO
                                    });
                                    //debugger;
                                    owningWindow = btn.up('window');
                                    owningWindow.close();
                                    myView.getSelectionModel().deselectAll();  
                                    myView.getStore().reload();

                                    myView.lookupReferenceHolder().lookupReference('summarycontainer').doFireEvent('reloadsummary');

                                    snap.getApplication().getStore('snap.store.VaultItemTrans').reload()
                                },
                                failure: function(frm,action) {
                                    btn.enable();                                    
                                    var errmsg = action.result.errmsg;
                                    if (action.failureType) {
                                        switch (action.failureType) {
                                            case Ext.form.action.Action.CLIENT_INVALID:
                                                console.log('client invalid');
                                                break;
                                            case Ext.form.action.Action.CONNECT_FAILURE:
                                                console.log('connect failure');
                                                break;
                                            case Ext.form.action.Action.SERVER_INVALID:
                                                console.log('server invalid');
                                                break;
                                        }
                                    }
                                    if (!action.result.errmsg || errmsg.length == 0) {
                                        errmsg = 'Error in form: ' + action.result.errorMessage;
                                    }                                   
                                    Ext.MessageBox.show({
                                        title: 'Message',
                                        msg: errmsg,
                                        buttons: Ext.MessageBox.OK,
                                        icon: Ext.MessageBox.ERROR
                                    });                            
                                }
                            });
                            }else{
                                Ext.MessageBox.show({
                                    title: 'Error Message',
                                    msg: 'All fields are required',
                                    buttons: Ext.MessageBox.OK,
                                    icon: Ext.MessageBox.ERROR
                                });
                            }
                    } 
                },{
                    text: 'Close',
                    handler: function(btn) {
                        owningWindow = btn.up('window');
                        owningWindow.close();
                    }
                },
            ],
                closeAction: 'destroy',
                items: transferpanel
            });
            transferwindow.show();
        
           
        }else {
            Ext.MessageBox.show({
                title: 'Conversion Error',
                msg: 'Please select record in Confirmed Status',
                buttons: Ext.MessageBox.OK,
                icon: Ext.MessageBox.ERROR
            });
        }
        
           
        // snap.getApplication().sendRequest({ 
        //     hdl: 'myconversion', action: 'detailview', id: record.data.id})
        // .then(function(data){
        //     if(data.success) {
        //         displayCallback(data.record);
        //     }
        // })
        // return false;
        // 
    },

    sendEmail : function(selectedID){
        var myView = this.getView(),
        // grid header data
        header = [];
        selected_ids = [];
        string_template = [];
        serialNumberLine = "";
        selectedSerialNumbers = "";
        length = 0;
        count = 0;
        // me = this, record;
        var sm = myView.getSelectionModel();        
        var selectedRecords = sm.getSelection(); 
      
        partnerCode = myView.partnercode;
        partnerEmail = myView.partneremail;

        // check if confirmed status, if not end
   
        // if(selectedRecords[0].data.rdmstatus != 1){
        //     Ext.MessageBox.show({
        //         title: 'Conversion Error',
        //         msg: 'Please select record in Confirmed Status',
        //         buttons: Ext.MessageBox.OK,
        //         icon: Ext.MessageBox.ERROR
        //     });
            
        // }
        
        // If have record, allow process
        if(selectedRecords[0].data.rdmstatus >= 2){
            var transferpanel = new Ext.form.Panel({			
                frame: true,
                layout: 'vbox',
                defaults: {
                    columnWidth: .5,                
                },         
                border: 0,
                bodyBorder: false,
                bodyPadding: 10,
                width: 580,
                items: [
                    {
                        layout: 'column',
                        items:[
                            {
                                items: [ 
                                    //{ xtype: 'hiddenfield', inputType: 'hidden', hidden: true, name: 'serialno' , value:selectedRecords[0].data.serialno,allowBlank: false},	                      
                                    /*{ 
                                        xtype: 'combobox',
                                        fieldLabel: 'From',
                                        name:'vaultfrom',
                                        typeAhead: true,
                                        triggerAction: 'all',
                                        selectOnTab: true,
                                        store: option1data,             
                                        lazyRender: true,
                                        displayField: 'name',
                                        valueField: 'id',
                                        queryMode: 'remote',
                                        remoteFilter: false,
                                        listClass: 'x-combo-list-small',
                                        forceSelection: true,
                                        allowBlank: false
                                    },*/ 
                                    {
                                        xtype : 'displayfield',
                                        width : '99%',
                                        padding: '0 1 0 1',
                                        value: "<h5 style=' width:100%;line-height: normal;overflow: inherit; margin:0px 0 30px; font-size: 15px;color:#757575;'><span style='background:#fff;position: relative;'>An email notification on the following record will be sent to <span style='color:#204a6d;'>"+partnerEmail+"</span></span></h5>",
                                        //style: "content: 'OR';display: inline-block;padding: 3px 5px 3px 0;padding-top: 3px;padding-right: 5px;padding-bottom: 3px;padding-left: 0px;background-color: #ffffff;font-size: 12px;font-weight: bold;text-transform: uppercase;color: #BBC3CE;letter-spacing: 1px; position: absolute;top: -0.6em;left: 0;",
                                        
                                    },
                                ]
                            },
                            // {
                            //     items:[
                            //         { 
                            //             xtype: 'combobox',
                            //             fieldLabel: 'To',
                            //             name:'vaultto',
                            //             typeAhead: true,
                            //             triggerAction: 'all',
                            //             selectOnTab: true,
                            //             store: tolocation,               
                            //             lazyRender: true,
                            //             displayField: 'name',
                            //             valueField: 'id',
                            //             queryMode: 'remote',
                            //             remoteFilter: false,
                            //             listClass: 'x-combo-list-small',
                            //             forceSelection: true,
                            //             allowBlank: false,
                            //         },   
                                    
                            //     ]
                            // },	
                        ]
                    },
                    // {
                    //     // xtype: 'datefield',
                    //     flex: 1,
                    //     // align: 'center',
                    //     xtype: 'datefield', 
                    //     format: 'Y-m-d H:i:s', 
                    //     fieldLabel: 'Document Date On', 
                    //     name: 'documentdateon',  
                    //     reference: 'documentdateon',
                    // },
                    {
                        xtype:'panel',
                        flex: 10,
                        width: 580,
                        height: 230,
                        layout: {
                            type: 'hbox',
                            align: 'center',
                            pack: 'center'
                        }, 
                        items: [
                            {
                                xtype: "fieldset",
                                title: "Selected Record",
                                collapsible: false,
                                default: {
                                    labelWidth: 30,
                                },
                                items: [
                                    {
                                        xtype: "container",
                                        height: 150,
                                        width: 300,
                                        scrollable: true,
                                        id: 'emailconversionrecords',
                                        reference:
                                            "deliverystatusdisplayfield",
                                    },
                                ],
                            },
                        ],
                    },
                ],						
            });

            // Add to transferpanel
            var panel = Ext.getCmp('emailconversionrecords');
            panel.removeAll();
            for(i = 0; i < selectedRecords.length; i++){
    
                // get serial numbers and code
                if (selectedRecords[0].data.rdmstatus >= 2){
                    items = Ext.JSON.decode(selectedRecords[0].data.rdmitems);
                    xreturn = '';
                    items.map((item) => {
                        xreturn += '<span><b>Serial:</b> '+item.serialnumber+', <b>Deno:</b> '+item.weight+', <br> <b>Gold:</b> '+item.code+'</span><br>';
                    })
                    // return xreturn;
                }
        
                selected_ids[i] = selectedRecords[i].data.id;
                string_template[i] = selectedRecords[i].data.rdmredemptionno + '<br>' + xreturn;
                //records[i] = selectedRecords[i].data.movetovaultlocationid;

                // Set a length limit counter to populate for horizontal display
                // Add length counter 
                length++;
                count++;

                

                //date = data.createdon.date;
                //filtereddate = Ext.Date.format(new Date(date), "Y-m-d");

                // Color based on status
                // If confirmed = orange
                // If completed = green
                // The rest = purple
                if(selectedRecords[i].data.rdmstatus == 0){
                    color = "color: blue ";
                    rdmstatus = "Pending";
                } else if(selectedRecords[i].data.rdmstatus == 1){
                    color = "color: orange ";
                    rdmstatus = "Confirmed";
                } else if(selectedRecords[i].data.rdmstatus == 2){
                    color = "color: green ";
                    rdmstatus = "Completed";
                } else if(selectedRecords[i].data.rdmstatus == 3){
                    color = "color: red ";
                    rdmstatus = "Failed";
                }else{
                    color = "color: orange ";
                    rdmstatus = "Process Delivery";
    
                }
                label = count.toString();
                value = '<span data-qtitle="'+string_template[i]+'" data-qwidth="200" '+
                'data-qtip="Status: '+ rdmstatus +' ">'+
                string_template[i] +'</span>';

                panel.add({
                    xtype: 'container',
                    height: 30,
                    //fieldStyle: 'background-color: #000000; background-image: none;',
                    //scrollable: true,
                    items: [{
                        xtype: 'displayfield', name:'serialnumber', value: value, reference: 'serialnumbers', fieldLabel: label, flex: 1, style:'padding-left: 20px;padding-right: 20px;', fieldStyle: color,
                    },
                    ]
                },);
            
                // If length is 5 or less, populate horizontally
                if(length <= 1){
                    serialNumberLine += "&nbsp;" + count + ".&nbsp;" + selectedRecords[i].data.rdmredemptionno + "&nbsp;".repeat(25);

                }else {
                    // Reset length
                    length = 0;

                    // Add last entry in line
                    serialNumberLine += "&nbsp;" + count + ".&nbsp;" + selectedRecords[i].data.rdmredemptionno + "&nbsp;".repeat(25);
                    // Add length to a newline
                    selectedSerialNumbers += '<p>' + serialNumberLine+ '</p>';
                    // Reset Serial Number Line
                    serialNumberLine = "";
                }
                
                // Marks end of loop
                if(selectedRecords.length - count == 0){
                    selectedSerialNumbers += '<p>' + serialNumberLine+ '</p>';
                }
                
            
                //count++;
            }
            
            layout = "<div style='height:100px;width:350px;border:1px solid #4e4e4e;overflow:auto;'>"+ selectedSerialNumbers  +"</div>";

            var transferwindow = new Ext.Window({
                title: 'Send Email To ' +partnerCode,
                layout: 'fit',
                width: 600,
                maxHeight: 700,
                modal: true,
                plain: true,
                buttonAlign: 'center',
                buttons: [{
                    text: 'Send Email to ' + partnerCode,
                    handler: function(btn) {
                        if (transferpanel.getForm().isValid()) {
                            btn.disable();
            
    
                            transferpanel.getForm().submit({
                                submitEmptyText: false,
                                url: 'index.php',
                                method: 'POST',
                                dataType: "json",
                                params: { hdl: 'myconversion', 'action': 'sendEmail', 
                                'id[]': selected_ids,
                                'partnercode': partnerCode,
                                },
                                waitMsg: 'Processing',
                                success: function(frm, action) { //success                                   
                                    Ext.MessageBox.show({
                                        title: 'Email Successfully Sent',
                                        msg: 'Notified relevant parties on successful conversion',
                                        buttons: Ext.MessageBox.OK,
                                        icon: Ext.MessageBox.INFO
                                    });
                                    //debugger;
                                    owningWindow = btn.up('window');
                                    owningWindow.close();
                                    myView.getSelectionModel().deselectAll();  
                                    myView.getStore().reload();

                                    myView.lookupReferenceHolder().lookupReference('summarycontainer').doFireEvent('reloadsummary');

                                    snap.getApplication().getStore('snap.store.VaultItemTrans').reload()
                                },
                                failure: function(frm,action) {
                                    btn.enable();                                    
                                    var errmsg = action.result.errmsg;
                                    if (action.failureType) {
                                        switch (action.failureType) {
                                            case Ext.form.action.Action.CLIENT_INVALID:
                                                console.log('client invalid');
                                                break;
                                            case Ext.form.action.Action.CONNECT_FAILURE:
                                                console.log('connect failure');
                                                break;
                                            case Ext.form.action.Action.SERVER_INVALID:
                                                console.log('server invalid');
                                                break;
                                        }
                                    }
                                    if (!action.result.errmsg || errmsg.length == 0) {
                                        errmsg = 'Error in form: ' + action.result.errorMessage;
                                    }                                   
                                    Ext.MessageBox.show({
                                        title: 'Message',
                                        msg: errmsg,
                                        buttons: Ext.MessageBox.OK,
                                        icon: Ext.MessageBox.ERROR
                                    });                            
                                }
                            });
                            }else{
                                Ext.MessageBox.show({
                                    title: 'Error Message',
                                    msg: 'All fields are required',
                                    buttons: Ext.MessageBox.OK,
                                    icon: Ext.MessageBox.ERROR
                                });
                            }
                    } 
                },{
                    text: 'Close',
                    handler: function(btn) {
                        owningWindow = btn.up('window');
                        owningWindow.close();
                    }
                },
            ],
                closeAction: 'destroy',
                items: transferpanel
            });
            transferwindow.show();
        
           
        }else{
            Ext.MessageBox.show({
                title: 'Not ready to notify ' +partnerCode,
                msg: 'The following record is still not completed',
                buttons: Ext.MessageBox.OK,
                icon: Ext.MessageBox.ERROR
            });
        }
        
           
        // snap.getApplication().sendRequest({ 
        //     hdl: 'myconversion', action: 'detailview', id: record.data.id})
        // .then(function(data){
        //     if(data.success) {
        //         displayCallback(data.record);
        //     }
        // })
        // return false;
        // 
    },
});