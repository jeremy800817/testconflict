Ext.define('snap.view.orderdashboard.MySpotOrderSpecialPosarrahnu', {
    extend:'snap.view.orderdashboard.MySpotOrderSpecial',
    permissionRoot: '/root/posarrahnu/sale',
    xtype: 'myspotorderspecialposarrahnuview', 
    type: 'posarrahnu',
    partnerCode : 'POSARRAHNU',
    priceStreamCode : 'Posarrahnu',
    requires: [

        //'Ext.layout.container.Fit',
        'snap.store.PosarrahnuSalesPriceStream',

    ],
});