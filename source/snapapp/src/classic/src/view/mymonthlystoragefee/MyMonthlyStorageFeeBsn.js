Ext.define('snap.view.mymonthlystoragefee.MyMonthlyStorageFeeBsn', {
    extend: 'snap.view.mymonthlystoragefee.MyMonthlyStorageFee',
    xtype: 'mybsnmonthlystoragefeeview',

    partnercode: 'BSN',
    permissionRoot: '/root/bsn/report/storagefee',
    store: {
        type: 'MyMonthlyStorageFee', proxy: {
            type: 'ajax',
            url: 'index.php?hdl=mymonthlystoragefee&action=list&partnercode=BSN',
            reader: {
                type: 'json',
                rootProperty: 'records',
            }
        },
    }
});