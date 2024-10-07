Ext.define('snap.view.mymonthlystoragefee.MyMonthlyStorageFeeOne', {
    extend: 'snap.view.mymonthlystoragefee.MyMonthlyStorageFee',
    xtype: 'myonemonthlystoragefeeview',

    partnercode: 'ONE',
    permissionRoot: '/root/one/report/storagefee',
    store: {
        type: 'MyMonthlyStorageFee', proxy: {
            type: 'ajax',
            url: 'index.php?hdl=mymonthlystoragefee&action=list&partnercode=ONE',
            reader: {
                type: 'json',
                rootProperty: 'records',
            }
        },
    }
});