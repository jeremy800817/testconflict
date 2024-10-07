Ext.define('snap.view.redemption.MyConversionBursa', {
    extend:'snap.view.redemption.Redemption',
    permissionRoot: '/root/bursa/redemption',
    xtype: 'bursaconversionview',
    viewModel: {
        type: 'redemption-redemption'
    },  
    store: { type: 'MyConversion' },
    items: [
        { reference: 'redemptionreq', ui: 'redemptionrequests',  xtype: 'redemptionrequests', partnercode: 'BURSA', partneremail: "ang@silverstream.my", personalcourier: true,
        store: {
            type: 'MyConversion', proxy: {
                type: 'ajax',
                url: 'index.php?hdl=redemption&action=list&partnercode=BURSA',
                reader: {
                    type: 'json',
                    rootProperty: 'records',
                }
            },
        },},
        { reference: 'redemptionsummary', ui: 'redemptionsummary',  xtype: 'redemptionsummary', type: 'bursa'},
    ]
});