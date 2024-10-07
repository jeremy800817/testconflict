Ext.define('snap.view.mymonthlystoragefee.MyMonthlyStorageFeeNoor', {
    extend: 'snap.view.mymonthlystoragefee.MyMonthlyStorageFee',
    xtype: 'mynoormonthlystoragefeeview',

    partnercode: 'NOOR',
    permissionRoot: '/root/noor/report/storagefee',
    store: {
        type: 'MyMonthlyStorageFee', proxy: {
            type: 'ajax',
            url: 'index.php?hdl=mymonthlystoragefee&action=list&partnercode=NOOR',
            reader: {
                type: 'json',
                rootProperty: 'records',
            }
        },
    }
});