Ext.define('snap.view.anppool.anppoolController', {
    extend: 'snap.view.gridpanel.BaseController',
    alias: 'controller.anppool-anppool',

    getPrintReport: function(btn){

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

        header = encodeURI(JSON.stringify(header));
        daterange = encodeURI(JSON.stringify(daterange));

        console.log('header',header)
        url = '?hdl=anppool&action=exportExcel&header='+header+'&daterange='+daterange;
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
                            params: { hdl: 'anppool', action: 'exportZip',
                                     header: header,
                                     daterange: daterange,
                                     email: schedulepanel.getValues().email,
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
                property: "ordercreatedon", type: "date", operator: "BETWEEN", value: [startDate, endDate]
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
    }
});