Ext.define('snap.view.mymonthlysummary.MyMonthlySummaryMcash', {
    extend: 'snap.view.mymonthlysummary.MyMonthlySummary',
    xtype: 'mymcashmonthlysummaryview',
    partnercode: 'MCASH',
    store: {
        type: 'MyMonthlySummary', proxy: {
            type: 'ajax',
            url: 'index.php?hdl=mymonthlysummary&action=list&partnercode=MCASH',
            reader: {
                type: 'json',
                rootProperty: 'records',
            }
        },
    },
});