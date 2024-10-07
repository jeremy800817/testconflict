Ext.define('snap.view.mymonthlystoragefee.MyMonthlyStorageFeeOnecall', {
    extend: 'snap.view.mymonthlystoragefee.MyMonthlyStorageFee',
    xtype: 'myonecallmonthlystoragefeeview',

    partnercode: 'ONECALL',
    permissionRoot: '/root/onecall/report/storagefee',
    store: {
        type: 'MyMonthlyStorageFee', proxy: {
            type: 'ajax',
            url: 'index.php?hdl=mymonthlystoragefee&action=list&partnercode=ONECALL',
            reader: {
                type: 'json',
                rootProperty: 'records',
            }
        },
    }
});