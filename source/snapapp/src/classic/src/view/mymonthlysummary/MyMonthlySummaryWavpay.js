Ext.define('snap.view.mymonthlysummary.MyMonthlySummaryWavpay', {
    extend: 'snap.view.mymonthlysummary.MyMonthlySummary',
    xtype: 'mywavpaymonthlysummaryview',
    partnercode: 'WAVPAY',
    store: {
        type: 'MyMonthlySummary', proxy: {
            type: 'ajax',
            url: 'index.php?hdl=mymonthlysummary&action=list&partnercode=WAVPAY',
            reader: {
                type: 'json',
                rootProperty: 'records',
            }
        },
    },
});