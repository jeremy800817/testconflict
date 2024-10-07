Ext.define('snap.view.mymonthlysummary.MyMonthlySummaryAir', {
    extend: 'snap.view.mymonthlysummary.MyMonthlySummary',
    xtype: 'myairmonthlysummaryview',
    partnercode: 'AIR',
    store: {
        type: 'MyMonthlySummary', proxy: {
            type: 'ajax',
            url: 'index.php?hdl=mymonthlysummary&action=list&partnercode=AIR',
            reader: {
                type: 'json',
                rootProperty: 'records',
            }
        },
    },
});