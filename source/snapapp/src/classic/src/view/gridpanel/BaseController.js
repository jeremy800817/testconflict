Ext.define('snap.view.gridpanel.BaseController', {
    extend: 'Ext.app.ViewController',
    alias: 'controller.gridpanel-base',

    init: function(view) {
        if (view instanceof snap.view.gridpanel.Base) {
            //Somehow have to bind the store this way......
           var pagingTool = this.lookupReference('gridPagingToolbar');
           if (pagingTool) pagingTool.setStore(view.getStore());
        }
    },

    /**
     * Handling for grid item double click event
     */
    onGridItemDoubleClicked: function(view, record, item, index, e, eOpts) {
        if (this.getView().gridEnableCellEditing) return true;
        if (this.getView().enableDetailView) this.showDetails();
    },

    /**
     * Handling for cell click event
     */
    onGridCellClicked: function(view, td, cellIndex, record, tr, rowIndex, e, eOpts) {
        if (this.gridSelectionCheckOnly) {
            if (cellIndex > 0) {
                this.getView().getSelectionModel().deselectAll();
                this.getView().getSelectionModel().select(rowIndex);
            }
        }
    },

    /**
     * Show details
     */
    showDetails: function() {
        var myView = this.getView(),
            me = this, record,
            startupSection = undefined;

        if (!myView.enableDetailView) return;
        var sm = myView.getSelectionModel();
        var selectedRecords = sm.getSelection();
        if (selectedRecords.length == 1) {
            for(var i = 0; i < selectedRecords.length; i++) {
                selectedID = selectedRecords[i].get('id');
                record = selectedRecords[i];
                break;
            }
        } else {
            Ext.MessageBox.show({
                title: 'Warning', buttons: Ext.MessageBox.OK, icon: Ext.MessageBox.WARNING,
                msg: 'Select a record first'});
            return;
        }
        if(Ext.isFunction(me['onPreLoadViewDetail'])) {
            record = this.onPreLoadViewDetail(record, function(finalRecord){
                me._doActualShowView(finalRecord);
            });
            if(false === record) return;
        }
        me._doActualShowView(record);
    },

    /**
     * Private method to actually show the detail window
     */
    _doActualShowView: function(record) {
        var myView = this.getView(),
            me = this,
            startupSection = undefined;

        //Prepare window to show
        if(! this.propWin) {
            //Configure the sections information if available.
            var buttonProp = [];
            if(myView.detailViewSections) {
                for(var i in myView.detailViewSections) {
                    if(myView.detailViewSections.hasOwnProperty(i)) {
                        var obj = {
                            text: myView.detailViewSections[i],
                            handler: Function('this._setDetailViewSource(\''+i+'\');').bind(me),
                            bind: {
                                disabled: '{nowShowing === "' + i + '"}'
                            }
                        };
                        if( undefined === startupSection) startupSection = i;
                        else obj.margin = '0 0 10 0';
                        buttonProp.push( obj);
                    }
                }
            }
            var container = [];
            if(buttonProp.length > 0) {
                container.push({
                    //height: 32,
                    xtype: 'container', layout: 'hbox', defaultType: 'button', margin: '0 0 10 0', 
                    items: buttonProp
                });
            }
            var detailPaneProps = {};
            Ext.apply(detailPaneProps, myView.detailViewConfig, { xtype: 'propertygrid', nameColumnWidth: 165, sortableColumns: false});
            container.push( detailPaneProps);
        	var propWin = new Ext.Window({
	            title: 'Properties' + ' ...',
	            //layout: 'fit',
                bodyPadding: 10,
	            modal: true,
	            width: myView.detailViewWindowWidth,
	            height: myView.detailViewWindowHeight,
	            closeAction: 'close',
	            // bodyPadding: 0,
	            // bodyBorder: false,
	            // bodyStyle: {
	            //     background: '#FFFFFF'
	            // },
	            maximizable: true,
	            plain: false,
	            scrollable: 'vertical',
                viewModel: {
                    data: {
                        nowShowing: startupSection
                    }
                },                
                items: container,
	            //html: content,
	            buttons: [{
	                text: 'Close',
	                buttonAlign: 'center',
	                handler: function() {
	                    propWin.close();
	                }
	            }]
	        });
	        this.propWin = propWin;

        }
        //Populate the details of the record
        var content = {};
        var  columns = myView.getColumns();
        var s = undefined;
        if(myView.detailViewUseRawData) {
            content = record;
        } else {
            for (var i = 0; i < columns.length; i++) {
                for(s in myView.detailViewSections) {
                    if(!content.hasOwnProperty(s)) content[s] = {};
                    var continueToSet = false;
                    for(var j = 0; j < myView.detailViewSectionMap[s].length; j++) {
                        if(myView.detailViewSectionMap[s][j] == columns[i].dataIndex) {
                            continueToSet = true;
                            break;
                        }
                    }
                    if(continueToSet) break;
                }
                var value = this.onGetItemDetail( record, columns[i]);
                if( value != null) {
                    if(undefined === s) content[columns[i].text.replace(/ /g, '&nbsp;')] = value;
                    else content[s][columns[i].text.replace(/ /g, '&nbsp;')] = value;
                }
            }
        }
        this.propWin.recordData = content;
        var vm = this.propWin.getViewModel();
        if(null !== vm.get('nowShowing')) this._setDetailViewSource(vm.get('nowShowing'));
        else this.propWin.down('grid').setSource(content);
        this.propWin.show();
    },

    /**
     * Private method to identify the properties to show on the grid
     */
    _setDetailViewSource: function(section) {
        this.propWin.getViewModel().set('nowShowing', section);
        this.propWin.down('grid').setSource(this.propWin.recordData[section]);
    },

    firstTimeMenuRefresh: function() {
        this.onGridSelectionChanged(null, []);
    },

    /**
     * This method is triggered when selection changed to refresh all the items on the toolbar
     */
    onGridSelectionChanged: function(sm, selections) {
    	var toolbar = this.getView().toolbarConfig;
        if (!toolbar) return;
    	for(i = 0; i < toolbar.length; i++) {
    		var anItem = toolbar[i];
    		if(!anItem || !anItem.handler) continue;
            var theToolbarItem = this.lookupReference(anItem.reference);
            var theContextMenuItem = this.lookupReference(anItem.reference+'_menu');
            // if(!theToolbarItem) {
            //     Ext.log({level: 'warn'}, 'The toolbar item for ' + (anItem.text || anItem.tooltip) + ' is not available');
            // }
            // if(!theContextMenuItem) {
            //     Ext.log({level: 'warn'}, 'The menu item for ' + (anItem.text || anItem.tooltip) + ' is not available');
            // }
    		if(anItem.canEnableItem && typeof anItem.canEnableItem == 'function') {
    			if(theToolbarItem) theToolbarItem.setDisabled(! anItem.canEnableItem(selections));
    			if(theContextMenuItem) theContextMenuItem.setDisabled(! anItem.canEnableItem(selections));
    		} else if(anItem.validSelection && anItem.validSelection != 'ignore') {
    			if('single' == anItem.validSelection) {
    				if(theToolbarItem) theToolbarItem.setDisabled(selections.length == 0 || selections.length > 1);
    				if(theContextMenuItem) theContextMenuItem.setDisabled(selections.length == 0 || selections.length > 1);
    			} else {
    				if(theToolbarItem) theToolbarItem.setDisabled(selections.length == 0);
    				if(theContextMenuItem) theContextMenuItem.setDisabled(selections.length == 0);
    			}
    		}
    	}
	},

	onContextMenuClick: function(view, record, item, index, e, eOpts) {
        var myView = this.getView();
        if( !myView.toolbarConfig && !myView.enableContextMenu) return;  //no show if have no toolbar or context menu.
        if( ! this.contextMenu) {
            items = [];
            var toolbar = myView.toolbarConfig;
            for(var i = 0; i < toolbar.length; i++) {
                var isDisabled = (this.lookupReference(toolbar[i].reference)) ? this.lookupReference(toolbar[i].reference).disabled : false;
                if(toolbar[i].enableMenu) {
                    var stringMethod = toolbar[i].handler;
                    items.push({handler: Function( 'this.' + stringMethod + '();').bind(this),
                                reference: toolbar[i].reference + '_menu', 
                                text: toolbar[i].menuText || toolbar[i].text, 
                                tooltip: toolbar[i].tooltip, iconCls: toolbar[i].iconCls, disabled: false/*isDisabled*/ });
                }
            }
            this.contextMenu = Ext.create('Ext.menu.Menu',{items: items}); 
        }
        //Force it to select one record everytime.
        myView.getSelectionModel().select(record);

		e.preventDefault();
		e.stopEvent();
		this.contextMenu.showAt(e.getXY());
	},

    onDelete: function(btn) {
        var action = 'delete';
        var confirmMessage = 'Are you sure you want to delete {0} selected record(s)?';
        var me = this;

        var sm = this.getView().getSelectionModel();
        var selectedRecords = sm.getSelection();
        if (selectedRecords.length == 0) {
            Ext.MessageBox.show({
                title: 'Warning', buttons: Ext.MessageBox.OK, icon: Ext.MessageBox.WARNING,
                msg: 'Select a record first'});
            return;
        } else /*if (selectedRecords.length > 0)*/ {
        	new Ext.Promise(function(fulfilled, rejected) {
        		Ext.MessageBox.confirm('Confirm', Ext.String.format(confirmMessage, selectedRecords.length), function(btn) {
        			if (btn == 'yes') fulfilled();
        		});
        	}).then(function(){
                var selectedIDs = new Array();
                for (var i = 0; i < selectedRecords.length; i++) {
                    selectedIDs[i] = selectedRecords[i].get('id');
                }
                snap.getApplication().sendRequest({ hdl: me.getView().getStore().getModel().entityName.toLowerCase(), 
                							action: 'delete', ids: selectedIDs.toString()}).then(
                	function(data, options) {
	                    if (data.success) {
	                        if (me.getView().gridShowDeleteSuccessfulMessage && action == 'delete') {
                                Ext.MessageBox.show({
                                    title: 'Info',
                                    msg: Ext.String.format('{0} selected record(s) has/have been successfully deleted.', selectedRecords.length),
                                    buttons: Ext.MessageBox.OK, icon: Ext.MessageBox.INFO,
                                    fn: function(btn) {
                                        me.getView().getStore().load();
                                    }
                                });
                            } else {
                                me.getView().getStore().load();
                            }
                        } else {
                            Ext.MessageBox.show({
                                title: 'Error Message',
                                msg: data.errmsg,
                                buttons: Ext.MessageBox.OK, icon: Ext.MessageBox.ERROR
                            });
                        }
                });
        	});
        }
    },

    showHideFilters: function(btn, e) {
        var me = this;
        var view = this.getView();
        var filters;
        filters = view.getFilterBar();
        if (!filters) {
            console.warn('Cant find filter plugin for this grid');
        }
        filters.setVisible.call(view, !view._filterBarPluginData.visible);
        btn.setTooltip((!view._filterBarPluginData.visible ? view._filterBarPluginData.showHideButtonTooltipDo : view._filterBarPluginData.showHideButtonTooltipUndo));

    },

    onAdd: function(btn) {
        this._onAddEdit(btn, 'add');
    },

    onEdit: function(btn) {
        this._onAddEdit(btn, 'edit');
    },

    /**
     * Method to get the detail item for the specific column.  Override here to provide additional or
     * special implementation for a particular column info.
     */
    onGetItemDetail: function( record, column) {
        var me = this;
        if(column.text == undefined || column.text.match(/&nbsp/)) return null;
        var value = Ext.isFunction(record['get']) ? record.get(column.dataIndex) : record[column.dataIndex];
        if(column.dataIndex === 'status') {
            if( value == 0 ) value = 'Inactive';
            else value = 'Active';
        } else if(Ext.isDate(value)) value = Ext.Date.format(value, 'D H:i:s F d, Y (O)');
        if (Ext.isFunction(me['onCustomGetItemDetail'])) {
            var customValue = this.onCustomGetItemDetail(record, column);
            if (customValue != '' && customValue != null && customValue !== undefined) value = customValue;
        }
        return value;
    },

    //private
    _onAddEdit: function(btn, formAction) {
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

        var gridFormView = Ext.create(myView.formClass, Ext.apply(myView.formConfig ? myView.formConfig : {}, {
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
        if (formAction == 'edit') {
            gridFormView.title = 'Edit ' + gridFormView.title + '...';
            // var sm = this.getView().getSelectionModel();
            // var selectedRecords = sm.getSelection();
            // var selectedRecord = selectedRecords[0];
            if(Ext.isFunction(me['onPreLoadForm'])) {
                if(! this.onPreLoadForm( gridFormView, addEditForm, selectedRecord, function(updatedRecord){
                    addEditForm.loadRecord(updatedRecord);
                    if(Ext.isFunction(me['onPostLoadForm'])) this.onPostLoadForm( gridFormView, addEditForm, updatedRecord);
                    me.gridFormView.show();
                  })) return;
            }
            addEditForm.loadRecord(selectedRecord);
            if(Ext.isFunction(me['onPostLoadForm'])) this.onPostLoadForm( gridFormView, addEditForm, selectedRecord);
        } else {
            gridFormView.title = 'Add ' + gridFormView.title + '...';
            if(Ext.isFunction(me['onPostLoadEmptyForm'])) this.onPostLoadEmptyForm( gridFormView, addEditForm);
        }
        this.gridFormView.show();
    },
    
    _isJson: function isJson(str) {
        try {
            JSON.parse(str);
        } catch (e) {
            return false;
        }
        return true;
    },

    //private
    _onSaveGridForm: function(btn) {
        // var owningWindow = btn.up('window');
        // var gridFormPanel = owningWindow.down('form');
        var me = this,
            myView = this.getView(),
            addEditForm = this.gridFormView.down('form').getForm();
        if (addEditForm.isValid()) {
            btn.disable();
            if( Ext.isFunction(me['onPreAddEditSubmit']) && !this.onPreAddEditSubmit(this._formAction, me.gridFormView, addEditForm, btn)) {
                btn.enable();
                return;
            }
            
            //sanitize user input before save into database, except password
            /* Ext.Array.each(addEditForm.getFields().items, function (field, index, self) {
                if ('password' != field.inputType && field.value && !me._isJson(field.value)) {
                    var encodedValue =  Ext.util.Format.htmlEncode(field.value);
                    field.setValue(encodedValue);
                }
            }); */
            
            addEditForm.submit({
                submitEmptyText: false,
                url: 'index.php',
                method: 'POST',
                params: { hdl: myView.getStore().getModel().entityName.toLowerCase(), action: this._formAction },
                waitMsg: 'Processing',
                success: function(frm, action){ //success
                    me.gridFormView.close();
                    me.gridFormView = undefined;
                    myView.getStore().reload();
                }, 
                failure: function(frm,action) { //failed
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

    ////////////////////////////////////////////////////////////////////
    ///
    ///Available Callbacks
    ///
    ////////////////////////////////////////////////////////////////////

    /**
     * This callback is called before the form has been pre-loaded.  There are 2 ways to use this method.
     * 1) Load some extra data to the form by doing some calculation etc.  In this way, all processing on
     *    the browser/client side and the function can return true to continue showing the form.
     * 2) Asynchronous operation, e.g. to get data from server.  We will have to wait for data to arrive from
     *    server before proceeding to load the form.  So for this case the function has to return false to 
     *    stop processing.  After loading data from server and doing all necessary job, we can use the 
     *    asyncLoadCallback() method to continue with showing of the form for user.
     */
    // onPreLoadForm: function( formView, form, record, asyncLoadCallback(newRecord)) {
    //     snap.getApplication().sendRequest({
    //         hdl: '__HANDLER__', 'action': '__ACTION__', id: record.data.id
    //     }, 'Fetching data from server....').then(
    //     //Received data from server already
    //     function(data){
    //         if(data.success){
    //             //TODO:  complete your processing of information from server here.
    //         }
    //         //Call the callback method to continue with form showing.
    //         asyncLoadCallback(myNewRecord);
    //     });
    //     return false;
    // },

    /**
     * This callback is activated after the form have been loaded to do some post event items
     */
    //onPostLoadForm: function( formView, form, record) {},
    
    /**
     * This method is called when attempting to add data for a form.  It can be used to initialise
     * default values for the records.
     */
    // onPostLoadEmptyForm: function( formView, form) {},

    /**
     * This method is called before a form is submitted to the server for add / edit operation.  The function
     * has to return true to continue with the submission or false to halt the process.  Furthermore, users
     * can also do further validations or checks.
     */
    // onPreAddEditSubmit: function(formAction, formView, formObject){}

    /**
     * This method is called to allow user the opportunity to format a field-value based data to be
     * used manually to show info instead of the standard rendering used.  This can be used in
     * conjunction with the config: detailViewUseRawData to manually format data to be rendered.
     * Return the data that should be used.
     */
    // onPreLoadViewDetail: function(record, displayCallback) {
        // //Example A:  1)  set detailViewUseRawData to true in view, then 
        // //            2) implement this function to provide own data
        // var data = { company: { 'Company name': record.data.name, 'Company address': record.data.address, 
        //                         'Company district' : record.data.district, 'Post code': record.data.postcode, 
        //                         'State' :record.data.state},
        //              contact: {'Key contact person': record.data.contactperson, 
        //                         'Phone no': record.data.contactno, 'Email Address' : record.data.email
        //                         },
        //              others: {'Created on': record.data.createdon, 'status' : record.data.status==1 ? 'Active' : 'Inactive',
        //                     'Some other data': 'ssssssss'}
        //             };
        // return data;

        // //Example B:  1)  set detailViewUseRawData to true in view, then 
        // //            2) get the data from server and after processing them, use callback function to return 
        // //                and continue with display  
        // //            3)  Implement function in PHP to return correct data
        //     snap.getApplication().sendRequest({
        //         hdl: '__HANDLER__', action: '__ACTION__', id: record.id}).then(
        //         function( data, options) {
        //             if(data.success) {
        //                 var serverRecord;
        //                 if(Ext.isArray(data.record)) serverRecord = data.record[0];
        //                 else serverRecord = data.record;
        //                 displayCallback(serverRecord);
        //             }
        //         }
        //     )
    // }
    getPrintReport: function(btn){
        handlerModule = btn.handlerModule;
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

        url = '?hdl='+handlerModule+'&action=exportExcel&header='+header+'&daterange='+daterange;
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

    boldText: function (value, rec) {
        rec.style = 'font-weight:bold'
        return Ext.util.Format.htmlEncode(value)
    },
    ordernoColor: function (value, rec, rowrec) {
        // console.log(rec,rowrec,'rec')
        if (rowrec.data.type == 'CompanySell'){
            rec.style = 'color:#209474'
        }
        if (rowrec.data.type == 'CompanyBuy'){
            rec.style = 'color:#d07b32'
        }
        return Ext.util.Format.htmlEncode(value)
    }, 
});
