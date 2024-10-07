Ext.define('snap.view.vaultitem.CommonVaulItemList', {
    extend: 'snap.view.vaultitem.vaultitemList',
    xtype: 'commonvaultitemview',
    requires: [
        'snap.store.CommonVaultItem',
        'snap.model.CommonVaultItem',
        'snap.view.vaultitem.vaultitemController',
        'snap.view.vaultitem.vaultitemModel'
    ],

    gridSelectionMode: 'MULTI',
    allowDeselect: true,
    // store: {
    //     type: 'CommonVaultItem'
    // },
    store: { type: 'CommonVaultItem',

        remoteFilter: true,
        filters: [
            // { property: 'allocated',value: '1'},
            { property: 'status',value: '1'},
        ],
        // sorters: [{
        //     property: 'pricetarget',
        //     direction: 'DESC'
        // }]
    },
    permissionRoot: '/root/common/vault',
    enableFilter: true,
    toolbarItems: [
        'detail', 'filter', '|',
        // Add new buttons
        // { reference: 'activatebutton', itemId: 'activateitem', iconCls: 'x-fa fa-plus-circle', handler: 'requestActivateItemForTransfer', validSelection: 'single' },
        // { reference: 'approvependingbutton', itemId: 'approvependingitem', iconCls: 'x-fa fa-thumbs-up', handler: 'approvePendingItemForTransfer', validSelection: 'single' },
        {
            reference: 'reqtransferbutton',
            text: 'Transfer',
            itemId: 'transferitem',
            iconCls: 'fa fa-exchange-alt',
            handler: 'showTransferFormCommon',
            showToolbarItemText: true,
            validSelection: 'multiple'
        },
        // End new buttons
        {
            text: 'Return To HQ',
            tooltip: 'Return & unallocate kilobar from Common to HQ',
            reference: 'returntohqforcebutton',
            itemId: 'returnToHqForce',
            iconCls: 'fa fa-home',
            handler: 'returnToHqForce',
            showToolbarItemText: true,
            validSelection: 'single'
        },
        '|',
        {
            text: 'Download',
            tooltip: 'Download Vault LIst',
            iconCls: 'x-fa fa-download',
            reference: 'exportbutton',
            handler: 'exportVaultListButton',
            showToolbarItemText: true,
            printType: 'xlsx', // printType: pending
        },
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
        afterrender: function (store) {
            var permission = '/root/common/vault/approve';
            
            // Init action permissions
            var btntransferitem = Ext.ComponentQuery.query('#transferitem')[0];
            // var btnactivateitem = Ext.ComponentQuery.query('#activateitem')[0];
            // var btnapprovependingitem = Ext.ComponentQuery.query('#approvependingitem')[0];

            Ext.create('Ext.tip.ToolTip', {
                target: btntransferitem.getEl(),
                html: 'Transfer Item'
            });
            // Ext.create('Ext.tip.ToolTip', {
            //     target: btnactivateitem.getEl(),
            //     html: 'Activate Kilobar for Transfer'
            // });
            // Ext.create('Ext.tip.ToolTip', {
            //     target: btnapprovependingitem.getEl(),
            //     html: 'Approve Pending Activation of Kilobar'
            // });
            // End action permission init
            
            this.store.sorters.clear();
            this.store.sort([{
                property: 'id',
                direction: 'DESC'
            }]);

            // if(this.lookupReference('approvependingbutton')){
            //     approveButton = this.lookupReference('approvependingbutton');
            //     approveButton.setHidden(true);
    
            //     // Check for type 
            //     var returnPermission = snap.getApplication().hasPermission(permission);
            //     if (true == returnPermission){
            //         approveButton.setHidden(false);
            //     } 
            // }
        }
    },

    isIdentical(array) {
        for (var i = 0; i < array.length - 1; i++) {
            if (array[i] !== array[i + 1]) {
                return false;
            }
        }
        return true;
    },

    checkActionPermission: function (view, record) {

        // check all selected records
        var myView = this.getView(),
            me = this,
            record;
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

        var btntransferitem = Ext.ComponentQuery.query('#transferitem')[0];
        // var btnconfirmtransfer = Ext.ComponentQuery.query('#confirmtransfer')[0];
        // var btnreturn = Ext.ComponentQuery.query('#returnitem')[0];
        // var btncanceltransfer = Ext.ComponentQuery.query('#cancelreqtransfer')[0];
        // var btnreturnhq = Ext.ComponentQuery.query('#returnToHQ')[0];
        btntransferitem.disable();
        // btnconfirmtransfer.disable();
        // btnreturn.disable();
        // btncanceltransfer.disable();
        // btnreturnhq.disable();

        //Init action permissions
        var btntransferitem = Ext.ComponentQuery.query('#transferitem')[0];
        // var btnactivateitem = Ext.ComponentQuery.query('#activateitem')[0];
        // var btnapprovependingitem = Ext.ComponentQuery.query('#approvependingitem')[0];


        btntransferitem.disable();
        // btnactivateitem.disable();
        // btnapprovependingitem.disable()
        
        var transferPermission = snap.getApplication().hasPermission('/root/common/vault/transfer');
        // var returnPermission = snap.getApplication().hasPermission('/root/common/vault/return');

  
        // Final checking if multiple records are selected

        // Do simple length check for multiple selection
        // If 1 or more record is selected
        if (selectedRecords.length >= 1) {
            // Check for all selected records 


            // store records in array
            for (i = 0; i < selectedRecords.length; i++) {
                vaultlocations[i] = selectedRecords[i].data.vaultlocationid;
                statuses[i] = selectedRecords[i].data.status;
                allocated[i] = selectedRecords[i].data.allocated;
                //records[i] = selectedRecords[i].data.movetovaultlocationid;
            }


            // Check if locations are identical, if yes, proceed as normal
            // Otherwise lock actions
            // Same check is done to status to make sure it is same
            // With the exception that if the selected records are allocated, and from hq
    
                if (false == this.isIdentical(vaultlocations) || false == this.isIdentical(statuses) || false == this.isIdentical(allocated)) {
                    // Disable all actionbars
                    btntransferitem.disable();
                    // btnconfirmtransfer.disable();
                    // btnreturn.disable();
                    // btncanceltransfer.disable();
                    // btnactivateitem.disable();
                    // btnapprovependingitem.disable();

                } else {

                    if (transferPermission == true && selectedRecords[0].data.status == 1 && (selectedRecords[0].data.allocated == 1 || selectedRecords[0].data.allocated == 0) && selectedRecords[0].data.vaultlocationid != 0) {
                        //btntransferitem.enable();      
                        // check if its the right partner before enabling button
                        
                        // If movetolocation is not 0, check for partnerid
                        // Compares current user partner id with moveto loc id
                        // If user partner is 0 ( Ace Staff )
                        // If user has partner id ( Customer account )
                
                        // Default HQ location
                        if (selectedRecords[0].data.vaultlocationid == 1 || selectedRecords[0].data.vaultlocationid == 2){
                            btntransferitem.enable();    
                        }else {
                            btntransferitem.disable();    
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
                
                        // Default HQ location
                        if (selectedRecords[0].data.vaultlocationid == 0 || selectedRecords[0].data.vaultlocationid == 1 || selectedRecords[0].data.vaultlocationid == 2){
                            btntransferitem.enable();    
                        }else {
                            btntransferitem.disable();    
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
                
                        // Default HQ location
                        if (selectedRecords[0].data.movetovaultlocationid == 1 ){
                            btnreturn.enable();   
                            btnconfirmtransfer.enable();   
                        }else {
                            btnreturn.disable();       
                        }
                    }
                    if (transferPermission == true && selectedRecords[0].data.status == 2 && (selectedRecords[0].data.allocated == 1 || selectedRecords[0].data.allocated == 0) && selectedRecords[0].data.movetovaultlocationid != null && selectedRecords[0].data.movetovaultlocationid != 1) {
                        // check if its the right partner before enabling button
                        
                        // If movetolocation is not 0, check for partnerid
                        // Compares current user partner id with moveto loc id
                        // If user partner is 0 ( Ace Staff )
                        // If user has partner id ( Customer account )
                
                        // For ace as they do not have partner id
                        // Default HQ location
                        if (selectedRecords[0].data.movetovaultlocationid == 1){
                            btnconfirmtransfer.enable();    
                        }else {
                            btnconfirmtransfer.disable();    
                        }
                               
                    }
                    if (transferPermission == true && selectedRecords[0].data.status == 2 && (selectedRecords[0].data.allocated == 1 || selectedRecords[0].data.allocated == 0) && selectedRecords[0].data.movetovaultlocationid != null && selectedRecords[0].data.movetovaultlocationid != 1) {
                        // check if its the right partner before enabling button
                        
                        // If movetolocation is not 0, check for partnerid
                        // Compares current user partner id with moveto loc id
                        // If user partner is 0 ( Ace Staff )
                        // If user has partner id ( Customer account )
                
                        // For ace as they do not have partner id
                        // Default HQ location
                        if (selectedRecords[0].data.movetovaultlocationid == 1){
                            btnconfirmtransfer.enable();    
                        }else {
                            btnconfirmtransfer.disable();    
                        }
                               
                    }
                    if (transferPermission == true && selectedRecords[0].data.status == 2) {
            
                        // For ace as they do not have partner id
                        // Default HQ location
                        if (selectedRecords[0].data.movetovaultlocationid == 1){
                            btncanceltransfer.disable();  
                        }else {
                            btncanceltransfer.enable();  
                        }
            
                                
                    }
            
                    // Check if have serial no + physical bar + but unallocated active ( must have vault location at ace hq - means there is physical )
                    // if (transferPermission == true && selectedRecords[0].data.status == 1 && (selectedRecords[0].data.allocated == 0) && selectedRecords[0].data.vaultlocationid == 1) {
                    //     btnactivateitem.enable();      
                       
                    // }
            
                    // Check if record is pending for activation
                    // if (transferPermission == true && selectedRecords[0].data.status == 5 && (selectedRecords[0].data.allocated == 0) && selectedRecords[0].data.vaultlocationid == 1) {
                    //     btnapprovependingitem.enable();      
                       
                    // }
                }



        }

    },

});