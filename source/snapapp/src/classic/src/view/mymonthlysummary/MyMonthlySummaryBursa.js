Ext.define('snap.view.mymonthlysummary.MyMonthlySummaryBursa', {
    extend: 'snap.view.mymonthlysummary.MyMonthlySummary',
    xtype: 'mybursamonthlysummaryview',
    partnercode: 'BURSA',
    store: {
        type: 'MyMonthlySummary', proxy: {
            type: 'ajax',
            url: 'index.php?hdl=mymonthlysummary&action=list&partnercode=BURSA',
            reader: {
                type: 'json',
                rootProperty: 'records',
            }
        },
    },
});