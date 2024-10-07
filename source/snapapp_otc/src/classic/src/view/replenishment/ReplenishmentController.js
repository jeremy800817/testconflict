Ext.define('snap.view.replenishment.ReplenishmentController', {
    extend: 'snap.view.gridpanel.BaseController',
    alias: 'controller.replenishment-replenishment',
    listeners: {
        render: function(store) {
            store.on('load', function(records) {               
            });
        }
    },
    onPreLoadViewDetail: function(record, displayCallback) {
        snap.getApplication().sendRequest({ hdl: 'replenishment', action: 'detailview', id: record.data.id})
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
        //var type=2;
        var type=selectedRecords[0].get('type'); 
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
			],						
        });       
        //if (selectedRecords.length == 1 && selectedRecords[0].get('status')==1) {
            var type=selectedRecords[0].get('type');            
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
                                params: { hdl: 'buyback', action: 'doLogistics', 
                                        partnerid: selectedRecords[0].data.partnerid,
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
                                params: { hdl: 'buyback', action: 'doLogistics',
                                        partnerid: selectedRecords[0].data.partnerid, 
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
           
           /*
         }else{
            Ext.MessageBox.show({
                title: 'Warning', buttons: Ext.MessageBox.OK, icon: Ext.MessageBox.WARNING,
                msg: 'Please select Confirmed request.'});
         }        */
    },
    onPreLoadForm: function (formView, form, record, asyncLoadCallback) {
      
    },

    addLogistic: function (){
        win = new snap.view.replenishment.ReplenishmentGridForm;
        win.show();
    }
});
