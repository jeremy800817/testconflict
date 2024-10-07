Ext.define('snap.view.mymonthlysummary.MyMonthlySummaryToyyib', {
    extend: 'snap.view.mymonthlysummary.MyMonthlySummary',
    xtype: 'mytoyyibmonthlysummaryview',
    partnercode: 'TOYYIB',
    store: {
        type: 'MyMonthlySummary', proxy: {
            type: 'ajax',
            url: 'index.php?hdl=mymonthlysummary&action=list&partnercode=TOYYIB',
            reader: {
                type: 'json',
                rootProperty: 'records',
            }
        },
    },
});