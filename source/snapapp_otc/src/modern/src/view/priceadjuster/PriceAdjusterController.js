Ext.define('snap.view.priceadjuster.PriceAdjusterController', {
    extend: 'snap.view.gridpanel.BaseController',
    alias: 'controller.priceadjuster-priceadjuster',


    config: {
    },

    onPostLoadEmptyForm: function (formView, form) {
        snap.getApplication().sendRequest({
            hdl: 'priceadjuster', 'action': 'prefillform',
        }, 'Fetching data from server....').then(
            //Received data from server already
            function (data) {
                if (data.success) {

                    formView.getController().lookupReference('pricecombo').getStore().loadData(data.priceproviders);

                }
            });
    },


    // onPreAddEditSubmit: function(formAction, theGridFormPanel, theGridForm, btn) {
    //     _this = this;
    //     Ext.MessageBox.confirm('Confirm', 'This will change your current data.', function(id) {
    //         console.log(id,theGridFormPanel);
    //         if (id == 'yes') {
    //             btn = theGridFormPanel.down('button');
    //             _this._onSaveGridForm(btn)
    //         }else{
    //             return false;
    //         }
    //     })
    // },

    /*for mobile*/
    onAdd: function () {
        /* var myView = this.getView();
        var grid = myView.down('#priceadjustergrid');
        var selectedRecords = grid.getSelection();
        console.log(selectedRecords); */
        
        var addPriceAdjusterPanel = new Ext.form.Panel({
            frame: true,
            layout: 'vbox',
            id: 'add_priceadjuster_form',
            reference: 'add_priceadjuster_form',
            name: 'add_priceadjuster_form',
            formBind: true,
            defaults: {
                errorTarget: 'under'
            },
            items: [
                {
                    xtype: 'label',
                    html: 'Price Provider'
                },
                {
                    xtype: 'combobox',
                    fieldLabel: 'Price Provider',
                    forceSelection: true,
                    enforceMaxLength: true,                    
                    name: 'priceproviderid',
                    id: 'priceproviderid',
                    store: [{
                        id: '-',
                        name: 'Select Price Provider',
                    }],
                    autoLoad: true,
                    queryMode: 'local',
                    editable: false,
                    // disableKeyFilter: false,
                    valueField: 'id',
                    displayField: 'name',
                    reference: 'pricecombo',
                    // handler: 'onclickcompany_handler',
                    allowBlank: false,
                    listeners: {
                        click:function(){
                            alert("clicked");
                        },
                        select: function(object,record){
                            snap.getApplication().sendRequest({
                                hdl: 'priceadjuster', action: 'getLatestData',  id: ((record && record.data.id) ? record.data.id : 0)
                            }, 'Fetching data from server....').then(
                            //Received data from server already
                            function(data){
                                if(data.success){
                                    Ext.getCmp('add_priceadjuster_form').setValues(data.data);
                                }
                            });
                        },
                        beforeactivate: function () {
                            Ext.MessageBox.confirm('Confirm', 'This will change your current data.', function (id) {
                                if (id == 'yes') {

                                }
                            })
                        },
                    }
                },
                {
                    xtype: 'label',
                    html: 'Fx Buy Premium'
                },
                {
                    xtype: 'numberfield',
                    fieldLabel: 'Fx Buy Premium',
                    name: 'fxbuypremium',
                    reference: 'fxbuypremium',
                    forceSelection: true,
                    enforceMaxLength: true,
                    step: 0.1,
                    minValue: 0,
                    allowBlank: false,
                    fieldStyle: "font-size: 1.1rem"
                },
                {
                    xtype: 'label',
                    html: 'Fx Sell Premium'
                }, {
                    xtype: 'numberfield',
                    fieldLabel: 'Fx Sell Premium',
                    name: 'fxsellpremium',
                    reference: 'fxsellpremium',
                    forceSelection: true,
                    enforceMaxLength: true,
                    step: 0.1,
                    minValue: 0,
                    allowBlank: false,
                    fieldStyle: "font-size: 1.1rem"
                },
                {
                    xtype: 'label',
                    html: 'Buy Margin'
                },
                {
                    xtype: 'numberfield',
                    fieldLabel: 'Buy Margin',
                    name: 'buymargin',
                    reference: 'buymargin',
                    forceSelection: true,
                    enforceMaxLength: true,
                    step: 0.1,
                    minValue: 0,
                    allowBlank: false,
                    fieldStyle: "font-size: 1.1rem"
                },
                {
                    xtype: 'label',
                    html: 'Sell Margin'
                }, {
                    xtype: 'numberfield',
                    fieldLabel: 'Sell Margin',
                    name: 'sellmargin',
                    reference: 'sellmargin',
                    forceSelection: true,
                    enforceMaxLength: true,
                    step: 0.1,
                    minValue: 0,
                    allowBlank: false,
                    fieldStyle: "font-size: 1.1rem"
                },
                {
                    xtype: 'label',
                    html: 'Refine Fee'
                },
                {
                    xtype: 'numberfield',
                    fieldLabel: 'Refine Fee',
                    name: 'refinefee',
                    reference: 'refinefee',
                    forceSelection: true,
                    enforceMaxLength: true,
                    step: 0.1,
                    minValue: 0,
                    allowBlank: false,
                    fieldStyle: "font-size: 1.1rem"
                },
                {
                    xtype: 'label',
                    html: 'Supplier Premium'
                },{
                    xtype: 'numberfield',
                    fieldLabel: 'Supplier Premium',
                    name: 'supplierpremium',
                    reference: 'supplierpremium',
                    forceSelection: true,
                    enforceMaxLength: true,
                    step: 0.1,
                    minValue: 0,
                    allowBlank: false,
                    fieldStyle: "font-size: 1.1rem"
                },
                {
                    xtype: 'label',
                    html: 'Sell Spread'
                },
                {
                    xtype: 'numberfield',
                    fieldLabel: 'Sell Spread',
                    name: 'sellspread',
                    reference: 'sellspread',                   
                    forceSelection: true,
                    enforceMaxLength: true,
                    step: 0.1,
                    // minValue: 0,
                    allowBlank: false,
                    fieldStyle: "font-size: 1.1rem"
                },
                {
                    xtype: 'label',
                    html: 'Buy Spread'
                },{
                    xtype: 'numberfield',
                    fieldLabel: 'Buy Spread',
                    name: 'buyspread',
                    reference: 'buyspread',                   
                    forceSelection: true,
                    enforceMaxLength: true,
                    step: 0.1,
                    // minValue: 0,
                    allowBlank: false,
                    fieldStyle: "font-size: 1.1rem"
                }
            ],
        });
        var addPriceAdjusterWindow = new Ext.Window({
            title: '<span style="color:#ffffff">Add Price Adjuster</span>',
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
                                var model = Ext.create('snap.view.priceadjuster.FormModel', Ext.getCmp('add_priceadjuster_form').getValues());
                                if (model.isValid()) {
                                    Ext.getCmp('add_priceadjuster_form').submit({
                                        submitEmptyText: false,
                                        url: 'index.php?hdl=priceadjuster&action=add',
                                        method: 'POST',					
                                        waitMsg: 'Processing',
                                        success: function (form, action) { //success							
                                            //Ext.Msg.alert('Success', 'Added Successfully !', Ext.emptyFn);
                                            Ext.getCmp('formwindow').close();
                                            Ext.getCmp('priceadjustergrid').getStore().reload();                                           
                                        },
                                        failure: function (form, action) {	
                                            Ext.Msg.alert('Error', action.errorMessage, Ext.emptyFn);
                                        }
                                    });
                                } else {
                                    var errors = model.getValidation().getData();
                                    console.log(errors);
                                    Object.keys(errors).forEach(function (f) {
                                        var field = Ext.getCmp('add_priceadjuster_form').getFields(f);
                                        if (field && errors[f] !== true) {
                                            console.error(f + ' => ' + errors[f]);
                                            field.markInvalid(errors[f]); // only work in 6.6
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
                addPriceAdjusterPanel
            ]
        });

        snap.getApplication().sendRequest({
            hdl: 'priceadjuster', 'action': 'prefillform',
        }, 'Fetching data from server....').then(
        //Received data from server already
        function (data) {
            if (data.success) {
                Ext.getCmp('priceproviderid').getStore().loadData(data.priceproviders);
            }
        });
        addPriceAdjusterWindow.show();

    },
    onclickprice: function(record) {
        alert();
       /*  x = record.value;
        console.log(record,x,'record.value;');


        // return;
        
        snap.getApplication().sendRequest({
            hdl: 'priceadjuster', action: 'getLatestData',  id: ((record && record.value) ? record.value : 0)
        }, 'Fetching data from server....').then(
        //Received data from server already
        function(data){
            if(data.success){
                //alert("aaa");
                console.log(data.data)
                //record.up('form').getForm().setValues(data.data)
            }
        }); */
    },

});
