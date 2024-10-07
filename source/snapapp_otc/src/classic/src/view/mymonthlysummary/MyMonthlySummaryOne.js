Ext.define('snap.view.mymonthlysummary.MyMonthlySummaryOne', {
    extend: 'snap.view.mymonthlysummary.MyMonthlySummary',
    xtype: 'myonemonthlysummaryview',
    partnercode: 'ONE',
    store: {
        type: 'MyMonthlySummary', proxy: {
            type: 'ajax',
            url: 'index.php?hdl=mymonthlysummary&action=list&partnercode=ONE',
            reader: {
                type: 'json',
                rootProperty: 'records',
            }
        },
    },
});