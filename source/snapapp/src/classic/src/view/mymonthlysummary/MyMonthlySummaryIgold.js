Ext.define('snap.view.mymonthlysummary.MyMonthlySummaryIgold', {
    extend: 'snap.view.mymonthlysummary.MyMonthlySummary',
    xtype: 'myigoldmonthlysummaryview',
    partnercode: 'IGOLD',
    store: {
        type: 'MyMonthlySummary', proxy: {
            type: 'ajax',
            url: 'index.php?hdl=mymonthlysummary&action=list&partnercode=IGOLD',
            reader: {
                type: 'json',
                rootProperty: 'records',
            }
        },
    },
});