Ext.define('snap.view.mymonthlysummary.MyMonthlySummaryGo', {
    extend: 'snap.view.mymonthlysummary.MyMonthlySummary',
    xtype: 'mygomonthlysummaryview',
    partnercode: 'GO',
    store: {
        type: 'MyMonthlySummary', proxy: {
            type: 'ajax',
            url: 'index.php?hdl=mymonthlysummary&action=list&partnercode=GO',
            reader: {
                type: 'json',
                rootProperty: 'records',
            }
        },
    },
});