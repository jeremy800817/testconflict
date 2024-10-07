Ext.define('snap.view.mymonthlystoragefee.MyMonthlyStorageFeeMcash', {
    extend: 'snap.view.mymonthlystoragefee.MyMonthlyStorageFee',
    xtype: 'mymcashmonthlystoragefeeview',

    partnercode: 'MCASH',
    permissionRoot: '/root/mcash/report/storagefee',
    store: {
        type: 'MyMonthlyStorageFee', proxy: {
            type: 'ajax',
            url: 'index.php?hdl=mymonthlystoragefee&action=list&partnercode=MCASH',
            reader: {
                type: 'json',
                rootProperty: 'records',
            }
        },
    }
});