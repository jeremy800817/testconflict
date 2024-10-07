Ext.define('snap.view.mymonthlysummary.MyMonthlySummaryNoor', {
    extend: 'snap.view.mymonthlysummary.MyMonthlySummary',
    xtype: 'mynoormonthlysummaryview',
    partnercode: 'NOOR',
    store: {
        type: 'MyMonthlySummary', proxy: {
            type: 'ajax',
            url: 'index.php?hdl=mymonthlysummary&action=list&partnercode=NOOR',
            reader: {
                type: 'json',
                rootProperty: 'records',
            }
        },
    },
});