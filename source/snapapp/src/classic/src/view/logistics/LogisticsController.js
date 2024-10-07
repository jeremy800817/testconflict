Ext.define('snap.view.logistics.LogisticController', {
    extend: 'snap.view.gridpanel.BaseController',
    alias: 'controller.logistic-logistic',



    onPreLoadForm: function( formView, form, record, asyncLoadCallback) {
    	var me = this;
        snap.getApplication().sendRequest({
            hdl: 'logistic', action: 'fillform', id: ((record && record.data) ? record.data.id : 0)
        }, 'Fetching data from server....').then(
        //Received data from server already
        function(data){
            if(data.success){

                formView.getController().lookupReference('deliverystatusdisplayfield').update(data.statushtml)

                // Condition for salesperson
                if(data.usertype == "Sale" && (record.data.status < 3 || record.data.status > 6)){

                    // Leave Blank
                    // Disables status option during pending -> packing and completed -> missing

                }else {
                    //1. Populate all the controls with tag information
                    formView.getController().lookupReference('status').getStore().loadData(data.status);
                    //formView.getController().lookupReference('deliverydate').value = data.deliverydate;
                    

                	//2.  Override the model object with our new fields data.
                	record = Ext.apply(record, data.record);
                	//We have to call this method because there are some custom fields that needs to be loaded.
                	form.setValues(data.record);
                }

            }
            //Call the callback method to continue with form showing.
            if(Ext.isFunction(asyncLoadCallback)) asyncLoadCallback(record);
            else {
            	record = Ext.apply(record, data.record);
            	form.loadRecord(record);
            }
        });
        return false;
	},

	onPostLoadEmptyForm: function( formView, form) {
        this.onPreLoadForm(formView, form, Ext.create('snap.model.Logistic', {id: 0}), null);
	},

    onPreLoadViewDetail: function(record, displayCallback) {
    	snap.getApplication().sendRequest({ hdl: 'logistic', action: 'detailview', id: record.data.id})
    	.then(function(data){
    		if(data.success) {
    			displayCallback(data.record);
    		}
    	})
        return false;
	},


    // Set text color for attempt when its maxed
    /*
    setTextColor: function(val,m,record) {
        if(record.get('status')==7) return '<span style="color:#0ead30;">' + val + '</span>';
        if(record.get('status')==8) return '<span style="color:#F42A12;">' + val + '</span>';
        if(record.get('attemps')==0) return '<span style="color:#000000;">' + val + '</span>';
        if(record.get('attemps')==1) return '<span style="color:#000000;">' + val + '</span>';
        if(record.get('attemps')==2) return '<span style="color:#000000;">' + val + '</span>';
        if(record.get('attemps')==3) return '<span style="color:#F42A12;">' + val + '</span>';
       

    },*/

    setTextColor: function(val,m,record) {
        if(record.get('status')==7) return '<span style="color:#000000;">' + val + '</span>';
        if(record.get('status')==8) return '<span style="color:#000000;">' + val + '</span>';
        if(record.get('attemps')==0) return '<span style="color:#000000;">' + val + '</span>';
        if(record.get('attemps')==1) return '<span style="color:#000000;">' + val + '</span>';
        if(record.get('attemps')==2) return '<span style="color:#000000;">' + val + '</span>';
        if(record.get('attemps')==3) return '<span style="color:#000000;">' + val + '</span>';
       

    },

    updateDeliveryStatus: function(btn, formAction) {
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

        /* ----------------------- Check  Order TYPE and Delivery Method --------------------*/

        var ordertype = selectedRecords[i].get('type');
        var vendorid = selectedRecords[i].get('vendorid');

        var userType = selectedRecords[i].get('usertype');

        var currentStatus = selectedRecords[i].get('status');
        
        var vendorvalue =  selectedRecords[i].get('vendorvalue');

        // get tag id and value 
        if("Operator" == userType){

            // Begin Ooerator
                // Check who deliver, (Ace/ Courier)
                if("CourLineClear" == vendorvalue  || "CourJ&T" == vendorvalue){
                    // Check status, if sending, enable redelivery attempt
                    if("5" == currentStatus){
                        var gridFormView = Ext.create(myView.formClass, Ext.apply(myView.formRedemption ? myView.formRedemption : {}, {
                            formDialogButtons: [{
                                xtype:'panel',
                                flex:3
                            },
                            {
                                text: 'Update Status',
                                flex: 2,
                                handler: function(btn) {
                                    me._updateStatus(btn);
                                }
                            },{
                                text: 'Reattempt Delivery',
                                flex: 2,
                                handler: function(btn) {
                                    me._updateAttempts(btn);
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
                                flex: 3.5,
                            },]
                        }));
                    }else{
                        var gridFormView = Ext.create(myView.formClass, Ext.apply(myView.formRedemption ? myView.formRedemption : {}, {
                            formDialogButtons: [{
                                xtype:'panel',
                                flex: 3,
                            },
                            {
                                text: 'Update Status',
                                flex: 2,
                                handler: function(btn) {
                                    me._updateStatus(btn);
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
                                flex:3.5
                            },]
                        }));
                    }


                } else if("CourGDEX" == vendorvalue ){
                    // For GDEX API
                    // Check status, if sending, enable redelivery attempt
                    // For delivery reattemp
                    if("5" == currentStatus){
                        var gridFormView = Ext.create(myView.formClass, Ext.apply(myView.formRedemption ? myView.formRedemption : {}, {
                            formDialogButtons: [{
                                xtype:'panel',
                                flex: 3,
                            },
                            {
                                text: 'Update Status',
                                flex: 2,
                                handler: function(btn) {
                                    me._updateStatus(btn);
                                }
                            },{
                                text: 'Reattempt Delivery',
                                flex: 2,
                                handler: function(btn) {
                                    me._updateAttempts(btn);
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
                                flex: 3.5,
                            },{
                                text: 'Print AWB',
                                flex: 1.5,
                                handler: function(btn) {
                                    // Print AWB here
                                    me.print_awb(btn);
                                }
                            }]
                        }));
                    } else if("1" == currentStatus || "2" == currentStatus){
                        // If status is pending or packed. allow pickup for GDEX API
                        var gridFormView = Ext.create(myView.formClass, Ext.apply(myView.formRedemption ? myView.formRedemption : {}, {
                            formDialogButtons: [{
                                xtype:'panel',
                                flex: 3,
                            },
                            {
                                text: 'Update Status',
                                flex: 2,
                                handler: function(btn) {
                                    me._updateStatus(btn);
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
                                flex: 3.5,
                            },{
                                text: 'Print AWB',
                                flex: 1.5,
                                handler: function(btn) {
                                    // Print AWB here
                                    me.print_awb(btn);
                                }
                            }]
                        }));
                    }
                    else{
                        // For every other situation
                        // Excluding Processing, Packed and Sending
                        var gridFormView = Ext.create(myView.formClass, Ext.apply(myView.formRedemption ? myView.formRedemption : {}, {
                            formDialogButtons: [{
                                xtype:'panel',
                                flex: 3,
                            },
                            {
                                text: 'Update Status',
                                flex: 2,
                                handler: function(btn) {
                                    me._updateStatus(btn);
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
                                flex:3.5
                            },{
                                text: 'Print AWB',
                                flex: 1.5,
                                handler: function(btn) {
                                    // Print AWB here
                                    me.print_awb(btn);
                                }
                            }]
                        }));
                    }


                }
                else{
                    // IF status is sending, enable redelivery by ace operator
                    if(currentStatus == "5"){
                        var gridFormView = Ext.create(myView.formClass, Ext.apply(myView.formRedemption ? myView.formRedemption : {}, {
                            formDialogButtons: [{
                                xtype:'panel',
                                flex: 3,
                            },{
                                text: 'Update Status',
                                flex: 2,
                                handler: function(btn) {
                                    me._updateStatus(btn);
                                }
                            },{
                                text: 'Reattempt Delivery',
                                flex: 2,
                                handler: function(btn) {
                                    me._updateAttempts(btn);
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
                                flex:3.5
                            },]
                        }));
                    }else{
                        var gridFormView = Ext.create(myView.formClass, Ext.apply(myView.formRedemption ? myView.formRedemption : {}, {
                            formDialogButtons: [{
                                xtype:'panel',
                                flex: 3,
                            },{
                                text: 'Update Status',
                                flex: 2,
                                handler: function(btn) {
                                    me._updateStatus(btn);
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
                                flex:2.5
                            },]
                        }));
                    }

                }
            
            // End Operator

        // Conditions for sales user access
        }else if("Sale" == userType){
            // Begin Sale
                // Check if GDEX courier
                if("CourGDEX" == vendorvalue || "CourLineClear" == vendorvalue || "CourJ&T" == vendorvalue){
                    // If status is not within salesman task range, display non modifiable display
                    if(currentStatus < 3 || currentStatus > 6){
                        var gridFormView = Ext.create(myView.formClass, Ext.apply(myView.formRedemption ? myView.formRedemption : {}, {
                            formDialogButtons: [{
                                xtype:'panel',
                                flex: 3,
                            },{
                                text: 'Update Status',
                                flex: 2,
                                handler: function(btn) {
                                    me._updateStatus(btn);
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
                                flex:2.5
                            },]
                        }));

                    }else {
                        // Display when status progression is within user job scope
                        // Not like it will ever be called due work flow
                        // This only exist in case of courier status showing up for sale status
                        var gridFormView = Ext.create(myView.formClass, Ext.apply(myView.formRedemption ? myView.formRedemption : {}, {
                            formDialogButtons: [{
                                xtype:'panel',
                                flex: 3,
                            },{
                                text: 'Update Status',
                                flex: 2,
                                handler: function(btn) {
                                    me._updateStatus(btn);
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
                                flex:2.5
                            },]
                        }));
                    }

                }else{
                    // Check if salesman falles within status progression scope
                    // Not in delivery process yet
                    if(currentStatus < 3 || currentStatus > 6){
                        var gridFormView = Ext.create(myView.formClass, Ext.apply(myView.formRedemption ? myView.formRedemption : {}, {
                            formDialogButtons: [{
                                xtype:'panel',
                                flex: 3,
                            },{
                                text: 'Update Status',
                                handler: function(btn) {
                                    me._updateStatus(btn);
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

                    }else {
                        // Delivery begins, from packed -> delivered

                        // If status is in transit and reattempt available
                        // Allow salesman to reattempt delivery
                        if( "5" == currentStatus){
                            var gridFormView = Ext.create(myView.formClass, Ext.apply(myView.formRedemption ? myView.formRedemption : {}, {
                                formDialogButtons: [{
                                    xtype:'panel',
                                    flex: 3,
                                },{
                                    text: 'Update Status',
                                    handler: function(btn) {
                                        me._updateStatus(btn);
                                    }
                                },{
                                    text: 'Reattempt Delivery',
                                    handler: function(btn) {
                                        me._updateAttempts(btn);
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
                        }else{
                            var gridFormView = Ext.create(myView.formClass, Ext.apply(myView.formRedemption ? myView.formRedemption : {}, {
                                formDialogButtons: [{
                                    xtype:'panel',
                                    flex: 3,
                                },{
                                    text: 'Update Status',
                                    handler: function(btn) {
                                        me._updateStatus(btn);
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
                        }

                    }



                }
            
            // End Sale

        }else{
            Ext.MessageBox.show({
                title: "ERROR",
                msg: "Not Authorized to view this page",
                buttons: Ext.MessageBox.OK,
                icon: Ext.MessageBox.WARNING
            });
        }






        /*
        var deliverymode;
        // Obtain delivery mode Information
        if (selectedRecords[i].get('vendorid') == 0){

            deliverymode = "Pre Appointment";

        } else if (selectedRecords[i].get('vendorid') == 1){

            deliverymode = "Self Delivery";

        } else if (selectedRecords[i].get('vendorid') == 2) {

            deliverymode = "Courier";

        } else {
            deliverymode = "Unidentified";
        } */
        // Acquire order data for display

        // console.log(gridFormView, myView,'asdadadsasdasdadads')
        // var logisticorderno = Ext.getCmp('logisticorderno');
        // var logisticordertype = Ext.getCmp('logisticordertype');
        // var logisticdeliverymode = Ext.getCmp('logisticdeliverymode');
        // var logisticattempts = Ext.getCmp('logisticattempts');


        // logisticorderno.setValue(selectedRecords[i].get('id'));
        // logisticordertype.setValue(selectedRecords[i].get('type'));
        // logisticdeliverymode.setValue(selectedRecords[i].get('vendorname'));
        // logisticattempts.setValue(selectedRecords[i].get('attemps'));
     
        gridFormView.controller.getView().lookupReference('logisticorderno').setValue(selectedRecords[i].get('id'));
        gridFormView.controller.getView().lookupReference('logisticordertype').setValue(selectedRecords[i].get('type'));
        gridFormView.controller.getView().lookupReference('logisticdeliverymode').setValue(selectedRecords[i].get('vendorvalue'));
        gridFormView.controller.getView().lookupReference('logisticattempts').setValue(selectedRecords[i].get('attemps'));
        
        // ************************************************************************************************************************************ //
        // Alter Redemption Form Based on Conditions
        // ************************************************************************************************************************************ //

          // get tag id and value 
          if("Operator" == userType){
            // Begin Ooerator
                // Check who deliver, (Ace/ Courier)
                if("CourLineClear" == vendorvalue  || "CourJ&T" == vendorvalue){
                    // Courier = AWB number
                    gridFormView.controller.getView().lookupReference('logisticdoawbnumber').setFieldLabel('AWB Number');


                } else if("CourGDEX" == vendorvalue ){
                    // For GDEX API
                    // Check status, if sending, enable redelivery attempt
                    // For delivery reattemp

                    // Courier = AWB number
                    gridFormView.controller.getView().lookupReference('logisticdoawbnumber').setFieldLabel('AWB Number');

                    if("1" == currentStatus || "2" == currentStatus){
                        // If status is pending or packed. allow pickup for GDEX API
                        // Pickup only for GDEX
                        gridFormView.controller.getView().lookupReference('logisticpickupbutton').setHidden(false);
                        gridFormView.controller.getView().lookupReference('logisticpickupbuttonpadding').setFlex(1);
                    }


                }
                else{
                    // For courier ACE
                    gridFormView.controller.getView().lookupReference('logisticdoawbnumber').setFieldLabel('Delivery Order No');
                }
            
            // End Operator

        // Conditions for sales user access
        }else if("Sale" == userType){

            gridFormView.controller.getView().lookupReference('logisticdoawbnumber').setFieldLabel('Delivery Order No');

            if(currentStatus < 3 || currentStatus > 6){

                // For Ace Salesman 
                // Status is view only
                gridFormView.controller.getView().lookupReference('status').setHidden(true);
                

             }


        }

        // New altering check
        // Buyback - hide attempts
        if("Buyback" != ordertype){

            // For Ace Salesman 
            // Status is view only
            gridFormView.controller.getView().lookupReference('logisticattempts').setHidden(false);

        }
        // Cour Ace = show salesman
        if("CourAce" != vendorvalue){

            gridFormView.controller.getView().lookupReference('salespersonace').setHidden(true);
        

        }

        // ************************************************************************************************************************************ //
        // End Altering
        // ************************************************************************************************************************************ //

        /*
        // Display Do number or AWB number depending on delivery type
        if("CourGDEX" == vendorname || "CourLineClear" == vendorname || "CourJ&T" == vendorname){
            // For Courier, Display AWB No
            gridFormView.controller.getView().lookupReference('logisticdoawbnumber').setFieldLabel('AWB Number');
            
            // Pickup only for GDEX
            //gridFormView.controller.getView().lookupReference('logisticpickupbutton').setHidden(true);
            //gridFormView.controller.getView().lookupReference('logisticpickupbuttonpadding').setFlex(2);

            // Only for
            //gridFormView.controller.getView().lookupReference('logisticstatus').setHidden(true);
            
            
        }else{
            // For Ace, Display D/O No
            gridFormView.controller.getView().lookupReference('logisticdoawbnumber').setFieldLabel('Delivery Order No');
        }
        */

        

        this.gridFormView = gridFormView;
        this._formAction = "edit";

        var addEditForm = this.gridFormView.down('form').getForm();

        gridFormView.title = 'Update ' + gridFormView.title + '...';
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

        this.gridFormView.show();
    },

    editDeliveryStatus: function(btn, formAction) {
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

        /* ----------------------- Check  Order TYPE and Delivery Method --------------------*/

        var ordertype = selectedRecords[i].get('type');
        var vendorid = selectedRecords[i].get('vendorid');

        var userType = selectedRecords[i].get('usertype');

        var currentStatus = selectedRecords[i].get('status');
        
        var vendorvalue =  selectedRecords[i].get('vendorvalue');

        // get tag id and value 
        var gridFormView = Ext.create(myView.formClass, Ext.apply(myView.formRedemption ? myView.formRedemption : {}, {
            formDialogButtons: [{
                xtype:'panel',
                flex:2
            },
            {
                text: 'Save',
                flex: 2,
                handler: function(btn) {
                    me._onSaveGridForm(btn);
                }
            },
            {
                xtype:'panel',
                flex: 0.5,
            },{
                text: 'Close',
                flex: 2,
                handler: function(btn) {
                    owningWindow = btn.up('window');
                    owningWindow.close();
                    me.gridFormView = null;
                }
            },
            {
                xtype:'panel',
                flex: 2,
            },]
        }));






        /*
        var deliverymode;
        // Obtain delivery mode Information
        if (selectedRecords[i].get('vendorid') == 0){

            deliverymode = "Pre Appointment";

        } else if (selectedRecords[i].get('vendorid') == 1){

            deliverymode = "Self Delivery";

        } else if (selectedRecords[i].get('vendorid') == 2) {

            deliverymode = "Courier";

        } else {
            deliverymode = "Unidentified";
        } */
        // Acquire order data for display

        // console.log(gridFormView, myView,'asdadadsasdasdadads')
        // var logisticorderno = Ext.getCmp('logisticorderno');
        // var logisticordertype = Ext.getCmp('logisticordertype');
        // var logisticdeliverymode = Ext.getCmp('logisticdeliverymode');
        // var logisticattempts = Ext.getCmp('logisticattempts');


        // logisticorderno.setValue(selectedRecords[i].get('id'));
        // logisticordertype.setValue(selectedRecords[i].get('type'));
        // logisticdeliverymode.setValue(selectedRecords[i].get('vendorname'));
        // logisticattempts.setValue(selectedRecords[i].get('attemps'));
     
        gridFormView.controller.getView().lookupReference('logisticorderno').setValue(selectedRecords[i].get('id'));
        gridFormView.controller.getView().lookupReference('logisticordertype').setValue(selectedRecords[i].get('type'));
        gridFormView.controller.getView().lookupReference('logisticdeliverymode').setValue(selectedRecords[i].get('vendorvalue'));
        gridFormView.controller.getView().lookupReference('logisticattempts').setValue(selectedRecords[i].get('attemps'));
        
        // ************************************************************************************************************************************ //
        // Alter Redemption Form Based on Conditions
        // ************************************************************************************************************************************ //

          // get tag id and value 
          if("Operator" == userType){
            // Begin Ooerator

                // Set view only for status
                gridFormView.controller.getView().lookupReference('status').setHidden(true);

                // Check who deliver, (Ace/ Courier)
                if("CourLineClear" == vendorvalue  || "CourJ&T" == vendorvalue){
                    // Courier = AWB number
                    gridFormView.controller.getView().lookupReference('logisticdoawbnumber').setFieldLabel('AWB Number');


                } else if("CourGDEX" == vendorvalue ){
                    // For GDEX API
                    // Check status, if sending, enable redelivery attempt
                    // For delivery reattemp

                    // Courier = AWB number
                    gridFormView.controller.getView().lookupReference('logisticdoawbnumber').setFieldLabel('AWB Number');

                    if("1" == currentStatus || "2" == currentStatus){
                        // If status is pending or packed. allow pickup for GDEX API
                        // Pickup only for GDEX
                        gridFormView.controller.getView().lookupReference('logisticpickupbutton').setHidden(false);
                        gridFormView.controller.getView().lookupReference('logisticpickupbuttonpadding').setFlex(1);
                    }


                }
                else{
                    // For courier ACE
                    gridFormView.controller.getView().lookupReference('logisticdoawbnumber').setFieldLabel('Delivery Order No');
                }
            
            // End Operator

        // Conditions for sales user access
        }else if("Sale" == userType){

            gridFormView.controller.getView().lookupReference('logisticdoawbnumber').setFieldLabel('Delivery Order No');

            // Set view only for status
            gridFormView.controller.getView().lookupReference('status').setHidden(true);


        }

        
        // New altering check
        // Buyback - hide attempts
        if("Buyback" != ordertype){

            // For Ace Salesman 
            // Status is view only
            gridFormView.controller.getView().lookupReference('logisticattempts').setHidden(false);

        }
        // Cour Ace = show salesman
        if("CourAce" != vendorvalue){

            gridFormView.controller.getView().lookupReference('salespersonace').setHidden(true);
        

        }
        
        // ************************************************************************************************************************************ //
        // End Altering
        // ************************************************************************************************************************************ //

        /*
        // Display Do number or AWB number depending on delivery type
        if("CourGDEX" == vendorname || "CourLineClear" == vendorname || "CourJ&T" == vendorname){
            // For Courier, Display AWB No
            gridFormView.controller.getView().lookupReference('logisticdoawbnumber').setFieldLabel('AWB Number');
            
            // Pickup only for GDEX
            //gridFormView.controller.getView().lookupReference('logisticpickupbutton').setHidden(true);
            //gridFormView.controller.getView().lookupReference('logisticpickupbuttonpadding').setFlex(2);

            // Only for
            //gridFormView.controller.getView().lookupReference('logisticstatus').setHidden(true);
            
            
        }else{
            // For Ace, Display D/O No
            gridFormView.controller.getView().lookupReference('logisticdoawbnumber').setFieldLabel('Delivery Order No');
        }
        */

        

        this.gridFormView = gridFormView;
        this._formAction = "edit";

        var addEditForm = this.gridFormView.down('form').getForm();

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

        this.gridFormView.show();
    },

    onGridItemDoubleClicked: function(view, record, item, index, e, eOpts) {
        if (this.getView().gridEnableCellEditing) return true;
        if (this.getView().enableDetailView) this.showDetails();
    },

    onAdd: function(btn) {
        this._onAddEdit(btn, 'add');
    },

    onEdit: function(btn) {
        this._onAddEdit(btn, 'edit');
    },

    assignAceSalesman: function(record) {
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
			],						
        });
        
        var type=selectedRecords[0].get('vendorvalue');     
        if (type=='CourAce') {
            var type=selectedRecords[0].get('vendorvalue');            
            var salesmaninputform = new Ext.Window({
                title: 'Assign Salesman for Logistics..',
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
                                params: { hdl: 'logistic', action: 'updateAceSalesmanToDelivery' },
                                waitMsg: 'Processing',
                                success: function(frm, action) { //success                                   
                                    Ext.MessageBox.show({
                                        title: 'Assigning salesman..',
                                        msg: 'Successfully Assigned',
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
                                        errmsg = 'Error in form: ' + action.result.errorMessage;
                                    }                                   
                                    Ext.MessageBox.show({
                                        title: 'Message',
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

            if(type=='CourAce'){
                salesmaninputform.show();
            }else if(vendorvalue == "CourGDEX" || vendorvalue == "CourLineClear" || vendorvalue == "CourJ&T"){
                Ext.MessageBox.show({
                    title: 'Error Message',
                    msg: 'This is for Ace delivery only',
                    buttons: Ext.MessageBox.OK,
                    icon: Ext.MessageBox.ERROR
                });
            }
           
           
         }else{
            Ext.MessageBox.show({
                title: 'Warning', buttons: Ext.MessageBox.OK, icon: Ext.MessageBox.WARNING,
                msg: 'Please select Ace Delivery request'});
         }        
    },


    _onSaveGridForm: function(btn) {
        // var owningWindow = btn.up('window');
        // var gridFormPanel = owningWindow.down('form');
        var me = this,
            myView = this.getView(),
            addEditForm = this.gridFormView.down('form').getForm();
        if (addEditForm.isValid()) {
            btn.disable();
            if( Ext.isFunction(me['onPreAddEditSubmit']) && !this.onPreAddEditSubmit(this._formAction, me.gridFormView, addEditForm)) {
                btn.enable();
                return;
            }

            if(addEditForm.getValues().senderid){
                senderid = addEditForm.getValues().senderid;
            }else{
                senderid = 0;
            }

            addEditForm.submit({
                submitEmptyText: false,
                url: 'index.php',
                method: 'POST',
                params: { hdl: 'logistic', action: 'updateLogisticInformation',
                          deliverydate: addEditForm.getValues().deliverydate,
                          awbno: addEditForm.getValues().awbno,
                          remarks: addEditForm.getValues().remarks,
                          senderid: senderid,
                        },
                //params: { hdl: myView.getStore().getModel().entityName.toLowerCase(), action: this._formAction },
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

    print_awb: function (btn){
        xcid = btn.up('window').controller.getView().lookupReference('logisticorderno').getValue();
        if (!xcid){
            Ext.MessageBox.show({
                title: 'Error Message',
                msg: 'Invalid AWB No.',
                buttons: Ext.MessageBox.OK,
                icon: Ext.MessageBox.ERROR
            });
        }
        url = '?hdl=logistic&action=printawb&id='+xcid;
        return Ext.DomHelper.append(Ext.getBody(), {
            tag: 'iframe',
            // id:'downloadIframe',
            frameBorder: 0,
            width: 0,
            height: 0,
            css: 'display:none;visibility:hidden;height: 0px;',
            src: url
          });
    },

    print_awb_example_download: function(btn){
        // var form = Ext.create('Ext.form.Panel', { // this wolud be your form 
        //     standardSubmit: true,         // this is the important part 
        //     url: url
        // });
        // // form.submit({target: '_blank',});
        // form.submit();
        // Ext.defer(function(){
        //     form.close();
        // }, 100);

        // return Ext.DomHelper.append(Ext.getBody(), {
        //     tag: 'iframe',
        //     // id:'downloadIframe',
        //     frameBorder: 0,
        //     width: 0,
        //     height: 0,
        //     css: 'display:none;visibility:hidden;height: 0px;',
        //     src: url
        //   });
    },
    _updateStatus: function(btn) {
        // var owningWindow = btn.up('window');
        // var gridFormPanel = owningWindow.down('form');
        var me = this,
            myView = this.getView(),
            addEditForm = this.gridFormView.down('form').getForm();
        if (addEditForm.isValid()) {
            btn.disable();
            if( Ext.isFunction(me['onPreAddEditSubmit']) && !this.onPreAddEditSubmit(this._formAction, me.gridFormView, addEditForm)) {
                btn.enable();
                return;
            }

            // Check if pickup is empty
            if (addEditForm.getFieldValues().isPickup != null){
                pickup = addEditForm.getFieldValues().isPickup;
            }else {
                pickup = "";
            } 

            if(addEditForm.getValues().senderid){
                senderid = addEditForm.getValues().senderid;
            }else{
                addEditForm.getValues().senderid = 0;
                senderid = 0;
            }
            addEditForm.submit({
                submitEmptyText: false,
                url: 'gtp.php',
                method: 'POST',
                dataType: "json",
                //params: { hdl: myView.getStore().getModel().entityName.toLowerCase(), action: this._formAction },
                params: { hdl: 'logistic', action: 'updateLogisticStatus',
                          deliverydate: addEditForm.getValues().deliverydate,
                          awbno: addEditForm.getValues().awbno,
                          remarks: addEditForm.getValues().remarks,
                          senderid: senderid,
                          isPickup: pickup,
                        },
                waitMsg: 'Processing',
                success: function(frm, action){ //success
                    
                    Ext.MessageBox.show({
                        title: 'Status Updated',
                        msg: 'Sent Successfully',
                        buttons: Ext.MessageBox.OK,
                        icon: Ext.MessageBox.INFO
                    });
                    btn.disable();
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

    // Update Attempts
    _updateAttempts: function(btn) {
        // var owningWindow = btn.up('window');
        // var gridFormPanel = owningWindow.down('form');
        var me = this,
            myView = this.getView(),
            addEditForm = this.gridFormView.down('form').getForm();
        if (addEditForm.isValid()) {
            btn.disable();
            if( Ext.isFunction(me['onPreAddEditSubmit']) && !this.onPreAddEditSubmit(this._formAction, me.gridFormView, addEditForm)) {
                btn.enable();
                return;
            }

            Ext.MessageBox.confirm('Confirm', 'Reattempting delivery?', function(id) {
                if (id == 'yes') {
                    addEditForm.submit({
                        submitEmptyText: false,
                        url: 'gtp.php',
                        method: 'POST',
                        dataType: "json",
                        //params: { hdl: myView.getStore().getModel().entityName.toLowerCase(), action: this._formAction },
                        params: { hdl: 'logistic', action: 'updateLogisticAttempts' },
                        waitMsg: 'Processing',
                        success: function(form, action){ //success

                            if (action.result.success){
                                res_msg = 'Sent Successfully';
                            }else{
                                res_msg = action.result.error_message;
                            }
                            Ext.MessageBox.show({
                                title: 'Reattempting delivery',
                                msg: res_msg,
                                buttons: Ext.MessageBox.OK,
                                icon: Ext.MessageBox.INFO
                            });

                            btn.disable();
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
                }
            }, this);
        } else {
            Ext.MessageBox.show({
                title: 'Error Message',
                msg: 'Error in the Form',
                buttons: Ext.MessageBox.OK,
                icon: Ext.MessageBox.ERROR
            });
        }
    },

    minimizeGridColumnGTP: function(btn) {
        
        Ext.getCmp('gtplogisticgrid').columnManager.getColumns()[5].hide();
        Ext.getCmp('gtplogisticgrid').columnManager.getColumns()[6].hide();
        Ext.getCmp('gtplogisticgrid').columnManager.getColumns()[7].hide();
        Ext.getCmp('gtplogisticgrid').columnManager.getColumns()[8].hide();
        Ext.getCmp('gtplogisticgrid').columnManager.getColumns()[9].hide();
        Ext.getCmp('gtplogisticgrid').columnManager.getColumns()[10].hide();
        Ext.getCmp('gtplogisticgrid').columnManager.getColumns()[11].hide();
        Ext.getCmp('gtplogisticgrid').columnManager.getColumns()[12].hide();
        Ext.getCmp('gtplogisticgrid').columnManager.getColumns()[13].hide();
        Ext.getCmp('gtplogisticgrid').columnManager.getColumns()[14].hide();
        Ext.getCmp('gtplogisticgrid').columnManager.getColumns()[15].hide();


    },

    expandGridColumnGTP: function(btn)  {

        Ext.getCmp('gtplogisticgrid').columnManager.getColumns()[5].show();
        Ext.getCmp('gtplogisticgrid').columnManager.getColumns()[6].show();
        Ext.getCmp('gtplogisticgrid').columnManager.getColumns()[7].show();
        Ext.getCmp('gtplogisticgrid').columnManager.getColumns()[8].show();
        Ext.getCmp('gtplogisticgrid').columnManager.getColumns()[9].show();
        Ext.getCmp('gtplogisticgrid').columnManager.getColumns()[10].show();
        Ext.getCmp('gtplogisticgrid').columnManager.getColumns()[11].show();
        Ext.getCmp('gtplogisticgrid').columnManager.getColumns()[12].show();
        Ext.getCmp('gtplogisticgrid').columnManager.getColumns()[13].show();
        Ext.getCmp('gtplogisticgrid').columnManager.getColumns()[14].show();
        Ext.getCmp('gtplogisticgrid').columnManager.getColumns()[15].show();
    },

    minimizeGridColumnMIB: function(btn) {
        Ext.getCmp('miblogisticgrid').columnManager.getColumns()[5].hide();
        Ext.getCmp('miblogisticgrid').columnManager.getColumns()[6].hide();
        Ext.getCmp('miblogisticgrid').columnManager.getColumns()[7].hide();
        Ext.getCmp('miblogisticgrid').columnManager.getColumns()[8].hide();
        Ext.getCmp('miblogisticgrid').columnManager.getColumns()[9].hide();
        Ext.getCmp('miblogisticgrid').columnManager.getColumns()[10].hide();
        Ext.getCmp('miblogisticgrid').columnManager.getColumns()[11].hide();
        Ext.getCmp('miblogisticgrid').columnManager.getColumns()[12].hide();
        Ext.getCmp('miblogisticgrid').columnManager.getColumns()[13].hide();
        Ext.getCmp('miblogisticgrid').columnManager.getColumns()[14].hide();
        Ext.getCmp('miblogisticgrid').columnManager.getColumns()[15].hide();


    },

    expandGridColumnMIB: function(btn)  {

        Ext.getCmp('miblogisticgrid').columnManager.getColumns()[5].show();
        Ext.getCmp('miblogisticgrid').columnManager.getColumns()[6].show();
        Ext.getCmp('miblogisticgrid').columnManager.getColumns()[7].show();
        Ext.getCmp('miblogisticgrid').columnManager.getColumns()[8].show();
        Ext.getCmp('miblogisticgrid').columnManager.getColumns()[9].show();
        Ext.getCmp('miblogisticgrid').columnManager.getColumns()[10].show();
        Ext.getCmp('miblogisticgrid').columnManager.getColumns()[11].show();
        Ext.getCmp('miblogisticgrid').columnManager.getColumns()[12].show();
        Ext.getCmp('miblogisticgrid').columnManager.getColumns()[13].show();
        Ext.getCmp('miblogisticgrid').columnManager.getColumns()[14].show();
        Ext.getCmp('miblogisticgrid').columnManager.getColumns()[15].show();
    },

    printButton: function(btn)  {
        // Add print function here 
        var myView = this.getView(),
            me = this, record;
        var sm = myView.getSelectionModel();        
        var selectedRecords = sm.getSelection();     
        var address=selectedRecords[0].data.address1+' '+selectedRecords[0].data.address2+' '+selectedRecords[0].data.address3;
        
        var record = selectedRecords[0].data;
        
        //debugger;
       //url = '?hdl=logistic&action=documentHTML';
        //url = '?hdl=logistic&action=documentHTML&list='+list+'&address='+address+'&addressFrom='+addressFrom+'&addressTo='+addressTo;
        // url = Ext.urlEncode(url);

        var url = 'index.php?hdl=logistic&action=getPrintDocuments&id='+record.id;
        Ext.Ajax.request({
            url: url,
            method: 'get',
            waitMsg: 'Processing',
            //params: { summaryfromdate: summaryfromdate, summarytodate: summarytodate, summarytype: summarytype },
            autoAbort: false,
            success: function (result) {
                var win = window.open('');
                    win.location = url;
                    win.focus();
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

        // var url = 'index.php?hdl=logistic&action=getPrintDocuments';
        // Ext.DomHelper.append(document.body, {
        //     tag: 'iframe',
        //     id:'downloadIframe',
        //     frameBorder: 0,
        //     width: 0,
        //     height: 0,
        //     css: 'display:none;visibility:hidden;height: 0px;',
        //     src: url
        // });

        // MY CODE -- START
        var selectedRecords = sm.getSelection(); 
        // console.log(selectedRecords);return;
        var record = selectedRecords[0].data;

        snap.getApplication().sendRequest({
            hdl: 'logistic', action: 'getPrintDocuments', id: record.id, recordType: record.type,
        }, 'Fetching data from server....').then(
        //Received data from server already
        function(data){
            if(data.success){
                
            }
        });
        return false;
        // MY CODE -- END
    },

    /*
    onConfirmPickup: function (elemnt) {
        var form = elemnt.up().lookupController().lookupReference('logisticredemptionpickup-form').getForm();
			
        if (form.isValid()) {

            logisticRedemption = form.getFieldValues();

            // Initialize Variables
            awbno = logisticRedemption.awbno;
            deliveryDate = Date.parse(logisticRedemption.deliverydate);				
            deliveryMode = logisticRedemption.deliverymode;
            orderNo = logisticRedemption.orderno;
            remarks = logisticRedemption.remarks;
            
        } else {
                Ext.MessageBox.show({
                title: "Error Notification",
                msg: "Please fill the required fields correctly.",
                buttons: Ext.MessageBox.OK,
                icon: Ext.MessageBox.WARNING
            });
        }
			
		
			
		//} else {
		//	Ext.toast('Form is invalid, please correct the errors.');
		//}
    },
    */
    getShipmentStatus: function(record) {
        var myView = this.getView(),
            me = this, record;
        var sm = myView.getSelectionModel();        
        var selectedRecords = sm.getSelection();

        // if (selectedRecords.)
        var record = selectedRecords[0];

        snap.getApplication().sendRequest({
            hdl: 'logistic', action: 'getShipmentStatus', id: ((record && record.data) ? record.data.id : 0)
        }, 'Fetching data from server....').then(
        //Received data from server already
        function(data){
            if(data.success){
                var propWin = new Ext.Window({
                    title: 'Status' + ' ...',
                    //layout: 'fit',
                    bodyPadding: 10,
                    modal: true,
                    width: 500,
                    height: 500,
                    closeAction: 'close',
                    // bodyPadding: 0,
                    // bodyBorder: false,
                    // bodyStyle: {
                    //     background: '#FFFFFF'
                    // },
                    maximizable: true,
                    plain: false,
                    scrollable: 'vertical',
                    
                    html: data.html,
                    buttons: [{
                        text: 'Close',
                        buttonAlign: 'center',
                        handler: function() {
                            propWin.close();
                        }
                    }]
                });
                propWin.show();
                
            }else {
                Ext.MessageBox.show({
                    title: 'Warning',
                    buttons: Ext.MessageBox.OK,
                    icon: Ext.MessageBox.WARNING,
                    msg: 'Select a record first'
                });
            }
        })        
    },

    createGdexPickupDatetime: function(btn, formAction) {
        myView = this.getView();
        me = this;
        var gridFormView = Ext.create(myView.formClass, Ext.apply(myView.formGdexPickup ? myView.formGdexPickup : {}, {
            formDialogButtons: [{
                xtype:'panel',
                flex:1
            },
            {
                text: 'Update Delivery Details',
                flex: 2.5,
                handler: function(btn) {
                    me._onSaveGridForm(btn);
                }
            },{
                text: 'Close',
                flex: 1,
                handler: function(btn) {
                    owningWindow = btn.up('window');
                    owningWindow.close();
                    me.gridFormView = null;
                }
            }]
        }));

        this.gridFormView = gridFormView;
        this._formAction = "edit";

        var addEditForm = this.gridFormView.down('form').getForm();

        gridFormView.title = 'Update ' + gridFormView.title + '...';
        
        this.gridFormView.show();
    },


    getShipmentDetails: function(record) {
        var myView = this.getView(),
            me = this, record;
        var sm = myView.getSelectionModel();        
        var selectedRecords = sm.getSelection();

        // if (selectedRecords.)
        var record = selectedRecords[0];

        snap.getApplication().sendRequest({
            hdl: 'logistic', action: 'getShipmentDetails', id: ((record && record.data) ? record.data.id : 0)
        }, 'Fetching data from server....').then(
        //Received data from server already
        function(data){
            if(data.success){
                var propWin = new Ext.Window({
                    title: 'Status' + ' ...',
                    //layout: 'fit',
                    bodyPadding: 10,
                    modal: true,
                    width: 500,
                    height: 500,
                    closeAction: 'close',
                    // bodyPadding: 0,
                    // bodyBorder: false,
                    // bodyStyle: {
                    //     background: '#FFFFFF'
                    // },
                    maximizable: true,
                    plain: false,
                    scrollable: 'vertical',
                    
                    html: data.html,
                    buttons: [{
                        text: 'Close',
                        buttonAlign: 'center',
                        handler: function() {
                            propWin.close();
                        }
                    }]
                });
                propWin.show();
                
            }else {
                Ext.MessageBox.show({
                    title: 'Warning',
                    buttons: Ext.MessageBox.OK,
                    icon: Ext.MessageBox.WARNING,
                    msg: 'Select a record first'
                });
            }
        })        
    },

    getlatestcourierstatus: function(elemnt) {
        var myView = this.getView(),
            me = this, elemnt;
        var sm = myView.getSelectionModel();        
        var selectedRecords = sm.getSelection();

        var record = selectedRecords[0];
        
        snap.getApplication().sendRequest({
            hdl: 'logistic', action: 'callCourierCrawler', id: ((record && record.data) ? record.data.id : 0)
        }, 'Fetching data from server....').then(
        //Received data from server already
        function(data){
            if(data.success){
                Ext.MessageBox.show({
                    title: 'Success',
                    buttons: Ext.MessageBox.OK,
                    icon: Ext.MessageBox.INFO,
                    msg: 'Updated with third party courier status'
                });
                
            }else {
                Ext.MessageBox.show({
                    title: 'Warning',
                    buttons: Ext.MessageBox.OK,
                    icon: Ext.MessageBox.WARNING,
                    msg: 'icon: Ext.MessageBox.INFO'
                });
            }
        })        
    },

});