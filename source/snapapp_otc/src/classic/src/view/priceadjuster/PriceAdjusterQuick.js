Ext.define('snap.view.priceadjuster.PriceAdjusterQuick1', {
    extend: 'Ext.window.Window',
    constrainHeader: true,
    title: "FIND",
    initComponent: function() {
        this.okButton = Ext.create("Ext.button.Button", {
            text: "A"
        });
        this.buttons = [this.okButton];
        this.callParent();
    }
});

Ext.define('snap.view.priceadjuster.PriceAdjusterQuick2', {
    extend: 'Ext.window.Window',

    height: 334,
    width: 540,
    layout: {
        type: 'border'
    },
    title: 'Run report',

    initComponent: function () {
        var me = this;

        Ext.applyIf(me, {
            items: [{
                xtype: 'form',
                bodyPadding: 10,
                region: 'center'
            }]
        });

        me.callParent(arguments);
    }
});



Ext.define('snap.view.priceadjuster.PriceAdjusterQuick', {
    extend: 'snap.view.gridpanel.GridForm',
    alias: 'widget.PriceAdjusterQuick',
    requires: [
        'Ext.panel.Panel',
        'Ext.window.Window',
        'Ext.form.*',
        'snap.view.gridpanel.BaseController',
        'snap.view.priceadjuster.PriceAdjusterController',
        'Ext.view.MultiSelector',
        'Ext.grid.*',
        'Ext.layout.container.Column',
    ],
    viewModel: {
        data: {
            theCompany: null,
            inputgrossweight: 0,

            total_poweight: 100, // all po sum weight
            total_grossweight: 0,   // after * purity(all)
            total_balanceweight: 0, // remaining po weight
            total_purity: 0,    // purity(all)

        }
    },
    store:{
        selectedGRNStore: {},
        hours: [{
            id: '1',
            name: 'Peak Hours',
            time: '07:00:00',
            timeend: '17:59:59',
        },{
            id: '2',
            name: 'Non-Peak Hours',
            time: '18:00:00',
            timeend: '06:59:59',
        }],
    },
    controller: 'priceadjuster-priceadjuster',
    reference: 'formWindow',
    formDialogTitle: 'PriceAdjuster',
    formDialogWidth: '1000px',
    enableFormDialogClosable: false,
    formPanelDefaults: {
        msgTarget: 'side',
        margins: '0 0 10 0'
    },
    height: '530px',
    formPanelDefaults: {
        border: false,
        //scrollable: true,
    },
    listeners: {
        'beforeedit': function (editor, e) {

        },
    },
    enableFormPanelFrame: false,

    initComponent: function(){
        _this = this;
        vm = this.getViewModel();

        this.callParent(arguments);
        return;

        var websocketurl = window.location.protocol == 'https:' ? 'wss://' : 'ws://'; websocketurl += window.location.hostname + '/index.php?hdl=pssubscribe&action=subscribe&pdt=DG-999-9&mib=1';
        var websocket2 = Ext.create ('Ext.ux.WebSocket', {
            url: websocketurl,
            // url: 'ws://bo.gtp.development/streamprice?hdl=pssubscribe&action=subscribe&pdt=DG-999-9&mib=1' ,
            listeners: {
                open: function (ws) {
                } ,
                close: function (ws) {
                    console.log ('The websocket is closed!');
                },
                error: function (ws, error) {
                    Ext.Error.raise ('ERRRROR: ' + error);
                } ,
                message: function (ws, message) {
                    message.data[0].companybuykg = (message.data[0].companybuy * 1000.00).toLocaleString();
                    message.data[0].companysellkg = (message.data[0].companysell * 1000.00).toLocaleString();
                    message.data[0].datetime = new Date(message.data[0].timestamp * 1000.00).toLocaleString();
                    vm.set("INTLX.MBB", message.data[0]);

                }
            },
        });


        var websocketurl_dealer = window.location.protocol == 'https:' ? 'wss://' : 'ws://'; websocketurl += window.location.hostname + '/index.php?hdl=pssubscribe&action=subscribe&pdt=DG-999-9&dealer1=1';
        var websocket3 = Ext.create ('Ext.ux.WebSocket', {
            url: (window.location.protocol == 'https:' ? 'wss://' : 'ws://') + window.location.hostname + '/index.php?hdl=pssubscribe&action=subscribe&pdt=DG-999-9&dealer1=1',
            // url: 'ws://bo.gtp.development/streamprice?hdl=pssubscribe&action=subscribe&pdt=DG-999-9&mib=1' ,
          // curl -H "Accept: text/event-stream" "http://migasit.ace2u.com/index.php?hdl=pssubscribe&action=subscribe&pdt=DG-999-9&mib=1"
            listeners: {
                open: function (ws) {
                } ,
                close: function (ws) {
                    console.log ('The websocket is closed!');
                },
                error: function (ws, error) {
                    Ext.Error.raise ('ERRRROR: ' + error);
                } ,
                message: function (ws, message) {
                    message.data[0].companybuykg = (message.data[0].companybuy * 1000.00).toLocaleString();
                    message.data[0].companysellkg = (message.data[0].companysell * 1000.00).toLocaleString();
                    message.data[0].datetime = new Date(message.data[0].timestamp * 1000.00).toLocaleString();
                    vm.set("IntxX.Dealer1", message.data[0]);

                }
            },
        });
        var websocketurl_mks = window.location.protocol == 'https:' ? 'wss://' : 'ws://'; websocketurl += window.location.hostname + '/index.php?hdl=pssubscribe&action=subscribe&pdt=DG-999-9&mks=1';
        var websocket4 = Ext.create ('Ext.ux.WebSocket', {
            url: (window.location.protocol == 'https:' ? 'wss://' : 'ws://') + window.location.hostname + '/index.php?hdl=pssubscribe&action=subscribe&pdt=DG-999-9&mks=1',
            // url: 'ws://bo.gtp.development/streamprice?hdl=pssubscribe&action=subscribe&pdt=DG-999-9&mib=1' ,
          // curl -H "Accept: text/event-stream" "http://migasit.ace2u.com/index.php?hdl=pssubscribe&action=subscribe&pdt=DG-999-9&mib=1"
            listeners: {
                open: function (ws) {
                } ,
                close: function (ws) {
                    console.log ('The websocket is closed!');
                },
                error: function (ws, error) {
                    Ext.Error.raise ('ERRRROR: ' + error);
                } ,
                message: function (ws, message) {
                    message.data[0].companybuykg = (message.data[0].companybuy * 1000.00).toLocaleString();
                    message.data[0].companysellkg = (message.data[0].companysell * 1000.00).toLocaleString();
                    message.data[0].datetime = new Date(message.data[0].timestamp * 1000.00).toLocaleString();
                    vm.set("IntxX.Mks", message.data[0]);

                }
            },
        });
        var websocketurl_pos = window.location.protocol == 'https:' ? 'wss://' : 'ws://'; websocketurl += window.location.hostname + '/index.php?hdl=pssubscribe&action=subscribe&pdt=DG-999-9&pos=1';
        var websocket5 = Ext.create ('Ext.ux.WebSocket', {
            url: (window.location.protocol == 'https:' ? 'wss://' : 'ws://') + window.location.hostname + '/index.php?hdl=pssubscribe&action=subscribe&pdt=DG-999-9&pos=1',
            // url: 'ws://bo.gtp.development/streamprice?hdl=pssubscribe&action=subscribe&pdt=DG-999-9&mib=1' ,
          // curl -H "Accept: text/event-stream" "http://migasit.ace2u.com/index.php?hdl=pssubscribe&action=subscribe&pdt=DG-999-9&mib=1"
            listeners: {
                open: function (ws) {
                } ,
                close: function (ws) {
                    console.log ('The websocket is closed!');
                },
                error: function (ws, error) {
                    Ext.Error.raise ('ERRRROR: ' + error);
                } ,
                message: function (ws, message) {
                    message.data[0].companybuykg = (message.data[0].companybuy * 1000.00).toLocaleString();
                    message.data[0].companysellkg = (message.data[0].companysell * 1000.00).toLocaleString();
                    message.data[0].datetime = new Date(message.data[0].timestamp * 1000.00).toLocaleString();
                    vm.set("IntxX.POS", message.data[0]);

                }
            },
        });
        this.callParent(arguments);
    },
    scrollable: true,
    height: '94%',
    formPanelMaxHeight: '100%',
    formPanelItems: [
        {
            xtype: 'fieldset',
            title: 'Quick Price Adjuster',
            scrollable: true,
            height: '100%',
            items: [{
                xtype: 'container',
                layout: {
                    type: 'vbox',
                    align: 'stretch'
                },
                items: [
                    {
                        xtype: 'container',
                        html: 'Loading..',
                        reference: 'quickadjusterDisplayContainer',
                    },
                    {
                        xtype: 'container',
                        margin: '0 0 0 0',
                        layout: {
                            type: 'vbox',
                            align: 'stretch'
                        },
                        scrollable: true,
                        reference: 'notesDisplays',
                    },
                    // {
               
                    //     xtype: 'container',
                    //     margin: '0 0 0 30',
                    //     reference: 'notesDisplays2',
                    // },

                    // {
                    //         xtype: 'container',
                    //         title: 'MIB Open GP',
                    //         headerPosition: 'top',
                    //         constrain: true,
                    //         x: 300, y: 395, alwaysOnTop: 7,
                    //         width: 300,
                    //         height: 280,
                    //         cls: ['background-blue','tradeprice-window'],
                    //         items: [{
                    //             xtype: 'container',
                    //             padding: '10px',
                    //             layout: {
                    //                 type:'table',
                    //                 columns: 2,
                    //                 trAttrs: { style: { 'text-align': 'center' } },
                    //                 tdAttrs: { style: { 'border': '1px solid black',  } }
                    //             },
                    //             defaults: {
                    //                 width: "100%"
                    //             },
                    //             items: [{
                    //                 xtype: 'displayfield',
                    //                 value: 'Buy',
                    //             },{
                    //                 xtype: 'displayfield',
                    //                 value: 'Sell',
                    //             },{
                    //                 xtype: 'container',
                    //                 layout: {
                    //                     type: 'vbox',
                    //                     align: 'center',
                    //                     pack: 'center'
                    //                 },
                    //                 items:[{
                    //                     xtype: 'displayfield',
                    //                     value: '0.00',
                    //                     bind: {
                    //                         value: '{mibchannel.companybuy}'
                    //                     },
                    //                     cls: 'largetext',
                    //                 },{
                    //                     xtype: 'displayfield',
                    //                     cls:'boldtext',
                    //                     value: 'per g',
                    //                 }]
                    //             },{
                    //                 xtype: 'container',
                    //                 layout: {
                    //                     type: 'vbox',
                    //                     align: 'center',
                    //                     pack: 'center'
                    //                 },
                    //                 items:[{
                    //                     xtype: 'displayfield',
                    //                     value: '0.00',
                    //                     bind: {
                    //                         value: '{mibchannel.companysell}'
                    //                     },
                    //                     cls: 'largetext',
                    //                 },{
                    //                     xtype: 'displayfield',
                    //                     cls:'boldtext',
                    //                     value: 'per g',
                    //                 }]
                    //             },{
                    //                 xtype: 'container',
                    //                 layout: {
                    //                     type: 'vbox',
                    //                     align: 'center',
                    //                     pack: 'center'
                    //                 },
                    //                 items:[{
                    //                     xtype: 'displayfield',
                    //                     value: 'UUID',
                    //                     cls: 'cusdisplay'
                    //                 },{
                    //                     xtype: 'displayfield',
                    //                     value: '-',
                    //                     bind: {
                    //                         value: '{mibchannel.uuid}'
                    //                     },
                    //                 }],
                    //                 colspan: 2,
                    //             },{
                    //                 xtype: 'container',
                    //                 layout: {
                    //                     type: 'vbox',
                    //                     align: 'center',
                    //                     pack: 'center'
                    //                 },
                    //                 items:[{
                    //                     xtype: 'displayfield',
                    //                     value: 'TimeStamp',
                    //                     cls: 'cusdisplay'
                    //                 },{
                    //                     xtype: 'displayfield',
                    //                     value: '-',
                    //                     bind: {
                    //                         value: '{mibchannel.datetime}'
                    //                     },
                    //                 }],
                    //                 colspan: 2,
                    //             }]
                    //         }]
                    // },
                
                    // {
               
                    //     xtype: 'container',
                    //     margin: '0 0 0 30',
                    //     // stytle: 'position:absolute',
                    //     html: [`
                    //     <div style="font-size: 0.7rem;">
                    //         <span style="color:#00309c">ACG Buy GP = INTLb (USD/oz) x ReutersFXb(USD → MYR) x 32.148 (Oz-->Kg) - Refining INTL - Premium ReutersFXb - Marginb - Spread Adjustor</span>
                    //         <br><b>INTLb</b>: INTL Gold buy price
                    //         <br><b>ReutersFXb</b>: Reuters USD->MYR buy price
                    //         <br><b>Refining INTL</b>: INTL Refining Fee
                    //         <br><b>Premium ReutersFXb</b>: Buy Premium for ReutersFX
                    //         <br><b>Marginb</b>: ACG Buy Margin
                    //         <br><b>Spread Adjustor</b>: Price adjustor
                    //         <br><br>
                    //         <span style="color:#00309c">ACG Sell GP = INTLs (USD/oz) x ReutersFXs(MYR → USD) x 32.148 (Oz-->Kg) + Premium INTL + Premium ReutersFXs + Margins + Spread Adjustor</span>
                    //         <br><b>INTLs</b>: INTL Gold sell price
                    //         <br><b>ReutersFXs</b>: Reuters MYR->USD sell price
                    //         <br><b>Premium INTL</b>: Premium for INTL
                    //         <br><b>Premium ReutersFXs</b>: Sell Premium for ReutersFX
                    //         <br><b>Margins</b>: ACG Sell Margin
                    //         <br><b>Spread Adjustor</b>: Price adjustor
                    //     </div>
                    //         `
                    //     ]
                    // }
                ]
            },]
        }
    ]
});
