Ext.define('snap.view.redemption.RedemptionGridForm', {
    extend: 'snap.view.gridpanel.GridForm',
    alias: 'widget.RedemptionGridForm',
    requires: [
        'Ext.panel.Panel',
        'Ext.window.Window',
        'Ext.form.*',
        'snap.view.gridpanel.BaseController',
        'snap.view.redemption.RedemptionTreeController'
    ],
    store:{
        teststore:Ext.create('snap.store.ProductItems'),
        pricesourceproviders:Ext.create('snap.store.PriceSourceProviders'),        
    },
    controller: 'gridpanel-redemptiontreecontroller',
    reference: 'formWindow',
    formDialogTitle: 'Redemption',
    formDialogWidth: '80%',
    enableFormDialogClosable: false,
    formPanelDefaults: {
        msgTarget: 'side',
        margins: '0 0 10 0'
    },
    height: '100%',
    formPanelDefaults: {
        border: false,
        //scrollable: true,
    },
    listeners: {
        'beforeedit': function (editor, e) {
            
        },
    },
    enableFormPanelFrame: false,
    formPanelItems: [
    ]
});
