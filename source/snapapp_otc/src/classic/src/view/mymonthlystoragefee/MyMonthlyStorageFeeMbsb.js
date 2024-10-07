Ext.define('snap.view.mymonthlystoragefee.MyMonthlyStorageFeeMbsb', {
    extend: 'snap.view.mymonthlystoragefee.MyMonthlyStorageFee',
    xtype: 'mymbsbmonthlystoragefeeview',

    partnercode: 'MBSB',
    permissionRoot: '/root/mbsb/report/storagefee',
    store: {
        type: 'MyMonthlyStorageFee', proxy: {
            type: 'ajax',
            url: 'index.php?hdl=mymonthlystoragefee&action=list&partnercode=MBSB',
            reader: {
                type: 'json',
                rootProperty: 'records',
            }
        },
    }
});