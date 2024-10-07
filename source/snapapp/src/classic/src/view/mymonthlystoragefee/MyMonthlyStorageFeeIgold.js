Ext.define('snap.view.mymonthlystoragefee.MyMonthlyStorageFeeIgold', {
    extend: 'snap.view.mymonthlystoragefee.MyMonthlyStorageFee',
    xtype: 'myigoldmonthlystoragefeeview',

    partnercode: 'IGOLD',
    permissionRoot: '/root/igold/report/storagefee',
    store: {
        type: 'MyMonthlyStorageFee', proxy: {
            type: 'ajax',
            url: 'index.php?hdl=mymonthlystoragefee&action=list&partnercode=IGOLD',
            reader: {
                type: 'json',
                rootProperty: 'records',
            }
        },
    }
});