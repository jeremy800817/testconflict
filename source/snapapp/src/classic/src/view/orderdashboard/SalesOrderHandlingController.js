Ext.define('snap.view.orderdashboard.SalesOrderHandlingController', {
    extend: 'snap.view.gridpanel.BaseController',
    alias: 'controller.salesorderhandling-salesorderhandling',


    onPreLoadViewDetail: function(record, displayCallback) {
        snap.getApplication().sendRequest({ hdl: 'order', action: 'detailview', id: record.data.id})
        .then(function(data){
            if(data.success) {
                displayCallback(data.record);
            }
        })
        return false;
    },

    sendToSAP: function(btn, formAction){
       
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
       
        // Grab Name for partnerrefid
        vendorcodes = vms.get('apicodesvendor');
        customercodes = vms.get('apicodescustomer');
       
        if (selectedRecord.data.type == 'CompanyBuy'){
            buyorselltypelabel = 'Buy from Customer';
            vendorcode = vendorcodes.find(x => x.id === selectedRecord.data.partnerrefid);
            if (vendorcode != undefined) {
                buyorsellvalue = vendorcode.name;
			} else {
				 Ext.MessageBox.show({
					title: "ERROR-A1001",
					msg: "Vendor Code is incorrect",
					buttons: Ext.MessageBox.OK,
					icon: Ext.MessageBox.WARNING
                });
                return;
			}
        }else if (selectedRecord.data.type == 'CompanySell'){
            buyorselltypelabel = 'Sell to Customer';
            customercode = customercodes.find(x => x.id === selectedRecord.data.partnerrefid);         
            if (customercode != undefined) {
                buyorsellvalue = customercode.name;
			} else {
				 Ext.MessageBox.show({
					title: "ERROR-A1001",
					msg: "Customer Code is incorrect",
					buttons: Ext.MessageBox.OK,
					icon: Ext.MessageBox.WARNING
                });
                return;
			}
        }else {
            Ext.getCmp('buyorselltosaporderhandling').setHidden(true);
        }
        
            // Filter Status 
            if (selectedRecord.data.status == 0){
                orderstatus = 'Pending';
            }else if(selectedRecord.data.status == 1){
                orderstatus = 'Confirmed';
            }else if(selectedRecord.data.status == 2){
                orderstatus = 'Pending Payment';
            }else if(selectedRecord.data.status == 3){
                orderstatus = 'Pending Cancel';
            }else if(selectedRecord.data.status == 4){
                orderstatus = 'Cancelled';
            }else if(selectedRecord.data.status == 5){
                orderstatus = 'Completed';
            }else if(selectedRecord.data.status == 6){
                orderstatus = 'Expired';
            }else {
                orderstatus = 'Unassigned';
            }
            
        
        // Order Handling window
        // Start SAP Form
       // Spot Panel for Total Xau Weight 
       var orderhandlingpanel = new Ext.form.Panel({			
            frame: true,
            layout: 'column',
            defaults: {
                columnWidth: .5,                
            },    
            reference: 'orderhandling-confirmation',     
            border: 0,
            bodyBorder: false,
            bodyPadding: 10,
            listeners:{

                beforerender:function(cmp){

                
                /*
                    var innerform = cmp.down().down().items;
                    //innerform.items[4].setHidden(true)

                    input = form.getFields();
                    for (index = 0; index <input.length; index++ ){
                        if(input.items[index].value == ""){

                            //(input.items[index].id);
                            itemname = input.items[index].id + "display";

                            // One without any display / hiding displays
                            if(input.items[index].id == 'totalvaluefuturedashboard' || input.items[index].id == 'totalxauweightfuturedashboard'){

                            }else {
                                Ext.getCmp(itemname).setHidden(true);
                            }
                            
                            
                        } 
                    }
                */

                }
            },
            items: [
                {
                    columnWidth: 0.48,
                    items: [
                        { xtype: 'hidden', hidden: true, name: 'id' },
                        { xtype: 'displayfield', fieldLabel:  'GTP Ref#', value: selectedRecord.data.id , name: 'GTP Ref#' },
                        { xtype: 'displayfield', fieldLabel: 'Booking Number', value: selectedRecord.data.orderno , name: 'Booking Number', },
                        { xtype: 'displayfield', fieldLabel: 'XAU Weight (g)', value: selectedRecord.data.xau , name: 'XAU Weight (g)', },
                        { xtype: 'displayfield', fieldLabel: 'Price (RM/g)', value: selectedRecord.data.price , name: 'Price (RM/g)', },
                        { xtype: 'displayfield', id: 'buyorselltosaporderhandling', fieldLabel: buyorselltypelabel, value: buyorsellvalue , name: 'buyorsell', },
                        { xtype: 'displayfield', fieldLabel: 'Product Type', value: selectedRecord.data.productname , name: 'productname', },
                       
                      
                    ]
                },
                {
                    columnWidth: 0.48,
                    items: [
                        { xtype: 'displayfield', fieldLabel:  'Status', value: orderstatus,  name: 'status' },
                        { xtype: 'textarea', fieldLabel: 'Remarks', value: '' , name: 'remarks', },
                        { xtype: 'displayfield', fieldLabel: 'Gross Value (RM)', value: selectedRecord.data.amount , name: 'grossvalue', },
                        { xtype: 'displayfield', fieldLabel: 'Contact Phone', value: '' , name: 'contactno', },
                       
                      
                    ]
                },
                
            ]	
        });



        var windowfororderhandling = new Ext.Window({
            title: 'Sales Order Handling',
            layout: 'fit',
            width: 850,
            maxHeight: 700,
            modal: true,
            plain: true,
            buttonAlign: 'center',
            buttons: [{
                text: 'Submit',
                handler: function(btn) {
                    if (orderhandlingpanel.getForm().isValid()) {
                        btn.disable();
                        orderhandlingpanel.getForm().submit({
                            submitEmptyText: false,
                            url: 'gtp.php',
                            method: 'POST',
                            dataType: "json",
                            params: { hdl: 'order', action: 'sendToSap', 
                                        gtprefno: selectedRecord.data.id, 
                                        bookingno: selectedRecord.data.orderno, 
                                        xauweight:  selectedRecord.data.xau,
                                        price: selectedRecord.data.price,
                                        code: selectedRecord.data.partnerrefid,
                                        name: buyorsellvalue,
                                        buyorsell : selectedRecord.data.type,
                                        product: selectedRecord.data.productname,
                                        remarks: orderhandlingpanel.getForm().getFieldValues().remarks,
                                        grossvalue: selectedRecord.data.amount,
                                        productid :selectedRecord.data.productid,
                                    },
                            waitMsg: 'Processing',
                            success: function(frm, action) { //success                                   
                                windowforordercomplete.show();
                                owningWindow = btn.up('window');
                                //owningWindow.closeAction='destroy';
                                owningWindow.close();
                                myView.getStore().reload();
                            },
                            failure: function(frm,action) {
                                btn.enable();                                    
                                var errmsg = action.result.errorMessage;
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
                                    //windowforordercomplete.show();
                                    errmsg = action.result.errorMessage;
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
                    //owningWindow.closeAction='destroy';
                    owningWindow.close();
                }
            }],
            listeners:{
                close:function(win) {
                    /*
                    // Reenable hidden components
                    Ext.getCmp('productfuturedisplay').setHidden(false);
                    Ext.getCmp('totalxauweightfuturedashboarddisplay').setHidden(false);
                    Ext.getCmp('acebuypricefuturedashboarddisplay').setHidden(false);
                    Ext.getCmp('acesellpricefuturedashboarddisplay').setHidden(false);
                    
                    // Clear cmp
                    Ext.getCmp('productfuturedisplay').destroy();
                    Ext.getCmp('totalxauweightfuturedashboarddisplay').destroy();
                    Ext.getCmp('acebuypricefuturedashboarddisplay').destroy();
                    Ext.getCmp('acesellpricefuturedashboarddisplay').destroy();
                    */
                }
            },
            closeAction: 'destroy',
            items: orderhandlingpanel
        });

        windowfororderhandling.show();
        /*snap.getApplication().sendRequest({ hdl: 'order', action: 'sendToSAP',})
        .then(function(data){
            if(data.success) {
                
            }
        })*/
    }
});
