Ext.define('snap.view.mymonthlystoragefee.MyMonthlyStorageFeeHope', {
    extend: 'snap.view.mymonthlystoragefee.MyMonthlyStorageFee',
    xtype: 'myhopemonthlystoragefeeview',

    partnercode: 'HOPE',
    permissionRoot: '/root/hope/report/storagefee',
    store: {
        type: 'MyMonthlyStorageFee', proxy: {
            type: 'ajax',
            url: 'index.php?hdl=mymonthlystoragefee&action=list&partnercode=HOPE',
            reader: {
                type: 'json',
                rootProperty: 'records',
            }
        },
    }
});