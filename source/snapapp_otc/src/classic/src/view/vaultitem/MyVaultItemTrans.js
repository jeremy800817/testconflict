Ext.define('snap.view.vaultitemtrans.MyVaulItemTrans', {
    extend: 'snap.view.vaultitemtrans.vaultitemTrans',
    xtype: 'myvaultitemtransview',
    requires: [
        'snap.store.VaultItemTrans',
        'snap.model.VaultItemTrans',
        'snap.view.vaultitemtrans.vaultitemtransController',
        'snap.view.vaultitemtrans.vaultitemtransModel'
    ],

    gridSelectionMode: 'MULTI',
    allowDeselect: true,
    store: {
        type: 'VaultItemTrans'
    },
    permissionRoot: '/root/mbb/vault',
    enableFilter: true,
    toolbarItems: [
        'detail', 'filter',
        '|',
        // {
        //     text: 'Edit',
        //     tooltip: 'Edit',
        //     reference: 'editvaultitemtrans',
        //     itemId: 'editvaultitemtrans',
        //     iconCls: 'fa fa-edit',
        //     handler: 'editvaultitemtrans',
        //     showToolbarItemText: true,
        //     validSelection: 'single'
        // },
        {
            text: 'Cancel',
            tooltip: 'Void/Reverse',
            reference: 'voidvaultitemtrans',
            itemId: 'voidvaultitemtrans',
            iconCls: 'fa fa-window-close',
            handler: 'voidvaultitemtrans',
            showToolbarItemText: true,
            validSelection: 'single'
        },
        '|',
        // {
        //     text: 'Download',
        //     tooltip: 'Download Vault LIst',
        //     iconCls: 'x-fa fa-download',
        //     reference: 'exportbutton',
        //     handler: 'exportVaultListButton',
        //     showToolbarItemText: true,
        //     printType: 'xlsx', // printType: pending
        // },
        // '|',
        {
            reference: 'printButton',
            text: 'Print Document',
            itemId: 'printButton',
            tooltip: 'Print Documents',
            iconCls: 'x-fa fa-print',
            handler: 'printButton',
            showToolbarItemText: true,
            validSelection: 'single'
        },
        {
            reference: 'approve',
            text: 'Approve',
            itemId: 'approveButton',
            tooltip: 'Approve Transaction',
            iconCls: 'x-fa fa-check',
            handler: 'approveButton',
            showToolbarItemText: true,
            validSelection: 'single'
        },
        {
            reference: 'complete',
            text: 'Complete',
            itemId: 'completeButton',
            tooltip: 'Complete Transaction',
            iconCls: 'x-fa fa-check-double',
            handler: 'completeButton',
            showToolbarItemText: true,
            validSelection: 'single'
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

            this.store.sorters.clear();
            this.store.sort([{
                property: 'id',
                direction: 'DESC'
            }]);

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
        var btnreturn = Ext.ComponentQuery.query('#returnitem')[0];
        // var btncanceltransfer = Ext.ComponentQuery.query('#cancelreqtransfer')[0];
        var btnreturnhq = Ext.ComponentQuery.query('#returnToHQ')[0];
        btntransferitem.disable();
        // btnconfirmtransfer.disable();
        btnreturn.disable();
        // btncanceltransfer.disable();
        btnreturnhq.disable();

        var transferPermission = snap.getApplication().hasPermission('/root/mbb/vault/transfer');
        var returnPermission = snap.getApplication().hasPermission('/root/mbb/vault/return');


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
            if (transferPermission == true && selected && record.data.status == 1 && (record.data.allocated == 1 || record.data.allocated == 0) && record.data.vaultlocationid <= 1) {
                btntransferitem.enable();
            } else if (transferPermission == true && selected && record.data.status == 1 && (record.data.allocated == 0 || record.data.allocated == 1) && record.data.vaultlocationid == 3) {
                btntransferitem.enable();
                btnreturnhq.enable();
            } else {
                if (false == this.isIdentical(vaultlocations) || false == this.isIdentical(statuses) || false == this.isIdentical(allocated)) {
                    // Disable all actionbars
                    btntransferitem.disable();
                    btnconfirmtransfer.disable();
                    btnreturn.disable();
                    btncanceltransfer.disable();
                    //btnactivateitem.disable();
                    //btnapprovependingitem.disable();

                } else {

                    if (transferPermission == true && selected && record.data.status == 1 && (record.data.allocated == 1 || record.data.allocated == 0) && record.data.vaultlocationid != 0) {
                        btntransferitem.enable();
                    }
                    if (transferPermission == true && selected && record.data.status == 1 && (record.data.allocated == 1) && record.data.vaultlocationid == 0) {
                        btntransferitem.enable();
                    }
                    if (transferPermission == true && selected && record.data.status == 2 && (record.data.allocated == 1 || record.data.allocated == 0) && record.data.movetovaultlocationid != null && record.data.movetovaultlocationid != 1) {
                        btnconfirmtransfer.enable();
                    }
                    if (returnPermission == true && selected && record.data.status == 2 && record.data.allocated == 0 && record.data.vaultlocationid == 2 && record.data.movetovaultlocationid == 1) {
                        btnreturn.enable();
                    }
                    if (transferPermission == true && selected && record.data.status == 2 && (record.data.allocated == 1 || record.data.allocated == 0) && record.data.movetovaultlocationid != null && record.data.movetovaultlocationid != 1) {
                        btnconfirmtransfer.enable();
                    }
                    if (transferPermission == true && selected && record.data.status == 2) {
                        btncanceltransfer.enable();
                    }
                    // Return to HQ from MBB
                    if (transferPermission == true && selected && record.data.status == 1 && (record.data.allocated == 0 || record.data.allocated == 1) && record.data.vaultlocationid == 3) {
                        btntransferitem.enable();
                        btnreturnhq.enable();
                    }
                }
            }


        }

    },


});