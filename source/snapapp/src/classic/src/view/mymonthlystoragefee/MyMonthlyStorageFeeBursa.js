Ext.define('snap.view.mymonthlystoragefee.MyMonthlyStorageFeeBursa', {
    extend: 'snap.view.mymonthlystoragefee.MyMonthlyStorageFee',
    xtype: 'mybursamonthlystoragefeeview',

    partnercode: 'BURSA',
    permissionRoot: '/root/bursa/report/storagefee',
    store: {
        type: 'MyMonthlyStorageFee', proxy: {
            type: 'ajax',
            url: 'index.php?hdl=mymonthlystoragefee&action=list&partnercode=BURSA',
            reader: {
                type: 'json',
                rootProperty: 'records',
            }
        },
    }
});