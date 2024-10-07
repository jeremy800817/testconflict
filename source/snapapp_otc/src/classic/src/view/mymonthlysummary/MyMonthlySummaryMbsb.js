Ext.define('snap.view.mymonthlysummary.MyMonthlySummaryMbsb', {
    extend: 'snap.view.mymonthlysummary.MyMonthlySummary',
    xtype: 'mymbsbmonthlysummaryview',
    partnercode: 'MBSB',
    store: {
        type: 'MyMonthlySummary', proxy: {
            type: 'ajax',
            url: 'index.php?hdl=mymonthlysummary&action=list&partnercode=MBSB',
            reader: {
                type: 'json',
                rootProperty: 'records',
            }
        },
    },
});