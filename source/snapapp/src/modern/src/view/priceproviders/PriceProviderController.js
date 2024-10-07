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
    /*Mobile*/
    addProvider:function(){
        this.onAddOrEdit('add')
    },
    editProvider:function(){
        this.onAddOrEdit('edit')
    },
    onAddOrEdit:function(btn){   
        var action='add';
        var headername='Add';
        if(btn=='edit'){
            action='edit';
            headername='Edit';
            var myView = this.getView();
            var grid = myView.down('#priceprovidergrid');
            var selectedRecords = grid.getSelection();
           
            if(selectedRecords==null){
                Ext.Msg.alert('Alert','Select a record first');
                return;
            }
           /*  var selecteddata=selectedRecords.data;
            Ext.getCmp('add_priceadjuster_form').setValues(selecteddata);
            console.log(selecteddata); */
            /* snap.getApplication().sendRequest({
                hdl: 'priceadprovider', action: 'list',  id: ((record && record.data.id) ? record.data.id : 0)
            }, 'Fetching data from server....').then(
            //Received data from server already
            function(data){
                console.log(data);
                if(data.success){
                   // Ext.getCmp('add_priceadjuster_form').setValues(data.data);
                }
            }); */
        }
        
        //return false;
        if (Ext.getCmp('formwindow') != null){
            Ext.getCmp('formwindow').destroy();
        }
        var addPriceProviderPanel = new Ext.form.Panel({
            frame: true,
            layout: 'vbox',
            id: 'add_priceprovider_form',
            reference: 'add_priceprovider_form',
            name: 'add_priceprovider_form',
            formBind: true,
            defaults: {
                errorTarget: 'under'
            },
            items: [
                { xtype: 'hiddenfield', hidden: true, name: 'id' },
                {
                    xtype: 'label',
                    html: 'Price Source'
                }, 
                {
                    xtype: 'combobox', fieldLabel: 'Price Source',
                    store: {
                        autoLoad: true,
                        type: 'PriceSourceProviders',
                        sorters: 'value'
                    },
                    queryMode: 'local',
                    remoteFilter: false,
                    name: 'pricesourceid',
                    valueField: 'id',
                    displayField: 'value',
                    forceSelection: true, editable: false,
                    renderer: function (value, metaData, record, rowIndex, colIndex, store, view) {
                        var productitems =Ext.getStore('pricesourceproviders').load();
                        console.log(productitems);
                        var catRecord = productitems.findRecord('id', value);
                        return catRecord ? catRecord.get('value') : '';
                    },
                }, 
                {
                    xtype: 'label',
                    html: 'White List IP'
                },   
                { xtype: 'textfield', fieldLabel: 'White List IP', name: 'whitelistip', width: '100%' },
                {
                    xtype: 'label',
                    html: 'URL'
                }, 
                { xtype: 'textfield', fieldLabel: 'URL', name: 'url', width: '100%' },
                {
                    xtype: 'label',
                    html: 'Connect Info'
                }, 
                { xtype: 'textfield', fieldLabel: 'Connect Info', name: 'connectinfo', width: '100%' },   
                {
                    xtype: 'label',
                    html: 'Code'
                },        
                { xtype: 'textfield', fieldLabel: 'Code', name: 'code', width: '100%' },
                {
                    xtype: 'label',
                    html: 'Name'
                }, 
                { xtype: 'textfield', fieldLabel: 'Name', name: 'name', width: '100%' },     
                {
                    xtype: 'label',
                    html: 'Product Category'
                }, 
                {
                    xtype: 'combobox', fieldLabel: 'Product Category', 
                    store: {
                        autoLoad: true,
                        type: 'ProductCategories',
                        sorters: 'value'
                    },
                     queryMode: 'local', remoteFilter: false,
                    name: 'productcategoryid', valueField: 'id', displayField: 'value',
                    forceSelection: true, editable: false,
                    renderer: function (value, metaData, record, rowIndex, colIndex, store, view) {     
                        var productitems =Ext.getStore('productcategories').load();                                                                              
                        var catRecord = productitems.findRecord('id', value);                                       
                        return catRecord ? catRecord.get('value') : ''; 
                    }, 
                }, 
                {
                    xtype: 'label',
                    html: 'Pullmode'
                }, 
                { xtype: 'textfield', fieldLabel: 'Pullmode', name: 'pullmode', width: '100%' },
                {
                    xtype: 'label',
                    html: 'Currency'
                }, 
                {
                    xtype: 'combobox', fieldLabel: 'Currency',
                    store: {
                        autoLoad: true,
                        type: 'CurrencyProviders',
                        sorters: 'value'
                    },
                    queryMode: 'local',
                    remoteFilter: false,
                    name: 'currencyid',
                    valueField: 'id',
                    displayField: 'value',
                    forceSelection: true, editable: false,
                    renderer: function (value, metaData, record, rowIndex, colIndex, store, view) {
                        var productitems =Ext.getStore('currencyproviders').load();
                        console.log(productitems);
                        var catRecord = productitems.findRecord('id', value);
                        return catRecord ? catRecord.get('value') : '';
                    },
                },
                {
                    xtype: 'label',
                    html: 'Lapse Time Allowance'
                }, 
                { xtype: 'textfield', fieldLabel: 'Lapse Time Allowance', name: 'lapsetimeallowance', width: '100%',                    
                    listeners: {
                        change: function( fld, newValue, oldValue, opts ) {                            
                            if(!/^-?[0-9]*(\.[0-9]{1,2})?$/.test(newValue)){                               
                                fld.setErrorMessage('Only positive/negative float (x.yy)/int formats allowed!');                                    
                            }else{
                                fld.setErrorMessage(null);                                    
                            }                                    
                        },             
                    }
                },
                {
                    xtype: 'label',
                    html: 'Future Order Strategy'
                }, 
                { xtype: 'textfield', fieldLabel: 'Future Order Strategy', name: 'futureorderstrategy', width: '100%' },
                {
                    xtype: 'label',
                    html: 'Future Order Params'
                }, 
                { xtype: 'textfield', fieldLabel: 'Future Order Params', name: 'futureorderparams', width: '100%' },
                { xtype: 'hiddenfield',  name: 'status', value: 1 },
            ],
        });
        var addPriceProviderWindow = new Ext.Window({
            title: '<span style="color:#ffffff">'+headername+' Price Provider</span>',
            id: 'formwindow',
            name: 'formwindow',
            reference: 'formwindow',
            layout: 'fit',
            width: '90%',
            height: '90%',
            header: {
                style: {
                    background: '#5fa2dd',
                },
            },
            modal: true,
            plain: true,
            buttonAlign: 'center',
            buttons: [
                {
                    xtype: 'toolbar',
                    flex: 1,
                    dock: 'bottom',
                    ui: 'footer',
                    layout: {
                        pack: 'end',
                        type: 'hbox'
                    },
                    items: [
                        {
                            text: 'Save',
                            handler: function (btn) {                                           
                                var model = Ext.create('snap.view.priceprovider.FormModel', Ext.getCmp('add_priceprovider_form').getValues());
                                if (model.isValid()) {
                                    Ext.getCmp('add_priceprovider_form').submit({
                                        submitEmptyText: false,
                                        url: 'index.php?hdl=priceprovider&action='+action,
                                        method: 'POST',					
                                        waitMsg: 'Processing',
                                        success: function (form, action) { //success							
                                            //Ext.Msg.alert('Success', 'Added Successfully !', Ext.emptyFn);
                                            Ext.getCmp('formwindow').close();
                                            Ext.getCmp('priceprovidergrid').getStore().reload();                                           
                                        },
                                        failure: function (form, action) {	
                                            Ext.Msg.alert('Error', action.errorMessage, Ext.emptyFn);
                                        }
                                    }); 
                                } else {
                                    var errors = model.getValidation().getData();
                                    console.log(errors);
                                    Object.keys(errors).forEach(function (f) {
                                        var field = Ext.getCmp('add_priceprovider_form').getFields(f);
                                        if (field && errors[f] !== true) {
                                            console.error(f + ' => ' + errors[f]);
                                            field.markInvalid(errors[f]); 
                                        }
                                    });
                                }                                 
                            },
                        },
                        {
                            text: 'Close',
                            handler: function (btn) {
                                owningWindow = btn.up('window');
                                //owningWindow.closeAction='destroy';
                                owningWindow.close();
                            }
                        }
                    ]
                }
            ],
            closeAction: 'destroy',
            items: [
                addPriceProviderPanel
            ]
        });
        if(btn=='edit'){
            var myView = this.getView();
            var grid = myView.down('#priceprovidergrid');
            var selectedRecords = grid.getSelection(); 
            Ext.getCmp('add_priceprovider_form').setValues(selectedRecords.data);
            /* snap.getApplication().sendRequest({
                hdl: 'priceprovider', action: 'list',  id: ((selectedRecords.data && selectedRecords.data.id) ? selectedRecords.data.id : 0)
            }, 'Fetching data from server....').then(
            //Received data from server already
            function(data){
                console.log(data);
                if(data.success){
                   Ext.getCmp('add_priceprovider_form').setValues(data.data);
                }
            }); */
        }

        addPriceProviderWindow.show();
    },
    /*Mobile*/

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
            sm = myView.down('#priceprovidergrid'),
            selectedRecords = sm.getSelection(),
            priceproviderid = selectedRecords.data.id,
            name = selectedRecords.data.name;

        Ext.Msg.confirm('Start Price Collector', 'Do you want to start Price Collection for '+name+'?', function(id) {
            if (id == 'yes') {
                snap.getApplication().sendRequest({
                    hdl: 'priceprovider', 'action': 'startPriceProvider', id: priceproviderid, status: 1
                }, 'Processing....').then(
                function(data){
                    if(data.success){
                        if(data.isrunning){
                            Ext.Msg.alert('Alert','Price Collector is already running' );
                            sm.getStore().reload();    
                        } else {
                            Ext.Msg.alert('Alert','Price Collector is now running' );                           
                            sm.getStore().reload();   
                        }
                        //Ext.getCmp('priceprovidergrid').getStore().reload();    
                    }
                });
            }
        }, this);

        return false;
    },

    stopPriceProvider: function(record, column) {       

            var myView = this.getView(),
            sm = myView.down('#priceprovidergrid'),
            selectedRecords = sm.getSelection(),
            priceproviderid = selectedRecords.data.id,
            name = selectedRecords.data.name;


        Ext.Msg.confirm('Stop Price Collector',  'Do you want to stop Price Collection for '+name+'?', function(id) {
            if (id == 'yes') {
                snap.getApplication().sendRequest({
                    hdl: 'priceprovider', 'action': 'stopPriceProvider', id: priceproviderid, status: 1
                }, 'Processing....').then(
                function(data){
                    if(data.success){
                        if(data.isstopped){
                            Ext.Msg.alert('Alert','Price Collector is stopped' );    
                            sm.getStore().reload();   
                        } else {
                            Ext.Msg.alert('Alert','Price Collector is not running' );                            
                            sm.getStore().reload();
                        }
                        //sm.getStore().reload();
                    }
                });
            }
        }, this);

        return false;
    },

});
