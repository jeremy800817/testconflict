Ext.define('snap.view.mymonthlystoragefee.MyMonthlyStorageFeeNubex', {
    extend: 'snap.view.mymonthlystoragefee.MyMonthlyStorageFee',
    xtype: 'mynubexmonthlystoragefeeview',

    partnercode: 'NUBEX',
    permissionRoot: '/root/nubex/report/storagefee',
    store: {
        type: 'MyMonthlyStorageFee', proxy: {
            type: 'ajax',
            url: 'index.php?hdl=mymonthlystoragefee&action=list&partnercode=NUBEX',
            reader: {
                type: 'json',
                rootProperty: 'records',
            }
        },
    }
});