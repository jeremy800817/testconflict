Ext.define('snap.view.mymonthlystoragefee.MyMonthlyStorageFeePosarrahnu', {
    extend: 'snap.view.mymonthlystoragefee.MyMonthlyStorageFee',
    xtype: 'myposarrahnumonthlystoragefeeview',

    partnercode: 'POSARRAHNU',
    permissionRoot: '/root/posarrahnu/report/storagefee',
    store: {
        type: 'MyMonthlyStorageFee', proxy: {
            type: 'ajax',
            url: 'index.php?hdl=mymonthlystoragefee&action=list&partnercode=POSARRAHNU',
            reader: {
                type: 'json',
                rootProperty: 'records',
            }
        },
    }
});