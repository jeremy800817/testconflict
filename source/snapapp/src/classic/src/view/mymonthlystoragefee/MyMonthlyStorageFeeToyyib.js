Ext.define('snap.view.mymonthlystoragefee.MyMonthlyStorageFeeToyyib', {
    extend: 'snap.view.mymonthlystoragefee.MyMonthlyStorageFee',
    xtype: 'mytoyyibmonthlystoragefeeview',

    partnercode: 'TOYYIB',
    permissionRoot: '/root/toyyib/report/storagefee',
    store: {
        type: 'MyMonthlyStorageFee', proxy: {
            type: 'ajax',
            url: 'index.php?hdl=mymonthlystoragefee&action=list&partnercode=TOYYIB',
            reader: {
                type: 'json',
                rootProperty: 'records',
            }
        },
    }
});