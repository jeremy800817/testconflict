Ext.define('snap.view.mymonthlysummary.MyMonthlySummaryOnecall', {
    extend: 'snap.view.mymonthlysummary.MyMonthlySummary',
    xtype: 'myonecallmonthlysummaryview',
    partnercode: 'ONECALL',
    store: {
        type: 'MyMonthlySummary', proxy: {
            type: 'ajax',
            url: 'index.php?hdl=mymonthlysummary&action=list&partnercode=ONECALL',
            reader: {
                type: 'json',
                rootProperty: 'records',
            }
        },
    },
});