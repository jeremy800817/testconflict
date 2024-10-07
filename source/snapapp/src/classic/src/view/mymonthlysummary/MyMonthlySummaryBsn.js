Ext.define('snap.view.mymonthlysummary.MyMonthlySummaryBsn', {
    extend: 'snap.view.mymonthlysummary.MyMonthlySummary',
    xtype: 'mybsnmonthlysummaryview',
    partnercode: 'BSN',
    store: {
        type: 'MyMonthlySummary', proxy: {
            type: 'ajax',
            url: 'index.php?hdl=mymonthlysummary&action=list&partnercode=BSN',
            reader: {
                type: 'json',
                rootProperty: 'records',
            }
        },
    },
});