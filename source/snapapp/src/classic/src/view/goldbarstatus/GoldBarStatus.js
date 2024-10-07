Ext.define('snap.view.goldbarstatus.GoldBarStatus', {
    extend: 'Ext.container.Container',
    xtype: 'goldbarstatusview',
    requires: [
        'snap.store.VaultItem',
        'snap.model.VaultItem',
        'snap.view.goldbarstatus.GoldBarStatusController',
        'snap.view.goldbarstatus.GoldBarStatusModel'
    ],
    controller: 'goldbarstatus-goldbarstatus',
    viewModel: {
        type: 'goldbarstatus-goldbarstatus',
    },
    layout: {
        type: 'vbox',
        align: 'center'
    },
    listeners: {
        afterrender: function () {
            snap.getApplication().sendRequest({
                hdl: 'goldbarstatus', action: 'getstatuscount'
            }, 'Fetching data from server....').then(
                function (data) {
                    if (data.success) {
                        Ext.get('logicalcount').dom.innerHTML = data.logicalcount;
                        Ext.get('reservedcount').dom.innerHTML = data.hqcount;
                        Ext.get('aceg4scount').dom.innerHTML = data.aceg4scount;
                        Ext.get('mbbg4scount').dom.innerHTML = data.mbbg4scount;
                        Ext.get('totalcount').dom.innerHTML = data.total;
                        Ext.get('overallcount').dom.innerHTML = data.overall;
                        
                    }
                })
        }
    },
    items: [
        {
            xtype: 'panel',
            layout: {
                type: 'vbox',
            },
            width: '100%',
            style: {
                padding: '5px',
            },
            items: [
                {
                    title: 'Kilobar Inventory By Warehouse Location',
                    header: {
                        style: 'background-color: #204A6D;border-color: #204A6D;',
                    },
                    layout: 'vbox',
                    width: '100%',
                    items: [   
                        {
                            layout:'hbox',
                            width: '100%',
                            items:[
                                {
                                    layout: 'vbox',
                                    width: '33.2%',
                                    style: {
                                        'margin': '5px 5px 0px 0px',
                                    },
                                    items: [
                                        {
                                            html: '<div style="line-height: 10px;background:#204A6D;padding:5px;text-align:center"><span style="color:#ffffff;width:100%;">TAIPAN</span></div>',
                                            width: '100%',
                                        },
        
                                    ]
                                },
                                {
                                    layout: 'vbox',
                                    width: '33.2%',
                                    style: {
                                        'margin': '5px 5px 0px 0px',
                                    },
                                    items: [
                                        {
                                            html: '<div style="line-height: 10px;background:#204A6D;padding:5px;text-align:center"><span style="color:#ffffff;width:100%;">G4S</span></div>',
                                            width: '100%',
                                        },
        
                                    ]
                                },
                                {
                                    layout: 'vbox',
                                    width: '33.33%',
                                    style: {
                                        'margin': '5px 5px 0px 0px',
                                    },
                                    items: [
                                        {
                                            html: '<div style="line-height: 10px;background:#204A6D;padding:5px;text-align:center"><span style="color:#ffffff;width:100%;">TOTAL</span></div>',
                                            width: '100%',
                                        },
        
                                    ]
                                },
                            ]
                        } ,                   
                        {
                            layout:'hbox',
                            width: '100%',
                            items:[
                                {
                                    layout: 'vbox',
                                    width: '16.6%',
                                    style: {
                                        'margin': '5px 5px 0px 0px',
                                    },
                                    items: [
                                        {
                                            html: '<div style="text-align: center;vertical-align: middle;line-height: 40px;background:#988c59"><span style="color:#ffffff;font-size:3em;font-weight:900;width:100%" id="logicalcount">-</span><div style="color:#ffffff;font-size:1.3em;">LOGICAL</div></div>',
                                            width: '100%',
                                        },
        
                                    ]
                                },
                                {
                                    layout: 'vbox',
                                    width: '16.6%',
                                    style: {
                                        'margin': '5px 5px 0px 0px',
                                    },
                                    items: [
                                        {
                                            html: '<div style="text-align: center;vertical-align: middle;line-height: 40px;background:#0D47A1"><span style="color:#ffffff;font-size:3em;font-weight:900;width:100%" id="reservedcount">-</span><div style="color:#ffffff;font-size:1.3em;">RESERVED</div></div>',
                                            width: '100%',
                                        },
        
                                    ]
                                },
                                {
                                    layout: 'vbox',
                                    width: '16.6%',
                                    style: {
                                        'margin': '5px 5px 0px 0px',
                                    },
                                    items: [
                                        {
                                            html: '<div style="text-align: center;vertical-align: middle;line-height: 40px;background:#ffb91b"><span style="color:#ffffff;font-size:3em;font-weight:900;width:100%" id="aceg4scount">-</span><div style="color:#ffffff;font-size:1.3em;">G4S-ACE</div></div>',
                                            width: '100%',
                                        },
        
                                    ]
                                },
                                {
                                    layout: 'vbox',
                                    width: '16.6%',
                                    style: {
                                        'margin': '5px 5px 0px 0px',
                                    },
                                    items: [
                                        {
                                            html: '<div style="text-align: center;vertical-align: middle;line-height: 40px;background:#1aa124"><span style="color:#ffffff;font-size:3em;font-weight:900;width:100%" id="mbbg4scount">-</span><div style="color:#ffffff;font-size:1.3em;">G4S-MBB</div></div>',
                                            width: '100%',
                                        },
        
                                    ]
                                },
                                {
                                    layout: 'vbox',
                                    width: '16.6%',
                                    style: {
                                        'margin': '5px 5px 0px 0px',
                                    },
                                    items: [
                                        {
                                            html: '<div style="text-align: center;vertical-align: middle;line-height: 40px;background:#B71C1C"><span style="color:#ffffff;font-size:3em;font-weight:900;width:100%" id="totalcount">-</span><div style="color:#ffffff;font-size:1.3em;">TOTAL</div></div>',
                                            width: '100%',
                                        },
        
                                    ]
                                },
                                ,
                                {
                                    layout: 'vbox',
                                    width: '16.6%',
                                    style: {
                                        'margin': '5px 5px 0px 0px',
                                    },
                                    items: [
                                        {
                                            html: '<div style="text-align: center;vertical-align: middle;line-height: 40px;background:#fc4e70"><span style="color:#ffffff;font-size:3em;font-weight:900;width:100%" id="overallcount">-</span><div style="color:#ffffff;font-size:1.3em;">OVERALL</div></div>',
                                            width: '100%',
                                        },
        
                                    ]
                                },
                            ]
                        }

                        
                    ]
                }, {
                    title: 'Minted GoldBar Inventory By MIB Branch Location',
                    header: {
                        style: 'background-color: #204A6D;border-color: #204A6D;',
                    },
                    region: 'center',
                    margin: '5 0 0 0',
                    xtype: 'goldbarlocationwise',
                }
            ],
        }
    ]
});
