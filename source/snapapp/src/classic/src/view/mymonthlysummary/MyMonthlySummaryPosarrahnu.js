Ext.define('snap.view.mymonthlysummary.MyMonthlySummaryPosarrahnu', {
    extend: 'snap.view.mymonthlysummary.MyMonthlySummary',
    xtype: 'myposarrahnumonthlysummaryview',
    partnercode: 'POSARRAHNU',
    store: {
        type: 'MyMonthlySummary', proxy: {
            type: 'ajax',
            url: 'index.php?hdl=mymonthlysummary&action=list&partnercode=POSARRAHNU',
            reader: {
                type: 'json',
                rootProperty: 'records',
            }
        },
    },
});