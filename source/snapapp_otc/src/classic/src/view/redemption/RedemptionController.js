Ext.define('snap.view.redemption.RedemptionController', {
    extend: 'snap.view.gridpanel.BaseController',
    alias: 'controller.redemption-redemption',
    listeners: {
        render: function(store) {
            store.on('load', function(records) {               
            });
        }
    }, 
    
    onPreLoadViewDetail: function(record, displayCallback) {
        snap.getApplication().sendRequest({ hdl: 'redemption', action: 'detailview', id: record.data.id})
        .then(function(data){
            if(data.success) {
                displayCallback(data.record);
            }
        })
        return false;
    },
    /*
    setTextColor: function(val,m,record) {      
        if(record.get('status')==0) return '<span style="color:#007bc5;">' + val + '</span>';
        if(record.get('status')==1) return '<span style="color:#FFA500;">' + val + '</span>';
        if(record.get('status')==2) return '<span style="color:#0ead30;">' + val + '</span>';
        if(record.get('status')==3) return '<span style="color:#F42A12;">' + val + '</span>';
        if(record.get('status')==4) return '<span style="color:#6C3483;">' + val + '</span>';
        if(record.get('status')==5) return '<span style="color:#6E2C00;">' + val + '</span>';
    },*/
    setTextColor: function(val,m,record) {      
        if(record.get('status')==0) return '<span style="color:#000000;">' + val + '</span>';
        if(record.get('status')==1) return '<span style="color:#000000;">' + val + '</span>';
        if(record.get('status')==2) return '<span style="color:#000000;">' + val + '</span>';
        if(record.get('status')==3) return '<span style="color:#000000;">' + val + '</span>';
        if(record.get('status')==4) return '<span style="color:#000000;">' + val + '</span>';
        if(record.get('status')==5) return '<span style="color:#000000;">' + val + '</span>';
    },
    setSerialno: function(val,m,record) { 
        status = record.get('status');
        if (status == 1){
            items = Ext.JSON.decode(record.get('items'));
            xreturn = '';
            items.map((item) => {
                xreturn += '<span>Serial: '+item.serialnumber+', Deno: '+item.weight+' , Gold: '+item.code+'</span><br>';
            })
            return xreturn;
        }
        return '';
    },
    setStatusDesc: function(value,store) {
        if(value==0) return 'Pending';
            if(value==1) return 'Confirmed';
            if(value==2) return 'Completed';
            if(value==3) return 'Failed';
            if(value==4) return 'Process Delivery';
            if(value==5) return 'Cancelled';
        else return '';
    },    
    scheduleDate: function(record) {
        var myView = this.getView(),
            me = this, record;
        var sm = myView.getSelectionModel();        
        var selectedRecords = sm.getSelection();     
        var address=selectedRecords[0].data.deliveryaddress1+' '+selectedRecords[0].data.deliveryaddress2+' '+selectedRecords[0].data.deliveryaddress3+' '+selectedRecords[0].data.deliverystate;
        var schedulepanel = new Ext.form.Panel({			
			frame: true,
            layout: 'column',
            defaults: {
                columnWidth: .5,                
            },         
            border: 0,
            bodyBorder: false,
			bodyPadding: 10,
			items: [
                {
                    items: [
                        { xtype: 'hiddenfield', inputType: 'hidden', hidden: true, name: 'id' , value:selectedRecords[0].id,allowBlank: false},	
                        { 
                            xtype: 'combobox',
                            fieldLabel: 'Sales Person',
                            name:'salespersonid',
                            typeAhead: true,
                            triggerAction: 'all',
                            selectOnTab: true,
                            store: {
                                autoLoad: true,
                                type: 'SalesPersons',                   
                                sorters: 'name'
                            },               
                            lazyRender: true,
                            displayField: 'name',
                            valueField: 'id',
                            queryMode: 'remote',
                            remoteFilter: false,
                            listClass: 'x-combo-list-small',
                            forceSelection: true,
                            allowBlank: false
                        },     
                    ]
                },
                {
                    items:[
                        { xtype: 'datefield', fieldLabel: 'Date of Delivery', name: 'dateofdelivery', format: 'Y-m-d H:i:s', allowBlank: false },                      
                    ]
                },			
			],						
        });
        var type = "";
        if(!selectedRecords[0].get('type')){
            type = selectedRecords[0].get('rdmtype'); 
            redemptionid =  selectedRecords[0].get('redemptionid');
            status =  selectedRecords[0].get('rdmstatus');
        }else {
            type = selectedRecords[0].get('type');
            redemptionid = selectedRecords[0].get('id');
            status =  selectedRecords[0].get('status');
        }
        
        var deliverypanel = new Ext.form.Panel({			
			frame: true,
            layout: 'column',
            defaults: {
                columnWidth: .5,                
            },         
            border: 0,
            bodyBorder: false,
			bodyPadding: 10,
			items: [
                {
                    items: [
                        { xtype: 'hiddenfield', inputType: 'hidden', hidden: true, name: 'id' , value:selectedRecords[0].id,allowBlank: false},	
                        { 
                            xtype: 'combobox',
                            fieldLabel: 'Delivery by',
                            name:'vendor',
                            typeAhead: true,
                            triggerAction: 'all',
                            selectOnTab: true,                            
                            store: {
                                autoLoad: true,
                                type: 'LogisticVendors',                   
                                filters: [{
                                    filterFn:function(record){
                                        if(type=='SpecialDelivery'){
                                            return record.data.value=='CourAce';
                                        }else{
                                            return record.data.value!='CourAce';
                                        }                                       
                                    }
                                }]
                            },                 
                            lazyRender: true,
                            displayField: 'value',
                            valueField: 'id',
                            queryMode: 'remote',
                            remoteFilter: false,
                            listClass: 'x-combo-list-small',
                            forceSelection: true,
                            allowBlank: false
                        },     
                    ]
                },   
                {

                    items:[
                        {
                            xtype : 'displayfield',
                            width : '99%',
                            padding: '0 1 0 1',
                            value: "<h5 style=' width:100%;line-height: normal;overflow: inherit; margin:0px 0 30px; font-size: 16px;color:#757575;'><span style='background:#fff;position: relative;'>Note: The SLA for Redemption special delivery is 5 days upon request. </span></h5>",
                            //style: "content: 'OR';display: inline-block;padding: 3px 5px 3px 0;padding-top: 3px;padding-right: 5px;padding-bottom: 3px;padding-left: 0px;background-color: #ffffff;font-size: 12px;font-weight: bold;text-transform: uppercase;color: #BBC3CE;letter-spacing: 1px; position: absolute;top: -0.6em;left: 0;",
                            
                        },
                    ]
                },	             		
			],						
        });       
        if (this.canScheduleDate(selectedRecords)) {
            //var type=selectedRecords[0].get('type');            
            var schedulewindowforappointment = new Ext.Window({
                title: 'Schedule delivery on Appointment..',
                layout: 'fit',
                width: 600,
                maxHeight: 700,
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
                                params: { hdl: 'redemption', action: 'doLogistics',
                                          redemptionid: redemptionid,
                                },
                                waitMsg: 'Processing',
                                success: function(frm, action) { //success                                   
                                    Ext.MessageBox.show({
                                        title: 'Logistics creation',
                                        msg: 'Sent Successfully',
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

            var schedulewindowfordelivery = new Ext.Window({
                title: 'Schedule delivery on Delivery type..',
                layout: 'fit',
                width: 600,
                maxHeight: 700,
                modal: true,
                plain: true,
                buttonAlign: 'center',
                buttons: [{
                    text: 'Submit',
                    handler: function(btn) {
                        if (deliverypanel.getForm().isValid()) {
                            btn.disable();
                            deliverypanel.getForm().submit({
                                submitEmptyText: false,
                                url: 'gtp.php',
                                method: 'POST',
                                params: { hdl: 'redemption', action: 'doLogistics',
                                          redemptionid: redemptionid,
                                },
                                waitMsg: 'Processing',
                                success: function(frm, action) { //success
                                    Ext.MessageBox.show({
                                        title: 'Logistics creation',
                                        msg: 'Sent Successfully',
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
                                        errmsg = 'Server error';
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
                        owningWindow.close();
                    }
                }],
                closeAction: 'destroy',
                items: deliverypanel
            });
            if(type=='Appointment'){
                schedulewindowforappointment.show();
            }else if(type=='Delivery' || type=='SpecialDelivery'){
                schedulewindowfordelivery.show();
            }
           
           
         }else{
            Ext.MessageBox.show({
                title: 'Warning', buttons: Ext.MessageBox.OK, icon: Ext.MessageBox.WARNING,
                msg: 'Please select Confirmed request.'});
         }        
    },

    canScheduleDate : function(selectedRecords) {
        if(!selectedRecords[0].get('type')){
            status =  selectedRecords[0].get('rdmstatus');
        }else {
            status =  selectedRecords[0].get('status');
        }

        return selectedRecords.length == 1 && status == 1;
    },


    onPreLoadForm: function (formView, form, record, asyncLoadCallback) {
      
    },

    getPrintReport: function(btn){

        // grid header data
        header = [];

        const reportingFields = [
            ['Item Code', ['code', 0]],
            ['Serial Number', ['serialnumber', 0]],
            // End custom
        ];
        //{ key1 : [val1, val2, val3] } 
        
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
                /* Custom for Total items only
                * If column = total items(totalquantity), add item and serial no
                */
                if ("totalquantity" == column.dataIndex){
                    // add json serial number and item code in
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

        url = '?hdl=redemption&action=exportExcel&header='+header+'&daterange='+daterange;
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

});
