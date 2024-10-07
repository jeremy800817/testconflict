Ext.define('snap.view.mymonthlystoragefee.MyMonthlyStorageFeeBmmb', {
    extend: 'snap.view.mymonthlystoragefee.MyMonthlyStorageFee',
    xtype: 'mybmmbmonthlystoragefeeview',

    partnercode: 'BMMB',
    permissionRoot: '/root/bmmb/report/storagefee',
    store: {
        type: 'MyMonthlyStorageFee', proxy: {
            type: 'ajax',
            url: 'index.php?hdl=mymonthlystoragefee&action=list&partnercode=BMMB',
            reader: {
                type: 'json',
                rootProperty: 'records',
            }
        },
    }
});