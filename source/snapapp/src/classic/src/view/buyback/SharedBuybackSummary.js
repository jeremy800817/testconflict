Ext.define('snap.view.buyback.SharedBuybackSummary', {
    extend: 'Ext.panel.Panel',
    xtype: 'sharedbuybacksummary',
    title: '',
    requires: [
        'snap.store.Buyback',
        'snap.model.Buyback',
        'snap.view.buyback.BuybackController',
        'snap.view.buyback.BuybackModel',

    ],
  
    //store: { type: 'RedemptionStatusCounts' },
    listeners: {
        afterrender: function () {
            snap.getApplication().sendRequest({
                hdl: 'buyback', action: 'getSummary', partner: this.partnercode,
            }, 'Fetching data from server....').then(
                function (data) {                    
                    if (data.success) {
                        Ext.get('buybackpendingcount').dom.innerHTML  = data.pendingstatuscount;
                        Ext.get('buybackconfirmedcount').dom.innerHTML  = data.confirmedstatuscount;
                        Ext.get('buybackdeliverycount').dom.innerHTML  = data.deliverystatuscount;	
                        Ext.get('buybackcompletedcount').dom.innerHTML  = data.completedstatuscount;			
                        Ext.get('buybackfailedcount').dom.innerHTML  = data.failedstatuscount;		
                        Ext.get('buybackreversedcount').dom.innerHTML  = data.reversedstatus;
                    }
                })
        }
    },
    controller: 'buyback-buyback',
    width: '100%',
    //height: 150,
    layout: {
        type: 'hbox',
    },
    defaults: {
        bodyStyle: 'padding:0px;margin-top:10px',
    },

    items: [
        {
            html: '<div style="background:#007bc5;height:40px;padding-top:6px;color:#ffffff;margin-left:2px;text-align:center">Pending : <span id="buybackpendingcount">0</span>&nbsp;</div>',
            flex: 1,
        }, {
            html: '&nbsp;',
        }, {
            html: '<div style="background:#FFA500;height:40px;padding-top:6px;color:#ffffff;margin-left:2px;text-align:center">Confirmed : <span id="buybackconfirmedcount">0</span>&nbsp;</div>',
            flex: 1,
        }, {
            html: '&nbsp;',
        }
        , {
            html: '<div style="background:#0ead30;height:40px;padding-top:6px;color:#ffffff;margin-left:2px;text-align:center">Process Collect : <span id="buybackdeliverycount">0</span>&nbsp;</div>',
            flex: 1,
        }, {
            html: '&nbsp;',
        }
        , {
            html: '<div style="background:#F42A12;height:40px;color:#ffffff;padding-top:6px;margin-left:2px;text-align:center">Completed : <span id="buybackcompletedcount">0</span>&nbsp;</div>',
            flex: 1,
        }, {
            html: '&nbsp;',
        }, {
            html: '<div style="background:#6C3483;height:40px;color:#ffffff;padding-top:6px;margin-left:2px;text-align:center">Failed Collection: <span id="buybackfailedcount">0</span>&nbsp;</div>',
            flex: 1,
        }, {
            html: '&nbsp;',
        }, {
            html: '<div style="background:#6E2C00;height:40px;color:#ffffff;padding-top:6px;margin-left:2px;text-align:center">Reversed : <span id="buybackreversedcount">0</span>&nbsp;</div>',
            flex: 1,
        },
        {
            html: '<div style="background:#BF0000;height:40px;color:#ffffff;padding-top:6px;margin-left:2px;text-align:center">Failed : <span id="buybackfailedtotalcount">0</span>&nbsp;</div>',
            flex: 1,
        }, {
            html: '&nbsp;',
        }
    ],

});