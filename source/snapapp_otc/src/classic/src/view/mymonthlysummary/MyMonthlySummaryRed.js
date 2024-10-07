Ext.define('snap.view.mymonthlysummary.MyMonthlySummaryRed', {
    extend: 'snap.view.mymonthlysummary.MyMonthlySummary',
    xtype: 'myredmonthlysummaryview',
    partnercode: 'RED',
    store: {
        type: 'MyMonthlySummary', proxy: {
            type: 'ajax',
            url: 'index.php?hdl=mymonthlysummary&action=list&partnercode=RED',
            reader: {
                type: 'json',
                rootProperty: 'records',
            }
        },
    },
});