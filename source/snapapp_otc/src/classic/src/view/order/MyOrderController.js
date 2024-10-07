Ext.define('snap.view.order.MyOrderController', {
    extend: 'snap.view.order.OrderController',
    alias: 'controller.myorder-myorder',


    onPreLoadViewDetail: function(record, displayCallback) {
        snap.getApplication().sendRequest({ hdl: 'myorder', action: 'detailview', id: record.data.id})
        .then(function(data){
            if(data.success) {
                displayCallback(data.record);
            }
        })
        return false;
    },

    getTransactionReport: function(btn){
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
       type = btn.reference;
       

       const reportingFields = [
            ['Date', ['createdon', 0]], 
            ['Transaction Ref No', ['refno', 0]],
            
        ];
        //{ key1 : [val1, val2, val3] } 
        
        for (let [key, value] of reportingFields) {
            //alert(key + " = " + value);
            columnleft = {
                // [_key]: _value
                text: key,
                index: value[0]
            }
            
            if (value[0] !== 0){
                columnleft.decimal = value[1];
            }
            header.push(columnleft);
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
                if('ordstatus' == column.dataIndex){
                    // dont push header if its status
                }else {
                    header.push(columnlist);
                }
              
            }
        });

        // Add a transaction header 
        
        
        startDate = this.getView().getReferences().startDate.getValue();
        endDate = this.getView().getReferences().endDate.getValue();

        // Alter this check if partner is BMMB
        if(partnerCode != 'BMMB'){
            if(!startDate || !endDate){
                Ext.MessageBox.show({
                    title: 'Filter Date',
                    msg: 'Please select date range for export',
                    buttons: Ext.MessageBox.OK,
                    icon: Ext.MessageBox.ERROR
                });
            }

            if(startDate > endDate){
                Ext.MessageBox.show({
                    title: 'Filter Date',
                    msg: 'Start date cannot be later than End date',
                    buttons: Ext.MessageBox.OK,
                    icon: Ext.MessageBox.ERROR
                });
                return
            }
        }else{
            if(!startDate || !endDate){
                Ext.MessageBox.show({
                    title: 'Filter Date',
                    msg: 'Please select date range for export',
                    buttons: Ext.MessageBox.OK,
                    icon: Ext.MessageBox.ERROR
                });
            }

            if(startDate > endDate){
                Ext.MessageBox.show({
                    title: 'Filter Date',
                    msg: 'Start date cannot be later than End date',
                    buttons: Ext.MessageBox.OK,
                    icon: Ext.MessageBox.ERROR
                });
                return
            }
        }
        
        // Do a daterange checker
        // If date exceeds 2 months, reject
        // Init date values
        var msecPerMinute = 1000 * 60;
        var msecPerHour = msecPerMinute * 60;
        var msecPerDay = msecPerHour * 24;

        // Calculate date interval 
        var interval = endDate - startDate;

        var intervalDays = Math.floor(interval / msecPerDay );

        // Get days of months
        // Startdate
        startMonth = new Date(startDate.getYear(), startDate.getMonth(), 0).getDate();

        endMonth = new Date(endDate.getYear(), endDate.getMonth(), 0).getDate();

        // Get 2 months range limit for filter
        rangeLimit = startMonth + endMonth;

        if (startDate && endDate){
            // Check if day exceeds 63 days 
            // Skip this check if partner is BMMB
            // if(partnerCode != 'BMMB'){
            //     if (rangeLimit >= intervalDays){
            //         // Check if day exceeds 63 days 
                    
            //         startDate = Ext.Date.format(startDate,'Y-m-d 00:00:00');
            //         endDate = Ext.Date.format(endDate,'Y-m-d 23:59:59');
            //         daterange = {
            //             startDate: startDate,
            //             endDate: endDate,
            //         }
            //     }else{
            //         Ext.MessageBox.show({
            //             title: 'Filter Date',
            //             msg: 'Please select date range within 2 months',
            //             buttons: Ext.MessageBox.OK,
            //             icon: Ext.MessageBox.ERROR
            //         });
            //         return
            //     }
            //     // End check
            // }else{
            //     // for bmmb no limit imposed
            //     startDate = Ext.Date.format(startDate,'Y-m-d 00:00:00');
            //     endDate = Ext.Date.format(endDate,'Y-m-d 23:59:59');
            //     daterange = {
            //         startDate: startDate,
            //         endDate: endDate,
            //     }
            // }
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

        type = encodeURI(JSON.stringify(type));

        // partnerCode = myView.partnercode;
        //url = '?hdl=bmmborder&action=exportExcel&header='+header+'&daterange='+daterange+'&type='+type;'
        url = '?hdl=myorder&action=exportExcel&header='+header+'&daterange='+daterange+'&partnercode='+partnerCode;
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

    getTransactionReportFromSearch: function(btn){
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
       type = btn.reference;
       

       const reportingFields = [
            ['Date', ['createdon', 0]], 
            ['Transaction Ref No', ['refno', 0]],
            
        ];
        //{ key1 : [val1, val2, val3] } 
        
        for (let [key, value] of reportingFields) {
            //alert(key + " = " + value);
            columnleft = {
                // [_key]: _value
                text: key,
                index: value[0]
            }
            
            if (value[0] !== 0){
                columnleft.decimal = value[1];
            }
            header.push(columnleft);
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
                if('ordpartnername' == column.dataIndex || 'ordstatus' == column.dataIndex || 'originalprice' == column.dataIndex ){
                    // dont push header if its status
                }else {
                    header.push(columnlist);
                }
              
            }
        });

        // Add a transaction header 
        
        
        startDate = this.getView().getReferences().startDate.getValue();
        endDate = this.getView().getReferences().endDate.getValue();

        // Alter this check if partner is BMMB
        if(partnerCode != 'BMMB'){
            if(!startDate || !endDate){
                Ext.MessageBox.show({
                    title: 'Filter Date',
                    msg: 'Please select date range for export',
                    buttons: Ext.MessageBox.OK,
                    icon: Ext.MessageBox.ERROR
                });
            }

            if(startDate > endDate){
                Ext.MessageBox.show({
                    title: 'Filter Date',
                    msg: 'Start date cannot be later than End date',
                    buttons: Ext.MessageBox.OK,
                    icon: Ext.MessageBox.ERROR
                });
                return
            }
        }else{
            if(!startDate || !endDate){
                Ext.MessageBox.show({
                    title: 'Filter Date',
                    msg: 'Please select date range for export',
                    buttons: Ext.MessageBox.OK,
                    icon: Ext.MessageBox.ERROR
                });
            }

            if(startDate > endDate){
                Ext.MessageBox.show({
                    title: 'Filter Date',
                    msg: 'Start date cannot be later than End date',
                    buttons: Ext.MessageBox.OK,
                    icon: Ext.MessageBox.ERROR
                });
                return
            }
        }
        
        // Do a daterange checker
        // If date exceeds 2 months, reject
        // Init date values
        var msecPerMinute = 1000 * 60;
        var msecPerHour = msecPerMinute * 60;
        var msecPerDay = msecPerHour * 24;

        // Calculate date interval 
        var interval = endDate - startDate;

        var intervalDays = Math.floor(interval / msecPerDay );

        // Get days of months
        // Startdate
        startMonth = new Date(startDate.getYear(), startDate.getMonth(), 0).getDate();

        endMonth = new Date(endDate.getYear(), endDate.getMonth(), 0).getDate();

        // Get 2 months range limit for filter
        rangeLimit = startMonth + endMonth;

        if (startDate && endDate){
            // Check if day exceeds 63 days 
            // Skip this check if partner is BMMB
            // if(partnerCode != 'BMMB'){
            //     if (rangeLimit >= intervalDays){
            //         // Check if day exceeds 63 days 
                    
            //         startDate = Ext.Date.format(startDate,'Y-m-d 00:00:00');
            //         endDate = Ext.Date.format(endDate,'Y-m-d 23:59:59');
            //         daterange = {
            //             startDate: startDate,
            //             endDate: endDate,
            //         }
            //     }else{
            //         Ext.MessageBox.show({
            //             title: 'Filter Date',
            //             msg: 'Please select date range within 2 months',
            //             buttons: Ext.MessageBox.OK,
            //             icon: Ext.MessageBox.ERROR
            //         });
            //         return
            //     }
            //     // End check
            // }else{
            //     // for bmmb no limit imposed
            //     startDate = Ext.Date.format(startDate,'Y-m-d 00:00:00');
            //     endDate = Ext.Date.format(endDate,'Y-m-d 23:59:59');
            //     daterange = {
            //         startDate: startDate,
            //         endDate: endDate,
            //     }
            // }
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

        type = encodeURI(JSON.stringify(type));

        // Get extra search parameters from current grid proxy
        currentProxy = btn.up().up().up().up().up().getController().lookupReference('myorder').getStore().proxy.url;
        // Skip first to index as they are not required
        // Init carryoverparams
        carryOverParams = '';

        for(i = 2; i < currentProxy.split('&').length; i++){
            carryOverParams += '&' + currentProxy.split('&')[i]
        }

        // partnerCode = myView.partnercode;
        //url = '?hdl=bmmborder&action=exportExcel&header='+header+'&daterange='+daterange+'&type='+type;'
        url = '?hdl=myorder&action=exportExcel&header='+header+'&daterange='+daterange+'&partnercode='+partnerCode+'&fromsearchresult='+true+carryOverParams;
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
    
    editReferenceNo: function(formView, form, record, asyncLoadCallback){
       
        var me = this, selectedRecord,
            myView = this.getView();
        var sm = myView.getSelectionModel();
        var selectedRecords = sm.getSelection();
            orderRefNo = [];
            orderId = [];

      var gridFormView = Ext.create(myView.formClass, Ext.apply(myView.formSendToSap ? myView.formSendToSap : {}, {
            formDialogButtons: [{
                text: 'Submit',
                handler: function(btn) {
                    gridFormView.setTitle("Confirm Update Ref No?");
                    me._editReferenceNo(btn);
                }
            },{
                text: 'Confirm Submit',
                hidden: true,
                handler: function(btn) {
                    me._sendEditReferenceNo(btn, orderRefNo, myView, gridFormView, orderId);
                }
            },{
                text: 'Close',
                handler: function(btn) {
                    owningWindow = btn.up('window');
                    owningWindow.close();
                    me.gridFormView = null;
                }
            },{
                text: 'Back',
                hidden:true,
                handler: function(btn) {
                    gridFormView.setTitle("Update Ref No.");
                    me._revertToEditForm(btn);
                }
            }]
        }));

        this.gridFormView = gridFormView;
        gridFormView.title = "Update Reference No.";
       
        if(1 <= selectedRecords.length) {
            //debugger;
            var panel = Ext.getCmp('sendtosapformdisplay');
    
            //date = data.createdon.date;
            //filtereddate = Ext.Date.format(new Date(date), "Y-m-d");
            panel.removeAll();
            for(var i = 0; i < selectedRecords.length; i++) {
                //selectedID = selectedRecords[i].get('id');
                //selectedRecord = selectedRecords[i];
                orderRefNo[i] = selectedRecords[i].data.refno;
                recordIndex = i;
                recordName = selectedRecords[i].data.id;
                orderId[i] = selectedRecords[i].data.orderid;

                if (selectedRecords[i].data.ordtype == 'CompanyBuy'){
                    buyorselltypelabel = 'Buy from Customer';
                    
                }else if (selectedRecords[i].data.ordtype == 'CompanySell'){
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
                                            xtype: 'displayfield', name:selectedRecords[i].data.ordorderno, value: selectedRecords[i].data.ordorderno, fieldLabel: 'Booking Number', flex: 1, style:'padding-left: 20px;padding-right: 20px;',
                                        },
                                        {
                                            xtype: 'displayfield', name:selectedRecords[i].ordxau, value: selectedRecords[i].data.ordxau, fieldLabel: "Xau Weight (g)", flex: 1, renderer: Ext.util.Format.numberRenderer('0,000.000'), style:'padding-left: 20px;padding-right: 20px;', 
                                        }
                                    ]
                                },
                                {
                                    xtype: 'fieldcontainer',
                                    layout: 'vbox',
                                    flex: 2,
                                    items: [
                                        {
                                            xtype: 'displayfield', name:selectedRecords[i].data.ordprice, value: selectedRecords[i].data.ordprice, fieldLabel: 'Price (RM/g)', flex: 1, renderer: Ext.util.Format.numberRenderer('0,000.00'), style:'padding-left: 20px;padding-right: 20px;', 
                                        },
                                        {
                                            xtype: 'textfield',
                                            listeners : {
                                                change : function (f, e){
                                                    this.setValue(this.getValue().replace(/[^0-9.]/g, '').replace(/(\..*?)\..*/g, '$1').match(/^\s*(?=.*[1-9])\d*(?:\.\d{0,2})?\s*/g));
                                                }
                                            },
                                            name: selectedRecords[i].dbmpdtverifiedamount, value: '' == selectedRecords[i].data.dbmpdtverifiedamount ? selectedRecords[i].data.ordamount : selectedRecords[i].data.dbmpdtverifiedamount, fieldLabel: 'Verified Amount', flex: 1, decimalPrecision: 2, style: 'padding-left: 20px;padding-right: 20px;',
                                        }
                                    ]
                                },
                                {
                                    xtype: 'fieldcontainer',
                                    layout: 'vbox',
                                    flex: 2,
                                    items: [
                                        {
                                            xtype: 'displayfield', name:selectedRecords[i].data.ordproductname, value: selectedRecords[i].data.ordproductname, fieldLabel: 'Product Type', flex: 1, style:'padding-left: 20px;padding-right: 20px;', 
                                        },
                                        {
                                            xtype: 'textfield', name:selectedRecords[i].dbmbankrefno, value: selectedRecords[i].data.dbmbankrefno, fieldLabel: 'Bank Ref No', flex: 1, style:'padding-left: 20px;padding-right: 20px;', 
                                        }
                                    ]
                                },
                                {
                                    xtype: 'fieldcontainer',
                                    layout: 'vbox',
                                    flex: 2,
                                    items: [
                                        {
                                            xtype: 'displayfield', name:selectedRecords[i].ordamount, value: selectedRecords[i].data.ordamount, fieldLabel: "Gross Value (RM)", flex: 1, renderer: Ext.util.Format.numberRenderer('0,000.00'), style:'padding-left: 20px;padding-right: 20px;', 
                                        },
                                        /*
                                        {
                                            xtype: 'datefield', format: 'Y-m-d H:i:s', name:selectedRecords[i].data.dbmpdtrequestedon, value: selectedRecords[i].data.dbmpdtrequestedon, fieldLabel: "Transaction Date", flex: 1, style:'padding-left: 20px;padding-right: 20px;',
                                        },*/
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

    editPendingRefundStatus: function (btn) {
        let me = this;
        
        var errmsg = "";
        var errbool = false;

        if(me.getView().getSelectionModel().getSelection().length > 1){
            errbool = true;
            errmsg = "Please select only one record";
            
            Ext.MessageBox.show({
                title: 'Warning',
                buttons: Ext.MessageBox.OK,
                icon: Ext.MessageBox.Warning,
                msg: errmsg,
            });
        }
        else{
            let selection = me.getView().getSelectionModel().getSelection()[0];
            if(!selection){
                errbool = true;
                errmsg = "Select a record first";
            }
            else if(selection.get('status') != 5){
                errbool = true;
                errmsg = "This Function is only for 'Pending Refund' status order";
            }
    
            if(errbool == true && errmsg != ""){
                Ext.MessageBox.show({
                    title: 'Warning',
                    buttons: Ext.MessageBox.OK,
                    icon: Ext.MessageBox.Warning,
                    msg: errmsg,
                });
            }
            else {
                var statusList = new Ext.data.Store({
                    fields:['value','text'],
                    data:[
                        {'value':'5', 'text': 'Pending Refund'},
                        {'value':'6', 'text': 'Refunded'},
                    ]
                });
                let popup = new Ext.Window({
                    title: 'Update Order Status',
                    layout: 'form',
                    width: 530,
                    closeAction: 'close',
    
                    items: [{
                        xtype: 'hiddenfield',
                        itemId: 'orderid',
                        fieldLabel: 'ID',
                        readOnly: true,
                        value: selection.get('id'),
                    },{
                        xtype: 'textfield',
                        fieldLabel: 'Transaction Ref No.',
                        itemId: 'refno',
                        readOnly: true,
                        value: selection.get('refno'),
                    },{
                        xtype: 'textfield',
                        fieldLabel: 'Customer Name',
                        readOnly: true,
                        value: selection.get('dbmpdtaccountholdername'),
                    },{
                        xtype: 'combo',
                        itemId: 'comboStatus',
                        store: statusList,
                        displayField: 'text',
                        valueField: 'value',
                        fieldLabel: 'Status',
                        value: selection.get('status'),
                        editable:false,
                    }],
    
                    buttons: [{
                        text: 'Update',
                        handler: function() {
                            var id = popup.items.get('orderid').getValue();
                            var refNo = popup.items.get('refno').getValue();
                            var status = popup.items.get('comboStatus').getValue();

                            if(status == null || status.trim() == ''){
                                Ext.MessageBox.show({
                                    title: 'Error',
                                    msg: 'Please select Status',
                                    buttons: Ext.MessageBox.OK, 
                                    icon: Ext.MessageBox.ERROR
                                });
                                return;
                            }
                            else{
                                popup.close();
                                snap.getApplication().sendRequest({
                                    hdl: 'mygoldtransaction', 
                                    action: 'updatePendingRefundStatus', 
                                    id: id,
                                    refno: refNo,
                                    status: status,
                                }, 'Sending request....').then(
                                    function (data) {
                                        if (data.success) {
                                            Ext.MessageBox.show({
                                                title: 'Message',
                                                msg: data.message,
                                                icon: Ext.MessageBox.INFO,
                                                buttons: Ext.MessageBox.OK, 
                                            });

                                            //me.getView().getSelectionModel().deselectAll();
                                            me.getView().getStore().reload();
            
                                            owningWindow = modalBtn.up('window');
                                            owningWindow.close();
            
                                            snap.getApplication().getStore('snap.store.MyGoldTransaction').reload();
                                            
                                        } 
                                        else {
                                            Ext.MessageBox.show({
                                                title: 'Error',
                                                msg: data.message,
                                                buttons: Ext.MessageBox.OK, icon: Ext.MessageBox.ERROR
                                            });
                                        }
                                    });  
                            }
                        }
                    },{
                        text: 'Cancel',
                        handler: function() {
                            popup.close();
                        }
                    }],
                    buttonAlign: 'center',
                });
    
                popup.show();
            }
        }
    },

    _editReferenceNo: function(btn, gridFormView) {
        // var owningWindow = btn.up('window');
        // var gridFormPanel = owningWindow.down('form');
        var me = this,
            myView = this.getView();
        
       
        // Get cmp
        // init button settings
        items = btn.up().up().items.items[0].items.items[0].items.items;


        for(i = 0; i < items.length; i++){
            // Set confirmation view
            items[i].items.items[1].items.items[1].setReadOnly(true);
            items[i].items.items[2].items.items[1].setReadOnly(true);
            //items[i].items.items[3].items.items[1].setReadOnly(true);
      
            
            // Set Highlight
            items[i].items.items[1].items.items[1].setFieldLabel("<b>Verified Amount</>");
            items[i].items.items[2].items.items[1].setFieldLabel("<b>Bank Ref No</>");
            //items[i].items.items[3].items.items[1].setFieldLabel("<b>Transaction Date</>");
            
            //items[i].items.items[1].items.items[1].setFieldStyle('background: linear-gradient(to right, rgba(23, 103, 239, 0.85), rgba(20, 196, 251, 0.85));');
            // If different amounts
            if (items[i].items.items[3].items.items[0].getValue() != items[i].items.items[1].items.items[1].getValue()) {
                $borderColor = 'border-color: red';
            } else {
                $borderColor = 'border-color: green';
            }
            
            items[i].items.items[1].items.items[1].setFieldStyle('border-style:solid; border-width : 2px;' + $borderColor);

            if (items[i].items.items[2].items.items[1].getValue() == '') {
                $borderColor = 'border-color: red';
            } else {
                $borderColor = 'border-color: green';
            }
            items[i].items.items[2].items.items[1].setFieldStyle('border-style:solid; border-width : 2px;' + $borderColor);
            //items[i].items.items[3].items.items[1].setFieldStyle('border-style:solid; border-width : 2px;');

        }



        // Swap buttons
        btn.up().items.items[0].setHidden(true);
        btn.up().items.items[1].setHidden(false);
        btn.up().items.items[2].setHidden(true);
        btn.up().items.items[3].setHidden(false);

    },

    _revertToEditForm: function(btn, gridFormView) {
        // var owningWindow = btn.up('window');
        // var gridFormPanel = owningWindow.down('form');
        var me = this,
            myView = this.getView();
        
       
        // Get cmp
        // init button settings
        items = btn.up().up().items.items[0].items.items[0].items.items;


        for(i = 0; i < items.length; i++){

            // Set confirmation view
            items[i].items.items[1].items.items[1].setReadOnly(false);
            items[i].items.items[2].items.items[1].setReadOnly(false);
            //items[i].items.items[3].items.items[1].setReadOnly(false);

           // Set Highlight
           items[i].items.items[1].items.items[1].setFieldLabel("Verified Amount");
           items[i].items.items[2].items.items[1].setFieldLabel("Bank Ref No");
           //items[i].items.items[3].items.items[1].setFieldLabel("Transaction Date");

           //items[i].items.items[1].items.items[1].setFieldStyle('background:#fff;');
           items[i].items.items[1].items.items[1].setFieldStyle('border-style:none; border-width : 1px;');
           items[i].items.items[2].items.items[1].setFieldStyle('border-style:none; border-width : 1px;');
           //items[i].items.items[3].items.items[1].setFieldStyle('border-style:none; border-width : 1px;');

      
        }

        // Swap buttons
        btn.up().items.items[0].setHidden(false);
        btn.up().items.items[1].setHidden(true);
        btn.up().items.items[2].setHidden(false);
        btn.up().items.items[3].setHidden(true);


    },

    _sendEditReferenceNo: function(btn, orderRefNo, grid, gridFormView, orderId) {
        // var owningWindow = btn.up('window');
        // var gridFormPanel = owningWindow.down('form');
        var me = this,
            myView = this.getView();
        
       
        // Get cmp
        // init button settings
        items = btn.up().up().items.items[0].items.items[0].items.items;
        
        verifiedAmount = []; 
        bankRefNo = [];
        //transactionDate = []; 

        for(i = 0; i < items.length; i++){
            verifiedAmount[i] = items[i].items.items[1].items.items[1].value;
            bankRefNo[i] = items[i].items.items[2].items.items[1].value;
            //transactionDate[i] = items[i].items.items[3].items.items[1].value;
        }
        gridFormView.close();
        
        snap.getApplication().sendRequest({
            hdl: 'mygoldtransaction', action: 'editReferenceNo', 
            'refno[]': orderRefNo,
            'verifiedamount[]': verifiedAmount,
            'bankrefno[]': bankRefNo,
            //'transactiondate[]': transactionDate,
            'orderid[]': orderId,

        }, 'Fetching data from server....').then(
        //Received data from server already
        function(data){
            if(data.success){
                
                //gridFormView.close();
                grid.getStore().reload();

            }
        });
    },

    uploadbulkpaymentresponse: function(btn, formAction) {
        var me = this, selectedRecord,
            myView = this.getView();
        var sm = myView.getSelectionModel();

        var gridFormView = Ext.create(myView.formClass, Ext.apply(myView.uploadbulkpaymentresponseform ? myView.uploadbulkpaymentresponseform : {}, {
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
    _uploadFile: function(btn) {
        // var owningWindow = btn.up('window');
        // var gridFormPanel = owningWindow.down('form');
        var me = this,
        myView = this.getView();
        partnerCode = myView.partnercode;
        // console.log('form',btn);return
        form = btn.lookupController().lookupReference('bpaymentlist-form').getForm();
        transactionlisting = form.getFieldValues();
        if (form.isValid()) {
            if ( transactionlisting.bpaymentlist != null) {
                form.submit({
                    url: 'index.php?hdl=myorder&action=uploadbulkpaymentresponse&partnercode='+partnerCode,
                    // url: 'index.php?hdl=tender&action=uploadTenderFile',
                    dataType: "json",
                    waitMsg: 'Uploading your bulk payment response...',
                    success: function(form, action) {
                        if (action.result.success){
                            Ext.Msg.alert('Success', 'Transactions list has updated.');
                            return;
                        }
                    },
                    failure: function (form, action) {  
                        Ext.Msg.alert('Exception', action.result.message);
                        return;
                    }
                });
            } else {
                    Ext.MessageBox.show({
                    title: "ERROR-A1001",
                    msg: "Choose correct .txt file",
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
                            params: { hdl: 'myorder', action: 'exportZip',
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

    // New OTC Feature for approval
    // Still subject to change
    approveTransaction: function (btn, formAction, id) {
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
        // var sm = myView.getSelectionModel();
        // var selectedRecords = sm.getSelection();
        // if(!transactionId){

        //     trxid = selectedRecords[0].data.id;
        //     ordamount = selectedRecords[0].get('ordamount');
        //     fullname = selectedRecords[0].get('achfullname');
        //     if (selectedRecords.length == 1) {
        //         for (var i = 0; i < selectedRecords.length; i++) {
        //             selectedID = selectedRecords[i].get('id');
        //             selectedRecord = selectedRecords[i];
        //             break;
        //         }
        //     } else if ('add' != formAction) {
        //         Ext.MessageBox.show({
        //             title: 'Warning', buttons: Ext.MessageBox.OK, icon: Ext.MessageBox.WARNING,
        //             msg: 'Select a record first'
        //         });
        //         return;
        //     }
        // }else{
        //     trxid = transactionId;
        //     store = this.getView().getStore();
        //     var record = store.findRecord('id', trxid);
        //     if (record) {
        //         // Do something with the record...
        //         ordamount = record.data.ordamount;
        //         fullname = record.data.achfullname;
        //     }
            
            
        // }
        trxid = transactionId;
        store = myView.getStore();
        var record = store.findRecord('id', trxid);

        if (record) {
            // Do something with the record...
            ordamount = record.data.ordamount;
            fullname = record.data.achfullname;
        }else{
            Ext.MessageBox.show({
                title: 'Warning', buttons: Ext.MessageBox.OK, icon: Ext.MessageBox.WARNING,
                msg: 'No Records Found'
            });
            return;
        }
            

        //debugger;
        // Check if buy or sell based on button reference
        /*
        if('dailytransactionsell' == btn.reference){
            // filter by companysell
            
        }else if('dailytransactionbuy' == btn.reference){
            // filter by companybuy
        }
        */


        // var partnerId = selectedRecords[i].data['partnerid'];
        // var accountHolderId = selectedRecords[i].data['id'];

        // Legend for Approval metrics for reference
        /* 
        * Case 1: X <= RM 10,000 ( Teller approve )
        * Case 2: RM 10,000 < X < RM 30,0000 ( Pegawai Cawagan )
        * Case 3: RM 30,0000 <= X  ( Requires approval code to approve )
        */
    // Case 2 Where in between RM 10,000 to RM 30,000

    // Case 3  Where Transaction exceeds RM 30,000
        var gridFormView = Ext.create(myView.formClass, Ext.apply(myView.formOtcApproval ? myView.formOtcApproval : {}, {
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
                    var approvalcode = Ext.getCmp('approvalcode').getValue();
                    var originalamount = selectedRecords[0].data.originalamount;

                    Ext.MessageBox.confirm(
                        'Confirm Approval', 'Are you sure you want to approve ?', function (btn) {
                            if (btn === 'yes') {
                                if(PROJECTBASE == 'BSN' ){
                                    //if(originalamount >= 30000){
                                        if(approvalcode.length != 0){
                                            if( approvalcode.length >= 2 && approvalcode.length <= 10){
        
                                                snap.getApplication().sendRequest({
                                                    hdl: 'myorder', 'action': 'approvePendingGoldTransactions', id: trxid, 'remarks': remarks, 'approvalcode': approvalcode , 
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
                                            }else{
        
                                                if(approvalcode.length < 2){
                                                    Ext.Msg.show({
                                                        title: 'Warning',
                                                        message: 'The approval code cannot be less than 2 characters.',
                                                        icon: Ext.MessageBox.WARNING,
                                                        buttons: Ext.Msg.OK,
                                                    });
                                                }   
        
                                                
                                            }
                                        }else{
                                            snap.getApplication().sendRequest({
                                                hdl: 'myorder', 'action': 'approvePendingGoldTransactions', id: trxid, 'remarks': remarks, 'approvalcode': approvalcode , 
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
                                    //}
                                }else{
                                    snap.getApplication().sendRequest({
                                        hdl: 'myorder', 'action': 'approvePendingGoldTransactions', id: trxid, 'remarks': remarks, 'approvalcode': approvalcode , 
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
                    var remarks = Ext.getCmp('approvalremarks').getValue();
                    Ext.MessageBox.confirm(
                        'Confirm Rejection', 'Are you sure you want to reject?', function (btn) {
                            if (btn === 'yes') {
                                snap.getApplication().sendRequest({
                                    hdl: 'myorder', 'action': 'rejectPendingGoldTransactions', id: trxid, 'remarks': remarks, 'approvalcode': approvalcode ,
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
        //fullname = selectedRecords[i].get('fullname');
        gridFormView.controller.getView().lookupReference('ordorderno').setValue(trxid);
        gridFormView.controller.getView().lookupReference('ordamount').setValue(ordamount);
        // check if less than 30000 hide aproval code
        var fieldset = Ext.getCmp('approvalfieldset');
        if(fieldset){
            // if(ordamount < 30000){
            //     fieldset.hide();
            // }else{
            //     fieldset.show();
            // }
        }else {
            console.error("Component with id 'approvalfieldset' not found.");
        }

        // gridFormView.accountHolderId = selectedRecords[i].get('id');

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

        me.gridFormView = gridFormView;
        // this._formAction = "edit";

        // var addEditForm = this.gridFormView.down('form').getForm();

        // gridFormView.title = 'Update ' + gridFormView.title + '...';
        // if (Ext.isFunction(me['onPreLoadForm'])) {
        //     if (!this.onPreLoadForm(gridFormView, addEditForm, selectedRecord, function (updatedRecord) {
        //         addEditForm.loadRecord(updatedRecord);
        //         if (Ext.isFunction(me['onPostLoadForm'])) this.onPostLoadForm(gridFormView, addEditForm, updatedRecord);
        //         me.gridFormView.show();
        //     })) return;
        // }
        // addEditForm.loadRecord(selectedRecord);
        // if (Ext.isFunction(me['onPostLoadForm'])) this.onPostLoadForm(gridFormView, addEditForm, selectedRecord);

        Ext.Ajax.request({
            url: 'index.php?hdl=myorder&action=getInterval&transactionId='+trxid,
            method: 'get',
            //waitMsg: 'Processing',
            //params: { summaryfromdate: summaryfromdate, summarytodate: summarytodate, summarytype: summarytype },
            autoAbort: false,
            success: function (result) {
                var data = JSON.parse(result.responseText);
                console.log(result);
                    if(data.success){
                        me.gridFormView.show();
                    }
                    else{
                        Ext.MessageBox.show({
                            title: "Error",
                            msg: "Transactions Expired",
                            buttons: Ext.MessageBox.OK,
                            icon: Ext.MessageBox.WARNING
                        });
                    } 
            
                          //debugger;
                          //window.location.href = result.responseText;
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

    printSpotOrderOtc: function(btn){
        var myView = this.getView(),
        me = this, selectedRecord;
        
        sm = myView.getSelectionModel();
        selectedRecords = sm.getSelection();
        // grid header data
        header = [];
        partnercode = myView.partnercode;

        printType = btn.reference;
        //debugger;
        // Check if buy or sell based on button reference
        /*
        if('dailytransactionsell' == btn.reference){
            // filter by companysell
            
        }else if('dailytransactionbuy' == btn.reference){
            // filter by companybuy
        }
        */
       
        // partnerCode = myView.partnercode;
        //url = '?hdl=bmmborder&action=exportExcel&header='+header+'&daterange='+daterange+'&type='+type;'
        if((selectedRecords != '')){
            if((selectedRecords[0].data.status != 1 && selectedRecords[0].data.status != 2) && printType == 'printspotorderotc'){
                Ext.MessageBox.show({
                    title: 'Error Message',
                    msg: 'Fail Transaction is not allowed to print.',
                    buttons: Ext.MessageBox.OK,
                    icon: Ext.MessageBox.ERROR
                });
            }else{
               var url = 'index.php?hdl=myorder&action=printSpotOrderOTC&orderid='+selectedRecords[0].data.orderid+'&partnercode='+partnercode+'&printType='+printType;
                Ext.Ajax.request({
                    url: url,
                    method: 'get',
                    waitMsg: 'Processing',
                    //params: { summaryfromdate: summaryfromdate, summarytodate: summarytodate, summarytype: summarytype },
                    autoAbort: false,
                    success: function (result) {
                                //debugger;
                               // window.location.href = result.responseText;
	                var responseData = Ext.decode(result.responseText);
                        if (responseData.success === true) {
                            var win = window.open('');
                            win.location = responseData.url;
                           win.focus();
                        }else {
                            // The request was successful, but the response indicates failure
                            Ext.MessageBox.show({
                                title: 'Error Message',
                                msg: responseData.errorMessage, // You can customize the error message based on the response
                                buttons: Ext.MessageBox.OK,
                                icon: Ext.MessageBox.ERROR
                            });
                        }
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
 
            }
            

        }else{
            Ext.MessageBox.show({
                title: 'Error Message',
                msg: 'No Records selected.',
                buttons: Ext.MessageBox.OK,
                icon: Ext.MessageBox.ERROR
            });
        }
    },
});
