Ext.define('snap.view.mymonthlystoragefee.MyMonthlyStorageFeeGo', {
    extend: 'snap.view.mymonthlystoragefee.MyMonthlyStorageFee',
    xtype: 'mygomonthlystoragefeeview',

    partnercode: 'GO',
    permissionRoot: '/root/go/report/storagefee',
    store: {
        type: 'MyMonthlyStorageFee', proxy: {
            type: 'ajax',
            url: 'index.php?hdl=mymonthlystoragefee&action=list&partnercode=GO',
            reader: {
                type: 'json',
                rootProperty: 'records',
            }
        },
    }
});