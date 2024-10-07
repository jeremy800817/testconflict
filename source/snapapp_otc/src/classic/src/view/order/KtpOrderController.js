Ext.define('snap.view.order.KtpOrderController', {
    extend: 'snap.view.order.MyOrderController',
    alias: 'controller.ktporder-ktporder',

    openDownloadGridForm: function(btn){
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
                hdl: 'myorder', action: 'getMerchantList', partnercode: myView.partnercode,
            }, 'Fetching data from server....').then(
                function (data) {
                    if (data.success) {
                        //console.log(data.merchantdata);
                        var var2 = new Ext.Window({
                            iconCls: 'x-fa fa-cube',
                            header: {
                                style : 'background-color: #204A6D;border-color: #204A6D;',
                            },
                            scrollable: true,title: 'Download',layout: 'fit',width: 400,height: 500,
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

    GenerateReport: function(selectedID){
        try {
            header = [];
            const reportingFields = [
                ['Date', ['createdon', 0]],
                ['Transaction Ref No', ['refno', 0]],
            ];
            for (let [key, value] of reportingFields) {
                columnleft = {
                    text: key,
                    index: value[0]
                }
                if (value[0] !== 0){
                    columnleft.decimal = value[1];
                }
                header.push(columnleft);
            }
    
            elmnt.getView('grid').getColumns().map(column => {
                if (column.isVisible() && column.dataIndex !== null){
                    _key = column.text
                    _value = column.dataIndex
                    columnlist = {
                        text: _key,
                        index: _value
                    }
                    if (column.exportdecimal !== null){
                        _decimal = column.exportdecimal;
                        columnlist.decimal = _decimal;
                    }
                    if('refno' == column.dataIndex || 'ordstatus' == column.dataIndex){
                        // dont push header if its status
                    }
                    else {
                        header.push(columnlist);
                    }
                }
            });
            
            // Add a transaction header 
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
                if (rangeLimit >= intervalDays){
                    // Check if day exceeds 63 days 
                        
                    startDate = Ext.Date.format(startDate,'Y-m-d 00:00:00');
                    endDate = Ext.Date.format(endDate,'Y-m-d 23:59:59');
                    daterange = {
                        startDate: startDate,
                        endDate: endDate,
                    }
                }
                else{
                    Ext.MessageBox.show({
                        title: 'Filter Date',
                        msg: 'Please select date range within 2 months',
                        buttons: Ext.MessageBox.OK,
                        icon: Ext.MessageBox.ERROR
                    });
                    return
                }
                // End check
            }
            else{
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
            //type = encodeURI(JSON.stringify(type));
            partnerCode = myView.partnercode;
            url = '?hdl=myorder&action=exportExcel&header='+header+'&daterange='+daterange+'&partnercode='+partnerCode+'&selected='+selectedID;
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
        }
        catch(exception){
            return false;
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
        

        _this = this;
        snap.getApplication().sendRequest({
            hdl: 'myorder', action: 'getMerchantList', partnercode: myView.partnercode,
        }, 'Fetching data from server....').then(
            function (data) {
                if (data.success) {
                    //console.log(data.merchantdata);
                    var var2 = new Ext.Window({
                        iconCls: 'x-fa fa-cube',
                        header: {
                            style : 'background-color: #204A6D;border-color: #204A6D;',
                        },
                        scrollable: true,title: 'Export Zip To Email..',layout: 'fit',
                        maxHeight: 2000,modal: true,plain: true,buttonAlign: 'center', 
                        margin: '0 5 5 0',
                        // defaults: { labelWidth: 190, width: '100%', layout: 'hbox', hideLabel: false },
                        viewModel: {
                            data: {
                                name: "KTP",
                                merchantdata: data.merchantdata
                            }
                        },
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
                                xtype: 'form',
                                reference: 'zip-download-form',
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
                        buttons: [{
                            text: 'Submit',
                            handler: function(){
                                // Point to Form with reference point
                                box = var2.lookupController().lookupReference('merchant-form').getForm();

                                // assign variable to form fields
                                form = box.getFieldValues();

                                overallform = var2.lookupController().lookupReference('zip-download-form').getForm();
                                email = overallform.getValues().email;

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

                                if(email == ""){
                                    Ext.MessageBox.show({
                                        title: 'Fill in destination email',
                                        msg: 'Please fill in email to be sent to',
                                        buttons: Ext.MessageBox.OK,
                                        icon: Ext.MessageBox.WARNING
                                    });
                                }

                                if(selected == ""){
                                    Ext.MessageBox.show({
                                        title: 'Select Checkbox',
                                        msg: 'Please select at least one option',
                                        buttons: Ext.MessageBox.OK,
                                        icon: Ext.MessageBox.WARNING
                                    });
                                }
                                else{

                                    overallform.submit({
                                        submitEmptyText: false,
                                        url: 'gtp.php',
                                        method: 'POST',
                                        dataType: "json",
                                        params: { hdl: 'myorder', action: 'exportZip',
                                                 header: header,
                                                 daterange: daterange,
                                                 email: email,
                                                 partnercode: partnercode,
                                                 selected: selected,
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

                                    // if (!status){
                                    //     Ext.MessageBox.show({
                                    //         title: 'Generate File',
                                    //         msg: 'Generating file failed',
                                    //         buttons: Ext.MessageBox.OK,
                                    //         icon: Ext.MessageBox.ERROR
                                    //     });
                                    // }
                                }
                                return false;
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
    },
});