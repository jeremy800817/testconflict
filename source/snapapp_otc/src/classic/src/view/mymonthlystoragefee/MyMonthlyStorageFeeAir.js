Ext.define('snap.view.mymonthlystoragefee.MyMonthlyStorageFeeAir', {
    extend: 'snap.view.mymonthlystoragefee.MyMonthlyStorageFee',
    xtype: 'myairmonthlystoragefeeview',

    partnercode: 'AIR',
    permissionRoot: '/root/air/report/storagefee',
    store: {
        type: 'MyMonthlyStorageFee', proxy: {
            type: 'ajax',
            url: 'index.php?hdl=mymonthlystoragefee&action=list&partnercode=AIR',
            reader: {
                type: 'json',
                rootProperty: 'records',
            }
        },
    }
});