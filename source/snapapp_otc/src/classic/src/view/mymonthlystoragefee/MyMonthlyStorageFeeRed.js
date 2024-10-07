Ext.define('snap.view.mymonthlystoragefee.MyMonthlyStorageFeeRed', {
    extend: 'snap.view.mymonthlystoragefee.MyMonthlyStorageFee',
    xtype: 'myredmonthlystoragefeeview',

    partnercode: 'RED',
    permissionRoot: '/root/red/report/storagefee',
    store: {
        type: 'MyMonthlyStorageFee', proxy: {
            type: 'ajax',
            url: 'index.php?hdl=mymonthlystoragefee&action=list&partnercode=RED',
            reader: {
                type: 'json',
                rootProperty: 'records',
            }
        },
    }
});