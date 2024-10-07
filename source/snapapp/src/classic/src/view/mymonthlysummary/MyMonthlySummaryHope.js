Ext.define('snap.view.mymonthlysummary.MyMonthlySummaryHope', {
    extend: 'snap.view.mymonthlysummary.MyMonthlySummary',
    xtype: 'myhopemonthlysummaryview',
    partnercode: 'HOPE',
    store: {
        type: 'MyMonthlySummary', proxy: {
            type: 'ajax',
            url: 'index.php?hdl=mymonthlysummary&action=list&partnercode=HOPE',
            reader: {
                type: 'json',
                rootProperty: 'records',
            }
        },
    },
});