Ext.define('snap.view.mymonthlysummary.MyMonthlySummaryBmmb', {
    extend: 'snap.view.mymonthlysummary.MyMonthlySummary',
    xtype: 'mybmmbmonthlysummaryview',
    partnercode: 'BMMB',
    store: {
        type: 'MyMonthlySummary', proxy: {
            type: 'ajax',
            url: 'index.php?hdl=mymonthlysummary&action=list&partnercode=BMMB',
            reader: {
                type: 'json',
                rootProperty: 'records',
            }
        },
    },
});