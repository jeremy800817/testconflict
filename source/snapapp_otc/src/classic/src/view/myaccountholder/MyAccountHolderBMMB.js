Ext.define('snap.view.myaccountholder.MyAccountHolderBMMB', {
    extend: 'snap.view.myaccountholder.MyAccountHolder',
    xtype: 'myaccountholderbmmbview',

    requires: [
        'snap.store.MyAccountHolder',
        'snap.model.MyAccountHolder',
        'snap.view.myaccountholder.MyAccountHolderController',
        'snap.view.myaccountholder.MyAccountHolderModel',
    ],
    permissionRoot: '/root/bmmb/profile',
    store: {
        type: 'MyAccountHolder',
        proxy: {
            type: 'ajax',
            url: 'index.php?hdl=myaccountholder&action=list&partnercode=BMMB',
            reader: {
                type: 'json',
                rootProperty: 'records',
            }
        },
    },
    partnerCode: 'BMMB',
    controller: 'myaccountholder-myaccountholder',

    viewModel: {
        type: 'myaccountholder-myaccountholder'
    },
/*
    checkActionPermission: function (view, record) {
        var selected = false;
        Ext.Array.each(view.getSelectionModel().getSelection(), function (items) {
            if (items.getId() == record.getId()) {
                selected = true;
                return false;
            }
        });

        var btnApprovePep = Ext.ComponentQuery.query('#approvePep')[0];
        btnApprovePep.disable();
        var approvalPermission = snap.getApplication().hasPermission('/root/bmmb/approval/approve');

        if (approvalPermission == true && selected && record.data.ispep == 1) {
            btnApprovePep.enable();
        }

        var suspendBtn = Ext.ComponentQuery.query('#suspendBtn')[0];
        suspendBtn.disable();
        var suspendPermission = snap.getApplication().hasPermission('/root/bmmb/profile/suspend');

        if (suspendPermission == true && selected && record.data.status == 1) {
            suspendBtn.enable();
        }

        var unsuspendBtn = Ext.ComponentQuery.query('#unsuspendBtn')[0];
        unsuspendBtn.disable();
        var unsuspendPermission = snap.getApplication().hasPermission('/root/bmmb/profile/unsuspend');

        if (unsuspendPermission == true && selected && record.data.status == 4) {
            unsuspendBtn.enable();
        }
    },
*/
});
