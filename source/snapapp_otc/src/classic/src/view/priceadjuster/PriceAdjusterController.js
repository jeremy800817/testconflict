Ext.define('snap.view.priceadjuster.PriceAdjusterController', {
    extend: 'snap.view.gridpanel.BaseController',
    alias: 'controller.priceadjuster-priceadjuster',
    config: {
    },

    onPostLoadEmptyForm: function( formView, form) {
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

    addsingleadjuster: function(button){
        // console.log(button, 'Button')

        // button.provider_code;
        form = button.up('form');

        this.add_all_adjuster(form);
    },

    add_all_adjuster: function(theGridForm){
        data = theGridForm.getValues();


        var me = this,
            myView = this.getView(),
            addEditForm = theGridForm;
        if (addEditForm.isValid()) {
            // btn.disable();
            addEditForm.submit({
                submitEmptyText: false,
                url: 'index.php',
                method: 'POST',
                params: { hdl: 'priceadjuster', action: 'addallquickadjuster' },
                waitMsg: 'Processing',
                success: function(frm, action){ //success

                    Ext.MessageBox.show({
                        title: 'Success',
                        msg: "Add Price Adjusters successful.",
                        buttons: Ext.MessageBox.OK,
                        icon: Ext.MessageBox.INFO
                    });
                    
                    me.gridFormView.close();
                    me.gridFormView = undefined;
                    myView.getStore().reload();
                }, 
                failure: function(frm,action) { //failed
                    // btn.enable();
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
                        errmsg = 'Unknown Error: ' + action.response.responseText;
                    }
                    if(action.result.field) {
                        var nameField = addEditForm.findField(action.result.field);
                        if(nameField) {
                            nameField.markInvalid(errmsg);
                            return;
                        }
                    }
                    Ext.MessageBox.show({
                        title: 'Error Message',
                        msg: errmsg,
                        buttons: Ext.MessageBox.OK,
                        icon: Ext.MessageBox.ERROR
                    });
                }
            });
        } else {
            Ext.MessageBox.show({
                title: 'Error Message',
                msg: 'Error in the Form',
                buttons: Ext.MessageBox.OK,
                icon: Ext.MessageBox.ERROR
            });
        }
    },

    onPreAddEditSubmit: function(formAction, theGridFormPanel, theGridForm, btn) {
        _this = this;
        var me = this,
        myView = this.getView(),
        addEditForm = theGridForm;
        msg = '';
        if (theGridFormPanel.$className == 'snap.view.priceadjuster.PriceAdjusterQuick'){
            this.add_all_adjuster(theGridForm);
            return;
        }

        temp_datetime = '1979-01-01 ' + theGridForm.getFieldValues().effectiveon;
        compare_time = new Date(temp_datetime).toLocaleTimeString('en-US', { hour12: false, 
            hour: "numeric", 
            minute: "numeric"});
        current_time = new Date().toLocaleTimeString('en-US', { hour12: false, 
            hour: "numeric", 
            minute: "numeric"});

        if (current_time >= compare_time ){
            msg = '<br><br>Price update shall commence <b>immediately</b>';
        }else{
            displayTime = new Date(temp_datetime).toLocaleTimeString('en-US');
            msg = '<br><br>Price update shall commence at the start of next tier <b>('+ displayTime +')</b>';
        }
        

        Ext.MessageBox.confirm('Confirm', 'This will change your current data.' + msg, function(id) {
            if (id == 'yes') {
                theGridForm.submit({
                    submitEmptyText: false,
                    url: 'index.php',
                    method: 'POST',
                    params: { hdl: 'priceadjuster', action: 'add' },
                    waitMsg: 'Processing',
                    success: function(frm, action){ //success
    
                        Ext.MessageBox.show({
                            title: 'Success',
                            msg: "Add Price Adjusters successful.",
                            buttons: Ext.MessageBox.OK,
                            icon: Ext.MessageBox.INFO
                        });
                        
                        me.gridFormView.close();
                        me.gridFormView = undefined;
                        myView.getStore().reload();
                    }, 
                    failure: function(frm,action) { //failed
                        // btn.enable();
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
                            errmsg = 'Unknown Error: ' + action.response.responseText;
                        }
                        if(action.result.field) {
                            var nameField = addEditForm.findField(action.result.field);
                            if(nameField) {
                                nameField.markInvalid(errmsg);
                                return;
                            }
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
                return false;
            }
        })
    },

    quickAdjuster_: function(){
        Ext.create('snap.view.priceadjuster.PriceAdjusterQuick').show();
    },

    quickAdjuster__: function(btn, formAction){
        var me = this, selectedRecord,
            myView = this.getView();
        var sm = myView.getSelectionModel();
        var selectedRecords = sm.getSelection();
        var gridFormView = Ext.create('snap.view.priceadjuster.PriceAdjusterQuick', Ext.apply(myView.formConfig ? myView.formConfig : {}, {
            formDialogButtons: [{ 
                text: 'Save',
                handler: function(btn) {
                    me._onSaveGridForm(btn);
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
        this._formAction = formAction;
        var addEditForm = this.gridFormView.down('form').getForm();
        gridFormView.title = 'Add ' + gridFormView.title + '...';
        if(Ext.isFunction(me['onPostLoadEmptyFormQuick'])) this.onPostLoadEmptyFormQuick( gridFormView, addEditForm);
        this.gridFormView.show();
    },

    onPostLoadEmptyFormQuick: function( formView, form) {
        var me = this;
        var panel = formView.lookupReference('notesDisplays');
        var quickadjusterDisplayContainer = formView.lookupReference('quickadjusterDisplayContainer');
        // console.log(formView, 'ME');return;
        snap.getApplication().sendRequest({
            hdl: 'priceadjuster', 'action': 'prefillformquick',
            partnercode: PROJECTBASE,
        }, 'Fetching data from server....').then(
            //Received data from server already
            function (data) {
                
                console.log(panel);
                if (data.success) {
                    if (data.success && data.priceadjusters) {
                        quickadjusterDisplayContainer.setHtml("");
                        // quickadjusterDisplayContainer.removeAll();
                        // console.log(data.priceadjusters);return;
                        //var panel = Ext.getCmp('notesDisplays');
                        // var panel2 = formView.lookupReference('notesDisplays2');
                        // panel.removeAll();
                        // pane2.removeAll();
                        data.priceadjusters.map((datax) => {
                            // datax = {
                            //     tier1,
                            //     tier2,
                            //     channel_code,
                            // }
                            panel.add(me.noteTemplate(datax))
                        })
                        // data.priceadjusters.tier2.map((x) => {
                        //     panel2.add(me.noteTemplate(x))
                        // })
                        me.init_live_stream(data.priceadjusters);
                        return;
                    }
                }
            });
    },

    noteTemplate: function (data) {

        tier1effectiveon = data.tier1.effectiveon.date;
        var tier1effectiveon = new Date(tier1effectiveon);
        // tier1effectiveon = d.format('H:i');
        
        tier1effectiveendon = data.tier1.effectiveendon.date;
        var tier1effectiveendon = new Date(tier1effectiveendon);
        // tier1effectiveendon = e.format('H:i');
        // To normalize time to 0 sec to display in front, 
        // 59 sec will be appended in backend
        tier1effectiveendon.setSeconds(0);

        tier2effectiveon = data.tier2.effectiveon.date;
        var tier2effectiveon = new Date(tier2effectiveon);
        // tier1effectiveon = d.format('H:i');
        
        tier2effectiveendon = data.tier2.effectiveendon.date;
        var tier2effectiveendon = new Date(tier2effectiveendon);
        // tier1effectiveendon = e.format('H:i');
        tier2effectiveendon.setSeconds(0);

        let peakbuy = []; // t1
        let peaksell = []; // t1
        let nonpeakbuy = [];
        let nonpeaksell = [];

        let ogpeakbuy = [];
        let ogpeaksell = [];
        let ognonpeakbuy = [];
        let ognonpeaksell = [];

        let ogpeakbuyinit = [];
        let ogpeaksellinit = [];
        let ognonpeakbuyinit = [];
        let ognonpeaksellinit = [];
        

        var returnx = {
            xtype: 'container',
            height: 300,
            //fieldStyle: 'background-color: #000000; background-image: none;',
            scrollable: true,
            flex: 1,
            layout: {
                type: 'hbox',
                align: 'stretch'
            },
            margin: '0 0 20 0',
            items: [{
                xtype: 'container',
                flex: 2,
                // flex: 3,
                layout: {
                    type: 'hbox',
                    // align: 'stretch'
                },
                items:[
                    {
                        xtype: 'form',
                        background: 'transparent',
                        layout: {
                            type: 'vbox',
                            // align: 'stretch'
                        },
                        // style: 'background: transparent!important;',
                        bodyStyle:{"background-color":"transparent"}, 
                        items: [{
                            xtype: "container",
                            layout: {
                                type: "hbox",
                                // align: "stretch",
                            },
                            reference: 'peak-container',
                            items: [{
                                xtype: 'label',
                                text: data.channel_code,
                                lineHeight: '16px',
                                labelStyle: 'font-weight: bold;font-size: 1.1rem;margin-bottom: 5px;',
                                style: 'font-weight: bold;font-size: 1.1rem;margin-bottom: 5px;',
                            },
                            {
                                xtype: 'checkboxfield',
                                name: 'provider'+'['+data.provider_id+']'+'['+data.tier1.id+'][usepercent]',
                                label: 'usepercent',
                                // value: 'usepercent',
                                margin: '0 0 0 5',
                                // checked: true,
                                listeners: {
                                    afterrender: function(value) {
                                     
                                        // Temporary Hide
                                        // Check if use percentage
                                        if(data.tier1.usepercent == 1){
                                            // Do something if its toggled
                                            this.up().up().items.items[0].items.items[1].setValue(true);
                                            this.up().up().items.items[0].items.items[2].setValue(true);
                                        }else{
    
                                        }
                                    },
                                    // Do Copy
                                    change: function(checkbox, newValue, oldValue, eOpts) {
                                        // Initialize pointer
                                        // nonPeakContainer = checkbox.up().lookupReferenceHolder('nonpeak-container').view.items.items[0].items.items[0].items.items[0].items.items;
                                        
                                        // Do checking before initializing
                                        // 1) If non peak is checked, uncheck 
                                        // 2) reset nonpeak to default
                                        // 3) Change and update tick for peak
                                        //altCheckBox = checkbox.up().up().items.items[5].items.items[2];
  
                                        // Initialize and check 
                                        if (newValue) {
                                       
                                            // Show panel
                                            // Set Height
                                            this.up().up().up().up().setHeight(420);

                                            // Toggle box
                                            this.up().up().items.items[7].setHidden(false);
                                            this.up().up().items.items[8].setHidden(false);
                                            this.up().up().items.items[9].setHidden(false);
                                            this.up().up().items.items[10].setHidden(false);

                                            // Set Allow Blank false 
                                            this.up().up().items.items[8].items.items[0].allowBlank = false;
                                            this.up().up().items.items[8].items.items[1].allowBlank = false;
                                            this.up().up().items.items[10].items.items[0].allowBlank = false;
                                            this.up().up().items.items[10].items.items[0].allowBlank = false;
                                            //buysellpercentage-container;

                                            // set hidden tier 2 container based on setting here
                                            this.up().up().items.items[0].items.items[2].setValue(true);
                                        }else{
                                            
                                            // Reset Height
                                            this.up().up().up().up().setHeight(300);

                                            // Hide panel
                                            this.up().up().items.items[7].setHidden(true);
                                            this.up().up().items.items[8].setHidden(true);
                                            this.up().up().items.items[9].setHidden(true);
                                            this.up().up().items.items[10].setHidden(true);
                                            
                                            // Set Allow Blank false 
                                            this.up().up().items.items[8].items.items[0].allowBlank = true;
                                            this.up().up().items.items[8].items.items[1].allowBlank = true;
                                            this.up().up().items.items[10].items.items[0].allowBlank = true;
                                            this.up().up().items.items[10].items.items[0].allowBlank = true;

                                            // Unset Hidden Tier 2 Content
                                            this.up().up().items.items[0].items.items[2].setValue(false);
                                        }

                                    }
                                }
                            },   
                            {
                                xtype: 'checkboxfield',
                                name: 'provider'+'['+data.provider_id+']'+'['+data.tier2.id+'][usepercent]',
                                label: 'usepercent',
                                style : 'color: #F6F6F6;border-color: #F6F6F6;display: none;',
                                // value: 'usepercent',
                                margin: '0 0 0 5',
                            }, {
                                xtype: 'label',
                                // Temporary Hide 
                                text: '( Tick to enable partner spread in % )',
                                lineHeight: '8px',
                                style: 'font-weight: bold;margin-top: 5px;',
                            },]
                        },
                        {
                            xtype: 'label',
                            text: 'Peak',
                            lineHeight: '8px',
                            style: 'font-weight: bold;margin-top: 5px;',
                        },{
                            xtype: "container",
                            layout: {
                                type: "hbox",
                                // align: "stretch",
                            },
                            reference: 'peak-container',
                            items: [
                                {
                                    xtype: 'numberfield',
                                    fieldLabel: 'Buy Spread',
                                    name: 'provider'+'['+data.provider_id+']'+'['+data.tier1.id+'][buyspread]',
                                    reference: 'buyspread',
                                    margin: '0 0 0 5',
                                    forceSelection: true,
                                    enforceMaxLength: true,
                                    step: 0.1,
                                    // minValue: 0,
                                    allowBlank: false,
                                    fieldStyle: "font-size: 1.1rem",
                                    value: data.tier1.buyspread,
                                },{
                                    xtype: 'numberfield',
                                    fieldLabel: 'Sell Spread',
                                    name: 'provider'+'['+data.provider_id+']'+'['+data.tier1.id+'][sellspread]',
                                    reference: 'sellspread',
                                    margin: '0 0 0 5',
                                    forceSelection: true,
                                    enforceMaxLength: true,
                                    step: 0.1,
                                    // minValue: 0,
                                    allowBlank: false,
                                    fieldStyle: "font-size: 1.1rem",
                                    value: data.tier1.sellspread,
                                },
                                {
                                    xtype: 'checkboxfield',
                                    name: 'provider'+'['+data.provider_id+']'+'['+data.tier1.id+'][usespreadcopy]',
                                    label: 'usespreadcopy',
                                    value: 'togglecopy',
                                    margin: '0 0 0 5',
                                    // checked: true,
                                    listeners: {
                                        afterrender: function(value) {

                                            // Temporary Hide
                                            // Check if use percentage
                           
                                            if(data.tier1.usespreadcopy == 1){
                                                // Do something if its toggled
                                                this.up().up().items.items[2].items.items[2].setValue(true);
                                            }
                                            // this.up().up().items.items[2].items.items[4].setValue(10000);
                                            // this.up().up().items.items[2].items.items[5].setValue(10000);
                                        },
                                        // Do Copy
                                        change: function(checkbox, newValue, oldValue, eOpts) {
                                            // Initialize pointer
                                            // nonPeakContainer = checkbox.up().lookupReferenceHolder('nonpeak-container').view.items.items[0].items.items[0].items.items[0].items.items;
                                            
                                            // Do checking before initializing
                                            // 1) If non peak is checked, uncheck 
                                            // 2) reset nonpeak to default
                                            // 3) Change and update tick for peak
                                            altCheckBox = checkbox.up().up().items.items[5].items.items[2];
                                            // Step 1 : If nonpeak is checked, uncheck it
                                            // debugger;
                        
                                            // if(altCheckBox.value == true){
                                               
                                            // }else{
                                            //     altCheckBox.setValue(false);
                                            // }

                                            // if(data.tier1.usespreadcopy == 1){
                                            //     ogpeakbuyinit = checkbox.up().up().items.items[2].items.items[4].value;
                                            //     ogpeaksellinit = checkbox.up().up().items.items[2].items.items[5].value;  
                                            //     ogpeakbuy = ogpeakbuyinit;
                                            //     ogpeaksell = ogpeaksellinit;
                                            // }
                                            // debugger;
                                            // Initialize
                                            // let peakbuy = []; // t1
                                            // let peaksell = []; // t1
                                            // let nonpeakbuy = [];
                                            // let nonpeaksell = [];

                                            // let ogpeakbuy = [];
                                            // let ogpeaksell = [];
                                            // let ognonpeakbuy = [];
                                            // let ognonpeaksell = [];

                                            // let ogpeakbuyinit = [];
                                            // let ogpeaksellinit = [];
                                            // Create temp storage array to store values
                                            if (newValue) {

                                                if(altCheckBox.value == true){

                                                    // Save Original Values if Updated
                                                    if(data.tier1.usespreadcopy == 1){
                                                        ogpeakbuyinit[data.provider_id] = checkbox.up().up().items.items[2].items.items[4].value;
                                                        ogpeaksellinit[data.provider_id] = checkbox.up().up().items.items[2].items.items[5].value;  

                                                    }
                            
                                                    // Revert to og value
                                                    peakbuy[data.provider_id] = ogpeakbuy[data.provider_id];
                                                    peaksell[data.provider_id] = ogpeaksell[data.provider_id];
                                                    nonpeakbuy[data.provider_id] = ognonpeakbuy[data.provider_id];
                                                    nonpeaksell[data.provider_id] = ognonpeaksell[data.provider_id];

                                                    checkbox.up().up().items.items[2].items.items[4].setValue(ogpeakbuy[data.provider_id]);
                                                    checkbox.up().up().items.items[2].items.items[5].setValue(ogpeaksell[data.provider_id]);
                                                    
                                                    // Put Set peakbuy with init values
                                                    if(data.tier1.usespreadcopy == 1){
                                                        peakbuy[data.provider_id] = ogpeakbuyinit[data.provider_id];
                                                        peaksell[data.provider_id] = ogpeaksellinit[data.provider_id];
                                                    }

                                                    altCheckBox.setValue(false);
                                                }else{
                                                    // Initialize value if new
        
                                                    // Do check if there are og values, if yes, use saved og value
                                                    // Check BuySpreadOriginal
                                                    // if(checkbox.up().up().items.items[2].items.items[4].value){
                                                    //     // If there were values previously, call old values
                                                    //     ogpeakbuy = peakbuy = checkbox.up().up().items.items[2].items.items[4].value;

                                                    // }else{
                                                    //     // First time save init
                                                    //     ogpeakbuy = peakbuy = checkbox.up().up().items.items[2].items.items[0].value;
                                                    //     // Save original value if there arent any previously
                                                    //     checkbox.up().up().items.items[2].items.items[4].setValue(ogpeakbuy);
                                                   
                                                    // }

                                                    // // Check SellSpreadOriginal
                                                    // if(checkbox.up().up().items.items[2].items.items[5].value){
                                                    //     // If there were values previously, call old values
                                                    //     ogpeaksell = peaksell = checkbox.up().up().items.items[2].items.items[5].value;

                                                    // }else{
                                                    //     // First time save init
                                                    //     ogpeaksell = peaksell = checkbox.up().up().items.items[2].items.items[1].value;
                                                    //     // Save original value if there arent any previously
                                                    //     checkbox.up().up().items.items[2].items.items[5].setValue(ogpeaksell);
                                                   
                                                    // }
                     
                                                    // Save Original Values if Updated
                                                    if(data.tier1.usespreadcopy == 1){
                                                        ogpeakbuyinit[data.provider_id] = checkbox.up().up().items.items[2].items.items[4].value;
                                                        ogpeaksellinit[data.provider_id] = checkbox.up().up().items.items[2].items.items[5].value;  

                                                    }
                                         
                                                    ogpeakbuy[data.provider_id] = peakbuy[data.provider_id] = checkbox.up().up().items.items[2].items.items[0].value;
                                                    ogpeaksell[data.provider_id] = peaksell[data.provider_id] = checkbox.up().up().items.items[2].items.items[1].value;
                                                    ognonpeakbuy[data.provider_id] = nonpeakbuy[data.provider_id] = checkbox.up().up().items.items[5].items.items[0].value;
                                                    ognonpeaksell[data.provider_id] = nonpeaksell[data.provider_id] = checkbox.up().up().items.items[5].items.items[1].value;

                                                    checkbox.up().up().items.items[2].items.items[4].setValue(ogpeakbuy[data.provider_id]);
                                                    checkbox.up().up().items.items[2].items.items[5].setValue(ogpeaksell[data.provider_id]);
                                                    
                                                    // Put Set peakbuy with init values
                                                    if(data.tier1.usespreadcopy == 1){
                                                        peakbuy[data.provider_id] = ogpeakbuyinit[data.provider_id];
                                                        peaksell[data.provider_id] = ogpeaksellinit[data.provider_id];
                                                    }
                                                }
                                                
                                            }else {
                                            
                                                // Save Original Values if Updated
                                                if(data.tier1.usespreadcopy == 1){
                                                    ogpeakbuyinit[data.provider_id] = checkbox.up().up().items.items[2].items.items[4].value;
                                                    ogpeaksellinit[data.provider_id] = checkbox.up().up().items.items[2].items.items[5].value;  

                                                }
                                                // Revert to og value
                                                peakbuy[data.provider_id] = ogpeakbuy[data.provider_id];
                                                peaksell[data.provider_id] = ogpeaksell[data.provider_id];
                                                nonpeakbuy[data.provider_id] = ognonpeakbuy[data.provider_id];
                                                nonpeaksell[data.provider_id] = ognonpeaksell[data.provider_id];

                                                checkbox.up().up().items.items[2].items.items[4].setValue(ogpeakbuy[data.provider_id]);
                                                checkbox.up().up().items.items[2].items.items[5].setValue(ogpeaksell[data.provider_id]);
                                                
                                                // Put Set peakbuy with init values
                                                if(data.tier1.usespreadcopy == 1){
                                                    peakbuy[data.provider_id] = ogpeakbuyinit[data.provider_id];
                                                    peaksell[data.provider_id] = ogpeaksellinit[data.provider_id];
                                                }
                                            }

                                            if (newValue) {
                                                // If tick
                                                // Blurs peak and copies peak with non peak value
                                                checkbox.up().up().items.items[2].items.items[0].setReadOnly(true);
                                                checkbox.up().up().items.items[2].items.items[1].setReadOnly(true);

                                                // Set peak value with non peak value
                                                checkbox.up().up().items.items[2].items.items[0].setValue(nonpeakbuy[data.provider_id]);
                                                checkbox.up().up().items.items[2].items.items[1].setValue(nonpeaksell[data.provider_id]);
                                            } else {
                                                // If untick
                                                // Unblurs peak and returns peak with original peak value
                                                checkbox.up().up().items.items[2].items.items[0].setReadOnly(false);
                                                checkbox.up().up().items.items[2].items.items[1].setReadOnly(false);

                                                // Set peak value with peak value
                                                checkbox.up().up().items.items[2].items.items[0].setValue(peakbuy[data.provider_id]);
                                                checkbox.up().up().items.items[2].items.items[1].setValue(peaksell[data.provider_id]);
                                            }
                                        }
                                    }
                                },   
                                {
                                    html: '<div style="color:#404040;font-weight: bold;background-color:#F6F6F6;">?</div>',
                                    width: '100%',
                                    listeners : {
                                        render: function(p) {
                                            this.getEl().dom.title = 'Tick to set peak spread to follow non peak spread';
                                            // var theElem = p.getEl();
                                            // withoutserialnumber = 0;
                                            // var theTip = Ext.create('Ext.tip.Tip', {
                                            //     html:  '<div>Click to view all Serial Numbers with <span span style="color:#ffffff;font-weight:900;">Delivery Order Number</span>&nbsp;</div>',
                                            //     style: {
        
                                            //     },
                                            //     margin: '520 0 0 520',
                                            //     shadow: false,
                                            //     maxHeight: 400,
                                            // });
                                            
                                            // p.getEl().on('mouseover', function(){
                                            //     theTip.showAt(theElem.getX(), theElem.getY());
                                            // });
                                            
                                            // p.getEl().on('mouseleave', function(){
                                            //     theTip.hide();
                                            // });
                                        },
                                         el: {
                                            click: function() {
                                                Ext.MessageBox.show({
                                                    title: 'Info',
                                                    msg: "Tick to set peak spread to follow non peak spread",
                                                    buttons: Ext.MessageBox.OK,
                                                    icon: Ext.MessageBox.INFO
                                                });
                                            }
                                        }
                                    }
                                },
                                {
                                    xtype: 'numberfield',
                                    fieldLabel: 'Original Buy Spread',
                                    name: 'provider'+'['+data.provider_id+']'+'['+data.tier1.id+'][buyspreadoriginal]',
                                    reference: 'buyspreadoriginal',
                                    margin: '0 0 0 5',
                                    forceSelection: true,
                                    enforceMaxLength: true,
                                    step: 0.1,
                                    // minValue: 0,
                                    allowBlank: true,
                                    fieldStyle: "font-size: 1.1rem",
                                    style : 'color: #F6F6F6;border-color: #F6F6F6;display: none;',
                                    value: data.tier1.buyspreadoriginal,
                                },{
                                    xtype: 'numberfield',
                                    fieldLabel: 'Original Sell Spread',
                                    name: 'provider'+'['+data.provider_id+']'+'['+data.tier1.id+'][sellspreadoriginal]',
                                    reference: 'sellspreadoriginal',
                                    margin: '0 0 0 5',
                                    forceSelection: true,
                                    enforceMaxLength: true,
                                    step: 0.1,
                                    // minValue: 0,
                                    allowBlank: true,
                                    fieldStyle: "font-size: 1.1rem",
                                    style : 'color: #F6F6F6;border-color: #F6F6F6;display: none;',
                                    value: data.tier1.sellspreadoriginal,
                                },
                            ]
                        },{
                            xtype: "container",
                            layout: {
                                type: "hbox",
                                // align: "stretch",
                            },
                            reference: 'peakdate-container',
                            items: [
                                {
                                    xtype: 'timefield',
                                    // xtype: 'datefield',
                                    // format: 'Y-m-d H:i:s',
                                    fieldLabel: 'From Time',
                                    name: 'provider'+'['+data.provider_id+']'+'['+data.tier1.id+'][tier1effectiveon]',
                                    // name: 'tier1effectiveon',
                                    reference: 'tier1effectiveon',
                                    margin: '5 0 0 5',
                                    forceSelection: true,
                                    enforceMaxLength: true,
                                    // step: 0.1,
                                    // minValue: 0,
                                    allowBlank: false,
                                    fieldStyle: "font-size: 1.1rem",
                                    format: 'H:i',
                                    minValue: Ext.Date.parse('00:00:00 AM', 'h:i:s A'),
                                    maxValue: Ext.Date.parse('23:59:59 PM', 'h:i:s A'),
                                    increment: 1,
                                    // bind: "{selectedhours.time}".toString(),
                                    value: tier1effectiveon,
                                    // bind: function(){
                                    //     return '{selectedhours.time}'
                                    // }
                                    // readOnly: true,
                                    listeners:{
                                        select: function(tf, record, eOpts){
                                            // set date to prevent overlapping on tier 1 effective end on
                                            var newDate = new Date('01/01/1970 '+ tf.rawValue);
                                            newDate.setMinutes(newDate.getMinutes()- 1441);
                                            
                                            // Set date on t1 effective end on
                                            tf.up().up().items.items[6].items.items[1].setValue(newDate);
                                        },
                                    }
                                },
                                {
                                    xtype: 'timefield',
                                    // xtype: 'datefield',
                                    // format: 'Y-m-d H:i:s',
                                    fieldLabel: 'To Time',
                                    name: 'provider'+'['+data.provider_id+']'+'['+data.tier1.id+'][tier1effectiveendon]',
                                    // name: 'tier1effectiveendon',
                                    reference: 'tier1effectiveendon',
                                    margin: '5 0 0 5',
                                    forceSelection: true,
                                    enforceMaxLength: true,
                                    // step: 0.1,
                                    // minValue: 0,
                                    allowBlank: true,
                                    fieldStyle: "font-size: 1.1rem",
                                    format: 'H:i',
                                    minValue: Ext.Date.parse('00:00:00 AM', 'h:i:s A'),
                                    maxValue: Ext.Date.parse('23:59:59 PM', 'h:i:s A'),
                                    increment: 1,
                                    // bind: "{selectedhours.timeend}".toString(),
                                    value: tier1effectiveendon,
                                    // bind: function(){
                                    //     return '{selectedhours.time}'
                                    // }
                                    // readOnly: true,
                                    listeners:{
                                        select: function(tf, record, eOpts){
                                            // set date to prevent overlapping on tier 1 effective on
                                            var newDate = new Date('01/01/1970 '+ tf.rawValue);
                                            newDate.setMinutes(newDate.getMinutes()- 1439);
                                            
                                            // Set date on t1 effective on
                                            tf.up().up().items.items[6].items.items[0].setValue(newDate);
                                        },
                                    }
                                },
                            ]
                        },{
                            xtype: 'label',
                            text: 'Non Peak',
                            style: 'font-weight: bold;margin-top: 5px;',
                        },{
                            xtype: "container",
                            layout: {
                                type: "hbox",
                                align: "stretch",
                            },
                            reference: 'nonpeak-container',
                            items: [
                                {
                                    xtype: 'numberfield',
                                    fieldLabel: 'Buy Spread',
                                    name: 'provider'+'['+data.provider_id+']'+'['+data.tier2.id+'][buyspread]',
                                    reference: 'buyspread',
                                    margin: '0 0 0 5',
                                    forceSelection: true,
                                    enforceMaxLength: true,
                                    step: 0.1,
                                    // minValue: 0,
                                    allowBlank: false,
                                    fieldStyle: "font-size: 1.1rem",
                                    value: data.tier2.buyspread,
                                },{
                                    xtype: 'numberfield',
                                    fieldLabel: 'Sell Spread',
                                    name: 'provider'+'['+data.provider_id+']'+'['+data.tier2.id+'][sellspread]',
                                    reference: 'sellspread',
                                    margin: '0 0 0 5',
                                    forceSelection: true,
                                    enforceMaxLength: true,
                                    step: 0.1,
                                    // minValue: 0,
                                    allowBlank: false,
                                    fieldStyle: "font-size: 1.1rem",
                                    value: data.tier2.sellspread,
                                },
                                {
                                    xtype: 'checkboxfield',
                                    name: 'provider'+'['+data.provider_id+']'+'['+data.tier2.id+'][usespreadcopy]',
                                    label: 'usespreadcopy',
                                    // value: 'togglecopy',
                                    margin: '0 0 0 5',
                                    // checked: true,
                                    listeners: {
                                        afterrender: function(value) {
                                     
                                            // Temporary Hide
                                            // Check if use percentage
                                            if(data.tier2.usespreadcopy == 1){
                                                // Do something if its toggled
                                                this.up().up().items.items[5].items.items[2].setValue(true);
                                            }
                                        },
                                        change: function(checkbox, newValue, oldValue, eOpts) {
                                            // Initialize pointer
                                            // nonPeakContainer = checkbox.up().lookupReferenceHolder('nonpeak-container').view.items.items[0].items.items[0].items.items[0].items.items;
                                            // Initialize
                                            // Do checking before initializing
                                            // 1) If peak is checked, uncheck 
                                            // 2) reset peak to default
                                            // 3) Change and update tick for non peak
                                            altCheckBox = checkbox.up().up().items.items[2].items.items[2];
                                            // Step 1 : If peak is checked, uncheck it
                                            // altCheckBox.setValue(false);
                                            // if(altCheckBox.value == true){
                                            //     altCheckBox.setValue(false);
                                            // }else{
                                            //     altCheckBox.setValue(false);
                                            // }
                                            
                                            // Store value in cache

                                            if (newValue) {

                                                if(altCheckBox.value == true){
                                                    
                                                    // Save Original Values if Updated
                                                    if(data.tier2.usespreadcopy == 1){
                                                        ognonpeakbuyinit[data.provider_id] = checkbox.up().up().items.items[5].items.items[4].value;
                                                        ognonpeaksellinit[data.provider_id] = checkbox.up().up().items.items[5].items.items[5].value;  

                                                    }

                                                    // Revert to og value
                                                    peakbuy[data.provider_id] = ogpeakbuy[data.provider_id];
                                                    peaksell[data.provider_id] = ogpeaksell[data.provider_id];
                                                    nonpeakbuy[data.provider_id] = ognonpeakbuy[data.provider_id];
                                                    nonpeaksell[data.provider_id] = ognonpeaksell[data.provider_id];

                                                    checkbox.up().up().items.items[5].items.items[4].setValue(ognonpeakbuy[data.provider_id]);
                                                    checkbox.up().up().items.items[5].items.items[5].setValue(ognonpeaksell[data.provider_id]);

                                                    // Put Set peakbuy with init values
                                                    if(data.tier2.usespreadcopy == 1){
                                                        nonpeakbuy[data.provider_id] = ognonpeakbuyinit[data.provider_id];
                                                        nonpeaksell[data.provider_id] = ognonpeaksellinit[data.provider_id];
                                                    }
                                                    
                                                    altCheckBox.setValue(false);
                                                }else{

                                                    // Save Original Values if Updated
                                                    if(data.tier2.usespreadcopy == 1){
                                                        ognonpeakbuyinit[data.provider_id] = checkbox.up().up().items.items[5].items.items[4].value;
                                                        ognonpeaksellinit[data.provider_id] = checkbox.up().up().items.items[5].items.items[5].value;  

                                                    }
                                                    // Initialize value if new
                                                    ogpeakbuy[data.provider_id] = peakbuy[data.provider_id] = checkbox.up().up().items.items[2].items.items[0].value;
                                                    ogpeaksell[data.provider_id] = peaksell[data.provider_id] = checkbox.up().up().items.items[2].items.items[1].value;
                                                    ognonpeakbuy[data.provider_id] = nonpeakbuy[data.provider_id] = checkbox.up().up().items.items[5].items.items[0].value;
                                                    ognonpeaksell[data.provider_id] = nonpeaksell[data.provider_id] = checkbox.up().up().items.items[5].items.items[1].value;

                                                    checkbox.up().up().items.items[5].items.items[4].setValue(ognonpeakbuy[data.provider_id]);
                                                    checkbox.up().up().items.items[5].items.items[5].setValue(ognonpeaksell[data.provider_id]);

                                                    // Put Set peakbuy with init values
                                                    if(data.tier2.usespreadcopy == 1){
                                                        nonpeakbuy[data.provider_id] = ognonpeakbuyinit[data.provider_id];
                                                        nonpeaksell[data.provider_id] = ognonpeaksellinit[data.provider_id];
                                                    }
                                                }
                                               
                                            }else {
                                                // Save Original Values if Updated
                                                if(data.tier2.usespreadcopy == 1){
                                                    ognonpeakbuyinit[data.provider_id] = checkbox.up().up().items.items[5].items.items[4].value;
                                                    ognonpeaksellinit[data.provider_id] = checkbox.up().up().items.items[5].items.items[5].value;  

                                                }

                                                // Revert to og value
                                                peakbuy[data.provider_id] = ogpeakbuy[data.provider_id];
                                                peaksell[data.provider_id] = ogpeaksell[data.provider_id];
                                                nonpeakbuy[data.provider_id] = ognonpeakbuy[data.provider_id];
                                                nonpeaksell[data.provider_id] = ognonpeaksell[data.provider_id];

                                                checkbox.up().up().items.items[5].items.items[4].setValue(ognonpeakbuy[data.provider_id]);
                                                checkbox.up().up().items.items[5].items.items[5].setValue(ognonpeaksell[data.provider_id]);

                                                // Put Set peakbuy with init values
                                                if(data.tier2.usespreadcopy == 1){
                                                    nonpeakbuy[data.provider_id] = ognonpeakbuyinit[data.provider_id];
                                                    nonpeaksell[data.provider_id] = ognonpeaksellinit[data.provider_id];
                                                }
                                            }

                                            // Original Value Save

                                            if (newValue) {
                                                // If tick
                                                // Blurs non peak and copies nonpeak with peak value
                                                checkbox.up().up().items.items[5].items.items[0].setReadOnly(true);
                                                checkbox.up().up().items.items[5].items.items[1].setReadOnly(true);

                                                // Set Nonpeak value with peak value
                                                checkbox.up().up().items.items[5].items.items[0].setValue(peakbuy[data.provider_id]);
                                                checkbox.up().up().items.items[5].items.items[1].setValue(peaksell[data.provider_id]);
                                            } else {
                                                // If untick
                                                // Unblurs non peak and returns nonpeak with original nonpeak value
                                                checkbox.up().up().items.items[5].items.items[0].setReadOnly(false);
                                                checkbox.up().up().items.items[5].items.items[1].setReadOnly(false);

                                                // Set Nonpeak value with peak value
                                                checkbox.up().up().items.items[5].items.items[0].setValue(nonpeakbuy[data.provider_id]);
                                                checkbox.up().up().items.items[5].items.items[1].setValue(nonpeaksell[data.provider_id]);
                                            }
                                        }
                                    }
                                },   
                                {
                                    html: '<div style="color:#404040;font-weight: bold;background-color:#F6F6F6;height:35px;width:10px;">?</div>',
                                    width: '100%',
                                    listeners : {
                                        render: function(p) {
                                            this.getEl().dom.title = 'Tick to set non peak spread to follow peak spread';
                                            // var theElem = p.getEl();
                                            // withoutserialnumber = 0;
                                            // var theTip = Ext.create('Ext.tip.Tip', {
                                            //     html:  '<div>Click to view all Serial Numbers with <span span style="color:#ffffff;font-weight:900;">Delivery Order Number</span>&nbsp;</div>',
                                            //     style: {
        
                                            //     },
                                            //     margin: '520 0 0 520',
                                            //     shadow: false,
                                            //     maxHeight: 400,
                                            // });
                                            
                                            // p.getEl().on('mouseover', function(){
                                            //     theTip.showAt(theElem.getX(), theElem.getY());
                                            // });
                                            
                                            // p.getEl().on('mouseleave', function(){
                                            //     theTip.hide();
                                            // });
                                        },
                                         el: {
                                            click: function() {
                                                Ext.MessageBox.show({
                                                    title: 'Info',
                                                    msg: "Tick to set non peak spread to follow peak spread",
                                                    buttons: Ext.MessageBox.OK,
                                                    icon: Ext.MessageBox.INFO
                                                });
                                            }
                                        }
                                    }
                                },
                                {
                                    xtype: 'numberfield',
                                    fieldLabel: 'Original Buy Spread',
                                    name: 'provider'+'['+data.provider_id+']'+'['+data.tier2.id+'][buyspreadoriginal]',
                                    reference: 'buyspreadoriginal',
                                    margin: '0 0 0 5',
                                    forceSelection: true,
                                    enforceMaxLength: true,
                                    step: 0.1,
                                    // minValue: 0,
                                    allowBlank: true,
                                    fieldStyle: "font-size: 1.1rem",
                                    style : 'color: #F6F6F6;border-color: #F6F6F6;display: none;',
                                    value: data.tier2.buyspreadoriginal,
                                },{
                                    xtype: 'numberfield',
                                    fieldLabel: 'Original Sell Spread',
                                    name: 'provider'+'['+data.provider_id+']'+'['+data.tier2.id+'][sellspreadoriginal]',
                                    reference: 'sellspreadoriginal',
                                    margin: '0 0 0 5',
                                    forceSelection: true,
                                    enforceMaxLength: true,
                                    step: 0.1,
                                    // minValue: 0,
                                    allowBlank: true,
                                    fieldStyle: "font-size: 1.1rem",
                                    style : 'color: #F6F6F6;border-color: #F6F6F6;display: none;',
                                    value: data.tier2.sellspreadoriginal,
                                },
                            ]
                        },{
                            xtype: "container",
                            layout: {
                                type: "hbox",
                                // align: "stretch",
                            },
                            reference: 'peakdate-container',
                            items: [
                                {
                                    xtype: 'timefield',
                                    // xtype: 'datefield',
                                    // format: 'Y-m-d H:i:s',
                                    fieldLabel: 'From Time',
                                    name: 'provider'+'['+data.provider_id+']'+'['+data.tier2.id+'][tier2effectiveon]',
                                    // name: 'tier2effectiveon',
                                    reference: 'tier2effectiveon',
                                    margin: '5 0 0 5',
                                    forceSelection: true,
                                    enforceMaxLength: true,
                                    // step: 0.1,
                                    // minValue: 0,
                                    allowBlank: false,
                                    fieldStyle: "font-size: 1.1rem",
                                    format: 'H:i',
                                    minValue: Ext.Date.parse('00:00:00 AM', 'h:i:s A'),
                                    maxValue: Ext.Date.parse('23:59:59 PM', 'h:i:s A'),
                                    increment: 1,
                                    // bind: "{selectedhours.time}".toString(),
                                    value: tier2effectiveon,
                                    // bind: function(){
                                    //     return '{selectedhours.time}'
                                    // }
                                    // readOnly: true,
                                    listeners:{
                                        select: function(tf, record, eOpts){
                                            // set date to prevent overlapping on tier 1 effective end on
                                            var newDate = new Date('01/01/1970 '+ tf.rawValue);
                                            newDate.setMinutes(newDate.getMinutes()- 1441);
                                            
                                            // Set date on t1 effective end on
                                            tf.up().up().items.items[3].items.items[1].setValue(newDate);
                                        },
                                    }
                                },
                                {
                                    xtype: 'timefield',
                                    // xtype: 'datefield',
                                    // format: 'Y-m-d H:i:s',
                                    fieldLabel: 'To Time',
                                    name: 'provider'+'['+data.provider_id+']'+'['+data.tier2.id+'][tier2effectiveendon]',
                                    // name: 'tier2effectiveendon',
                                    reference: 'tier2effectiveendon',
                                    margin: '5 0 0 5',
                                    forceSelection: true,
                                    enforceMaxLength: true,
                                    // step: 0.1,
                                    // minValue: 0,
                                    allowBlank: true,
                                    fieldStyle: "font-size: 1.1rem",
                                    format: 'H:i',
                                    minValue: Ext.Date.parse('00:00:00 AM', 'h:i:s A'),
                                    maxValue: Ext.Date.parse('23:59:59 PM', 'h:i:s A'),
                                    increment: 1,
                                    // bind: "{selectedhours.timeend}".toString(),
                                    value: tier2effectiveendon,
                                    // bind: function(){
                                    //     return '{selectedhours.time}'
                                    // }
                                    // readOnly: true,
                                    listeners:{
                                        select: function(tf, record, eOpts){
                                            // set date to prevent overlapping on tier 1 effective on
                                            var newDate = new Date('01/01/1970 '+ tf.rawValue);
                                            newDate.setMinutes(newDate.getMinutes()- 1439);
                                            
                                            // Set date on t1 effective on
                                            tf.up().up().items.items[3].items.items[0].setValue(newDate);
                                        },
                                    }
                                },
                            ]
                        },
                        // Add button before save
                        {
                            xtype: 'label',
                            text: 'Peak (Partner Spread)',
                            style: 'font-weight: bold;margin-top: 5px;border: 1px black',
                            reference: 'peakbuysellpercentage-label',
                            hidden: true,
                        },{
                            xtype: "container",
                            layout: {
                                type: "hbox",
                                align: "stretch",
                            },
                            reference: 'peakbuysellpercentage-container',
                            hidden: true,
                            items: [
                                {
                                    xtype: 'numberfield',
                                    fieldLabel: 'Buy Percentage (%)',
                                    name: 'provider'+'['+data.provider_id+']'+'['+ data.tier1.id+'][buypercentage]',
                                    reference: 'peakbuypercentage',
                                    margin: '0 0 0 5',
                                    forceSelection: true,
                                    enforceMaxLength: true,
                                    step: 0.1,
                                    // minValue: 0,
                                    // allowBlank: false,
                                    fieldStyle: "font-size: 1.1rem",
                                    maxValue: 100,
                                    minValue: -100,
                                    value: data.tier1.buypercent,
                                },
                                {
                                    xtype: 'numberfield',
                                    fieldLabel: 'Sell Percentage (%)',
                                    name: 'provider'+'['+data.provider_id+']'+'['+data.tier1.id +'][sellpercentage]',
                                    reference: 'peaksellpercentage',
                                    margin: '0 0 0 5',
                                    forceSelection: true,
                                    enforceMaxLength: true,
                                    step: 0.1,
                                    // minValue: 0,
                                    // allowBlank: false,
                                    fieldStyle: "font-size: 1.1rem",
                                    maxValue: 100,
                                    minValue: -100,
                                    value: data.tier1.sellpercent,
                                },
    
                            ]
                        },
                        {
                            xtype: 'label',
                            text: 'Non Peak (Partner Spread)',
                            style: 'font-weight: bold;margin-top: 5px;',
                            reference: 'nonpeakbuysellpercentage-label',
                            hidden: true,
                        },{
                            xtype: "container",
                            layout: {
                                type: "hbox",
                                align: "stretch",
                            },
                            reference: 'peakbuysellpercentage-container',
                            hidden: true,
                            items: [
                                {
                                    xtype: 'numberfield',
                                    fieldLabel: 'Buy Percentage (%)',
                                    name: 'provider'+'['+data.provider_id+']'+'['+data.tier2.id+'][buypercentage]',
                                    reference: 'nonpeakbuypercentage',
                                    margin: '0 0 0 5',
                                    forceSelection: true,
                                    enforceMaxLength: true,
                                    step: 0.1,
                                    // minValue: 0,
                                    // allowBlank: false,
                                    fieldStyle: "font-size: 1.1rem",
                                    maxValue: 100,
                                    minValue: -100,
                                    value: data.tier2.buypercent,
                                },
                                {
                                    xtype: 'numberfield',
                                    fieldLabel: 'Sell Percentage (%)',
                                    name: 'provider'+'['+data.provider_id+']'+'['+data.tier2.id+'][sellpercentage]',
                                    reference: 'nonpeaksellpercentage',
                                    margin: '0 0 0 5',
                                    forceSelection: true,
                                    enforceMaxLength: true,
                                    step: 0.1,
                                    // minValue: 0,
                                    // allowBlank: false,
                                    fieldStyle: "font-size: 1.1rem",
                                    maxValue: 100,
                                    minValue: -100,
                                    value: data.tier2.sellpercent,
                                },
    
                            ]
                        },
                        {
                            xtype: 'button',
                            text: 'Save '+data.channel_code+' only',
                            width: '230px',
                            align: 'center',
                            margin: '8 0',
                            handler: 'addsingleadjuster',
                            provider_code: data.channel_code,
                        }]
                    }
                ]
            },{
                
                        xtype: 'container',
                        flex:1,
                            title: data.channel_code,
                            headerPosition: 'top',
                            constrain: true,
                            x: 300, y: 395, alwaysOnTop: 7,
                            width: 300,
                            height: 280,
                            cls: ['background-blue','tradeprice-window'],
                            items: [{
                                xtype: 'container',
                                padding: '10px',
                                style: { 'margin-top': '45px',  },
                                layout: {
                                    type:'table',
                                    columns: 2,
                                    trAttrs: { style: { 'text-align': 'center' } },
                                    tdAttrs: { style: { 'border': '1px solid black',  } }
                                },
                                defaults: {
                                    width: "100%"
                                },
                                items: [{
                                    xtype: 'displayfield',
                                    value: 'Buy',
                                },{
                                    xtype: 'displayfield',
                                    value: 'Sell',
                                },{
                                    xtype: 'container',
                                    layout: {
                                        type: 'vbox',
                                        align: 'center',
                                        pack: 'center'
                                    },
                                    items:[{
                                        xtype: 'displayfield',
                                        value: '0.00',
                                        bind: {
                                            value: '{'+data.channel_code+'.companybuynum}'
                                            // value: '{'+data.channel_code+'.companybuy}'
                                        },
                                        cls: 'largetext',
                                    },{
                                        xtype: 'displayfield',
                                        cls:'boldtext',
                                        value: 'per g',
                                    }]
                                },{
                                    xtype: 'container',
                                    layout: {
                                        type: 'vbox',
                                        align: 'center',
                                        pack: 'center'
                                    },
                                    items:[{
                                        xtype: 'displayfield',
                                        value: '0.00',
                                        bind: {
                                            value: '{'+data.channel_code+'.companysellnum}'
                                            // value: '{'+data.channel_code+'.companysell}'
                                        },
                                        cls: 'largetext',
                                    },{
                                        xtype: 'displayfield',
                                        cls:'boldtext',
                                        value: 'per g',
                                    }]
                                },{
                                    xtype: 'container',
                                    height: '46px',
                                    layout: {
                                        type: 'vbox',
                                        align: 'center',
                                        pack: 'center'
                                    },
                                    items:[{
                                        xtype: 'displayfield',
                                        value: 'UUID',
                                        cls: 'cusdisplay'
                                    },{
                                        xtype: 'displayfield',
                                        value: '-',
                                        bind: {
                                            value: '{'+data.channel_code+'.uuid}'
                                        },
                                    }],
                                    colspan: 2,
                                },{
                                    xtype: 'container',
                                    height: '46px',
                                    layout: {
                                        type: 'vbox',
                                        align: 'center',
                                        pack: 'center'
                                    },
                                    items:[{
                                        xtype: 'displayfield',
                                        value: 'TimeStamp',
                                        cls: 'cusdisplay'
                                    },{
                                        xtype: 'displayfield',
                                        value: '-',
                                        bind: {
                                            value: '{'+data.channel_code+'.datetime}'
                                        },
                                    }],
                                    colspan: 2,
                                }]
                            }]

            }],
        }
    return returnx
    },

    livepreview: function(data){
        var returnx = {
                            xtype: 'container',
                            title: data.channelvar,
                            headerPosition: 'top',
                            constrain: true,
                            x: 300, y: 395, alwaysOnTop: 7,
                            width: 300,
                            height: 280,
                            cls: ['background-blue','tradeprice-window'],
                            items: [{
                                xtype: 'container',
                                padding: '10px',
                                layout: {
                                    type:'table',
                                    columns: 2,
                                    trAttrs: { style: { 'text-align': 'center' } },
                                    tdAttrs: { style: { 'border': '1px solid black',  } }
                                },
                                defaults: {
                                    width: "100%"
                                },
                                items: [{
                                    xtype: 'displayfield',
                                    value: 'Buy',
                                },{
                                    xtype: 'displayfield',
                                    value: 'Sell',
                                },{
                                    xtype: 'container',
                                    layout: {
                                        type: 'vbox',
                                        align: 'center',
                                        pack: 'center'
                                    },
                                    items:[{
                                        xtype: 'displayfield',
                                        value: '0.00',
                                        bind: {
                                            value: '{'+data.channelvar+'.companybuy}'
                                        },
                                        cls: 'largetext',
                                    },{
                                        xtype: 'displayfield',
                                        cls:'boldtext',
                                        value: 'per g',
                                    }]
                                },{
                                    xtype: 'container',
                                    layout: {
                                        type: 'vbox',
                                        align: 'center',
                                        pack: 'center'
                                    },
                                    items:[{
                                        xtype: 'displayfield',
                                        value: '0.00',
                                        bind: {
                                            value: '{'+data.channelvar+'.companysell}'
                                        },
                                        cls: 'largetext',
                                    },{
                                        xtype: 'displayfield',
                                        cls:'boldtext',
                                        value: 'per g',
                                    }]
                                },{
                                    xtype: 'container',
                                    layout: {
                                        type: 'vbox',
                                        align: 'center',
                                        pack: 'center'
                                    },
                                    items:[{
                                        xtype: 'displayfield',
                                        value: 'UUID',
                                        cls: 'cusdisplay'
                                    },{
                                        xtype: 'displayfield',
                                        value: '-',
                                        bind: {
                                            value: '{'+data.channelvar+'.uuid}'
                                        },
                                    }],
                                    colspan: 2,
                                },{
                                    xtype: 'container',
                                    layout: {
                                        type: 'vbox',
                                        align: 'center',
                                        pack: 'center'
                                    },
                                    items:[{
                                        xtype: 'displayfield',
                                        value: 'TimeStamp',
                                        cls: 'cusdisplay'
                                    },{
                                        xtype: 'displayfield',
                                        value: '-',
                                        bind: {
                                            value: '{'+data.channelvar+'.datetime}'
                                        },
                                    }],
                                    colspan: 2,
                                }]
                            }]
        }
        return returnx;
    },

    formatPriceColor: function(newPrice, oldPrice){
        newPrice = newPrice.toString();
        oldPrice = oldPrice.toString();

        let result = oldPrice.match(/\<span.*\>(.*)\<\/span\>/);

        if (result) {
            oldPrice = result[1];
        }
        // Green
        if (newPrice > oldPrice) {
            return '<span style="color:#1ac69c;">'+newPrice+' ↑</span>';
        }
        // Red
        if (newPrice < oldPrice) {
            return '<span style="color:#FF4848;">'+newPrice+' ↓</span>';
        }
        if (newPrice == oldPrice) {
            return newPrice;
        }
    },

    init_live_stream: function(data){
        _this = this;
        // console.log(this,'THIIIS_debug')
        vm = this.getView().getController().gridFormView.getViewModel();
        // vm.set("INTLX_MBB", {"companybuy": 133});
        // return;
        // this.callParent(arguments);
        // return;

        data.map((priceprovider) => {
            //var websocketurl = window.location.protocol == 'https:' ? 'wss://' : 'ws://'; websocketurl += window.location.hostname + '/index.php?hdl=pssubscribe&action=subscribe&pdt=DG-999-9&code='+ priceprovider.channel_code;
            // const source = new EventSource('https://otc-uat.ace2u.com:8443/index.php?hdl=pssubscribe&action=subscribesales&pdt=DG-999-9&onecent=1&code='+ priceprovider.channel_code);
            const source = new EventSource('https://gtp-uat-app-lb-123853570.ap-southeast-1.elb.amazonaws.com/index.php?hdl=pssubscribe&action=subscribesales&pdt=DG-999-9&onecent=1&code='+ priceprovider.channel_code);
            // const source = new EventSource('https://10.10.55.114/index.php?hdl=pssubscribe&action=subscribesales&pdt=DG-999-9&onecent=1&code=INTLX.BSN');
            source.onmessage = function(event) {
                // handle the message received from the server
                jsonString  = event.data;
                // sample format below
                // '{"event":"read","data":[{"companybuy":287.471222,"companysell":295.568383,"uuid":"PS00000000005B7581","timestamp":1683098630}]}'
                message = JSON.parse(jsonString);
            
                // message = JSON.parse(message);
                message.data[0].companybuykg = (message.data[0].companybuy * 1000.00).toLocaleString();
                message.data[0].companysellkg = (message.data[0].companysell * 1000.00).toLocaleString();
                message.data[0].datetime = new Date(message.data[0].timestamp * 1000.00).toLocaleString();
                

                
                // Filter price
                numbuy = message.data[0].companybuy;
                numsell = message.data[0].companysell;
                
                // // Convert to string
                // numbuy.toString();
                // numsell.toString();

                // // numbuy = numbuy.slice(0, (numbuy.indexOf("."))+4);
                // buynumber  = Number(numbuy);
        
                // // numsell = numsell.slice(0, (numsell.indexOf("."))+4);
                // sellnumber  = Number(numsell);

                buynumber = Math.trunc(numbuy*Math.pow(10, 3))/Math.pow(10, 3);
                sellnumber = Math.trunc(numsell*Math.pow(10, 3))/Math.pow(10, 3)
                
                // message.data[0].companybuynum = (buynumber).toLocaleString();
                // message.data[0].companysellnum = (sellnumber).toLocaleString();
                
                message.data[0].companybuynum = (buynumber).toLocaleString();
                message.data[0].companysellnum = (sellnumber).toLocaleString();

                if (vm.get(priceprovider.channel_code)) {
                    Object.keys(message.data[0]).map(function(key, index) {
                        let fields = [
                            'companybuy', 'companysell'
                        ];
                        if(fields.includes(key)){
                            if (vm.get(priceprovider.channel_code)[key]) {
                                appendkey = key + 'num';
                                message.data[0][appendkey] = _this.formatPriceColor(message.data[0][appendkey], vm.get(priceprovider.channel_code)[appendkey]);
                            }
                        }
                    });
                }

                // message.data[0][appendkey] = _this.formatPriceColor(message.data[0][key], vm.get(channelName)[key]);
                // message.data[0][appendkey] = _this.formatPriceColor(message.data[0][key], vm.get(channelName)[key]);

                message.data[0].companybuystem = (message.data[0].companybuy * 1000.00).toLocaleString();
                message.data[0].companysellstem = (message.data[0].companysell * 1000.00).toLocaleString();
                vm.set(priceprovider.channel_code, message.data[0]);
                
            };
            //var websocketurl = 'wss://gtp2uat.ace2u.com/streamprice?hdl=pssubscribe&action=subscribesales&pdt=DG-999-9&air=1&code=INTLX.GTP_T1';
            Ext.create ('Ext.ux.WebSocket', {
                url: websocketurl,
                // url: 'ws://bo.gtp.development/streamprice?hdl=pssubscribe&action=subscribe&pdt=DG-999-9&mib=1' ,
                listeners: {
                    open: function (ws) {
                    } ,
                    close: function (ws) {
                        console.log ('The websocket is closed!');
                    },
                    error: function (ws, error) {
                        Ext.Error.raise ('ERRRROR: ' + error);
                    } ,
                    message: function (ws, message) {
                        message = JSON.parse(message);
                        message.data[0].companybuykg = (message.data[0].companybuy * 1000.00).toLocaleString();
                        message.data[0].companysellkg = (message.data[0].companysell * 1000.00).toLocaleString();
                        message.data[0].datetime = new Date(message.data[0].timestamp * 1000.00).toLocaleString();
                        

                        
                        // Filter price
                        numbuy = message.data[0].companybuy;
                        numsell = message.data[0].companysell;
                        
                        // // Convert to string
                        // numbuy.toString();
                        // numsell.toString();

                        // // numbuy = numbuy.slice(0, (numbuy.indexOf("."))+4);
                        // buynumber  = Number(numbuy);
                
                        // // numsell = numsell.slice(0, (numsell.indexOf("."))+4);
                        // sellnumber  = Number(numsell);

                        buynumber = Math.trunc(numbuy*Math.pow(10, 3))/Math.pow(10, 3);
                        sellnumber = Math.trunc(numsell*Math.pow(10, 3))/Math.pow(10, 3)
                        
                        // message.data[0].companybuynum = (buynumber).toLocaleString();
                        // message.data[0].companysellnum = (sellnumber).toLocaleString();
                        
                        message.data[0].companybuynum = (buynumber).toLocaleString();
                        message.data[0].companysellnum = (sellnumber).toLocaleString();

                        if (vm.get(priceprovider.channel_code)) {
                            Object.keys(message.data[0]).map(function(key, index) {
                                let fields = [
                                    'companybuy', 'companysell'
                                ];
                                if(fields.includes(key)){
                                    if (vm.get(priceprovider.channel_code)[key]) {
                                        appendkey = key + 'num';
                                        message.data[0][appendkey] = _this.formatPriceColor(message.data[0][appendkey], vm.get(priceprovider.channel_code)[appendkey]);
                                    }
                                }
                            });
                        }

                        // message.data[0][appendkey] = _this.formatPriceColor(message.data[0][key], vm.get(channelName)[key]);
                        // message.data[0][appendkey] = _this.formatPriceColor(message.data[0][key], vm.get(channelName)[key]);

                        message.data[0].companybuystem = (message.data[0].companybuy * 1000.00).toLocaleString();
                        message.data[0].companysellstem = (message.data[0].companysell * 1000.00).toLocaleString();
                        vm.set(priceprovider.channel_code, message.data[0]);
                    }
                },
            });
        })

        
    }
});
