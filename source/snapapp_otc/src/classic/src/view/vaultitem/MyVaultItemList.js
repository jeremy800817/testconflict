Ext.define('snap.view.vaultitem.MyVaulItemList', {
    extend: 'snap.view.vaultitem.vaultitemList',
    xtype: 'myvaultitemview',
    requires: [
        'snap.store.MyVaultItem',
        'snap.model.MyVaultItem',
        'snap.view.vaultitem.vaultitemController',
        'snap.view.vaultitem.vaultitemModel'
    ],
    gridSelectionMode: 'MULTI',
    allowDeselect:true,
    store: { type: 'MyVaultItem'},
    // permissionRoot: '/root/bmmb/vault',
    enableFilter: true,
    toolbarItems: [
        'detail','filter', '|',
        /*
        { reference: 'reqtransferbutton',text: 'Transfer Item', itemId: 'transferitem',iconCls: 'fa fa-arrows-alt-h', handler: 'myRequestTransfer', validSelection: 'multiple' },
        //{ reference: 'reqtransferbutton',text: 'Transfer Item', itemId: 'transferitem',iconCls: 'fa fa-arrows-alt-h', handler: 'showTransferForm', validSelection: 'single' },
        { reference: 'confirmtransferbutton', text: 'Confirm Transfer', itemId: 'confirmtransfer', iconCls: 'x-fa fa-check-square', handler: 'confirmTransferOrReturn', validSelection: 'single' },
        '|',
        { reference: 'cancelreqtransferbutton', text: 'Cancel Request Transfer', itemId: 'cancelreqtransfer', iconCls: 'x-fa fa-stop-circle', handler: 'cancelTransfer', validSelection: 'single' },
        '|',
        { reference: 'returnbutton', itemId: 'returnitem', text: 'Return Item', iconCls: 'fa fa-reply', handler: 'returnItem', validSelection: 'single' },
        '|',
        { reference: 'activatebutton', text: 'Activate Item For Transfer', itemId: 'activateitem', iconCls: 'x-fa fa-plus-circle', handler: 'requestActivateItemForTransfer', validSelection: 'single' },
        { reference: 'approvependingbutton', text: 'Approve Pending Items For Transfer', itemId: 'approvependingitem', iconCls: 'x-fa fa-thumbs-up', handler: 'approvePendingItemForTransfer', validSelection: 'single' },
        '|',
        { reference: 'printGoldBarListButton', text: 'Print Gold Bar List', itemId: 'printGoldBarListButton', tooltip: 'Print Gold Bar List', iconCls: 'x-fa fa-file', handler: 'printGoldBarListButton'},
        {
            text: 'Download',tooltip: 'Download Vault LIst',iconCls: 'x-fa fa-download', reference: 'exportbutton', handler: 'exportVaultListButton',  showToolbarItemText: true, printType: 'xlsx', // printType: pending
        },
        '|',
        { reference: 'printButton', text: 'Print Document', itemId: 'printButton', tooltip: 'Print Documents', iconCls: 'x-fa fa-print', handler: 'printDocumentSelection', },
        { reference: 'createPrintButton', text: 'Create Document', itemId: 'createPrintButton', tooltip: 'Print Documents', iconCls: 'x-fa fa-book', handler: 'createPrintButton', },
        //{ reference: 'transferbuttonbmmb', itemId: 'transferbuttonbmmb', text: 'Return Item', iconCls: 'fa fa-reply', handler: 'returnItemBmmb', validSelection: 'single' },
        */
    ],

    listeners: {       
        cellclick: function (view, cell, cellIndex, record, row, rowIndex, e) {   
            var me = this;
            me.checkActionPermission(view, record);
        },
        beforeitemkeyup: function (view, record, item, index, e) {            
            var me = this;
            me.checkActionPermission(view, record);
        },
        // afterrender: function(store) {            
        //     var originType = this.up().type;
        //     var rpermission = '/root/' + originType + '/vault/return';
        //     var apermission = '/root/' + originType + '/vault/approve';

        //     // Initialize toolbar
        //     var toolbar = this.dockedItems.items[2];

        //     // Create toolbar ids
        //     var transferitemid = originType + "transferitem";
        //     var confirmtransferid = originType + "confirmtransfer";
        //     var returnitemid = originType + "returnitem";
        //     var cancelreqtransferid = originType + "cancelreqtransfer";
        //     var activateitemid = originType + "activateitem";
        //     var approvependingitemid = originType + "approvependingitem";
            
        //     // Add panels to toolbar
        //     toolbar.add(
        //         { reference: 'reqtransferbutton', itemId: transferitemid, iconCls: 'fa fa-arrows-alt-h', handler: 'myRequestTransfer', validSelection: 'multiple' },
        //         //{ reference: 'reqtransferbutton',text: 'Transfer Item', itemId: 'transferitem',iconCls: 'fa fa-arrows-alt-h', handler: 'showTransferForm', validSelection: 'single' },
        //         { reference: 'confirmtransferbutton', itemId: confirmtransferid, iconCls: 'x-fa fa-check-square', handler: 'confirmTransferOrReturn', validSelection: 'single' },
        //         // '|',
        //         { reference: 'cancelreqtransferbutton', itemId: cancelreqtransferid, iconCls: 'x-fa fa-stop-circle', handler: 'cancelTransfer', validSelection: 'single' },
        //         // '|',
        //         { reference: 'returnbutton', itemId: returnitemid, iconCls: 'fa fa-reply', handler: 'returnItem', validSelection: 'single' },
        //         // '|',
        //         { reference: 'activatebutton', itemId: activateitemid, iconCls: 'x-fa fa-plus-circle', handler: 'requestActivateItemForTransfer', validSelection: 'single' },
        //         { reference: 'approvependingbutton', itemId: approvependingitemid, iconCls: 'x-fa fa-thumbs-up', handler: 'approvePendingItemForTransfer', validSelection: 'single' },
        //         '|',
        //         { reference: 'printGoldBarListButton', text: 'Print Gold Bar List', itemId: 'printGoldBarListButton', tooltip: 'Print Gold Bar List', iconCls: 'x-fa fa-file', handler: 'printGoldBarListButton'},
        //         {
        //             text: 'Download',tooltip: 'Download Vault LIst',iconCls: 'x-fa fa-download', reference: 'exportbutton', handler: 'exportVaultListButton',  showToolbarItemText: true, printType: 'xlsx', // printType: pending
        //         },
        //         // '|',
        //         // { reference: 'printButton', itemId: 'printButton', tooltip: 'Print Documents', iconCls: 'x-fa fa-print', handler: 'printDocumentSelection', },
        //         // { reference: 'createPrintButton', itemId: 'createPrintButton', tooltip: 'Create Document for Print', iconCls: 'x-fa fa-book', handler: 'createPrintButton', },
        //         //{ reference: 'transferbuttonbmmb', itemId: 'transferbuttonbmmb', text: 'Return Item', iconCls: 'fa fa-reply', handler: 'returnItemBmmb', validSelection: 'single' },
        //     );


        //     var btntransferitem = Ext.ComponentQuery.query("#" + transferitemid)[0];
        //     var btnconfirmtransfer = Ext.ComponentQuery.query("#" + confirmtransferid)[0];
        //     var btnreturn = Ext.ComponentQuery.query("#" + returnitemid)[0];
        //     var btncanceltransfer = Ext.ComponentQuery.query("#" + cancelreqtransferid)[0];
        //     var btnactivateitem = Ext.ComponentQuery.query("#" + activateitemid)[0];
        //     var btnapprovependingitem = Ext.ComponentQuery.query("#" + approvependingitemid)[0];

        //     btntransferitem.disable();
        //     btnconfirmtransfer.disable();
        //     btnreturn.disable();
        //     btncanceltransfer.disable();
        //     btnactivateitem.disable();
        //     btnapprovependingitem.disable();
            
            
        //     Ext.create('Ext.tip.ToolTip', {
        //         target: btntransferitem.getEl(),
        //         html: 'Transfer Item'
        //     });
        //     Ext.create('Ext.tip.ToolTip', {
        //         target: btnconfirmtransfer.getEl(),
        //         html: 'Confirm Transfer'
        //     });
        //     Ext.create('Ext.tip.ToolTip', {
        //         target: btnreturn.getEl(),
        //         html: 'Return Item'
        //     });
        //     Ext.create('Ext.tip.ToolTip', {
        //         target: btncanceltransfer.getEl(),
        //         html: 'Cancel Transfer'
        //     });

        //     this.store.sorters.clear();
        //     this.store.sort([{
        //         property: 'id',
        //         direction: 'DESC'
        //     }]);  
           
        //     // if(this.lookupReference('approvependingbutton')){
        //     //     approveButton = this.lookupReference('approvependingbutton');
        //     //     approveButton.setHidden(true);
    
        //     //     // Check for type 
        //     //     var returnPermission = snap.getApplication().hasPermission(apermission);
        //     //     if (true == returnPermission){
        //     //         approveButton.setHidden(false);
        //     //     } 
        //     // }

        //     if(this.lookupReference('returnbutton')){
        //         returnButton = this.lookupReference('returnbutton');
        //         returnButton.setHidden(true);
    
        //         // Check for type 
        //         // By right is only for trader
        //         if ("Operator" == snap.getApplication().usertype || "Sale" == snap.getApplication().usertype  || "Trader" == snap.getApplication().usertype ){
                    
        //             // Build permission
                 
        //             // Do permission check for said user roles
        //             var returnPermission = snap.getApplication().hasPermission(rpermission);
        //             if(returnPermission == true){
        //                 returnButton.setHidden(false);
        //             }
        //         } 
        //     }

        //     if(this.lookupReference('activatebutton')){
        //         activateButton = this.lookupReference('activatebutton');
        //         activateButton.setHidden(true);
    
        //         // Check for type 
        //         if ("Operator" == snap.getApplication().usertype || "Sale" == snap.getApplication().usertype  || "Trader" == snap.getApplication().usertype ){
                    
        //             // Build permission
        //             var permission = '/root/' + originType + '/vault/approve';
        //             // Do permission check for said user roles
        //             var approvePermission = snap.getApplication().hasPermission(apermission);
        //             if(approvePermission == true){
        //                 activateButton.setHidden(false);
        //             }
                    
                    
        //         } 
        //     }

        //     if(this.lookupReference('approvependingbutton')){
        //         approveButton = this.lookupReference('approvependingbutton');
        //         approveButton.setHidden(true);
    
        //         // Check for type 
        //         if ("Operator" == snap.getApplication().usertype || "Sale" == snap.getApplication().usertype  || "Trader" == snap.getApplication().usertype ){

        //             // Do permission check for said user roles
        //             var ackApprovePermission = snap.getApplication().hasPermission(apermission);
        //             if(ackApprovePermission == true){
        //                 approveButton.setHidden(false);
        //             }
                    
                    
        //         } 
        //     }
        // }
    },

    isIdentical(array) {
        for(var i = 0; i < array.length - 1; i++) {
            if(array[i] !== array[i+1]) {
                return false;
            }
        }
        return true;
    },

    checkActionPermission: function (view, record) {
        // check all selected records
        var myView = this.getView(),
            me = this, record;
        var sm = myView.getSelectionModel();
        var selectedRecords = sm.getSelection();  
            vaultlocations = [];
            statuses = [];
            allocated = [];

        var selected = false;
        Ext.Array.each(view.getSelectionModel().getSelection(), function (items) {
            if (items.getId() == record.getId()) {
                selected = true;
                return false;
            }
        });

        var originType = this.up().type;
        var transferPermission = '/root/' + originType + '/vault/transfer';
        var returnPermission = '/root/' + originType + '/vault/return';
       
        var transferitemid = originType + "transferitem";
        var confirmtransferid = originType + "confirmtransfer";
        var returnitemid = originType + "returnitem";
        var cancelreqtransferid = originType + "cancelreqtransfer";
        var activateitemid = originType + "activateitem";
        var approvependingitemid = originType + "approvependingitem";
        
        /*
        var btntransferitem = Ext.ComponentQuery.query('#transferitem')[0];
        var btnconfirmtransfer = Ext.ComponentQuery.query('#confirmtransfer')[0];
        var btnreturn = Ext.ComponentQuery.query('#returnitem')[0];
        var btncanceltransfer = Ext.ComponentQuery.query('#cancelreqtransfer')[0];
        var btnactivateitem = Ext.ComponentQuery.query('#activateitem')[0];
        var btnapprovependingitem = Ext.ComponentQuery.query('#approvependingitem')[0];
        */

        var btntransferitem = Ext.ComponentQuery.query("#" + transferitemid)[0];
        var btnconfirmtransfer = Ext.ComponentQuery.query("#" + confirmtransferid)[0];
        var btnreturn = Ext.ComponentQuery.query("#" + returnitemid)[0];
        var btncanceltransfer = Ext.ComponentQuery.query("#" + cancelreqtransferid)[0];
        var btnactivateitem = Ext.ComponentQuery.query("#" + activateitemid)[0];
        var btnapprovependingitem = Ext.ComponentQuery.query("#" + approvependingitemid)[0];


        btntransferitem.disable();
        // btnconfirmtransfer.disable();
        btnreturn.disable();
        btncanceltransfer.disable();
        btnactivateitem.disable();
        btnapprovependingitem.disable();
        
        var transferPermission = snap.getApplication().hasPermission(transferPermission);
        var returnPermission = snap.getApplication().hasPermission(returnPermission);
      
        // Add new admin permission

        // Final checking if multiple records are selected

        // Do simple length check for multiple selection
        // If 1 or more record is selected
        if(selectedRecords.length >= 1){
            // Check for all selected records 

            // store records in array
            for(i = 0; i < selectedRecords.length; i++){
                vaultlocations[i] = selectedRecords[i].data.vaultlocationid;
                statuses[i] = selectedRecords[i].data.status;
                allocated[i] = selectedRecords[i].data.allocated;
                //records[i] = selectedRecords[i].data.movetovaultlocationid;
            }
           
            // Check if locations are identical, if yes, proceed as normal
            // Otherwise lock actions
            // Same check is done to status to make sure it is same
            if (false == this.isIdentical(vaultlocations) || false == this.isIdentical(statuses) || false == this.isIdentical(allocated)){
                // Disable all actionbars
                btntransferitem.disable();
                btnconfirmtransfer.disable();
                btnreturn.disable();
                btncanceltransfer.disable();
                btnactivateitem.disable();
                btnapprovependingitem.disable();
            }else {
                // If Go or One Gold
                if("go" === originType || "one" === originType){
                    if (transferPermission == true && selected && selectedRecords[0].data.status == 1 && (selectedRecords[0].data.allocated == 1 || selectedRecords[0].data.allocated == 0) && selectedRecords[0].data.vaultlocationid != 0) {
                        btntransferitem.enable();            
                    }
                    if (transferPermission == true && selected && selectedRecords[0].data.status == 1 && (selectedRecords[0].data.allocated == 1) && selectedRecords[0].data.vaultlocationid == 0) {
                        btntransferitem.enable();            
                    }
                    if (transferPermission == true && selectedRecords[0].data.status == 2 && (selectedRecords[0].data.allocated == 1 || selectedRecords[0].data.allocated == 0) && selectedRecords[0].data.movetovaultlocationid != null && selectedRecords[0].data.movetovaultlocationid != 1) {
                        btnconfirmtransfer.enable();            
                    }
                    /*if (returnPermission == true && selected && record.data.status == 2 && record.data.allocated == 0 && record.data.vaultlocationid == 2 && record.data.movetovaultlocationid == 1) {
                        btnreturn.enable();  
                    }*/
                    if (returnPermission == true && selected && selectedRecords[0].data.status == 2 && selectedRecords[0].data.allocated == 0 && selectedRecords[0].data.vaultlocationid == 2 && selectedRecords[0].data.movetovaultlocationid == 1) {
                        btnreturn.enable();            
                    }
                    if (transferPermission == true && selected && selectedRecords[0].data.status == 2 && (selectedRecords[0].data.allocated == 1 || selectedRecords[0].data.allocated == 0) && selectedRecords[0].data.movetovaultlocationid != null && record.data.movetovaultlocationid != 1) {
                        btnconfirmtransfer.enable();           
                    }
                    
                    if (transferPermission == true && selected && selectedRecords[0].data.status == 2) {
                        btncanceltransfer.enable();            
                    }
                    // Check if have serial no + physical bar + but unallocated active ( must have vault location at ace hq - means there is physical )
                    if (transferPermission == true && selectedRecords[0].data.status == 1 && (selectedRecords[0].data.allocated == 0) && selectedRecords[0].data.vaultlocationid == 1) {
                        btnactivateitem.enable();      
                       
                    }
            
                    // Check if record is pending for activation
                    if (transferPermission == true && selectedRecords[0].data.status == 5 && (selectedRecords[0].data.allocated == 0) && selectedRecords[0].data.vaultlocationid == 1) {
                        btnapprovependingitem.enable();      
                       
                    }
                }else if("bmmb" === originType){
                    if (transferPermission == true && selectedRecords[0].data.status == 1 && (selectedRecords[0].data.allocated == 1) && selectedRecords[0].data.vaultlocationid != 0) {
                        //btntransferitem.enable();      
                        // check if its the right partner before enabling button
                        
                        // If movetolocation is not 0, check for partnerid
                        // Compares current user partner id with moveto loc id
                        // If user partner is 0 ( Ace Staff )
                        // If user has partner id ( Customer account )
                
                        // For customer as they have partner
                        if(vmv.get('userpartnerid') != 0){
                      
                            // Check if partnerid is same as movetovaultlocationid
                            if (selectedRecords[0].data.movetolocationpartnerid == vmv.get('userpartnerid')){
                                btntransferitem.enable();    
                            }else {
                                btntransferitem.disable();    
                            }
                        }else{
                            // For ace as they do not have partner id
                
                            // Default HQ location
                            if (selectedRecords[0].data.vaultlocationid == 1){
                                btntransferitem.enable();    
                            }else {
                                btntransferitem.disable();    
                            }
                        }      
                       
                    }
                    if (transferPermission == true && selectedRecords[0].data.status == 1 && (selectedRecords[0].data.allocated == 1) && selectedRecords[0].data.vaultlocationid == 0) {
                        // For transfer without do number ( no vault location )
                         //btntransferitem.enable();      
                        // check if its the right partner before enabling button
                        
                        // If movetolocation is not 0, check for partnerid
                        // Compares current user partner id with moveto loc id
                        // If user partner is 0 ( Ace Staff )
                        // If user has partner id ( Customer account )
                
                        // For customer as they have partner
                        if(vmv.get('userpartnerid') != 0){
                      
                            // Check if partnerid is same as movetovaultlocationid
                            if (selectedRecords[0].data.movetolocationpartnerid == vmv.get('userpartnerid')){
                                btntransferitem.disable();    
                            }else {
                                btntransferitem.disable();    
                            }
                        }else{
                            // For ace as they do not have partner id
                
                            // Default HQ location
                            if (selectedRecords[0].data.vaultlocationid == 0 || selectedRecords[0].data.vaultlocationid == 1){
                                btntransferitem.disable();    
                            }else {
                                btntransferitem.disable();    
                            }
                        }      
                    }
                    if (transferPermission == true && selectedRecords[0].data.status == 2 && (selectedRecords[0].data.allocated == 1 || selectedRecords[0].data.allocated == 0) && selectedRecords[0].data.movetovaultlocationid != null && selectedRecords[0].data.movetovaultlocationid != 1) {
                        btnconfirmtransfer.enable();            
                    }
                    /*if (returnPermission == true && selected && record.data.status == 2 && record.data.allocated == 0 && record.data.vaultlocationid == 2 && record.data.movetovaultlocationid == 1) {
                        btnreturn.enable();  
                    }*/
                    if (returnPermission == true && selectedRecords[0].data.status == 2 && (selectedRecords[0].data.allocated == 1 || selectedRecords[0].data.allocated == 0)  && selectedRecords[0].data.vaultlocationid == 4 && selectedRecords[0].data.movetovaultlocationid == 1) {
                        //           
                        // check if its the right partner before enabling button
                        
                        // If movetolocation is not 0, check for partnerid
                        // Compares current user partner id with moveto loc id
                        // If user partner is 0 ( Ace Staff )
                        // If user has partner id ( Customer account )
                
                        // For customer as they have partner
                        if(vmv.get('userpartnerid') != 0){
                            
                            btnreturn.disable();
                           
                        }else{
                            // For ace as they do not have partner id
                
                            // Default HQ location
                            if (selectedRecords[0].data.movetovaultlocationid == 1 ){
                                btnreturn.enable();   
                                btnconfirmtransfer.enable();   
                            }else {
                                btnreturn.disable();       
                            }
                        }
                    }
                    if (transferPermission == true && selectedRecords[0].data.status == 2 && (selectedRecords[0].data.allocated == 1 || selectedRecords[0].data.allocated == 0) && selectedRecords[0].data.movetovaultlocationid != null && selectedRecords[0].data.movetovaultlocationid != 1) {
                        // check if its the right partner before enabling button
                        
                        // If movetolocation is not 0, check for partnerid
                        // Compares current user partner id with moveto loc id
                        // If user partner is 0 ( Ace Staff )
                        // If user has partner id ( Customer account )
                
                        // For customer as they have partner
                        if(vmv.get('userpartnerid') != 0){
                      
                            // Check if partnerid is same as movetovaultlocationid
                            if (selectedRecords[0].data.movetolocationpartnerid == vmv.get('userpartnerid')){
            
                                // if same cannot confirm
                                btnconfirmtransfer.disable();    
            
                            }else {
                                btnconfirmtransfer.enable();    
                            }
                        }else{
                            // For ace as they do not have partner id
                            // Default HQ location
                            if (selectedRecords[0].data.movetovaultlocationid == 1){
                                btnconfirmtransfer.enable();    
                            }else {
                                btnconfirmtransfer.disable();    
                            }
                        }
                               
                    }
                    if (transferPermission == true && selectedRecords[0].data.status == 2 && (selectedRecords[0].data.allocated == 1 || selectedRecords[0].data.allocated == 0) && selectedRecords[0].data.movetovaultlocationid != null && selectedRecords[0].data.movetovaultlocationid != 1) {
                        // check if its the right partner before enabling button
                        
                        // If movetolocation is not 0, check for partnerid
                        // Compares current user partner id with moveto loc id
                        // If user partner is 0 ( Ace Staff )
                        // If user has partner id ( Customer account )
                
                        // For customer as they have partner
                        if(vmv.get('userpartnerid') != 0){
                      
                            // Check if partnerid is same as movetovaultlocationid
                            if (selectedRecords[0].data.movetolocationpartnerid == vmv.get('userpartnerid')){
            
                                // if same cannot confirm
                                btnconfirmtransfer.disable();    
            
                            }else {
                                btnconfirmtransfer.enable();    
                            }
                        }else{
                            // For ace as they do not have partner id
                            // Default HQ location
                            if (selectedRecords[0].data.movetovaultlocationid == 1){
                                btnconfirmtransfer.enable();    
                            }else {
                                btnconfirmtransfer.disable();    
                            }
                        }
                               
                    }
                    if (transferPermission == true && selectedRecords[0].data.status == 2) {
            
                        // For customer as they have partner
                        if(vmv.get('userpartnerid') != 0){
                      
                            // Check if partnerid is same as movetovaultlocationid
                            if (selectedRecords[0].data.movetolocationpartnerid == vmv.get('userpartnerid')){
            
                                // if same cannot cancel
                                btncanceltransfer.enable();  
                            }else {
                                btncanceltransfer.disable();    
                            }
                        }else{
                            // For ace as they do not have partner id
                            // Default HQ location
                            if (selectedRecords[0].data.movetovaultlocationid == 1){
                                btncanceltransfer.disable();  
                            }else {
                                btncanceltransfer.enable();  
                            }
                        }
            
                                
                    }
            
                    // Check if have serial no + physical bar + but unallocated active ( must have vault location at ace hq - means there is physical )
                    if (transferPermission == true && selectedRecords[0].data.status == 1 && (selectedRecords[0].data.allocated == 0) && selectedRecords[0].data.vaultlocationid == 1) {
                        btnactivateitem.enable();      
                       
                    }
            
                    // Check if record is pending for activation
                    if (transferPermission == true && selectedRecords[0].data.status == 5 && (selectedRecords[0].data.allocated == 0) && selectedRecords[0].data.vaultlocationid == 1) {
                        btnapprovependingitem.enable();      
                       
                    }
                }
               
            }
           
        }

        
    },    

    


    //////////////////////////////////////////////////////////////
    /// View properties settings
    ///////////////////////////////////////////////////////////////
    

    //////////////////////////////////////////////////////////////
    /// Add / edit form settings
    ///////////////////////////////////////////////////////////////
    
    


});
