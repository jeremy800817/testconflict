Ext.define('snap.view.order.OrderController', {
    extend: 'snap.view.gridpanel.BaseController',
    alias: 'controller.order-order',


    onPreLoadViewDetail: function(record, displayCallback) {
        snap.getApplication().sendRequest({ hdl: 'order', action: 'detailview', id: record.data.id})
        .then(function(data){
            if(data.success) {
                displayCallback(data.record);
            }
        })
        return false;
    },

    /*
    sendToSAP: function(formView, form, record, asyncLoadCallback){
       
        var me = this, selectedRecord,
            myView = this.getView();
        var sm = myView.getSelectionModel();
        var selectedRecords = sm.getSelection();
        if (selectedRecords.length == 1) {
            for(var i = 0; i < selectedRecords.length; i++) {
                selectedID = selectedRecords[i].get('id');
                selectedRecord = selectedRecords[i];
                break;
            }
        } else if('add' != formAction) {
            Ext.MessageBox.show({
                title: 'Warning', buttons: Ext.MessageBox.OK, icon: Ext.MessageBox.WARNING,
                msg: 'Select a record first'});
            return;
        }
      // alert(record.data.id);

      var gridFormView = Ext.create(myView.formClass, Ext.apply(myView.formSap ? myView.formSap : {}, {
            formDialogButtons: [{
                text: 'Submit',
                handler: function(btn) {
                    me._sendToSap(btn);
                }
            },{
                text: 'Close',
                handler: function(btn) {
                    owningWindow = btn.up('window');
                    owningWindow.close();
                    me.gridFormView = null;
                }
            }]
        }));

        this.gridFormView = gridFormView;
       

        // <---------------> Grid
        //gridFormView.title = 'View all notes ...';
       
        snap.getApplication().sendRequest({
            hdl: 'order', 'action': 'fillSapForm', id: selectedRecord.data.id
        }, 'Fetching data from server....').then(
            //Received data from server already
            function (data) {
                if (data.success) {
                    //alert(data.apicodescustomer);
       
                        // Grab Name for partnerrefid
                    vendorcodes = data.apicodesvendor;
                    customercodes = data.apicodescustomer;
                    
                    /*
                    if (selectedRecord.data.type == 'CompanyBuy'){
                        buyorselltypelabel = 'Buy from Customer';
                        vendorcode = vendorcodes.find(x => x.id === selectedRecord.data.partnerrefid);
                        if (vendorcode != undefined) {
                            buyorsellvalue = vendorcode.name;
                            partnerreference = vendorcode;
                        } else {
                            Ext.MessageBox.show({
                                title: "ERROR-A1001",
                                msg: "Vendor Code is incorrect",
                                buttons: Ext.MessageBox.OK,
                                icon: Ext.MessageBox.WARNING
                            });
                            return;
                        }
                    }else if (selectedRecord.data.type == 'CompanySell'){
                        buyorselltypelabel = 'Sell to Customer';
                        customercode = customercodes.find(x => x.id === selectedRecord.data.partnerrefid);         
                        if (customercode != undefined) {
                            buyorsellvalue = customercode.name;
                            partnerreference = customercode;
                        } else {
                            Ext.MessageBox.show({
                                title: "ERROR-A1001",
                                msg: "Customer Code is incorrect",
                                buttons: Ext.MessageBox.OK,
                                icon: Ext.MessageBox.WARNING
                            });
                            return;
                        }
                    }else {
                        me.gridFormView.getController().lookupReference('buyorsell').setHidden(true);
                    }

                    
                    if (selectedRecord.data.type == 'CompanyBuy'){
                        buyorselltypelabel = 'Buy from Customer';
                        
                    }else if (selectedRecord.data.type == 'CompanySell'){
                        buyorselltypelabel = 'Sell to Customer';
                        
                    }else {
                        me.gridFormView.getController().lookupReference('buyorsell').setHidden(true);
                    }
                    partnerreference = 'a';
                    buyorsellvalue = "b";

                    // Filter Status 
                    if (selectedRecord.data.status == 0){
                        orderstatus = 'Pending';
                    }else if(selectedRecord.data.status == 1){
                        orderstatus = 'Confirmed';
                    }else if(selectedRecord.data.status == 2){
                        orderstatus = 'Pending Payment';
                    }else if(selectedRecord.data.status == 3){
                        orderstatus = 'Pending Cancel';
                    }else if(selectedRecord.data.status == 4){
                        orderstatus = 'Cancelled';
                    }else if(selectedRecord.data.status == 5){
                        orderstatus = 'Completed';
                    }else if(selectedRecord.data.status == 6){
                        orderstatus = 'Expired';
                    }else {
                        orderstatus = 'Unassigned';
                    }
                   
                    me.gridFormView.getController().lookupReference('gtpref').setValue(selectedRecord.data.id);
                    me.gridFormView.getController().lookupReference('bookingnumber').setValue(selectedRecord.data.orderno);
                    me.gridFormView.getController().lookupReference('price').setValue(selectedRecord.data.price);
                    me.gridFormView.getController().lookupReference('xauweight').setValue(selectedRecord.data.xau);
                    me.gridFormView.getController().lookupReference('buyorsell').setFieldLabel(buyorselltypelabel);
                    me.gridFormView.getController().lookupReference('buyorsell').setValue(buyorsellvalue);
                    me.gridFormView.getController().lookupReference('productname').setValue(selectedRecord.data.productname);
                    me.gridFormView.getController().lookupReference('status').setValue(orderstatus);
                    me.gridFormView.getController().lookupReference('grossvalue').setValue(selectedRecord.data.amount);

                    // Data transfer
                    me.gridFormView.getController().lookupReference('partnerreference').setValue(partnerreference);
                    me.gridFormView.getController().lookupReference('companybuyorsell').setValue(selectedRecord.data.type);
                    me.gridFormView.getController().lookupReference('product').setValue(selectedRecord.data.productid);
                    //form.down('radiogroup').setValue({ status: data.servicerecord.status });
                    //gridFormView.getController().lookupReference('sapcompanysellcode1').getStore().loadData(data.apicodescustomer);
                   // gridFormView.getController().lookupReference('sapcompanysellcode1').getStore().loadData(data.apicodescustomer);
                   // gridFormView.getController().lookupReference('sapcompanybuycode1').getStore().loadData(data.apicodesvendor);
                  
                    me.gridFormView.show();
                }
            });

        /*
        var windowfororderhandling = new Ext.Window({
            title: 'Sales Order Handling',
            layout: 'fit',
            width: 850,
            maxHeight: 700,
            modal: true,
            plain: true,
            buttonAlign: 'center',
            buttons: [{
                text: 'Submit',
                handler: function(btn) {
                    if (orderhandlingpanel.getForm().isValid()) {
                        btn.disable();
                        orderhandlingpanel.getForm().submit({
                            submitEmptyText: false,
                            url: 'gtp.php',
                            method: 'POST',
                            dataType: "json",
                            params: { hdl: 'order', action: 'sendToSap', 
                                        gtprefno: selectedRecord.data.id, 
                                        bookingno: selectedRecord.data.orderno, 
                                        xauweight:  selectedRecord.data.xau,
                                        price: selectedRecord.data.price,
                                        code: selectedRecord.data.partnerrefid,
                                        name: buyorsellvalue,
                                        buyorsell : selectedRecord.data.type,
                                        product: selectedRecord.data.productname,
                                        remarks: orderhandlingpanel.getForm().getFieldValues().remarks,
                                        grossvalue: selectedRecord.data.amount,
                                        productid :selectedRecord.data.productid,
                                    },
                            waitMsg: 'Processing',
                            success: function(frm, action) { //success                                   
                                windowforordercomplete.show();
                                owningWindow = btn.up('window');
                                //owningWindow.closeAction='destroy';
                                owningWindow.close();
                                myView.getStore().reload();
                            },
                            failure: function(frm,action) {
                                btn.enable();                                    
                                var errmsg = action.result.errorMessage;
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
                                    //windowforordercomplete.show();
                                    errmsg = action.result.errorMessage;
                                }                                   
                                Ext.MessageBox.show({
                                    title: 'Error Message',
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
                    //owningWindow.closeAction='destroy';
                    owningWindow.close();
                }
            }],
            listeners:{
                close:function(win) {
                    /*
                    // Reenable hidden components
                    Ext.getCmp('productfuturedisplay').setHidden(false);
                    Ext.getCmp('totalxauweightfuturedashboarddisplay').setHidden(false);
                    Ext.getCmp('acebuypricefuturedashboarddisplay').setHidden(false);
                    Ext.getCmp('acesellpricefuturedashboarddisplay').setHidden(false);
                    
                    // Clear cmp
                    Ext.getCmp('productfuturedisplay').destroy();
                    Ext.getCmp('totalxauweightfuturedashboarddisplay').destroy();
                    Ext.getCmp('acebuypricefuturedashboarddisplay').destroy();
                    Ext.getCmp('acesellpricefuturedashboarddisplay').destroy();
                    
                }
            },
            closeAction: 'destroy',
            items: orderhandlingpanel
        });*/

       // windowfororderhandling.show();
        /*snap.getApplication().sendRequest({ hdl: 'order', action: 'sendToSAP',})
        .then(function(data){
            if(data.success) {
                
            }
        })
    },*/

    sendToSAP: function(formView, form, record, asyncLoadCallback){
       
        var me = this, selectedRecord,
            myView = this.getView();
        var sm = myView.getSelectionModel();
        var selectedRecords = sm.getSelection();
            orderid = [];

      var gridFormView = Ext.create(myView.formClass, Ext.apply(myView.formSendToSap ? myView.formSendToSap : {}, {
            formDialogButtons: [{
                text: 'Submit',
                handler: function(btn) {
                    me._sendToSap(btn, orderid);
                }
            },{
                text: 'Close',
                handler: function(btn) {
                    owningWindow = btn.up('window');
                    owningWindow.close();
                    me.gridFormView = null;
                }
            }]
        }));

        this.gridFormView = gridFormView;
       
        if(1 <= selectedRecords.length) {
            //debugger;
            var panel = Ext.getCmp('sendtosapformdisplay');
    
            //date = data.createdon.date;
            //filtereddate = Ext.Date.format(new Date(date), "Y-m-d");
            panel.removeAll();
            for(var i = 0; i < selectedRecords.length; i++) {
                //selectedID = selectedRecords[i].get('id');
                //selectedRecord = selectedRecords[i];
                orderid[i] = selectedRecords[i].data.id;
                recordIndex = i;
                recordName = selectedRecords[i].data.id;

                if (selectedRecords[i].data.type == 'CompanyBuy'){
                    buyorselltypelabel = 'Buy from Customer';
                    
                }else if (selectedRecords[i].data.type == 'CompanySell'){
                    buyorselltypelabel = 'Sell to Customer';
                    
                }
                // Filter Status 
                if (selectedRecords[i].data.status == 0){
                orderstatus = 'Pending';
                }else if(selectedRecords[i].data.status == 1){
                    orderstatus = 'Confirmed';
                }else if(selectedRecords[i].data.status == 2){
                    orderstatus = 'Pending Payment';
                }else if(selectedRecords[i].data.status == 3){
                    orderstatus = 'Pending Cancel';
                }else if(selectedRecords[i].data.status == 4){
                    orderstatus = 'Cancelled';
                }else if(selectedRecords[i].data.status == 5){
                    orderstatus = 'Completed';
                }else if(selectedRecords[i].data.status == 6){
                    orderstatus = 'Expired';
                }else {
                    orderstatus = 'Unassigned';
                }
                panel.add(
                    {
                        xtype: 'fieldset',
                        title: "GTP Ref #" + recordName,
                        layout: 'hbox',
                        defaultType: 'textfield',
                        fieldDefaults: {
                            anchor: '100%',
                            msgTarget: 'side',
                            margin: '0 0 5 0',
                            width: '100%',
                        },
                        items: [
                                  {
                                    xtype: 'fieldcontainer',
                                    layout: 'vbox',
                                    flex: 2,
                                    items: [
                                        {
                                            xtype: 'displayfield', name:selectedRecords[i].data.orderno, value: selectedRecords[i].data.orderno, fieldLabel: 'Booking Number', flex: 1, style:'padding-left: 20px;padding-right: 20px;',
                                        },
                                        {
                                            xtype: 'displayfield', name:selectedRecords[i].xau, value: selectedRecords[i].data.xau, fieldLabel: "Xau Weight (g)", flex: 1, renderer: Ext.util.Format.numberRenderer('0,000.000'), style:'padding-left: 20px;padding-right: 20px;', 
                                        }
                                    ]
                                },
                                {
                                    xtype: 'fieldcontainer',
                                    layout: 'vbox',
                                    flex: 2,
                                    items: [
                                        {
                                            xtype: 'displayfield', name:selectedRecords[i].data.price, value: selectedRecords[i].data.price, fieldLabel: 'Price (RM/g)', flex: 1, renderer: Ext.util.Format.numberRenderer('0,000.000'), style:'padding-left: 20px;padding-right: 20px;', 
                                        },
                                        {
                                            xtype: 'displayfield', name:selectedRecords[i].type, value: selectedRecords[i].data.type, fieldLabel: buyorselltypelabel, flex: 1, style:'padding-left: 20px;padding-right: 20px;', 
                                        }
                                    ]
                                },
                                {
                                    xtype: 'fieldcontainer',
                                    layout: 'vbox',
                                    flex: 2,
                                    items: [
                                        {
                                            xtype: 'displayfield', name:selectedRecords[i].data.productname, value: selectedRecords[i].data.productname, fieldLabel: 'Product Type', flex: 1, style:'padding-left: 20px;padding-right: 20px;', 
                                        },
                                        {
                                            xtype: 'displayfield', name:selectedRecords[i].amount, value: selectedRecords[i].data.amount, fieldLabel: "Gross Value (RM)", flex: 1, renderer: Ext.util.Format.numberRenderer('0,000.000'), style:'padding-left: 20px;padding-right: 20px;', 
                                        }
                                    ]
                                },
                                {
                                    xtype: 'fieldcontainer',
                                    layout: 'vbox',
                                    flex: 2,
                                    items: [
                                        {
                                            xtype: 'displayfield', name:selectedRecords[i].data.status, value: orderstatus, fieldLabel: "Status", flex: 1, style:'padding-left: 20px;padding-right: 20px;',
                                        },
                                    ]
                                },
                              ]
                    })
            }
            
        }else {
            Ext.MessageBox.show({
                title: 'Warning', buttons: Ext.MessageBox.OK, icon: Ext.MessageBox.WARNING,
                msg: 'Select a record first'});
            return;
        }
      // alert(record.data.id);

        // <---------------> Grid
        //gridFormView.title = 'View all notes ...';
        me.gridFormView.show();

    },
    _sendToSap: function(btn, orderid) {
        // var owningWindow = btn.up('window');
        // var gridFormPanel = owningWindow.down('form');
        var me = this,
            myView = this.getView();
            snap.getApplication().sendRequest({
                hdl: 'order', action: 'sendToSap', 
                'id[]': orderid,
            }, 'Fetching data from server....').then(
            //Received data from server already
            function(data){
                if(data.success){
                    owningWindow = btn.up('window');
                
                    owningWindow.close(); 
                    Ext.MessageBox.show({
                        title: 'Success',
                        msg: 'Successfully sent to SAP',
                        buttons: Ext.MessageBox.OK,
                        icon: Ext.MessageBox.INFO
                    });

                }else {
                    owningWindow.close(); 
                    Ext.MessageBox.show({
                        title: 'Error Message',
                        msg: 'Failed to send to SAP',
                        buttons: Ext.MessageBox.OK,
                        icon: Ext.MessageBox.ERROR
                    });
                }
            });
    },

    /*
    cancelOrders: function(btn, formAction) {
        var me = this, selectedRecord,
            myView = this.getView();
        var sm = myView.getSelectionModel();
        var selectedRecords = sm.getSelection();
        if (selectedRecords.length == 1) {
            for(var i = 0; i < selectedRecords.length; i++) {
                selectedID = selectedRecords[i].get('id');
                selectedRecord = selectedRecords[i];
                break;
            }
        } else if('add' != formAction) {
            Ext.MessageBox.show({
                title: 'Warning', buttons: Ext.MessageBox.OK, icon: Ext.MessageBox.WARNING,
                msg: 'Select a record first'});
            return;
        }

        snap.getApplication().sendRequest({ 
            hdl: 'order', action: 'cancelOrder', 
                id: selectedRecord.data.id,
                partnerid: selectedRecord.data.partnerid,
                apiversion: selectedRecord.data.apiversion,
                refid: selectedRecord.data.partnerrefid,
                notifyurl: selectedRecord.data.notifyurl,
                reference: selectedRecord.data.remarks,
                timestamp: selectedRecord.data.createdon,
            },'Cancelling order....').then(
        function(data){
    		if(data.success) {
                Ext.MessageBox.show({
                    title: 'Notification', buttons: Ext.MessageBox.OK, icon: Ext.MessageBox.ALERT,
                    msg: 'Successfully cancelled order'});
            }
                
    	})
    },*/

    cancelOrders: function (record) {
        var myView = this.getView(),
            me = this, record;
        var sm = myView.getSelectionModel();
        var selectedRecords = sm.getSelection();  
        
        // Initiallze values
        orderid = [];
        orderNumberLine = "";
        selectedOrderNumbers = "";
        length = 0;
        count = 0;
        
        
        for(i = 0; i < selectedRecords.length; i++){
            orderid[i] = selectedRecords[i].data.id;
            //records[i] = selectedRecords[i].data.movetovaultlocationid;

            // Set a length limit counter to populate for horizontal display
            // Add length counter 
            length++;
            count++;;
           
             // If length is 5 or less, populate horizontally
             if(length <= 1){
                orderNumberLine += "&nbsp;" + count + ".&nbsp;" + selectedRecords[i].data.orderno + "&nbsp;".repeat(25);

            }else {
                // Reset length
                length = 0;

                // Add last entry in line
                orderNumberLine += "&nbsp;" + count + ".&nbsp;" + selectedRecords[i].data.orderno + "&nbsp;".repeat(25);
                // Add length to a newline
                selectedOrderNumbers += '<p>' + orderNumberLine+ '</p>';
                // Reset Serial Number Line
                orderNumberLine = "";
            }
            
            // Marks end of loop
            if(selectedRecords.length - count == 0){
                selectedOrderNumbers += '<p>' + orderNumberLine+ '</p>';
            }
            
           
            
        }
        
        layout = "<div style='height:100px;width:350px;border:1px solid #4e4e4e;overflow:auto;'>"+ selectedOrderNumbers  +"</div>";

        Ext.MessageBox.confirm(
            'Confirm', 'Are you sure you want to cancel the following orders? \n' + layout, function (btn) {
                if (btn === 'yes') {
                    snap.getApplication().sendRequest({
                        hdl: 'order', 'action': 'cancelOrder', 'id[]': orderid,
                    }, 'Sending request....').then(                        
                        function (data) {
                            if (data.success) {      
                                myView.getSelectionModel().deselectAll();                         
                                myView.getStore().reload();     
                                Ext.MessageBox.show({
                                    title: 'Notification', 
                                    msg: 'Successfully cancelled order',
                                    buttons: Ext.MessageBox.OK, icon: Ext.MessageBox.ALERT,
                                });                          
                            }else{
                                Ext.MessageBox.show({
                                    title: 'Error Message',
                                    msg: data.errmsg,
                                    buttons: Ext.MessageBox.OK, icon: Ext.MessageBox.ERROR
                                });
                            }
                        });                    
                }                
            });

    },    


    getPrintReport: function(btn){

        // grid header data
        header = []
        btn.up('grid').getColumns().map(column => {
            if (column.isVisible() && column.dataIndex !== null){
                _key = column.text;
                if('priceprovidername' == column.dataIndex){
                    // if column is priceprovidername add priceprovidercode
                    _value = 'priceprovidercode';
                }else {
                    _value = column.dataIndex;
                }
        
                columnlist = {
                    // [_key]: _value
                    text: _key,
                    index: _value
                }
                if (column.exportdecimal !== null){
                    _decimal = column.exportdecimal;
                    columnlist.decimal = _decimal;
                }
                header.push(columnlist);
            }
        });

        startDate = this.getView().getReferences().startDate.getValue()
        endDate = this.getView().getReferences().endDate.getValue()

        if(startDate > endDate){
            Ext.MessageBox.show({
                title: 'Filter Date',
                msg: 'Start date cannot be later than End date',
                buttons: Ext.MessageBox.OK,
                icon: Ext.MessageBox.ERROR
            });
            return
        }
        
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

        if (this.getView().partnercode){
            partnercode = this.getView().partnercode;
        }else{
            partnercode = 'mib';
        }

        url = '?hdl=miborder&action=exportExcel&header='+header+'&daterange='+daterange+'&partnercode='+partnercode;
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

    printReceipt: function(btn){
        handlerModule = btn.handlerModule;

        var myView = this.getView(),
            me = this, record;
        var sm = myView.getSelectionModel();
        var selectedRecords = sm.getSelection();

        var record = selectedRecords[0];

        orderid = (record && record.data) ? record.data.id : 0;
        url = '?hdl=order&action=printSpotOrder&orderid='+orderid;
        // url = Ext.urlEncode(url);
        var win = window.open('');
        win.location = url;
        win.focus();
        // Ext.DomHelper.append(document.body, {
        //     tag: 'iframe',
        //     id:'downloadIframe',
        //     frameBorder: 0,
        //     width: 0,
        //     height: 0,
        //     css: 'display:none;visibility:hidden;height: 0px;',
        //     src: url
        //   });
    },

    getPrintReportJob: function(btn){
        myView = this.getView();
        // grid header data
        header = []
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
                header.push(columnlist);
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

        // header = encodeURI(JSON.stringify(header));
        // daterange = encodeURI(JSON.stringify(daterange));
        header = JSON.stringify(header);
        daterange = JSON.stringify(daterange);
        
        if (this.getView().partnercode){
            partnercode = this.getView().partnercode;
        }else{
            partnercode = 'MIB';
        }
        
        var schedulepanel = new Ext.form.Panel({			
			frame: true,
            // layout: 'column',
            // defaults: {
            //     columnWidth: .5,                
            // },         
            border: 0,
            bodyBorder: false,
			bodyPadding: 10,
            width: 1200,
			items: [
                {
                    items: [
                        { xtype: 'hidden', hidden: true, name: 'id' },
                        {
                            itemId: 'user_main_fieldset',
                            xtype: 'fieldset',
                            title: 'Main Information',
                            title: 'Date Range',
                            layout: 'hbox',
                            defaultType: 'textfield',
                            fieldDefaults: {
                                anchor: '100%',
                                msgTarget: 'side',
                                margin: '0 0 5 0',
                                width: '100%',
                            },
                            items: [
                                {
                                    xtype: 'fieldcontainer',
                                    fieldLabel: '',
                                    defaultType: 'textboxfield',
                                    layout: 'hbox',
                                    items: [
                                        {
                                            xtype: 'displayfield', allowBlank: false, fieldLabel: 'Start Date', value : startDate, reference: 'accountholderpepname', name: 'accountholderpepname', flex: 5, style: 'padding-left: 20px;', labelWidth: '10%',
                                        },
                                        {
                                            xtype: 'displayfield', allowBlank: false, fieldLabel: 'End Date', value : endDate,  reference: 'accountholderpepic', name: 'accountholderpepic', flex: 5, style: 'padding-left: 20px;', labelWidth: '10%',
                                        },
                                    ]
                                }
                            ]
                        },
                        {
                            xtype: 'fieldset', title: 'Send to Email', collapsible: false,
                            items: [
                                {
                                    xtype: 'fieldcontainer',
                                    layout: {
                                        type: 'hbox',
                                    },
                                    items: [
                                        {
                                            xtype: 'textfield', fieldLabel: '', name: 'email', reference: 'email', flex: 2, style: 'padding-left: 20px;', id: 'email'
                                        },
                                    ]
                                },
                            ]
                        }
                    ],
                },		
			],						
        });
       
        var schedulewindowforappointment = new Ext.Window({
            title: 'Export Zip To Email..',
            layout: 'fit',
            height: 400,
            width: 700,
            modal: true,
            plain: true,
            buttonAlign: 'center',
            buttons: [{
                text: 'Submit',
                handler: function(btn) {
                    if (schedulepanel.getForm().isValid()) {
                        btn.disable();
                        schedulepanel.getForm().submit({
                            submitEmptyText: false,
                            url: 'gtp.php',
                            method: 'POST',
                            dataType: "json",
                            params: { hdl: 'order', action: 'exportZip',
                                     header: header,
                                     daterange: daterange,
                                     email: schedulepanel.getValues().email,
                                     partnercode: partnercode,
                            },
                            waitMsg: 'Sending',
                            success: function(frm, action) { //success                                   
                                Ext.MessageBox.show({
                                    title: 'Processing Zip',
                                    msg: 'The process will takes up to 15 - 30 minutes, please check your email later',
                                    buttons: Ext.MessageBox.OK,
                                    icon: Ext.MessageBox.INFO
                                });
                                owningWindow = btn.up('window');
                                owningWindow.close();
                                myView.getStore().reload();
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
                                    errmsg = 'Server Error';
                                }                                   
                                Ext.MessageBox.show({
                                    title: 'Error Message',
                                    msg: errmsg,
                                    buttons: Ext.MessageBox.OK,
                                    icon: Ext.MessageBox.ERROR
                                });                             
                            }
                        });
                        // Close Window on send
                        // owningWindow = btn.up('window');
                        // owningWindow.close();
                        // myView.getStore().reload();

                        // url = '?hdl=anppool&action=exportZip&header='+header+'&daterange='+daterange+'&email='+schedulepanel.getValues().email;
                        // url = Ext.urlEncode(url);
                
                        // Ext.DomHelper.append(document.body, {
                        //     tag: 'iframe',
                        //     id:'downloadIframe',
                        //     frameBorder: 0,
                        //     width: 0,
                        //     height: 0,
                        //     css: 'display:none;visibility:hidden;height: 0px;',
                        //     src: url
                        // });
                        // Ext.MessageBox.show({
                        //     title: 'Processing Zip to Email',
                        //     msg: 'The system will prompt a message again once process is done ',
                        //     buttons: Ext.MessageBox.OK,
                        //     icon: Ext.MessageBox.INFO
                        // });

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
            }],
            closeAction: 'destroy',
            items: schedulepanel
        });
        schedulewindowforappointment.show();
        // console.log('header',header)
        // url = '?hdl=anppool&action=exportZip&header='+header+'&daterange='+daterange;
        // url = Ext.urlEncode(url);

        // Ext.DomHelper.append(document.body, {
        //     tag: 'iframe',
        //     id:'downloadIframe',
        //     frameBorder: 0,
        //     width: 0,
        //     height: 0,
        //     css: 'display:none;visibility:hidden;height: 0px;',
        //     src: url
        //   });
    },
});
