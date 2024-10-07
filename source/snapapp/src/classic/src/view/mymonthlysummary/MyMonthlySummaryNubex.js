Ext.define('snap.view.mymonthlysummary.MyMonthlySummaryNubex', {
    extend: 'snap.view.mymonthlysummary.MyMonthlySummary',
    xtype: 'mynubexmonthlysummaryview',
    partnercode: 'NUBEX',
    store: {
        type: 'MyMonthlySummary', proxy: {
            type: 'ajax',
            url: 'index.php?hdl=mymonthlysummary&action=list&partnercode=NUBEX',
            reader: {
                type: 'json',
                rootProperty: 'records',
            }
        },
    },
});